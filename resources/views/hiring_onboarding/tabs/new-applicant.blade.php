@if (request('view') === 'details')
    {{-- Show applicant details --}}
    @include('hiring_onboarding.tabs.partials.applicant-details')
@else

    <!-- Page Title -->
    <div class="p-4 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">New Applicants</h2>
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
                <select class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                    <option>Select Source</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Employee Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available
                        Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr class="hover:bg-gray-50 bg-yellow-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-09-27 09:45 AM</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Josh Daniel Collins</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">09477789871</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 underline">jc.921@gmail.com</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Mon - Fri | 7 AM - 3 PM</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#FBBF24] text-[#6C5600]">Pending</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex items-center space-x-2">
                            <!-- View Button -->
                            <a href="{{ route('hiring_onboarding.index', ['view' => 'details']) }}"
                                class="w-8 h-8 flex items-center justify-center text-gray-600 rounded hover:bg-gray-100 transition-colors"
                                title="View applicant details">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <!-- Archive Button -->
                            <button
                                class="w-8 h-8 text-gray-600 rounded hover:bg-gray-100 transition-colors"
                                title="Archive this applicant">
                                <i class="fas fa-archive text-xs"></i>
                            </button>
                        </div>
                    </td>

                </tr>
                <tr class="hover:bg-gray-50 bg-orange-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-09-27 10:10 AM</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Nidal Kendrick</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">09789998767</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 underline">nidal@gmail.com</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Mon - Wed | 7 AM - 4 PM</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#FF7515] text-[#FFE8D8]">Fail
                            (No Answer)</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                        <!-- View Button -->
                        <button class="w-8 h-8 text-gray-600 rounded hover:bg-gray-100 transition-colors"
                                title="View applicant details">
                            <i class="fas fa-eye text-xs"></i>
                        </button>
                        <!-- Archive Button -->
                        <button class="w-8 h-8 text-gray-600 rounded hover:bg-gray-100 transition-colors"
                                title="Archive this applicant">
                            <i class="fas fa-archive text-xs"></i>
                        </button>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50 bg-purple-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-09-27 11:00 AM</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Kageyam Lazola</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">09775456351</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 underline">lazola.k@gmail.com</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Mon - Fri | 10 AM - 3 PM</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#A78BFA] text-[#6037D9]">Fail
                            (Re-schedule)</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                        <!-- View Button -->
                        <button class="w-8 h-8 text-gray-600 rounded hover:bg-gray-100 transition-colors"
                                title="View applicant details">
                            <i class="fas fa-eye text-xs"></i>
                        </button>
                        <!-- Archive Button -->
                        <button class="w-8 h-8 text-gray-600 rounded hover:bg-gray-100 transition-colors"
                                title="Archive this applicant">
                            <i class="fas fa-archive text-xs"></i>
                        </button>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50 bg-yellow-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-09-27 11:45 AM</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">John Doe</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">09886536455</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 underline">j.d@gmail.com</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Tue - Wed | 7 AM - 3 PM</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#FBBF24] text-[#6C5600]">Pending</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                        <!-- View Button -->
                        <button class="w-8 h-8 text-gray-600 rounded hover:bg-gray-100 transition-colors"
                                title="View applicant details">
                            <i class="fas fa-eye text-xs"></i>
                        </button>
                        <!-- Archive Button -->
                        <button class="w-8 h-8 text-gray-600 rounded hover:bg-gray-100 transition-colors"
                                title="Archive this applicant">
                            <i class="fas fa-archive text-xs"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-500">
            Showing 1 to 4 of 4 results
        </div>
        <div class="flex items-center space-x-2">
            <button
                class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="px-3 py-1 bg-slate-700 text-white rounded text-sm">1</button>
            <button
                class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                disabled>
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
@endif