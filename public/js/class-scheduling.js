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
    
    if (!confirm(`This will automatically assign tutors for ${dayName}, ${date}. Continue?`)) {
        return;
    }

    // Show loading state
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
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
            
            alert(message);
            location.reload(); // Refresh to show new assignments
        } else {
            alert('Auto-assignment failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Auto-assignment failed. Please try again.');
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

// Keep your existing autoAssignTutors function for bulk assignment
function autoAssignTutors() {
    if (!confirm('This will automatically assign tutors to all unassigned/partially assigned schedules. Continue?')) {
        return;
    }

    // Show loading state
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
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
            alert(data.message);
            location.reload(); // Refresh to show new assignments
        } else {
            alert('Auto-assignment failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Auto-assignment failed. Please try again.');
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
    if (!confirm('Are you sure you want to auto-assign tutors for all classes on ' + date + '?')) {
        return;
    }

    const button = event.target;
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
            alert(`Auto-assignment completed! ${data.assigned} tutors assigned to classes.`);
            location.reload(); // Refresh to show new assignments
        } else {
            alert('Auto-assignment failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Auto-assignment failed. Please try again.');
    })
    .finally(() => {
        // Restore button
        button.innerHTML = originalText;
        button.disabled = false;
    });
}