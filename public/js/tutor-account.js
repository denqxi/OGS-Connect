document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
});

function setupEventListeners() {
    // Password change form
    const updatePasswordBtn = document.getElementById('updatePasswordBtn');
    if (updatePasswordBtn) {
        updatePasswordBtn.addEventListener('click', handlePasswordChange);
    }

    // Password match indicator
    const newPasswordField = document.getElementById('newPassword');
    const confirmPasswordField = document.getElementById('confirmPassword');
    
    if (newPasswordField && confirmPasswordField) {
        newPasswordField.addEventListener('input', checkPasswordMatch);
        confirmPasswordField.addEventListener('input', checkPasswordMatch);
        
        // Clear indicator when fields are cleared
        newPasswordField.addEventListener('input', function() {
            if (this.value === '') {
                clearPasswordMatchIndicator();
            }
        });
        confirmPasswordField.addEventListener('input', function() {
            if (this.value === '') {
                clearPasswordMatchIndicator();
            }
        });
    }

    // Password toggle functionality
    setupPasswordToggles();
}

async function handlePasswordChange() {
    const currentPassword = document.getElementById('currentPassword').value.trim();
    const newPassword = document.getElementById('newPassword').value.trim();
    const confirmPassword = document.getElementById('confirmPassword').value.trim();

    // Basic validation
    if (!currentPassword || !newPassword || !confirmPassword) {
        showPasswordMessage('Please fill in all password fields.', 'error');
        showNotification('Please fill in all password fields.', 'error');
        return;
    }

    if (newPassword !== confirmPassword) {
        showPasswordMessage('New passwords do not match.', 'error');
        showNotification('New passwords do not match.', 'error');
        return;
    }

    if (newPassword.length < 8) {
        showPasswordMessage('New password must be at least 8 characters long.', 'error');
        showNotification('New password must be at least 8 characters long.', 'error');
        return;
    }

    // Confirm password change
    const confirmed = await showConfirmationModal(
        'Change Password',
        'Are you sure you want to change your password? You will need to use the new password for future logins.',
        'Change Password',
        'Cancel'
    );

    if (!confirmed) return;

    await proceedWithPasswordChange();
}

async function proceedWithPasswordChange() {
    const updateBtn = document.getElementById('updatePasswordBtn');
    const originalText = updateBtn.textContent;

    try {
        // Show loading state
        updateBtn.disabled = true;
        updateBtn.textContent = 'Updating...';

        const response = await fetch('/tutor/change-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                current_password: document.getElementById('currentPassword').value,
                new_password: document.getElementById('newPassword').value,
                new_password_confirmation: document.getElementById('confirmPassword').value
            })
        });

        const result = await response.json();

        if (result.success) {
            showPasswordMessage('Password updated successfully!', 'success');
            showNotification('Password updated successfully!', 'success');
            
            // Clear the form
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
            clearPasswordMatchIndicator();
        } else {
            showPasswordMessage(result.message || 'Failed to update password.', 'error');
            showNotification(result.message || 'Failed to update password.', 'error');
        }
    } catch (error) {
        console.error('Error updating password:', error);
        showPasswordMessage('An error occurred while updating password.', 'error');
        showNotification('An error occurred while updating password.', 'error');
    } finally {
        // Restore button state
        updateBtn.disabled = false;
        updateBtn.textContent = originalText;
    }
}

function checkPasswordMatch() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const indicator = document.getElementById('passwordMatchIndicator');

    if (!indicator) return;

    if (newPassword && confirmPassword) {
        if (newPassword === confirmPassword) {
            indicator.textContent = 'Passwords match ✓';
            indicator.className = 'text-sm text-green-600 dark:text-green-400 mt-1';
        } else {
            indicator.textContent = 'Passwords do not match ✗';
            indicator.className = 'text-sm text-red-600 dark:text-red-400 mt-1';
        }
    } else {
        clearPasswordMatchIndicator();
    }
}

function clearPasswordMatchIndicator() {
    const indicator = document.getElementById('passwordMatchIndicator');
    if (indicator) {
        indicator.textContent = '';
        indicator.className = '';
    }
}

function setupPasswordToggles() {
    const toggles = document.querySelectorAll('.password-toggle');
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                targetInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
}

function showPasswordMessage(message, type) {
    const messageContainer = document.getElementById('passwordMessageContainer');
    if (!messageContainer) return;

    const className = type === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
    
    messageContainer.innerHTML = `<p class="${className} text-sm mt-2">${message}</p>`;

    // Clear message after 5 seconds
    setTimeout(() => {
        messageContainer.innerHTML = '';
    }, 5000);
}

// Toast notification function
function showNotification(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium transition-all duration-300 transform translate-x-full`;
    
    // Set background color based on type
    switch (type) {
        case 'success':
            toast.classList.add('bg-green-500');
            break;
        case 'error':
            toast.classList.add('bg-red-500');
            break;
        case 'warning':
            toast.classList.add('bg-yellow-500');
            break;
        default:
            toast.classList.add('bg-blue-500');
    }
    
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Modal confirmation function
function showConfirmationModal(title, message, confirmText = 'Confirm', cancelText = 'Cancel') {
    return new Promise((resolve) => {
        // Create modal elements
        const modalOverlay = document.createElement('div');
        modalOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
        
        const modalContent = document.createElement('div');
        modalContent.className = 'bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6 shadow-xl';
        
        modalContent.innerHTML = `
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">${title}</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-6">${message}</p>
            <div class="flex justify-end space-x-3">
                <button id="modalCancel" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white transition-colors">
                    ${cancelText}
                </button>
                <button id="modalConfirm" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                    ${confirmText}
                </button>
            </div>
        `;
        
        modalOverlay.appendChild(modalContent);
        document.body.appendChild(modalOverlay);
        
        // Handle confirm
        modalContent.querySelector('#modalConfirm').addEventListener('click', () => {
            document.body.removeChild(modalOverlay);
            resolve(true);
        });
        
        // Handle cancel
        modalContent.querySelector('#modalCancel').addEventListener('click', () => {
            document.body.removeChild(modalOverlay);
            resolve(false);
        });
        
        // Handle overlay click
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                document.body.removeChild(modalOverlay);
                resolve(false);
            }
        });
    });
}