<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmployeePaymentInformation extends Model
{
    protected $table = 'employee_payment_information';
    
    protected $fillable = [
        'employee_id',
        'employee_type',
        'payment_method',
        'bank_name',
        'account_number',
        'account_name',
        'paypal_email',
        'gcash_number',
        'paymaya_number',
        'hourly_rate',
        'monthly_salary',
        'payment_frequency',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'monthly_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the employee (tutor or supervisor) that owns the payment information.
     */
    public function employee()
    {
        if ($this->employee_type === 'tutor') {
            return $this->belongsTo(Tutor::class, 'employee_id', 'tutorID');
        } elseif ($this->employee_type === 'supervisor') {
            return $this->belongsTo(Supervisor::class, 'employee_id', 'supID');
        }
        
        return null;
    }



    /**
     * Get payment method options
     */
    public static function getPaymentMethods()
    {
        return [
            'gcash' => 'GCash',
            'paypal' => 'PayPal',
            'paymaya' => 'PayMaya',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash'
        ];
    }

    /**
     * Get payment frequency options
     */
    public static function getPaymentFrequencies()
    {
        return [
            'hourly' => 'Hourly',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly'
        ];
    }

    /**
     * Get formatted payment method name
     */
    public function getFormattedPaymentMethodAttribute()
    {
        return self::getPaymentMethods()[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    /**
     * Get formatted payment frequency name
     */
    public function getFormattedPaymentFrequencyAttribute()
    {
        return self::getPaymentFrequencies()[$this->payment_frequency] ?? $this->payment_frequency;
    }

    /**
     * Get payment method in uppercase
     */
    public function getPaymentMethodUppercaseAttribute()
    {
        return strtoupper($this->payment_method ?? '');
    }

}