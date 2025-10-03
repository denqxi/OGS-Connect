@extends('layouts.app')

@section('title', 'Payment Information')

@section('content')
    @include('layouts.header', ['pageTitle' => 'Payment Information'])

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">
                            Success
                        </h3>
                        <div class="mt-1 text-sm text-green-700">
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Payment Information Form -->
        <div class="bg-white shadow-md rounded-xl p-4 sm:p-6 space-y-6">
            <h3 class="text-base sm:text-lg md:text-xl font-semibold text-[#0E335D] border-b-2 border-[#0E335D] pb-2">
                Payment Information</h3>
            
            <form method="POST" action="{{ route('payment-information.store') }}" class="space-y-6">
                @csrf
                
                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-[#0E335D] mb-2">Payment Method *</label>
                    <select name="payment_method" id="payment_method" required
                            class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent">
                        <option value="gcash" {{ old('payment_method', $paymentInfo->payment_method ?? 'gcash') == 'gcash' ? 'selected' : '' }}>GCash</option>
                        <option value="paymaya" {{ old('payment_method', $paymentInfo->payment_method ?? '') == 'paymaya' ? 'selected' : '' }}>PayMaya</option>
                        <option value="bdo" {{ old('payment_method', $paymentInfo->payment_method ?? '') == 'bdo' ? 'selected' : '' }}>BDO Bank Transfer</option>
                        <option value="bpi" {{ old('payment_method', $paymentInfo->payment_method ?? '') == 'bpi' ? 'selected' : '' }}>BPI Bank Transfer</option>
                        <option value="metrobank" {{ old('payment_method', $paymentInfo->payment_method ?? '') == 'metrobank' ? 'selected' : '' }}>Metrobank Transfer</option>
                        <option value="landbank" {{ old('payment_method', $paymentInfo->payment_method ?? '') == 'landbank' ? 'selected' : '' }}>Landbank Transfer</option>
                        <option value="unionbank" {{ old('payment_method', $paymentInfo->payment_method ?? '') == 'unionbank' ? 'selected' : '' }}>UnionBank Transfer</option>
                        <option value="paypal" {{ old('payment_method', $paymentInfo->payment_method ?? '') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                        <option value="remittance" {{ old('payment_method', $paymentInfo->payment_method ?? '') == 'remittance' ? 'selected' : '' }}>Remittance Center</option>
                        <option value="cash" {{ old('payment_method', $paymentInfo->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                    </select>
                    @error('payment_method')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Bank Transfer Details -->
                <div id="bank_details" class="space-y-4" style="display: none;">
                    <h4 class="text-sm font-medium text-[#0E335D]">Bank Transfer Details</h4>
                    
                    <div>
                        <label class="block text-sm font-medium text-[#0E335D] mb-2">Account Number</label>
                        <input type="text" name="account_number" value="{{ old('account_number', $paymentInfo->account_number ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent">
                        @error('account_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-[#0E335D] mb-2">Account Name</label>
                        <input type="text" name="account_name" value="{{ old('account_name', $paymentInfo->account_name ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent">
                        @error('account_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- PayPal Details -->
                <div id="paypal_details" class="space-y-4" style="display: none;">
                    <h4 class="text-sm font-medium text-[#0E335D]">PayPal Details</h4>
                    
                    <div>
                        <label class="block text-sm font-medium text-[#0E335D] mb-2">PayPal Email</label>
                        <input type="email" name="paypal_email" value="{{ old('paypal_email', $paymentInfo->paypal_email ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent">
                        @error('paypal_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- GCash Details -->
                <div id="gcash_details" class="space-y-4" style="display: none;">
                    <h4 class="text-sm font-medium text-[#0E335D]">GCash Details</h4>
                    
                    <div>
                        <label class="block text-sm font-medium text-[#0E335D] mb-2">GCash Number</label>
                        <input type="text" name="gcash_number" value="{{ old('gcash_number', $paymentInfo->gcash_number ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent">
                        @error('gcash_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- PayMaya Details -->
                <div id="paymaya_details" class="space-y-4" style="display: none;">
                    <h4 class="text-sm font-medium text-[#0E335D]">PayMaya Details</h4>
                    
                    <div>
                        <label class="block text-sm font-medium text-[#0E335D] mb-2">PayMaya Number</label>
                        <input type="text" name="paymaya_number" value="{{ old('paymaya_number', $paymentInfo->paymaya_number ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent">
                        @error('paymaya_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>


                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-[#0E335D] mb-2">Notes</label>
                    <textarea name="notes" rows="3" 
                              class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent"
                              placeholder="Any additional notes about payment...">{{ old('notes', $paymentInfo->notes ?? '') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit"
                            class="px-6 py-3 bg-[#0E335D] text-white text-sm rounded-lg hover:bg-gray-800 transform transition duration-200 hover:scale-105">
                        Update Payment Information
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethodSelect = document.getElementById('payment_method');
            const bankDetails = document.getElementById('bank_details');
            const paypalDetails = document.getElementById('paypal_details');
            const gcashDetails = document.getElementById('gcash_details');
            const paymayaDetails = document.getElementById('paymaya_details');

            function togglePaymentDetails() {
                // Hide all details first
                bankDetails.style.display = 'none';
                paypalDetails.style.display = 'none';
                gcashDetails.style.display = 'none';
                paymayaDetails.style.display = 'none';

                // Show relevant details based on selection
                const selectedMethod = paymentMethodSelect.value;
                switch(selectedMethod) {
                    case 'bdo':
                    case 'bpi':
                    case 'metrobank':
                    case 'landbank':
                    case 'unionbank':
                        bankDetails.style.display = 'block';
                        break;
                    case 'paypal':
                        paypalDetails.style.display = 'block';
                        break;
                    case 'gcash':
                        gcashDetails.style.display = 'block';
                        break;
                    case 'paymaya':
                        paymayaDetails.style.display = 'block';
                        break;
                }
            }

            // Initial call to show correct details if form has existing data
            // Since GCash is default, show GCash details by default
            togglePaymentDetails();

            // Listen for changes
            paymentMethodSelect.addEventListener('change', togglePaymentDetails);
        });
    </script>
@endsection
