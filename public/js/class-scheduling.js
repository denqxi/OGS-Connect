/**
 * Class Scheduling JavaScript
 * Handles search, filter, and table functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeClassScheduling();
});

function initializeClassScheduling() {
    // Get DOM elements
    const searchInput = document.getElementById('realTimeSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    const tableRows = document.querySelectorAll('.table-row');
    const noResultsMessage = document.getElementById('noSearchResults');
    const noResultsRow = document.getElementById('noResultsRow');
    const resultCount = document.getElementById('resultCount');
    const paginationSection = document.getElementById('paginationSection');
    const filterType = document.getElementById('filterType');
    const dateFilter = document.getElementById('dateFilter');
    const dayFilter = document.getElementById('dayFilter');

    let searchTimeout;

    // Initialize filter functionality
    if (filterType && dateFilter && dayFilter) {
        filterType.addEventListener('change', function() {
            const selectedType = this.value;
            
            // Hide both filters first
            dateFilter.classList.add('hidden');
            dayFilter.classList.add('hidden');
            
            // Clear values
            dateFilter.value = '';
            dayFilter.value = '';
            
            // Show the selected filter
            if (selectedType === 'date') {
                dateFilter.classList.remove('hidden');
            } else if (selectedType === 'day') {
                dayFilter.classList.remove('hidden');
            }
        });
    }

    // Search functionality is handled by class-scheduling-search.js
    // This prevents conflicts between client-side and server-side search

    // Search function removed - handled by class-scheduling-search.js

    // Highlighting functions removed - handled by class-scheduling-search.js

    // URL parameter initialization removed - handled by class-scheduling-search.js
}

// Add these functions to your class-scheduling.js file

function autoAssignSpecificSchedule(date, day) {
    const dayName = getDayName(day);
    
    // Show custom confirmation modal
    showAutoAssignConfirmation(`This will automatically assign tutors for ${dayName}, ${date}. Continue?`, () => {
        performAutoAssignSpecificSchedule(date, day);
    });
}

function performAutoAssignSpecificSchedule(date, day) {
    // Find the original auto-assign button
    let button = document.querySelector(`button[onclick*="autoAssignSpecificSchedule('${date}', '${day}')"]`);
    if (!button) {
        // Fallback: find any button that might be the auto-assign button
        const buttons = document.querySelectorAll('button');
        const autoAssignButton = Array.from(buttons).find(btn => 
            btn.textContent.includes('Auto Assign') || 
            btn.textContent.includes('auto-assign') ||
            (btn.onclick && btn.onclick.toString().includes('autoAssignSpecificSchedule'))
        );
        if (!autoAssignButton) {
            showNotification('Auto-assign button not found', 'error');
            return;
        }
        button = autoAssignButton;
    }
    
    const originalHTML = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';
    button.disabled = true;

    // Make the request
    fetch(`/schedules/auto-assign/${date}/${day}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show detailed success message
            let message = data.message;
            if (data.data && data.data.summary) {
                message += `\n\nSummary:`;
                message += `\n• Classes processed: ${data.data.summary.total_classes_processed}`;
                message += `\n• Tutors assigned: ${data.data.summary.total_tutors_assigned}`;
                message += `\n• Classes with new assignments: ${data.data.summary.classes_with_assignments}`;
            }
            
            showNotification(message, 'success');
            location.reload(); // Refresh to show new assignments
        } else {
            showNotification('Auto-assignment failed: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Auto-assignment failed. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button
        button.innerHTML = originalHTML;
        button.disabled = false;
    });
}

function getDayName(day) {
    const dayMap = {
        'mon': 'Monday',
        'tue': 'Tuesday', 
        'wed': 'Wednesday',
        'thur': 'Thursday',
        'fri': 'Friday',
        'sat': 'Saturday',
        'sun': 'Sunday'
    };
    return dayMap[day] || day;
}

// Auto-assign confirmation modal functionality
let autoAssignCallback = null;

function showAutoAssignConfirmation(message, callback) {
    const modal = document.getElementById('autoAssignConfirmationModal');
    const messageElement = document.getElementById('autoAssignMessage');
    
    if (messageElement) {
        messageElement.textContent = message;
    }
    
    autoAssignCallback = callback;
    
    if (modal) {
        modal.classList.remove('hidden');
    } else {
        console.error('Auto-assign confirmation modal not found');
    }
}

function hideAutoAssignConfirmation() {
    const modal = document.getElementById('autoAssignConfirmationModal');
    if (modal) {
        modal.classList.add('hidden');
    }
    // Don't clear the callback immediately - let the callback execute first
    setTimeout(() => {
        autoAssignCallback = null;
    }, 100);
}

// Auto-assign modal event listeners
document.addEventListener('DOMContentLoaded', function() {
    const closeAutoAssignModal = document.getElementById('closeAutoAssignModal');
    const cancelAutoAssign = document.getElementById('cancelAutoAssign');
    const confirmAutoAssign = document.getElementById('confirmAutoAssign');
    const autoAssignModal = document.getElementById('autoAssignConfirmationModal');

    if (closeAutoAssignModal) {
        closeAutoAssignModal.addEventListener('click', hideAutoAssignConfirmation);
    }

    if (cancelAutoAssign) {
        cancelAutoAssign.addEventListener('click', hideAutoAssignConfirmation);
    }

    if (confirmAutoAssign) {
        confirmAutoAssign.addEventListener('click', () => {
            if (autoAssignCallback) {
                // Execute callback first, then hide modal
                autoAssignCallback();
                hideAutoAssignConfirmation();
            } else {
                console.error('No auto-assign callback found');
                hideAutoAssignConfirmation();
            }
        });
    } else {
        console.error('confirmAutoAssign button not found');
    }

    // Close modal when clicking outside
    if (autoAssignModal) {
        autoAssignModal.addEventListener('click', (e) => {
            if (e.target === autoAssignModal) {
                hideAutoAssignConfirmation();
            }
        });
    }
});

// Keep your existing autoAssignTutors function for bulk assignment
function autoAssignTutors() {
    // Show custom confirmation modal
    showAutoAssignConfirmation('This will automatically assign tutors to all unassigned/partially assigned schedules. Continue?', () => {
        performAutoAssignTutors();
    });
}

function performAutoAssignTutors() {
    // Find the original auto-assign button
    let button = document.querySelector('button[onclick*="autoAssignTutors()"]');
    if (!button) {
        // Fallback: find any button that might be the auto-assign button
        const buttons = document.querySelectorAll('button');
        const autoAssignButton = Array.from(buttons).find(btn => 
            btn.textContent.includes('Auto Assign') || 
            btn.textContent.includes('auto-assign') ||
            (btn.onclick && btn.onclick.toString().includes('autoAssignTutors'))
        );
        if (!autoAssignButton) {
            showNotification('Auto-assign button not found', 'error');
            return;
        }
        button = autoAssignButton;
    }
    
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Assigning...</span>';
    button.disabled = true;

    // Get current filter values
    const date = document.querySelector('select[name="date"]').value;
    const day = document.querySelector('select[name="day"]').value;

    fetch('/schedules/auto-assign', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ date, day })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            location.reload(); // Refresh to show new assignments
        } else {
            showNotification('Auto-assignment failed: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Auto-assignment failed. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

/**
 * Auto-assign tutors for a specific day
 */
function autoAssignForThisDay(date) {
    // Show custom confirmation modal
    showAutoAssignConfirmation('Are you sure you want to auto-assign tutors for all classes on ' + date + '?', () => {
        performAutoAssignForThisDay(date);
    });
}

function performAutoAssignForThisDay(date) {
    // Find the original auto-assign button - try multiple selectors
    let button = document.querySelector(`button[onclick*="autoAssignForThisDay('${date}')"]`);
    
    if (!button) {
        // Try finding by text content
        const buttons = document.querySelectorAll('button');
        button = Array.from(buttons).find(btn => 
            btn.textContent.includes('Auto Assign All') || 
            btn.textContent.includes('Auto Assign')
        );
    }
    
    if (!button) {
        console.error('Auto-assign button not found for date:', date);
        showNotification('Auto-assign button not found', 'error');
        return;
    }
    
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Assigning...';
    button.disabled = true;

    // Make AJAX request to auto-assign for this specific date
    fetch(`/schedules/auto-assign/${date}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Auto-assignment completed! ${data.assigned} tutors assigned to classes.`, 'success');
            location.reload(); // Refresh to show new assignments
        } else {
            showNotification('Auto-assignment failed: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Auto-assignment error:', error);
        showNotification('Auto-assignment failed. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Function to change rows per page
function changeRowsPerPage(perPage) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('per_page', perPage);
    currentUrl.searchParams.delete('page'); // Reset to first page when changing per_page
    window.location.href = currentUrl.toString();
}