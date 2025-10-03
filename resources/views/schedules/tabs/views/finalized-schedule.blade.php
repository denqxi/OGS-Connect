@php
    // Get all classes for the selected date with tutor assignments (including cancelled for visibility)
    $dayClasses = \App\Models\DailyData::where('date', $date)
                                       ->where('schedule_status', 'finalized')
                                       ->with(['tutorAssignments.tutor'])
                                       ->orderBy('school')
                                       ->orderBy('time_pht')
                                       ->get();
    
    // Get supervisor information for the schedule
    $supervisorInfo = null;
    if ($dayClasses->count() > 0) {
        $firstClass = $dayClasses->first();
        
        // Try to get supervisor from schedule history (prioritize 'finalized' action)
        $finalizedHistory = \App\Models\ScheduleHistory::where('class_date', $date)
            ->where('action', 'finalized')
            ->first();
            
        if ($finalizedHistory && $finalizedHistory->performed_by) {
            $supervisor = \App\Models\Supervisor::where('supID', $finalizedHistory->performed_by)->first();
            if ($supervisor) {
                $supervisorInfo = [
                    'id' => $supervisor->supID,
                    'name' => $supervisor->sfname . ' ' . $supervisor->slname,
                    'action' => 'Finalized'
                ];
            }
        }
        
        // If no finalized history, try to get from assigned supervisor
        if (!$supervisorInfo && $firstClass->assigned_supervisor) {
            $supervisor = \App\Models\Supervisor::where('supID', $firstClass->assigned_supervisor)->first();
            if ($supervisor) {
                $supervisorInfo = [
                    'id' => $supervisor->supID,
                    'name' => $supervisor->sfname . ' ' . $supervisor->slname,
                    'action' => 'Assigned'
                ];
            }
        }
    }
    
    // Get grouped information for the header (excluding cancelled classes for statistics)
    $dayInfo = \App\Models\DailyData::select([
        'date',
        \DB::raw('DAYNAME(date) as day'),
        \DB::raw('GROUP_CONCAT(DISTINCT school ORDER BY school ASC SEPARATOR ", ") as schools'),
        \DB::raw('COUNT(*) as class_count'),
        \DB::raw('SUM(number_required) as total_required')
    ])
    ->where('date', $date)
    ->where('schedule_status', 'finalized')
    ->where('class_status', '!=', 'cancelled') // Only count active classes in statistics
    ->groupBy('date')
    ->first();
@endphp

<!-- Main Content -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <!-- Left: Title -->
        <h2 class="text-xl font-semibold text-gray-800">Finalized Schedule</h2>
        <!-- Right: Buttons -->
        <div class="flex items-center space-x-2">
            <a href="{{ route('schedules.index', array_merge(request()->except(['view_date', 'search', 'school_filter']), ['tab' => 'history'])) }}"
                class="flex items-center space-x-2 px-4 py-2 bg-[#606979] text-white rounded-full text-sm font-medium 
                        hover:bg-[#4f5a66] transform transition duration-200 hover:scale-105">
                <i class="fas fa-arrow-left"></i>
                <span>Back to History</span>
            </a>

            <button onclick="exportSingleSchedule('{{ $date }}')" 
                    class="flex items-center space-x-2 bg-[#0E335D] text-white px-4 py-2 rounded-full text-sm font-medium 
                           hover:bg-[#184679] transform transition duration-200 hover:scale-105">
                <i class="fas fa-file-excel"></i>
                <span>Export Excel</span>
            </button>
        </div>
    </div>

    <hr class="my-6">

    <!-- Status and Info -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Status:</span>
            @php
                // Only calculate status for active (non-cancelled) classes
                $activeClasses = $dayClasses->filter(function($class) {
                    return $class->class_status !== 'cancelled';
                });
                
                $totalRequired = $activeClasses->sum('number_required');
                $totalAssigned = $activeClasses->sum(function($class) { 
                    return $class->tutorAssignments->filter(function($assignment) {
                        return !$assignment->is_backup;
                    })->count();
                });
                
                $statusText = 'Finalized';
                $statusColor = 'text-green-600';
            @endphp
            <p class="{{ $statusColor }} font-semibold">{{ $statusText }} ({{ $totalAssigned }}/{{ $totalRequired }})</p>
            
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
        <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Supervisor:</span>
            @if($supervisorInfo)
                <div class="flex items-center space-x-2 mt-1">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                        @if($supervisorInfo['action'] === 'Finalized') bg-green-100 text-green-800 @else bg-blue-100 text-blue-800 @endif">
                        <i class="fas fa-user-tie mr-1"></i>
                        {{ $supervisorInfo['name'] }}
                    </span>
                    <span class="text-xs text-gray-500">{{ $supervisorInfo['action'] }}</span>
                </div>
            @else
                <p class="text-gray-500 text-sm">Not assigned</p>
            @endif
        </div>
    </div>

    <hr class="my-6">

    <!-- Schedule Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        @forelse($dayClasses as $class)
        <!-- Time Slot Card -->
        <div class="border border-gray-200 rounded-lg shadow-sm overflow-hidden flex flex-col
            @if($class->class_status === 'cancelled') opacity-80 bg-gray-100 @else bg-white @endif">
            <div class="px-4 py-3
                @if($class->class_status === 'cancelled') bg-gray-400 text-gray-100 @else bg-green-600 text-white @endif">
                <h3 class="font-semibold text-center">
                    {{ $class->class ?? 'N/A' }} |
                    {{ \Carbon\Carbon::parse($class->time_pht)->format('g:i A') }}
                </h3>
                <p class="text-xs text-center @if($class->class_status === 'cancelled') text-gray-200 @else text-green-200 @endif mt-1">{{ $class->school }}</p>
                <div class="text-center mt-2">
                    @if($class->class_status === 'cancelled')
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-200 text-gray-700 border border-gray-400">
                            <i class="fas fa-times-circle mr-1"></i>
                            Cancelled
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>
                            Finalized
                        </span>
                    @endif
                </div>
            </div>

            <!-- Card Body - Flexible height -->
            <div class="p-4 flex-grow flex flex-col">
                @php
                    $requiredTutors = $class->number_required ?? 0;
                    $leftColumnCount = ceil($requiredTutors / 2);
                    $rightColumnCount = $requiredTutors - $leftColumnCount;
                    // Get assigned tutors for this class separated by type
                    $mainTutors = $class->tutorAssignments->filter(function($assignment) {
                        return !$assignment->is_backup;
                    })->map(function($assignment) {
                        return $assignment->tutor->full_name ?? $assignment->tutor->name;
                    })->values()->toArray();
                    $backupTutors = $class->tutorAssignments->filter(function($assignment) {
                        return $assignment->is_backup;
                    })->map(function($assignment) {
                        return $assignment->tutor->full_name ?? $assignment->tutor->name;
                    })->values()->toArray();
                    // Create tutor slots with main tutors only (backup shown separately)
                    $tutorSlots = [];
                    for ($i = 0; $i < $requiredTutors; $i++) {
                        $tutorSlots[] = $mainTutors[$i] ?? null;
                    }
                    // Split into columns
                    $leftSlots = array_slice($tutorSlots, 0, $leftColumnCount);
                    $rightSlots = array_slice($tutorSlots, $leftColumnCount);
                @endphp
                @if($class->class_status === 'cancelled')
                    <div class="text-center text-base text-gray-500 font-semibold mb-3">This class was cancelled.</div>
                    @if(!empty($class->cancel_reason))
                        <div class="text-center text-sm text-gray-400 mb-4">Reason: {{ $class->cancel_reason }}</div>
                    @endif
                @endif
                <div class="text-center text-sm text-gray-600 font-medium mb-3">TUTORS</div>
                <!-- Fixed height tutor slots container -->
                <div class="flex-grow min-h-[200px] mb-4">
                    <div class="grid grid-cols-2 gap-3 h-full">
                        <!-- Left Column -->
                        <div class="text-sm space-y-2">
                            @foreach($leftSlots as $tutor)
                                @if($tutor)
                                    <div class="py-2 px-3 bg-green-50 border border-green-200 rounded text-green-700 text-center font-medium">
                                        {{ $tutor }}
                                    </div>
                                @else
                                    <div class="py-2 px-3 bg-gray-50 rounded text-gray-400 text-center">
                                        Not Assigned
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <!-- Right Column -->
                        <div class="text-sm space-y-2">
                            @foreach($rightSlots as $tutor)
                                @if($tutor)
                                    <div class="py-2 px-3 bg-green-50 border border-green-200 rounded text-green-700 text-center font-medium">
                                        {{ $tutor }}
                                    </div>
                                @else
                                    <div class="py-2 px-3 bg-gray-50 rounded text-gray-400 text-center">
                                        Not Assigned
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                <!-- Backup Tutor Section -->
                @if(count($backupTutors) > 0)
                <div class="mb-4">
                    <div class="text-center text-sm text-gray-700 font-medium mb-3">BACKUP TUTOR</div>
                    <div class="space-y-2">
                        @foreach($backupTutors as $backupTutor)
                            <div class="py-3 px-4 bg-orange-50 border border-orange-200 rounded-lg">
                                <div class="flex items-center justify-center space-x-2">
                                    <div class="flex items-center bg-white px-3 py-2 rounded-full border border-orange-300 shadow-sm">
                                        <i class="fas fa-user-graduate text-orange-600 text-sm mr-2"></i>
                                        <span class="text-gray-800 font-medium text-base">{{ $backupTutor }}</span>
                                        <span class="ml-2 text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full font-medium">Backup</span>
                                    </div>
                                </div>
                                <div class="text-center text-xs text-gray-500 mt-2">Available if needed</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
                <!-- Bottom section - Always at bottom -->
                <div class="flex items-center justify-center border-t pt-3 mt-auto">
                    <span class="text-sm text-gray-700 font-medium">
                        Tutors: {{ count($mainTutors) }}/{{ $class->number_required ?? 0 }}
                        @if(count($backupTutors) > 0)
                            <span class="inline-flex items-center ml-3 px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800 border border-orange-300">
                                <i class="fas fa-user-clock mr-1 text-xs"></i>
                                +{{ count($backupTutors) }} on standby
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
        @empty
        <!-- No Classes Message -->
        <div class="col-span-full text-center py-8">
            <i class="fas fa-calendar-times text-4xl mb-4 opacity-50 text-gray-400"></i>
            <p class="text-lg font-medium text-gray-500">No finalized schedule found</p>
            <p class="text-sm text-gray-400">The schedule for this date may not be finalized yet</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Scripts --}}
<script>
function exportSingleSchedule(date) {
    console.log('Exporting schedule for date:', date);
    
    // Create a form to export the single schedule
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("schedules.export-selected") }}';
    form.style.display = 'none';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    // Add the selected date - use 'dates[]' to match controller expectation
    const dateInput = document.createElement('input');
    dateInput.type = 'hidden';
    dateInput.name = 'dates[]';
    dateInput.value = date;
    form.appendChild(dateInput);
    
    console.log('Form action:', form.action);
    console.log('Form data:', new FormData(form));
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>