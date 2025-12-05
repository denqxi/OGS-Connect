<!-- Page Title -->
<div class="p-6 border-b border-gray-200">
    <h2 class="text-xl font-semibold text-gray-800">Tutor Availability</h2>
</div>

<!-- Search Filters -->
<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
    </div>
    
    <form method="GET" action="{{ route('schedules.index') }}" id="tutorFilterForm">
        <input type="hidden" name="tab" value="employee">
        
        <div class="flex justify-between items-center space-x-4">
            <!-- Left side -->
            <div class="flex items-center space-x-4 flex-1 max-w-lg">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search full name, email, phone..."
                           id="tutorSearch"
                           class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md text-sm 
                                  focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
                                  focus:ring-0 focus:shadow-xl">
                    <button type="button" id="clearSearch" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <select name="status" id="filterStatus" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <div class="flex items-center space-x-1">
                <span class="text-sm text-gray-600">Available at:</span>
                        <div class="relative group">
                            <i class="fas fa-info-circle text-gray-400 text-xs cursor-help"></i>
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                Optional filters: Select day and/or time range using the dropdowns
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center space-x-2">
                            <div class="flex flex-col items-center">
                                <label class="text-xs text-gray-500 mb-1">Start</label>
                        <select id="startTime" 
                                class="border border-gray-300 rounded-md px-2 py-1 text-sm text-gray-600 bg-white w-20"
                                onchange="updateTimeRange(); updateEndTimeOptions();">
                            <option value="">--</option>
                            @for($hour = 7; $hour <= 15; $hour++)
                                @for($minute = 0; $minute < 60; $minute += 30)
                                    @php
                                        $time = sprintf('%02d:%02d', $hour, $minute);
                                        // Skip times after 3:30 PM (15:30)
                                        if ($hour == 15 && $minute > 30) break;
                                    @endphp
                                    <option value="{{ $time }}">{{ $time }}</option>
                                @endfor
                            @endfor
                        </select>
                            </div>
                            <span class="text-gray-400 text-sm">-</span>
                            <div class="flex flex-col items-center">
                                <label class="text-xs text-gray-500 mb-1">End</label>
                        <select id="endTime" 
                                class="border border-gray-300 rounded-md px-2 py-1 text-sm text-gray-600 bg-white w-20"
                                onchange="updateTimeRange()">
                            <option value="">--</option>
                            @for($hour = 7; $hour <= 15; $hour++)
                                @for($minute = 0; $minute < 60; $minute += 30)
                                    @php
                                        $time = sprintf('%02d:%02d', $hour, $minute);
                                        // Skip times after 3:30 PM (15:30)
                                        if ($hour == 15 && $minute > 30) break;
                                    @endphp
                                    <option value="{{ $time }}">{{ $time }}</option>
                                @endfor
                            @endfor
                </select>
                            </div>
                            <input type="hidden" name="time_slot" id="filterTimeSlot" value="{{ request('time_slot') }}">
                        </div>
                        
                        <span class="text-sm text-gray-400">+</span>
                
                <select name="day" id="filterDay" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
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
                            <option value="{{ $dayValue }}" {{ request('day') == $dayValue ? 'selected' : '' }}>
                                {{ $dayDisplay }}
                            </option>
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
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <button type="submit" 
                            class="bg-[#0E335D] text-white px-6 py-2 rounded-md text-sm font-medium hover:bg-[#0E335D]/90 transition-colors flex items-center space-x-2">
                        <i class="fas fa-search"></i>
                        <span>Apply</span>
                    </button>
                    
                    @if(request()->hasAny(['search', 'status', 'time_slot', 'day']))
                        <a href="{{ route('schedules.index', ['tab' => 'employee']) }}" 
                           class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-600 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Tutor Table -->
<div class="overflow-x-auto">
    <table class="w-full" id="tutorTable">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="tutorTableBody">
            @forelse($tutors ?? [] as $tutor)
            <tr class="hover:bg-gray-50 tutor-row" data-searchable="{{ strtolower(($tutor->full_name ?? '') . ' ' . ($tutor->email ?? '') . ' ' . ($tutor->phone_number ?? '')) }}">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $tutor->full_name ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tutor->phone_number ?? 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 underline">
                    <a href="mailto:{{ $tutor->email ?? '' }}">{{ $tutor->email ?? 'N/A' }}</a>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    @if(isset($tutor->formatted_available_time) && $tutor->formatted_available_time)
                        <div class="flex flex-col">
                            @php
                                // Display the formatted available time directly from the query result
                                $displayTime = is_array($tutor->formatted_available_time) 
                                    ? implode(', ', $tutor->formatted_available_time) 
                                    : $tutor->formatted_available_time;
                                
                                // Filtering is now handled in the controller query
                                // If both day and time filters are applied, show only the filtered combination
                                if (request('day') && request('time_slot')) {
                                    $dayName = request('day');
                                    $timeSlot = request('time_slot');
                                    
                                    // Ensure dayName is a string
                                    if (is_array($dayName)) {
                                        $dayName = $dayName[0] ?? '';
                                    }
                                    
                                    // Ensure timeSlot is a string
                                    if (is_array($timeSlot)) {
                                        $timeSlot = $timeSlot[0] ?? '';
                                    }
                                    
                                    // Convert day to proper format for display
                                    $dayMap = [
                                        'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday',
                                        'thur' => 'Thursday', 'fri' => 'Friday'
                                    ];
                                    $displayDay = $dayMap[$dayName] ?? ucfirst($dayName);
                                    
                                    $filteredTime = $displayDay . ' - ' . $timeSlot;
                                }
                                // If only day filter is applied, show all times for that day
                                elseif (request('day')) {
                                    $dayName = request('day');
                                    
                                    // Ensure dayName is a string
                                    if (is_array($dayName)) {
                                        $dayName = $dayName[0] ?? '';
                                    }
                                    
                                    $dayMap = [
                                        'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday',
                                        'thur' => 'Thursday', 'fri' => 'Friday'
                                    ];
                                    $displayDay = $dayMap[$dayName] ?? ucfirst($dayName);
                                    
                                    // Extract times for this specific day from the available_times
                                    $availableTimes = $tutor->available_times ?? '';
                                    
                                    $dayTimes = [];
                                    
                                    // Handle array format of available_times
                                    if (is_array($availableTimes)) {
                                        // If available_times is an array, look for the specific day
                                        $dayKey = strtolower($dayName);
                                        $dayTimes = $availableTimes[$dayKey] ?? $availableTimes[$dayName] ?? [];
                                        
                                        // Ensure dayTimes is an array of strings
                                        if (!is_array($dayTimes)) {
                                            $dayTimes = [$dayTimes];
                                        }
                                        
                                        // Convert any array elements to strings
                                        $dayTimes = array_map(function($time) {
                                            return is_array($time) ? implode('-', $time) : (string)$time;
                                        }, $dayTimes);
                                    } else {
                                        // Handle string format of available_times
                                        $availableTimes = (string)$availableTimes;
                                        
                                        // Parse the available_times string to find times for this day
                                        if (preg_match_all('/' . preg_quote($dayName, '/') . ':\s*([^,]+)/i', $availableTimes, $matches)) {
                                            foreach ($matches[1] as $time) {
                                                $dayTimes[] = trim($time);
                                            }
                                        }
                                    }
                                    
                                    if (!empty($dayTimes)) {
                                        $filteredTime = $displayDay . ' - ' . implode(', ', $dayTimes);
                                    } else {
                                        $filteredTime = $displayDay . ' - No specific times';
                                    }
                                }
                                // If only time filter is applied, show the actual available time ranges that contain the requested time
                                elseif (request('time_slot')) {
                                    $requestedTimeSlot = request('time_slot');
                                    
                                    // Ensure requestedTimeSlot is a string
                                    if (is_array($requestedTimeSlot)) {
                                        $requestedTimeSlot = $requestedTimeSlot[0] ?? '';
                                    }
                                    
                                    $availableTimes = $tutor->available_times ?? '';
                                    
                                    $matchingRanges = [];
                                    
                                    // Handle array format of available_times
                                    if (is_array($availableTimes)) {
                                        // If available_times is an array, iterate through each day
                                        foreach ($availableTimes as $day => $times) {
                                            if (!is_array($times)) {
                                                $times = [$times];
                                            }
                                            
                                            foreach ($times as $timeRange) {
                                                if (strpos($timeRange, '-') !== false) {
                                                    list($startTime, $endTime) = explode('-', $timeRange);
                                                    
                                                    // Check if the requested time range is contained within this available range
                                                    if (strpos($requestedTimeSlot, '-') !== false) {
                                                        list($requestedStart, $requestedEnd) = explode('-', $requestedTimeSlot);
                                                        
                                                        // Convert times to minutes for comparison
                                                        $requestedStartMinutes = (int)substr($requestedStart, 0, 2) * 60 + (int)substr($requestedStart, 3, 2);
                                                        $requestedEndMinutes = (int)substr($requestedEnd, 0, 2) * 60 + (int)substr($requestedEnd, 3, 2);
                                                        $startMinutes = (int)substr($startTime, 0, 2) * 60 + (int)substr($startTime, 3, 2);
                                                        $endMinutes = (int)substr($endTime, 0, 2) * 60 + (int)substr($endTime, 3, 2);
                                                        
                                                        // Check if requested range is contained within available range
                                                        if ($requestedStartMinutes >= $startMinutes && $requestedEndMinutes <= $endMinutes) {
                                                            $dayMap = [
                                                                'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday',
                                                                'thur' => 'Thursday', 'fri' => 'Friday'
                                                            ];
                                                            $dayName = $dayMap[$day] ?? ucfirst($day);
                                                            $matchingRanges[] = $dayName . ': ' . $startTime . '-' . $endTime;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        // Handle string format of available_times
                                        $availableTimes = (string)$availableTimes;
                                        
                                        // Parse the available_times string to find time ranges that contain the requested time
                                        if (preg_match_all('/([a-z]+):\s*([0-9]{1,2}:[0-9]{2})\s*-\s*([0-9]{1,2}:[0-9]{2})/i', $availableTimes, $matches, PREG_SET_ORDER)) {
                                        foreach ($matches as $match) {
                                            $day = $match[1];
                                            $startTime = $match[2];
                                            $endTime = $match[3];
                                            
                                            // Check if the requested time range is contained within this available range
                                            if (strpos($requestedTimeSlot, '-') !== false) {
                                                list($requestedStart, $requestedEnd) = explode('-', $requestedTimeSlot);
                                                
                                                // Convert times to minutes for comparison
                                                $requestedStartMinutes = (int)substr($requestedStart, 0, 2) * 60 + (int)substr($requestedStart, 3, 2);
                                                $requestedEndMinutes = (int)substr($requestedEnd, 0, 2) * 60 + (int)substr($requestedEnd, 3, 2);
                                                $startMinutes = (int)substr($startTime, 0, 2) * 60 + (int)substr($startTime, 3, 2);
                                                $endMinutes = (int)substr($endTime, 0, 2) * 60 + (int)substr($endTime, 3, 2);
                                                
                                                // Check if requested range is contained within available range
                                                if ($requestedStartMinutes >= $startMinutes && $requestedEndMinutes <= $endMinutes) {
                                                    $dayMap = [
                                                        'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday',
                                                        'thur' => 'Thursday', 'fri' => 'Friday'
                                                    ];
                                                    $dayName = $dayMap[$day] ?? ucfirst($day);
                                                    $matchingRanges[] = $dayName . ': ' . $startTime . '-' . $endTime;
                                                }
                                            }
                                        }
                                        }
                                    }
                                    
                                    if (!empty($matchingRanges)) {
                                        $filteredTime = implode(', ', $matchingRanges);
                                    } else {
                                        $formattedTime = $tutor->formatted_available_time;
                                        $filteredTime = is_array($formattedTime) ? implode(', ', $formattedTime) : $formattedTime;
                                    }
                                }
                                // If no filters applied, show full availability
                                else {
                                    $formattedTime = $tutor->formatted_available_time;
                                    $filteredTime = is_array($formattedTime) ? implode(', ', $formattedTime) : $formattedTime;
                                }
                            @endphp
                            
                            <span class="font-medium text-gray-700">{{ $displayTime }}</span>
                            <span class="text-xs text-green-600 font-medium">(GLS Account)</span>
                        </div>
                    @else
                        <span class="text-red-500 text-sm">No GLS availability</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        {{ $tutor->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($tutor->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                    @if($tutor->status === 'active')
                        <button class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition-colors text-xs font-medium"
                                onclick="toggleTutorStatus('{{ $tutor->tutorID }}', 'inactive')">
                            Deactivate
                        </button>
                    @else
                        <button class="px-3 py-1 bg-green-100 text-green-600 rounded hover:bg-green-200 transition-colors text-xs font-medium"
                                onclick="toggleTutorStatus('{{ $tutor->tutorID }}', 'active')">
                            Activate
                        </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr id="noResultsRow">
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                    <p class="text-lg font-medium">No tutors found</p>
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
@if(isset($tutors))
    @include('schedules.tabs.partials.tutor-pagination', ['tutors' => $tutors])
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

<script src="{{ asset('js/employee-availability.js') }}"></script>
