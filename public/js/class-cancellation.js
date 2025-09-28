// Class Cancellation and Restoration Functions

// Function to cancel a class
function cancelClass(classId, className, schoolName) {
    if (confirm(`Are you sure you want to cancel ${className} at ${schoolName}?\n\nThis will:\n• Mark the class as cancelled\n• Preserve tutor assignments for potential restoration`)) {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('CSRF token not found');
            showNotification('Security token not found. Please refresh the page.', 'error');
            return;
        }

        fetch(`/schedules/cancel-class/${classId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification('Class cancelled successfully', 'success');
                // Reload the page to reflect changes
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification(data.message || 'Failed to cancel class', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while cancelling the class', 'error');
        });
    }
}

// Function to show notifications
function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium transition-all duration-300 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.textContent = message;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}