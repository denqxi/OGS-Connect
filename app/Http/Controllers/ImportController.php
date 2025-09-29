<?php

namespace App\Http\Controllers;

use App\Models\DailyData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
            ]);

            // Additional security: Check file extension
            $file = $request->file('file');
            $allowedExtensions = ['xlsx', 'xls', 'csv'];
            $fileExtension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file type. Only Excel (.xlsx, .xls) and CSV files are allowed.'
                ], 400);
            }

            // Additional security: Check MIME type
            $allowedMimeTypes = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                'application/vnd.ms-excel', // .xls
                'text/csv', // .csv
                'application/csv' // .csv alternative
            ];
            
            if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file format. Please upload a valid Excel or CSV file.'
                ], 400);
            }
            
            // Process the file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            Log::info('=== STARTING FILE UPLOAD ===', [
                'filename' => $file->getClientOriginalName(),
                'total_rows' => count($rows),
                'first_row' => $rows[0] ?? null,
                'second_row' => $rows[1] ?? null,
            ]);

            if (empty($rows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data found in the uploaded file'
                ], 400);
            }

            // Remove header row
            $hasHeaders = true;
            if ($hasHeaders && !empty($rows)) {
                Log::info('Header row removed', ['headers' => $rows[0]]);
                array_shift($rows);
            }

            if (empty($rows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data rows found after removing headers'
                ], 400);
            }

            Log::info('=== AFTER HEADER REMOVAL ===', [
                'remaining_rows' => count($rows),
                'first_data_row' => $rows[0] ?? null,
                'second_data_row' => $rows[1] ?? null,
            ]);

            // Initialize counters
            $duplicateCount = 0;
            $totalRows = 0;
            $errorRows = 0;
            $validDataRows = [];

            // First pass: Parse and validate data
            foreach ($rows as $rowIndex => $row) {
                // Skip completely empty rows
                if (empty($row) || count(array_filter($row, function($cell) { return !is_null($cell) && $cell !== ''; })) === 0) {
                    Log::info("Skipping empty row at index {$rowIndex}");
                    continue;
                }
                
                $totalRows++;
                $rowNumber = $rowIndex + 2; // +2 because we removed header and arrays are 0-indexed

                Log::info("=== PROCESSING ROW {$rowNumber} ===", [
                    'row_index' => $rowIndex,
                    'raw_row' => $row
                ]);

                try {
                    // Parse data according to your Excel structure
                    $schoolValue = $this->sanitizeString($row[0] ?? null);      
                    $classValue = $this->sanitizeString($row[1] ?? null);       
                    $dateValue = $this->parseDate($row[3] ?? null, $rowNumber); 
                    $dayValue = $this->sanitizeString($row[4] ?? null);         
                    $timeJST = $this->parseTime($row[5] ?? null, $rowNumber);   
                    $numberRequired = $this->parseNumber($row[7] ?? 0);         

                    Log::info("Parsed values for row {$rowNumber}", [
                        'school' => $schoolValue,
                        'class' => $classValue,
                        'date' => $dateValue,
                        'day' => $dayValue,
                        'time_jst' => $timeJST,
                        'number_required' => $numberRequired,
                    ]);

                    // Check if required fields are present
                    if (empty($dateValue)) {
                        $errorRows++;
                        Log::error("Row {$rowNumber}: Missing or invalid date", [
                            'raw_date' => $row[3] ?? null,
                            'parsed_date' => $dateValue
                        ]);
                        continue;
                    }

                    if (empty($schoolValue)) {
                        $errorRows++;
                        Log::error("Row {$rowNumber}: Missing school", [
                            'raw_school' => $row[0] ?? null,
                            'parsed_school' => $schoolValue
                        ]);
                        continue;
                    }

                    // Store valid data for processing
                    $validDataRows[] = [
                        'row_number' => $rowNumber,
                        'date' => $dateValue,
                        'day' => $dayValue,
                        'class' => $classValue,
                        'school' => $schoolValue,
                        'time_jst' => $timeJST,
                        'number_required' => $numberRequired
                    ];

                    // Check for duplicates (should be 0 since table is truncated)
                    if (!empty($classValue)) {
                        $existingRecord = DailyData::where('date', $dateValue)
                                                  ->where('school', $schoolValue)
                                                  ->where('class', $classValue)
                                                  ->first();
                        
                        if ($existingRecord) {
                            $duplicateCount++;
                            Log::info("Duplicate found for row {$rowNumber}", [
                                'existing_id' => $existingRecord->id
                            ]);
                        } else {
                            Log::info("No duplicate found for row {$rowNumber}");
                        }
                    }

                } catch (\Exception $e) {
                    $errorRows++;
                    Log::error("Error parsing row {$rowNumber}", [
                        'error' => $e->getMessage(),
                        'raw_row' => $row,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            $validRows = $totalRows - $errorRows;

            Log::info('=== FIRST PASS COMPLETE ===', [
                'total_rows' => $totalRows,
                'error_rows' => $errorRows,
                'valid_rows' => $validRows,
                'duplicate_count' => $duplicateCount,
                'valid_data_count' => count($validDataRows)
            ]);

            // **DEBUG: Let's see what validDataRows contains**
            Log::info('=== VALID DATA ROWS DEBUG ===', [
                'valid_data_rows_count' => count($validDataRows),
                'first_valid_row' => $validDataRows[0] ?? 'NONE',
                'second_valid_row' => $validDataRows[1] ?? 'NONE'
            ]);

            // If too many errors, stop
            if ($errorRows >= $totalRows || $validRows <= 0) {
                Log::error('=== STOPPING DUE TO ERRORS ===', [
                    'error_rows' => $errorRows,
                    'total_rows' => $totalRows,
                    'valid_rows' => $validRows
                ]);
                return response()->json([
                    'success' => false,
                    'message' => "Cannot process file. {$errorRows} out of {$totalRows} rows have errors."
                ], 400);
            }

            // Check if we have valid data to process
            if (empty($validDataRows)) {
                Log::error('=== NO VALID DATA ROWS TO PROCESS ===');
                return response()->json([
                    'success' => false,
                    'message' => "No valid data rows found to process."
                ], 400);
            }

            // Check duplicate percentage
            $duplicatePercentage = $validRows > 0 ? ($duplicateCount / $validRows) * 100 : 0;
            
            Log::info("=== DUPLICATE ANALYSIS ===", [
                'duplicate_count' => $duplicateCount,
                'valid_rows' => $validRows,
                'duplicate_percentage' => $duplicatePercentage
            ]);

            // Only reject if there are MANY duplicates (very strict)
            if ($duplicateCount >= 20 && $duplicatePercentage >= 90) {
                Log::info('=== REJECTING DUE TO DUPLICATES ===');
                return response()->json([
                    'success' => false,
                    'message' => "File appears to be already uploaded. {$duplicateCount} duplicates found."
                ], 422);
            }

            // Second pass: Actually create the records
            Log::info('=== STARTING SECOND PASS (CREATION) ===', [
                'valid_data_rows_to_process' => count($validDataRows)
            ]);
            
            $imported = 0;
            $skipped = 0;
            $updated = 0;
            $skippedRows = [];


            foreach ($validDataRows as $index => $validRow) {
                Log::info("=== PROCESSING VALID ROW {$index} (Original Row {$validRow['row_number']}) ===", $validRow);

                try {
                    // **DEBUG: Check if DailyData model is accessible**
                    $testCount = DailyData::count();
                    Log::info("Current DailyData record count: {$testCount}");

                    // Check for exact match
                    Log::info("Checking for exact match...");
                    $exactMatch = DailyData::where('date', $validRow['date'])
                                          ->where('school', $validRow['school'])
                                          ->where('class', $validRow['class'])
                                          ->where('time_jst', $validRow['time_jst'])
                                          ->where('day', $validRow['day'])
                                          ->where('number_required', $validRow['number_required'])
                                          ->first();
                    
                    if ($exactMatch) {
                        $skipped++;
                        Log::info("Skipped exact match for row {$validRow['row_number']}", [
                            'existing_id' => $exactMatch->id
                        ]);
                        continue;
                    }

                    Log::info("No exact match found. Checking for partial match...");

                    // Check for partial match
                    $partialMatch = DailyData::where('date', $validRow['date'])
                                            ->where('school', $validRow['school'])
                                            ->where('class', $validRow['class'])
                                            ->first();

                    if ($partialMatch) {
                        Log::info("Found partial match, updating...", ['existing_id' => $partialMatch->id]);
                        
                        // Update existing
                        $timePHT = !empty($validRow['time_jst'])
                            ? Carbon::parse($validRow['time_jst'])->subHour()->format('H:i:s')
                            : null;

                        $partialMatch->update([
                            'day' => $validRow['day'],
                            'time_jst' => $validRow['time_jst'],
                            'time_pht' => $timePHT, // ✅ keep both in sync
                        ]);
                        $updated++;
                        Log::info("Updated record {$partialMatch->id} for row {$validRow['row_number']}");
                    } else {
                        Log::info("No partial match found. Creating new record...");
                        
                        // Compute time_pht (subtract 1 hour from JST)
                        $timePHT = !empty($validRow['time_jst'])
                            ? Carbon::parse($validRow['time_jst'])->subHour()->format('H:i:s')
                            : null;

                        $recordData = [
                            'date' => $validRow['date'],
                            'day' => $validRow['day'],
                            'class' => $validRow['class'],
                            'school' => $validRow['school'],
                            'time_jst' => $validRow['time_jst'],
                            'time_pht' => $timePHT,
                            'number_required' => $validRow['number_required'],
                        ];

                        try {
                            $newRecord = DailyData::create($recordData);
                            $imported++;
                            Log::info("✅ Successfully created new record {$newRecord->id} for row {$validRow['row_number']}");
                        } catch (\Illuminate\Database\QueryException $e) {
                            if ($e->getCode() == 23000) {
                                // Duplicate row, skip gracefully
                                $skipped++;
                                $skippedRows[] = "{$validRow['date']} - {$validRow['school']} - {$validRow['class']} ({$validRow['time_jst']})";
                                Log::info("⚠️ Duplicate skipped for row {$validRow['row_number']}", $recordData);
                                continue;
                            } else {
                                throw $e;
                            }
                        }
                    }

                } catch (\Exception $e) {
                    Log::error("❌ Failed to process valid row {$validRow['row_number']}", [
                        'error' => $e->getMessage(),
                        'line' => $e->getLine(),
                        'file' => $e->getFile(),
                        'data' => $validRow,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            Log::info('=== UPLOAD COMPLETE ===', [
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
                'errors' => $errorRows
            ]);

            // Build response message
            $messages = [];
            if ($imported > 0) $messages[] = "imported {$imported} new records";
            if ($updated > 0) $messages[] = "updated {$updated} existing records";
            if ($skipped > 0) $messages[] = "skipped {$skipped} duplicates";
            if ($errorRows > 0) $messages[] = "skipped {$errorRows} invalid rows";

            $message = count($messages) > 0 ? "Successfully " . implode(', ', $messages) : "No records were processed";

            // Limit skipped rows listed in message (avoid too long response)
            $skippedPreview = !empty($skippedRows) 
                ? array_slice($skippedRows, 0, 5) 
                : [];

            if (!empty($skippedPreview)) {
                $message .= ". Some duplicates were skipped: " . implode('; ', $skippedPreview);
                if (count($skippedRows) > 5) {
                    $message .= " ... and " . (count($skippedRows) - 5) . " more.";
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'stats' => [
                    'imported' => $imported,
                    'updated' => $updated,
                    'skipped' => $skipped,
                    'errors' => $errorRows
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('=== FILE UPLOAD FAILED ===', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'filename' => $file ? $file->getClientOriginalName() : 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 400);
        }
    }

    private function parseDate($value, $rowNumber = null)
    {
        Log::info("Parsing date for row {$rowNumber}", ['input' => $value, 'type' => gettype($value)]);
        
        if (empty($value)) {
            return null;
        }
        
        try {
            if (is_numeric($value) && $value > 1) {
                $result = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->format('Y-m-d');
                Log::info("Parsed Excel date number", ['input' => $value, 'output' => $result]);
                return $result;
            }
            
            if (is_string($value)) {
                if (preg_match('/^\d{1,2}\/\d{1,2}$/', $value)) {
                    $currentYear = date('Y');
                    $value = $currentYear . '/' . $value;
                }
                $result = Carbon::parse($value)->format('Y-m-d');
                Log::info("Parsed string date", ['input' => $value, 'output' => $result]);
                return $result;
            }
            
            $result = Carbon::parse($value)->format('Y-m-d');
            Log::info("Parsed generic date", ['input' => $value, 'output' => $result]);
            return $result;
        } catch (\Exception $e) {
            Log::error("Date parsing failed", ['row' => $rowNumber, 'input' => $value, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function parseTime($value, $rowNumber = null)
    {
        if (empty($value)) return null;
        
        try {
            if (is_numeric($value) && $value < 1) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('H:i:s');
            }
            
            if (is_string($value) && preg_match('/^\d{1,2}:\d{2}$/', $value)) {
                return Carbon::createFromFormat('H:i', $value)->format('H:i:s');
            }
            
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            Log::info("Time parsing failed, returning original", ['input' => $value, 'error' => $e->getMessage()]);
            return is_string($value) ? $value : null;
        }
    }

    private function parseNumber($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        
        if (is_string($value)) {
            preg_match('/\d+/', $value, $matches);
            return isset($matches[0]) ? (int) $matches[0] : 0;
        }
        
        return 0;
    }

    private function sanitizeString($value)
    {
        if (empty($value)) return null;
        return trim((string) $value);
    }
}
