<!-- Page Title -->
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <h2 class="text-xl font-semibold text-gray-800">Class Scheduling</h2>
</div>
<!-- Search Filters -->
<div class="bg-white px-6 py-4 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
        <button class="flex items-center space-x-2 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
            <i class="fas fa-upload"></i>
            <span>Upload Excel</span>
        </button>
    </div>
    <div class="flex items-center space-x-4">
        <div class="relative flex-1 max-w-md">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <input type="text" 
                   placeholder="search school..." 
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
        </div>
        <select class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
            <option>Date</option>
        </select>
        <select class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
            <option>Day</option>
        </select>
        <select class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
            <option>Status</option> 
        </select>
    </div>
</div>
<!-- Class Scheduling Table -->
<div class="bg-white overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Day</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">School</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Number Required</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tutors Assigned</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">September 2, 2025</td>
                <td class="px-6 py-4 text-sm text-gray-500">Tuesday</td>
                <td class="px-6 py-4 text-sm text-gray-900 font-medium">TOKOGAWA</td>
                <td class="px-6 py-4 text-sm text-center text-gray-500">15</td>
                <td class="px-6 py-4 text-sm text-center text-gray-500">15</td>
                <td class="px-6 py-4 text-sm">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Partially Assigned</span>
                </td>
                <td class="px-6 py-4 text-sm">
                    <button class="w-8 h-8 bg-blue-100 text-blue-600 rounded hover:bg-blue-200">
                        <i class="fas fa-search text-xs"></i>
                    </button>
                </td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">September 3, 2025</td>
                <td class="px-6 py-4 text-sm text-gray-500">Wednesday</td>
                <td class="px-6 py-4 text-sm text-gray-900 font-medium">TOKOGAWA</td>
                <td class="px-6 py-4 text-sm text-center text-gray-500">10</td>
                <td class="px-6 py-4 text-sm text-center text-gray-500">10</td>
                <td class="px-6 py-4 text-sm">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Fully Assigned</span>
                </td>
                <td class="px-6 py-4 text-sm">
                    <button class="w-8 h-8 bg-blue-100 text-blue-600 rounded hover:bg-blue-200">
                        <i class="fas fa-search text-xs"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<!-- Pagination -->
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
    <div class="text-sm text-gray-500">
        Showing 1 to 2 of 2 results
    </div>
    <div class="flex items-center space-x-2">
        <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50" disabled>
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="px-3 py-1 bg-slate-700 text-white rounded text-sm">1</button>
        <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50" disabled>
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>