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
            $scheduleIds = $request->input('schedule_ids', []);
            
            if (empty($scheduleIds)) {
                return back()->with('error', 'No schedules selected for export');
            }

            // Use new table structure with schedule IDs to avoid duplicates
            $schedules = \App\Models\ScheduleDailyData::leftJoin('assigned_daily_data', 'schedules_daily_data.id', '=', 'assigned_daily_data.schedule_daily_data_id')
                ->leftJoin('tutors as main_tutor_info', 'assigned_daily_data.main_tutor', '=', 'main_tutor_info.tutor_id')
                ->leftJoin('applicants as main_applicant', 'main_tutor_info.applicant_id', '=', 'main_applicant.applicant_id')
                ->leftJoin('tutors as backup_tutor_info', 'assigned_daily_data.backup_tutor', '=', 'backup_tutor_info.tutor_id')
                ->leftJoin('applicants as backup_applicant', 'backup_tutor_info.applicant_id', '=', 'backup_applicant.applicant_id')
                ->whereIn('schedules_daily_data.id', $scheduleIds)
                ->where('assigned_daily_data.class_status', 'fully_assigned')
                ->select(
                    'schedules_daily_data.*',
                    'assigned_daily_data.main_tutor',
                    'assigned_daily_data.backup_tutor',
                    'assigned_daily_data.finalized_at',
                    \DB::raw("CONCAT(COALESCE(main_applicant.first_name, ''), ' ', COALESCE(main_applicant.last_name, '')) as main_tutor_name"),
                    \DB::raw("CONCAT(COALESCE(backup_applicant.first_name, ''), ' ', COALESCE(backup_applicant.last_name, '')) as backup_tutor_name")
                )
                ->orderBy('schedules_daily_data.date')
                ->orderBy('schedules_daily_data.time')
                ->get();

            if ($schedules->isEmpty()) {
                return back()->with('error', 'No schedules found for export');
            }

            return $this->exportService->exportSelectedSchedules($schedules);
            
        } catch (\Exception $e) {
            Log::error('Error exporting selected schedules: ' . $e->getMessage());
            return back()->with('error', 'Failed to export selected schedules. Please try again.');
        }
    }

    /**
     * Export all fully assigned schedules
     */
    public function exportAll(Request $request)
    {
        try {
            // Get all fully assigned schedules
            $schedules = \App\Models\ScheduleDailyData::join('assigned_daily_data', 'schedules_daily_data.id', '=', 'assigned_daily_data.schedule_daily_data_id')
                ->leftJoin('tutors as main_tutor_info', 'assigned_daily_data.main_tutor', '=', 'main_tutor_info.tutor_id')
                ->leftJoin('applicants as main_applicant', 'main_tutor_info.applicant_id', '=', 'main_applicant.applicant_id')
                ->leftJoin('tutors as backup_tutor_info', 'assigned_daily_data.backup_tutor', '=', 'backup_tutor_info.tutor_id')
                ->leftJoin('applicants as backup_applicant', 'backup_tutor_info.applicant_id', '=', 'backup_applicant.applicant_id')
                ->where('assigned_daily_data.class_status', 'fully_assigned')
                ->select(
                    'schedules_daily_data.*',
                    'assigned_daily_data.main_tutor',
                    'assigned_daily_data.backup_tutor',
                    'assigned_daily_data.finalized_at',
                    \DB::raw("CONCAT(COALESCE(main_applicant.first_name, ''), ' ', COALESCE(main_applicant.last_name, '')) as main_tutor_name"),
                    \DB::raw("CONCAT(COALESCE(backup_applicant.first_name, ''), ' ', COALESCE(backup_applicant.last_name, '')) as backup_tutor_name")
                )
                ->orderBy('schedules_daily_data.date')
                ->orderBy('schedules_daily_data.time')
                ->get();

            if ($schedules->isEmpty()) {
                return back()->with('error', 'No schedules found for export');
            }

            return $this->exportService->exportSelectedSchedules($schedules);
            
        } catch (\Exception $e) {
            Log::error('Error exporting all schedules: ' . $e->getMessage());
            return back()->with('error', 'Failed to export schedules. Please try again.');
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
