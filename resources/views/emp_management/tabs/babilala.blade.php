<!-- Page Title -->
<div class="p-4 border-b border-gray-200">
    <h2 class="text-xl font-bold text-[#A78BFA]">BabiLala Account</h2>
</div>

<!-- Search Filters -->
<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
    </div>
    <form method="GET" action="{{ route('employees.index') }}" class="flex justify-between items-center space-x-4">
        <input type="hidden" name="tab" value="babilala">
        
        <!-- Left side -->
        <div class="flex items-center space-x-4 flex-1 max-w-lg">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" placeholder="search name..." value="{{ request('search') }}"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm 
              focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
              focus:ring-0 focus:shadow-xl">
            </div>
            <select name="status" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                <option value="">Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">Available at:</span>
            <select name="time_slot" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                <option value="">Time Range</option>
                <option value="7:00-8:00" {{ request('time_slot') == '7:00-8:00' ? 'selected' : '' }}>7:00-8:00</option>
                <option value="8:00-9:00" {{ request('time_slot') == '8:00-9:00' ? 'selected' : '' }}>8:00-9:00</option>
                <option value="9:00-10:00" {{ request('time_slot') == '9:00-10:00' ? 'selected' : '' }}>9:00-10:00</option>
                <option value="10:00-11:00" {{ request('time_slot') == '10:00-11:00' ? 'selected' : '' }}>10:00-11:00</option>
                <option value="11:00-12:00" {{ request('time_slot') == '11:00-12:00' ? 'selected' : '' }}>11:00-12:00</option>
                <option value="12:00-13:00" {{ request('time_slot') == '12:00-13:00' ? 'selected' : '' }}>12:00-13:00</option>
                <option value="13:00-14:00" {{ request('time_slot') == '13:00-14:00' ? 'selected' : '' }}>13:00-14:00</option>
                <option value="14:00-15:00" {{ request('time_slot') == '14:00-15:00' ? 'selected' : '' }}>14:00-15:00</option>
                <option value="15:00-16:00" {{ request('time_slot') == '15:00-16:00' ? 'selected' : '' }}>15:00-16:00</option>
                <option value="16:00-17:00" {{ request('time_slot') == '16:00-17:00' ? 'selected' : '' }}>16:00-17:00</option>
                <option value="17:00-18:00" {{ request('time_slot') == '17:00-18:00' ? 'selected' : '' }}>17:00-18:00</option>
                <option value="18:00-19:00" {{ request('time_slot') == '18:00-19:00' ? 'selected' : '' }}>18:00-19:00</option>
                <option value="19:00-20:00" {{ request('time_slot') == '19:00-20:00' ? 'selected' : '' }}>19:00-20:00</option>
                <option value="20:00-21:00" {{ request('time_slot') == '20:00-21:00' ? 'selected' : '' }}>20:00-21:00</option>
                <option value="21:00-22:00" {{ request('time_slot') == '21:00-22:00' ? 'selected' : '' }}>21:00-22:00</option>
            </select>
            <select name="day" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                <option value="">Day</option>
                <option value="monday" {{ request('day') == 'monday' ? 'selected' : '' }}>Monday</option>
                <option value="tuesday" {{ request('day') == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                <option value="wednesday" {{ request('day') == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                <option value="thursday" {{ request('day') == 'thursday' ? 'selected' : '' }}>Thursday</option>
                <option value="friday" {{ request('day') == 'friday' ? 'selected' : '' }}>Friday</option>
                <option value="saturday" {{ request('day') == 'saturday' ? 'selected' : '' }}>Saturday</option>
                <option value="sunday" {{ request('day') == 'sunday' ? 'selected' : '' }}>Sunday</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-[#A78BFA] text-white rounded-md text-sm hover:bg-[#A78BFA]/80">
                Search
            </button>
        </div>
    </form>
</div>

<!-- Employee Table -->
<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="babilalaTableBody">
            @forelse($tutors as $tutor)
                @php
                    $babilalaAccount = $tutor->accounts->where('account_name', 'Babilala')->first();
                    $paymentInfo = $tutor->paymentInformation;
                    $tutorDetails = $tutor->tutorDetails;
                @endphp
                <tr class="hover:bg-gray-50 babilala-row" data-searchable="{{ strtolower(($tutor->full_name ?? '') . ' ' . ($tutor->email ?? '') . ' ' . ($tutor->phone_number ?? '')) }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $tutor->full_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 underline">
                        <a href="mailto:{{ $tutor->email ?? '' }}">{{ $tutor->email ?? 'N/A' }}</a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($babilalaAccount)
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-700">{{ $babilalaAccount->getFormattedAvailableTimeAttribute() }}</span>
                                <span class="text-xs text-purple-600 font-medium">(Babilala Account)</span>
                            </div>
                        @else
                            <span class="text-red-500 text-sm">No Babilala availability</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $tutor->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($tutor->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex space-x-2">
                            <button onclick="openEmployeeModal('tutor', '{{ $tutor->tutorID }}')" 
                                    class="w-8 h-8 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 inline-flex items-center justify-center transition-colors"
                                    title="View Details">
                                <i class="fas fa-search text-xs"></i>
                            </button>
                            @if($tutor->status === 'active')
                                <button onclick="toggleTutorStatus('{{ $tutor->tutorID }}', 'inactive')" class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition-colors text-xs font-medium" title="Deactivate">
                                    Deactivate
                                </button>
                            @else
                                <button onclick="toggleTutorStatus('{{ $tutor->tutorID }}', 'active')" class="px-3 py-1 bg-green-100 text-green-600 rounded hover:bg-green-200 transition-colors text-xs font-medium" title="Activate">
                                    Activate
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                        No tutors found with Babilala accounts.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>


<!-- Pagination -->
@if(isset($tutors))
    @include('emp_management.partials.compact-pagination', ['data' => $tutors, 'tab' => 'babilala'])
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
                button.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(`An unexpected error occurred while updating ${tutorName}'s status. Please check your internet connection and try again.`, 'error');
            
            // Restore button state
            button.disabled = false;
            button.textContent = originalText;
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
