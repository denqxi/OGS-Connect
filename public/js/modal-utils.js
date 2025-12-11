/**
 * Shared Modal Utilities
 * Provides consistent modal dialogs across the application
 */

/**
 * Show a notification modal
 * @param {string} message - The message to display
 * @param {string} type - Type: 'success', 'error', 'warning', 'info'
 */
function showNotificationModal(message, type = 'info') {
    // Remove existing modal if any
    const existingModal = document.getElementById('notificationModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Set colors and icons based on type
    let iconClass, iconBg, buttonColor, titleText, borderColor;
    switch (type) {
        case 'success':
            iconClass = 'fas fa-check-circle';
            iconBg = 'bg-green-100 dark:bg-green-900/30';
            buttonColor = 'bg-green-600 hover:bg-green-700 focus:ring-green-500';
            titleText = 'Success';
            borderColor = 'border-green-500';
            break;
        case 'error':
            iconClass = 'fas fa-times-circle';
            iconBg = 'bg-red-100 dark:bg-red-900/30';
            buttonColor = 'bg-red-600 hover:bg-red-700 focus:ring-red-500';
            titleText = 'Error';
            borderColor = 'border-red-500';
            break;
        case 'warning':
            iconClass = 'fas fa-exclamation-triangle';
            iconBg = 'bg-yellow-100 dark:bg-yellow-900/30';
            buttonColor = 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500';
            titleText = 'Warning';
            borderColor = 'border-yellow-500';
            break;
        default:
            iconClass = 'fas fa-info-circle';
            iconBg = 'bg-blue-100 dark:bg-blue-900/30';
            buttonColor = 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500';
            titleText = 'Information';
            borderColor = 'border-blue-500';
    }

    // Create modal element
    const modal = document.createElement('div');
    modal.id = 'notificationModal';
    modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4';
    modal.style.animation = 'fadeIn 0.2s ease-out';

    modal.innerHTML = `
        <style>
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideUp {
                from { transform: translateY(20px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
        </style>
        
        <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-50 backdrop-blur-sm" onclick="closeNotificationModal()"></div>
        
        <div class="relative w-full max-w-md transform transition-all" style="animation: slideUp 0.3s ease-out">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border-t-4 ${borderColor} overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <div class="${iconBg} rounded-full p-3 flex-shrink-0">
                            <i class="${iconClass} text-2xl ${type === 'success' ? 'text-green-600 dark:text-green-400' : type === 'error' ? 'text-red-600 dark:text-red-400' : type === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : 'text-blue-600 dark:text-blue-400'}"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">${titleText}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">${message}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 flex justify-end">
                    <button onclick="closeNotificationModal()" class="px-8 py-2.5 text-sm font-medium text-white ${buttonColor} rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 shadow-sm hover:shadow-md">
                        OK
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
}

/**
 * Close the notification modal
 */
function closeNotificationModal() {
    const modal = document.getElementById('notificationModal');
    if (modal) {
        modal.style.animation = 'fadeOut 0.2s ease-in';
        setTimeout(() => modal.remove(), 200);
    }
}

/**
 * Show a confirmation modal
 * @param {string} message - The confirmation message
 * @param {function} onConfirm - Callback when confirmed
 * @param {function} onCancel - Optional callback when cancelled
 * @param {string} confirmText - Text for confirm button (default: "Confirm")
 * @param {string} cancelText - Text for cancel button (default: "Cancel")
 */
function showConfirmationModal(message, onConfirm, onCancel = null, confirmText = 'Confirm', cancelText = 'Cancel') {
    // Remove existing modal if any
    const existingModal = document.getElementById('confirmationModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Create modal element
    const modal = document.createElement('div');
    modal.id = 'confirmationModal';
    modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4';
    modal.style.animation = 'fadeIn 0.2s ease-out';

    modal.innerHTML = `
        <style>
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideUp {
                from { transform: translateY(20px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
        </style>
        
        <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-50 backdrop-blur-sm" onclick="closeConfirmationModal(false)"></div>
        
        <div class="relative w-full max-w-md transform transition-all" style="animation: slideUp 0.3s ease-out">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border-t-4 border-blue-500 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <div class="bg-blue-100 dark:bg-blue-900/30 rounded-full p-3 flex-shrink-0">
                            <i class="fas fa-question-circle text-2xl text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Confirm Action</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">${message}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 flex justify-end space-x-3">
                    <button onclick="closeConfirmationModal(false)" class="px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 shadow-sm">
                        ${cancelText}
                    </button>
                    <button onclick="closeConfirmationModal(true)" class="px-8 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md">
                        ${confirmText}
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Store callbacks
    window._confirmationModalCallbacks = {
        onConfirm: onConfirm,
        onCancel: onCancel
    };
}

/**
 * Close the confirmation modal
 * @param {boolean} confirmed - Whether the action was confirmed
 */
function closeConfirmationModal(confirmed) {
    const modal = document.getElementById('confirmationModal');
    if (modal) {
        modal.style.animation = 'fadeOut 0.2s ease-in';
        setTimeout(() => {
            modal.remove();
            
            // Execute callbacks
            if (window._confirmationModalCallbacks) {
                if (confirmed && window._confirmationModalCallbacks.onConfirm) {
                    window._confirmationModalCallbacks.onConfirm();
                } else if (!confirmed && window._confirmationModalCallbacks.onCancel) {
                    window._confirmationModalCallbacks.onCancel();
                }
                delete window._confirmationModalCallbacks;
            }
        }, 200);
    }
}

// Make functions globally available
window.showNotificationModal = showNotificationModal;
window.closeNotificationModal = closeNotificationModal;
window.showConfirmationModal = showConfirmationModal;
window.closeConfirmationModal = closeConfirmationModal;
