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
        'payment_method_id',
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
     * Get the payment method details
     */
    public function paymentMethodDetails()
    {
        return $this->belongsTo(PaymentMethodDetails::class, 'payment_method_id');
    }

    /**
     * Get the payment details (normalized fields)
     */
    public function paymentDetails()
    {
        return $this->hasMany(EmployeePaymentDetails::class, 'employee_payment_id');
    }

    /**
     * Get payment method options from the normalized table
     */
    public static function getPaymentMethods()
    {
        return PaymentMethodDetails::where('is_active', true)
            ->pluck('description', 'payment_method_name')
            ->toArray();
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
        return $this->paymentMethodDetails->description ?? $this->paymentMethodDetails->payment_method_name ?? 'Unknown';
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
        return strtoupper($this->paymentMethodDetails->payment_method_name ?? '');
    }

    /**
     * Get the appropriate payment details based on payment method
     */
    public function getPaymentDetailsArrayAttribute()
    {
        $details = [];
        
        foreach ($this->paymentDetails as $detail) {
            $details[$detail->field_name] = $detail->field_value;
        }
        
        return $details;
    }

    /**
     * Get a specific payment detail by field name
     */
    public function getPaymentDetail($fieldName)
    {
        $detail = $this->paymentDetails()->where('field_name', $fieldName)->first();
        return $detail ? $detail->field_value : null;
    }

    /**
     * Set a payment detail
     */
    public function setPaymentDetail($fieldName, $fieldValue)
    {
        $this->paymentDetails()->updateOrCreate(
            ['field_name' => $fieldName],
            ['field_value' => $fieldValue]
        );
    }
}