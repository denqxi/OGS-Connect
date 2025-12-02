<!-- Search Filters -->
<div class="px-4 md:px-6 pt-4 md:pt-6 pb-3 border-b border-gray-200">
    <form method="GET" action="{{ route('employees.index') }}" id="glsFilterForm">
        <input type="hidden" name="tab" value="gls">

        <div class="flex items-center justify-between gap-x-6">

            <!-- Left label -->
            <h3 class="text-sm font-medium text-gray-700 whitespace-nowrap">
                Search Filters
            </h3>

            <!-- Middle filters -->
            <div class="flex items-center gap-x-4 flex-1">

                <!-- Search Input (SHORTER WIDTH) -->
                <div class="relative" style="max-width: 250px; width: 100%;">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>

                    <input type="text" 
                           name="search"
                           placeholder="Search tutors..."
                           value="{{ request('search') }}"
                           id="glsSearch"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm 
                                  focus:outline-none focus:border-[#2A5382] focus:ring-1 focus:ring-[#2A5382]/50">
                </div>

                <!-- Status -->
                <select name="status" id="filterGlsStatus"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 bg-white
                               focus:border-[#2A5382] focus:ring-1 focus:ring-[#2A5382]/50"
                        onchange="handleGlsFilterChange('status')">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Right filters -->
            <div class="flex items-center gap-x-4">

                <span class="text-sm text-gray-600 whitespace-nowrap">Available at:</span>

                <!-- Time Slot -->
                <select name="time_slot" id="filterGlsTimeSlot"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 bg-white
                               focus:border-[#2A5382] focus:ring-1 focus:ring-[#2A5382]/50"
                        onchange="handleGlsFilterChange('time_slot')">
                    <option value="">All Times</option>
                    <option value="07:00 - 08:00" {{ request('time_slot') == '07:00 - 08:00' ? 'selected' : '' }}>07:00 - 08:00 AM</option>
                    <option value="08:00 - 09:00" {{ request('time_slot') == '08:00 - 09:00' ? 'selected' : '' }}>08:00 - 09:00 AM</option>
                    <option value="09:00 - 10:00" {{ request('time_slot') == '09:00 - 10:00' ? 'selected' : '' }}>09:00 - 10:00 AM</option>
                    <option value="10:00 - 11:00" {{ request('time_slot') == '10:00 - 11:00' ? 'selected' : '' }}>10:00 - 11:00 AM</option>
                    <option value="11:00 - 12:00" {{ request('time_slot') == '11:00 - 12:00' ? 'selected' : '' }}>11:00 - 12:00 PM</option>
                    <option value="12:00 - 13:00" {{ request('time_slot') == '12:00 - 13:00' ? 'selected' : '' }}>12:00 - 13:00 PM</option>
                    <option value="13:00 - 14:00" {{ request('time_slot') == '13:00 - 14:00' ? 'selected' : '' }}>01:00 - 02:00 PM</option>
                    <option value="14:00 - 15:00" {{ request('time_slot') == '14:00 - 15:00' ? 'selected' : '' }}>02:00 - 03:00 PM</option>
                    <option value="15:00 - 16:00" {{ request('time_slot') == '15:00 - 16:00' ? 'selected' : '' }}>03:00 - 04:00 PM</option>
                </select>

                <!-- Day -->
                <select name="day" id="filterGlsDay"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 bg-white
                               focus:border-[#2A5382] focus:ring-1 focus:ring-[#2A5382]/50"
                        onchange="handleGlsFilterChange('day')">
                    <option value="">All Days</option>
                    <option value="mon" {{ request('day') == 'mon' ? 'selected' : '' }}>Monday</option>
                    <option value="tue" {{ request('day') == 'tue' ? 'selected' : '' }}>Tuesday</option>
                    <option value="wed" {{ request('day') == 'wed' ? 'selected' : '' }}>Wednesday</option>
                    <option value="thur" {{ request('day') == 'thur' ? 'selected' : '' }}>Thursday</option>
                    <option value="fri" {{ request('day') == 'fri' ? 'selected' : '' }}>Friday</option>
                </select>
            </div>

        </div>
    </form>
</div>


<!-- Employee Table -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutor ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('employees.index', array_merge(request()->all(), ['tab' => 'gls', 'sort' => request('sort') === 'name' && request('direction') === 'desc' ? '' : 'name', 'direction' => request('sort') === 'name' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                    <div class="flex items-center gap-1">
                        Name
                        @if(request('sort') === 'name')
                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                        @else
                            <i class="fas fa-sort text-xs opacity-30"></i>
                        @endif
                    </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Days</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('employees.index', array_merge(request()->all(), ['tab' => 'gls', 'sort' => request('sort') === 'status' && request('direction') === 'desc' ? '' : 'status', 'direction' => request('sort') === 'status' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                    <div class="flex items-center gap-1">
                        Status
                        @if(request('sort') === 'status')
                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                        @else
                            <i class="fas fa-sort text-xs opacity-30"></i>
                        @endif
                    </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="glsTableBody">
            @forelse($tutors ?? [] as $tutor)
            <tr class="hover:bg-gray-50 gls-row" data-searchable="{{ strtolower(($tutor->full_name ?? '') . ' ' . ($tutor->email ?? '') . ' ' . ($tutor->phone_number ?? '')) }}">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tutor->tutorID ? str_pad($tutor->tutorID, 4, '0', STR_PAD_LEFT) : 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $tutor->full_name ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    @if($tutor->workPreferences)
                        <div class="flex flex-col">
                            <span class="font-medium text-gray-700">
                                {{ $tutor->workPreferences->start_time ? \Carbon\Carbon::parse($tutor->workPreferences->start_time)->format('g:i A') : 'N/A' }} - 
                                {{ $tutor->workPreferences->end_time ? \Carbon\Carbon::parse($tutor->workPreferences->end_time)->format('g:i A') : 'N/A' }}
                                <span class="text-xs text-gray-500">({{ $tutor->workPreferences->timezone ?? 'UTC' }})</span>
                            </span>
                        </div>
                    @else
                        <span class="text-red-500 text-sm">Not set</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">
                    @if($tutor->workPreferences && $tutor->workPreferences->days_available)
                        @php
                            $days = is_array($tutor->workPreferences->days_available) 
                                ? $tutor->workPreferences->days_available 
                                : json_decode($tutor->workPreferences->days_available, true);
                            $dayMap = [
                                'monday' => 'Mon', 'mon' => 'Mon',
                                'tuesday' => 'Tue', 'tue' => 'Tue',
                                'wednesday' => 'Wed', 'wed' => 'Wed',
                                'thursday' => 'Thu', 'thur' => 'Thu', 'thu' => 'Thu',
                                'friday' => 'Fri', 'fri' => 'Fri',
                                'saturday' => 'Sat', 'sat' => 'Sat',
                                'sunday' => 'Sun', 'sun' => 'Sun'
                            ];
                            $allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            $normalizedDays = array_map('strtolower', $days);
                            
                            // Check if all weekdays
                            $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                            $isAllWeekdays = count(array_intersect($normalizedDays, $weekdays)) === 5 && count($normalizedDays) === 5;
                            
                            // Check if all days
                            $isAllDays = count(array_intersect($normalizedDays, $allDays)) === 7;
                            
                            if ($isAllDays) {
                                $displayText = 'Mon-Sun';
                            } elseif ($isAllWeekdays) {
                                $displayText = 'Mon-Fri';
                            } else {
                                // Show abbreviated days
                                $abbrevDays = array_map(function($day) use ($dayMap) {
                                    return $dayMap[strtolower($day)] ?? ucfirst(substr($day, 0, 3));
                                }, $days);
                                $displayText = implode(', ', $abbrevDays);
                            }
                        @endphp
                        <span class="font-medium">{{ $displayText }}</span>
                    @else
                        <span class="text-red-500 text-sm">Not set</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $tutor->status === 'active' ? 'bg-[#65DB7F]' : 'bg-[#F65353]' }}"></span>
                        <span class="text-xs font-medium text-gray-500">
                            {{ ucfirst($tutor->status) }}
                        </span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex space-x-2">
                        <button onclick="openEmployeeModal('tutor', '{{ $tutor->tutorID }}')" 
                                class="w-8 h-8 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 inline-flex items-center justify-center transition-colors"
                                title="View Details">
                            <i class="fas fa-eye text-xs"></i>
                        </button>
                        @if($tutor->status === 'active')
                            <button class="w-8 h-8 bg-red-100 text-red-600 rounded hover:bg-red-200 inline-flex items-center justify-center transition-colors"
                                    onclick="toggleTutorStatus('{{ $tutor->tutorID }}', 'inactive')" title="Deactivate">
                                <i class="fas fa-user-slash text-xs"></i>
                            </button>
                        @else
                            <button class="w-8 h-8 bg-green-100 text-green-600 rounded hover:bg-green-200 inline-flex items-center justify-center transition-colors"
                                    onclick="toggleTutorStatus('{{ $tutor->tutorID }}', 'active')" title="Activate">
                                <i class="fas fa-user-check text-xs"></i>
                            </button>
                            <button class="w-8 h-8 bg-orange-100 text-orange-600 rounded hover:bg-orange-200 inline-flex items-center justify-center transition-colors"
                                    onclick="openArchiveModal('tutor', '{{ $tutor->tutorID }}', '{{ $tutor->full_name }}')" title="Archive">
                                <i class="fas fa-archive text-xs"></i>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr id="noGlsResultsRow">
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                    <p class="text-lg font-medium">No GLS tutors found</p>
                    <p class="text-sm">Try adjusting your search criteria</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>


<!-- Pagination -->
@if(isset($tutors))
    @include('emp_management.partials.compact-pagination', ['data' => $tutors, 'tab' => 'gls'])
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
    window.glsTotalResults = @json(isset($tutors) && method_exists($tutors, 'total') ? $tutors->total() : 0);
    
    function handleGlsFilterChange(changed) {
        // Time slot and day are mutually exclusive, but status works with both
        if (changed === 'time_slot') {
            document.getElementById('filterGlsDay').value = '';
        } else if (changed === 'day') {
            document.getElementById('filterGlsTimeSlot').value = '';
        }
        
        // Submit the form to apply filters
        document.getElementById('glsFilterForm').submit();
    }

    // Handle search input - submit form on Enter
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('glsSearch');
        const form = document.getElementById('glsFilterForm');

        if (searchInput) {
            // Handle Enter key
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.submit();
                }
            });
        }
    });

    // Toggle tutor status (active/inactive)
    function toggleTutorStatus(tutorId, newStatus) {
        if (!tutorId) {
            showNotification('Unable to identify the tutor. Please refresh the page and try again.', 'error');
            return;
        }

        // Get tutor name and button reference for better user experience
        const tutorRow = event.target.closest('tr');
        const tutorName = tutorRow ? tutorRow.querySelector('td:nth-child(2)').textContent.trim() : 'this tutor';
        const button = event.target; // Capture the button reference

        // Show custom confirmation modal
        const action = newStatus === 'active' ? 'activate' : 'deactivate';
        const actionText = newStatus === 'active' ? 'activate and make available for class assignments' : 'deactivate and remove from class assignments';
        
        showConfirmationModal(
            `Are you sure you want to ${action} ${tutorName}?`,
            `This will ${actionText}.`,
            () => {
                // User confirmed, proceed with the action
                proceedWithStatusUpdate(tutorId, newStatus, tutorName, button);
            }
        );
    }

    // Function to proceed with status update after confirmation
    function proceedWithStatusUpdate(tutorId, newStatus, tutorName, button) {
        console.log('Proceeding with status update:', { tutorId, newStatus, tutorName });
        
        const originalHTML = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';

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
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                // Show success toast notification
                const successMessage = newStatus === 'active' 
                    ? `${tutorName} has been activated and is now available for class assignments.`
                    : `${tutorName} has been deactivated and will no longer receive class assignments.`;
                
                showNotification(successMessage, 'success');
                
                // Reload the page after a short delay to let users see the success message
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                // Show error notification
                showNotification(`Failed to update ${tutorName}'s status. ${data.message || 'Please try again or contact support if the issue persists.'}`, 'error');
                
                // Restore button state
                button.disabled = false;
                button.innerHTML = originalHTML;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(`An unexpected error occurred while updating ${tutorName}'s status. Please check your internet connection and try again.`, 'error');
            
            // Restore button state
            button.disabled = false;
            button.innerHTML = originalHTML;
        });
    }

    // Custom confirmation modal function
    function showConfirmationModal(title, message, onConfirm) {
        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
        overlay.id = 'confirmation-overlay';
        
        // Create modal content
        const modal = document.createElement('div');
        modal.className = 'bg-white rounded-lg shadow-xl max-w-md w-full mx-4';
        modal.innerHTML = `
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-gray-900">${title}</h3>
                    </div>
                </div>
                <div class="mb-6">
                    <p class="text-sm text-gray-500">${message}</p>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" id="cancel-btn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </button>
                    <button type="button" id="confirm-btn" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Confirm
                    </button>
                </div>
            </div>
        `;
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        // Add event listeners
        document.getElementById('cancel-btn').addEventListener('click', () => {
            document.body.removeChild(overlay);
        });
        
        document.getElementById('confirm-btn').addEventListener('click', () => {
            console.log('Confirm button clicked');
            document.body.removeChild(overlay);
            onConfirm();
        });
        
        // Close on overlay click
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                document.body.removeChild(overlay);
            }
        });
        
        // Close on Escape key
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                document.body.removeChild(overlay);
                document.removeEventListener('keydown', handleEscape);
            }
        };
        document.addEventListener('keydown', handleEscape);
    }

    // Toast notification function (consistent with other parts of the application)
    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-medium transition-all duration-300 transform translate-x-full opacity-0 ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        
        // Add icon and message
        notification.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        ${type === 'success' 
                            ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>'
                            : '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>'
                        }
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">${message}</p>
                </div>
            </div>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Show notification with animation
        setTimeout(() => {
            notification.classList.remove('translate-x-full', 'opacity-0');
        }, 100);
        
        // Remove after appropriate duration (longer for success messages)
        const duration = type === 'success' ? 3000 : 4000;
        setTimeout(() => {
            notification.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, duration);
    }
</script>
