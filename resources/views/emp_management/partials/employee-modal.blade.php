<!-- Employee Details Modal -->
<x-modal name="employee-details" maxWidth="6xl">
    <div class="p-0 bg-white">
        <!-- Loading State -->
        <div id="modal-loading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="text-gray-600 mt-4">Loading employee details...</p>
        </div>

        <!-- Modal Content -->
        <div id="modal-content" class="hidden">
            <!-- Tutor Portal Style Modal -->
            <div id="tutor-modal" class="hidden">
                <!-- Header -->
                <div class="bg-white border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-[#0E335D]">Tutor Details</h2>
                        <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeEmployeeModal()">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Profile Overview -->
                <div class="bg-gradient-to-r from-blue-50 via-blue-100 to-blue-50 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center space-x-4">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=96&h=96&fit=crop&crop=face"
                                alt="Profile" class="w-20 h-20 rounded-full object-cover border-2 border-blue-400">
                            <div>
                                <h2 class="text-lg font-semibold text-[#0E335D]" id="tutor-name">Tutor Name</h2>
                                <p class="text-gray-600 text-sm" id="tutor-email">tutor@email.com</p>
                                <p class="text-gray-500 text-xs" id="tutor-id">Tutor ID: N/A</p>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 text-left md:text-right w-full md:w-40">
                            <label class="block text-sm text-gray-600 mb-1">Status:</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" id="tutor-status-badge">
                                Active
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white border-b border-gray-200 px-6">
                    <nav class="flex space-x-8">
                        <button onclick="showTutorTab('personal')" id="tutor-personal-tab"
                            class="py-4 px-1 border-b-2 border-blue-600 font-medium text-sm text-blue-600">
                            <i class="fas fa-user mr-2"></i>Personal Info
                        </button>
                        <button onclick="showTutorTab('payment')" id="tutor-payment-tab"
                            class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700">
                            <i class="fas fa-credit-card mr-2"></i>Payment Info
                        </button>
                        <button onclick="showTutorTab('availability')" id="tutor-availability-tab"
                            class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700">
                            <i class="fas fa-calendar-alt mr-2"></i>Work Availability
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6 bg-gray-50">
                    <!-- Personal Info Tab -->
                    <div id="tutor-personal-content" class="space-y-6">
                        <h3 class="font-semibold text-gray-700 mb-2">Personal Information</h3>
                        <div class="bg-white rounded-lg shadow-md p-6 space-y-4 border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="tutor-personal-info">
                                <!-- Dynamic content will be inserted here -->
                            </div>
                        </div>

                        <!-- Additional Details -->
                        <h3 class="font-semibold text-gray-700 mb-2">Additional Details</h3>
                        <div class="bg-white rounded-lg shadow-md p-6 space-y-4 border border-gray-200" id="tutor-additional-info">
                            <!-- Dynamic content will be inserted here -->
                        </div>
                    </div>

                    <!-- Payment Info Tab -->
                    <div id="tutor-payment-content" class="hidden space-y-6">
                        <h3 class="font-semibold text-gray-700 mb-2">Payment Information</h3>
                        <div class="bg-white rounded-lg shadow-md p-6 space-y-4 border border-gray-200" id="tutor-payment-info">
                            <!-- Dynamic content will be inserted here -->
                        </div>
                    </div>

                    <!-- Work Availability Tab -->
                    <div id="tutor-availability-content" class="hidden space-y-6">
                        <h3 class="font-semibold text-gray-700 mb-2">Work Availability</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="tutor-availability-info">
                            <!-- Dynamic content will be inserted here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supervisor Profile Style Modal -->
            <div id="supervisor-modal" class="hidden">
                <!-- Header -->
                <div class="bg-white border-b border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-[#0E335D]">Supervisor Details</h2>
                        <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeEmployeeModal()">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Profile Overview -->
                <div class="bg-gradient-to-r from-[#BCE6D4] to-[#9DC9FD] p-6">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between space-y-4 md:space-y-0">
                        <!-- Profile Info -->
                        <div class="flex items-center space-x-4 md:space-x-6">
                            <div class="relative">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face"
                                    alt="Profile" class="w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 rounded-full object-cover border-4 border-white shadow-md">
                            </div>
                            <div class="text-left">
                                <h2 class="text-lg sm:text-xl md:text-2xl font-semibold text-[#0E335D]" id="supervisor-name">Supervisor Name</h2>
                                <p class="text-xs sm:text-sm md:text-base text-[#0E335D]" id="supervisor-email">supervisor@email.com</p>
                            </div>
                        </div>

                        <!-- Assigned Role -->
                        <div class="mt-2 md:mt-0 text-left md:text-right">
                            <p class="text-xs sm:text-sm md:text-sm font-medium text-[#0E335D]">Assigned Role</p>
                            <h3 class="text-sm sm:text-base md:text-lg font-semibold text-[#0E335D]" id="supervisor-role">
                                Supervisor
                            </h3>
                            <p class="text-xs text-gray-500 mt-1" id="supervisor-account-info">Managing tutors</p>
                        </div>
                    </div>
                </div>

                <!-- Content Sections -->
                <div class="p-6 bg-gray-50 space-y-6">
                    <!-- Personal Information -->
                    <div class="bg-white shadow-md rounded-xl p-6 space-y-4">
                        <h3 class="text-base sm:text-lg md:text-xl font-semibold text-[#0E335D] border-b-2 border-[#0E335D] pb-2">
                            Personal Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="supervisor-personal-info">
                            <!-- Dynamic content will be inserted here -->
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="bg-white shadow-md rounded-xl p-6 space-y-4">
                        <h3 class="text-base sm:text-lg md:text-xl font-semibold text-[#0E335D] border-b-2 border-[#0E335D] pb-2">
                            Payment Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="supervisor-payment-info">
                            <!-- Dynamic content will be inserted here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-modal>

<script>
let currentTutorData = null;
let currentSupervisorData = null;

function openEmployeeModal(type, id) {
    // Show modal
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'employee-details' }));
    
    // Show loading state
    document.getElementById('modal-loading').classList.remove('hidden');
    document.getElementById('modal-content').classList.add('hidden');
    
    // Hide both modals
    document.getElementById('tutor-modal').classList.add('hidden');
    document.getElementById('supervisor-modal').classList.add('hidden');
    
    // Fetch employee data
    const url = type === 'tutor' ? `/employees/tutor/${id}` : `/employees/supervisor/${id}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (type === 'tutor') {
                    currentTutorData = data.data;
                    populateTutorModal(data.data);
                } else {
                    currentSupervisorData = data.data;
                    populateSupervisorModal(data.data);
                }
            } else {
                alert('Failed to load employee details');
                closeEmployeeModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while loading employee details');
            closeEmployeeModal();
        });
}

function closeEmployeeModal() {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'employee-details' }));
}

function populateTutorModal(data) {
    // Hide loading, show content
    document.getElementById('modal-loading').classList.add('hidden');
    document.getElementById('modal-content').classList.remove('hidden');
    document.getElementById('tutor-modal').classList.remove('hidden');
    
    // Update header
    document.getElementById('tutor-name').textContent = data.full_name || 'N/A';
    document.getElementById('tutor-email').textContent = data.email || 'N/A';
    document.getElementById('tutor-id').textContent = `Tutor ID: ${data.id || 'N/A'}`;
    
    // Update status badge
    const statusElement = document.getElementById('tutor-status-badge');
    statusElement.textContent = data.status ? data.status.charAt(0).toUpperCase() + data.status.slice(1) : 'N/A';
    statusElement.className = `inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${
        data.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
    }`;
    
    // Populate personal info
    const personalInfo = document.getElementById('tutor-personal-info');
    personalInfo.innerHTML = `
        <div>
            <label class="block text-sm text-gray-600 mb-1">First Name</label>
            <input type="text" value="${data.first_name || ''}" readonly
                class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Last Name</label>
            <input type="text" value="${data.last_name || ''}" readonly
                class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Date of Birth</label>
            <input type="date" value="${data.date_of_birth || ''}" readonly
                class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Email</label>
            <input type="email" value="${data.email || ''}" readonly
                class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Phone Number</label>
            <input type="text" value="${data.phone_number || ''}" readonly
                class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Date Hired</label>
            <input type="text" value="${data.created_at || 'N/A'}" readonly
                class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
        </div>
    `;
    
    // Populate additional info
    const additionalInfo = document.getElementById('tutor-additional-info');
    if (data.tutor_details) {
        const details = data.tutor_details;
        additionalInfo.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Address</label>
                    <input type="text" value="${details.address || ''}" readonly
                        class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Educational Attainment</label>
                    <input type="text" value="${details.educational_attainment || ''}" readonly
                        class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">ESL Teaching Experience</label>
                    <input type="text" value="${details.esl_teaching_experience || ''}" readonly
                        class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Work Setup</label>
                    <input type="text" value="${details.work_setup || ''}" readonly
                        class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">First Day of Teaching</label>
                    <input type="date" value="${details.first_day_of_teaching || ''}" readonly
                        class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
                </div>
            </div>
        `;
    } else {
        additionalInfo.innerHTML = '<p class="text-gray-500 italic">No additional details available</p>';
    }
    
    // Populate payment info - show all available payment methods
    const paymentInfo = document.getElementById('tutor-payment-info');
    if (data.payment_information) {
        const payment = data.payment_information;
        let paymentHtml = '<div class="space-y-6">';
        
        // Check for Bank Transfer details
        if (payment.bank_name || payment.account_number || payment.account_name) {
            paymentHtml += `
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-lg font-semibold text-blue-800 mb-3 flex items-center">
                        <i class="fas fa-university mr-2"></i>Bank Transfer
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Bank Name</label>
                            <input type="text" value="${payment.bank_name || 'N/A'}" readonly
                                class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Account Number</label>
                            <input type="text" value="${payment.account_number || 'N/A'}" readonly
                                class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm text-gray-600 mb-1">Account Name</label>
                            <input type="text" value="${payment.account_name || 'N/A'}" readonly
                                class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Check for GCash details
        if (payment.gcash_number) {
            paymentHtml += `
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h4 class="text-lg font-semibold text-green-800 mb-3 flex items-center">
                        <i class="fas fa-mobile-alt mr-2"></i>GCash
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">GCash Number</label>
                            <input type="text" value="${payment.gcash_number || 'N/A'}" readonly
                                class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Account Name</label>
                            <input type="text" value="${payment.account_name || 'N/A'}" readonly
                                class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Check for PayPal details
        if (payment.paypal_email) {
            paymentHtml += `
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="text-lg font-semibold text-yellow-800 mb-3 flex items-center">
                        <i class="fab fa-paypal mr-2"></i>PayPal
                    </h4>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">PayPal Email</label>
                        <input type="email" value="${payment.paypal_email || 'N/A'}" readonly
                            class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
                    </div>
                </div>
            `;
        }
        
        // Check for PayMaya details
        if (payment.paymaya_number) {
            paymentHtml += `
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h4 class="text-lg font-semibold text-purple-800 mb-3 flex items-center">
                        <i class="fas fa-credit-card mr-2"></i>PayMaya
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">PayMaya Number</label>
                            <input type="text" value="${payment.paymaya_number || 'N/A'}" readonly
                                class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Account Name</label>
                            <input type="text" value="${payment.account_name || 'N/A'}" readonly
                                class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Check if no payment methods are available
        if (!payment.bank_name && !payment.gcash_number && !payment.paypal_email && !payment.paymaya_number) {
            paymentHtml += '<p class="text-gray-500 italic text-center py-8">No payment information available</p>';
        }
        
        paymentHtml += '</div>';
        paymentInfo.innerHTML = paymentHtml;
    } else {
        paymentInfo.innerHTML = '<p class="text-gray-500 italic text-center py-8">No payment information available</p>';
    }
    
    // Populate work availability info
    const availabilityInfo = document.getElementById('tutor-availability-info');
    if (data.accounts && data.accounts.length > 0) {
        availabilityInfo.innerHTML = data.accounts.map(account => `
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-gray-800">${account.account_name}</h4>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                        account.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    }">
                        ${account.status.charAt(0).toUpperCase() + account.status.slice(1)}
                    </span>
                </div>
                <div class="space-y-3">
                    ${account.account_name === 'GLS' && account.gls_id ? `
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">GLS ID</label>
                            <input type="text" value="${account.gls_id}" readonly
                                class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
                        </div>
                    ` : ''}
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Work Availability</label>
                        <button type="button" onclick="toggleAvailability('${account.id}-${account.account_name}')" 
                            class="w-full text-left border border-gray-300 rounded-md px-3 py-2 bg-gray-50 text-gray-900 hover:bg-gray-100 transition-colors flex items-center justify-between">
                            <span id="availability-summary-${account.id}-${account.account_name}">${getAvailabilitySummary(account)}</span>
                            <i class="fas fa-chevron-down transition-transform duration-200" id="availability-arrow-${account.id}-${account.account_name}"></i>
                        </button>
                        <div id="availability-details-${account.id}-${account.account_name}" class="hidden mt-2 border border-gray-200 rounded-md bg-white shadow-sm">
                            <div class="p-3">
                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Availability Details</div>
                                <div class="space-y-2">
                                    ${getAvailabilityList(account)}
                                </div>
                            </div>
                        </div>
                    </div>
                    ${account.account_name === 'Talk915' && account.ms_teams_id ? `
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">MS Teams ID</label>
                            <input type="text" value="${account.ms_teams_id}" readonly
                                class="border border-gray-300 rounded-md px-3 py-2 w-full bg-gray-50 text-gray-900">
                        </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
    } else {
        availabilityInfo.innerHTML = '<p class="text-gray-500 italic">No availability information available</p>';
    }
    
    // Show personal tab by default
    showTutorTab('personal');
}

function populateSupervisorModal(data) {
    // Hide loading, show content
    document.getElementById('modal-loading').classList.add('hidden');
    document.getElementById('modal-content').classList.remove('hidden');
    document.getElementById('supervisor-modal').classList.remove('hidden');
    
    // Update header
    document.getElementById('supervisor-name').textContent = data.full_name || 'N/A';
    document.getElementById('supervisor-email').textContent = data.email || 'N/A';
    
    // Update role
    const roleElement = document.getElementById('supervisor-role');
    const accountInfoElement = document.getElementById('supervisor-account-info');
    if (data.assigned_account) {
        roleElement.textContent = `${data.assigned_account} Supervisor`;
        accountInfoElement.textContent = `Managing ${data.assigned_account} tutors`;
    } else {
        roleElement.textContent = 'Supervisor';
        accountInfoElement.textContent = 'Managing tutors';
    }
    
    // Populate personal info
    const personalInfo = document.getElementById('supervisor-personal-info');
    personalInfo.innerHTML = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" value="${data.full_name || 'N/A'}" readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" value="${data.email || 'N/A'}" readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input type="text" value="${data.phone_number || 'N/A'}" readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
            </div>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Birth Date</label>
                <input type="date" value="${data.birth_date || ''}" readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Hired</label>
                <input type="text" value="${data.created_at || 'N/A'}" readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${
                    data.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                }">
                    ${data.status ? data.status.charAt(0).toUpperCase() + data.status.slice(1) : 'N/A'}
                </span>
            </div>
        </div>
    `;
    
    // Populate payment info
    const paymentInfo = document.getElementById('supervisor-payment-info');
    paymentInfo.innerHTML = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Account</label>
                <input type="text" value="${data.assigned_account || 'Unassigned'}" readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Role</label>
                <input type="text" value="${data.role || 'N/A'}" readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
                <input type="text" value="${data.shift || 'N/A'}" readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
            </div>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">MS Teams Account</label>
                <input type="text" value="${data.ms_teams || 'N/A'}" readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Updated</label>
                <input type="text" value="${data.updated_at || 'N/A'}" readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supervisor ID</label>
                <input type="text" value="${data.id || 'N/A'}" readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
            </div>
        </div>
    `;
}

function showTutorTab(tabName) {
    // Hide all tab contents
    document.getElementById('tutor-personal-content').classList.add('hidden');
    document.getElementById('tutor-payment-content').classList.add('hidden');
    document.getElementById('tutor-availability-content').classList.add('hidden');
    
    // Remove active class from all tabs
    document.getElementById('tutor-personal-tab').className = 'py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700';
    document.getElementById('tutor-payment-tab').className = 'py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700';
    document.getElementById('tutor-availability-tab').className = 'py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700';
    
    // Show selected tab content
    document.getElementById(`tutor-${tabName}-content`).classList.remove('hidden');
    
    // Add active class to selected tab
    document.getElementById(`tutor-${tabName}-tab`).className = 'py-4 px-1 border-b-2 border-[#0E335D] font-medium text-sm text-[#0E335D]';
}

// Toggle supervisor status (active/inactive)
function toggleSupervisorStatus(supervisorId, newStatus) {
    if (!supervisorId) {
        alert('Error: Supervisor ID not found');
        return;
    }

    // Show confirmation dialog
    const action = newStatus === 'active' ? 'activate' : 'deactivate';
    if (!confirm(`Are you sure you want to ${action} this supervisor?`)) {
        return;
    }

    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';

    // Make AJAX request
    fetch(`/supervisors/${supervisorId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert(data.message);
            
            // Reload the page to reflect changes
            window.location.reload();
        } else {
            // Show error message
            alert(data.message || 'Failed to update supervisor status');
            
            // Restore button state
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating supervisor status');
        
        // Restore button state
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Helper functions for availability display
function getAvailabilitySummary(account) {
    const availableTime = account.formatted_available_time || 'N/A';
    const availableDays = account.formatted_available_days || 'N/A';
    
    if (availableTime === 'N/A' && availableDays === 'N/A') {
        return 'No availability information';
    }
    
    // Count available entries
    let count = 0;
    if (availableTime !== 'N/A') {
        count += availableTime.split(', ').length;
    }
    if (availableDays !== 'N/A') {
        count += availableDays.split(', ').length;
    }
    
    return `${count} availability entries - Click to view details`;
}

function getAvailabilityList(account) {
    const availableTime = account.formatted_available_time || 'N/A';
    const availableDays = account.formatted_available_days || 'N/A';
    
    let html = '';
    
    if (availableTime !== 'N/A') {
        const timeEntries = availableTime.split(', ');
        timeEntries.forEach(entry => {
            html += `<div class="flex items-center py-1 px-2 bg-blue-50 rounded text-sm">
                        <i class="fas fa-clock text-blue-500 mr-2"></i>
                        <span class="text-gray-700">${entry.trim()}</span>
                     </div>`;
        });
    }
    
    if (availableDays !== 'N/A') {
        const dayEntries = availableDays.split(', ');
        dayEntries.forEach(entry => {
            html += `<div class="flex items-center py-1 px-2 bg-green-50 rounded text-sm">
                        <i class="fas fa-calendar text-green-500 mr-2"></i>
                        <span class="text-gray-700">${entry.trim()}</span>
                     </div>`;
        });
    }
    
    if (!html) {
        html = '<div class="text-gray-500 italic text-sm">No availability information</div>';
    }
    
    return html;
}

// Toggle availability dropdown
function toggleAvailability(uniqueId) {
    const details = document.getElementById(`availability-details-${uniqueId}`);
    const arrow = document.getElementById(`availability-arrow-${uniqueId}`);
    
    if (details && arrow) {
        const isOpen = !details.classList.contains('hidden');
        
        if (isOpen) {
            // Close this dropdown
            details.classList.add('hidden');
            arrow.style.transform = 'rotate(0deg)';
        } else {
            // Close all other availability dropdowns first
            document.querySelectorAll('[id^="availability-details-"]').forEach(dropdown => {
                if (dropdown.id !== `availability-details-${uniqueId}`) {
                    dropdown.classList.add('hidden');
                }
            });
            document.querySelectorAll('[id^="availability-arrow-"]').forEach(arrowEl => {
                if (arrowEl.id !== `availability-arrow-${uniqueId}`) {
                    arrowEl.style.transform = 'rotate(0deg)';
                }
            });
            
            // Open this dropdown
            details.classList.remove('hidden');
            arrow.style.transform = 'rotate(180deg)';
        }
    }
}
</script>
