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

<div class="bg-white px-6 pt-6 pb-4 border-b border-gray-200">
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

            <button type="button" onclick="document.getElementById('excelFileInput').click()"
                class="flex items-center space-x-2 bg-[#0E335D] text-white px-4 py-2 rounded-md text-sm font-medium 
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
    <div class="overflow-x-auto" id="tableContainer">
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
                            } elseif ($rawStatus === 'pending_acceptance') {
                                $statusDisplay = 'Pending Acceptance';
                                $dotColor = 'bg-blue-500';
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
                            <button type="button" onclick="openScheduleDetailsModal('{{ $viewDate }}', '{{ $data->school }}', {{ json_encode($data) }})" 
                                    class="w-8 h-8 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 inline-flex items-center justify-center transition-colors"
                                    title="View Details">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                            
                            <!-- Assign Supervisor Button -->
                            <button type="button" onclick="openAssignSupervisorModal('{{ $viewDate }}', '{{ $data->school }}', '{{ $data->time }}', {{ json_encode([
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
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="bg-[#0E335D] text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
                <h2 class="text-xl font-bold">Schedule Information</h2>
                <button type="button" onclick="closeScheduleDetailsModal()" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="overflow-y-auto flex-grow">
                <div class="p-6">
                    <!-- Schedule Overview Card -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-5 mb-6 border border-blue-200 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                            Schedule Overview
                        </h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Date -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                                <p class="text-sm font-semibold text-gray-800" id="detail-date">-</p>
                            </div>
                            
                            <!-- Day -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Day</label>
                                <p class="text-sm font-semibold text-gray-800" id="detail-day">-</p>
                            </div>
                            
                            <!-- Time -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Time</label>
                                <p class="text-sm font-semibold text-gray-800" id="detail-times">-</p>
                            </div>
                            
                            <!-- Duration -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Duration</label>
                                <p class="text-sm font-semibold text-gray-800" id="detail-duration">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- School & Class Information -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-5 mb-6 border border-green-200 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-school text-green-600 mr-2"></i>
                            School & Class Details
                        </h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <!-- School -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">School</label>
                                <p class="text-sm font-semibold text-gray-800" id="detail-school">-</p>
                            </div>
                            
                            <!-- Account -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Account</label>
                                <p class="text-sm font-semibold text-gray-800" id="detail-account">-</p>
                            </div>
                            
                            <!-- Class -->
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                                <p class="text-sm font-semibold text-gray-800" id="detail-classes">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Information -->
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-5 mb-6 border border-purple-200 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-users text-purple-600 mr-2"></i>
                            Assignment Details
                        </h3>
                        
                        <div class="space-y-4">
                            <!-- Status -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Status</label>
                                <div id="detail-status"></div>
                            </div>
                            
                            <!-- Supervisor -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Assigned Supervisor</label>
                                <p class="text-sm font-semibold text-gray-800" id="detail-supervisor">-</p>
                            </div>
                            
                            <!-- Main Tutor -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Main Tutor</label>
                                <div class="bg-white rounded-md px-3 py-2 border border-gray-200">
                                    <p class="text-sm font-semibold text-gray-800" id="detail-main-tutor">-</p>
                                </div>
                            </div>
                            
                            <!-- Backup Tutor -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Backup Tutor</label>
                                <div class="bg-white rounded-md px-3 py-2 border border-gray-200">
                                    <p class="text-sm font-semibold text-gray-800" id="detail-backup-tutor">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 flex justify-between items-center border-t flex-shrink-0">
                <input type="hidden" id="detail-schedule-id" value="">
                <input type="hidden" id="detail-assignment-id" value="">
                <input type="hidden" id="detail-raw-status" value="">
                
                <!-- Cancel Button (only for assigned schedules) -->
                <button type="button" id="cancelScheduleButton" onclick="handleCancelSchedule()" class="hidden px-6 py-2 bg-red-600 text-white rounded-md font-semibold hover:bg-red-700 transition-colors flex items-center gap-2 shadow-sm">
                    <i class="fas fa-ban"></i>
                    <span>Cancel Schedule</span>
                </button>

                <!-- Finalize Button (only for partially_assigned) -->
                <button type="button" id="finalizeButton" onclick="confirmFinalizeSchedule()" class="hidden px-6 py-2 bg-green-600 text-white rounded-md font-semibold hover:bg-green-700 transition-colors flex items-center gap-2 shadow-sm">
                    <i class="fas fa-check-circle"></i>
                    <span>Mark as Fully Assigned</span>
                </button>
                
                <div class="flex-1"></div>
                
                <button type="button" onclick="closeScheduleDetailsModal()" class="px-6 py-2 bg-gray-500 text-white rounded-md font-semibold hover:bg-gray-600 transition-colors shadow-sm">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Finalize Confirmation Modal -->
    <div id="finalizeConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-start mb-4">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3 mr-4">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Finalize Schedule Assignment</h3>
                        <p class="text-sm text-gray-600">
                            Are you sure you want to mark this schedule as <strong>Fully Assigned</strong>?
                        </p>
                        <p class="text-sm text-gray-500 mt-2">
                            This action will finalize the assignment and notify the assigned tutors.
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-lg">
                <button type="button" onclick="closeFinalizeModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md font-medium hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="proceedFinalize()" 
                        class="px-4 py-2 bg-green-600 text-white rounded-md font-medium hover:bg-green-700 transition-colors">
                    OK
                </button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-start mb-4">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3 mr-4">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Success!</h3>
                        <p class="text-sm text-gray-600" id="successMessage">
                            Schedule finalized successfully! 2 tutor(s) have been notified.
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end rounded-b-lg">
                <button type="button" onclick="closeSuccessModal()" 
                        class="px-4 py-2 bg-green-600 text-white rounded-md font-medium hover:bg-green-700 transition-colors">
                    OK
                </button>
            </div>
        </div>
    </div>

    <!-- Cancel Schedule Modal -->
    <div id="cancelScheduleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg w-full max-w-2xl shadow-2xl">
            <div class="bg-gradient-to-r from-red-600 to-red-700 p-6 rounded-t-lg flex items-center justify-between">
                <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    Cancel Schedule
                </h2>
                <button type="button" onclick="closeCancelScheduleModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <form id="cancelScheduleForm" method="POST">
                @csrf
                <div class="p-6 space-y-6">
                    <!-- Warning Banner -->
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3 mt-0.5"></i>
                            <div>
                                <h4 class="font-semibold text-red-800 mb-1">Important Notice</h4>
                                <p class="text-sm text-red-700">
                                    Cancelling this schedule will:
                                </p>
                                <ul class="list-disc list-inside text-sm text-red-700 mt-2 space-y-1">
                                    <li>Block payment for the original main tutor</li>
                                    <li>Promote the backup tutor to main tutor (if available)</li>
                                    <li>Notify all affected tutors</li>
                                    <li>Require reassignment if no backup is available</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-700 mb-3">Schedule Details</h4>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-500">Date:</span>
                                <span id="cancel-schedule-date" class="font-medium text-gray-700 ml-2">-</span>
                            </div>
                            <div>
                                <span class="text-gray-500">School:</span>
                                <span id="cancel-schedule-school" class="font-medium text-gray-700 ml-2">-</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Class:</span>
                                <span id="cancel-schedule-class" class="font-medium text-gray-700 ml-2">-</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Main Tutor:</span>
                                <span id="cancel-schedule-main-tutor" class="font-medium text-gray-700 ml-2">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Cancellation Reason -->
                    <div>
                        <label for="cancellation-reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Cancellation Reason <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="cancellation-reason" 
                            name="cancellation_reason" 
                            rows="4" 
                            required
                            placeholder="Please provide a detailed reason for cancelling this schedule..."
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">This reason will be included in the notification to all affected parties.</p>
                    </div>

                    <!-- Cancelled By -->
                    <input type="hidden" id="cancelled-by" name="cancelled_by" value="supervisor">
                    <input type="hidden" id="cancel-assignment-id" name="assignment_id">
                </div>

                <!-- Actions -->
                <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex items-center justify-end gap-3">
                    <button type="button" onclick="closeCancelScheduleModal()" 
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                        Keep Schedule
                    </button>
                    <button type="submit" id="confirmCancelButton"
                        class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
                        <i class="fas fa-ban"></i>
                        <span>Confirm Cancellation</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Supervisor Modal -->
    <div id="assignSupervisorModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg w-full max-w-xl mx-4">
            <!-- Header -->
            <div class="bg-[#0E335D] text-white px-6 py-4 flex items-center justify-between">
                <h2 class="text-xl font-bold">Assign Supervisor to Watch Tutor</h2>
                <button type="button" onclick="closeAssignSupervisorModal()" class="text-white hover:text-gray-200">
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
                <button type="button" onclick="closeAssignSupervisorModal()" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="button" onclick="submitAssignSupervisor()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
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
            console.log('Day value:', data.day);
            
            // Store schedule ID and assignment ID
            document.getElementById('detail-schedule-id').value = data.id || '';
            document.getElementById('detail-assignment-id').value = data.assignment_id || '';
            
            // Format date
            const dateObj = new Date(date);
            document.getElementById('detail-date').textContent = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            
            // Day - if not provided in data, get from date
            let dayName = data.day;
            if (!dayName || dayName.trim() === '') {
                const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                dayName = days[dateObj.getDay()];
            }
            document.getElementById('detail-day').textContent = dayName || '-';
            
            // School
            document.getElementById('detail-school').textContent = data.school || school || '-';
            
            // Class
            document.getElementById('detail-classes').textContent = data.class || '-';
            
            // Time - format it nicely
            if (data.time) {
                try {
                    const timeObj = new Date('2000-01-01 ' + data.time);
                    document.getElementById('detail-times').textContent = timeObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                } catch (e) {
                    document.getElementById('detail-times').textContent = data.time;
                }
            } else {
                document.getElementById('detail-times').textContent = '-';
            }
            
            // Duration
            if (data.duration) {
                document.getElementById('detail-duration').textContent = data.duration + ' minutes';
            } else {
                document.getElementById('detail-duration').textContent = '25 minutes';
            }
            
            // Account name - use the actual account_name from data
            document.getElementById('detail-account').textContent = data.account_name || data.school || school || '-';
            
            // Status - use raw database value with better styling
            const statusDiv = document.getElementById('detail-status');
            const rawStatus = data.raw_class_status || null;
            let statusText = 'Not Assigned';
            let statusColor = 'bg-red-100 text-red-800 border-red-200';
            let iconClass = 'fa-times-circle';
            
            if (rawStatus === 'fully_assigned') {
                statusText = 'Fully Assigned';
                statusColor = 'bg-green-100 text-green-800 border-green-200';
                iconClass = 'fa-check-circle';
            } else if (rawStatus === 'partially_assigned') {
                statusText = 'Partially Assigned';
                statusColor = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                iconClass = 'fa-exclamation-circle';
            } else if (rawStatus === 'pending_acceptance') {
                statusText = 'Pending Acceptance';
                statusColor = 'bg-blue-100 text-blue-800 border-blue-200';
                iconClass = 'fa-clock';
            } else if (rawStatus === 'cancelled') {
                statusText = 'Cancelled';
                statusColor = 'bg-gray-100 text-gray-800 border-gray-200';
                iconClass = 'fa-ban';
            } else if (rawStatus === 'not_assigned') {
                statusText = 'Not Assigned';
                statusColor = 'bg-red-100 text-red-800 border-red-200';
                iconClass = 'fa-times-circle';
            }
            
            statusDiv.innerHTML = `
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border ${statusColor}">
                    <i class="fas ${iconClass}"></i>
                    <span class="font-semibold">${statusText}</span>
                </div>
            `;
            
            // Store raw status
            document.getElementById('detail-raw-status').value = rawStatus || '';
            
            // Show/hide finalize button based on status
            const finalizeButton = document.getElementById('finalizeButton');
            if (rawStatus === 'partially_assigned') {
                finalizeButton.classList.remove('hidden');
            } else {
                finalizeButton.classList.add('hidden');
            }

            // Show/hide cancel button based on status (allow cancel for assigned schedules)
            const cancelButton = document.getElementById('cancelScheduleButton');
            if (rawStatus === 'fully_assigned' || rawStatus === 'partially_assigned') {
                cancelButton.classList.remove('hidden');
            } else {
                cancelButton.classList.add('hidden');
            }
            
            // Assigned Supervisor
            document.getElementById('detail-supervisor').textContent = data.assigned_supervisors || data.assigned_supervisor || 'None';
            
            // Main Tutor
            document.getElementById('detail-main-tutor').textContent = data.main_tutor_name || 'Not Assigned';
            
            // Backup Tutor
            document.getElementById('detail-backup-tutor').textContent = data.backup_tutor_name || 'Not Assigned';

            // Store schedule data for cancellation
            window.currentScheduleData = data;
            
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
                showNotification('Assignment ID not found', 'error');
                return;
            }
            
            // Show confirmation modal
            document.getElementById('finalizeConfirmModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeFinalizeModal() {
            document.getElementById('finalizeConfirmModal').classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        function proceedFinalize() {
            const assignmentId = document.getElementById('detail-assignment-id').value;
            const scheduleId = document.getElementById('detail-schedule-id').value;
            closeFinalizeModal();
            finalizeSchedule(assignmentId, scheduleId);
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
                    closeScheduleDetailsModal();
                    showSuccessModal(data.message || 'Schedule finalized successfully! Tutors have been notified.');
                } else {
                    showErrorNotification('Error: ' + (data.message || 'Failed to finalize schedule'));
                    finalizeButton.disabled = false;
                    finalizeButton.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorNotification('An error occurred while finalizing the schedule');
                finalizeButton.disabled = false;
                finalizeButton.innerHTML = originalText;
            });
        }
        
        function showSuccessModal(message) {
            document.getElementById('successMessage').textContent = message;
            document.getElementById('successModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeSuccessModal() {
            document.getElementById('successModal').classList.add('hidden');
            document.body.style.overflow = '';
            location.reload(); // Refresh to show updated data
        }
        
        function showErrorNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg max-w-md';
            notification.innerHTML = `
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                    <p class="text-sm">${message}</p>
                </div>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transition = 'opacity 0.3s';
                setTimeout(() => document.body.removeChild(notification), 300);
            }, 4000);
        }

        // Handle cancel schedule button click
        function handleCancelSchedule() {
            const assignmentId = document.getElementById('detail-assignment-id').value;
            if (!assignmentId) {
                showErrorNotification('No assignment ID found');
                return;
            }
            
            if (!window.currentScheduleData) {
                showErrorNotification('Schedule data not available');
                return;
            }
            
            openCancelScheduleModal(assignmentId, window.currentScheduleData);
        }

        // Cancel Schedule Modal Functions
        function openCancelScheduleModal(assignmentId, scheduleData) {
            console.log('Opening cancel modal for assignment:', assignmentId, 'data:', scheduleData);
            
            // Set form action
            const form = document.getElementById('cancelScheduleForm');
            form.action = `/schedules/cancel/${assignmentId}`;
            
            // Set assignment ID
            document.getElementById('cancel-assignment-id').value = assignmentId;
            
            // Populate schedule details
            const dateObj = new Date(scheduleData.date || scheduleData.schedule_date);
            document.getElementById('cancel-schedule-date').textContent = 
                dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            
            document.getElementById('cancel-schedule-school').textContent = scheduleData.school || '-';
            document.getElementById('cancel-schedule-class').textContent = scheduleData.class || '-';
            document.getElementById('cancel-schedule-main-tutor').textContent = scheduleData.main_tutor_name || '-';
            
            // Reset form
            document.getElementById('cancellation-reason').value = '';
            
            // Show modal
            document.getElementById('cancelScheduleModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeCancelScheduleModal() {
            document.getElementById('cancelScheduleModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Handle cancel form submission
        document.getElementById('cancelScheduleForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const submitButton = document.getElementById('confirmCancelButton');
            const originalText = submitButton.innerHTML;
            
            // Disable button and show loading
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Cancelling...';
            
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeCancelScheduleModal();
                    closeScheduleDetailsModal();
                    showSuccessModal(data.message || 'Schedule cancelled successfully!');
                } else {
                    showErrorNotification('Error: ' + (data.message || 'Failed to cancel schedule'));
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorNotification('An error occurred while cancelling the schedule');
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    </script>
    <script src="{{ asset('js/class-scheduling-search.js') }}"></script>
    <script src="{{ asset('js/class-scheduling.js') }}"></script>
    <script src="{{ asset('js/excel-upload.js') }}"></script>
@endif
