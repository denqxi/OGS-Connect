<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    protected $table = 'archives';

    protected $primaryKey = 'archive_id';

    protected $fillable = [
        'applicant_id',
        'archive_by',
        'reason',
        'notes',
        'archive_date',
        'archive_date_time',
        'category',
        'status',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'archive_date_time' => 'datetime',
    ];

    public $timestamps = true;

    public function applicant()
    {
        return $this->belongsTo(\App\Models\Applicant::class, 'applicant_id', 'applicant_id');
    }
}
