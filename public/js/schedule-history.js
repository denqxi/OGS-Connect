// schedule-history.js
// Handles checkbox selection, export, and filter UI for schedule history

function toggleAllSchedules() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const scheduleCheckboxes = document.querySelectorAll('.schedule-checkbox');
    scheduleCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    updateExportButton();
}

function updateExportButton() {
    const selectedCheckboxes = document.querySelectorAll('.schedule-checkbox:checked');
    const exportButton = document.getElementById('exportButton');
    const exportButtonText = document.getElementById('exportButtonText');
    const selectAllCheckbox = document.getElementById('selectAll');
    const selectedCount = selectedCheckboxes.length;
    const totalCheckboxes = document.querySelectorAll('.schedule-checkbox').length;
    if (selectedCount > 0) {
        exportButton.disabled = false;
        exportButton.classList.remove('opacity-50', 'cursor-not-allowed');
        exportButtonText.textContent = `Export Selected (${selectedCount})`;
    } else {
        exportButton.disabled = true;
        exportButton.classList.add('opacity-50', 'cursor-not-allowed');
        exportButtonText.textContent = 'Export Selected (0)';
    }
    // Update select all checkbox state
    if (selectedCount === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (selectedCount === totalCheckboxes) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
        selectAllCheckbox.checked = false;
    }
}

function exportSelectedSchedules() {
    const selectedCheckboxes = document.querySelectorAll('.schedule-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        showNotificationAlert('⚠️ Please check the boxes next to the schedules you want to export.\n\nTip: Use the checkbox at the top to select all schedules.');
        return;
    }
    
    // Show confirmation modal
    document.getElementById('selectedCount').textContent = selectedCheckboxes.length;
    document.getElementById('exportSelectedModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeExportSelectedModal() {
    document.getElementById('exportSelectedModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function confirmExportSelected() {
    const selectedCheckboxes = document.querySelectorAll('.schedule-checkbox:checked');
    
    // Close modal
    closeExportSelectedModal();
    
    // Create form to submit selected schedule IDs
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = exportSelectedSchedulesRoute;
    form.style.display = 'none';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    // Add selected schedule IDs
    selectedCheckboxes.forEach(checkbox => {
        const scheduleIdInput = document.createElement('input');
        scheduleIdInput.type = 'hidden';
        scheduleIdInput.name = 'schedule_ids[]';
        scheduleIdInput.value = checkbox.value;
        form.appendChild(scheduleIdInput);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function showNotificationAlert(message) {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 z-50 bg-yellow-500 text-white px-6 py-4 rounded-lg shadow-lg max-w-md';
    notification.innerHTML = `
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-xl mr-3"></i>
            <p class="text-sm whitespace-pre-line">${message}</p>
        </div>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s';
        setTimeout(() => document.body.removeChild(notification), 300);
    }, 4000);
}

document.addEventListener('DOMContentLoaded', function() {
    updateExportButton();
});
