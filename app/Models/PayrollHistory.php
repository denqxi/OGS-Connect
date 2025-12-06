<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollHistory extends Model
{
    protected $table = 'payroll_history';
    protected $primaryKey = 'payroll_history_id';

    protected $fillable = [
        'tutor_id',
        'pay_period',
        'total_amount',
        'submission_type',
        'status',
        'recipient_email',
        'notes',
        'submitted_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'total_amount' => 'decimal:2'
    ];

    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'tutor_id', 'tutor_id');
    }
}
