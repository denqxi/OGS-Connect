/**
 * Global Modal Helper Functions
 * Replaces alert() calls with modal dialogs
 */

// Create modal container if it doesn't exist
function ensureModalContainer() {
    if (!document.getElementById('globalModalContainer')) {
        const container = document.createElement('div');
        container.id = 'globalModalContainer';
        container.innerHTML = `
            <!-- Success Modal -->
            <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="display: none;">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-center text-gray-900" id="successModalTitle">Success</h3>
                        <p class="mt-2 text-sm text-center text-gray-600" id="successModalMessage"></p>
                        <div class="mt-6">
                            <button onclick="closeSuccessModal()" class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                OK
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Modal -->
            <div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="display: none;">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-center text-gray-900" id="errorModalTitle">Error</h3>
                        <p class="mt-2 text-sm text-center text-gray-600" id="errorModalMessage"></p>
                        <div class="mt-6">
                            <button onclick="closeErrorModal()" class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                OK
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirm Modal -->
            <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="display: none;">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-yellow-100 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-center text-gray-900" id="confirmModalTitle">Confirm Action</h3>
                        <p class="mt-2 text-sm text-center text-gray-600" id="confirmModalMessage"></p>
                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <button onclick="cancelConfirmModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Cancel
                            </button>
                            <button onclick="acceptConfirmModal()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(container);
    }
}

// Initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ensureModalContainer);
} else {
    ensureModalContainer();
}

// Success Modal Functions
function showSuccessModal(message, title = 'Success') {
    ensureModalContainer();
    const modal = document.getElementById('successModal');
    document.getElementById('successModalTitle').textContent = title;
    document.getElementById('successModalMessage').textContent = message;
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
}

function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
}

// Error Modal Functions
function showErrorModal(message, title = 'Error') {
    ensureModalContainer();
    const modal = document.getElementById('errorModal');
    document.getElementById('errorModalTitle').textContent = title;
    document.getElementById('errorModalMessage').textContent = message;
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
}

function closeErrorModal() {
    const modal = document.getElementById('errorModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
}

// Confirm Modal Functions
let confirmCallback = null;

function showConfirmModal(message, title = 'Confirm Action') {
    ensureModalContainer();
    return new Promise((resolve) => {
        const modal = document.getElementById('confirmModal');
        document.getElementById('confirmModalTitle').textContent = title;
        document.getElementById('confirmModalMessage').textContent = message;
        
        confirmCallback = resolve;
        
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
    });
}

function acceptConfirmModal() {
    const modal = document.getElementById('confirmModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    if (confirmCallback) {
        confirmCallback(true);
        confirmCallback = null;
    }
}

function cancelConfirmModal() {
    const modal = document.getElementById('confirmModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    if (confirmCallback) {
        confirmCallback(false);
        confirmCallback = null;
    }
}

// Replace native alert (optional - can be enabled globally)
function replaceNativeAlerts() {
    window.alert = function(message) {
        showErrorModal(message, 'Alert');
    };
    
    window.confirm = function(message) {
        return showConfirmModal(message);
    };
}

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showSuccessModal,
        closeSuccessModal,
        showErrorModal,
        closeErrorModal,
        showConfirmModal,
        acceptConfirmModal,
        cancelConfirmModal,
        replaceNativeAlerts
    };
}
