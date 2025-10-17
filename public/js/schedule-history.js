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
        exportButtonText.textContent = `Export File (${selectedCount} selected)`;
    } else {
        exportButton.disabled = true;
        exportButton.classList.add('opacity-50', 'cursor-not-allowed');
        exportButtonText.textContent = 'Export File (0 selected)';
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
        alert('Please select at least one schedule to export.');
        return;
    }
    // Create form to submit selected dates
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
    // Add selected dates
    selectedCheckboxes.forEach(checkbox => {
        const dateInput = document.createElement('input');
        dateInput.type = 'hidden';
        dateInput.name = 'dates[]';
        dateInput.value = checkbox.value;
        form.appendChild(dateInput);
    });
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

document.addEventListener('DOMContentLoaded', function() {
    updateExportButton();
});

// Function to change rows per page
function changeRowsPerPage(perPage) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('per_page', perPage);
    currentUrl.searchParams.delete('page'); // Reset to first page when changing per_page
    window.location.href = currentUrl.toString();
}
