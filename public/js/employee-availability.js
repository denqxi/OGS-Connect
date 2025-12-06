// Tutor total results will be set by the Blade template
    
function handleTutorFilterChange(changed) {
    // Allow day and time filters to work together, but clear them when status changes
    if (changed === 'status') {
        // When status changes, clear day and time filters
        document.getElementById('filterTimeSlot').value = '';
        document.getElementById('filterDay').value = '';
        // Also clear the time pickers
        document.getElementById('startTime').value = '';
        document.getElementById('endTime').value = '';
    } else if (changed === 'day') {
        // When day changes, preserve the current time picker values
        const startTime = document.getElementById('startTime').value;
        const endTime = document.getElementById('endTime').value;
        const timeSlotInput = document.getElementById('filterTimeSlot');
        
        if (startTime && endTime) {
            const timeRange = startTime + '-' + endTime;
            timeSlotInput.value = timeRange;
        }
    }
    
    // Update clear button visibility
    updateClearButtonVisibility();
    
    // DISABLED AUTO-SUBMIT - User must click Apply button manually
    // document.getElementById('tutorFilterForm').submit();
}

// Debounce timer for time range updates
let timeRangeTimeout;

// Function to show/hide clear button based on day and time filters
function updateClearButtonVisibility() {
    const clearSearchBtn = document.getElementById('clearSearch');
    const dayFilter = document.getElementById('filterDay');
    const startTime = document.getElementById('startTime');
    const endTime = document.getElementById('endTime');
    
    if (clearSearchBtn) {
        // Show clear button only if day or time filters are applied
        const hasDayFilter = dayFilter && dayFilter.value;
        const hasTimeFilter = startTime && endTime && startTime.value && endTime.value;
        
        if (hasDayFilter || hasTimeFilter) {
            clearSearchBtn.classList.remove('hidden');
        } else {
            clearSearchBtn.classList.add('hidden');
        }
    }
}

// Function to update end time options based on selected start time
function updateEndTimeOptions() {
    const startTimeSelect = document.getElementById('startTime');
    const endTimeSelect = document.getElementById('endTime');
    const selectedStartTime = startTimeSelect.value;
    
    if (!selectedStartTime) {
        // If no start time selected, show all options
        const options = endTimeSelect.querySelectorAll('option');
        options.forEach(option => {
            option.disabled = false;
            option.style.display = '';
        });
        return;
    }
    
    // Convert start time to minutes for comparison
    const [startHour, startMinute] = selectedStartTime.split(':').map(Number);
    const startTimeMinutes = startHour * 60 + startMinute;
    
    // Update end time options
    const options = endTimeSelect.querySelectorAll('option');
    options.forEach(option => {
        if (option.value === '') {
            // Keep the empty option enabled
            option.disabled = false;
            option.style.display = '';
            return;
        }
        
        // Convert option time to minutes
        const [optionHour, optionMinute] = option.value.split(':').map(Number);
        const optionTimeMinutes = optionHour * 60 + optionMinute;
        
        // Disable options that are before or equal to start time
        if (optionTimeMinutes <= startTimeMinutes) {
            option.disabled = true;
            option.style.display = 'none';
        } else {
            option.disabled = false;
            option.style.display = '';
        }
    });
    
    // If current end time is now invalid, clear it
    if (endTimeSelect.value && endTimeSelect.value !== '') {
        const [endHour, endMinute] = endTimeSelect.value.split(':').map(Number);
        const endTimeMinutes = endHour * 60 + endMinute;
        
        if (endTimeMinutes <= startTimeMinutes) {
            endTimeSelect.value = '';
        }
    }
}

function updateTimeRange() {
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    const timeSlotInput = document.getElementById('filterTimeSlot');
    const dayFilter = document.getElementById('filterDay');
    
    // Clear any existing timeout
    clearTimeout(timeRangeTimeout);
    
    if (startTime && endTime) {
        // Convert time format from HH:MM to HH:MM-HH:MM
        const timeRange = startTime + '-' + endTime;
        timeSlotInput.value = timeRange;
        
        // Update clear button visibility
        updateClearButtonVisibility();
        
        // DISABLED AUTO-SUBMIT - User must click Apply button manually
        // if (dayFilter.value) {
        //     timeRangeTimeout = setTimeout(() => {
        //         document.getElementById('tutorFilterForm').submit();
        //     }, 1000);
        // }
    } else if (!startTime && !endTime) {
        // Clear the time filter if both are empty
        timeSlotInput.value = '';
        
        // Update clear button visibility
        updateClearButtonVisibility();
        
        // DISABLED AUTO-SUBMIT - User must click Apply button manually
        // if (dayFilter.value) {
        //     timeRangeTimeout = setTimeout(() => {
        //         document.getElementById('tutorFilterForm').submit();
        //     }, 1000);
        // }
    }
}

// Initialize time pickers with existing values on page load
function initializeTimePickers() {
    const timeSlot = document.getElementById('filterTimeSlot').value;
    if (timeSlot && timeSlot.includes('-')) {
        const [start, end] = timeSlot.split('-');
        document.getElementById('startTime').value = start;
        document.getElementById('endTime').value = end;
    }
}

// Handle search input - submit form on Enter or when user stops typing
document.addEventListener('DOMContentLoaded', function() {
    // Initialize time pickers with existing values
    initializeTimePickers();
    
    // Initialize end time options based on start time
    updateEndTimeOptions();
    
    // Initialize clear button visibility
    updateClearButtonVisibility();
    
    const searchInput = document.getElementById('tutorSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const form = document.getElementById('tutorFilterForm');
    let searchTimeout;

    if (searchInput) {
        // DISABLED AUTO-SUBMIT on typing - User must press Enter or click Apply
        // searchInput.addEventListener('input', function() {
        //     const query = this.value.trim();
        //     if (clearSearchBtn) clearSearchBtn.classList.add('hidden');
        //     clearTimeout(searchTimeout);
        //     searchTimeout = setTimeout(() => {
        //         form.submit();
        //     }, 800);
        // });

        // Handle Enter key only
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                form.submit();
            }
        });
    }

    // Clear button functionality - clear only day and time filters
        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function() {
            // Clear day and time filters only
            document.getElementById('filterDay').value = '';
            document.getElementById('startTime').value = '';
            document.getElementById('endTime').value = '';
            document.getElementById('filterTimeSlot').value = '';
            
            // Hide clear button
                clearSearchBtn.classList.add('hidden');
            
            // Submit form to apply changes
                form.submit();
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
    const tutorName = tutorRow ? tutorRow.querySelector('td:first-child').textContent.trim() : 'this tutor';
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