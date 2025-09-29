@php
    // Get all classes for the selected date with tutor assignments (including cancelled for management)
    $dayClasses = \App\Models\DailyData::where('date', $date)
                                       ->with(['tutorAssignments.tutor'])
                                       ->orderBy('school')
                                       ->orderBy('time_jst')
                                       ->get();
    
    // Check if this schedule is finalized
    $isFinalized = $dayClasses->where('schedule_status', 'final')->count() > 0;
    $finalizedAt = $isFinalized ? $dayClasses->where('schedule_status', 'final')->first()->finalized_at : null;
    
    // Get grouped information for the header (only active classes for statistics)
    $dayInfo = \App\Models\DailyData::select([
        'date',
        'day',
        \DB::raw('GROUP_CONCAT(DISTINCT school ORDER BY school ASC SEPARATOR ", ") as schools'),
        \DB::raw('COUNT(*) as class_count'),
        \DB::raw('SUM(number_required) as total_required')
    ])
    ->where('date', $date)
    ->where('class_status', '!=', 'cancelled') // Only count active classes in statistics
    ->groupBy('date', 'day')
    ->first();

    // Determine currently logged in supervisor id (if any)
    $currentSupervisorId = session('supervisor_id');
    if (!$currentSupervisorId && auth('supervisor')->check()) {
        $currentSupervisorId = auth('supervisor')->user()->supID;
    }
    
    
    // Check if the current supervisor owns this schedule
    $scheduleOwnedByCurrentSupervisor = true;
    if ($currentSupervisorId) {
        $existingOwner = \App\Models\DailyData::where('date', $date)
            ->whereNotNull('assigned_supervisor')
            ->where('assigned_supervisor', '!=', $currentSupervisorId)
            ->first();
        $scheduleOwnedByCurrentSupervisor = !$existingOwner;
    }
@endphp

<!-- Main Content -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <!-- Finalized Schedule Banner -->
    @if($isFinalized)
    <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-lock text-blue-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-blue-800">Schedule Finalized</h3>
                    <p class="text-sm text-blue-600">
                        This schedule was finalized on {{ \Carbon\Carbon::parse($finalizedAt)->format('F j, Y \a\t g:i A') }} and is now locked.
                    </p>
                    <p class="text-xs text-blue-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        All editing functions are disabled. View-only mode is active.
                    </p>
                </div>
            </div>
            <div class="flex-shrink-0">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Read Only
                </span>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <!-- Left: Title -->
        <h2 class="text-xl font-semibold text-gray-800">
            Class Scheduling
            @if($isFinalized)
                <i class="fas fa-lock text-blue-500 ml-2" title="Schedule is finalized and locked"></i>
            @endif
        </h2>
        <!-- Right: Buttons -->
        <div class="flex items-center space-x-2">
            <a href="{{ route('schedules.index', ['tab' => 'class', 'page' => $page ?? 1]) }}"
                class="flex items-center space-x-2 px-4 py-2 bg-[#606979] text-white rounded-full text-sm font-medium 
                        hover:bg-[#4f5a66] transform transition duration-200 hover:scale-105">
                <i class="fas fa-arrow-left"></i>
                <span>Back</span>
            </a>

            <!-- Auto-Assign Button for this specific date -->
            @if($isFinalized)
                <button class="flex items-center space-x-2 bg-gray-400 text-white px-4 py-2 rounded-full text-sm font-medium cursor-not-allowed opacity-60" disabled>
                    <i class="fas fa-lock"></i>
                    <span>Schedule Locked</span>
                </button>
            @elseif(!$scheduleOwnedByCurrentSupervisor)
                <button class="flex items-center space-x-2 bg-gray-400 text-white px-4 py-2 rounded-full text-sm font-medium cursor-not-allowed opacity-60" disabled>
                    <i class="fas fa-user-lock"></i>
                    <span>Owned by Another Supervisor</span>
                </button>
            @else
                <button onclick="autoAssignForThisDay('{{ $date }}')"
                        class="flex items-center space-x-2 bg-green-600 text-white px-4 py-2 rounded-full text-sm font-medium 
                                hover:bg-green-700 transform transition duration-200 hover:scale-105">
                    <i class="fas fa-magic"></i>
                    <span>Auto Assign All</span>
                </button>
            @endif

            <!-- Tentative Excel Export Button -->
            <button
                onclick="exportSchedule('tentative', '{{ $date }}')"
                class="flex items-center space-x-2 bg-[#0E335D] text-white px-4 py-2 rounded-full text-sm font-medium 
                        hover:bg-[#184679] transform transition duration-200 hover:scale-105">
                <i class="fas fa-file-excel"></i>
                <span>Tentative Excel</span>
            </button>

            <!-- Final Excel Export Button -->
            <button
                onclick="exportSchedule('final', '{{ $date }}')"
                class="flex items-center space-x-2 bg-[#0E335D] text-white px-4 py-2 rounded-full text-sm font-medium 
                        hover:bg-[#184679] transform transition duration-200 hover:scale-105">
                <i class="fas fa-file-excel"></i>
                <span>Final Excel</span>
            </button>
        </div>
    </div>

    <hr class="my-6">

    <!-- Status and Info -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Status:</span>
            @php
                // Only calculate status for active (non-cancelled) classes
                $activeClasses = $dayClasses->filter(function($class) {
                    return $class->class_status !== 'cancelled';
                });
                
                // Debug: Show counts
                $totalClasses = $dayClasses->count();
                $activeClassCount = $activeClasses->count();
                $cancelledClassCount = $dayClasses->where('class_status', 'cancelled')->count();
                
                $totalRequired = $activeClasses->sum('number_required');
                
                // Fix: Only count assignments from active (non-cancelled) classes
                $totalAssigned = 0;
                foreach ($activeClasses as $class) {
                    if ($class->class_status !== 'cancelled') { // Double-check to be absolutely sure
                        $totalAssigned += $class->tutorAssignments->filter(function($assignment) {
                            return !$assignment->is_backup;
                        })->count();
                    }
                }
                
                if ($totalRequired == 0) {
                    $statusText = 'No Active Classes';
                    $statusColor = 'text-gray-600';
                } elseif ($totalAssigned == 0) {
                    $statusText = 'Not Assigned';
                    $statusColor = 'text-red-600';
                } elseif ($totalAssigned >= $totalRequired) {
                    $statusText = 'Fully Assigned';
                    $statusColor = 'text-green-600';
                } else {
                    $statusText = 'Partially Assigned';
                    $statusColor = 'text-yellow-600';
                }
            @endphp
            <p class="{{ $statusColor }} font-semibold">{{ $statusText }} ({{ $totalAssigned }}/{{ $totalRequired }})</p>
            <p class="text-xs text-gray-500 mt-1">
                Total: {{ $totalClasses }} | Active: {{ $activeClassCount }} | Cancelled: {{ $cancelledClassCount }}
            </p>
            
            @php
                $cancelledCount = $dayClasses->where('class_status', 'cancelled')->count();
            @endphp
            @if($cancelledCount > 0)
                <p class="text-xs text-red-600 mt-1">{{ $cancelledCount }} class(es) cancelled</p>
            @endif
        </div>
        <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Schools:</span>
            <p class="text-gray-800 font-semibold">{{ $dayInfo->schools ?? 'N/A' }}</p>
        </div>
        <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Date:</span>
            <p class="text-gray-800 font-semibold">{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</p>
        </div>
        <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Day:</span>
            <p class="text-gray-800 font-semibold">{{ $dayInfo->day ?? 'N/A' }}</p>
        </div>
    </div>

    <hr class="my-6">

    <!-- Schedule Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
        @forelse($dayClasses as $class)
        <!-- Time Slot Card -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden flex flex-col 
                    {{ $class->class_status === 'cancelled' ? 'opacity-75 bg-red-50' : '' }}">
            <div class="bg-[#0E335D] text-white px-4 py-3 {{ $class->class_status === 'cancelled' ? 'bg-red-500' : '' }}">
                <h3 class="font-semibold text-center">
                    {{ $class->class ?? 'N/A' }} | 
                    @if($class->time_jst)
                        @php
                            // Convert JST to PHT (JST is UTC+9, PHT is UTC+8, so PHT = JST - 1 hour)
                            $jstTime = \Carbon\Carbon::parse($class->time_jst);
                            $phtTime = $jstTime->subHour();
                        @endphp
                        {{ $phtTime->format('g:i A') }}
                    @else
                        N/A
                    @endif
                </h3>
                <p class="text-xs text-center text-blue-200 mt-1">{{ $class->school }}</p>
                @php
                    $ownerName = null;
                    if (!empty($class->finalized_by)) {
                        $owner = \App\Models\Supervisor::where('supID', $class->finalized_by)->first();
                        $ownerName = $owner ? $owner->full_name : null;
                    }
                @endphp
                @if($ownerName && $currentSupervisorId && $class->finalized_by !== $currentSupervisorId)
                    <div class="mt-2 flex justify-center">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xxs font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200">
                            <i class="fas fa-user-shield mr-1 text-xs"></i>
                            Supervised by {{ $ownerName }}
                        </span>
                    </div>
                @endif
                @if($class->class_status === 'cancelled')
                    <div class="text-center mt-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-times-circle mr-1"></i>
                            Cancelled
                        </span>
                    </div>
                @endif
            </div>
            
            <!-- Card Body - Flexible height -->
            <div class="p-4 flex-grow flex flex-col">
                <div class="text-center text-sm text-gray-600 font-medium mb-3">TUTORS</div>
                
                <!-- Fixed height tutor slots container -->
                <div class="flex-grow min-h-[200px] mb-4">
                    @php
                        $requiredTutors = $class->number_required ?? 0;
                    @endphp
                    
                    @php
                        // Get assigned tutors for this class separated by type
                        $mainTutors = $class->tutorAssignments->filter(function($assignment) {
                            return !$assignment->is_backup; // Use ! instead of where()
                        })->map(function($assignment) {
                            return $assignment->tutor->full_name;
                        })->values()->toArray();
                        
                        $backupTutors = $class->tutorAssignments->filter(function($assignment) {
                            return $assignment->is_backup; // Use filter instead of where()
                        })->map(function($assignment) {
                            return $assignment->tutor->full_name;
                        })->values()->toArray();
                        
                        $mainTutorCount = count($mainTutors);
                        
                        // Create tutor slots with main tutors only (backup shown separately)
                        $tutorSlots = [];
                        for ($i = 0; $i < $requiredTutors; $i++) {
                            $tutorSlots[] = $mainTutors[$i] ?? null;
                        }
                    @endphp
                    
                    @if($class->class_status === 'cancelled')
                        <!-- Cancelled Class - Still show tutors but grayed out -->
                        <div class="grid grid-cols-2 gap-2 h-full opacity-60">
                            <!-- Tutor Slots -->
                            @foreach($tutorSlots as $index => $tutor)
                                @if($tutor)
                                    <div class="py-2 px-3 bg-gray-100 border border-gray-300 rounded text-gray-500 text-center font-medium text-sm">
                                        {{ $tutor }} <span class="text-xs">(Cancelled)</span>
                                    </div>
                                @else
                                    <div class="py-2 px-3 bg-gray-50 rounded text-gray-400 text-center text-sm">
                                        Not Assigned
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <!-- Normal Tutor Assignment Display -->
                        <div class="grid grid-cols-2 gap-2 h-full">
                            <!-- Tutor Slots -->
                            @foreach($tutorSlots as $index => $tutor)
                                @if($tutor)
                                    <div class="py-2 px-3 bg-green-50 border border-green-200 rounded text-green-700 text-center font-medium text-sm">
                                        {{ $tutor }}
                                    </div>
                                @else
                                    <div class="py-2 px-3 bg-gray-50 rounded text-gray-400 text-center text-sm">
                                        Not Assigned
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Backup Tutor Section -->
                @if(count($backupTutors) > 0)
                <div class="mb-4 {{ $class->class_status === 'cancelled' ? 'opacity-60' : '' }}">
                    <div class="text-center text-sm text-gray-700 font-medium mb-3">BACKUP TUTOR</div>
                    <div class="space-y-2">
                        @foreach($backupTutors as $backupTutor)
                            <div class="py-3 px-4 {{ $class->class_status === 'cancelled' ? 'bg-gray-100 border-gray-300' : 'bg-green-50 border-green-200' }} border rounded-lg">
                                <div class="flex items-center justify-center space-x-2">
                                    <div class="flex items-center bg-white px-3 py-2 rounded-full border {{ $class->class_status === 'cancelled' ? 'border-gray-300' : 'border-green-300' }} shadow-sm">
                                        <i class="fas fa-user-graduate {{ $class->class_status === 'cancelled' ? 'text-gray-500' : 'text-green-600' }} text-sm mr-2"></i>
                                        <span class="{{ $class->class_status === 'cancelled' ? 'text-gray-600' : 'text-gray-800' }} font-medium text-base">{{ $backupTutor }}</span>
                                        <span class="ml-2 text-xs {{ $class->class_status === 'cancelled' ? 'bg-gray-100 text-gray-600' : 'bg-green-100 text-green-700' }} px-2 py-1 rounded-full font-medium">
                                            {{ $class->class_status === 'cancelled' ? 'Cancelled' : 'Ready' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-center text-xs text-gray-500 mt-2">
                                    {{ $class->class_status === 'cancelled' ? 'Was on standby (cancelled)' : 'Available if needed' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Bottom section - Always at bottom -->
                <div class="flex items-center justify-between border-t pt-3 mt-auto">
                    @if($class->class_status === 'cancelled')
                        <span class="text-sm text-red-600 font-medium">
                            <i class="fas fa-times-circle mr-1"></i>
                            Cancelled - Tutors: {{ count($mainTutors) }}/{{ $class->number_required ?? 0 }}
                            @if(count($backupTutors) > 0)
                                <span class="inline-flex items-center ml-3 px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700 border border-red-300">
                                    <i class="fas fa-user-clock mr-1 text-xs"></i>
                                    +{{ count($backupTutors) }} was on standby
                                </span>
                            @endif
                        </span>
                    @else
                        <span class="text-sm text-gray-700 font-medium">
                            Tutors: {{ count($mainTutors) }}/{{ $class->number_required ?? 0 }}
                            @if(count($backupTutors) > 0)
                                <span class="inline-flex items-center ml-3 px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-300">
                                    <i class="fas fa-user-clock mr-1 text-xs"></i>
                                    +{{ count($backupTutors) }} on standby
                                </span>
                            @endif
                        </span>
                    @endif
                    <div class="flex items-center space-x-2">
                        @if($class->class_status === 'cancelled')
                            <!-- No actions available for cancelled classes -->
                            <span class="text-xs text-gray-400 italic">No actions available</span>
                        @elseif($isFinalized)
                            <!-- No actions available for finalized schedules -->
                            <div class="flex items-center space-x-1">
                                <i class="fas fa-lock text-blue-400 text-xs"></i>
                                <span class="text-xs text-blue-600 italic">Schedule Locked</span>
                            </div>
                        @elseif(!$scheduleOwnedByCurrentSupervisor)
                            <!-- No actions available - owned by another supervisor -->
                            <div class="flex items-center space-x-1">
                                <i class="fas fa-user-lock text-gray-400 text-xs"></i>
                                <span class="text-xs text-gray-600 italic">Owned by Another Supervisor</span>
                            </div>
                        @else
                            <!-- Edit Button -->
                            <button
                                class="editBtn text-[#F6B40E] hover:text-[#C88F00] transform transition duration-200 hover:scale-110"
                                data-class="{{ $class->class }}" 
                                data-time="{{ $class->time_jst ? \Carbon\Carbon::parse($class->time_jst)->subHour()->format('g:i A') : 'N/A' }}" 
                                data-date="{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}"
                                data-school="{{ $class->school }}"
                                data-required="{{ $class->number_required }}"
                                data-class-id="{{ $class->id }}"
                                data-assigned-tutors="{{ implode(',', $mainTutors) }}"
                                data-backup-tutor="{{ count($backupTutors) > 0 ? $backupTutors[0] : '' }}"
                                title="Edit Schedule">
                                <i class="fas fa-edit"></i>
                            </button>
                            <!-- Cancel Class Button -->
                            <button
                                onclick="cancelClass({{ $class->id }}, '{{ $class->class }}', '{{ $class->school }}')"
                                class="text-red-600 hover:text-red-800 transform transition duration-200 hover:scale-110"
                                title="Cancel Class">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <!-- No Classes Message -->
        <div class="col-span-full text-center py-8">
            <i class="fas fa-calendar-times text-4xl mb-4 opacity-50 text-gray-400"></i>
            <p class="text-lg font-medium text-gray-500">No classes found for this date</p>
            <p class="text-sm text-gray-400">Please check if data has been uploaded for this date</p>
        </div>
        @endforelse
    </div>

    <!-- Action Buttons -->
    @if($dayClasses->count() > 0)
    <div class="flex items-center justify-center space-x-4">
        @if($isFinalized)
            <div class="bg-gray-100 border-2 border-gray-300 rounded-full px-6 py-2 flex items-center space-x-2">
                <i class="fas fa-check-circle text-green-500"></i>
                <span class="text-gray-700 font-medium">Schedule Already Finalized</span>
            </div>
        @else
            <button onclick="saveScheduleAs('final', '{{ $date }}')"
                class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-full font-medium 
                        transform transition duration-200 hover:scale-105">
                Save as Final
            </button>
        @endif
    </div>
    @endif
</div>

<!-- Edit Schedule Modal -->
<div id="editScheduleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg">
        <!-- Header -->
        <div class="flex justify-between items-center bg-yellow-400 text-black px-4 py-3 rounded-t-lg">
            <h2 class="text-lg font-bold">Edit Schedule</h2>
            <button id="closeModal" class="text-black font-bold text-xl">&times;</button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4 text-sm">
            <!-- Class / Time / Date / School -->
            <div>
                <p><span class="font-semibold">Class:</span> <span id="modalClass">N/A</span></p>
                <p><span class="font-semibold">School:</span> <span id="modalSchool">N/A</span></p>
                <div class="flex justify-between">
                    <p><span class="font-semibold">Time:</span> <span id="modalTime">N/A</span></p>
                    <p><span class="font-semibold">Date:</span> <span id="modalDate">N/A</span></p>
                </div>
                <p><span class="font-semibold">Required Tutors:</span> <span id="modalRequired">0</span></p>
            </div>
            <hr class="my-3">

            <!-- Assigned Tutors -->
            <div class="flex justify-between items-center">
                <span class="font-semibold">Assigned Tutors:</span>
                <div class="relative w-48">
                    <!-- Custom searchable dropdown for main tutors -->
                    <div class="searchable-select" id="addTutorContainer">
                        <input type="text" id="addTutorSearch" placeholder="Add tutor" 
                               class="border border-gray-300 rounded px-2 py-1 text-sm w-full bg-white cursor-pointer" readonly>
                        <div class="dropdown-arrow absolute right-2 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <div id="addTutorDropdown" class="absolute z-50 w-full bg-white border border-gray-300 rounded-b shadow-lg max-h-48 overflow-y-auto hidden">
                            <!-- Options will be populated here -->
                        </div>
                    </div>
                    <select id="addTutorSelect" class="hidden">
                        <option value="">Add tutor</option>
                    </select>
                </div>
            </div>

            <hr class="my-3">

            <!-- Tutor Grid -->
            <div id="tutorGrid" class="grid grid-cols-2 gap-4">
                <!-- Tutors will be inserted here -->
            </div>

            <hr class="my-3">

            <!-- Backup Tutor -->
            <div>
                <span class="font-semibold">Backup Tutor:</span>
                <div class="relative mt-1">
                    <!-- Custom searchable dropdown for backup tutors -->
                    <div class="searchable-select" id="backupTutorContainer">
                        <input type="text" id="backupTutorSearch" placeholder="Select backup tutor" 
                               class="border border-gray-300 rounded px-2 py-2 w-full text-sm bg-white cursor-pointer" readonly>
                        <div class="dropdown-arrow absolute right-2 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <div id="backupTutorDropdown" class="absolute z-50 w-full bg-white border border-gray-300 rounded-b shadow-lg max-h-48 overflow-y-auto hidden">
                            <!-- Options will be populated here -->
                        </div>
                    </div>
                    <select id="backupTutorSelect" class="hidden">
                        <option value="">Select backup tutor</option>
                    </select>
                </div>
            </div>

            <hr class="my-3">
        </div>

        <!-- Footer -->
        <div class="flex justify-end items-center space-x-3 px-6 py-4 border-t">
            <button id="cancelModal"
                class="px-4 py-2 rounded-full border border-gray-300 text-gray-600 
                   hover:bg-gray-200 transform transition duration-200 hover:scale-105">
                Cancel
            </button>

            <button id="saveChanges"
                class="px-4 py-2 rounded-full bg-green-500 text-white 
                   hover:bg-green-600 transform transition duration-200 hover:scale-105">
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Modal Script -->
<script src="{{ asset('js/per-day-schedule-modal.js') }}"></script>

<!-- Include the class scheduling JavaScript for auto-assign functionality -->
<script src="{{ asset('js/class-scheduling.js') }}"></script>

<!-- Include class cancellation functionality -->
<script src="{{ asset('js/class-cancellation.js') }}"></script>

<script src="{{ asset('js/save-schedule.js') }}"></script>
