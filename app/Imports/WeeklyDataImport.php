<?php

namespace App\Imports;

use App\Models\DailyData;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class WeeklyDataImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Debug full row from Excel
        Log::info('Importing row:', $row);

        // Skip empty rows
        if (!isset($row['school']) || empty($row['school'])) {
            Log::warning('Skipped empty row', $row);
            return null;
        }

        // Debug critical column
        Log::info('Number Required Value:', [
            'raw' => $row['number_required'] ?? 'MISSING'
        ]);

        return new DailyData([
            'school'          => $row['school'] ?? null,
            'class'           => $row['class'] ?? null,

            // Excel column: "Duration (if not 25 mins)"
            'duration'        => $row['duration_if_not_25_mins'] ?? 25,

            'date'            => isset($row['date'])
                                    ? Date::excelToDateTimeObject($row['date'])->format('Y-m-d')
                                    : null,

            'day'             => $row['day'] ?? null,

            // Excel column "Time (JST)" → "time_jst"
            'time_jst'        => isset($row['time_jst'])
                                    ? Date::excelToDateTimeObject($row['time_jst'])->format('H:i')
                                    : null,

            // Excel column "Time (PHT)" → "time_pht"
            'time_pht'        => isset($row['time_pht'])
                                    ? Date::excelToDateTimeObject($row['time_pht'])->format('H:i')
                                    : null,

            // Excel column "Number Required (Tutors)"
            'number_required' => $row['number_required'] ?? null,
        ]);
    }
}
