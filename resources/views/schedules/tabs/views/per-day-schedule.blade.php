@php
    // Get all classes for the selected date with tutor assignments
    $dayClasses = \App\Models\DailyData::where('date', $date)
                                       ->with(['tutorAssignments.tutor'])
                                       ->orderBy('school')
                                       ->orderBy('time_jst')
                                       ->get();
    
    // Get grouped information for the header
    $dayInfo = \App\Models\DailyData::select([
        'date',
        'day',
        \DB::raw('GROUP_CONCAT(DISTINCT school ORDER BY school ASC SEPARATOR ", ") as schools'),
        \DB::raw('COUNT(*) as class_count'),
        \DB::raw('SUM(number_required) as total_required')
    ])
    ->where('date', $date)
    ->groupBy('date', 'day')
    ->first();
@endphp

<!-- Main Content -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <!-- Left: Title -->
        <h2 class="text-xl font-semibold text-gray-800">Class Scheduling</h2>
        <!-- Right: Buttons -->
        <div class="flex items-center space-x-2">
            <a href="{{ route('schedules.index', ['tab' => 'class']) }}"
                class="flex items-center space-x-2 px-4 py-2 bg-[#606979] text-white rounded-full text-sm font-medium 
                        hover:bg-[#4f5a66] transform transition duration-200 hover:scale-105">
                <i class="fas fa-arrow-left"></i>
                <span>Back</span>
            </a>

            <!-- Auto-Assign Button for this specific date -->
            <button onclick="autoAssignForThisDay('{{ $date }}')"
                    class="flex items-center space-x-2 bg-green-600 text-white px-4 py-2 rounded-full text-sm font-medium 
                            hover:bg-green-700 transform transition duration-200 hover:scale-105">
                <i class="fas fa-magic"></i>
                <span>Auto Assign All</span>
            </button>

            <button
                onclick="exportSchedule('tentative', '{{ $date }}')"
                class="flex items-center space-x-2 bg-[#0E335D] text-white px-4 py-2 rounded-full text-sm font-medium 
                        hover:bg-[#184679] transform transition duration-200 hover:scale-105">
                <i class="fas fa-file-excel"></i>
                <span>Export Excel</span>
            </button>
        </div>
    </div>

    <hr class="my-6">

    <!-- Status and Info -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Status:</span>
            @php
                $totalRequired = $dayClasses->sum('number_required');
                $totalAssigned = $dayClasses->sum(function($class) { 
                    return $class->tutorAssignments->filter(function($assignment) {
                        return !$assignment->is_backup;
                    })->count();
                });
                
                if ($totalAssigned == 0) {
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
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        @forelse($dayClasses as $class)
        <!-- Time Slot Card -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden flex flex-col">
            <div class="bg-[#0E335D] text-white px-4 py-3">
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
            </div>
            
            <!-- Card Body - Flexible height -->
            <div class="p-4 flex-grow flex flex-col">
                <div class="text-center text-sm text-gray-600 font-medium mb-3">TUTORS</div>
                
                <!-- Fixed height tutor slots container -->
                <div class="flex-grow min-h-[200px] mb-4">
                    @php
                        $requiredTutors = $class->number_required ?? 0;
                        $leftColumnCount = ceil($requiredTutors / 2);
                        $rightColumnCount = $requiredTutors - $leftColumnCount;
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
                        
                        // Split into columns
                        $leftSlots = array_slice($tutorSlots, 0, $leftColumnCount);
                        $rightSlots = array_slice($tutorSlots, $leftColumnCount);
                    @endphp
                    
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
                            <div class="py-3 px-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex items-center justify-center space-x-2">
                                    <div class="flex items-center bg-white px-3 py-2 rounded-full border border-green-300 shadow-sm">
                                        <i class="fas fa-user-graduate text-green-600 text-sm mr-2"></i>
                                        <span class="text-gray-800 font-medium text-base">{{ $backupTutor }}</span>
                                        <span class="ml-2 text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-medium">Ready</span>
                                    </div>
                                </div>
                                <div class="text-center text-xs text-gray-500 mt-2">Available if needed</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Bottom section - Always at bottom -->
                <div class="flex items-center justify-between border-t pt-3 mt-auto">
                    <span class="text-sm text-gray-700 font-medium">
                        Teachers: {{ count($mainTutors) }}/{{ $class->number_required ?? 0 }}
                        @if(count($backupTutors) > 0)
                            <span class="inline-flex items-center ml-3 px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-300">
                                <i class="fas fa-user-clock mr-1 text-xs"></i>
                                +{{ count($backupTutors) }} on standby
                            </span>
                        @endif
                    </span>
                    <button
                        class="editBtn text-[#F6B40E] hover:text-[#C88F00] transform transition duration-200 hover:scale-110"
                        data-class="{{ $class->class }}" 
                        data-time="{{ $class->time_jst ? \Carbon\Carbon::parse($class->time_jst)->subHour()->format('g:i A') : 'N/A' }}" 
                        data-date="{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}"
                        data-school="{{ $class->school }}"
                        data-required="{{ $class->number_required }}"
                        data-class-id="{{ $class->id }}"
                        data-assigned-tutors="{{ implode(',', $mainTutors) }}"
                        data-backup-tutor="{{ count($backupTutors) > 0 ? $backupTutors[0] : '' }}">
                        <i class="fas fa-edit"></i>
                    </button>
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
        <button onclick="saveScheduleAs('partial', '{{ $date }}')"
            class="bg-[#F6B40E] hover:bg-[#C88F00] text-white px-6 py-2 rounded-full font-medium 
                    transform transition duration-200 hover:scale-105">
            Save as Partial
        </button>

        <button onclick="saveScheduleAs('final', '{{ $date }}')"
            class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-full font-medium 
                    transform transition duration-200 hover:scale-105">
            Save as Final
        </button>
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
                <select id="addTutorSelect" class="border border-gray-300 rounded px-2 py-1 text-sm w-48">
                    <option value="">Add tutor</option>
                    <!-- Populate with available tutors -->
                </select>
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
                <select id="backupTutorSelect" class="border border-gray-300 rounded px-2 py-2 w-full text-sm mt-1">
                    <option value="">Select backup tutor</option>
                    <!-- Will be populated dynamically -->
                </select>
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
