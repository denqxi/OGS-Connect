// Global variable to track if payment info exists
let hasExistingPaymentInfo = false;

document.addEventListener('DOMContentLoaded', function() {
    loadPaymentDetails();
    setupPaymentEventListeners();
});

function setupPaymentEventListeners() {
    const editPaymentBtn = document.getElementById('editPaymentBtn');
    const addPaymentBtn = document.getElementById('addPaymentBtn');
    
    if (editPaymentBtn) {
        editPaymentBtn.addEventListener('click', function() {
            if (hasExistingPaymentInfo) {
                showEditPaymentForm();
            } else {
                showPaymentSetupForm();
            }
        });
    }
    
    if (addPaymentBtn) {
        addPaymentBtn.addEventListener('click', function() {
            showAddPaymentForm();
        });
    }
}

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
        
        if (result.success && result.tutor_info && result.tutor_info.payment_info && result.tutor_info.payment_info.length > 0) {
            hasExistingPaymentInfo = true;
            // Get the first (and only) payment record
            displayPaymentDetails(result.tutor_info.payment_info[0]);
        } else {
            hasExistingPaymentInfo = false;
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

    const paymentMethodNames = {
        'gcash': 'GCash',
        'paypal': 'PayPal',
        'paymaya': 'PayMaya',
        'bank_transfer': 'Bank Transfer',
        'cash': 'Cash'
    };

    const formatPaymentMethod = (method) => {
        return paymentMethodNames[method] || method || 'N/A';
    };


            // Handle single payment record with multiple payment methods
            const payment = paymentInfo;

            let paymentFieldsHTML = '';

            // Check which payment methods have data and create cards for each
            const paymentMethodsList = [];
            
            // Check GCash
            if (payment.gcash_number) {
                paymentMethodsList.push({
                    method: 'gcash',
                    displayName: 'GCash',
                    data: {
                        gcash_number: payment.gcash_number,
                        account_name: payment.account_name
                    }
                });
            }
            
            // Check PayPal
            if (payment.paypal_email) {
                paymentMethodsList.push({
                    method: 'paypal',
                    displayName: 'PayPal',
                    data: {
                        paypal_email: payment.paypal_email,
                        account_name: payment.account_name
                    }
                });
            }
            
            // Check PayMaya
            if (payment.paymaya_number) {
                paymentMethodsList.push({
                    method: 'paymaya',
                    displayName: 'PayMaya',
                    data: {
                        paymaya_number: payment.paymaya_number,
                        account_name: payment.account_name
                    }
                });
            }
            
            // Check Bank Transfer
            if (payment.bank_name && payment.account_number) {
                paymentMethodsList.push({
                    method: 'bank_transfer',
                    displayName: 'Bank Transfer',
                    data: {
                        bank_name: payment.bank_name,
                        account_number: payment.account_number,
                        account_name: payment.account_name
                    }
                });
            }
            
            // Check Cash (if no other methods or if explicitly set)
            if (payment.payment_method === 'cash' || paymentMethodsList.length === 0) {
                paymentMethodsList.push({
                    method: 'cash',
                    displayName: 'Cash',
                    data: {
                        account_name: payment.account_name
                    }
                });
            }

            // Replace the container content with multiple payment cards
            let allPaymentCards = '';
            
            paymentMethodsList.forEach((paymentMethod, index) => {
                let cardContent = `<h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">${paymentMethod.displayName}</h4>`;
                
                // Generate fields based on payment method
                if (paymentMethod.method === 'gcash') {
                    cardContent += `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">GCash Number</label>
                                <input type="text" value="${paymentMethod.data.gcash_number || 'N/A'}" disabled
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Account Name</label>
                                <input type="text" value="${paymentMethod.data.account_name || 'N/A'}" disabled
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                            </div>
                        </div>
                    `;
                } else if (paymentMethod.method === 'paypal') {
                    cardContent += `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">PayPal Email</label>
                                <input type="text" value="${paymentMethod.data.paypal_email || 'N/A'}" disabled
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Account Name</label>
                                <input type="text" value="${paymentMethod.data.account_name || 'N/A'}" disabled
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                            </div>
                        </div>
                    `;
                } else if (paymentMethod.method === 'paymaya') {
                    cardContent += `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">PayMaya Number</label>
                                <input type="text" value="${paymentMethod.data.paymaya_number || 'N/A'}" disabled
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Account Name</label>
                                <input type="text" value="${paymentMethod.data.account_name || 'N/A'}" disabled
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                            </div>
                        </div>
                    `;
                } else if (paymentMethod.method === 'bank_transfer') {
                    cardContent += `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Bank Name</label>
                                <input type="text" value="${paymentMethod.data.bank_name || 'N/A'}" disabled
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Account Number</label>
                                <input type="text" value="${paymentMethod.data.account_number || 'N/A'}" disabled
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Account Name</label>
                                <input type="text" value="${paymentMethod.data.account_name || 'N/A'}" disabled
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                            </div>
                        </div>
                    `;
                } else if (paymentMethod.method === 'cash') {
                    cardContent += `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1">Account Name</label>
                                <input type="text" value="${paymentMethod.data.account_name || 'N/A'}" disabled
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Cash payments will be handled directly with the administrator.</p>
                        </div>
                    `;
                }
                
                // Create a complete payment card for each payment method
                allPaymentCards += `
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700 mb-4">
                        ${cardContent}
                    </div>
                `;
            });
            
            // Replace only the paymentDetailsContainer content
            container.innerHTML = allPaymentCards;

    // Event listeners are handled in setupPaymentEventListeners()
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
            <div class="mt-4">
                <button onclick="showPaymentSetupForm()" 
                        class="bg-[#F39C12] text-white font-semibold px-6 py-2 rounded-full hover:bg-[#D97706] transition-colors duration-200">
                    Set Up Payment Information
                </button>
            </div>
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

function showAddPaymentForm() {
    const container = document.getElementById('paymentDetailsContainer');
    if (!container) return;

    container.innerHTML = `
        <div class="space-y-6">
            <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Add Payment Method</h4>
            
            <form id="addPaymentForm" class="space-y-4">
                <!-- Payment Method Selection -->
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Payment Method</label>
                    <select name="payment_method" id="addPaymentMethod" required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none">
                        <option value="">Select Payment Method</option>
                        <option value="gcash">GCash</option>
                        <option value="paypal">PayPal</option>
                        <option value="paymaya">PayMaya</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>

                <!-- Dynamic fields based on payment method -->
                <div id="addPaymentFields">
                    <!-- Fields will be populated based on selected payment method -->
                </div>

                <!-- Account Name (common to all methods) -->
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Account Name</label>
                    <input type="text" name="account_name" required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                        placeholder="Enter your full name">
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="loadPaymentDetails()" 
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="bg-green-600 text-white font-semibold px-6 py-2 rounded-full hover:bg-green-700 transition-colors duration-200">
                        Add Payment Method
                    </button>
                </div>
            </form>
        </div>
    `;

    // Add event listener for payment method change
    const paymentMethodSelect = document.getElementById('addPaymentMethod');
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', updateAddPaymentFields);
    }

    // Add form submission handler
    const form = document.getElementById('addPaymentForm');
    if (form) {
        form.addEventListener('submit', handleAddPayment);
    }
}

function showPaymentSetupForm() {
    const container = document.getElementById('paymentDetailsContainer');
    if (!container) return;

    container.innerHTML = `
        <div class="space-y-6">
            <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Set Up Payment Information</h4>
            
            <form id="paymentSetupForm" class="space-y-4">
                <!-- Payment Method Selection -->
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Payment Method</label>
                    <select name="payment_method" id="paymentMethod" required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none">
                        <option value="">Select Payment Method</option>
                        <option value="gcash">GCash</option>
                        <option value="paypal">PayPal</option>
                        <option value="paymaya">PayMaya</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>

                <!-- Dynamic fields based on payment method -->
                <div id="paymentFields">
                    <!-- Fields will be populated based on selected payment method -->
                </div>

                <!-- Account Name (common to all methods) -->
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Account Name</label>
                    <input type="text" name="account_name" required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                        placeholder="Enter your full name">
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="loadPaymentDetails()" 
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="bg-[#F39C12] text-white font-semibold px-6 py-2 rounded-full hover:bg-[#D97706] transition-colors duration-200">
                        Save Payment Information
                    </button>
                </div>
            </form>
        </div>
    `;

    // Add event listener for payment method change
    const paymentMethodSelect = document.getElementById('paymentMethod');
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', updatePaymentFields);
    }

    // Add form submission handler
    const form = document.getElementById('paymentSetupForm');
    if (form) {
        form.addEventListener('submit', handlePaymentSetup);
    }
}

async function showEditPaymentForm() {
    const container = document.getElementById('paymentDetailsContainer');
    if (!container) return;

    // Show loading state
    container.innerHTML = `
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#F39C12] mx-auto"></div>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Loading payment information...</p>
        </div>
    `;

    try {
        // Fetch current payment information
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
        
        if (result.success && result.tutor_info && result.tutor_info.payment_info && result.tutor_info.payment_info.length > 0) {
            const paymentInfo = result.tutor_info.payment_info[0];
            
            container.innerHTML = `
                <div class="space-y-6">
                    <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Edit Payment Information</h4>
                    
                    <form id="editPaymentForm" class="space-y-4">
                        <!-- Payment Method Selection -->
                        <div>
                            <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Payment Method</label>
                            <select name="payment_method" id="editPaymentMethod" required
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none">
                                <option value="">Select Payment Method</option>
                                <option value="gcash">GCash</option>
                                <option value="paypal">PayPal</option>
                                <option value="paymaya">PayMaya</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>

                        <!-- Dynamic fields based on payment method -->
                        <div id="editPaymentFields">
                            <!-- Fields will be populated based on selected payment method -->
                        </div>

                        <!-- Account Name (common to all methods) -->
                        <div>
                            <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Account Name</label>
                            <input type="text" name="account_name" required
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                                placeholder="Enter your full name"
                                value="${paymentInfo.account_name || ''}">
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="loadPaymentDetails()" 
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                class="bg-[#F39C12] text-white font-semibold px-6 py-2 rounded-full hover:bg-[#D97706] transition-colors duration-200">
                                Update Payment Information
                            </button>
                        </div>
                    </form>
                </div>
            `;

            // Add event listener for payment method change
            const paymentMethodSelect = document.getElementById('editPaymentMethod');
            if (paymentMethodSelect) {
                paymentMethodSelect.addEventListener('change', updateEditPaymentFields);
            }

            // Add form submission handler
            const form = document.getElementById('editPaymentForm');
            if (form) {
                form.addEventListener('submit', handleEditPayment);
            }

            // Pre-populate fields with existing data
            populateEditFields(paymentInfo);
            
        } else {
            // No payment info found, show setup form instead
            showPaymentSetupForm();
        }
    } catch (error) {
        console.error('Error loading payment information:', error);
        container.innerHTML = `
            <div class="text-center py-8">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Error Loading Payment Information</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Unable to load payment information. Please try again.</p>
                <div class="mt-4">
                    <button onclick="loadPaymentDetails()" 
                            class="bg-[#F39C12] text-white font-semibold px-4 py-2 rounded-full hover:bg-[#D97706] transition-colors duration-200">
                        Try Again
                    </button>
                </div>
            </div>
        `;
    }
}

function updateEditPaymentFields() {
    const paymentMethod = document.getElementById('editPaymentMethod').value;
    const fieldsContainer = document.getElementById('editPaymentFields');
    
    if (!fieldsContainer) return;

    let fieldsHTML = '';

    switch (paymentMethod) {
        case 'gcash':
            fieldsHTML = `
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">GCash Number</label>
                    <input type="text" name="gcash_number" required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                        placeholder="Enter your GCash number">
                </div>
            `;
            break;
        case 'paypal':
            fieldsHTML = `
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">PayPal Email</label>
                    <input type="email" name="paypal_email" required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                        placeholder="Enter your PayPal email">
                </div>
            `;
            break;
        case 'paymaya':
            fieldsHTML = `
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">PayMaya Number</label>
                    <input type="text" name="paymaya_number" required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                        placeholder="Enter your PayMaya number">
                </div>
            `;
            break;
        case 'bank_transfer':
            fieldsHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Bank Name</label>
                        <input type="text" name="bank_name" required
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                            placeholder="Enter bank name">
                    </div>
                    <div>
                        <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Account Number</label>
                        <input type="text" name="account_number" required
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                            placeholder="Enter account number">
                    </div>
                </div>
            `;
            break;
        case 'cash':
            fieldsHTML = `
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <p>Cash payments will be handled directly with the administrator.</p>
                </div>
            `;
            break;
    }

    fieldsContainer.innerHTML = fieldsHTML;
}

function populateEditFields(paymentInfo) {
    // Store the payment info globally so it can be accessed when payment method is selected
    window.currentPaymentInfo = paymentInfo;
    
    // Add event listener to payment method dropdown to populate fields when selected
    const paymentMethodSelect = document.getElementById('editPaymentMethod');
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            const selectedMethod = this.value;
            if (selectedMethod && window.currentPaymentInfo) {
                populateFieldsForMethod(selectedMethod, window.currentPaymentInfo);
            }
        });
    }
}

function populateFieldsForMethod(paymentMethod, paymentInfo) {
    // Update the fields container with the selected payment method's fields
    updateEditPaymentFields();
    
    // Wait a moment for the fields to be rendered, then populate them
    setTimeout(() => {
        // Pre-populate account name
        const accountNameField = document.querySelector('input[name="account_name"]');
        if (accountNameField) {
            accountNameField.value = paymentInfo.account_name || '';
        }
        
        // Pre-populate method-specific fields
        switch (paymentMethod) {
            case 'gcash':
                const gcashNumberField = document.querySelector('input[name="gcash_number"]');
                if (gcashNumberField) {
                    gcashNumberField.value = paymentInfo.gcash_number || '';
                }
                break;
            case 'paypal':
                const paypalEmailField = document.querySelector('input[name="paypal_email"]');
                if (paypalEmailField) {
                    paypalEmailField.value = paymentInfo.paypal_email || '';
                }
                break;
            case 'paymaya':
                const paymayaNumberField = document.querySelector('input[name="paymaya_number"]');
                if (paymayaNumberField) {
                    paymayaNumberField.value = paymentInfo.paymaya_number || '';
                }
                break;
            case 'bank_transfer':
                const bankNameField = document.querySelector('input[name="bank_name"]');
                const accountNumberField = document.querySelector('input[name="account_number"]');
                if (bankNameField) {
                    bankNameField.value = paymentInfo.bank_name || '';
                }
                if (accountNumberField) {
                    accountNumberField.value = paymentInfo.account_number || '';
                }
                break;
            case 'cash':
                // Cash doesn't have additional fields to populate
                break;
        }
    }, 200);
}

async function handleEditPayment(event) {
    event.preventDefault();
    
    // Show confirmation modal
    showConfirmationModal(
        'Update Payment Information',
        'Are you sure you want to update your payment information? This will replace your existing payment details.',
        'Update Payment',
        'Cancel',
        proceedWithEditPayment
    );
}

async function proceedWithEditPayment() {
    const form = document.getElementById('editPaymentForm');
    const formData = new FormData(form);
    const paymentData = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('/tutor/setup-payment', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(paymentData)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Payment information updated successfully!', 'success');
            loadPaymentDetails(); // Reload the payment details
        } else {
            showNotification(result.message || 'Failed to update payment information. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error updating payment information:', error);
        showNotification('An error occurred while updating your payment information. Please try again.', 'error');
    }
}

function updateAddPaymentFields() {
    const paymentMethod = document.getElementById('addPaymentMethod').value;
    const fieldsContainer = document.getElementById('addPaymentFields');
    
    if (!fieldsContainer) return;

    let fieldsHTML = '';

    switch (paymentMethod) {
        case 'gcash':
            fieldsHTML = `
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">GCash Number</label>
                    <input type="text" name="gcash_number" required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                        placeholder="Enter your GCash number">
                </div>
            `;
            break;
        case 'paypal':
            fieldsHTML = `
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">PayPal Email</label>
                    <input type="email" name="paypal_email" required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                        placeholder="Enter your PayPal email">
                </div>
            `;
            break;
        case 'paymaya':
            fieldsHTML = `
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">PayMaya Number</label>
                    <input type="text" name="paymaya_number" required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                        placeholder="Enter your PayMaya number">
                </div>
            `;
            break;
        case 'bank_transfer':
            fieldsHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Bank Name</label>
                        <input type="text" name="bank_name" required
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                            placeholder="Enter bank name">
                    </div>
                    <div>
                        <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Account Number</label>
                        <input type="text" name="account_number" required
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                            placeholder="Enter account number">
                    </div>
                </div>
            `;
            break;
        case 'cash':
            fieldsHTML = `
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <p>Cash payments will be handled directly with the administrator.</p>
                </div>
            `;
            break;
    }

    fieldsContainer.innerHTML = fieldsHTML;
}

async function handleAddPayment(event) {
    event.preventDefault();
    
    // Show confirmation modal
    showConfirmationModal(
        'Add Payment Method',
        'Are you sure you want to add this payment method? This will be added to your payment options.',
        'Add Payment Method',
        'Cancel',
        proceedWithAddPayment
    );
}

async function proceedWithAddPayment() {
    const form = document.getElementById('addPaymentForm');
    const formData = new FormData(form);
    const paymentData = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('/tutor/setup-payment', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(paymentData)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Payment method added successfully!', 'success');
            loadPaymentDetails(); // Reload the payment details
        } else {
            showNotification(result.message || 'Failed to add payment method. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error adding payment method:', error);
        showNotification('An error occurred while adding your payment method. Please try again.', 'error');
    }
}

function updatePaymentFields() {
    const paymentMethod = document.getElementById('paymentMethod').value;
    const fieldsContainer = document.getElementById('paymentFields');
    
    if (!fieldsContainer) return;

    let fieldsHTML = '';

    switch (paymentMethod) {
        case 'gcash':
            fieldsHTML = `
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">GCash Number</label>
                    <input type="text" name="gcash_number" required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                        placeholder="Enter your GCash number">
                </div>
            `;
            break;
        case 'paypal':
            fieldsHTML = `
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">PayPal Email</label>
                    <input type="email" name="paypal_email" required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                        placeholder="Enter your PayPal email">
                </div>
            `;
            break;
        case 'paymaya':
            fieldsHTML = `
                <div>
                    <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">PayMaya Number</label>
                    <input type="text" name="paymaya_number" required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                        placeholder="Enter your PayMaya number">
                </div>
            `;
            break;
        case 'bank_transfer':
            fieldsHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Bank Name</label>
                        <input type="text" name="bank_name" required
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                            placeholder="Enter bank name">
                    </div>
                    <div>
                        <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Account Number</label>
                        <input type="text" name="account_number" required
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#F39C12] focus:outline-none"
                            placeholder="Enter account number">
                    </div>
                </div>
            `;
            break;
        case 'cash':
            fieldsHTML = `
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <p>Cash payments will be handled directly with the administrator.</p>
                </div>
            `;
            break;
    }

    fieldsContainer.innerHTML = fieldsHTML;
}

async function handlePaymentSetup(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const paymentData = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('/tutor/setup-payment', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(paymentData)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Payment information saved successfully!', 'success');
            loadPaymentDetails(); // Reload the payment details
        } else {
            showNotification(result.message || 'Failed to save payment information. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error saving payment information:', error);
        showNotification('An error occurred while saving your payment information. Please try again.', 'error');
    }
}

// Toast notification function
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.toast-notification');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'toast-notification fixed bottom-4 right-4 z-50 max-w-sm w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 transform translate-x-full transition-transform duration-300 ease-in-out';
    
    // Set icon and colors based on type
    let icon, bgColor, textColor, borderColor;
    switch (type) {
        case 'success':
            icon = 'fas fa-check-circle';
            bgColor = 'bg-green-50 dark:bg-green-900';
            textColor = 'text-green-800 dark:text-green-200';
            borderColor = 'border-green-200 dark:border-green-700';
            break;
        case 'error':
            icon = 'fas fa-exclamation-circle';
            bgColor = 'bg-red-50 dark:bg-red-900';
            textColor = 'text-red-800 dark:text-red-200';
            borderColor = 'border-red-200 dark:border-red-700';
            break;
        case 'warning':
            icon = 'fas fa-exclamation-triangle';
            bgColor = 'bg-yellow-50 dark:bg-yellow-900';
            textColor = 'text-yellow-800 dark:text-yellow-200';
            borderColor = 'border-yellow-200 dark:border-yellow-700';
            break;
        default:
            icon = 'fas fa-info-circle';
            bgColor = 'bg-blue-50 dark:bg-blue-900';
            textColor = 'text-blue-800 dark:text-blue-200';
            borderColor = 'border-blue-200 dark:border-blue-700';
    }

    notification.innerHTML = `
        <div class="p-4 ${bgColor} ${textColor} ${borderColor} border-l-4 rounded-lg">
            <div class="flex items-center">
                <i class="${icon} mr-3 text-lg"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="ml-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;

    // Add to page
    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

// Modal confirmation function
function showConfirmationModal(title, message, confirmText, cancelText, onConfirm) {
    // Remove existing modal if any
    const existingModal = document.getElementById('confirmationModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Create modal
    const modal = document.createElement('div');
    modal.id = 'confirmationModal';
    modal.className = 'fixed inset-0 z-50 overflow-y-auto';
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen px-4 py-4">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeConfirmationModal()"></div>
            <div class="relative w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                    <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">${title}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">${message}</p>
                    <div class="flex justify-center space-x-3">
                        <button onclick="closeConfirmationModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md transition-colors">
                            ${cancelText}
                        </button>
                        <button id="confirmModalBtn" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors">
                            ${confirmText}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Add to page
    document.body.appendChild(modal);

    // Add event listener to confirm button
    document.getElementById('confirmModalBtn').addEventListener('click', () => {
        closeConfirmationModal();
        if (onConfirm) {
            onConfirm();
        }
    });
}

function closeConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    if (modal) {
        modal.remove();
    }
}