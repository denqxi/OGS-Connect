<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePaymentDetails extends Model
{
    protected $fillable = [
        'employee_payment_id',
        'field_name',
        'field_value'
    ];

    /**
     * Get the employee payment information that owns this detail
     */
    public function employeePaymentInformation()
    {
        return $this->belongsTo(EmployeePaymentInformation::class);
    }

    /**
     * Get payment details by field name
     */
    public function scopeByFieldName($query, $fieldName)
    {
        return $query->where('field_name', $fieldName);
    }

    /**
     * Get payment details for a specific payment record
     */
    public function scopeForPayment($query, $paymentId)
    {
        return $query->where('employee_payment_id', $paymentId);
    }
}
