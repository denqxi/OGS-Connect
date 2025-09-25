<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyData extends Model
{
    use HasFactory;

    protected $table = 'daily_data';

    protected $fillable = [
        'school',
        'class',
        'duration',
        'date',
        'day',
        'time_jst',
        'time_pht',
        'number_required',
        'tutors_assigned'
    ];

    protected $casts = [
        'date' => 'date',
        'time_jst' => 'datetime:H:i',
        'time_pht' => 'datetime:H:i',
    ];
}
