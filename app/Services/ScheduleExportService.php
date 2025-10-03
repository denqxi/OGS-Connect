<?php

namespace App\Services;

use App\Models\DailyData;
use App\Models\TutorAssignment;
use App\Models\Supervisor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ScheduleExportService
{
    /**
     * Export tentative schedule to Excel (restored from original)
     */
    public function exportTentativeSchedule($date)
    {
        try {
            $query = \App\Models\DailyData::with(['tutorAssignments.tutor' => function($q) {
                $q->with(['accounts' => function($qa) {
                    $qa->where('account_name', 'GLS')->where('status', 'active')->select(['id', 'tutor_id', 'account_name', 'gls_id', 'username', 'screen_name']);
                }]);
            }])
                ->where(function($q) {
                    $q->where('schedule_status', '!=', 'finalized')
                        ->orWhereNull('schedule_status');
                });

            if ($date) {
                $query->where('date', $date);
            }

            $schedules = $query->orderBy('date')
                ->orderBy('school')
                ->orderBy('time_jst')
                ->get();

            if ($schedules->isEmpty()) {
                throw new \Exception('No tentative schedules found for the specified criteria.');
            }

            // Get current supervisor for logging purposes
            $currentSupervisorId = session('supervisor_id');
            if (!$currentSupervisorId && \Illuminate\Support\Facades\Auth::guard('supervisor')->check()) {
                $currentSupervisorId = \Illuminate\Support\Facades\Auth::guard('supervisor')->user()->supID;
            }
            foreach ($schedules as $class) {
                if (method_exists($class, 'createHistoryRecord')) {
                    $class->createHistoryRecord(
                        'exported',
                        $currentSupervisorId,
                        'Exported Tentative Schedule',
                        null,
                        [
                            'export_type' => 'tentative',
                            'date' => $class->date,
                            'exported_by' => $currentSupervisorId
                        ]
                    );
                }
            }

            $spreadsheet = new Spreadsheet();
            // Group schedules for overview and class sheets
            $groupedSchedules = [];
            $classSheetsData = [];
            foreach ($schedules as $schedule) {
                $date = $schedule->date; // Y-m-d
                $dateFormatted = \Carbon\Carbon::parse($date)->format('F j, Y');
                $time = $schedule->time_jst ?? '';
                $key = $dateFormatted . '|' . $schedule->school . '|' . $schedule->class;
                $sheetKey = $dateFormatted . ' - ' . $schedule->school . ' - ' . $schedule->class;

                // Get the supervisor who finalized this schedule
                $scheduleSupervisorName = $this->getScheduleSupervisorName($schedule);

                // Overview grouping
                $slotKey = ($schedule->time_jst ?? '') . '|' . $schedule->school;
                if (!isset($groupedSchedules[$slotKey])) {
                    $groupedSchedules[$slotKey] = [
                        'schools' => [$schedule->school],
                        'date' => $schedule->date, // Add date for overview header
                        'time' => $schedule->time_jst,
                        'total_slots' => $schedule->number_required ?? 0,
                        'main_tutors' => [],
                        'backup_tutors' => [],
                        'supervisor_name' => $scheduleSupervisorName
                    ];
                } else {
                    if (!in_array($schedule->school, $groupedSchedules[$slotKey]['schools'])) {
                        $groupedSchedules[$slotKey]['schools'][] = $schedule->school;
                    }
                    // Add slots for additional classes at the same time/school
                    $groupedSchedules[$slotKey]['total_slots'] += ($schedule->number_required ?? 0);
                }

                $mainTutors = [];
                $backupTutors = [];
                foreach ($schedule->tutorAssignments as $assignment) {
                    $tutor = $assignment->tutor;
                    if (!$tutor) continue;
                    // Fetch GLS account directly from DB for this tutor
                    $glsAccount = $tutor->accounts()->where('account_name', 'GLS')->first();
                    $glsId = '';
                    if ($glsAccount) {
                        // Use toArray() to extract gls_id, since property/array access fails
                        $glsArr = method_exists($glsAccount, 'toArray') ? $glsAccount->toArray() : [];
                        $glsId = isset($glsArr['gls_id']) ? $glsArr['gls_id'] : '';
                    }
                    $glsUsername = $glsAccount && $glsAccount->username ? $glsAccount->username : '';
                    $glsScreenName = $glsAccount && $glsAccount->screen_name ? $glsAccount->screen_name : '';
                    $glsIdStr = (string)$glsId;
                    
                    $tutorArr = [
                        'glsID' => $glsIdStr,
                        'full_name' => $tutor->full_name,
                        'glsUsername' => $glsUsername,
                        'glsScreenName' => $glsScreenName,
                        'sex' => $tutor->sex,
                        'supervisor' => $scheduleSupervisorName,
                        'is_backup' => $assignment->is_backup,
                        'is_cancelled' => $schedule->class_status === 'cancelled',
                    ];
                    
                    if ($assignment->is_backup) {
                        $backupTutors[] = $tutorArr;
                        $groupedSchedules[$slotKey]['backup_tutors'][] = $tutor->full_name;
                    } else {
                        $mainTutors[] = $tutorArr;
                        $groupedSchedules[$slotKey]['main_tutors'][] = $tutor->full_name;
                    }
                }
                $classSheetsData[$sheetKey] = array_merge($mainTutors, $backupTutors);
            }
            $overviewSheet = $spreadsheet->getActiveSheet();
            $this->createOverviewSheet($overviewSheet, $groupedSchedules);
            $overviewSheet->setTitle('Overview');
            $this->createClassSheets($spreadsheet, $classSheetsData, true, false);
            $writer = new Xlsx($spreadsheet);
            $fileName = 'Tentative_Schedule_' . now()->format('Ymd_His') . '.xlsx';

            return response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting tentative schedule: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export final schedule to Excel (restored from original)
     */
    public function exportFinalSchedule($date)
    {
        try {
            $query = \App\Models\DailyData::with(['tutorAssignments.tutor'])
                ->select('*'); // Explicitly select all fields including cancellation_reason
            // If a specific date is requested, export that date regardless of status;
            // otherwise export all finalized schedules
            if ($date) {
                $query->whereDate('date', $date);
            } else {
                $query->where('schedule_status', 'finalized');
            }
            
            $schedules = $query->orderBy('date')
                ->orderBy('school')
                ->orderBy('time_jst')
                ->get();

            if ($schedules->isEmpty()) {
                throw new \Exception('No finalized schedules found for the specified criteria');
            }

            // Build per-class sheets only (no overview) with cancelled markings
            $spreadsheet = new Spreadsheet();
            $classSheetsData = [];
            foreach ($schedules as $schedule) {
                $date = \Carbon\Carbon::parse($schedule->date)->format('F j, Y');
                $sheetKey = $date . ' - ' . $schedule->school . ' - ' . $schedule->class;

                // Get the supervisor who finalized this schedule
                $scheduleSupervisorName = $this->getScheduleSupervisorName($schedule);

                $mainTutors = [];
                $backupTutors = [];
                foreach ($schedule->tutorAssignments as $assignment) {
                    $tutor = $assignment->tutor;
                    if (!$tutor) { continue; }

                    $glsAccount = $tutor->accounts()->where('account_name', 'GLS')->first();
                    $glsArr = $glsAccount && method_exists($glsAccount, 'toArray') ? $glsAccount->toArray() : [];
                    $glsId = isset($glsArr['gls_id']) ? (string)$glsArr['gls_id'] : '';
                    $glsUsername = $glsAccount && $glsAccount->username ? $glsAccount->username : '';
                    $glsScreenName = $glsAccount && $glsAccount->screen_name ? $glsAccount->screen_name : '';

                    $tutorArr = [
                        'glsID' => $glsId,
                        'full_name' => $tutor->full_name,
                        'glsUsername' => $glsUsername,
                        'glsScreenName' => $glsScreenName,
                        'sex' => $tutor->sex,
                        'supervisor' => $scheduleSupervisorName,
                        'is_backup' => $assignment->is_backup,
                        'is_cancelled' => $schedule->class_status === 'cancelled',
                    ];

                    if ($assignment->is_backup) {
                        $backupTutors[] = $tutorArr;
                    } else {
                        $mainTutors[] = $tutorArr;
                    }
                }

                // Add cancellation reason for all cancelled classes
                if ($schedule->class_status === 'cancelled') {
                    $cancellationReason = $schedule->cancellation_reason ? 
                        'CLASS CANCELLED - ' . $schedule->cancellation_reason : 
                        'CLASS CANCELLED';
                    
                    // Add cancellation reason as the first entry
                    array_unshift($mainTutors, [
                        'glsID' => '',
                        'full_name' => $cancellationReason,
                        'glsUsername' => '',
                        'glsScreenName' => '',
                        'sex' => '',
                        'supervisor' => $scheduleSupervisorName,
                        'is_backup' => false,
                        'is_cancelled' => true,
                    ]);
                }

                $classSheetsData[$sheetKey] = array_merge($mainTutors, $backupTutors);
            }

            $this->createClassSheets($spreadsheet, $classSheetsData, false, true);
            // Remove the default empty sheet
            if ($spreadsheet->getSheetCount() > 1) {
                $spreadsheet->removeSheetByIndex(0);
                $spreadsheet->setActiveSheetIndex(0);
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'finalized_schedule_' . date('Y-m-d_H-i-s') . '.xlsx';
            return response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error exporting final schedule: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get schedules for a specific date with all related data
     */
    private function getSchedulesForDate($date)
    {
        return DailyData::with([
            'tutorAssignments.tutor.accounts' => function($query) {
                $query->where('account_name', 'GLS')->active();
            }
        ])
        ->where('date', $date)
        ->orderBy('time_jst')
        ->get();
    }

    /**
     * Format schedule data for Excel export
     */
    private function formatScheduleData($schedules, $type)
    {
        $formattedData = [];
        
        foreach ($schedules as $schedule) {
            $scheduleSupervisorName = $this->getSupervisorName($schedule->assigned_supervisor);
            
            $mainTutors = [];
            $backupTutors = [];
            
            foreach ($schedule->tutorAssignments as $assignment) {
                $tutor = $assignment->tutor;
                if (!$tutor) continue;

                $glsAccount = $tutor->accounts->firstWhere('account_name', 'GLS');
                $glsId = $glsAccount ? (string)$glsAccount->gls_id : '';
                $glsUsername = $glsAccount ? $glsAccount->username : '';
                $glsScreenName = $glsAccount ? $glsAccount->screen_name : '';

                $tutorData = [
                    'glsID' => $glsId,
                    'full_name' => $tutor->full_name,
                    'glsUsername' => $glsUsername,
                    'glsScreenName' => $glsScreenName,
                    'sex' => $tutor->sex,
                    'supervisor' => $scheduleSupervisorName,
                    'is_backup' => $assignment->is_backup,
                    'is_cancelled' => $schedule->class_status === 'cancelled',
                ];

                if ($assignment->is_backup) {
                    $backupTutors[] = $tutorData;
                } else {
                    $mainTutors[] = $tutorData;
                }
            }

            $formattedData[] = [
                'schedule' => $schedule,
                'main_tutors' => $mainTutors,
                'backup_tutors' => $backupTutors,
                'supervisor' => $scheduleSupervisorName
            ];
        }

        return $formattedData;
    }

    /**
     * Generate Excel file from formatted data
     */
    private function generateExcelFile($formattedData, $type, $date)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $this->setupExcelHeaders($sheet, $type, $date);
        $this->populateExcelData($sheet, $formattedData);
        $this->applyExcelStyling($sheet);
        
        $filename = $this->generateFilename($type, $date);
        
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Setup Excel headers and title
     */
    private function setupExcelHeaders($sheet, $type, $date)
    {
        $title = $type === 'final' ? 'FINAL SCHEDULE' : 'TENTATIVE SCHEDULE';
        $sheet->setCellValue('A1', $title);
        $sheet->setCellValue('A2', 'Date: ' . $date);
        
        // Headers
        $headers = [
            'A4' => 'Time',
            'B4' => 'Class',
            'C4' => 'School',
            'D4' => 'Main Tutor(s)',
            'E4' => 'Backup Tutor(s)',
            'F4' => 'Supervisor',
            'G4' => 'Status'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
    }

    /**
     * Populate Excel with schedule data
     */
    private function populateExcelData($sheet, $formattedData)
    {
        $row = 5;
        
        foreach ($formattedData as $data) {
            $schedule = $data['schedule'];
            $mainTutors = $data['main_tutors'];
            $backupTutors = $data['backup_tutors'];
            
            $sheet->setCellValue('A' . $row, $schedule->time_jst ? \Carbon\Carbon::parse($schedule->time_jst)->format('H:i') . ' - ' . \Carbon\Carbon::parse($schedule->time_jst)->addMinutes($schedule->duration)->format('H:i') : 'N/A');
            $sheet->setCellValue('B' . $row, $schedule->class);
            $sheet->setCellValue('C' . $row, $schedule->school);
            $sheet->setCellValue('D' . $row, $this->formatTutorNames($mainTutors));
            $sheet->setCellValue('E' . $row, $this->formatTutorNames($backupTutors));
            $sheet->setCellValue('F' . $row, $data['supervisor']);
            $sheet->setCellValue('G' . $row, ucfirst($schedule->class_status));
            
            $row++;
        }
    }

    /**
     * Apply Excel styling
     */
    private function applyExcelStyling($sheet)
    {
        // Title styling
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true);
        
        // Header styling
        $headerRange = 'A4:G4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');
        
        // Auto-size columns
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Add borders
        $sheet->getStyle('A4:G' . ($sheet->getHighestRow()))
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
    }

    /**
     * Export selected schedules with overview and analytics (restored from original)
     */
    public function exportSelectedSchedules($schedules)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $classSheetsData = [];
            $groupedSchedules = [];
            
            foreach ($schedules as $schedule) {
                $date = \Carbon\Carbon::parse($schedule->date)->format('F j, Y');
                $sheetKey = $date . ' - ' . $schedule->school . ' - ' . $schedule->class;

                // Get the supervisor who finalized this schedule
                $scheduleSupervisorName = $this->getScheduleSupervisorName($schedule);

                // Grouped overview structure per time and school
                $slotKey = ($schedule->time_jst ?? '') . '|' . $schedule->school;
                if (!isset($groupedSchedules[$slotKey])) {
                    $groupedSchedules[$slotKey] = [
                        'schools' => [$schedule->school],
                        'date' => $schedule->date,
                        'time' => $schedule->time_jst,
                        'total_slots' => $schedule->number_required ?? 0,
                        'main_tutors' => [],
                        'backup_tutors' => []
                    ];
                } else {
                    if (!in_array($schedule->school, $groupedSchedules[$slotKey]['schools'])) {
                        $groupedSchedules[$slotKey]['schools'][] = $schedule->school;
                    }
                    // Add slots for additional classes at the same time/school
                    $groupedSchedules[$slotKey]['total_slots'] += ($schedule->number_required ?? 0);
                }

                $mainTutors = [];
                $backupTutors = [];
                foreach ($schedule->tutorAssignments as $assignment) {
                    $tutor = $assignment->tutor;
                    if (!$tutor) { continue; }

                    $glsAccount = $tutor->accounts()->where('account_name', 'GLS')->first();
                    $glsArr = $glsAccount && method_exists($glsAccount, 'toArray') ? $glsAccount->toArray() : [];
                    $glsId = isset($glsArr['gls_id']) ? (string)$glsArr['gls_id'] : '';
                    $glsUsername = $glsAccount && $glsAccount->username ? $glsAccount->username : '';
                    $glsScreenName = $glsAccount && $glsAccount->screen_name ? $glsAccount->screen_name : '';

                    $tutorArr = [
                        'glsID' => $glsId,
                        'full_name' => $tutor->full_name,
                        'glsUsername' => $glsUsername,
                        'glsScreenName' => $glsScreenName,
                        'sex' => $tutor->sex,
                        'supervisor' => $scheduleSupervisorName,
                        'is_backup' => $assignment->is_backup,
                        'is_cancelled' => $schedule->class_status === 'cancelled',
                    ];

                    if ($assignment->is_backup) {
                        $backupTutors[] = $tutorArr;
                        $groupedSchedules[$slotKey]['backup_tutors'][] = $tutor->full_name;
                    } else {
                        $mainTutors[] = $tutorArr;
                        $groupedSchedules[$slotKey]['main_tutors'][] = $tutor->full_name;
                    }
                }

                // Add cancellation reason for all cancelled classes
                if ($schedule->class_status === 'cancelled') {
                    $cancellationReason = $schedule->cancellation_reason ? 
                        'CLASS CANCELLED - ' . $schedule->cancellation_reason : 
                        'CLASS CANCELLED';
                    
                    // Add cancellation reason as the first entry
                    array_unshift($mainTutors, [
                        'glsID' => '',
                        'full_name' => $cancellationReason,
                        'glsUsername' => '',
                        'glsScreenName' => '',
                        'sex' => '',
                        'supervisor' => $scheduleSupervisorName,
                        'is_backup' => false,
                        'is_cancelled' => true,
                    ]);
                }

                $classSheetsData[$sheetKey] = array_merge($mainTutors, $backupTutors);
            }

            // Create overview sheet first with visualizations
            $overviewSheet = $spreadsheet->getActiveSheet();
            $this->createSelectedScheduleOverviewSheet($overviewSheet, $groupedSchedules, $schedules, null);
            $overviewSheet->setTitle('Overview');
            
            // Create per-class sheets
            $this->createClassSheets($spreadsheet, $classSheetsData, false, true);
            
            $writer = new Xlsx($spreadsheet);
            $fileName = 'Selected_Schedules_' . now()->format('Ymd_His') . '.xlsx';
            
            return response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting selected schedules: ' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Get the supervisor name who finalized a schedule from schedule history
     */
    private function getScheduleSupervisorName($schedule)
    {
        Log::debug('getScheduleSupervisorName called', [
            'schedule_id' => $schedule->id,
            'schedule_class' => $schedule->class,
            'finalized_by' => $schedule->finalized_by,
            'schedule_status' => $schedule->schedule_status
        ]);
        
        // For tentative schedules, look for assignment actions first
        if ($schedule->schedule_status !== 'finalized') {
            // Look for 'assigned' actions first (who actually worked on the schedule)
            $assignmentRecord = \App\Models\ScheduleHistory::where('class_id', $schedule->id)
                ->where('action', 'assigned')
                ->orderBy('created_at', 'desc')
                ->first();
                
            Log::debug('Assignment history record found', [
                'assignment_record' => $assignmentRecord ? $assignmentRecord->toArray() : null
            ]);
                
            if ($assignmentRecord && $assignmentRecord->performed_by) {
                $supervisor = \App\Models\Supervisor::where('supID', $assignmentRecord->performed_by)->first();
                Log::debug('Supervisor found from assignment action', [
                    'performed_by' => $assignmentRecord->performed_by,
                    'supervisor' => $supervisor ? $supervisor->toArray() : null
                ]);
                if ($supervisor) {
                    return $supervisor->full_name;
                }
            }
        }
        
        // Look for the 'finalized' action in schedule history for this class
        $historyRecord = \App\Models\ScheduleHistory::where('class_id', $schedule->id)
            ->where('action', 'finalized')
            ->orderBy('created_at', 'desc')
            ->first();
            
        Log::debug('Finalized history record found', [
            'history_record' => $historyRecord ? $historyRecord->toArray() : null
        ]);
            
        if ($historyRecord && $historyRecord->performed_by) {
            $supervisor = \App\Models\Supervisor::where('supID', $historyRecord->performed_by)->first();
            Log::debug('Supervisor found from finalized action', [
                'performed_by' => $historyRecord->performed_by,
                'supervisor' => $supervisor ? $supervisor->toArray() : null
            ]);
            if ($supervisor) {
                return $supervisor->full_name;
            }
        }
        
        // If no finalized action found, look for any action that might indicate who created/finalized the schedule
        $anyHistoryRecord = \App\Models\ScheduleHistory::where('class_id', $schedule->id)
            ->whereIn('action', ['finalized', 'assigned', 'created', 'updated', 'exported'])
            ->orderBy('created_at', 'desc')
            ->first();
            
        Log::debug('Any history record found', [
            'history_record' => $anyHistoryRecord ? $anyHistoryRecord->toArray() : null
        ]);
            
        if ($anyHistoryRecord && $anyHistoryRecord->performed_by) {
            $supervisor = \App\Models\Supervisor::where('supID', $anyHistoryRecord->performed_by)->first();
            Log::debug('Supervisor found from any action', [
                'performed_by' => $anyHistoryRecord->performed_by,
                'supervisor' => $supervisor ? $supervisor->toArray() : null
            ]);
            if ($supervisor) {
                return $supervisor->full_name;
            }
        }
        
        // Fallback to finalized_by field if no history record found
        if ($schedule->finalized_by) {
            $supervisor = \App\Models\Supervisor::where('supID', $schedule->finalized_by)->first();
            Log::debug('Supervisor found from finalized_by field', [
                'finalized_by' => $schedule->finalized_by,
                'supervisor' => $supervisor ? $supervisor->toArray() : null
            ]);
            if ($supervisor) {
                return $supervisor->full_name;
            }
        }
        
        // Final fallback to current session supervisor
        $currentSupervisorId = session('supervisor_id');
        if (!$currentSupervisorId && \Illuminate\Support\Facades\Auth::guard('supervisor')->check()) {
            $currentSupervisorId = \Illuminate\Support\Facades\Auth::guard('supervisor')->user()->supID;
        }
        
        if ($currentSupervisorId) {
            $supervisor = \App\Models\Supervisor::where('supID', $currentSupervisorId)->first();
            Log::debug('Using current session supervisor as final fallback', [
                'current_supervisor_id' => $currentSupervisorId,
                'supervisor' => $supervisor ? $supervisor->toArray() : null
            ]);
            if ($supervisor) {
                return $supervisor->full_name;
            }
        }
        
        Log::debug('No supervisor found for schedule', [
            'schedule_id' => $schedule->id
        ]);
        
        return null;
    }

    /**
     * Create the overview sheet with the existing format (from original)
     */
    private function createOverviewSheet($sheet, $groupedSchedules)
    {
        // Matrix layout: each column is a slot (school/time), with headers, main tutors, BACK UP row, backup tutors
        $columnIndex = 1;
        $maxMain = 0;
        $maxBackup = 0;
        foreach ($groupedSchedules as $slotKey => $data) {
            $mainCount = count($data['main_tutors'] ?? []);
            $backupCount = count($data['backup_tutors'] ?? []);
            if ($mainCount > $maxMain) $maxMain = $mainCount;
            if ($backupCount > $maxBackup) $maxBackup = $backupCount;
        }
        $mainRows = $maxMain;
        $backupRows = $maxBackup;
        $totalRows = 3 + $mainRows + 1 + 1 + $backupRows; // 3 header rows, main tutors, gap, BACK UP, backup tutors

        foreach ($groupedSchedules as $slotKey => $data) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
            // Row 1: School name (FFFACD)
            $sheet->setCellValue($col.'1', $data['schools'][0] ?? '');
            $sheet->getStyle($col.'1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFACD']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
            // Row 2: Date (Time) (ADD8E6)
            // Format: Sep 5 (8:05am) -- PH time, not JST
            // Always show 'M j (g:ia)' using date and time from $data, fallback to blank if either missing
            $dateStr = '';
            if (!empty($data['date']) && !empty($data['time'])) {
                try {
                    $dateObj = \Carbon\Carbon::parse($data['date']);
                    $jstTime = $data['time'];
                    $timeObj = null;
                    try {
                        $timeObj = \Carbon\Carbon::parse($jstTime);
                    } catch (\Exception $e) {
                        try {
                            $timeObj = \Carbon\Carbon::createFromFormat('H:i:s', $jstTime);
                        } catch (\Exception $e2) {
                            try {
                                $timeObj = \Carbon\Carbon::createFromFormat('H:i', $jstTime);
                            } catch (\Exception $e3) {
                                $timeObj = null;
                            }
                        }
                    }
                    if ($timeObj) {
                        $phTimeObj = $timeObj->copy()->subHour();
                        $dateStr = $dateObj->format('M j') . ' (' . ltrim($phTimeObj->format('g:ia'), '0') . ')';
                    } else {
                        $dateStr = '';
                    }
                } catch (\Exception $e) {
                    $dateStr = '';
                }
            } else {
                $dateStr = '';
            }
            $sheet->setCellValue($col.'2', $dateStr);
            $sheet->getStyle($col.'2')->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'ADD8E6']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
            // Row 3: Slot count (E6F3FF)
            $sheet->setCellValue($col.'3', '(' . ($data['total_slots'] ?? 0) . ' Slots)');
            $sheet->getStyle($col.'3')->applyFromArray([
                'font' => ['italic' => true, 'size' => 11],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'E6F3FF']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);

            // Main tutors (rows 4 to 4+mainRows-1)
            $mainTutors = $data['main_tutors'] ?? [];
            for ($i = 0; $i < $mainRows; $i++) {
                $row = 4 + $i;
                $sheet->setCellValue($col.$row, $mainTutors[$i] ?? '');
                $sheet->getStyle($col.$row)->applyFromArray([
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
                ]);
            }

            // 2-row gap between main tutors and BACK UP (no border)
            $gapRow1 = 4 + $mainRows;
            $gapRow2 = $gapRow1 + 1;
            $sheet->setCellValue($col.$gapRow1, '');
            $sheet->setCellValue($col.$gapRow2, '');
            $sheet->getStyle($col.$gapRow1)->applyFromArray([
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE]]
            ]);
            $sheet->getStyle($col.$gapRow2)->applyFromArray([
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE]]
            ]);

            // BACK UP row (row after gap)
            $backupHeaderRow = $gapRow2 + 1;
            $sheet->setCellValue($col.$backupHeaderRow, 'BACK UP');
            $sheet->getStyle($col.$backupHeaderRow)->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFE599']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]   
            ]);

            // Backup tutors (rows after BACK UP)
            $backupTutors = $data['backup_tutors'] ?? [];
            for ($i = 0; $i < $backupRows; $i++) {
                $row = $backupHeaderRow + 1 + $i;
                $sheet->setCellValue($col.$row, $backupTutors[$i] ?? '');
                $sheet->getStyle($col.$row)->applyFromArray([
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFE6CC']],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
                ]);
            }

            // Fill any remaining empty cells to ensure all columns have the same number of rows
            for ($row = $backupHeaderRow + 1 + $backupRows; $row <= $totalRows; $row++) {
                $sheet->setCellValue($col.$row, '');
                $sheet->getStyle($col.$row)->applyFromArray([
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
                ]);
            }

            // Set column width
            $sheet->getColumnDimension($col)->setWidth(20);

            $columnIndex++;
        }
        
        // Set additional column widths for better readability
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        
        // Freeze the top 3 header rows
        $sheet->freezePane('A4');
    }

    /**
     * Create enhanced overview sheet for selected schedules with visualizations
     */
    private function createSelectedScheduleOverviewSheet($sheet, $groupedSchedules, $schedules, $supervisorName = null)
    {
        // Calculate summary statistics
        $totalClasses = count($schedules);
        $totalSlots = array_sum(array_column($groupedSchedules, 'total_slots'));
        $totalMainTutors = 0;
        $totalBackupTutors = 0;
        $schools = [];
        $timeSlots = [];
        
        foreach ($groupedSchedules as $data) {
            $totalMainTutors += count($data['main_tutors'] ?? []);
            $totalBackupTutors += count($data['backup_tutors'] ?? []);
            $schools = array_merge($schools, $data['schools'] ?? []);
            if (!empty($data['time'])) {
                $timeSlots[] = $data['time'];
            }
        }
        
        $uniqueSchools = array_unique($schools);
        $uniqueTimeSlots = array_unique($timeSlots);
        $fillRate = $totalSlots > 0 ? round(($totalMainTutors / $totalSlots) * 100, 1) : 0;
        
        // Format time slots as strings for display (convert JST to PHT)
        $formattedTimeSlots = array_map(function($time) {
            return $this->convertJstToPht($time);
        }, $uniqueTimeSlots);
        
        // Set optimized column widths for better layout
        $sheet->getColumnDimension('A')->setWidth(18);  // Labels
        $sheet->getColumnDimension('B')->setWidth(12);  // Values
        $sheet->getColumnDimension('C')->setWidth(18);  // Labels
        $sheet->getColumnDimension('D')->setWidth(12);  // Values
        $sheet->getColumnDimension('E')->setWidth(18);  // Labels
        $sheet->getColumnDimension('F')->setWidth(12);  // Values
        $sheet->getColumnDimension('G')->setWidth(18);  // Labels
        $sheet->getColumnDimension('H')->setWidth(12);  // Values
        $sheet->getColumnDimension('I')->setWidth(18);  // Labels
        $sheet->getColumnDimension('J')->setWidth(12);  // Values
        
        // Create improved summary section
        $this->createImprovedSummarySection($sheet, $totalClasses, $totalSlots, $totalMainTutors, $totalBackupTutors, count($uniqueSchools), $formattedTimeSlots, $fillRate);
        
        // Create improved visualizations section
        $this->createImprovedVisualizationSection($sheet, $groupedSchedules, $uniqueSchools, $uniqueTimeSlots);
        
        // Create improved insights section
        $this->createImprovedInsightsSection($sheet, $groupedSchedules, $totalSlots, $totalMainTutors, $fillRate);
    }

    /**
     * Create improved summary statistics section
     */
    private function createImprovedSummarySection($sheet, $totalClasses, $totalSlots, $totalMainTutors, $totalBackupTutors, $uniqueSchools, $formattedTimeSlots, $fillRate)
    {
        // Title
        $sheet->setCellValue('A1', 'SCHEDULE OVERVIEW & ANALYTICS');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '2A5382']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        
        // Summary statistics in a more organized grid layout
        $stats = [
            ['Total Classes', $totalClasses, 'Total Slots', $totalSlots, 'Fill Rate', $fillRate . '%'],
            ['Main Tutors', $totalMainTutors, 'Backup Tutors', $totalBackupTutors, 'Schools', $uniqueSchools]
        ];
        
        $row = 3;
        foreach ($stats as $statRow) {
            $sheet->setCellValue('A' . $row, $statRow[0] . ':');
            $sheet->setCellValue('B' . $row, $statRow[1]);
            $sheet->setCellValue('C' . $row, $statRow[2] . ':');
            $sheet->setCellValue('D' . $row, $statRow[3]);
            $sheet->setCellValue('E' . $row, $statRow[4] . ':');
            $sheet->setCellValue('F' . $row, $statRow[5]);
            
            // Style the label cells
            $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'E6F3FF']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
            
            // Style the value cells with appropriate colors
            $valueCells = ['B' . $row, 'D' . $row, 'F' . $row];
            foreach ($valueCells as $cell) {
                $fillColor = 'F0F8FF';
                if (strpos($cell, 'F') !== false && $row == 3) { // Fill Rate cell
                    $fillColor = $fillRate >= 90 ? '90EE90' : ($fillRate >= 70 ? 'FFE4B5' : 'FFB6C1');
                }
                
                $sheet->getStyle($cell)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => $fillColor]],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
                ]);
            }
            
            $row++;
        }
        
        // Time slots section - display in a more readable format
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Time Slots (' . count($formattedTimeSlots) . '):');
        $sheet->mergeCells('A' . $row . ':J' . $row);
        $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'F0F8FF']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        
        $row++;
        // Display time slots in a grid format instead of one long string
        $timeSlotsPerRow = 5;
        $timeSlotChunks = array_chunk($formattedTimeSlots, $timeSlotsPerRow);
        
        foreach ($timeSlotChunks as $chunk) {
            $col = 'A';
            foreach ($chunk as $timeSlot) {
                $sheet->setCellValue($col . $row, $timeSlot);
                $sheet->getStyle($col . $row)->applyFromArray([
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'F8F8FF']],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
                ]);
                $col++;
            }
            $row++;
        }
    }

    /**
     * Create improved visualization section with better layout
     */
    private function createImprovedVisualizationSection($sheet, $groupedSchedules, $uniqueSchools, $uniqueTimeSlots)
    {
        // Calculate starting row based on time slots section
        $startRow = 8 + ceil(count($uniqueTimeSlots) / 5) + 2;
        
        // School distribution chart
        $sheet->setCellValue('A' . $startRow, 'SCHOOL DISTRIBUTION');
        $sheet->mergeCells('A' . $startRow . ':E' . $startRow);
        $sheet->getStyle('A' . $startRow . ':E' . $startRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFE4B5']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        
        $row = $startRow + 1;
        foreach ($uniqueSchools as $school) {
            $classCount = 0;
            $tutorCount = 0;
            foreach ($groupedSchedules as $data) {
                if (in_array($school, $data['schools'] ?? [])) {
                    $classCount++;
                    $tutorCount += count($data['main_tutors'] ?? []);
                }
            }
            
            $sheet->setCellValue('A' . $row, $school);
            $sheet->setCellValue('B' . $row, $classCount . ' classes');
            $sheet->setCellValue('C' . $row, $tutorCount . ' tutors');
            
            // Create a visual bar chart using cell background colors and text
            $barLength = min(25, max(1, $tutorCount));
            $bar = str_repeat('█', $barLength);
            $sheet->setCellValue('D' . $row, $bar);
            
            // Add background color to make the bar more visible
            $barColor = $tutorCount > 15 ? '90EE90' : ($tutorCount > 10 ? 'FFE4B5' : 'FFB6C1');
            $sheet->getStyle('D' . $row)->applyFromArray([
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => $barColor]],
                'font' => ['color' => ['rgb' => '000000'], 'size' => 10]
            ]);
            
            // Add percentage
            $totalTutors = array_sum(array_map(function($data) {
                return count($data['main_tutors'] ?? []);
            }, $groupedSchedules));
            $percentage = $totalTutors > 0 ? round(($tutorCount / $totalTutors) * 100, 1) : 0;
            $sheet->setCellValue('E' . $row, $percentage . '%');
            
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
            $row++;
        }
        
        // Time slot distribution - only show if not too many time slots
        if (count($uniqueTimeSlots) <= 15) {
            $sheet->setCellValue('G' . $startRow, 'TIME SLOT DISTRIBUTION');
            $sheet->mergeCells('G' . $startRow . ':J' . $startRow);
            $sheet->getStyle('G' . $startRow . ':J' . $startRow)->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'E6E6FA']],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
            
            $row = $startRow + 1;
            foreach ($uniqueTimeSlots as $time) {
                $classCount = 0;
                $tutorCount = 0;
                $timeStr = $this->convertJstToPht($time);
                foreach ($groupedSchedules as $data) {
                    $dataTimeStr = $this->convertJstToPht($data['time']);
                    if ($dataTimeStr === $timeStr) {
                        $classCount++;
                        $tutorCount += count($data['main_tutors'] ?? []);
                    }
                }
                
                $sheet->setCellValue('G' . $row, $timeStr);
                $sheet->setCellValue('H' . $row, $classCount . ' classes');
                
                // Create a visual bar chart with background colors
                $barLength = min(20, max(1, $tutorCount));
                $bar = str_repeat('█', $barLength);
                $sheet->setCellValue('I' . $row, $bar);
                
                // Add background color to make the bar more visible
                $barColor = $tutorCount > 8 ? '90EE90' : ($tutorCount > 5 ? 'FFE4B5' : 'FFB6C1');
                $sheet->getStyle('I' . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => $barColor]],
                    'font' => ['color' => ['rgb' => '000000'], 'size' => 10]
                ]);
                
                // Add percentage
                $percentage = $totalTutors > 0 ? round(($tutorCount / $totalTutors) * 100, 1) : 0;
                $sheet->setCellValue('J' . $row, $percentage . '%');
                
                $sheet->getStyle('G' . $row . ':J' . $row)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
                ]);
                $row++;
            }
        }
    }

    /**
     * Create improved insights and recommendations section
     */
    private function createImprovedInsightsSection($sheet, $groupedSchedules, $totalSlots, $totalMainTutors, $fillRate)
    {
        // Calculate starting row dynamically
        $startRow = 8 + ceil(count($groupedSchedules) / 5) + 2 + count($groupedSchedules) + 5;
        
        // Insights header
        $sheet->setCellValue('A' . $startRow, 'INSIGHTS & RECOMMENDATIONS');
        $sheet->mergeCells('A' . $startRow . ':J' . $startRow);
        $sheet->getStyle('A' . $startRow . ':J' . $startRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'DDA0DD']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        
        $row = $startRow + 1;
        $insights = [];
        
        // Generate insights based on data
        if ($fillRate >= 100) {
            $insights[] = "✅ Excellent! All slots are fully assigned.";
        } elseif ($fillRate >= 90) {
            $insights[] = "✅ Very good fill rate. Only " . ($totalSlots - $totalMainTutors) . " slots remaining.";
        } elseif ($fillRate >= 70) {
            $insights[] = "⚠️ Moderate fill rate. " . ($totalSlots - $totalMainTutors) . " slots still need assignment.";
        } else {
            $insights[] = "❌ Low fill rate. " . ($totalSlots - $totalMainTutors) . " slots need urgent attention.";
        }
        
        // Check for backup tutor coverage
        $totalBackupTutors = 0;
        foreach ($groupedSchedules as $data) {
            $totalBackupTutors += count($data['backup_tutors'] ?? []);
        }
        
        if ($totalBackupTutors > 0) {
            $insights[] = "🔄 " . $totalBackupTutors . " backup tutors available for coverage.";
        } else {
            $insights[] = "⚠️ No backup tutors assigned. Consider adding backup coverage.";
        }
        
        // Check for time distribution
        $timeSlotCounts = [];
        foreach ($groupedSchedules as $data) {
            if (!empty($data['time'])) {
                $timeKey = is_object($data['time']) ? $data['time']->format('H:i:s') : (string)$data['time'];
                $timeSlotCounts[$timeKey] = ($timeSlotCounts[$timeKey] ?? 0) + 1;
            }
        }
        
        if (count($timeSlotCounts) > 1) {
            $maxTime = array_keys($timeSlotCounts, max($timeSlotCounts))[0];
            $formattedMaxTime = $this->convertJstToPht($maxTime);
            $insights[] = "📊 Peak time slot: " . $formattedMaxTime . " (" . max($timeSlotCounts) . " classes)";
        }
        
        // Add school distribution insights
        $schoolCounts = [];
        foreach ($groupedSchedules as $data) {
            foreach ($data['schools'] ?? [] as $school) {
                $schoolCounts[$school] = ($schoolCounts[$school] ?? 0) + 1;
            }
        }
        
        if (count($schoolCounts) > 1) {
            $maxSchool = array_keys($schoolCounts, max($schoolCounts))[0];
            $insights[] = "🏫 Most active school: " . $maxSchool . " (" . max($schoolCounts) . " time slots)";
        }
        
        // Display insights in a more organized format
        $insightsPerRow = 2;
        $insightChunks = array_chunk($insights, $insightsPerRow);
        
        foreach ($insightChunks as $chunk) {
            $col = 'A';
            foreach ($chunk as $insight) {
                $sheet->setCellValue($col . $row, $insight);
                $sheet->mergeCells($col . $row . ':' . chr(ord($col) + 4) . $row);
                
                $sheet->getStyle($col . $row . ':' . chr(ord($col) + 4) . $row)->applyFromArray([
                    'font' => ['size' => 10],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP, 'wrapText' => true],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'F8F8FF']],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
                ]);
                
                $col = 'F'; // Move to second column for next insight
            }
            $row++;
        }
    }

    /**
     * Convert JST time to PHT time
     * JST is UTC+9, PHT is UTC+8, so we subtract 1 hour
     */
    private function convertJstToPht($time)
    {
        if (empty($time)) {
            return '';
        }

        try {
            $timeStr = (string)$time;
            
            // Try different time formats (including 12-hour format with AM/PM)
            $formats = ['H:i:s', 'H:i', 'g:i A', 'g:i:s A', 'g:iA', 'g:i:sA'];
            $timeObj = null;
            
            foreach ($formats as $format) {
                try {
                    $timeObj = \Carbon\Carbon::createFromFormat($format, $timeStr);
                    break;
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            if (!$timeObj) {
                // If no format works, try to parse as a general time
                $timeObj = \Carbon\Carbon::parse($timeStr);
            }

            // Convert JST to PHT (subtract 1 hour)
            $phtTime = $timeObj->subHour();
            $result = $phtTime->format('H:i');
            
            return $result;
        } catch (\Exception $e) {
            // If conversion fails, return the original time as string
            return (string)$time;
        }
    }

    /**
     * Create individual class sheets with GLS tutor information
     */
    private function createClassSheets($spreadsheet, $classSheetsData, $editable = false, $showCancelledMarkings = true)
    {
        foreach ($classSheetsData as $className => $tutorData) {
            $classSheet = $spreadsheet->createSheet();
            $classSheet->setTitle($this->sanitizeSheetName($className));
            
            $isCancelled = !empty($tutorData) && ($tutorData[0]['is_cancelled'] ?? false) && $showCancelledMarkings;
            
            $classSheet->setCellValue('A1', 'No.');
            $classSheet->setCellValue('B1', 'glsID');
            $classSheet->setCellValue('C1', 'Full Name');
            $classSheet->setCellValue('D1', 'glsUsername');
            $classSheet->setCellValue('E1', 'glsScreenName');
            $classSheet->setCellValue('F1', 'Sex');
            $headerColor = $isCancelled ? 'FF9999' : 'ADD8E6';
            $classSheet->getStyle('A1:F1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $headerColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
            if ($isCancelled) {
                $row = 2;
                
                // Get the cancellation text from the tutor data if available
                $cancellationText = 'CLASS CANCELLED';
                if (count($tutorData) > 0 && strpos($tutorData[0]['full_name'], 'CLASS CANCELLED') === 0) {
                    $cancellationText = $tutorData[0]['full_name'];
                }
                
                
                $classSheet->setCellValue('A' . $row, $cancellationText);
                $classSheet->mergeCells('A' . $row . ':F' . $row); // Fixed: merge only A to F, not G
                $classSheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FF0000']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THICK]]
                ]);
                $row += 2;
                
                if (count($tutorData) === 1 && strpos($tutorData[0]['full_name'], 'CLASS CANCELLED') === 0) {
                    // Set proper column widths for cancelled class display
                    $classSheet->getColumnDimension('A')->setWidth(8);   // No.
                    $classSheet->getColumnDimension('B')->setWidth(12);  // glsID
                    $classSheet->getColumnDimension('C')->setWidth(35);  // Full Name (increased for cancellation reasons)
                    $classSheet->getColumnDimension('D')->setWidth(15);  // glsUsername
                    $classSheet->getColumnDimension('E')->setWidth(15);  // glsScreenName
                    $classSheet->getColumnDimension('F')->setWidth(8);   // Sex
                    continue;
                }
            } else {
                $row = 2;
            }

            $number = 1;
            $mainTutors = array_filter($tutorData, function($t) { return empty($t['is_backup']); });
            $backupTutors = array_filter($tutorData, function($t) { return !empty($t['is_backup']); });

            foreach ($mainTutors as $tutor) {
                $classSheet->setCellValue('A' . $row, $number);
                $classSheet->setCellValue('B' . $row, $tutor['glsID']);
                $classSheet->setCellValue('C' . $row, $tutor['full_name']);
                $classSheet->setCellValue('D' . $row, $tutor['glsUsername']);
                $classSheet->setCellValue('E' . $row, $tutor['glsScreenName']);
                $classSheet->setCellValue('F' . $row, $tutor['sex']);
                $backgroundColor = ($isCancelled && $showCancelledMarkings) ? 'F0F0F0' : 'FFFFFF';
                $fontColor = ($isCancelled && $showCancelledMarkings) ? '808080' : '000000';
                $classSheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $backgroundColor]],
                    'font' => ['color' => ['rgb' => $fontColor]]
                ]);
                $row++;
                $number++;
            }
            
            if (count($backupTutors) > 0) {
                $row++;
                $classSheet->setCellValue('A' . $row, 'BACKUP TUTORS');
                $classSheet->mergeCells('A' . $row . ':F' . $row);
                $backupHeaderColor = ($isCancelled && $showCancelledMarkings) ? 'E6E6E6' : 'FFF2CC';
                $backupFontColor = ($isCancelled && $showCancelledMarkings) ? '606060' : '000000';
                $classSheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $backupFontColor]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $backupHeaderColor]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);
                $row++;
                foreach ($backupTutors as $tutor) {
                    $classSheet->setCellValue('A' . $row, $number);
                    $classSheet->setCellValue('B' . $row, $tutor['glsID']);
                    $classSheet->setCellValue('C' . $row, $tutor['full_name']);
                    $classSheet->setCellValue('D' . $row, $tutor['glsUsername']);
                    $classSheet->setCellValue('E' . $row, $tutor['glsScreenName']);
                    $classSheet->setCellValue('F' . $row, $tutor['sex']);
                    $backupRowColor = ($isCancelled && $showCancelledMarkings) ? 'E6E6E6' : 'FFE6CC';
                    $backupRowFontColor = ($isCancelled && $showCancelledMarkings) ? '606060' : '000000';
                    $classSheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $backupRowColor]],
                        'font' => ['color' => ['rgb' => $backupRowFontColor]]
                    ]);
                    $row++;
                    $number++;
                }
            }

            // Gap after backup tutors
            $row++;

            // Supervisor section below backup tutors
            $classSheet->setCellValue('A' . $row, 'SUPERVISOR');
            $classSheet->mergeCells('A' . $row . ':F' . $row);
            $classSheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E6F3FF']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
            $row++;
            $supervisorName = null;
            if (!empty($mainTutors)) {
                $firstMain = reset($mainTutors);
                if (is_array($firstMain) && isset($firstMain['supervisor'])) {
                    $supervisorName = $firstMain['supervisor'];
                }
            }
            if (!$supervisorName && !empty($backupTutors)) {
                $firstBackup = reset($backupTutors);
                if (is_array($firstBackup) && isset($firstBackup['supervisor'])) {
                    $supervisorName = $firstBackup['supervisor'];
                }
            }
            if (!$supervisorName && !empty($tutorData)) {
                $firstAny = reset($tutorData);
                if (is_array($firstAny) && isset($firstAny['supervisor'])) {
                    $supervisorName = $firstAny['supervisor'];
                }
            }
            $classSheet->setCellValue('A' . $row, $supervisorName ?: '');
            $classSheet->mergeCells('A' . $row . ':F' . $row);
            $classSheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F2F2F2']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);

            // Set optimized column widths for class sheets
            $classSheet->getColumnDimension('A')->setWidth(8);   // No.
            $classSheet->getColumnDimension('B')->setWidth(12);  // glsID
            $classSheet->getColumnDimension('C')->setWidth(35);  // Full Name (increased for cancellation reasons)
            $classSheet->getColumnDimension('D')->setWidth(15);  // glsUsername
            $classSheet->getColumnDimension('E')->setWidth(15);  // glsScreenName
            $classSheet->getColumnDimension('F')->setWidth(8);   // Sex
            
            if (!$editable) {
                $classSheet->getProtection()->setSheet(true);
            }
        }
    }

    /**
     * Sanitize sheet name for Excel compatibility
     */
    private function sanitizeSheetName($name)
    {
        $name = str_replace(['\\', '/', '*', '?', ':', '[', ']'], '-', $name);
        return substr($name, 0, 31);
    }

    /**
     * Format tutor names for display
     */
    private function formatTutorNames($tutors)
    {
        if (empty($tutors)) {
            return 'No tutors assigned';
        }
        
        return implode(', ', array_map(function($tutor) {
            return $tutor['full_name'] . ' (' . $tutor['glsID'] . ')';
        }, $tutors));
    }

    /**
     * Get supervisor name by ID
     */
    private function getSupervisorName($supervisorId)
    {
        if (!$supervisorId) {
            return 'Unassigned';
        }
        
        $supervisor = Supervisor::find($supervisorId);
        return $supervisor ? $supervisor->full_name : 'Unknown';
    }

    /**
     * Generate filename for export
     */
    private function generateFilename($type, $date)
    {
        $typeLabel = $type === 'final' ? 'Final' : 'Tentative';
        return "Schedule_{$typeLabel}_{$date}.xlsx";
    }
}
