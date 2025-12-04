
<!-- Search Filters -->
<div class="px-4 md:px-6 pt-4 md:pt-6 pb-3 border-b border-gray-200">
    <div class="flex items-center gap-x-4">
        <!-- Left label -->
        <h3 class="text-sm font-medium text-gray-700 whitespace-nowrap">
            Search Filters
        </h3>

        <!-- Search Input -->
        <div class="relative" style="max-width: 250px; width: 100%;">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" 
                   id="searchInput"
                   placeholder="Search archived employees..."
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm 
                          focus:outline-none focus:border-[#2A5382] focus:ring-1 focus:ring-[#2A5382]/50">
        </div>

        <!-- Reason Filter -->
        <select id="filterReason"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 bg-white
                       focus:border-[#2A5382] focus:ring-1 focus:ring-[#2A5382]/50">
            <option value="">All Reasons</option>
            <option value="Resigned">Resigned</option>
            <option value="Terminated">Terminated</option>
            <option value="Inactive">Inactive</option>
            <option value="Retired">Retired</option>
        </select>
    </div>
</div>

<!-- Table -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Archived</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
        </thead>
        <tbody id="tutorTableBody" class="bg-white divide-y divide-gray-200">
            <!-- Dynamic rows go here -->
        </tbody>
    </table>
    
    <!-- Empty State -->
    <div id="emptyState" class="hidden">
        <div class="flex flex-col items-center justify-center py-12 px-4">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-archive text-gray-400 text-4xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">No Archived Employees</h3>
            <p class="text-sm text-gray-500 text-center max-w-md">
                There are currently no archived employees to display. Archived employees will appear here when they are marked as resigned, terminated, or retired.
            </p>
        </div>
    </div>
</div>

<!-- Pagination -->
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between h-16 w-full" id="paginationSection">
    <div id="paginationInfo" class="text-sm text-gray-500">
        Showing 0 results
    </div>
    <div class="flex items-center justify-center space-x-2 w-[300px]">
        <button id="prevBtn" class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button id="pageNumber" class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center">1</button>
        <button id="nextBtn" class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<script>
    let tutors = [];
    const rowsPerPage = 5;
    let currentPage = 1;
    let filteredTutors = [];

    // Fetch archived employees from the server
    async function loadArchivedEmployees() {
        try {
            const response = await fetch('/employees/archived', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            if (data.success) {
                tutors = data.data;
                filteredTutors = [...tutors];
                renderTable();
            } else {
                console.error('Failed to load archived employees:', data.message);
            }
        } catch (error) {
            console.error('Error loading archived employees:', error);
        }
    }

    // Load data on page load
    loadArchivedEmployees();

    const tableBody = document.getElementById("tutorTableBody");
    const paginationInfo = document.getElementById("paginationInfo");
    const pageNumber = document.getElementById("pageNumber");
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");
    const searchInput = document.getElementById("searchInput");
    const filterReason = document.getElementById("filterReason");

    function renderTable() {
        tableBody.innerHTML = "";
        const emptyState = document.getElementById("emptyState");
        const total = filteredTutors.length;
        
        // Show/hide empty state
        if (total === 0) {
            emptyState.classList.remove("hidden");
            paginationInfo.textContent = 'Showing 0 results';
            pageNumber.textContent = '0';
            prevBtn.disabled = true;
            nextBtn.disabled = true;
            return;
        } else {
            emptyState.classList.add("hidden");
        }
        
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginated = filteredTutors.slice(start, end);

        // Always show result count
        const startResult = start + 1;
        const endResult = Math.min(end, total);
        paginationInfo.textContent = `Showing ${startResult} to ${endResult} of ${total} results`;

        paginated.forEach((tutor, index) => {
            const employeeType = tutor.employee_type || 'tutor';
            const archiveId = tutor.archive_id;
            
            const row = `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${tutor.date}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${tutor.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#FF7515]"></span>
                            <span class="text-xs font-medium text-gray-500">${tutor.reason}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#F65353]"></span>
                            <span class="text-xs font-medium text-gray-500">${tutor.status}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex space-x-2">
                            <button onclick="openArchivedEmployeeModal(${archiveId})" class="w-8 h-8 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 inline-flex items-center justify-center transition-colors" title="View Details">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML("beforeend", row);
        });

        pageNumber.textContent = currentPage;
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = end >= total;
    }
    
    // Function to view archived employee details - uses the same modal as other employee tabs
    function openArchivedEmployeeModal(archiveId) {
        // Find the archived employee data
        const employee = tutors.find(t => t.archive_id === archiveId);
        if (!employee) {
            alert('Employee details not found');
            return;
        }
        
        const payload = employee.payload || {};
        const employeeType = employee.employee_type || 'tutor';
        
        // Open the existing employee modal
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'employee-details' }));
        
        // Show loading state
        document.getElementById('modal-loading').classList.remove('hidden');
        document.getElementById('modal-content').classList.add('hidden');
        
        // Hide both modals initially
        document.getElementById('tutor-modal').classList.add('hidden');
        document.getElementById('supervisor-modal').classList.add('hidden');
        
        // Simulate fetch and populate with archived data
        setTimeout(() => {
            if (employeeType === 'tutor') {
                populateTutorModalWithArchived(employee, payload);
            } else {
                populateSupervisorModalWithArchived(employee, payload);
            }
        }, 100);
    }
    
    function populateTutorModalWithArchived(employee, payload) {
        console.log('Archived employee:', employee);
        console.log('Archived payload:', payload);
        
        // Hide loading, show content
        document.getElementById('modal-loading').classList.add('hidden');
        document.getElementById('modal-content').classList.remove('hidden');
        document.getElementById('tutor-modal').classList.remove('hidden');
        
        // Update header
        document.getElementById('tutor-name').textContent = employee.name || 'N/A';
        document.getElementById('tutor-username').textContent = '@' + (payload.username || 'archived');
        document.getElementById('tutor-id').textContent = `Tutor ID: ${payload.tutor_id_formatted || 'N/A'}`;
        
        // Update status badge - always archived
        const statusElement = document.getElementById('tutor-status-badge');
        statusElement.innerHTML = `
            <span class="w-2.5 h-2.5 rounded-full bg-[#F65353]"></span>
            <span class="text-xs font-medium text-gray-700">Archived</span>
        `;
        statusElement.className = 'inline-flex items-center gap-2';
        
        // Populate personal info
        const personalInfo = document.getElementById('tutor-personal-info');
        personalInfo.innerHTML = `
            <div>
                <label class="block text-sm text-gray-600 mb-1">First Name</label>
                <input type="text" value="${payload.first_name || ''}" readonly
                    class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Middle Name</label>
                <input type="text" value="${payload.middle_name || 'N/A'}" readonly
                    class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Last Name</label>
                <input type="text" value="${payload.last_name || ''}" readonly
                    class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Date of Birth</label>
                <input type="text" value="${payload.date_of_birth || 'N/A'}" readonly
                    class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Personal Email</label>
                <input type="email" value="${payload.personal_email || 'N/A'}" readonly
                    class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Company Email</label>
                <input type="email" value="${payload.email || 'N/A'}" readonly
                    class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Phone Number</label>
                <input type="text" value="${payload.contact_number || ''}" readonly
                    class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Date Hired</label>
                <input type="text" value="${payload.created_at || 'N/A'}" readonly
                    class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
            </div>
        `;
        
        // Populate additional info
        const additionalInfo = document.getElementById('tutor-additional-info');
        additionalInfo.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Address</label>
                    <input type="text" value="${payload.address || ''}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Educational Attainment</label>
                    <input type="text" value="${payload.educational_attainment || ''}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">ESL Teaching Experience</label>
                    <input type="text" value="${payload.esl_teaching_experience || ''}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Work Setup</label>
                    <input type="text" value="${payload.work_setup || ''}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">First Day of Teaching</label>
                    <input type="text" value="${payload.created_at || 'N/A'}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
                </div>
            </div>
            <div class="mt-6 pt-6 border-t border-gray-300">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Archive Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Archive Date</label>
                        <input type="text" value="${employee.date}" readonly
                            class="border border-gray-200 rounded-md px-3 py-2 w-full bg-red-50 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Archive Reason</label>
                        <input type="text" value="${employee.reason}" readonly
                            class="border border-gray-200 rounded-md px-3 py-2 w-full bg-red-50 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
                    </div>
                    ${employee.notes ? `
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">Archive Notes</label>
                        <textarea readonly rows="2"
                            class="border border-gray-200 rounded-md px-3 py-2 w-full bg-red-50 text-gray-900 focus:outline-none focus:ring-0 cursor-default">${employee.notes}</textarea>
                    </div>` : ''}
                </div>
            </div>
        `;
        
        // Payment info
        document.getElementById('tutor-payment-info').innerHTML = `
            <p class="text-gray-500 italic text-center py-8">Payment information is not available for archived employees.</p>
        `;
        
        // Work availability
        const availabilityInfo = document.getElementById('tutor-availability-info');
        if (payload.days_available && payload.days_available.length > 0) {
            const accountName = payload.account_name && payload.account_name.length <= 3 
                ? payload.account_name.toUpperCase() 
                : (payload.account_name || 'N/A');
            
            const dayNames = {
                'monday': 'Monday', 'mon': 'Monday',
                'tuesday': 'Tuesday', 'tue': 'Tuesday',
                'wednesday': 'Wednesday', 'wed': 'Wednesday',
                'thursday': 'Thursday', 'thur': 'Thursday', 'thu': 'Thursday',
                'friday': 'Friday', 'fri': 'Friday',
                'saturday': 'Saturday', 'sat': 'Saturday',
                'sunday': 'Sunday', 'sun': 'Sunday'
            };
            
            const dayBadges = payload.days_available.map(day => {
                const dayName = dayNames[day.toLowerCase()] || day;
                return `<span class="inline-flex items-center px-3 py-1 text-sm font-medium text-gray-800">${dayName}</span>`;
            }).join(' ');
            
            availabilityInfo.innerHTML = `
                <div class="col-span-full bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-8">
                    <div class="mb-6 text-center">
                        <h4 class="text-2xl font-bold text-[#0E335D] mb-1">${accountName}</h4>
                        <p class="text-sm text-gray-600">Work Availability Schedule</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="text-center">
                            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-sm">
                                <div class="mb-3"><i class="fas fa-clock text-3xl text-gray-500"></i></div>
                                <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">Start Time</p>
                                <p class="text-2xl font-bold text-gray-800">${payload.start_time || 'N/A'}</p>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-sm">
                                <div class="mb-3"><i class="fas fa-clock text-3xl text-gray-500"></i></div>
                                <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">End Time</p>
                                <p class="text-2xl font-bold text-gray-800">${payload.end_time || 'N/A'}</p>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-sm">
                                <div class="mb-3"><i class="fas fa-globe text-3xl text-gray-500"></i></div>
                                <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">Timezone</p>
                                <p class="text-2xl font-bold text-gray-800">${payload.timezone || 'UTC'}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8">
                        <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-sm">
                            <div class="text-center mb-4">
                                <i class="fas fa-calendar-check text-2xl text-[#0E335D]"></i>
                                <p class="text-sm font-medium text-gray-600 mt-2">Available Days</p>
                            </div>
                            <div class="flex flex-wrap justify-center gap-2">${dayBadges}</div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            availabilityInfo.innerHTML = `
                <p class="text-gray-500 italic text-center py-8">Work availability information is not available for this archived employee.</p>
            `;
        }
    }
    
    function populateSupervisorModalWithArchived(employee, payload) {
        console.log('Archived supervisor:', employee);
        console.log('Archived supervisor payload:', payload);
        
        // Hide loading, show content
        document.getElementById('modal-loading').classList.add('hidden');
        document.getElementById('modal-content').classList.remove('hidden');
        document.getElementById('supervisor-modal').classList.remove('hidden');
        
        // Update header
        document.getElementById('supervisor-name').textContent = employee.name || 'N/A';
        document.getElementById('supervisor-id').textContent = `Supervisor ID: ${payload.sup_id || 'N/A'}`;
        document.getElementById('supervisor-role-title').textContent = 'Supervisor';
        
        // Update status badge
        const statusBadge = document.getElementById('supervisor-status-badge');
        statusBadge.innerHTML = `
            <span class="w-2.5 h-2.5 rounded-full bg-[#F65353]"></span>
            <span class="text-xs font-medium text-gray-700">Archived</span>
        `;
        
        // Populate personal info
        const personalInfo = document.getElementById('supervisor-personal-info');
        const nameParts = (payload.full_name || employee.name || '').split(' ');
        const firstName = nameParts[0] || '';
        const lastName = nameParts.slice(1).join(' ') || '';
        
        personalInfo.innerHTML = `
            <div>
                <label class="block text-sm text-gray-600 mb-1">First Name</label>
                <input type="text" value="${firstName}" readonly
                    class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Middle Name</label>
                <input type="text" value="N/A" readonly
                    class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Last Name</label>
                <input type="text" value="${lastName}" readonly
                    class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Date of Birth</label>
                <input type="text" value="N/A" readonly
                    class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Email</label>
                <input type="email" value="${payload.email || 'N/A'}" readonly
                    class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Phone Number</label>
                <input type="text" value="${payload.contact_number || ''}" readonly
                    class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Date Hired</label>
                <input type="text" value="N/A" readonly
                    class="border border-gray-200 rounded-md px-3 py-2 w-full bg-gray-100 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
            </div>
        `;
        
        // Populate work info with archive information
        const paymentInfo = document.getElementById('supervisor-payment-info');
        paymentInfo.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Archive Date</label>
                    <input type="text" value="${employee.date}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-red-50 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Archive Reason</label>
                    <input type="text" value="${employee.reason}" readonly
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-red-50 text-gray-900 focus:outline-none focus:ring-0 cursor-default">
                </div>
                ${employee.notes ? `
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-600 mb-1">Archive Notes</label>
                    <textarea readonly rows="2"
                        class="border border-gray-200 rounded-md px-3 py-2 w-full bg-red-50 text-gray-900 focus:outline-none focus:ring-0 cursor-default">${employee.notes}</textarea>
                </div>` : ''}
            </div>
        `;
        
        // Clear schedule info
        const availabilityInfo = document.getElementById('supervisor-availability-info');
        availabilityInfo.innerHTML = `
            <p class="text-gray-500 italic text-center py-8">Work schedule information is not available for archived employees.</p>
        `;
    }

    function updateFilter() {
        const query = searchInput.value.toLowerCase();
        const reasonFilter = filterReason.value;
        filteredTutors = tutors.filter(t =>
            (t.name.toLowerCase().includes(query)) &&
            (reasonFilter === "" || t.reason === reasonFilter)
        );
        currentPage = 1;
        renderTable();
    }

    searchInput.addEventListener("input", updateFilter);
    filterReason.addEventListener("change", updateFilter);

    prevBtn.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });

    nextBtn.addEventListener("click", () => {
        if ((currentPage * rowsPerPage) < filteredTutors.length) {
            currentPage++;
            renderTable();
        }
    });

    // Show/hide pagination buttons based on total count
    function updatePaginationVisibility() {
        const paginationButtons = document.querySelector("#paginationSection .flex.items-center.justify-center");
        if (filteredTutors.length >= 6) {
            paginationButtons.style.display = "flex";
        } else {
            paginationButtons.style.display = "none";
        }
    }

    // Call after renderTable
    const originalRenderTable = renderTable;
    renderTable = function() {
        originalRenderTable();
        updatePaginationVisibility();
    };

    renderTable();
</script>
