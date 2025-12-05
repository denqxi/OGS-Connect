@extends('layouts.app')

@section('title', 'OGS Connect')

@section('content')
    <!-- Header -->
    @include('layouts.header', ['pageTitle' => 'Scheduling'])

<style>
    /* Fix dropdown z-index conflicts */
    .searchable-select .absolute {
        z-index: 40 !important;
    }
    
    /* Ensure header dropdown has highest z-index */
    [x-data] .absolute {
        z-index: 60 !important;
    }
    
    /* Fix dropdown positioning */
    .searchable-select {
        position: relative;
    }
    
    /* Prevent dropdown overflow issues */
    .searchable-select .absolute {
        position: absolute !important;
        top: 100% !important;
        left: 0 !important;
        right: 0 !important;
    }
</style>

<script>
    // Reset dropdowns on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Close any open dropdowns
        const dropdowns = document.querySelectorAll('.searchable-select .absolute');
        dropdowns.forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
        
        // Reset search inputs
        const searchInputs = document.querySelectorAll('#addTutorSearch, #backupTutorSearch');
        searchInputs.forEach(input => {
            if (input) {
                input.setAttribute('readonly', 'readonly');
                input.value = '';
            }
        });
        
        // Close any open modals
        const modals = document.querySelectorAll('#editScheduleModal, #uploadModal');
        modals.forEach(modal => {
            modal.classList.add('hidden');
        });
        
        // Restore body scrolling
        document.body.style.overflow = '';
    });
</script>


    <!-- Tab Navigation -->
    <div class="bg-white border-b border-gray-200 px-4 md:px-6 rounded-xl shadow-sm mb-6">
        <nav class="flex overflow-x-auto md:space-x-8 no-scrollbar">
            <!-- Calendar -->
            <a href="{{ route('schedules.index', ['tab' => 'employee']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ request('tab', 'employee') == 'employee' ? 'border-b-2 border-[#0E335D] text-[#0E335D]' : 'text-[#0E335D] hover:text-[#0E335D]/70' }} 
           font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-calendar-alt"></i>
                <span class="hidden sm:inline">Calendar</span>
            </a>

            <!-- Class Scheduling-->
            <a href="{{ route('schedules.index', ['tab' => 'class']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ request('tab') == 'class' ? 'border-b-2 border-[#4AC066] text-[#4AC066]' : 'text-[#4AC066] hover:text-[#4AC066]/70' }} 
           font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-calendar-plus"></i>
                <span class="hidden sm:inline">Class Scheduling</span>
            </a>

            <!-- Schedule History-->
            <a href="{{ route('schedules.index', ['tab' => 'history']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ request('tab') == 'history' ? 'border-b-2 border-[#D97B15] text-[#D97B15]' : 'text-[#D97B15] hover:text-[#D97B15]/70' }} 
           font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-history"></i>
                <span class="hidden sm:inline">Schedule History</span>
            </a>
        </nav>
    </div>


    <!-- Main Content -->
    <div>
        <div class="max-w-full mx-auto">
            <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                @if (request('tab', 'employee') == 'employee')
                    @include('schedules.tabs.employee-availability-calendar')
                @elseif(request('tab') == 'class')
                    @include('schedules.tabs.class-scheduling')
                @elseif(request('tab') == 'history')
                    @include('schedules.tabs.schedule-history')
                @endif
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
function exportSchedule(type, specificDate = null) {
    // Show loading indicator
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Exporting...
    `;
    
    // Handle final export: call dedicated finalized export endpoint (no overview)
    if (type === 'final') {
        if (!specificDate) {
            showNotification('Date is required for final export.', 'error');
            button.disabled = false;
            button.innerHTML = originalText;
            return;
        }

        const finalUrl = `{{ route('schedules.export-final') }}?date=${encodeURIComponent(specificDate)}`;
        fetch(finalUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `finalized_schedule_${specificDate.replace(/[\/\s]/g, '-')}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            showNotification(`Final schedule for ${specificDate} exported successfully!`, 'success');
        })
        .catch(error => {
            console.error('Final export error:', error);
            showNotification('Error exporting final schedule. Please try again.', 'error');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
        return;
    }
    
    // Handle tentative export (existing logic)
    let exportUrl = '/schedules/export-tentative';
    
    // If specific date is provided, add it as a parameter
    if (specificDate) {
        exportUrl += `?date=${encodeURIComponent(specificDate)}`;
    }
    
    // Create a temporary link to trigger the download
    fetch(exportUrl, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.blob();
    })
    .then(blob => {
        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        
        // Create filename with date if specific date is provided
        let filename = `${type}_schedule_${new Date().toISOString().slice(0,10)}.xlsx`;
        if (specificDate) {
            filename = `${type}_schedule_${specificDate.replace(/[\/\s]/g, '-')}.xlsx`;
        }
        a.download = filename;
        
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        // Show success message
        const dateText = specificDate ? ` for ${specificDate}` : '';
        showNotification(`Excel file${dateText} exported successfully!`, 'success');
    })
    .catch(error => {
        console.error('Export error:', error);
        showNotification('Error exporting file. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

function exportSelectedSchedules() {
    const selectedDates = Array.from(document.querySelectorAll('.schedule-checkbox:checked')).map(cb => cb.value);
    
    if (selectedDates.length === 0) {
        showNotification('Please select at least one schedule to export.', 'error');
        return;
    }
    
    // Show loading indicator
    const button = document.getElementById('exportButton');
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Exporting...
    `;
    
    // Create form data with selected dates
    const formData = new FormData();
    selectedDates.forEach(date => {
        formData.append('dates[]', date);
    });
    
    // Export selected schedules
    fetch('/schedules/export-selected', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.blob();
    })
    .then(blob => {
        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `selected_schedules_${new Date().toISOString().slice(0,10)}.xlsx`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        // Show success message
        showNotification(`Excel file with ${selectedDates.length} schedule(s) exported successfully!`, 'success');
    })
    .catch(error => {
        console.error('Export error:', error);
        showNotification('Error exporting selected schedules. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

function toggleAllSchedules() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.schedule-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateExportButton();
}

function updateExportButton() {
    const selectedCount = document.querySelectorAll('.schedule-checkbox:checked').length;
    const exportButton = document.getElementById('exportButton');
    const exportButtonText = document.getElementById('exportButtonText');
    const selectAll = document.getElementById('selectAll');
    const totalCheckboxes = document.querySelectorAll('.schedule-checkbox').length;
    
    if (selectedCount > 0) {
        exportButton.disabled = false;
        exportButton.classList.remove('opacity-50', 'cursor-not-allowed');
        exportButton.classList.add('hover:bg-[#184679]');
        exportButtonText.textContent = `Export File (${selectedCount} selected)`;
    } else {
        exportButton.disabled = true;
        exportButton.classList.add('opacity-50', 'cursor-not-allowed');
        exportButton.classList.remove('hover:bg-[#184679]');
        exportButtonText.textContent = 'Export File (0 selected)';
    }
    
    // Update select all checkbox
    if (selectedCount === totalCheckboxes && totalCheckboxes > 0) {
        selectAll.checked = true;
        selectAll.indeterminate = false;
    } else if (selectedCount > 0) {
        selectAll.checked = false;
        selectAll.indeterminate = true;
    } else {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    }
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-medium transition-all duration-300 transform translate-x-full opacity-0 ${
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
</script>
@endpush
