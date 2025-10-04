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
        // First, migrate any unique data from employee_payment_details to employee_payment_information
        $this->migrateUniqueDataToMainTable();
        
        // Then drop the redundant table
        Schema::dropIfExists('employee_payment_details');
    }

    /**
     * Migrate unique data from employee_payment_details to employee_payment_information
     */
    private function migrateUniqueDataToMainTable()
    {
        echo "Migrating unique data from employee_payment_details to employee_payment_information...\n";
        
        // Get all payment details grouped by payment_id
        $paymentDetails = DB::table('employee_payment_details')
            ->select('employee_payment_id', 'field_name', 'field_value')
            ->get()
            ->groupBy('employee_payment_id');
        
        $updated = 0;
        
        foreach ($paymentDetails as $paymentId => $details) {
            $updateData = [];
            
            // Convert details to array for easier processing
            $detailsArray = $details->keyBy('field_name');
            
            // Map field names to column names
            $fieldMapping = [
                'gcash_number' => 'gcash_number',
                'paymaya_number' => 'paymaya_number',
                'paypal_email' => 'paypal_email',
                'account_number' => 'account_number',
                'account_name' => 'account_name',
                'bank_name' => 'bank_name',
            ];
            
            // Check if we need to update any fields
            foreach ($fieldMapping as $detailField => $columnField) {
                if (isset($detailsArray[$detailField])) {
                    $detailValue = $detailsArray[$detailField]->field_value;
                    
                    // Only update if the main table field is null or empty
                    $currentValue = DB::table('employee_payment_information')
                        ->where('id', $paymentId)
                        ->value($columnField);
                    
                    if (empty($currentValue) && !empty($detailValue)) {
                        $updateData[$columnField] = $detailValue;
                    }
                }
            }
            
            // Update the main table if we have data to migrate
            if (!empty($updateData)) {
                DB::table('employee_payment_information')
                    ->where('id', $paymentId)
                    ->update($updateData);
                $updated++;
                
                echo "Updated payment record ID {$paymentId} with: " . implode(', ', array_keys($updateData)) . "\n";
            }
        }
        
        echo "Migration completed. Updated {$updated} payment records.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the employee_payment_details table
        Schema::create('employee_payment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_payment_id');
            $table->string('field_name');
            $table->text('field_value');
            $table->timestamps();
            
            $table->foreign('employee_payment_id')->references('id')->on('employee_payment_information')->onDelete('cascade');
            $table->index('employee_payment_id');
            $table->index(['employee_payment_id', 'field_name']);
        });
        
        // Migrate data back from main table to details table
        $this->migrateDataBackToDetailsTable();
    }
    
    /**
     * Migrate data back from main table to details table (for rollback)
     */
    private function migrateDataBackToDetailsTable()
    {
        $paymentRecords = DB::table('employee_payment_information')->get();
        
        foreach ($paymentRecords as $record) {
            $details = [];
            
            // Map main table fields back to detail records
            $fieldMapping = [
                'gcash_number' => 'gcash_number',
                'paymaya_number' => 'paymaya_number',
                'paypal_email' => 'paypal_email',
                'account_number' => 'account_number',
                'account_name' => 'account_name',
                'bank_name' => 'bank_name',
            ];
            
            foreach ($fieldMapping as $columnField => $detailField) {
                if (!empty($record->$columnField)) {
                    $details[] = [
                        'employee_payment_id' => $record->id,
                        'field_name' => $detailField,
                        'field_value' => $record->$columnField,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            if (!empty($details)) {
                DB::table('employee_payment_details')->insert($details);
            }
        }
    }
};