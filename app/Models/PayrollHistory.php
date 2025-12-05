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
        'submission_type',
        'status',
        'recipient_email',
        'notes',
        'submitted_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime'
    ];

    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'tutor_id', 'tutor_id');
    }
}
