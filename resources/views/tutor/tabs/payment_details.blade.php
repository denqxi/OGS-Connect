<!-- Payment Information Content -->
<div class="space-y-6">
    <!-- Row 1: Section Title -->
    <h3 class="text-lg md:text-xl font-semibold text-[#4B5563] dark:text-[#F39C12]">
        Payment Information
    </h3>
    <hr>
    <!-- Row 2: p text (left) + button (right) -->
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Manage your payment info
        </p>
        <div class="flex space-x-2">
            <button id="addPaymentBtn" class="bg-green-600 text-white font-semibold px-4 py-2 rounded-full hover:bg-green-700 transition-colors duration-200">
                <i class="fas fa-plus mr-1"></i>Add Payment Method
            </button>
        </div>
    </div>

    <!-- Payment Info Card -->
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700"
        id="paymentDetailsContainer">
        
        <!-- Payment information will be dynamically loaded here -->
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#F39C12] mx-auto"></div>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Loading payment information...</p>
        </div>
    </div>
</div>

<script src="{{ asset('js/tutor-payment.js') }}"></script>
