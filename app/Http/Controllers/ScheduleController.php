<?php

namespace App\Http\Controllers;

use App\Models\DailyData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'class');
        
        if ($tab === 'class') {
            // Build query for daily data with grouping by date and day only
            $query = DailyData::select([
                'date',
                'day',
                DB::raw('GROUP_CONCAT(DISTINCT school ORDER BY school ASC SEPARATOR ", ") as schools'),
                DB::raw('SUM(number_required) as total_required'),
                DB::raw('COUNT(*) as class_count')
            ])
            ->groupBy('date', 'day');
            
            // Apply filters
            if ($request->filled('search')) {
                $query->havingRaw('GROUP_CONCAT(DISTINCT school ORDER BY school ASC SEPARATOR ", ") LIKE ?', ['%' . $request->search . '%']);
            }
            
            if ($request->filled('date')) {
                $query->where('date', $request->date);
            }
            
            if ($request->filled('day')) {
                $query->where('day', $request->day);
            }
            
            // Get paginated results
            $dailyData = $query->orderBy('date', 'desc')
                              ->paginate(10);
            
            // Get available dates for filter dropdown
            $availableDates = DailyData::distinct()
                                      ->whereNotNull('date')
                                      ->orderBy('date', 'desc')
                                      ->pluck('date');
            
            return view('schedules.index', compact('dailyData', 'availableDates'));
        }
        
        // For other tabs, return empty data
        return view('schedules.index', [
            'dailyData' => collect([]),
            'availableDates' => collect([])
        ]);
    }
}
