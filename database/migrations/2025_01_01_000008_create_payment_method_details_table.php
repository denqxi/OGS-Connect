<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_method_details', function (Blueprint $table) {
            $table->id();
            $table->string('payment_method_name'); // 'BANK_TRANSFER', 'GCASH', 'PAYMAYA', 'PAYPAL'
            $table->json('required_fields'); // Array of required field names
            $table->json('field_validation_rules')->nullable(); // Validation rules for each field
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique('payment_method_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_method_details');
    }
};
