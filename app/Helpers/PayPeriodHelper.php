<?php

namespace App\Helpers;

use Carbon\Carbon;

class PayPeriodHelper
{
    /**
     * Determine the current pay period for a given date.
     * 
     * Rules:
     * - First period: 28th (prev month) → 12th (current month), release on 15th
     * - Second period: 13th → 27th (same month), release on last day of month
     * 
     * @param Carbon|string|null $date
     * @return array ['start' => Carbon, 'end' => Carbon, 'release' => Carbon, 'label' => string]
     */
    public static function getCurrentPeriod($date = null): array
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $day = (int) $date->format('j');

        if ($day >= 28) {
            // Belongs to first period of NEXT month (28 → 12)
            // Set to 28th of current month first, then add month to get next month safely
            $periodStart = Carbon::create($date->year, $date->month, 28);
            $nextMonth = $periodStart->copy()->addMonth();
            $periodEnd = Carbon::create($nextMonth->year, $nextMonth->month, 12);
            $releaseDate = Carbon::create($nextMonth->year, $nextMonth->month, 15);
            $label = $periodEnd->format('Y-m') . ' (28-12)';
        } elseif ($day >= 13) {
            // Second period of current month (13 → 27)
            $periodStart = Carbon::create($date->year, $date->month, 13);
            $periodEnd = Carbon::create($date->year, $date->month, 27);
            $releaseDate = $date->copy()->endOfMonth();
            $label = $date->format('Y-m') . ' (13-27)';
        } else {
            // First period (28 prev month → 12 current month)
            $prevMonth = $date->copy()->subMonth();
            $periodStart = Carbon::create($prevMonth->year, $prevMonth->month, 28);
            $periodEnd = Carbon::create($date->year, $date->month, 12);
            $releaseDate = Carbon::create($date->year, $date->month, 15);
            $label = $date->format('Y-m') . ' (28-12)';
        }

        return [
            'start' => $periodStart,
            'end' => $periodEnd,
            'release' => $releaseDate,
            'label' => $label,
        ];
    }

    /**
     * Get period boundaries for a specific period type and month.
     * 
     * @param string $periodType '28-12' or '13-27'
     * @param int $year
     * @param int $month
     * @return array ['start' => Carbon, 'end' => Carbon, 'release' => Carbon, 'label' => string]
     */
    public static function getPeriodForMonth(string $periodType, int $year, int $month): array
    {
        $date = Carbon::create($year, $month, 1);

        if ($periodType === '28-12') {
            // First period: 28th of prev month → 12th of this month
            $periodStart = $date->copy()->subMonth()->day(28);
            $periodEnd = $date->copy()->day(12);
            $releaseDate = $date->copy()->day(15);
            $label = $date->format('Y-m') . ' (28-12)';
        } else {
            // Second period: 13th → 27th of same month
            $periodStart = $date->copy()->day(13);
            $periodEnd = $date->copy()->day(27);
            $releaseDate = $date->copy()->endOfMonth();
            $label = $date->format('Y-m') . ' (13-27)';
        }

        return [
            'start' => $periodStart,
            'end' => $periodEnd,
            'release' => $releaseDate,
            'label' => $label,
        ];
    }

    /**
     * Determine which pay period a specific date belongs to.
     * 
     * @param Carbon|string $workDate
     * @return array ['start' => Carbon, 'end' => Carbon, 'release' => Carbon, 'label' => string]
     */
    public static function getPeriodForDate($workDate): array
    {
        return self::getCurrentPeriod($workDate);
    }

    /**
     * Format period as a string suitable for database storage and display.
     * 
     * @param Carbon $start
     * @param Carbon $end
     * @return string e.g., "2025-02 (28-12)" or "2025-03 (13-27)"
     */
    public static function formatPeriodLabel(Carbon $start, Carbon $end): string
    {
        $day = (int) $start->format('j');
        
        if ($day === 28) {
            // First period: use the end month
            return $end->format('Y-m') . ' (28-12)';
        } else {
            // Second period: use the start month
            return $start->format('Y-m') . ' (13-27)';
        }
    }

    /**
     * Check if a date is within a given pay period.
     * 
     * @param Carbon|string $date
     * @param Carbon $periodStart
     * @param Carbon $periodEnd
     * @return bool
     */
    public static function isDateInPeriod($date, Carbon $periodStart, Carbon $periodEnd): bool
    {
        $date = Carbon::parse($date);
        return $date->between($periodStart, $periodEnd);
    }

    /**
     * Get all periods for a given year.
     * 
     * @param int $year
     * @return array Array of period data
     */
    public static function getPeriodsForYear(int $year): array
    {
        $periods = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $periods[] = self::getPeriodForMonth('28-12', $year, $month);
            $periods[] = self::getPeriodForMonth('13-27', $year, $month);
        }
        
        return $periods;
    }
}
