<?php

namespace App\Helpers;

class DateHelper
{
    /**
     * Format days array to shortened notation
     * Example: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'] => 'Mon-Fri'
     * Example: ['monday', 'tuesday', 'wednesday', 'thursday'] => 'Mon-Thu'
     * Example: ['monday', 'wednesday', 'thursday'] => 'Mon, Wed-Thu'
     * 
     * @param array|null $days
     * @return string
     */
    public static function formatDaysAvailable($days = null)
    {
        if (!$days || !is_array($days) || count($days) === 0) {
            return 'N/A';
        }

        // Map day names to short names and order
        $dayMap = [
            'monday' => 'Mon',
            'tuesday' => 'Tue',
            'wednesday' => 'Wed',
            'thursday' => 'Thu',
            'friday' => 'Fri',
            'saturday' => 'Sat',
            'sunday' => 'Sun',
        ];

        $dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // Normalize the days array to lowercase
        $normalizedDays = array_map('strtolower', $days);

        // Filter only valid days and maintain order
        $validDays = array_values(array_filter($dayOrder, function ($day) use ($normalizedDays) {
            return in_array($day, $normalizedDays);
        }));

        if (count($validDays) === 0) {
            return 'N/A';
        }

        // Convert to short day names and get indices for range checking
        $dayIndices = [];
        $shortDays = [];
        foreach ($validDays as $day) {
            $shortDays[] = $dayMap[$day];
            $dayIndices[] = array_search($day, $dayOrder);
        }

        // Group consecutive days into ranges
        $result = [];
        $start = 0;

        for ($i = 1; $i <= count($dayIndices); $i++) {
            // Check if we need to end current range (last item or non-consecutive)
            if ($i === count($dayIndices) || $dayIndices[$i] !== $dayIndices[$i - 1] + 1) {
                // Add the range
                if ($start === $i - 1) {
                    $result[] = $shortDays[$start];
                } else {
                    $result[] = $shortDays[$start] . '-' . $shortDays[$i - 1];
                }
                $start = $i;
            }
        }

        return implode(', ', $result);
    }
}
