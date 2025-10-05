
<!-- Search Filters -->
<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Archived</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
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
    const tutors = [
        { date: "Jan 12, 2024", name: "Alice Wong", phone: "09123456789", email: "alice@example.com", reason: "Resigned", status: "Archived" },
        { date: "Feb 03, 2024", name: "Bob Smith", phone: "09234567890", email: "bob@example.com", reason: "Terminated", status: "Archived" },
        { date: "Mar 20, 2024", name: "Cathy Johnson", phone: "09345678901", email: "cathy@example.com", reason: "Inactive", status: "Archived" },
        { date: "Apr 02, 2024", name: "David Brown", phone: "09456789012", email: "david@example.com", reason: "Resigned", status: "Archived" },
        { date: "May 09, 2024", name: "Ella Garcia", phone: "09567890123", email: "ella@example.com", reason: "Retired", status: "Archived" },
        { date: "Jun 18, 2024", name: "Frank Lee", phone: "09678901234", email: "frank@example.com", reason: "Inactive", status: "Archived" },
    ];

    const rowsPerPage = 5;
    let currentPage = 1;
    let filteredTutors = [...tutors];

    const tableBody = document.getElementById("tutorTableBody");
    const paginationInfo = document.getElementById("paginationInfo");
    const pageNumber = document.getElementById("pageNumber");
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");
    const searchInput = document.getElementById("searchInput");
    const filterReason = document.getElementById("filterReason");
    const clearSearch = document.getElementById("clearSearch");

    function renderTable() {
        tableBody.innerHTML = "";
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginated = filteredTutors.slice(start, end);

        paginated.forEach(tutor => {
            const row = `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${tutor.date}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${tutor.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${tutor.phone}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 underline">
                        <a href="mailto:${tutor.email}">${tutor.email}</a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${tutor.reason}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            ${tutor.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition-colors text-xs font-medium">
                            Restore
                        </button>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML("beforeend", row);
        });

        const total = filteredTutors.length;
        const startResult = total === 0 ? 0 : start + 1;
        const endResult = Math.min(end, total);
        paginationInfo.textContent = `Showing ${startResult}-${endResult} of ${total} results`;

        pageNumber.textContent = currentPage;
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = end >= total;
    }

    function updateFilter() {
        const query = searchInput.value.toLowerCase();
        const reasonFilter = filterReason.value;
        filteredTutors = tutors.filter(t =>
            (t.name.toLowerCase().includes(query) || t.email.toLowerCase().includes(query) || t.phone.includes(query)) &&
            (reasonFilter === "" || t.reason === reasonFilter)
        );
        currentPage = 1;
        renderTable();
        clearSearch.classList.toggle("hidden", query === "");
    }

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
        if ((currentPage * rowsPerPage) < filteredTutors.length) {
            currentPage++;
            renderTable();
        }
    });

    renderTable();
</script>
