<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethodDetails extends Model
{
    protected $fillable = [
        'payment_method_name',
        'required_fields',
        'field_validation_rules',
        'description',
        'is_active'
    ];

    protected $casts = [
        'required_fields' => 'array',
        'field_validation_rules' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get the payment information records that use this payment method
     */
    public function employeePaymentInformation()
    {
        return $this->hasMany(EmployeePaymentInformation::class);
    }

    /**
     * Get the required fields for this payment method
     */
    public function getRequiredFieldsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    /**
     * Get the validation rules for this payment method
     */
    public function getFieldValidationRulesAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }
}
