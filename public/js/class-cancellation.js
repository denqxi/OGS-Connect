// Class Cancellation and Restoration Functions

// Function to cancel a class
function cancelClass(classId, className, schoolName) {
    // Show cancellation modal instead of simple confirm
    showCancellationModal(classId, className, schoolName);
}

// Function to show cancellation modal
function showCancellationModal(classId, className, schoolName) {
    // Create modal HTML
    const modalHTML = `
        <div id="cancellationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
                <!-- Header -->
                <div class="flex justify-between items-center bg-red-500 text-white px-4 py-3 rounded-t-lg">
                    <h2 class="text-lg font-bold">Cancel Class</h2>
                    <button id="closeCancellationModal" class="text-white font-bold text-xl hover:text-gray-200">&times;</button>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-gray-700 mb-2">
                            <strong>Class:</strong> ${className}
                        </p>
                        <p class="text-gray-700 mb-4">
                            <strong>School:</strong> ${schoolName}
                        </p>
                        <p class="text-sm text-gray-600 mb-4">
                            This will mark the class as cancelled and preserve tutor assignments for potential restoration.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label for="cancellationReason" class="block text-sm font-medium text-gray-700 mb-2">
                            Reason for cancellation <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="cancellationReason" 
                            rows="3" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Please provide a reason for cancelling this class..."
                            required
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">This information will be recorded for audit purposes.</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end items-center space-x-3 px-6 py-4 border-t">
                    <button id="cancelCancellation" class="px-4 py-2 rounded-full border border-gray-300 text-gray-600 hover:bg-gray-200 transform transition duration-200 hover:scale-105">
                        Cancel
                    </button>
                    <button id="confirmCancellation" class="px-4 py-2 rounded-full bg-red-500 text-white hover:bg-red-600 transform transition duration-200 hover:scale-105">
                        Cancel Class
                    </button>
                </div>
            </div>
        </div>
    `;

    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Get modal elements
    const modal = document.getElementById('cancellationModal');
    const closeBtn = document.getElementById('closeCancellationModal');
    const cancelBtn = document.getElementById('cancelCancellation');
    const confirmBtn = document.getElementById('confirmCancellation');
    const reasonInput = document.getElementById('cancellationReason');

    // Close modal function
    function closeModal() {
        if (modal) {
            modal.remove();
        }
    }

    // Event listeners
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    
    // Close on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Confirm cancellation
    confirmBtn.addEventListener('click', () => {
        const reason = reasonInput.value.trim();
        
        if (!reason) {
            reasonInput.focus();
            reasonInput.classList.add('border-red-500');
            showNotification('Please provide a reason for cancellation', 'error');
            return;
        }

        // Remove error styling
        reasonInput.classList.remove('border-red-500');

        // Disable button and show loading
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Cancelling...';

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('CSRF token not found');
            showNotification('Security token not found. Please refresh the page.', 'error');
            closeModal();
            return;
        }

        fetch(`/schedules/cancel-class/${classId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            },
            body: JSON.stringify({
                cancellation_reason: reason
            })
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
                // Re-enable button
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Cancel Class';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while cancelling the class', 'error');
            // Re-enable button
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Cancel Class';
        });
    });

    // Focus on reason input
    setTimeout(() => {
        reasonInput.focus();
    }, 100);
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