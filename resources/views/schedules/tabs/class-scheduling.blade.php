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

<div class="bg-white px-0 pb-4 border-b border-gray-200">
    <div class="flex items-center justify-between">

        <!-- LEFT SIDE: LABEL + FILTERS IN ONE ROW -->
        <div class="flex items-center space-x-4 overflow-x-auto whitespace-nowrap">

            <h3 class="text-sm font-medium text-gray-700">Search Filters:</h3>

            <form method="GET" action="{{ route('schedules.index') }}" id="filterForm" class="flex items-center space-x-3">
                <input type="hidden" name="tab" value="class">

                <!-- Date -->
                <select name="date" id="filterDate"
                    class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="this.form.submit()">
                    <option value="">All Dates</option>
                    @if(isset($availableDates) && $availableDates->count() > 0)
                        @foreach($availableDates as $date)
                            <option value="{{ $date }}" {{ request('date') == $date ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                            </option>
                        @endforeach
                    @else
                        <option value="" disabled>No dates available</option>
                    @endif
                </select>

                <!-- Day -->
                <select name="day" id="filterDay"
                    class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="this.form.submit()">
                    <option value="">All Days</option>

                    @if(isset($availableDays) && $availableDays->count() > 0)
                        @foreach($availableDays as $day)
                            @php
                                $dayMap = [
                                    'Mon'=>'mon','Tue'=>'tue','Wed'=>'wed','Thu'=>'thur','Fri'=>'fri',
                                    'Monday'=>'mon','Tuesday'=>'tue','Wednesday'=>'wed','Thursday'=>'thur','Friday'=>'fri',
                                    'mon'=>'mon','tue'=>'tue','wed'=>'wed','thur'=>'thur','fri'=>'fri'
                                ];
                                $displayMap = [
                                    'Mon'=>'Monday','Tue'=>'Tuesday','Wed'=>'Wednesday','Thu'=>'Thursday','Fri'=>'Friday',
                                    'Monday'=>'Monday','Tuesday'=>'Tuesday','Wednesday'=>'Wednesday','Thursday'=>'Thursday','Friday'=>'Friday',
                                    'mon'=>'Monday','tue'=>'Tuesday','wed'=>'Wednesday','thur'=>'Thursday','fri'=>'Friday'
                                ];
                                $dayValue = $dayMap[$day] ?? strtolower($day);
                                $dayDisplay = $displayMap[$day] ?? ucfirst($day);
                            @endphp
                            <option value="{{ $dayValue }}" {{ request('day') == $dayValue ? 'selected' : '' }}>
                                {{ $dayDisplay }}
                            </option>
                        @endforeach
                    @endif
                </select>

                <!-- Status -->
                <select name="status" id="filterStatus"
                    class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="fully_assigned" {{ request('status') == 'fully_assigned' ? 'selected' : '' }}>Fully Assigned</option>
                    <option value="partially_assigned" {{ request('status') == 'partially_assigned' ? 'selected' : '' }}>Partially Assigned</option>
                    <option value="not_assigned" {{ request('status') == 'not_assigned' ? 'selected' : '' }}>Not Assigned</option>
                </select>

                <!-- Clear -->
                @if(request()->hasAny(['date', 'day', 'status']))
                    <a href="{{ route('schedules.index', ['tab' => 'class']) }}"
                        onclick="event.preventDefault(); document.getElementById('filterForm').reset(); removePageParam(); window.location='{{ route('schedules.index', ['tab' => 'class']) }}';"
                        class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-600">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- RIGHT SIDE: UPLOAD BUTTON -->
        <div class="relative">
            <input type="file" 
                   id="excelFileInput" 
                   accept=".xlsx,.xls,.csv" 
                   class="hidden"
                   onchange="uploadExcelFile()">

            <button onclick="document.getElementById('excelFileInput').click()"
                class="flex items-center space-x-2 bg-[#0E335D] text-white px-4 py-2 rounded-full text-sm font-medium 
                hover:bg-[#184679] transition duration-200 hover:scale-105">
                <i class="fas fa-file-excel"></i>
                <span>Upload Excel</span>
            </button>
        </div>

    </div>
</div>

<script>
function removePageParam() {
    const url = new URL(window.location.href);
    url.searchParams.delete('page');
    window.history.replaceState({}, '', url);
}
</script>


    <!-- Class Scheduling Table -->
    <div class="bg-white overflow-x-auto" id="tableContainer">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('schedules.index', array_merge(request()->query(), ['tab' => 'class', 'sort' => request('sort') === 'date' && request('direction') === 'desc' ? '' : 'date', 'direction' => request('sort') === 'date' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Date
                            @if(request('sort') === 'date')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('schedules.index', array_merge(request()->query(), ['tab' => 'class', 'sort' => request('sort') === 'day' && request('direction') === 'desc' ? '' : 'day', 'direction' => request('sort') === 'day' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Day
                            @if(request('sort') === 'day')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('schedules.index', array_merge(request()->query(), ['tab' => 'class', 'sort' => request('sort') === 'time' && request('direction') === 'desc' ? '' : 'time', 'direction' => request('sort') === 'time' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Time
                            @if(request('sort') === 'time')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('schedules.index', array_merge(request()->query(), ['tab' => 'class', 'sort' => request('sort') === 'school' && request('direction') === 'desc' ? '' : 'school', 'direction' => request('sort') === 'school' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            School
                            @if(request('sort') === 'school')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('schedules.index', array_merge(request()->query(), ['tab' => 'class', 'sort' => request('sort') === 'status' && request('direction') === 'desc' ? '' : 'status', 'direction' => request('sort') === 'status' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Status
                            @if(request('sort') === 'status')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                @forelse($dailyData ?? [] as $data)
                <tr class="hover:bg-gray-50 table-row" data-searchable="{{ strtolower($data->school ?? '') }}">
                    <!-- Date -->
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $data->date ? \Carbon\Carbon::parse($data->date)->format('F j, Y') : '-' }}
                    </td>
                    
                    <!-- Day -->
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $data->day ?? '-' }}
                    </td>
                    
                    <!-- Time -->
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @if($data->time)
                            @php
                                $startTime = \Carbon\Carbon::parse($data->time);
                                $endTime = $data->duration ? $startTime->copy()->addMinutes($data->duration) : $startTime;
                            @endphp
                            {{ $startTime->format('g:i A') }} - {{ $endTime->format('g:i A') }}
                        @else
                            -
                        @endif
                    </td>
                    
                    <!-- School -->
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                        {{ $data->school ?? '-' }}
                    </td>
                    
                    <!-- Class -->
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $data->class ?? '-' }}
                    </td>
                    
                    <!-- Status - Display RAW database value -->
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @php
                            $rawStatus = $data->raw_class_status ?? null;

                            // Defaults
                            $statusDisplay = 'Not Assigned';
                            $dotColor = 'bg-red-500';

                            if ($rawStatus === 'fully_assigned') {
                                $statusDisplay = 'Fully Assigned';
                                $dotColor = 'bg-green-500';
                            } elseif ($rawStatus === 'partially_assigned') {
                                $statusDisplay = 'Partially Assigned';
                                $dotColor = 'bg-yellow-500';
                            } elseif ($rawStatus === 'cancelled') {
                                $statusDisplay = 'Cancelled';
                                $dotColor = 'bg-gray-500';
                            } elseif ($rawStatus === 'not_assigned') {
                                $statusDisplay = 'Not Assigned';
                                $dotColor = 'bg-red-500';
                            }
                        @endphp

                        <div class="flex items-center gap-2">
                            <!-- Small round status dot -->
                            <span class="h-2.5 w-2.5 rounded-full {{ $dotColor }}"></span>
                            <span>{{ $statusDisplay }}</span>
                        </div>
                    </td>
  
                    <!-- Actions -->
                    <td class="px-6 py-4 text-sm">
                        @php
                            $viewDate = $data->date;
                            if ($viewDate instanceof \Carbon\Carbon) {
                                $viewDate = $viewDate->format('Y-m-d');
                            } else {
                                $viewDate = \Carbon\Carbon::parse($viewDate)->format('Y-m-d');
                            }
                            
                            // Check if current supervisor owns this schedule
                            $currentSupervisorId = session('supervisor_id');
                            if (!$currentSupervisorId && auth('supervisor')->check()) {
                                $currentSupervisorId = auth('supervisor')->user()->supID;
                            }
                            
                            $canModify = true;
                            if ($currentSupervisorId && !empty($data->assigned_supervisor_ids)) {
                                $assignedSupervisors = explode(', ', $data->assigned_supervisor_ids);
                                $canModify = in_array($currentSupervisorId, $assignedSupervisors);
                            }
                        @endphp
                        
                        <div class="flex items-center justify-center space-x-2">
                            <!-- View Details Button -->
                            <button onclick="openScheduleDetailsModal('{{ $viewDate }}', '{{ $data->school }}', {{ json_encode($data) }})" 
                                    class="w-8 h-8 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 inline-flex items-center justify-center transition-colors"
                                    title="View Details">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                            
                            <!-- Assign Supervisor Button -->
                            <button onclick="openAssignSupervisorModal('{{ $viewDate }}', '{{ $data->school }}', '{{ $data->time }}', {{ json_encode([
                                'status' => $data->raw_class_status,
                                'schedule_id' => $data->id,
                                'main_tutor_id' => $data->assignedData->main_tutor ?? null,
                                'backup_tutor_id' => $data->assignedData->backup_tutor ?? null,
                                'main_tutor_name' => $data->main_tutor_name ?? null,
                                'backup_tutor_name' => $data->backup_tutor_name ?? null
                            ]) }})" 
                                    class="w-8 h-8 bg-green-100 text-green-600 rounded hover:bg-green-200 inline-flex items-center justify-center transition-colors"
                                    title="Assign Tutor">
                                <i class="fas fa-user-plus text-xs"></i>
                            </button>
                            
                            <!-- Ownership Indicator -->
                            @if(!empty($data->assigned_supervisor_ids) && !$canModify)
                                <div class="w-8 h-8 bg-gray-100 text-gray-400 rounded inline-flex items-center justify-center"
                                     title="Owned by Another Supervisor - View Only">
                                    <i class="fas fa-user-lock text-xs"></i>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr id="noResultsRow">
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-calendar-times text-4xl mb-4 opacity-50"></i>
                        <p class="text-lg font-medium">No scheduling data found</p>
                        <p class="text-sm">Try adjusting your search criteria</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- No Search Results Message -->
        <div id="noSearchResults" class="hidden bg-white px-6 py-8 text-center text-gray-500 border-t">
            <i class="fas fa-search text-4xl mb-4 opacity-50"></i>
            <p class="text-lg font-medium">No schools found</p>
            <p class="text-sm">Try adjusting your search terms</p>
        </div>
    </div>

    <!-- Upload Progress Modal -->
    <div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 w-96">
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
        @include('schedules.partials.class-pagination', ['dailyData' => $dailyData])
    </div>

    <!-- Schedule Details Modal -->
    <div id="scheduleDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="bg-[#0E335D] text-white px-6 py-4 flex items-center justify-between sticky top-0 z-10">
                <h2 class="text-xl font-bold">Schedule Details</h2>
                <button onclick="closeScheduleDetailsModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6 space-y-4">
                <!-- Date & Day -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                        <label class="block text-sm font-bold text-blue-900 uppercase mb-2">Date</label>
                        <input type="text" id="detail-date" readonly class="w-full bg-transparent border-0 font-semibold text-blue-900 focus:outline-none">
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
                        <label class="block text-sm font-bold text-purple-900 uppercase mb-2">Day</label>
                        <input type="text" id="detail-day" readonly class="w-full bg-transparent border-0 font-semibold text-purple-900 focus:outline-none">
                    </div>
                </div>
                
                <!-- School -->
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 p-4 rounded-lg border border-indigo-200">
                    <label class="block text-sm font-bold text-indigo-900 uppercase mb-2">School</label>
                    <input type="text" id="detail-school" readonly class="w-full bg-transparent border-0 font-semibold text-indigo-900 focus:outline-none">
                </div>
                
                <!-- Classes -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
                    <label class="block text-sm font-bold text-green-900 uppercase mb-2">Classes</label>
                    <textarea id="detail-classes" readonly rows="2" class="w-full bg-transparent border-0 font-semibold text-green-900 focus:outline-none resize-none"></textarea>
                </div>
                
                <!-- Time Slots -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 rounded-lg border border-yellow-200">
                    <label class="block text-sm font-bold text-yellow-900 uppercase mb-2">Time Slots</label>
                    <textarea id="detail-times" readonly rows="3" class="w-full bg-transparent border-0 font-semibold text-yellow-900 focus:outline-none resize-none"></textarea>
                </div>
                
                <!-- Duration -->
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-4 rounded-lg border border-orange-200">
                    <label class="block text-sm font-bold text-orange-900 uppercase mb-2">Duration</label>
                    <input type="text" id="detail-duration" readonly class="w-full bg-transparent border-0 font-semibold text-orange-900 focus:outline-none">
                </div>
                
                <!-- Status & Account -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gradient-to-br from-red-50 to-red-100 p-4 rounded-lg border border-red-200">
                        <label class="block text-sm font-bold text-red-900 uppercase mb-2">Status</label>
                        <div id="detail-status"></div>
                    </div>
                    <div class="bg-gradient-to-br from-pink-50 to-pink-100 p-4 rounded-lg border border-pink-200">
                        <label class="block text-sm font-bold text-pink-900 uppercase mb-2">Account</label>
                        <input type="text" id="detail-account" readonly class="w-full bg-transparent border-0 font-semibold text-pink-900 focus:outline-none">
                    </div>
                </div>
                
                <!-- Assigned Supervisor -->
                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 p-4 rounded-lg border border-emerald-200">
                    <label class="block text-sm font-bold text-emerald-900 uppercase mb-2">Assigned Supervisor</label>
                    <input type="text" id="detail-supervisor" readonly class="w-full bg-transparent border-0 font-semibold text-emerald-900 focus:outline-none">
                </div>
                
                <!-- Main Tutor -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                    <label class="block text-sm font-bold text-blue-900 uppercase mb-2">Main Tutor</label>
                    <input type="text" id="detail-main-tutor" readonly class="w-full bg-transparent border-0 font-semibold text-blue-900 focus:outline-none">
                </div>
                
                <!-- Backup Tutor -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
                    <label class="block text-sm font-bold text-purple-900 uppercase mb-2">Backup Tutor</label>
                    <input type="text" id="detail-backup-tutor" readonly class="w-full bg-transparent border-0 font-semibold text-purple-900 focus:outline-none">
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 flex justify-between items-center border-t">
                <input type="hidden" id="detail-schedule-id" value="">
                <input type="hidden" id="detail-assignment-id" value="">
                <input type="hidden" id="detail-raw-status" value="">
                
                <!-- Finalize Button (only for partially_assigned) -->
                <button id="finalizeButton" onclick="confirmFinalizeSchedule()" class="hidden px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <span>Mark as Fully Assigned</span>
                </button>
                
                <div class="flex-1"></div>
                
                <button onclick="closeScheduleDetailsModal()" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Assign Supervisor Modal -->
    <div id="assignSupervisorModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg w-full max-w-xl mx-4">
            <!-- Header -->
            <div class="bg-[#0E335D] text-white px-6 py-4 flex items-center justify-between">
                <h2 class="text-xl font-bold">Assign Supervisor to Watch Tutor</h2>
                <button onclick="closeAssignSupervisorModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6 space-y-4">
                <!-- Schedule Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-1">Schedule</p>
                    <p class="text-lg font-semibold text-gray-900" id="assign-schedule-info">N/A</p>
                </div>
                
                <!-- Supervisor Selection (Auto-filled) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Supervisor</label>
                    @php
                        $loggedInSupervisor = Auth::guard('supervisor')->user();
                    @endphp
                    @if($loggedInSupervisor)
                        <input type="text" readonly value="{{ $loggedInSupervisor->full_name }} ({{ $loggedInSupervisor->assigned_account }})" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 font-medium">
                        <input type="hidden" id="assign-supervisor" value="{{ $loggedInSupervisor->supervisor_id }}">
                        <input type="hidden" id="supervisor-account" value="{{ $loggedInSupervisor->assigned_account }}">
                    @else
                        <input type="text" readonly value="Not logged in as supervisor" class="w-full px-4 py-3 border border-red-300 rounded-lg bg-red-50 text-red-700">
                    @endif
                </div>
                
                <!-- Main Tutor Selection (Filtered by account and availability) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Main Tutor <span class="text-red-500">*</span></label>
                    <select id="assign-main-tutor" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" onchange="updateBackupTutorOptions()">
                        <option value="">Loading tutors...</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Showing only tutors from your account who are available for this schedule</p>
                </div>
                
                <!-- Backup Tutor Selection (Filtered by account and availability) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Backup Tutor</label>
                    <select id="assign-backup-tutor" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="">-- Select Backup Tutor --</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Cannot select the same tutor as Main Tutor</p>
                </div>
                
                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <textarea id="assign-notes" rows="3" placeholder="Any special instructions or notes..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"></textarea>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3 border-t">
                <button onclick="closeAssignSupervisorModal()" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button onclick="submitAssignSupervisor()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                    <i class="fas fa-user-plus"></i>
                    Assign
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Set global variables for JavaScript files
        window.uploadRoute = '{{ route("import.upload") }}';
        window.csrfToken = '{{ csrf_token() }}';
        window.searchSchedulesRoute = '{{ route("api.search-schedules") }}';
        
        // Modal Functions
        function openScheduleDetailsModal(date, school, data) {
            console.log('Opening modal with data:', data); // Debug
            
            // Store schedule ID and assignment ID
            document.getElementById('detail-schedule-id').value = data.id || '';
            document.getElementById('detail-assignment-id').value = data.assignment_id || '';
            
            // Format date
            document.getElementById('detail-date').value = new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            
            // Day
            document.getElementById('detail-day').value = data.day || '-';
            
            // School
            document.getElementById('detail-school').value = data.school || school || '-';
            
            // Class
            document.getElementById('detail-classes').value = data.class || '-';
            
            // Time - format it nicely
            if (data.time) {
                try {
                    const timeObj = new Date('2000-01-01 ' + data.time);
                    document.getElementById('detail-times').value = timeObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                } catch (e) {
                    document.getElementById('detail-times').value = data.time;
                }
            } else {
                document.getElementById('detail-times').value = '-';
            }
            
            // Duration
            if (data.duration) {
                document.getElementById('detail-duration').value = data.duration + ' minutes';
            } else {
                document.getElementById('detail-duration').value = '25 minutes';
            }
            
            // Account name - use the actual account_name from data
            document.getElementById('detail-account').value = data.account_name || data.school || school || '-';
            
            // Status - use raw database value
            const statusDiv = document.getElementById('detail-status');
            const rawStatus = data.raw_class_status || null;
            let statusText = 'Not Assigned';
            let statusColor = 'bg-red-100 text-red-800';
            
            if (rawStatus === 'fully_assigned') {
                statusText = 'Fully Assigned';
                statusColor = 'bg-green-100 text-green-800';
            } else if (rawStatus === 'partially_assigned') {
                statusText = 'Partially Assigned';
                statusColor = 'bg-yellow-100 text-yellow-800';
            } else if (rawStatus === 'cancelled') {
                statusText = 'Cancelled';
                statusColor = 'bg-gray-200 text-gray-800';
            } else if (rawStatus === 'not_assigned') {
                statusText = 'Not Assigned';
                statusColor = 'bg-red-100 text-red-800';
            }
            
            statusDiv.innerHTML = `<span class="px-3 py-1 rounded-full text-sm font-medium ${statusColor}">${statusText}</span><br><span class="text-xs text-gray-500 mt-1">DB: ${rawStatus || 'null'}</span>`;
            
            // Store raw status
            document.getElementById('detail-raw-status').value = rawStatus || '';
            
            // Show/hide finalize button based on status
            const finalizeButton = document.getElementById('finalizeButton');
            if (rawStatus === 'partially_assigned') {
                finalizeButton.classList.remove('hidden');
            } else {
                finalizeButton.classList.add('hidden');
            }
            
            // Assigned Supervisor
            document.getElementById('detail-supervisor').value = data.assigned_supervisors || data.assigned_supervisor || 'None';
            
            // Main Tutor
            document.getElementById('detail-main-tutor').value = data.main_tutor_name || 'Not Assigned';
            
            // Backup Tutor
            document.getElementById('detail-backup-tutor').value = data.backup_tutor_name || 'Not Assigned';
            
            document.getElementById('scheduleDetailsModal').classList.remove('hidden');
        }
        
        function closeScheduleDetailsModal() {
            document.getElementById('scheduleDetailsModal').classList.add('hidden');
        }
        
        let assignScheduleDate = '';
        let assignScheduleAccount = '';
        let assignScheduleTime = '';
        let currentScheduleData = null;
        
        function openAssignSupervisorModal(date, account, time = null, scheduleData = null) {
            assignScheduleDate = date;
            assignScheduleAccount = account;
            assignScheduleTime = time;
            currentScheduleData = scheduleData;
            
            document.getElementById('assign-schedule-info').textContent = `${account} - ${new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}`;
            document.getElementById('assign-notes').value = '';
            
            // Reset dropdowns
            const mainTutorDropdown = document.getElementById('assign-main-tutor');
            const backupTutorDropdown = document.getElementById('assign-backup-tutor');
            
            // If status is partially_assigned, show placeholder to avoid reassigning
            if (scheduleData && scheduleData.status === 'partially_assigned') {
                if (scheduleData.main_tutor_id) {
                    mainTutorDropdown.innerHTML = `<option value="">Select a different main tutor (Current: ${scheduleData.main_tutor_name || 'Assigned'})</option>`;
                }
                if (scheduleData.backup_tutor_id) {
                    backupTutorDropdown.innerHTML = `<option value="">Select a different backup tutor (Current: ${scheduleData.backup_tutor_name || 'Assigned'})</option>`;
                }
            }
            
            document.getElementById('assignSupervisorModal').classList.remove('hidden');
            
            // Fetch available tutors for this date/time and supervisor's account
            const supervisorAccount = document.getElementById('supervisor-account')?.value;
            
            if (supervisorAccount) {
                fetchAvailableTutors(date, time, supervisorAccount);
            } else {
                mainTutorDropdown.innerHTML = '<option value="">No supervisor account found</option>';
                backupTutorDropdown.innerHTML = '<option value="">No supervisor account found</option>';
            }
        }
        
        // Store all available tutors for filtering
        let allAvailableTutors = [];
        
        function fetchAvailableTutors(date, time, account) {
            const mainTutorDropdown = document.getElementById('assign-main-tutor');
            const backupTutorDropdown = document.getElementById('assign-backup-tutor');
            
            // Show loading state unless partially assigned status has set placeholders
            if (!mainTutorDropdown.innerHTML.includes('Current:')) {
                mainTutorDropdown.innerHTML = '<option value="">Loading tutors...</option>';
            }
            if (!backupTutorDropdown.innerHTML.includes('Current:')) {
                backupTutorDropdown.innerHTML = '<option value="">-- Select Backup Tutor --</option>';
            }
            
            // Build query parameters
            const params = new URLSearchParams({
                date: date,
                account: account,
                exclude_time_conflicts: 1  // Request backend to filter out tutors with time conflicts
            });
            
            if (time) {
                params.append('time', time);
            }
            
            // Fetch tutors via AJAX
            fetch(`{{ route('api.available-tutors') }}?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.tutors.length > 0) {
                        allAvailableTutors = data.tutors;
                        
                        // Set main tutor dropdown with placeholder for partially assigned
                        const mainPlaceholder = currentScheduleData && currentScheduleData.status === 'partially_assigned' && currentScheduleData.main_tutor_id
                            ? `Select a different main tutor (Current: ${currentScheduleData.main_tutor_name || 'Assigned'})`
                            : '-- Select Main Tutor --';
                        mainTutorDropdown.innerHTML = `<option value="">${mainPlaceholder}</option>`;
                        
                        data.tutors.forEach(tutor => {
                            // Skip if this tutor is currently assigned to avoid reassignment
                            if (currentScheduleData && (tutor.id == currentScheduleData.main_tutor_id || tutor.id == currentScheduleData.backup_tutor_id)) {
                                return;
                            }
                            
                            const option = document.createElement('option');
                            option.value = tutor.id;
                            // Format: "TutorID - Full Name - Availability"
                            option.textContent = `${tutor.tutorID} - ${tutor.name} - ${tutor.availability}`;
                            mainTutorDropdown.appendChild(option);
                        });
                        updateBackupTutorOptions();
                    } else {
                        mainTutorDropdown.innerHTML = '<option value="">No tutors available for this schedule</option>';
                        backupTutorDropdown.innerHTML = '<option value="">No tutors available</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching tutors:', error);
                    mainTutorDropdown.innerHTML = '<option value="">Error loading tutors</option>';
                    backupTutorDropdown.innerHTML = '<option value="">Error loading tutors</option>';
                });
        }
        
        function updateBackupTutorOptions() {
            const mainTutorId = document.getElementById('assign-main-tutor').value;
            const backupTutorDropdown = document.getElementById('assign-backup-tutor');
            
            // Set backup tutor dropdown with placeholder for partially assigned
            const backupPlaceholder = currentScheduleData && currentScheduleData.status === 'partially_assigned' && currentScheduleData.backup_tutor_id
                ? `Select a different backup tutor (Current: ${currentScheduleData.backup_tutor_name || 'Assigned'})`
                : '-- Select Backup Tutor --';
            backupTutorDropdown.innerHTML = `<option value="">${backupPlaceholder}</option>`;
            
            // Populate backup tutor options, excluding the selected main tutor and currently assigned tutors
            allAvailableTutors.forEach(tutor => {
                // Exclude if: selected as main, or currently assigned to this schedule
                if (tutor.id != mainTutorId && 
                    !(currentScheduleData && (tutor.id == currentScheduleData.main_tutor_id || tutor.id == currentScheduleData.backup_tutor_id))) {
                    const option = document.createElement('option');
                    option.value = tutor.id;
                    // Format: "TutorID - Full Name - Availability"
                    option.textContent = `${tutor.tutorID} - ${tutor.name} - ${tutor.availability}`;
                    backupTutorDropdown.appendChild(option);
                }
            });
        }
        
        function closeAssignSupervisorModal() {
            document.getElementById('assignSupervisorModal').classList.add('hidden');
        }
        
        function submitAssignSupervisor() {
            const mainTutorId = document.getElementById('assign-main-tutor').value;
            const backupTutorId = document.getElementById('assign-backup-tutor').value;
            const notes = document.getElementById('assign-notes').value;
            
            if (!mainTutorId) {
                alert('Please select a main tutor');
                return;
            }
            
            // Show loading state
            const submitBtn = event.target;
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Assigning...';
            
            // Send AJAX request
            fetch('{{ route("api.assign-tutor") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    date: assignScheduleDate,
                    school: assignScheduleAccount,
                    time: assignScheduleTime,
                    main_tutor_id: mainTutorId,
                    backup_tutor_id: backupTutorId || null,
                    notes: notes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Tutor assigned successfully!');
                    closeAssignSupervisorModal();
                    location.reload(); // Refresh to show updated data
                } else {
                    alert('Error: ' + (data.message || 'Failed to assign tutor'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while assigning the tutor');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        }
        
        // Finalize schedule functions
        function confirmFinalizeSchedule() {
            const assignmentId = document.getElementById('detail-assignment-id').value;
            const scheduleId = document.getElementById('detail-schedule-id').value;
            
            if (!assignmentId) {
                alert('Assignment ID not found');
                return;
            }
            
            if (confirm('Are you sure you want to mark this schedule as Fully Assigned? This action will finalize the assignment and notify the assigned tutors.')) {
                finalizeSchedule(assignmentId, scheduleId);
            }
        }
        
        function finalizeSchedule(assignmentId, scheduleId) {
            const finalizeButton = document.getElementById('finalizeButton');
            const originalText = finalizeButton.innerHTML;
            
            // Show loading state
            finalizeButton.disabled = true;
            finalizeButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Finalizing...';
            
            // Send AJAX request
            fetch('{{ route("api.finalize-schedule") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    assignment_id: assignmentId,
                    schedule_id: scheduleId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Schedule finalized successfully! Tutors have been notified.');
                    closeScheduleDetailsModal();
                    location.reload(); // Refresh to show updated data
                } else {
                    alert('Error: ' + (data.message || 'Failed to finalize schedule'));
                    finalizeButton.disabled = false;
                    finalizeButton.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while finalizing the schedule');
                finalizeButton.disabled = false;
                finalizeButton.innerHTML = originalText;
            });
        }
    </script>
    <script src="{{ asset('js/class-scheduling-search.js') }}"></script>
    <script src="{{ asset('js/class-scheduling.js') }}"></script>
    <script src="{{ asset('js/excel-upload.js') }}"></script>
@endif
