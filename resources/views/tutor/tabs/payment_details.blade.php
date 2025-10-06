<!-- Payment Information Content -->
<div class="space-y-6">
    <!-- Row 1: Section Title -->
    <h3 class="text-lg md:text-xl font-semibold text-[#F39C12] dark:text-[#F39C12]">
        Payment Information
    </h3>
    <hr>
    <!-- Row 2: p text (left) + button (right) -->
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Manage your payment info
        </p>
        <button
            class="bg-[#F39C12] text-white font-semibold px-6 py-2 rounded-full hover:bg-[#D97706] transition-colors duration-200">
            Edit Payment Info
        </button>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadPaymentDetails();
});

async function loadPaymentDetails() {
    try {
        const response = await fetch('/tutor/availability/', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const result = await response.json();
        
        if (result.success && result.tutor_info && result.tutor_info.payment_info) {
            displayPaymentDetails(result.tutor_info.payment_info);
        } else {
            showNoPaymentDetails();
        }
    } catch (error) {
        console.error('Error loading payment details:', error);
        showPaymentError();
    }
}

function displayPaymentDetails(paymentInfo) {
    const container = document.getElementById('paymentDetailsContainer');
    if (!container) return;

    const paymentMethods = {
        'gcash': 'GCash',
        'paypal': 'PayPal',
        'paymaya': 'PayMaya',
        'bank_transfer': 'Bank Transfer',
        'cash': 'Cash'
    };

    const formatPaymentMethod = (method) => {
        return paymentMethods[method] || method || 'N/A';
    };


    // Generate HTML based on payment method
    let paymentFieldsHTML = '';
    
    if (paymentInfo.payment_method === 'gcash') {
        // GCash specific fields
        paymentFieldsHTML = `
            <!-- Payment Method -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Payment Method</label>
                    <input type="text" value="${formatPaymentMethod(paymentInfo.payment_method)}" disabled
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">GCash Number</label>
                    <input type="text" value="${paymentInfo.gcash_number || 'N/A'}" disabled
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Account Name</label>
                    <input type="text" value="${paymentInfo.account_name || 'N/A'}" disabled
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>

            </div>
        `;
    } else if (paymentInfo.payment_method === 'paypal') {
        // PayPal specific fields
        paymentFieldsHTML = `
            <!-- Payment Method -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Payment Method</label>
                    <input type="text" value="${formatPaymentMethod(paymentInfo.payment_method)}" disabled
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">PayPal Email</label>
                    <input type="text" value="${paymentInfo.paypal_email || 'N/A'}" disabled
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Account Name</label>
                    <input type="text" value="${paymentInfo.account_name || 'N/A'}" disabled
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>

            </div>
        `;
    } else if (paymentInfo.payment_method === 'paymaya') {
        // PayMaya specific fields
        paymentFieldsHTML = `
            <!-- Payment Method -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Payment Method</label>
                    <input type="text" value="${formatPaymentMethod(paymentInfo.payment_method)}" disabled
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">PayMaya Number</label>
                    <input type="text" value="${paymentInfo.paymaya_number || 'N/A'}" disabled
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Account Name</label>
                    <input type="text" value="${paymentInfo.account_name || 'N/A'}" disabled
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>

            </div>
        `;
    } else if (paymentInfo.payment_method === 'bank_transfer') {
        // Bank Transfer specific fields
        paymentFieldsHTML = `
            <!-- Payment Method -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Payment Method</label>
                    <input type="text" value="${formatPaymentMethod(paymentInfo.payment_method)}" disabled
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Bank Name</label>
                    <input type="text" value="${paymentInfo.bank_name || 'N/A'}" disabled
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Account Number</label>
                    <input type="text" value="${paymentInfo.account_number || 'N/A'}" disabled
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Account Name</label>
                    <input type="text" value="${paymentInfo.account_name || 'N/A'}" disabled
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>

            </div>
        `;
    } else {
        // Cash or other payment methods
        paymentFieldsHTML = `
            <!-- Payment Method -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Payment Method</label>
                    <input type="text" value="${formatPaymentMethod(paymentInfo.payment_method)}" disabled
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Account Name</label>
                    <input type="text" value="${paymentInfo.account_name || 'N/A'}" disabled
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                </div>

            </div>
        `;
    }

    container.innerHTML = `
        ${paymentFieldsHTML}


    `;
}

function showNoPaymentDetails() {
    const container = document.getElementById('paymentDetailsContainer');
    if (!container) return;

    container.innerHTML = `
        <div class="text-center py-8">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-700">
                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
            </div>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No Payment Information</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Payment information has not been set up yet.</p>
        </div>
    `;
}

function showPaymentError() {
    const container = document.getElementById('paymentDetailsContainer');
    if (!container) return;

    container.innerHTML = `
        <div class="text-center py-8">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Error Loading Payment Information</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">There was an error loading your payment information. Please try again.</p>
        </div>
    `;
}
</script>
