<!-- Page Title -->
<div class="p-4 border-b border-gray-200">
    <h2 class="text-xl font-bold text-gray-800">Archive</h2>
</div>

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
                <input type="text" placeholder="search name..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm 
              focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
              focus:ring-0 focus:shadow-xl">
            </div>
            <select class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                <option>Select Status</option>
            </select>
        </div>
    </div>
</div>

<!-- Archive Table -->
<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <!-- Record 1 -->
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-09-27 09:15</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Michael Reyes</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">09181234567</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 underline">michael.reyes@gmail.com</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Did not meet requirements</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#AA1B1B] text-white">
                        Not Recommended
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <button class="w-8 h-8 bg-[#9DC9FD] text-[#2C5B8C] rounded hover:bg-[#7BB4FB] transition-colors">
                        <i class="fas fa-eye text-xs"></i>
                    </button>
                </td>
            </tr>

            <!-- Record 2 -->
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-09-27 10:45</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Angela Cruz</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">09981239876</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 underline">angela.cruz@gmail.com</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Declined offer</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#E02F2F] text-white">
                        Declined
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <button class="w-8 h-8 bg-[#9DC9FD] text-[#2C5B8C] rounded hover:bg-[#7BB4FB] transition-colors">
                        <i class="fas fa-eye text-xs"></i>
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
        <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="px-3 py-1 bg-slate-700 text-white rounded text-sm">1</button>
        <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>
