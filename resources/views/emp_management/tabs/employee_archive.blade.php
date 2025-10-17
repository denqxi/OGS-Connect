
<!-- Search Filters -->
<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
        <div class="flex items-center space-x-2">
            <button id="bulkRestoreBtn" class="px-3 py-1 bg-green-100 text-green-600 rounded hover:bg-green-200 transition-colors text-xs font-medium" 
                    disabled title="Restore selected archived employees">
                Restore Selected
            </button>
        </div>
    </div>
    
    <div class="flex justify-between items-center space-x-4">
        <!-- Left side -->
        <div class="flex items-center space-x-4 flex-1 max-w-lg">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" 
                       id="searchInput"
                       placeholder="Search full name, email, phone..."
                       class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md text-sm 
                              focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
                              focus:ring-0 focus:shadow-xl">
                <button type="button" id="clearSearch" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <select id="filterReason" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                <option value="">All Reasons</option>
                <option value="Resigned">Resigned</option>
                <option value="Terminated">Terminated</option>
                <option value="Inactive">Inactive</option>
                <option value="Retired">Retired</option>
            </select>
        </div>
    </div>
</div>

<!-- Table -->
<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <input type="checkbox" id="selectAllCheckbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Archived</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
        </thead>
        <tbody id="tutorTableBody" class="bg-white divide-y divide-gray-200">
            <!-- Dynamic rows go here -->
        </tbody>
    </table>
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
    // Get data from Laravel
    const archivedEmployees = @json($archivedEmployees ?? []);
    const rowsPerPage = 5;
    let currentPage = 1;
    let filteredEmployees = [...archivedEmployees.data];
    let selectedEmployees = new Set();

    const tableBody = document.getElementById("tutorTableBody");
    const paginationInfo = document.getElementById("paginationInfo");
    const pageNumber = document.getElementById("pageNumber");
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");
    const searchInput = document.getElementById("searchInput");
    const filterReason = document.getElementById("filterReason");
    const clearSearch = document.getElementById("clearSearch");
    const selectAllCheckbox = document.getElementById("selectAllCheckbox");
    const bulkRestoreBtn = document.getElementById("bulkRestoreBtn");

    function renderTable() {
        tableBody.innerHTML = "";
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginated = filteredEmployees.slice(start, end);

        paginated.forEach(employee => {
            const isSelected = selectedEmployees.has(employee.id);
            const typeColor = employee.type === 'tutor' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800';
            const typeText = employee.type === 'tutor' ? 'Tutor' : 'Supervisor';
            
            const row = `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" 
                               class="employee-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                               data-id="${employee.id}" 
                               data-type="${employee.type}"
                               ${isSelected ? 'checked' : ''}>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${employee.archived_at}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${employee.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${employee.phone}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        <a href="mailto:${employee.email}">${employee.email}</a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${typeColor}">
                            ${typeText}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${employee.reason}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            ${employee.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button onclick="restoreEmployee('${employee.id}', '${employee.type}')" 
                                class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition-colors text-xs font-medium"
                                title="Restore this employee and reactivate their account">
                            Restore
                        </button>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML("beforeend", row);
        });

        // Update pagination info
        const total = filteredEmployees.length;
        const startResult = total === 0 ? 0 : start + 1;
        const endResult = Math.min(end, total);
        paginationInfo.textContent = `Showing ${startResult}-${endResult} of ${total} results`;

        pageNumber.textContent = currentPage;
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = end >= total;

        // Update select all checkbox
        updateSelectAllCheckbox();
        updateBulkRestoreButton();
    }

    function updateFilter() {
        const query = searchInput.value.toLowerCase();
        const reasonFilter = filterReason.value;
        filteredEmployees = archivedEmployees.data.filter(emp =>
            (emp.name.toLowerCase().includes(query) || emp.email.toLowerCase().includes(query) || emp.phone.includes(query)) &&
            (reasonFilter === "" || emp.reason === reasonFilter)
        );
        currentPage = 1;
        selectedEmployees.clear();
        renderTable();
        clearSearch.classList.toggle("hidden", query === "");
    }

    function updateSelectAllCheckbox() {
        const checkboxes = document.querySelectorAll('.employee-checkbox');
        const checkedCount = document.querySelectorAll('.employee-checkbox:checked').length;
        selectAllCheckbox.checked = checkedCount === checkboxes.length && checkboxes.length > 0;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
    }

    function updateBulkRestoreButton() {
        const checkedBoxes = document.querySelectorAll('.employee-checkbox:checked');
        bulkRestoreBtn.disabled = checkedBoxes.length === 0;
        bulkRestoreBtn.textContent = checkedBoxes.length > 0 ? `Restore Selected (${checkedBoxes.length})` : 'Restore Selected';
    }

    function restoreEmployee(id, type) {
        if (confirm('Are you sure you want to restore this employee?')) {
            fetch('{{ route("employees.restore") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ id: id, type: type })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while restoring the employee.');
            });
        }
    }

    function bulkRestore() {
        const checkedBoxes = document.querySelectorAll('.employee-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Please select employees to restore.');
            return;
        }

        if (confirm(`Are you sure you want to restore ${checkedBoxes.length} selected employees?`)) {
            const ids = Array.from(checkedBoxes).map(cb => cb.dataset.id);
            const types = Array.from(checkedBoxes).map(cb => cb.dataset.type);
            
            // Group by type for bulk operations
            const tutorIds = ids.filter((id, index) => types[index] === 'tutor');
            const supervisorIds = ids.filter((id, index) => types[index] === 'supervisor');

            const promises = [];
            
            if (tutorIds.length > 0) {
                promises.push(
                    fetch('{{ route("employees.bulk-restore") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ ids: tutorIds, type: 'tutor' })
                    })
                );
            }
            
            if (supervisorIds.length > 0) {
                promises.push(
                    fetch('{{ route("employees.bulk-restore") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ ids: supervisorIds, type: 'supervisor' })
                    })
                );
            }

            Promise.all(promises)
                .then(responses => Promise.all(responses.map(r => r.json())))
                .then(results => {
                    const successCount = results.reduce((sum, result) => sum + (result.success ? 1 : 0), 0);
                    if (successCount === results.length) {
                        alert('All selected employees have been restored successfully!');
                        location.reload();
                    } else {
                        alert('Some employees could not be restored. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while restoring employees.');
                });
        }
    }

    // Event listeners
    searchInput.addEventListener("input", updateFilter);
    filterReason.addEventListener("change", updateFilter);
    clearSearch.addEventListener("click", () => {
        searchInput.value = "";
        updateFilter();
    });

    prevBtn.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });

    nextBtn.addEventListener("click", () => {
        if ((currentPage * rowsPerPage) < filteredEmployees.length) {
            currentPage++;
            renderTable();
        }
    });

    selectAllCheckbox.addEventListener("change", (e) => {
        const checkboxes = document.querySelectorAll('.employee-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = e.target.checked;
            if (e.target.checked) {
                selectedEmployees.add(cb.dataset.id);
            } else {
                selectedEmployees.delete(cb.dataset.id);
            }
        });
        updateBulkRestoreButton();
    });


    bulkRestoreBtn.addEventListener("click", bulkRestore);

    // Handle individual checkbox changes
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('employee-checkbox')) {
            if (e.target.checked) {
                selectedEmployees.add(e.target.dataset.id);
            } else {
                selectedEmployees.delete(e.target.dataset.id);
            }
            updateSelectAllCheckbox();
            updateBulkRestoreButton();
        }
    });

    renderTable();
</script>
