<!-- Employee Details Modal -->
<x-modal name="employee-details" maxWidth="6xl">
    <div class="p-0 bg-white">
        <!-- Loading State -->
        <div id="modal-loading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="text-gray-500 mt-4">Loading employee details...</p>
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
                <div class="bg-gradient-to-r from-blue-100 via-green-100 to-green-200 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center space-x-4">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=96&h=96&fit=crop&crop=face"
                                alt="Profile" class="w-20 h-20 rounded-full object-cover border-2 border-[#0E335D]">
                            <div>
                                <h2 class="text-lg font-semibold text-[#0E335D]" id="tutor-name">Tutor Name</h2>
                                <p class="text-gray-600 text-xs" id="tutor-id">Tutor ID: N/A</p>
                                <p class="text-gray-700 text-sm" id="tutor-username">@username</p>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <h2 class="text-gray-700 text-sm font-bold text-right" id="tutor-name">Status</h2>
                            <span id="tutor-status-badge" class="inline-flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                                <span class="text-xs font-medium text-gray-700">N/A</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white border-b border-gray-200 px-6">
                    <nav class="flex space-x-8">
                        <button onclick="showTutorTab('personal')" id="tutor-personal-tab"
                            class="py-4 px-1 border-b-2 border-[#0E335D] font-medium text-sm text-[#0E335D]">
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
                        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200" id="tutor-additional-info">
                            <!-- Dynamic content will be inserted here -->
                        </div>
                    </div>

                    <!-- Payment Info Tab -->
                    <div id="tutor-payment-content" class="hidden space-y-6">
                        <h3 class="font-semibold text-gray-700 mb-2">Payment Information</h3>
                        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200" id="tutor-payment-info">
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
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center space-x-4">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=96&h=96&fit=crop&crop=face"
                                alt="Profile" class="w-20 h-20 rounded-full object-cover border-2 border-[#0E335D]">
                            <div>
                                <h2 class="text-lg font-semibold text-[#0E335D]" id="supervisor-name">Supervisor Name</h2>
                                <p class="text-gray-600 text-xs" id="supervisor-id">Supervisor ID: N/A</p>
                                <p class="text-gray-700 text-sm" id="supervisor-role-title">Supervisor</p>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <h2 class="text-gray-700 text-sm font-bold text-right">Status</h2>
                            <span id="supervisor-status-badge" class="inline-flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                                <span class="text-xs font-medium text-gray-700">N/A</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white border-b border-gray-200 px-6">
                    <nav class="flex space-x-8">
                        <button onclick="showSupervisorTab('personal')" id="supervisor-personal-tab"
                            class="py-4 px-1 border-b-2 border-[#0E335D] font-medium text-sm text-[#0E335D]">
                            <i class="fas fa-user mr-2"></i>Personal Info
                        </button>
                        <button onclick="showSupervisorTab('payment')" id="supervisor-payment-tab"
                            class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700">
                            <i class="fas fa-briefcase mr-2"></i>Work Info
                        </button>
                        <button onclick="showSupervisorTab('availability')" id="supervisor-availability-tab"
                            class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700">
                            <i class="fas fa-calendar-alt mr-2"></i>Work Schedule
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6 bg-gray-50">
                    <!-- Personal Info Tab -->
                    <div id="supervisor-personal-content" class="space-y-6">
                        <h3 class="font-semibold text-gray-700 mb-2">Personal Information</h3>
                        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="supervisor-personal-info">
                                <!-- Dynamic content will be inserted here -->
                            </div>
                        </div>
                    </div>

                    <!-- Work Info Tab -->
                    <div id="supervisor-payment-content" class="hidden space-y-6">
                        <h3 class="font-semibold text-gray-700 mb-2">Work Information</h3>
                        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="supervisor-payment-info">
                                <!-- Dynamic content will be inserted here -->
                            </div>
                        </div>
                    </div>

                    <!-- Work Schedule Tab -->
                    <div id="supervisor-availability-content" class="hidden space-y-6">
                        <h3 class="font-semibold text-gray-700 mb-2">Work Schedule</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="supervisor-availability-info">
                            <!-- Dynamic content will be inserted here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Archive Modal - Outside modal-content so it displays when modal-content is hidden -->
        <div id="archive-modal" class="hidden">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-[#0E335D]">Archive Employee</h2>
                    <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeArchiveModal()">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Archive Form -->
            <div class="p-6 bg-gray-50 max-h-[calc(100vh-200px)] overflow-y-auto">
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 space-y-6">
                    <!-- Employee Info Display -->
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Employee Being Archived</p>
                        <p class="text-lg font-semibold text-gray-900" id="archive-employee-name">N/A</p>
                    </div>

                    <!-- Archive Reason -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Archive <span class="text-red-500">*</span></label>
                        <textarea id="archive-reason" 
                                placeholder="Please provide the reason why this employee is being archived..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 
                                       focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50
                                       resize-none" rows="4"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>
                    </div>

                    <!-- Archived By (Auto-filled) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Archived By</label>
                        <input type="text" id="archive-by" 
                               readonly
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-900 
                                      focus:outline-none cursor-default">
                        <p class="text-xs text-gray-500 mt-1">Auto-filled with current user</p>
                    </div>

                    <!-- Additional Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                        <textarea id="archive-notes" 
                                placeholder="Any additional notes or information about the archival (optional)..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 
                                       focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50
                                       resize-none" rows="3"></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 justify-end pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeArchiveModal()"
                                class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 
                                       transition-colors font-medium">
                            Cancel
                        </button>
                        <button type="button" onclick="submitArchive()"
                                class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 
                                       transition-colors font-medium flex items-center gap-2">
                            <i class="fas fa-archive"></i>
                            Archive Employee
                        </button>
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
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data); // Debug log
            if (data.success) {
                if (type === 'tutor') {
                    currentTutorData = data.data;
                    populateTutorModal(data.data);
                } else {
                    currentSupervisorData = data.data;
                    populateSupervisorModal(data.data);
                }
            } else {
                console.error('Server error:', data.message || 'Unknown error');
                alert('Failed to load employee details: ' + (data.message || 'Unknown error'));
                closeEmployeeModal();
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('An error occurred while loading employee details: ' + error.message);
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
    document.getElementById('tutor-username').textContent = '@' + (data.username || 'N/A');
    document.getElementById('tutor-id').textContent = `Tutor ID: ${data.tutorID || 'N/A'}`;
    
    // Update status badge
    const statusElement = document.getElementById('tutor-status-badge');
    const statusText = data.status ? data.status.charAt(0).toUpperCase() + data.status.slice(1) : 'N/A';
    const circleColor = data.status === 'active' ? 'bg-[#65DB7F]' : 'bg-[#F65353]';
    
    statusElement.innerHTML = `
        <span class="w-2.5 h-2.5 rounded-full ${circleColor}"></span>
        <span class="text-xs font-medium text-gray-700">${statusText}</span>
    `;
    statusElement.className = 'inline-flex items-center gap-2';
    
    // Populate personal info
    const personalInfo = document.getElementById('tutor-personal-info');
    personalInfo.innerHTML = `
        <div>
            <label class="block text-sm text-gray-600 mb-1">First Name</label>
            <input type="text" 
            value="${data.first_name || ''}" 
            readonly
            class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Middle Name</label>
            <input type="text" value="${data.middle_name || 'N/A'}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Last Name</label>
            <input type="text" value="${data.last_name || ''}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Date of Birth</label>
            <input type="text" value="${data.date_of_birth || 'N/A'}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Personal Email</label>
            <input type="email" value="${data.personal_email || 'N/A'}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Company Email</label>
            <input type="email" value="${data.email || 'N/A'}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Phone Number</label>
            <input type="text" value="${data.phone_number || ''}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Date Hired</label>
            <input type="text" value="${data.created_at || 'N/A'}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
    `;
    
    // Populate additional info in 3-column grid
    const additionalInfo = document.getElementById('tutor-additional-info');
    if (data.tutor_details) {
        const details = data.tutor_details;
        additionalInfo.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Address</label>
                    <input type="text" value="${details.address || ''}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                        focus:outline-none focus:ring-0 cursor-default">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Educational Attainment</label>
                    <input type="text" value="${details.educational_attainment || ''}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                        focus:outline-none focus:ring-0 cursor-default">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">ESL Teaching Experience</label>
                    <input type="text" value="${details.esl_teaching_experience || ''}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                        focus:outline-none focus:ring-0 cursor-default">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Work Setup</label>
                    <input type="text" value="${details.work_setup || ''}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                        focus:outline-none focus:ring-0 cursor-default">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">First Day of Teaching</label>
                    <input type="text" value="${details.first_day_of_teaching || 'N/A'}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                        focus:outline-none focus:ring-0 cursor-default">
                </div>
            </div>
        `;
    } else {
        additionalInfo.innerHTML = '<p class="text-gray-500 italic">No additional details available</p>';
    }
    
    // Populate payment info - show all available payment methods in 3-column grid
    const paymentInfo = document.getElementById('tutor-payment-info');
    if (data.payment_information) {
        const payment = data.payment_information;
        let paymentHtml = '<div class="grid grid-cols-1 md:grid-cols-3 gap-4">';
        
        // Bank Transfer fields
        if (payment.bank_name || payment.account_number || payment.account_name) {
            paymentHtml += `
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Bank Name</label>
                    <input type="text" value="${payment.bank_name || 'N/A'}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                        focus:outline-none focus:ring-0 cursor-default">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Account Number</label>
                    <input type="text" value="${payment.account_number || 'N/A'}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                        focus:outline-none focus:ring-0 cursor-default">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Account Name</label>
                    <input type="text" value="${payment.account_name || 'N/A'}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                        focus:outline-none focus:ring-0 cursor-default">
                </div>
            `;
        }
        
        // GCash fields
        if (payment.gcash_number) {
            paymentHtml += `
                <div>
                    <label class="block text-sm text-gray-600 mb-1">GCash Number</label>
                    <input type="text" value="${payment.gcash_number || 'N/A'}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                        focus:outline-none focus:ring-0 cursor-default">
                </div>
            `;
        }
        
        // PayPal field
        if (payment.paypal_email) {
            paymentHtml += `
                <div>
                    <label class="block text-sm text-gray-600 mb-1">PayPal Email</label>
                    <input type="email" value="${payment.paypal_email || 'N/A'}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                        focus:outline-none focus:ring-0 cursor-default">
                </div>
            `;
        }
        
        // PayMaya fields
        if (payment.paymaya_number) {
            paymentHtml += `
                <div>
                    <label class="block text-sm text-gray-600 mb-1">PayMaya Number</label>
                    <input type="text" value="${payment.paymaya_number || 'N/A'}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                        focus:outline-none focus:ring-0 cursor-default">
                </div>
            `;
        }
        
        // Check if no payment methods are available
        if (!payment.bank_name && !payment.gcash_number && !payment.paypal_email && !payment.paymaya_number) {
            paymentHtml = '<p class="text-gray-500 italic text-center py-8">No payment information available</p>';
        } else {
            paymentHtml += '</div>';
        }
        
        paymentInfo.innerHTML = paymentHtml;
    } else {
        paymentInfo.innerHTML = '<p class="text-gray-500 italic text-center py-8">No payment information available</p>';
    }
    
    // Populate work availability info
    const availabilityInfo = document.getElementById('tutor-availability-info');
    if (data.availability) {
        const avail = data.availability;
        const daysArray = Array.isArray(avail.days_available) ? avail.days_available : JSON.parse(avail.days_available || '[]');
        
        // Map day names
        const dayNames = {
            'monday': 'Monday', 'mon': 'Monday',
            'tuesday': 'Tuesday', 'tue': 'Tuesday',
            'wednesday': 'Wednesday', 'wed': 'Wednesday',
            'thursday': 'Thursday', 'thur': 'Thursday', 'thu': 'Thursday',
            'friday': 'Friday', 'fri': 'Friday',
            'saturday': 'Saturday', 'sat': 'Saturday',
            'sunday': 'Sunday', 'sun': 'Sunday'
        };
        
        // Create day badges
        const dayBadges = daysArray.map(day => {
            const dayName = dayNames[day.toLowerCase()] || day;
            return `<span class="inline-flex items-center px-3 py-1 text-sm font-medium text-gray-800">${dayName}</span>`;
        }).join(' ');
        
        const accountName = avail.account_name.length <= 3 
            ? avail.account_name.toUpperCase() 
            : avail.account_name.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()).join(' ');
        
        availabilityInfo.innerHTML = `
            <div class="col-span-full bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-8">
                <!-- Header -->
                <div class="mb-6 text-center">
                    <h4 class="text-2xl font-bold text-[#0E335D] mb-1"> 
                        ${accountName}
                    </h4>
                    <p class="text-sm text-gray-600">Work Availability Schedule</p>
                </div>

                <!-- Content Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Start Time -->
                    <div class="text-center">
                        <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                            <div class="mb-3">
                                <i class="fas fa-clock text-3xl text-gray-500"></i>
                            </div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">Start Time</p>
                            <p class="text-2xl font-bold text-gray-800">${avail.start_time}</p>
                        </div>
                    </div>
                    
                    <!-- End Time -->
                    <div class="text-center">
                        <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                            <div class="mb-3">
                                <i class="fas fa-clock text-3xl text-gray-500"></i>
                            </div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">End Time</p>
                            <p class="text-2xl font-bold text-gray-800">${avail.end_time}</p>
                        </div>
                    </div>
                    
                    <!-- Timezone -->
                    <div class="text-center">
                        <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                            <div class="mb-3">
                                <i class="fas fa-globe text-3xl text-gray-500"></i>
                            </div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">Timezone</p>
                            <p class="text-2xl font-bold text-gray-800">${avail.timezone}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Available Days -->
                <div class="mt-8">
                    <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-sm">
                        <div class="text-center mb-4">
                            <i class="fas fa-calendar-week text-2xl text-gray-500 mb-2"></i>
                            <p class="text-xs uppercase tracking-wide text-gray-500">Available Days</p>
                        </div>
                        <div class="flex flex-wrap justify-center gap-3">
                            ${dayBadges || '<span class="text-gray-500 italic">No days specified</span>'}
                        </div>
                    </div>
                </div>
            </div>
        `;
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
    document.getElementById('supervisor-id').textContent = `Supervisor ID: ${data.id || 'N/A'}`;
    document.getElementById('supervisor-role-title').textContent = data.assigned_account ? `${data.assigned_account} Supervisor` : 'Supervisor';
    
    // Update status badge
    const statusBadge = document.getElementById('supervisor-status-badge');
    const statusColor = data.status === 'active' ? 'bg-[#65DB7F]' : 'bg-[#F65353]';
    const statusText = data.status ? data.status.charAt(0).toUpperCase() + data.status.slice(1) : 'N/A';
    statusBadge.innerHTML = `
        <span class="w-2.5 h-2.5 rounded-full ${statusColor}"></span>
        <span class="text-xs font-medium text-gray-700">${statusText}</span>
    `;
    
    // Populate personal info in 3-column grid
    const personalInfo = document.getElementById('supervisor-personal-info');
    personalInfo.innerHTML = `
        <div>
            <label class="block text-sm text-gray-600 mb-1">First Name</label>
            <input type="text" value="${data.first_name || ''}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Middle Name</label>
            <input type="text" value="${data.middle_name || 'N/A'}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Last Name</label>
            <input type="text" value="${data.last_name || ''}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Date of Birth</label>
            <input type="text" value="${data.birth_date || 'N/A'}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Email</label>
            <input type="email" value="${data.email || 'N/A'}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Phone Number</label>
            <input type="text" value="${data.phone_number || ''}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Date Hired</label>
            <input type="text" value="${data.created_at || 'N/A'}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
    `;
    
    // Populate work info in 3-column grid
    const paymentInfo = document.getElementById('supervisor-payment-info');
    paymentInfo.innerHTML = `
        <div>
            <label class="block text-sm text-gray-600 mb-1">Assigned Account</label>
            <input type="text" value="${data.assigned_account || 'Unassigned'}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Assigned Role</label>
            <input type="text" value="${data.role || 'N/A'}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">MS Teams Account</label>
            <input type="text" value="${data.ms_teams || 'N/A'}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Last Updated</label>
            <input type="text" value="${data.updated_at || 'N/A'}" readonly
                class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 
                focus:outline-none focus:ring-0 cursor-default">
        </div>
    `;
    
    // Populate work availability info
    const availabilityInfo = document.getElementById('supervisor-availability-info');
    if (data.start_time && data.end_time && data.days_available) {
        const daysArray = Array.isArray(data.days_available) ? data.days_available : JSON.parse(data.days_available || '[]');
        
        // Map day names
        const dayNames = {
            'monday': 'Monday', 'mon': 'Monday',
            'tuesday': 'Tuesday', 'tue': 'Tuesday',
            'wednesday': 'Wednesday', 'wed': 'Wednesday',
            'thursday': 'Thursday', 'thur': 'Thursday', 'thu': 'Thursday',
            'friday': 'Friday', 'fri': 'Friday',
            'saturday': 'Saturday', 'sat': 'Saturday',
            'sunday': 'Sunday', 'sun': 'Sunday'
        };
        
        // Create day badges
        const dayBadges = daysArray.map(day => {
            const dayName = dayNames[day.toLowerCase()] || day;
            return `<span class="inline-flex items-center px-3 py-1 text-sm font-medium text-gray-800">${dayName}</span>`;
        }).join(' ');
        
        availabilityInfo.innerHTML = `
            <div class="col-span-full bg-gradient-to-br from-teal-50 to-orange-50 rounded-xl p-8 border border-teal-100">
                <!-- Header -->
                <div class="mb-6 text-center">
                    <h4 class="text-2xl font-bold text-teal-800 mb-1">Work Schedule</h4>
                    <p class="text-sm text-gray-600">Supervisor availability</p>
                </div>

                <!-- Content Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Start Time -->
                    <div class="text-center">
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow border border-teal-100">
                            <div class="mb-3">
                                <i class="fas fa-clock text-3xl text-teal-600"></i>
                            </div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">Start Time</p>
                            <p class="text-2xl font-bold text-teal-800">${data.start_time}</p>
                        </div>
                    </div>
                    
                    <!-- End Time -->
                    <div class="text-center">
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow border border-orange-100">
                            <div class="mb-3">
                                <i class="fas fa-clock text-3xl text-orange-600"></i>
                            </div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">End Time</p>
                            <p class="text-2xl font-bold text-orange-800">${data.end_time}</p>
                        </div>
                    </div>
                    
                    <!-- Timezone -->
                    <div class="text-center">
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow border border-teal-100">
                            <div class="mb-3">
                                <i class="fas fa-globe text-3xl text-teal-600"></i>
                            </div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">Timezone</p>
                            <p class="text-2xl font-bold text-teal-800">${data.timezone || 'Not set'}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Available Days -->
                <div class="mt-8">
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-sm border border-orange-100">
                        <div class="text-center mb-4">
                            <i class="fas fa-calendar-week text-2xl text-orange-600 mb-2"></i>
                            <p class="text-xs uppercase tracking-wide text-gray-500">Available Days</p>
                        </div>
                        <div class="flex flex-wrap justify-center gap-3">
                            ${dayBadges || '<span class="text-gray-500 italic">No days specified</span>'}
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else {
        availabilityInfo.innerHTML = `
            <div class="col-span-full bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-8 text-center border border-gray-200">
                <i class="fas fa-calendar-times text-4xl text-gray-400 mb-3"></i>
                <p class="text-gray-600 font-medium">No availability information set</p>
                <p class="text-sm text-gray-500 mt-2">Supervisor work schedule not configured yet</p>
            </div>
        `;
    }
    
    // Show personal tab by default
    showSupervisorTab('personal');
}

function showSupervisorTab(tabName) {
    // Hide all tab contents
    document.getElementById('supervisor-personal-content').classList.add('hidden');
    document.getElementById('supervisor-payment-content').classList.add('hidden');
    document.getElementById('supervisor-availability-content').classList.add('hidden');
    
    // Remove active class from all tabs
    document.getElementById('supervisor-personal-tab').className = 'py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700';
    document.getElementById('supervisor-payment-tab').className = 'py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700';
    document.getElementById('supervisor-availability-tab').className = 'py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700';
    
    // Show selected tab content
    document.getElementById(`supervisor-${tabName}-content`).classList.remove('hidden');
    
    // Add active class to selected tab
    document.getElementById(`supervisor-${tabName}-tab`).className = 'py-4 px-1 border-b-2 border-[#0E335D] font-medium text-sm text-[#0E335D]';
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

// Archive modal functions
let archiveData = {
    type: null,
    id: null,
    name: null
};

function openArchiveModal(type, id, name) {
    archiveData = { type, id, name };
    
    // Show the main modal first
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'employee-details' }));
    
    // Wait a moment for modal to be visible, then hide content and show archive modal
    setTimeout(() => {
        // Hide employee details modals
        document.getElementById('modal-loading').classList.add('hidden');
        document.getElementById('modal-content').classList.add('hidden');
        document.getElementById('tutor-modal').classList.add('hidden');
        document.getElementById('supervisor-modal').classList.add('hidden');
        
        // Show archive modal
        document.getElementById('archive-modal').classList.remove('hidden');
        
        // Populate employee name
        document.getElementById('archive-employee-name').textContent = name || 'N/A';
        
        // Auto-fill archived by with current user (get from page if available)
        const currentUser = document.querySelector('meta[name="current-user"]')?.getAttribute('content') || 'Current User';
        document.getElementById('archive-by').value = currentUser;
        
        // Clear form fields
        document.getElementById('archive-reason').value = '';
        document.getElementById('archive-notes').value = '';
    }, 100);
}

function closeArchiveModal() {
    document.getElementById('archive-modal').classList.add('hidden');
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'employee-details' }));
    archiveData = { type: null, id: null, name: null };
}

function submitArchive() {
    const reason = document.getElementById('archive-reason').value.trim();
    const notes = document.getElementById('archive-notes').value.trim();
    const archivedBy = document.getElementById('archive-by').value;
    
    // Validation
    if (!reason) {
        alert('Please provide a reason for archiving');
        return;
    }
    
    if (reason.length > 500) {
        alert('Reason must not exceed 500 characters');
        return;
    }
    
    if (!archiveData.id) {
        alert('Error: Employee ID not found');
        return;
    }
    
    // Show confirmation
    if (!confirm(`Are you sure you want to archive ${archiveData.name}? This action cannot be easily undone.`)) {
        return;
    }
    
    // Disable button to prevent double submission
    const submitBtn = event.target;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Archiving...';
    
    // Make AJAX request
    fetch(`/employees/archive`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            type: archiveData.type,
            id: archiveData.id,
            reason: reason,
            notes: notes,
            archived_by: archivedBy
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message || 'Employee archived successfully');
            closeArchiveModal();
            // Reload page to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            alert(data.message || 'Failed to archive employee');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-archive"></i> Archive Employee';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while archiving the employee: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-archive"></i> Archive Employee';
    });
}
</script>
