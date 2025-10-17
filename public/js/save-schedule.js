// save-schedule.js
// Function to save schedule with specified status
function saveScheduleAs(status, date) {
    // Show custom confirmation modal for final save
    showSaveFinalConfirmation(date, () => {
        performSaveScheduleAs(status, date);
    });
}

function performSaveScheduleAs(status, date) {

    // Show loading state
    const buttons = document.querySelectorAll('button');
    buttons.forEach(btn => {
        if (btn.textContent.includes('Save as')) {
            btn.disabled = true;
            btn.textContent = 'Saving...';
        }
    });

    fetch('/schedules/save-schedule', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            date: date,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification(data.message, 'success');
            // If saving as final, redirect to schedule history after delay
            if (status === 'final' || status === 'finalized') {
                setTimeout(() => {
                    window.location.href = '/scheduling?tab=history';
                }, 2500);
            } else {
                // Reload the page after delay to show the updated status
                setTimeout(() => {
                    location.reload();
                }, 2500);
            }
        } else {
            showNotification(data.message || 'Failed to save schedule', 'error');
            // Reset buttons
            resetSaveButtons();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while saving the schedule', 'error');
        // Reset buttons
        resetSaveButtons();
    });
}

// Helper function to reset save buttons
function resetSaveButtons() {
    const buttons = document.querySelectorAll('button');
    buttons.forEach(btn => {
        if (btn.textContent.includes('Saving')) {
            btn.disabled = false;
            btn.textContent = 'Save as Final';
        }
    });
}

// Save as Final confirmation modal functionality
let saveFinalCallback = null;

function showSaveFinalConfirmation(date, callback) {
    const modal = document.getElementById('saveFinalConfirmationModal');
    const messageElement = document.getElementById('saveFinalMessage');
    const dateElement = document.getElementById('saveFinalDate');
    
    if (messageElement) {
        messageElement.textContent = `Are you sure you want to save all classes for ${date} as FINAL?`;
    }
    
    if (dateElement) {
        dateElement.textContent = date;
    }
    
    saveFinalCallback = callback;
    
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function hideSaveFinalConfirmation() {
    const modal = document.getElementById('saveFinalConfirmationModal');
    if (modal) {
        modal.classList.add('hidden');
    }
    // Don't clear the callback immediately - let the callback execute first
    setTimeout(() => {
        saveFinalCallback = null;
    }, 100);
}

// Save as Final modal event listeners
document.addEventListener('DOMContentLoaded', function() {
    const closeSaveFinalModal = document.getElementById('closeSaveFinalModal');
    const cancelSaveFinal = document.getElementById('cancelSaveFinal');
    const confirmSaveFinal = document.getElementById('confirmSaveFinal');
    const saveFinalModal = document.getElementById('saveFinalConfirmationModal');

    if (closeSaveFinalModal) {
        closeSaveFinalModal.addEventListener('click', hideSaveFinalConfirmation);
    }

    if (cancelSaveFinal) {
        cancelSaveFinal.addEventListener('click', hideSaveFinalConfirmation);
    }

    if (confirmSaveFinal) {
        confirmSaveFinal.addEventListener('click', () => {
            if (saveFinalCallback) {
                // Execute callback first, then hide modal
                saveFinalCallback();
                hideSaveFinalConfirmation();
            } else {
                console.error('No save final callback found');
                hideSaveFinalConfirmation();
            }
        });
    } else {
        console.error('confirmSaveFinal button not found');
    }

    // Close modal when clicking outside
    if (saveFinalModal) {
        saveFinalModal.addEventListener('click', (e) => {
            if (e.target === saveFinalModal) {
                hideSaveFinalConfirmation();
            }
        });
    }
});

// Enhanced toast notification function (consistent with other parts of the application)
if (typeof showNotification === 'undefined') {
    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-medium transition-all duration-300 transform translate-x-full opacity-0 ${
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
}
