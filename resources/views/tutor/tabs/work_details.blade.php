<div class="p-6 border-b border-gray-200">
    <h2 class="text-xl font-semibold text-gray-800">Work Details</h2>
</div>

<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
    </div>

    <form method="GET" action="{{ route('payroll.index') }}" id="tutorFilterForm">
        <input type="hidden" name="filter_applied" value="employee_payroll">
        <div class="flex justify-between items-center space-x-4">
            <div class="flex items-center space-x-4 flex-1 max-w-lg">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search full name, email, phone..." id="tutorSearch"
                        class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md text-sm 
                                  focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
                                  focus:ring-0 focus:shadow-xl">
                    <button type="button" id="clearSearch"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <select name="status" id="filterStatus"
                    class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white"
                    onchange="handleTutorFilterChange('status')">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <button type="button" id="addClassModal" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded text-xs" onclick="createWorkDetail()">
                        Add Class
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Work Details Table -->
<div class="overflow-x-auto">
    <table class="w-full" id="tutorTable">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Work Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="tutorTableBody">
            @php
                // Prefer a dedicated $workDetails collection passed from controller.
                // Fallback: collect work details from tutors if available.
                $rows = $workDetails ?? (isset($tutors) ? $tutors->flatMap(fn($t) => $t->workDetails ?? collect()) : collect());
            @endphp

            @forelse($rows as $detail)
                @php
                    // Some DBs store tutor identifier in tutor_id as formatted tutorID
                    $tutorId = $detail->tutor_id ?? null;
                    $displayDate = $detail->date ?? $detail->created_at?->format('Y-m-d') ?? 'N/A';
                    $StartTime = $detail->start_time ?? 'N/A';
                    $EndTime = $detail->end_time ?? 'N/A';
                    $classNo = $detail->class_no ?? ($detail->work_type ?? '—');
                    $rate = $detail->rate_per_class ?? 'N/A';
                    $status = $detail->status ?? ($detail->status ?? 'pending');
                @endphp
                <tr class="hover:bg-gray-50 tutor-row" data-searchable="{{ strtolower($tutorId . ' ' . ($detail->ph_time ?? '') . ' ' . ($classNo ?? '')) }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $displayDate }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $StartTime }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $EndTime }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $classNo }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">₱{{ $rate }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ ucfirst($status) }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                        <div class="flex items-center space-x-2">
                            <button type="button" class="px-3 py-1 bg-indigo-600 text-white rounded text-xs" onclick="openWorkDetailEditor('{{ $detail->id ?? $detail->work_detail_id ?? $detail->id }}')">Edit</button>
                            <button type="button" class="px-3 py-1 bg-red-100 text-red-600 rounded text-xs" onclick="confirmDeleteWorkDetail('{{ $detail->id ?? $detail->work_detail_id ?? $detail->id }}')">Delete</button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="noResultsRow">
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                        <p class="text-lg font-medium">No work details found</p>
                        <p class="text-sm">Try adjusting your search criteria</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- No Search Results Message -->
    <div id="noSearchResults" class="hidden bg-white px-6 py-8 text-center text-gray-500 border-t">
        <i class="fas fa-search text-4xl mb-4 opacity-50"></i>
        <p class="text-lg font-medium">No tutors found</p>
        <p class="text-sm">Try adjusting your search terms</p>
    </div>
</div>

<!-- Pagination -->
@if(isset($workDetails) && method_exists($workDetails, 'links'))
    @include('tutor.tabs.partials.work_details_pagination', ['workDetails' => $workDetails])
@else
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between h-16 w-full" id="paginationSection">
    <div class="text-sm text-gray-500">
        Showing 0 results
    </div>
    <div class="flex items-center justify-center space-x-2 w-[300px]">
        <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center">1</button>
        <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>
@endif
<script src="{{ asset('js/tutor-work.js') }}"></script>
