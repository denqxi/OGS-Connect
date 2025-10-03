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
                    <button type="button" id="clearSearch" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 {{ request('search') ? '' : 'hidden' }}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <select name="status" id="filterStatus" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="handleTutorFilterChange('status')">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">Available at:</span>
                <select name="time_slot" id="filterTimeSlot" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="handleTutorFilterChange('time_slot')">
                    <option value="">All Times</option>
                    @if(isset($availableTimeSlots) && $availableTimeSlots->count() > 0)
                        @foreach($availableTimeSlots as $timeSlot)
                            <option value="{{ $timeSlot }}" {{ request('time_slot') == $timeSlot ? 'selected' : '' }}>
                                {{ $timeSlot }}
                            </option>
                        @endforeach
                    @else
                        <!-- Fallback options if availableTimeSlots is not set or empty -->
                        <option value="07:00 - 08:00" {{ request('time_slot') == '07:00 - 08:00' ? 'selected' : '' }}>07:00 - 08:00</option>
                        <option value="08:00 - 09:00" {{ request('time_slot') == '08:00 - 09:00' ? 'selected' : '' }}>08:00 - 09:00</option>
                        <option value="09:00 - 10:00" {{ request('time_slot') == '09:00 - 10:00' ? 'selected' : '' }}>09:00 - 10:00</option>
                        <option value="10:00 - 11:00" {{ request('time_slot') == '10:00 - 11:00' ? 'selected' : '' }}>10:00 - 11:00</option>
                    @endif
                </select>
                
                <select name="day" id="filterDay" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="handleTutorFilterChange('day')">
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

                @if(request()->hasAny(['search', 'status', 'time_slot', 'day']))
                    <a href="{{ route('schedules.index', ['tab' => 'employee']) }}" 
                       class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-600 transition-colors">
                        Clear
                    </a>
                @endif
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
            @php
                $glsAccount = $tutor->accounts->firstWhere('account_name', 'GLS');
            @endphp
            <tr class="hover:bg-gray-50 tutor-row" data-searchable="{{ strtolower(($tutor->full_name ?? '') . ' ' . ($tutor->email ?? '') . ' ' . ($tutor->phone_number ?? '')) }}">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $tutor->full_name ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tutor->phone_number ?? 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 underline">
                    <a href="mailto:{{ $tutor->email ?? '' }}">{{ $tutor->email ?? 'N/A' }}</a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    @if($glsAccount)
                        <div class="flex flex-col">
                            <span class="font-medium text-gray-700">{{ $glsAccount->formatted_available_time }}</span>
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
                        <button class="px-3 py-1 bg-red-100 text-red-600 rounded-md hover:bg-red-200 transition-colors text-xs font-medium"
                                onclick="toggleTutorStatus('{{ $tutor->tutorID }}', 'inactive')">
                            Deactivate
                        </button>
                    @else
                        <button class="px-3 py-1 bg-green-100 text-green-600 rounded-md hover:bg-green-200 transition-colors text-xs font-medium"
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

<script>
    window.tutorTotalResults = @json(isset($tutors) && method_exists($tutors, 'total') ? $tutors->total() : 0);
    
    function handleTutorFilterChange(changed) {
        // Only one filter can be active at a time
        if (changed === 'time_slot') {
            document.getElementById('filterDay').value = '';
            document.getElementById('filterStatus').value = '';
        } else if (changed === 'day') {
            document.getElementById('filterTimeSlot').value = '';
            document.getElementById('filterStatus').value = '';
        } else if (changed === 'status') {
            document.getElementById('filterTimeSlot').value = '';
            document.getElementById('filterDay').value = '';
        }
        
        // Submit the form to apply filters
        document.getElementById('tutorFilterForm').submit();
    }

    // Handle search input - submit form on Enter or when user stops typing
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('tutorSearch');
        const clearSearchBtn = document.getElementById('clearSearch');
        const form = document.getElementById('tutorFilterForm');
        let searchTimeout;

        if (searchInput) {
            // Handle search input with debounce
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                // Show/hide clear button
                if (query.length > 0) {
                    if (clearSearchBtn) clearSearchBtn.classList.remove('hidden');
                } else {
                    if (clearSearchBtn) clearSearchBtn.classList.add('hidden');
                }

                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Debounce search - auto-submit form after user stops typing for 800ms
                searchTimeout = setTimeout(() => {
                    form.submit();
                }, 800);
            });

            // Handle Enter key
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimeout);
                    form.submit();
                }
            });

            // Clear search
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    clearSearchBtn.classList.add('hidden');
                    form.submit();
                });
            }
        }
    });

    // Toggle tutor status (active/inactive)
    function toggleTutorStatus(tutorId, newStatus) {
        if (!tutorId) {
            alert('Error: Tutor ID not found');
            return;
        }

        // Show confirmation dialog
        const action = newStatus === 'active' ? 'activate' : 'deactivate';
        if (!confirm(`Are you sure you want to ${action} this tutor?`)) {
            return;
        }

        // Show loading state
        const button = event.target;
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Updating...';

        // Make AJAX request
        fetch(`/tutors/${tutorId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert(data.message);
                
                // Reload the page to reflect changes
                window.location.reload();
            } else {
                // Show error message
                alert(data.message || 'Failed to update tutor status');
                
                // Restore button state
                button.disabled = false;
                button.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating tutor status');
            
            // Restore button state
            button.disabled = false;
            button.textContent = originalText;
        });
    }
</script>
