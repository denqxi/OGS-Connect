@if (request('view_date'))
    {{-- Show the daily schedule view for finalized schedules --}}
    @include('schedules.tabs.views.finalized-schedule', ['date' => request('view_date')])
@else
<!-- Page Title -->
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <h2 class="text-xl font-semibold text-gray-800">Schedule History</h2>
</div>

<!-- Search Filters -->
<div class="bg-white px-6 py-4 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
    </div>
    <form method="GET" action="{{ route('schedules.index') }}" class="flex justify-between items-center flex-wrap gap-4">
        <input type="hidden" name="tab" value="history">
        <!-- Left Group: Search + Selects -->
        <div class="flex flex-wrap items-center gap-4 flex-1 max-w-3xl">
            <div class="relative flex-1 max-w-md">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="search school name..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm 
              focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
              focus:ring-0 focus:shadow-xl">
            </div>
            <select name="date" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                <option value="">Select Date</option>
                @if(isset($availableDates))
                    @foreach($availableDates as $availableDate)
                        <option value="{{ $availableDate }}" {{ request('date') == $availableDate ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($availableDate)->format('F j, Y') }}
                        </option>
                    @endforeach
                @endif
            </select>
            <select name="day" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                <option value="">Select Day</option>
                @if(isset($availableDays))
                    @foreach($availableDays as $availableDay)
                        <option value="{{ $availableDay }}" {{ request('day') == $availableDay ? 'selected' : '' }}>
                            {{ ucfirst($availableDay) }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <!-- Right Group: Search and Export Buttons -->
        <div class="flex items-center space-x-2">
            <button type="submit"
                class="flex items-center space-x-2 bg-[#2A5382] text-white px-4 py-2 rounded-full text-sm font-medium 
                        hover:bg-[#1e3a5c] transform transition duration-200 hover:scale-105">
                <i class="fas fa-search"></i>
                <span>Search</span>
            </button>
            <button type="button"
                id="exportButton"
                onclick="exportSelectedSchedules()"
                class="flex items-center space-x-2 bg-[#0E335D] text-white px-4 py-2 rounded-full text-sm font-medium 
                        hover:bg-[#184679] transform transition duration-200 hover:scale-105 opacity-50 cursor-not-allowed"
                disabled>
                <i class="fas fa-file-export"></i>
                <span id="exportButtonText">Export File (0 selected)</span>
            </button>
        </div>
    </form>
</div>


<!-- Schedule History Table -->
<div class="bg-white overflow-x-auto">
    <table class="w-full table-auto">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" onchange="toggleAllSchedules()">
                </th>
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
            @if(isset($scheduleHistory) && $scheduleHistory->count() > 0)
                @foreach($scheduleHistory as $history)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm">
                        <input type="checkbox" name="selected_schedules[]" value="{{ $history->date }}" class="schedule-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" onchange="updateExportButton()">
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ \Carbon\Carbon::parse($history->date)->format('F j, Y') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ ucfirst($history->day) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                        {{ $history->schools }}
                    </td>
                    <td class="px-6 py-4 text-sm text-center text-gray-500">
                        {{ $history->total_required }}
                    </td>
                    <td class="px-6 py-4 text-sm text-center text-gray-500">
                        {{ $history->total_assigned }}
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Done
                        </span>
                        <div class="text-xs text-gray-400 mt-1">
                            Finalized: {{ \Carbon\Carbon::parse($history->finalized_at)->format('M j, Y g:i A') }}
                        </div>  
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('schedules.index', ['tab' => 'history', 'view_date' => \Carbon\Carbon::parse($history->date)->format('Y-m-d')]) }}"
                            class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-600 rounded hover:bg-blue-200"
                            title="View Schedule">
                            <i class="fas fa-search text-xs"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-calendar-times text-4xl mb-4 opacity-50"></i>
                        <p class="text-lg font-medium">No finalized schedules found</p>
                        <p class="text-sm">Schedules will appear here after being saved as "Final"</p>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if(isset($scheduleHistory) && method_exists($scheduleHistory, 'hasPages') && $scheduleHistory->hasPages())
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
    <div class="text-sm text-gray-500">
        Showing {{ $scheduleHistory->count() }} of {{ $scheduleHistory->total() }} finalized schedules
    </div>
    <div class="flex items-center space-x-2">
        @if ($scheduleHistory->onFirstPage())
            <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
        @else
            <a href="{{ $scheduleHistory->appends(request()->query())->previousPageUrl() }}" 
               class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        @foreach ($scheduleHistory->appends(request()->query())->getUrlRange(1, $scheduleHistory->lastPage()) as $page => $url)
            @if ($page == $scheduleHistory->currentPage())
                <button class="px-3 py-1 bg-slate-700 text-white rounded text-sm">{{ $page }}</button>
            @else
                <a href="{{ $url }}" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">{{ $page }}</a>
            @endif
        @endforeach

        @if ($scheduleHistory->hasMorePages())
            <a href="{{ $scheduleHistory->appends(request()->query())->nextPageUrl() }}" 
               class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">
                <i class="fas fa-chevron-right"></i>
            </a>
        @else
            <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50" disabled>
                <i class="fas fa-chevron-right"></i>
            </button>
        @endif
    </div>
</div>
@else
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
    <div class="text-sm text-gray-500">
        @if(isset($scheduleHistory))
            Showing {{ $scheduleHistory->count() }} finalized schedules
        @else
            No results
        @endif
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
@endif

<!-- JavaScript for Schedule History -->
<script>
    // Pass CSRF token and export route to JS
    const csrfToken = "{{ csrf_token() }}";
    const exportSelectedSchedulesRoute = "{{ route('schedules.export-selected') }}";
</script>
<script src="{{ asset('js/schedule-history.js') }}"></script>
@endif


