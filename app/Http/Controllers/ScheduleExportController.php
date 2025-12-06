<?php

namespace App\Http\Controllers;

use App\Services\ScheduleExportService;
use App\Models\DailyData;
use App\Models\ScheduleHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ScheduleExportController extends Controller
{
    protected $exportService;

    public function __construct(ScheduleExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Export tentative schedule to Excel
     */
    public function exportTentative(Request $request)
    {
        try {
            $date = $request->input('date');
            
            if (!$date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Date is required for export'
                ], 400);
            }

            return $this->exportService->exportTentativeSchedule($date);
            
        } catch (\Exception $e) {
            Log::error('Error exporting tentative schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export tentative schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export final schedule to Excel
     */
    public function exportFinal(Request $request)
    {
        try {
            $date = $request->input('date');
            
            if (!$date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Date is required for export'
                ], 400);
            }

            return $this->exportService->exportFinalSchedule($date);
            
        } catch (\Exception $e) {
            Log::error('Error exporting final schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export final schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export selected schedules to Excel
     */
    public function exportSelected(Request $request)
    {
        try {
            $dates = $request->input('dates', []);
            
            if (empty($dates)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedules selected for export'
                ], 400);
            }

            // Use new table structure
            $schedules = \App\Models\ScheduleDailyData::leftJoin('assigned_daily_data', 'schedules_daily_data.id', '=', 'assigned_daily_data.schedule_daily_data_id')
                ->whereIn('schedules_daily_data.date', $dates)
                ->where('assigned_daily_data.class_status', 'fully_assigned')
                ->select(
                    'schedules_daily_data.*',
                    'assigned_daily_data.main_tutor',
                    'assigned_daily_data.backup_tutor',
                    'assigned_daily_data.finalized_at'
                )
                ->orderBy('schedules_daily_data.date')
                ->orderBy('schedules_daily_data.time')
                ->get();

            if ($schedules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedules found for export'
                ], 404);
            }

            return $this->exportService->exportSelectedSchedules($schedules);
            
        } catch (\Exception $e) {
            Log::error('Error exporting selected schedules: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export selected schedules: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export schedule history to Excel
     */
    public function exportHistory(Request $request)
    {
        try {
            $query = ScheduleHistory::with(['dailyData', 'performer'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $history = $query->get();

            return $this->exportHistoryToExcel($history);
            
        } catch (\Exception $e) {
            Log::error('Error exporting history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export selected schedules to Excel
     */
    private function exportSelectedSchedules($schedules)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Title
        $sheet->setCellValue('A1', 'SELECTED SCHEDULES EXPORT');
        $sheet->setCellValue('A2', 'Generated: ' . now()->format('Y-m-d H:i:s'));
        
        // Headers
        $headers = [
            'A4' => 'Date',
            'B4' => 'Time',
            'C4' => 'Class',
            'D4' => 'School',
            'E4' => 'Main Tutor(s)',
            'F4' => 'Backup Tutor(s)',
            'G4' => 'Supervisor',
            'H4' => 'Status'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        
        // Data
        $row = 5;
        foreach ($schedules as $schedule) {
            $mainTutors = [];
            $backupTutors = [];
            
            foreach ($schedule->tutorAssignments as $assignment) {
                $tutor = $assignment->tutor;
                if (!$tutor) continue;
                
                $tutorName = $tutor->full_name ?? 'Unknown Tutor';
                if ($assignment->is_backup) {
                    $backupTutors[] = $tutorName;
                } else {
                    $mainTutors[] = $tutorName;
                }
            }
            
            try {
                \Log::info('Export: Setting cell values for row ' . $row);
                $sheet->setCellValue('A' . $row, $schedule->date ?? 'N/A');
                $timeValue = 'N/A';
                if ($schedule->time_jst) {
                    try {
                        $startTime = \Carbon\Carbon::parse($schedule->time_jst);
                        $duration = $schedule->duration ?? 0;
                        $endTime = $startTime->copy()->addMinutes($duration);
                        $timeValue = $startTime->format('H:i') . ' - ' . $endTime->format('H:i');
                    } catch (\Exception $e) {
                        $timeValue = 'N/A';
                    }
                }
                $sheet->setCellValue('B' . $row, $timeValue);
                $sheet->setCellValue('C' . $row, $schedule->class ?? 'N/A');
                $sheet->setCellValue('D' . $row, $schedule->school ?? 'N/A');
                $sheet->setCellValue('E' . $row, !empty($mainTutors) ? implode(', ', $mainTutors) : 'N/A');
                $sheet->setCellValue('F' . $row, !empty($backupTutors) ? implode(', ', $backupTutors) : 'N/A');
                $sheet->setCellValue('G' . $row, $schedule->assigned_supervisor ?? 'Unassigned');
                $statusText = ucfirst($schedule->class_status ?? 'unknown');
                if ($schedule->class_status === 'cancelled' && $schedule->cancellation_reason) {
                    $statusText .= ' - ' . $schedule->cancellation_reason;
                }
                $sheet->setCellValue('H' . $row, $statusText);
            } catch (\Exception $e) {
                \Log::error('Export: Error setting cell values for row ' . $row . ': ' . $e->getMessage());
                \Log::error('Export: Schedule data - Date: ' . $schedule->date . ', Class: ' . $schedule->class . ', School: ' . $schedule->school);
                throw $e;
            }
            
            $row++;
        }
        
        // Apply styling
        $lastRow = $row - 1;
        if ($lastRow > 0) {
            \Log::info('Export: Applying styling for range A1:H' . $lastRow);
            $this->applyExcelStyling($sheet, 'A1:H' . $lastRow);
        }
        
        // Generate filename and download
        $filename = 'Selected_Schedules_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Export history to Excel
     */
    private function exportHistoryToExcel($history)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Title
        $sheet->setCellValue('A1', 'SCHEDULE HISTORY EXPORT');
        $sheet->setCellValue('A2', 'Generated: ' . now()->format('Y-m-d H:i:s'));
        
        // Headers
        $headers = [
            'A4' => 'Date/Time',
            'B4' => 'Action',
            'C4' => 'Class',
            'D4' => 'School',
            'E4' => 'Performed By',
            'F4' => 'Description',
            'G4' => 'Status'
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        
        // Data
        $row = 5;
        foreach ($history as $record) {
            $sheet->setCellValue('A' . $row, $record->created_at->format('Y-m-d H:i:s'));
            $sheet->setCellValue('B' . $row, ucfirst($record->action));
            $sheet->setCellValue('C' . $row, $record->dailyData->class ?? 'N/A');
            $sheet->setCellValue('D' . $row, $record->dailyData->school ?? 'N/A');
            $sheet->setCellValue('E' . $row, $record->performer->full_name ?? 'System');
            $sheet->setCellValue('F' . $row, $record->description);
            $sheet->setCellValue('G' . $row, ucfirst($record->status));
            
            $row++;
        }
        
        // Apply styling
        $this->applyExcelStyling($sheet, 'A1:G' . ($row - 1));
        
        // Generate filename and download
        $filename = 'Schedule_History_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Apply Excel styling
     */
    private function applyExcelStyling($sheet, $range)
    {
        try {
            \Log::info('Export: Starting styling for range: ' . $range);
            
            // Basic title styling
            $sheet->getStyle('A1')->getFont()->setBold(true);
            $sheet->getStyle('A2')->getFont()->setBold(true);
            
            // Basic header styling
            $sheet->getStyle('A4:H4')->getFont()->setBold(true);
            
            // Set optimized column widths instead of auto-size for better control
            $sheet->getColumnDimension('A')->setWidth(12);  // Date
            $sheet->getColumnDimension('B')->setWidth(15);  // Time
            $sheet->getColumnDimension('C')->setWidth(20);  // Class
            $sheet->getColumnDimension('D')->setWidth(25);  // School
            $sheet->getColumnDimension('E')->setWidth(30);  // Main Tutor(s)
            $sheet->getColumnDimension('F')->setWidth(30);  // Backup Tutor(s)
            $sheet->getColumnDimension('G')->setWidth(20);  // Supervisor
            $sheet->getColumnDimension('H')->setWidth(15);  // Status
            
            \Log::info('Export: Basic styling completed successfully');
        } catch (\Exception $e) {
            \Log::error('Export: Error applying styling: ' . $e->getMessage());
            \Log::error('Export: Range was: ' . $range);
            // Continue without styling if there's an error
        }
    }
}
