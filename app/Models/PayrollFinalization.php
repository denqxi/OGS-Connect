<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollFinalization extends Model
{
    protected $table = 'payroll_finalizations';
    protected $primaryKey = 'finalization_id';
    public $timestamps = true;

    protected $fillable = [
        'tutor_id',
        'pay_period',
        'total_amount',
        'work_details_count',
        'status',
        'finalized_at',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'work_details_count' => 'integer',
        'finalized_at' => 'datetime',
    ];

    /**
     * Get the tutor that this finalization belongs to
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'tutor_id', 'tutor_id');
    }
}
