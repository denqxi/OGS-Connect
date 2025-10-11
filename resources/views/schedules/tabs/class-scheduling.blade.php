@if (request('view_date'))
    {{-- Show the daily schedule view --}}
    @include('schedules.tabs.views.per-day-schedule', [
        'date' => request('view_date'),
        'dayClasses' => $dayClasses ?? collect(),
        'isFinalized' => $isFinalized ?? false,
        'finalizedAt' => $finalizedAt ?? null,
        'dayInfo' => $dayInfo ?? null,
        'availableTutors' => $availableTutors ?? collect(),
        'availableTimeSlots' => $availableTimeSlots ?? collect()
    ])
@else
    <!-- Page Title with Upload Button -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Class Scheduling</h2>
            
            <!-- Upload Excel Button -->
            <div class="relative">
                <input type="file" 
                       id="excelFileInput" 
                       accept=".xlsx,.xls,.csv" 
                       class="hidden"
                       onchange="uploadExcelFile()">
                <button onclick="document.getElementById('excelFileInput').click()"
                        class="flex items-center space-x-2 bg-[#0E335D] text-white px-4 py-2 rounded-full text-sm font-medium 
                              hover:bg-[#184679] transform transition duration-200 hover:scale-105">
                    <i class="fas fa-file-excel"></i>
                    <span>Upload Excel</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Search Filters -->
    <div class="bg-white px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
        </div>
        <form method="GET" action="{{ route('schedules.index') }}" id="filterForm">
            <input type="hidden" name="tab" value="class">
            <div class="flex justify-between items-center space-x-4">
                <!-- Left Group: Search + Filters -->
                <div class="flex items-center space-x-4 flex-1 max-w-3xl">
                    <div class="relative flex-1 max-w-md">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" 
                   id="realTimeSearch"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search schools..." 
                   class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md text-sm 
                       focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
                       focus:ring-0 focus:shadow-xl">
                        <!-- Spinner -->
                        <div id="searchSpinner" class="absolute right-8 top-1/2 transform -translate-y-1/2 hidden">
                            <i class="fas fa-spinner fa-spin text-gray-400"></i>
                        </div>
                        <!-- Clear button -->
                        <button type="button" id="clearSearch" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 {{ request('search') ? '' : 'hidden' }}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <!-- Only one filter active at a time -->
                    <select name="date" id="filterDate" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="handleFilterChange('date')">
                        <option value="">All Dates</option>
                        @if(isset($availableDates) && $availableDates->count() > 0)
                            @foreach($availableDates as $date)
                                <option value="{{ $date }}" {{ request('date') == $date ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                                </option>
                            @endforeach
                        @else
                            <!-- Debug: Show if availableDates is not set or empty -->
                            <option value="" disabled>No dates available (Debug: availableDates not set or empty)</option>
                        @endif
                    </select>
                    <select name="day" id="filterDay" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="handleFilterChange('day')">
                        <option value="">All Days</option>
                        @if(isset($availableDays) && $availableDays->count() > 0)
                            @foreach($availableDays as $day)
                                @php
                                    // Handle capitalized abbreviated day names from database (Mon, Tue, Wed, Thu, Fri)
                                    $dayMap = [
                                        'Mon' => 'mon', 'Tue' => 'tue', 'Wed' => 'wed',
                                        'Thu' => 'thur', 'Fri' => 'fri',
                                        'Monday' => 'mon', 'Tuesday' => 'tue', 'Wednesday' => 'wed',
                                        'Thursday' => 'thur', 'Friday' => 'fri',
                                        'mon' => 'mon', 'tue' => 'tue', 'wed' => 'wed',
                                        'thur' => 'thur', 'fri' => 'fri'
                                    ];
                                    
                                    $displayMap = [
                                        'Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday',
                                        'Thu' => 'Thursday', 'Fri' => 'Friday',
                                        'Monday' => 'Monday', 'Tuesday' => 'Tuesday', 'Wednesday' => 'Wednesday',
                                        'Thursday' => 'Thursday', 'Friday' => 'Friday',
                                        'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday',
                                        'thur' => 'Thursday', 'fri' => 'Friday'
                                    ];
                                    
                                    $dayValue = $dayMap[$day] ?? strtolower($day);
                                    $dayDisplay = $displayMap[$day] ?? ucfirst($day);
                                @endphp
                                @if(in_array($day, ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'mon', 'tue', 'wed', 'thur', 'fri']))
                                    <option value="{{ $dayValue }}" {{ request('day') == $dayValue ? 'selected' : '' }}>
                                        {{ $dayDisplay }}
                                    </option>
                                @endif
                            @endforeach
                        @else
                            <!-- Fallback options if availableDays is not set or empty -->
                            <option value="mon" {{ request('day') == 'mon' ? 'selected' : '' }}>Monday</option>
                            <option value="tue" {{ request('day') == 'tue' ? 'selected' : '' }}>Tuesday</option>
                            <option value="wed" {{ request('day') == 'wed' ? 'selected' : '' }}>Wednesday</option>
                            <option value="thur" {{ request('day') == 'thur' ? 'selected' : '' }}>Thursday</option>
                            <option value="fri" {{ request('day') == 'fri' ? 'selected' : '' }}>Friday</option>
                        @endif
                    </select>
                    <select name="status" id="filterStatus" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="handleFilterChange('status')">
                        <option value="">All Status</option>
                        <option value="fully_assigned" {{ request('status') == 'fully_assigned' ? 'selected' : '' }}>Fully Assigned</option>
                        <option value="partially_assigned" {{ request('status') == 'partially_assigned' ? 'selected' : '' }}>Partially Assigned</option>
                        <option value="not_assigned" {{ request('status') == 'not_assigned' ? 'selected' : '' }}>Not Assigned</option>
                    </select>
                    @if(request()->hasAny(['search', 'date', 'day', 'status']))
                    <a href="{{ route('schedules.index', ['tab' => 'class']) }}" 
                       onclick="event.preventDefault(); document.getElementById('filterForm').reset(); removePageParam(); window.location='{{ route('schedules.index', ['tab' => 'class']) }}';"
                       class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-700 transition-colors">
                        Clear
                    </a>
                    @endif
                </div>
            </div>
            <script>
                function removePageParam() {
                    // Remove the page param if present so search always starts at page 1
                    const url = new URL(window.location.href);
                    url.searchParams.delete('page');
                    window.history.replaceState({}, '', url);
                }
                function handleFilterChange(changed) {
                    // Only one filter can be active at a time
                    if (changed === 'date') {
                        document.getElementById('filterDay').value = '';
                        document.getElementById('filterStatus').value = '';
                    } else if (changed === 'day') {
                        document.getElementById('filterDate').value = '';
                        document.getElementById('filterStatus').value = '';
                    } else if (changed === 'status') {
                        document.getElementById('filterDate').value = '';
                        document.getElementById('filterDay').value = '';
                    }
                    
                    // Use requestAnimationFrame to ensure DOM is updated
                    requestAnimationFrame(function() {
                        document.getElementById('filterForm').submit();
                    });
                }
            </script>
        </form>
    </div>

    <!-- Class Scheduling Table -->
    <div class="bg-white overflow-x-auto" id="tableContainer">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Day</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Schools</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Classes</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total Required</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supervisor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                @include('schedules.partials.class-table-rows', ['dailyData' => $dailyData])
            </tbody>
        </table>

        <!-- No Search Results Message -->
        <div id="noSearchResults" class="hidden bg-white px-6 py-8 text-center text-gray-500 border-t border-gray-200">
            <i class="fas fa-search text-4xl mb-4 opacity-50"></i>
            <p class="text-lg font-medium">No schools found</p>
            <p class="text-sm">Try adjusting your search terms</p>
        </div>
    </div>

    <!-- Upload Progress Modal -->
    <div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 w-96 border border-gray-200">
            <div class="text-center">
                <div class="mb-4">
                    <i id="uploadIcon" class="fas fa-file-upload text-4xl text-blue-600"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">Uploading Excel File</h3>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
                    <div id="uploadProgress" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <p id="uploadStatus" class="text-sm text-gray-600">Preparing upload...</p>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div id="paginationContainer">
        @include('schedules.partials.compact-class-pagination', ['dailyData' => $dailyData])
    </div>

    <!-- Scripts -->
    <script>
        // Set global variables for JavaScript files
        window.uploadRoute = '{{ route("import.upload") }}';
        window.csrfToken = '{{ csrf_token() }}';
        window.searchSchedulesRoute = '{{ route("api.search-schedules") }}';
    </script>
    <script src="{{ asset('js/class-scheduling-search.js') }}"></script>
    <script src="{{ asset('js/class-scheduling.js') }}"></script>
    <script src="{{ asset('js/excel-upload.js') }}"></script>
@endif
