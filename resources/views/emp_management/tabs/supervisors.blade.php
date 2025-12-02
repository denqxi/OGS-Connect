<!-- Search Filters -->
<div class="px-4 md:px-6 pt-4 md:pt-6 pb-3 border-b border-gray-200">
    <form method="GET" action="{{ route('employees.index') }}" id="supervisorFilterForm">
        <input type="hidden" name="tab" value="supervisors">
        
        <div class="flex items-center gap-x-4">
            <!-- Left label -->
            <h3 class="text-sm font-medium text-gray-700 whitespace-nowrap">
                Search Filters
            </h3>

            <!-- Search Input -->
            <div class="relative" style="max-width: 250px; width: 100%;">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" 
                       name="search"
                       placeholder="Search supervisors..."
                       value="{{ request('search') }}"
                       id="supervisorSearch"
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm 
                              focus:outline-none focus:border-[#2A5382] focus:ring-1 focus:ring-[#2A5382]/50">
            </div>
            
            <!-- Status -->
            <select name="status" id="filterSupervisorStatus"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 bg-white
                           focus:border-[#2A5382] focus:ring-1 focus:ring-[#2A5382]/50"
                    onchange="handleSupervisorFilterChange('status')">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>

            <!-- Account -->
            <select name="account" id="filterSupervisorAccount"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 bg-white
                           focus:border-[#2A5382] focus:ring-1 focus:ring-[#2A5382]/50"
                    onchange="handleSupervisorFilterChange('account')">
                <option value="">All Accounts</option>
                <option value="GLS" {{ request('account') == 'GLS' ? 'selected' : '' }}>GLS</option>
                <option value="Tutlo" {{ request('account') == 'Tutlo' ? 'selected' : '' }}>Tutlo</option>
                <option value="Babilala" {{ request('account') == 'Babilala' ? 'selected' : '' }}>Babilala</option>
                <option value="Talk915" {{ request('account') == 'Talk915' ? 'selected' : '' }}>Talk915</option>
            </select>
        </div>
    </form>
</div>

<!-- Supervisor Table -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('employees.index', array_merge(request()->all(), ['tab' => 'supervisors', 'sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}'">
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('employees.index', array_merge(request()->all(), ['tab' => 'supervisors', 'sort' => 'assigned_account', 'direction' => request('sort') === 'assigned_account' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}'">
                    <div class="flex items-center gap-1">
                        Assigned Account
                        @if(request('sort') === 'assigned_account')
                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                        @else
                            <i class="fas fa-sort text-xs opacity-30"></i>
                        @endif
                    </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('employees.index', array_merge(request()->all(), ['tab' => 'supervisors', 'sort' => 'status', 'direction' => request('sort') === 'status' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}'">
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
        <tbody class="bg-white divide-y divide-gray-200" id="supervisorTableBody">
            @forelse($supervisors ?? [] as $supervisor)
            <tr class="hover:bg-gray-50 supervisor-row" data-searchable="{{ strtolower(($supervisor->full_name ?? '') . ' ' . ($supervisor->email ?? '') . ' ' . ($supervisor->phone_number ?? '')) }}">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $supervisor->supID ? str_pad($supervisor->supID, 4, '0', STR_PAD_LEFT) : 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $supervisor->full_name ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    @if($supervisor->start_time && $supervisor->end_time)
                        <div class="flex flex-col">
                            <span class="font-medium text-gray-700">
                                {{ \Carbon\Carbon::parse($supervisor->start_time)->format('g:i A') }} - 
                                {{ \Carbon\Carbon::parse($supervisor->end_time)->format('g:i A') }}
                                <span class="text-xs text-gray-500">({{ $supervisor->timezone ?? 'UTC' }})</span>
                            </span>
                        </div>
                    @else
                        <span class="text-red-500 text-sm">Not set</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">
                    @if($supervisor->days_available && is_array($supervisor->days_available))
                        @php
                            $days = $supervisor->days_available;
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
                            
                            $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                            $isAllWeekdays = count(array_intersect($normalizedDays, $weekdays)) === 5 && count($normalizedDays) === 5;
                            $isAllDays = count(array_intersect($normalizedDays, $allDays)) === 7;
                            
                            if ($isAllDays) {
                                $displayText = 'Mon-Sun';
                            } elseif ($isAllWeekdays) {
                                $displayText = 'Mon-Fri';
                            } else {
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
                    @if($supervisor->assigned_account)
                        <div class="flex items-center gap-2">
                            @php
                                $accountColors = [
                                    'GLS' => 'bg-[#0E335D]',
                                    'Tutlo' => 'bg-[#E6B800]',
                                    'Babilala' => 'bg-[#A78BFA]',
                                    'Talk915' => 'bg-[#128AD4]',
                                ];
                                $circleColor = $accountColors[$supervisor->assigned_account] ?? 'bg-blue-500';
                            @endphp
                            <span class="w-2.5 h-2.5 rounded-full {{ $circleColor }}"></span>
                            <span class="text-xs font-medium text-gray-500">
                                {{ $supervisor->assigned_account }}
                            </span>
                        </div>
                    @else
                        <span class="text-gray-400 text-xs">Unassigned</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $supervisor->status === 'active' ? 'bg-[#65DB7F]' : 'bg-[#F65353]' }}"></span>
                        <span class="text-xs font-medium text-gray-500">
                            {{ ucfirst($supervisor->status) }}
                        </span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex space-x-2">
                        <button onclick="openEmployeeModal('supervisor', '{{ $supervisor->supID }}')" 
                                class="w-8 h-8 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 inline-flex items-center justify-center transition-colors"
                                title="View Details">
                            <i class="fas fa-eye text-xs"></i>
                        </button>
                        @if($supervisor->status === 'active')
                            <button class="w-8 h-8 bg-red-100 text-red-600 rounded hover:bg-red-200 inline-flex items-center justify-center transition-colors"
                                    onclick="toggleSupervisorStatus('{{ $supervisor->supID }}', 'inactive')" title="Deactivate">
                                <i class="fas fa-user-slash text-xs"></i>
                            </button>
                        @else
                            <button class="w-8 h-8 bg-green-100 text-green-600 rounded hover:bg-green-200 inline-flex items-center justify-center transition-colors"
                                    onclick="toggleSupervisorStatus('{{ $supervisor->supID }}', 'active')" title="Activate">
                                <i class="fas fa-user-check text-xs"></i>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr id="noSupervisorResultsRow">
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-user-tie text-4xl mb-4 opacity-50"></i>
                    <p class="text-lg font-medium">No supervisors found</p>
                    <p class="text-sm">Try adjusting your search criteria</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if(isset($supervisors))
    @include('emp_management.partials.compact-pagination', ['data' => $supervisors, 'tab' => 'supervisors'])
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
    window.supervisorTotalResults = @json(isset($supervisors) && method_exists($supervisors, 'total') ? $supervisors->total() : 0);
    
    function handleSupervisorFilterChange(changed) {
        // Submit the form to apply filters
        document.getElementById('supervisorFilterForm').submit();
    }

    // Handle search input - submit form on Enter
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('supervisorSearch');
        const form = document.getElementById('supervisorFilterForm');

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

    // Toggle supervisor status (active/inactive)
    function toggleSupervisorStatus(supervisorId, newStatus) {
        if (!supervisorId) {
            showNotification('Unable to identify the supervisor. Please refresh the page and try again.', 'error');
            return;
        }

        // Get supervisor name and button reference for better user experience
        const supervisorRow = event.target.closest('tr');
        const supervisorName = supervisorRow ? supervisorRow.querySelector('td:nth-child(1) a').textContent.trim() : 'this supervisor';
        const button = event.target.closest('button');

        // Show custom confirmation modal
        const action = newStatus === 'active' ? 'activate' : 'deactivate';
        const actionText = newStatus === 'active' ? 'activate' : 'deactivate';
        
        showConfirmationModal(
            `Are you sure you want to ${action} ${supervisorName}?`,
            `This will ${actionText} the supervisor.`,
            () => {
                proceedWithSupervisorStatusUpdate(supervisorId, newStatus, supervisorName, button);
            }
        );
    }

    // Function to proceed with status update after confirmation
    function proceedWithSupervisorStatusUpdate(supervisorId, newStatus, supervisorName, button) {
        const originalHTML = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';

        fetch(`/supervisors/${supervisorId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const successMessage = newStatus === 'active' 
                    ? `${supervisorName} has been activated.`
                    : `${supervisorName} has been deactivated.`;
                
                showNotification(successMessage, 'success');
                setTimeout(() => window.location.reload(), 2000);
            } else {
                showNotification(`Failed to update ${supervisorName}'s status. ${data.message || 'Please try again.'}`, 'error');
                button.disabled = false;
                button.innerHTML = originalHTML;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(`An unexpected error occurred while updating ${supervisorName}'s status.`, 'error');
            button.disabled = false;
            button.innerHTML = originalHTML;
        });
    }

    // Custom confirmation modal function
    function showConfirmationModal(title, message, onConfirm) {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
        
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
                    <button type="button" id="cancel-btn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                    <button type="button" id="confirm-btn" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">Confirm</button>
                </div>
            </div>
        `;
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        document.getElementById('cancel-btn').addEventListener('click', () => document.body.removeChild(overlay));
        document.getElementById('confirm-btn').addEventListener('click', () => {
            document.body.removeChild(overlay);
            onConfirm();
        });
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) document.body.removeChild(overlay);
        });
    }

    // Toast notification function
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-medium transition-all duration-300 transform translate-x-full opacity-0 ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        
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
        
        document.body.appendChild(notification);
        setTimeout(() => notification.classList.remove('translate-x-full', 'opacity-0'), 100);
        
        const duration = type === 'success' ? 3000 : 4000;
        setTimeout(() => {
            notification.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                if (document.body.contains(notification)) document.body.removeChild(notification);
            }, 300);
        }, duration);
    }
</script>
