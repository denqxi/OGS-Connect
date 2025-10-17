@php
    // Data is now passed from the controller - no need to query here

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
    
    <!-- Back Button - Separated and positioned better -->
    <div class="mb-4">
        <a href="{{ route('schedules.index', ['tab' => 'class', 'page' => $page ?? 1]) }}"
            class="inline-flex items-center space-x-2 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium 
                    hover:bg-gray-200 hover:text-gray-900 transform transition duration-200 hover:scale-105 border border-gray-300">
            <i class="fas fa-arrow-left text-xs"></i>
            <span>Back</span>
        </a>
    </div>

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

            <!-- Auto-Assign Button for this specific date -->
            @if($isFinalized)
                <button class="inline-flex items-center space-x-2 px-3 py-2 bg-gray-400 text-white rounded-lg text-sm font-medium cursor-not-allowed border border-gray-300 opacity-60" disabled>
                    <i class="fas fa-lock text-xs"></i>
                    <span>Schedule Locked</span>
                </button>
            @elseif(!$scheduleOwnedByCurrentSupervisor)
                <button class="inline-flex items-center space-x-2 px-3 py-2 bg-gray-400 text-white rounded-lg text-sm font-medium cursor-not-allowed border border-gray-300 opacity-60" disabled>
                    <i class="fas fa-user-lock text-xs"></i>
                    <span>Owned by Another Supervisor</span>
                </button>
            @else
                <button onclick="autoAssignForThisDay('{{ $date }}')"
                        class="inline-flex items-center space-x-2 px-3 py-2 bg-green-600 text-white rounded-lg text-sm font-medium 
                                hover:bg-green-700 transform transition duration-200 hover:scale-105 border border-green-300">
                    <i class="fas fa-magic text-xs"></i>
                    <span>Auto Assign All</span>
                </button>
            @endif

            <!-- Tentative Excel Export Button -->
            <button
                onclick="exportSchedule('tentative', '{{ $date }}')"
                class="inline-flex items-center space-x-2 px-3 py-2 bg-[#0E335D] text-white rounded-lg text-sm font-medium 
                        hover:bg-[#184679] transform transition duration-200 hover:scale-105 border border-[#0E335D]">
                <i class="fas fa-file-excel text-xs"></i>
                <span>Tentative Excel</span>
            </button>

            <!-- Final Excel Export Button -->
            <button
                onclick="exportSchedule('final', '{{ $date }}')"
                class="inline-flex items-center space-x-2 px-3 py-2 bg-[#0E335D] text-white rounded-lg text-sm font-medium 
                        hover:bg-[#184679] transform transition duration-200 hover:scale-105 border border-[#0E335D]">
                <i class="fas fa-file-excel text-xs"></i>
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
                        @if($class->cancellation_reason)
                            <div class="mt-2 text-xs text-red-600 bg-red-50 px-2 py-1 rounded border border-red-200">
                                <strong>Reason:</strong> {{ $class->cancellation_reason }}
                            </div>
                        @endif
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
                                class="editBtn bg-blue-100 text-blue-600 hover:bg-blue-200 hover:text-blue-700 p-2 rounded-lg transform transition duration-200 hover:scale-105 shadow-sm hover:shadow-md"
                                data-class="{{ $class->class }}" 
                                data-time="{{ $class->time_jst ? \Carbon\Carbon::parse($class->time_jst)->subHour()->format('g:i A') : 'N/A' }}" 
                                data-date="{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}"
                                data-raw-date="{{ $date }}"
                                data-day="{{ strtolower(\Carbon\Carbon::parse($date)->format('l')) }}"
                                data-time-slot="{{ $class->time_jst ? \Carbon\Carbon::parse($class->time_jst)->subHour()->format('H:i') . ' - ' . \Carbon\Carbon::parse($class->time_jst)->format('H:i') : '' }}"
                                data-school="{{ $class->school }}"
                                data-required="{{ $class->number_required }}"
                                data-class-id="{{ $class->id }}"
                                data-assigned-tutors="{{ implode(',', $mainTutors) }}"
                                data-backup-tutor="{{ count($backupTutors) > 0 ? $backupTutors[0] : '' }}"
                                title="Edit Schedule">
                                <i class="fas fa-edit text-sm"></i>
                            </button>
                            <!-- Cancel Class Button -->
                            <button
                                onclick="cancelClass({{ $class->id }}, '{{ $class->class }}', '{{ $class->school }}')"
                                class="bg-red-100 text-red-600 hover:bg-red-200 hover:text-red-700 p-2 rounded-lg transform transition duration-200 hover:scale-105 shadow-sm hover:shadow-md"
                                title="Cancel Class">
                                <i class="fas fa-times-circle text-sm"></i>
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
        @elseif(!$scheduleOwnedByCurrentSupervisor)
            <div class="bg-gray-100 border-2 border-gray-300 rounded-full px-6 py-2 flex items-center space-x-2">
                <i class="fas fa-user-lock text-gray-500"></i>
                <span class="text-gray-700 font-medium">Owned by Another Supervisor</span>
            </div>
        @else
            <button onclick="saveScheduleAs('final', '{{ $date }}')"
                class="inline-flex items-center space-x-2 px-3 py-2 bg-green-500 text-white rounded-lg text-sm font-medium 
                        hover:bg-green-600 transform transition duration-200 hover:scale-105 border border-green-300"
                title="Finalize this schedule - cannot be modified after saving">
                <i class="fas fa-save text-xs"></i>
                <span>Save as Final</span>
            </button>
        @endif
    </div>
    @endif
</div>

<!-- Save as Final Confirmation Modal -->
<div id="saveFinalConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[100] hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
        <!-- Header -->
        <div class="flex justify-between items-center bg-red-500 text-white px-4 py-3 rounded-t-lg">
            <h2 class="text-lg font-bold">Finalize Schedule</h2>
            <button id="closeSaveFinalModal" class="text-white font-bold text-xl">&times;</button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">Finalize Schedule?</h3>
                    <p class="text-sm text-gray-500 mt-1" id="saveFinalMessage">Are you sure you want to finalize this schedule?</p>
                </div>
            </div>
            
            <div class="bg-red-50 rounded-lg p-3 mb-4">
                <p class="text-sm text-red-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Warning:</strong> Once saved as FINAL, these schedules will be locked and can only be cancelled or rescheduled!
                </p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-sm text-gray-600">
                    <span class="font-semibold">Date:</span> <span id="saveFinalDate">N/A</span><br>
                    <span class="font-semibold">Status:</span> <span class="text-red-600 font-semibold">FINAL</span>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end items-center space-x-3 px-6 py-4 border-t">
            <button id="cancelSaveFinal"
                class="px-4 py-2 rounded-full border border-gray-300 text-gray-600 
                   hover:bg-gray-200 transform transition duration-200 hover:scale-105">
                Cancel
            </button>

            <button id="confirmSaveFinal"
                class="px-4 py-2 rounded-full bg-red-500 text-white 
                   hover:bg-red-600 transform transition duration-200 hover:scale-105">
                <i class="fas fa-lock mr-1"></i>Finalize Schedule
            </button>
        </div>
    </div>
</div>

<!-- Auto-Assign Confirmation Modal -->
<div id="autoAssignConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[100] hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
        <!-- Header -->
        <div class="flex justify-between items-center bg-green-500 text-white px-4 py-3 rounded-t-lg">
            <h2 class="text-lg font-bold">Auto-Assign Tutors</h2>
            <button id="closeAutoAssignModal" class="text-white font-bold text-xl">&times;</button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">Confirm Auto-Assignment</h3>
                    <p class="text-sm text-gray-500 mt-1" id="autoAssignMessage">Are you sure you want to auto-assign tutors?</p>
                </div>
            </div>
            
            <div class="bg-blue-50 rounded-lg p-3 mb-4">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    This will automatically assign available tutors to unassigned classes based on their availability and preferences.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end items-center space-x-3 px-6 py-4 border-t">
            <button id="cancelAutoAssign"
                class="px-4 py-2 rounded-full border border-gray-300 text-gray-600 
                   hover:bg-gray-200 transform transition duration-200 hover:scale-105">
                Cancel
            </button>

            <button id="confirmAutoAssign"
                class="px-4 py-2 rounded-full bg-green-500 text-white 
                   hover:bg-green-600 transform transition duration-200 hover:scale-105">
                <i class="fas fa-magic mr-1"></i>Auto Assign
            </button>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[100] hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
        <!-- Header -->
        <div class="flex justify-between items-center bg-blue-500 text-white px-4 py-3 rounded-t-lg">
            <h2 class="text-lg font-bold">Confirm Changes</h2>
            <button id="closeConfirmationModal" class="text-white font-bold text-xl">&times;</button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">Save Changes?</h3>
                    <p class="text-sm text-gray-500 mt-1">Are you sure you want to save the tutor assignments for this class?</p>
                </div>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                <p class="text-sm text-gray-600">
                    <span class="font-semibold">Class:</span> <span id="confirmClass">N/A</span><br>
                    <span class="font-semibold">Date:</span> <span id="confirmDate">N/A</span><br>
                    <span class="font-semibold">Time:</span> <span id="confirmTime">N/A</span>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end items-center space-x-3 px-6 py-4 border-t">
            <button id="cancelConfirmation"
                class="px-4 py-2 rounded-full border border-gray-300 text-gray-600 bg-white
                   hover:bg-gray-50 hover:border-gray-400 transform transition duration-200 hover:scale-105 shadow-sm">
                Cancel
            </button>

            <button id="confirmSave"
                class="px-4 py-2 rounded-full bg-green-500 text-white 
                   hover:bg-green-600 transform transition duration-200 hover:scale-105">
                <i class="fas fa-save mr-1"></i>Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Tutor Cancellation Reason Modal -->
<div id="tutorCancellationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[100] hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <!-- Header -->
        <div class="flex justify-between items-center bg-red-500 text-white px-4 py-3 rounded-t-lg">
            <h2 class="text-lg font-bold">Remove Tutor Assignment</h2>
            <button id="closeTutorCancellationModal" class="text-white font-bold text-xl">&times;</button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">Confirm Tutor Removal</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        You are about to remove <strong id="tutorNameToRemove">Tutor Name</strong> from <strong id="classNameToRemove">Class Name</strong>.
                    </p>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="tutorCancellationReason" class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for removal <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="tutorCancellationReason" 
                    rows="4" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none"
                    placeholder="Please provide a reason for removing this tutor from the assignment..."
                    required></textarea>
                <div id="tutorCancellationReasonError" class="text-red-500 text-sm mt-1 hidden">
                    Please provide a reason for the removal.
                </div>
            </div>

            <div class="bg-yellow-50 rounded-lg p-3 mb-4">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    This action will permanently remove the tutor from this class assignment. This cannot be undone.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end items-center space-x-3 px-6 py-4 border-t">
            <button id="cancelTutorCancellation"
                class="px-4 py-2 rounded-md border border-gray-300 text-gray-600 
                   hover:bg-gray-200 transform transition duration-200 hover:scale-105">
                Cancel
            </button>

            <button id="confirmTutorCancellation"
                class="px-4 py-2 rounded-md bg-red-500 text-white 
                   hover:bg-red-600 transform transition duration-200 hover:scale-105">
                <i class="fas fa-trash mr-2"></i>
                Remove Tutor
            </button>
        </div>
    </div>
</div>

<!-- Cancellation Reason View Modal -->
<div id="cancellationReasonModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[100] hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
        <!-- Header -->
        <div class="flex justify-between items-center bg-blue-500 text-white px-4 py-3 rounded-t-lg">
            <h2 class="text-lg font-bold">Cancellation Details</h2>
            <button id="closeCancellationReasonModal" class="text-white font-bold text-xl">&times;</button>
        </div>

        <!-- Body -->
        <div class="p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tutor:</label>
                    <p id="cancelledTutorName" class="text-lg font-semibold text-gray-900">-</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role:</label>
                    <p id="cancelledTutorRole" class="text-sm text-gray-600">-</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cancelled At:</label>
                    <p id="cancelledAt" class="text-sm text-gray-600">-</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cancelled By:</label>
                    <p id="cancelledBy" class="text-sm text-gray-600">-</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Cancellation:</label>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                        <p id="cancellationReasonText" class="text-sm text-gray-800 whitespace-pre-wrap">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end items-center space-x-3 px-6 py-4 border-t">
            <button id="closeCancellationReasonModalBtn"
                class="px-4 py-2 rounded-md bg-blue-500 text-white 
                   hover:bg-blue-600 transform transition duration-200 hover:scale-105">
                Close
            </button>
        </div>
    </div>
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
            <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-2">
                <div id="tutorGrid" class="grid grid-cols-2 gap-4">
                    <!-- Tutors will be inserted here -->
                </div>
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

            <!-- Cancelled Tutors Section -->
            <div id="cancelledTutorsSection" class="hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-semibold text-red-600">Cancelled Tutors:</span>
                    <span class="text-xs text-gray-500">Click to see cancellation reason</span>
                </div>
                <div id="cancelledTutorsGrid" class="space-y-2 max-h-32 overflow-y-auto">
                    <!-- Cancelled tutors will be inserted here -->
                </div>
                <hr class="my-3">
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end items-center space-x-3 px-6 py-4 border-t">
            <button id="cancelModal"
                class="px-4 py-2 rounded-full border border-gray-300 text-gray-600 bg-white
                   hover:bg-gray-50 hover:border-gray-400 transform transition duration-200 hover:scale-105 shadow-sm">
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

<!-- Pagination -->
@if($dayClasses->hasPages())
<div class="mt-8 flex justify-center">
    {{ $dayClasses->appends(request()->query())->links() }}
</div>
@endif

<!-- Modal Script -->
<script src="{{ asset('js/per-day-schedule-modal.js') }}"></script>

<!-- Include the class scheduling JavaScript for auto-assign functionality -->
<script src="{{ asset('js/class-scheduling.js') }}"></script>

<!-- Include class cancellation functionality -->
<script src="{{ asset('js/class-cancellation.js') }}"></script>

<script src="{{ asset('js/save-schedule.js') }}"></script>
