<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, migrate existing data to the new normalized structure
        $this->migrateExistingPaymentData();
        
        // Then remove the payment method specific columns
        Schema::table('employee_payment_information', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'account_number', 
                'account_name',
                'paypal_email',
                'gcash_number',
                'paymaya_number'
            ]);
        });
    }

    /**
     * Migrate existing payment data to normalized structure
     */
    private function migrateExistingPaymentData()
    {
        $paymentRecords = DB::table('employee_payment_information')->get();
        
        foreach ($paymentRecords as $record) {
            $paymentDetails = [];
            
            // Map existing fields to new structure based on payment method
            switch ($record->payment_method) {
                case 'gcash':
                    if (!empty($record->gcash_number)) {
                        $paymentDetails[] = [
                            'employee_payment_id' => $record->id,
                            'field_name' => 'gcash_number',
                            'field_value' => $record->gcash_number,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    break;
                    
                case 'paymaya':
                    if (!empty($record->paymaya_number)) {
                        $paymentDetails[] = [
                            'employee_payment_id' => $record->id,
                            'field_name' => 'paymaya_number',
                            'field_value' => $record->paymaya_number,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    break;
                    
                case 'paypal':
                    if (!empty($record->paypal_email)) {
                        $paymentDetails[] = [
                            'employee_payment_id' => $record->id,
                            'field_name' => 'paypal_email',
                            'field_value' => $record->paypal_email,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    break;
                    
                case 'bdo':
                case 'bpi':
                case 'metrobank':
                case 'landbank':
                case 'unionbank':
                    if (!empty($record->account_number)) {
                        $paymentDetails[] = [
                            'employee_payment_id' => $record->id,
                            'field_name' => 'account_number',
                            'field_value' => $record->account_number,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    if (!empty($record->account_name)) {
                        $paymentDetails[] = [
                            'employee_payment_id' => $record->id,
                            'field_name' => 'account_name',
                            'field_value' => $record->account_name,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    break;
                    
                case 'remittance':
                    if (!empty($record->account_name)) {
                        $paymentDetails[] = [
                            'employee_payment_id' => $record->id,
                            'field_name' => 'account_name',
                            'field_value' => $record->account_name,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    break;
            }
            
            // Insert the normalized data
            if (!empty($paymentDetails)) {
                DB::table('employee_payment_details')->insert($paymentDetails);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the columns
        Schema::table('employee_payment_information', function (Blueprint $table) {
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('paypal_email')->nullable();
            $table->string('gcash_number')->nullable();
            $table->string('paymaya_number')->nullable();
        });
        
        // Migrate data back from normalized structure
        $this->migrateDataBack();
    }
    
    /**
     * Migrate data back from normalized structure
     */
    private function migrateDataBack()
    {
        $paymentRecords = DB::table('employee_payment_information')->get();
        
        foreach ($paymentRecords as $record) {
            $details = DB::table('employee_payment_details')
                ->where('employee_payment_id', $record->id)
                ->get()
                ->keyBy('field_name');
            
            $updateData = [];
            
            if (isset($details['gcash_number'])) {
                $updateData['gcash_number'] = $details['gcash_number']->field_value;
            }
            if (isset($details['paymaya_number'])) {
                $updateData['paymaya_number'] = $details['paymaya_number']->field_value;
            }
            if (isset($details['paypal_email'])) {
                $updateData['paypal_email'] = $details['paypal_email']->field_value;
            }
            if (isset($details['account_number'])) {
                $updateData['account_number'] = $details['account_number']->field_value;
            }
            if (isset($details['account_name'])) {
                $updateData['account_name'] = $details['account_name']->field_value;
            }
            
            if (!empty($updateData)) {
                DB::table('employee_payment_information')
                    ->where('id', $record->id)
                    ->update($updateData);
            }
        }
    }
};
