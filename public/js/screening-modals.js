/**
 * ============================================================================
 * SCREENING MODALS - JavaScript Module
 * ============================================================================
 * Handles all modal interactions for the hiring & onboarding screening process
 * 
 * @author OGS Connect
 * @version 1.0.0
 */

// ============================================================================
// GLOBAL STATE
// ============================================================================
let currentDemoId = null;
let currentDemoDetailsId = null;
let currentDemoApplicantId = null;
let currentOnboardingId = null;
let pendingAction = null;
let assignmentHistory = [];

// ============================================================================
// CONSTANTS
// ============================================================================
const availableAccounts = ['gls', 'talk915', 'babilala', 'tutlo'];

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Format date to readable format (e.g., "November 7, 2025, 2:30 PM")
 */
function formatDateToWords(dateString) {
    if (!dateString) return '';
    
    // If it's already formatted (contains month name), return as is
    if (dateString.match(/^[A-Z][a-z]+ \d{1,2}, \d{4}/)) {
        return dateString;
    }
    
    // Replace " - " with space for proper parsing
    const cleanedDate = dateString.replace(' - ', ' ');
    
    const date = new Date(cleanedDate);
    if (isNaN(date.getTime())) return dateString;
    
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    };
    
    return date.toLocaleString('en-US', options);
}

/**
 * Get CSRF token from page
 */
function getCsrfToken() {
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (metaToken) return metaToken.content;
    
    const formToken = document.querySelector('input[name="_token"]');
    if (formToken) return formToken.value;
    
    throw new Error('CSRF token not found. Please refresh the page.');
}

/**
 * Show modal by ID
 */
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'flex';
}

/**
 * Hide modal by ID
 */
function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'none';
}

// ============================================================================
// ERROR HANDLING
// ============================================================================

function showModalError(message, type = 'error') {
    const errorDiv = document.getElementById('modalError');
    const errorMessage = document.getElementById('modalErrorMessage');
    
    if (!errorDiv || !errorMessage) return;
    
    errorMessage.textContent = message;
    
    if (type === 'info') {
        errorDiv.className = 'px-6 py-3 bg-blue-50 border-l-4 border-blue-400';
        errorMessage.className = 'text-sm text-blue-700';
    } else {
        errorDiv.className = 'px-6 py-3 bg-red-50 border-l-4 border-red-400';
        errorMessage.className = 'text-sm text-red-700';
    }
    
    errorDiv.classList.remove('hidden');
}

function hideModalError() {
    const errorDiv = document.getElementById('modalError');
    if (errorDiv) errorDiv.classList.add('hidden');
}

function showPassModalError(message) {
    const errorDiv = document.getElementById('passModalError');
    const errorMessage = document.getElementById('passModalErrorMessage');
    if (errorDiv && errorMessage) {
        errorMessage.textContent = message;
        errorDiv.classList.remove('hidden');
    }
}

function hidePassModalError() {
    const errorDiv = document.getElementById('passModalError');
    if (errorDiv) errorDiv.classList.add('hidden');
}

function showFailModalError(message) {
    const errorDiv = document.getElementById('failModalError');
    const errorMessage = document.getElementById('failModalErrorMessage');
    if (errorDiv && errorMessage) {
        errorMessage.textContent = message;
        errorDiv.classList.remove('hidden');
    }
}

function hideFailModalError() {
    const errorDiv = document.getElementById('failModalError');
    if (errorDiv) errorDiv.classList.add('hidden');
}

// ============================================================================
// EDIT MODAL FUNCTIONS
// ============================================================================

async function loadEditModalData(demoId) {
    try {
        showModalError('Loading screening data...', 'info');
        
        const response = await fetch(`/demo/${demoId}/edit-data`);
        if (!response.ok) throw new Error('Failed to load screening data');

        const data = await response.json();
        hideModalError();
        openEditModal(demoId, data);
    } catch (error) {
        console.error('Error loading screening data:', error);
        showModalError('Failed to load screening data: ' + error.message);
    }
}

function openEditModal(demoId, data) {
    currentDemoId = demoId;
    assignmentHistory = data.assignment_history || [];
    window.currentModalData = data;
    
    console.log('Opening edit modal with data:', data);
    
    // Populate form fields
    document.getElementById('editForm').action = `/demo/${demoId}`;
    // Only set interviewer if data has it, otherwise keep the server-rendered value
    if (data.interviewer) {
        document.getElementById('modal_interviewer').value = data.interviewer;
    }
    document.getElementById('modal_email').value = data.email || '';
    
    // Set assigned account
    const assignedAccountSelect = document.getElementById('modal_assigned_account');
    if (assignedAccountSelect && data.assigned_account) {
        console.log('Setting assigned account to:', data.assigned_account);
        
        // Remove selected attribute from all options first
        Array.from(assignedAccountSelect.options).forEach(option => {
            option.removeAttribute('selected');
        });
        
        // Try direct value assignment first
        assignedAccountSelect.value = data.assigned_account;
        
        // If direct value assignment didn't work, try case-insensitive matching
        if (!assignedAccountSelect.value || assignedAccountSelect.value === '') {
            const accountValue = data.assigned_account.toLowerCase();
            const options = assignedAccountSelect.options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].value.toLowerCase() === accountValue) {
                    options[i].setAttribute('selected', 'selected');
                    assignedAccountSelect.selectedIndex = i;
                    console.log('Matched account at index:', i, 'with value:', options[i].value);
                    break;
                }
            }
        } else {
            // Mark the matched option as selected
            const selectedOption = assignedAccountSelect.options[assignedAccountSelect.selectedIndex];
            if (selectedOption) {
                selectedOption.setAttribute('selected', 'selected');
            }
        }
        
        console.log('Final assigned account value:', assignedAccountSelect.value);
    }
    
    document.getElementById('modal_hiring_status').value = data.hiring_status || '';
    document.getElementById('modal_notes').value = data.notes || '';
    
    // Handle schedule - display as formatted text
    const schedule = data.schedule || data.demo_schedule || data.interview_time || '';
    const scheduleInput = document.getElementById('modal_schedule');
    if (schedule) {
        scheduleInput.value = formatDateToWords(schedule);
    } else {
        scheduleInput.value = '';
    }
    
    showModal('editModal');
}

function closeEditModal() {
    hideModal('editModal');
    hideModalError();
    currentDemoId = null;
}

// ============================================================================
// PASS MODAL FUNCTIONS
// ============================================================================

function showPassModal() {
    if (!currentDemoId) {
        alert('No screening ID found');
        return;
    }
    
    // Pre-fill assigned account from the main modal
    const assignedAccount = document.getElementById('modal_assigned_account').value;
    document.getElementById('pass_assigned_account').value = assignedAccount;
    
    // Set form action
    document.getElementById('passForm').action = `/demo/${currentDemoId}/status`;
    
    // Update next status options based on current status
    updateNextStatusOptions();
    
    // Hide any previous errors
    hidePassModalError();
    
    // Show the modal
    showModal('passModal');
}

function hidePassModal() {
    hideModal('passModal');
    hidePassModalError();
}

function updateNextStatusOptions() {
    const currentStatus = document.getElementById('modal_hiring_status').value;
    const nextStatusSelect = document.getElementById('pass_next_status');
    
    // Clear existing options
    nextStatusSelect.innerHTML = '<option value="" selected disabled>Select Next Status</option>';
    
    let availableOptions = [];
    
    switch(currentStatus) {
        case 'screening':
            availableOptions = [
                { value: 'training', text: 'Training' },
                { value: 'demo', text: 'Demo' }
            ];
            break;
        case 'training':
            availableOptions = [{ value: 'demo', text: 'Demo' }];
            break;
        case 'demo':
            availableOptions = [{ value: 'onboarding', text: 'Onboarding' }];
            break;
        case 'onboarding':
            availableOptions = [];
            break;
        default:
            availableOptions = [];
    }
    
    availableOptions.forEach(option => {
        const optionElement = document.createElement('option');
        optionElement.value = option.value;
        optionElement.textContent = option.text;
        nextStatusSelect.appendChild(optionElement);
    });
}

function togglePassSchedule() {
    const nextStatus = document.getElementById('pass_next_status').value;
    const scheduleField = document.getElementById('pass_schedule_field');
    
    if (nextStatus === 'demo') {
        scheduleField.style.display = 'block';
        document.getElementById('pass_demo_schedule').required = true;
    } else {
        scheduleField.style.display = 'none';
        document.getElementById('pass_demo_schedule').required = false;
        document.getElementById('pass_demo_schedule').value = '';
    }
}

function showPassConfirmation() {
    const interviewer = document.getElementById('pass_interviewer').value;
    const nextStatus = document.getElementById('pass_next_status').value;
    const schedule = document.getElementById('pass_demo_schedule').value;
    const notes = document.getElementById('pass_notes').value;
    
    if (!interviewer) {
        showPassModalError('Please enter interviewer name');
        return;
    }
    
    if (!nextStatus) {
        showPassModalError('Please select next status');
        return;
    }
    
    if (nextStatus === 'demo' && !schedule) {
        showPassModalError('Please select a schedule for demo');
        return;
    }
    
    const form = document.getElementById('passForm');
    if (form && form.checkValidity()) {
        const assignedAccount = document.getElementById('pass_assigned_account').value;
        updatePassConfirmationModal(interviewer, assignedAccount, nextStatus, schedule, notes);
        showModal('passConfirmationModal');
    } else {
        form.reportValidity();
    }
}

function updatePassConfirmationModal(interviewer, assignedAccount, nextStatus, schedule, notes) {
    const titleElement = document.getElementById('passConfirmationTitle');
    const messageElement = document.getElementById('passConfirmationMessage');
    
    if (!titleElement || !messageElement) return;
    
    titleElement.textContent = 'Confirm Pass';
    
    let nextActionText = '';
    switch(nextStatus) {
        case 'screening': nextActionText = 'Move to Screening stage'; break;
        case 'demo': nextActionText = 'Move to Demo stage'; break;
        case 'training': nextActionText = 'Move to Training stage'; break;
        case 'onboarding': nextActionText = 'Move to Onboarding stage'; break;
        default: nextActionText = 'Update status';
    }
    
    messageElement.innerHTML = `
        This applicant has <span class="font-bold text-green-600">passed</span> and will be moved to the next stage.
        <br><br>
        <strong>Next Action:</strong> ${nextActionText}
    `;
}

function hidePassConfirmation() {
    hideModal('passConfirmationModal');
}

async function submitPassForm() {
    const form = document.getElementById('passForm');
    if (!form) return;
    
    const interviewer = document.getElementById('pass_interviewer').value;
    const nextStatus = document.getElementById('pass_next_status').value;
    const schedule = document.getElementById('pass_demo_schedule').value;
    
    if (!interviewer) {
        showPassModalError('Please enter interviewer name');
        return;
    }
    
    if (!nextStatus) {
        showPassModalError('Please select next status');
        return;
    }
    
    if (nextStatus === 'demo' && !schedule) {
        showPassModalError('Please select a schedule for demo');
        return;
    }
    
    const confirmButton = document.querySelector('button[onclick="submitPassForm()"]');
    if (confirmButton) {
        confirmButton.disabled = true;
        confirmButton.innerHTML = 'Processing...';
    }
    
    try {
        const response = await fetch(form.action, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                next_status: nextStatus,
                next_schedule: schedule,
                notes: document.getElementById('pass_notes').value
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to process pass action');
        }

        const data = await response.json();
        if (data.success) {
            hidePassConfirmation();
            hidePassModal();
            closeEditModal();
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to process pass action');
        }
    } catch (error) {
        console.error('Error:', error);
        showPassModalError('Failed to process pass action: ' + error.message);
        
        if (confirmButton) {
            confirmButton.disabled = false;
            confirmButton.innerHTML = 'Confirm';
        }
    }
}

// ============================================================================
// FAIL MODAL FUNCTIONS
// ============================================================================

function showFailOptionsModal() {
    if (!currentDemoId) {
        alert('No screening ID found');
        return;
    }
    
    // Reset form
    document.getElementById('fail_reason').value = '';
    document.getElementById('new_interview_time').value = '';
    document.getElementById('transfer_assigned_account').value = '';
    document.getElementById('transfer_status').value = '';
    document.getElementById('transfer_schedule').value = '';
    document.getElementById('fail_notes').value = '';
    
    // Hide all conditional sections
    document.getElementById('new_interview_time_section').style.display = 'none';
    document.getElementById('transfer_account_section').style.display = 'none';
    
    // Reset styling
    const failModalHeader = document.getElementById('failModalHeader');
    const failSubmitButton = document.getElementById('failSubmitButton');
    if (failModalHeader) failModalHeader.className = 'flex justify-between items-center px-6 py-3 bg-[#0E335D] rounded-t-lg';
    if (failSubmitButton) failSubmitButton.className = 'bg-[#F65353] text-white px-6 py-2 rounded-lg font-semibold hover:opacity-90 transition';
    
    // Pre-filter accounts for transfer
    preFilterTransferAccounts();
    
    // Hide any previous errors
    hideFailModalError();
    
    showModal('failOptionsModal');
}

function hideFailOptionsModal() {
    hideModal('failOptionsModal');
    hideFailModalError();
}

function toggleFailFields() {
    const failReason = document.getElementById('fail_reason').value;
    const newInterviewTimeSection = document.getElementById('new_interview_time_section');
    const transferAccountSection = document.getElementById('transfer_account_section');
    const failModalHeader = document.getElementById('failModalHeader');
    const failSubmitButton = document.getElementById('failSubmitButton');
    
    // Hide all sections first
    newInterviewTimeSection.style.display = 'none';
    transferAccountSection.style.display = 'none';
    
    // Change colors and button text based on fail reason
    if (failReason === 'transfer_account') {
        if (failModalHeader) failModalHeader.className = 'flex justify-between items-center px-6 py-3 bg-[#2A5382] rounded-t-lg flex-shrink-0';
        if (failSubmitButton) {
            failSubmitButton.className = 'bg-[#0E335D] text-white px-6 py-2 rounded-lg font-semibold hover:opacity-90 transition';
            failSubmitButton.textContent = 'Transfer';
        }
    } else {
        if (failModalHeader) failModalHeader.className = 'flex justify-between items-center px-6 py-3 bg-[#0E335D] rounded-t-lg flex-shrink-0';
        if (failSubmitButton) {
            failSubmitButton.className = 'bg-[#F65353] text-white px-6 py-2 rounded-lg font-semibold hover:opacity-90 transition';
            failSubmitButton.textContent = 'Fail';
        }
    }
    
    // Show relevant section
    if (failReason === 'missed') {
        newInterviewTimeSection.style.display = 'block';
    } else if (failReason === 'transfer_account') {
        transferAccountSection.style.display = 'block';
        preFilterTransferAccounts();
    }
}

function getAssignmentHistory() {
    return assignmentHistory || [];
}

function preFilterTransferAccounts() {
    const currentAccount = document.getElementById('modal_assigned_account').value;
    const accountSelect = document.getElementById('transfer_assigned_account');
    const history = getAssignmentHistory();
    
    if (!accountSelect) return;
    
    // Clear existing options
    accountSelect.innerHTML = '<option value="" disabled selected>Select a different account</option>';
    
    // Add only available accounts
    const blockedAccounts = [currentAccount, ...history];
    const availableOptions = availableAccounts.filter(account => !blockedAccounts.includes(account));
    
    availableOptions.forEach(account => {
        const option = document.createElement('option');
        option.value = account;
        option.textContent = account.charAt(0).toUpperCase() + account.slice(1);
        accountSelect.appendChild(option);
    });
}

function showFailConfirmation() {
    const failReason = document.getElementById('fail_reason').value;
    const interviewer = document.getElementById('fail_interviewer').value;
    const notes = document.getElementById('fail_notes').value;
    
    if (!failReason) {
        showFailModalError('Please select a failure reason');
        return;
    }
    
    if (!interviewer) {
        showFailModalError('Please enter interviewer name');
        return;
    }
    
    if (failReason === 'missed') {
        const newInterviewTime = document.getElementById('new_interview_time').value;
        if (!newInterviewTime) {
            showFailModalError('Please select a new interview time for missed interview');
            return;
        }
    } else if (failReason === 'transfer_account') {
        const assignedAccount = document.getElementById('transfer_assigned_account').value;
        const newStatus = document.getElementById('transfer_status').value;
        const schedule = document.getElementById('transfer_schedule').value;
        
        if (!assignedAccount || !newStatus || !schedule) {
            showFailModalError('Please fill in all transfer account fields');
            return;
        }
    }
    
    updateFailConfirmationModal(failReason, interviewer, notes);
    showModal('failConfirmationModal');
}

function updateFailConfirmationModal(failReason, interviewer, notes) {
    const titleElement = document.getElementById('failConfirmationTitle');
    const messageElement = document.getElementById('failConfirmationMessage');
    
    if (!titleElement || !messageElement) return;
    
    switch(failReason) {
        case 'missed':
            const newInterviewTime = document.getElementById('new_interview_time').value;
            titleElement.textContent = 'Confirm Missed Interview';
            messageElement.innerHTML = `
                Are you sure you want to mark this applicant as <span class="font-bold text-red-600">missed interview</span>?
                <br>
            `;
            break;
            
        case 'declined':
            titleElement.textContent = 'Confirm Declined';
            messageElement.innerHTML = `
                Are you sure you want to mark this applicant as <span class="font-bold text-red-600">declined</span>?
                <br>
            `;
            break;
            
        case 'not_recommended':
            titleElement.textContent = 'Confirm Not Recommended';
            messageElement.innerHTML = `
                Are you sure you want to mark this applicant as <span class="font-bold text-red-600">not recommended</span>?
                <br>
            `;
            break;
            
        case 'transfer_account':
            const assignedAccount = document.getElementById('transfer_assigned_account').value;
            const newStatus = document.getElementById('transfer_status').value;
            const schedule = document.getElementById('transfer_schedule').value;
            titleElement.textContent = 'Confirm Account Transfer';
            messageElement.innerHTML = `
                Are you sure you want to <span class="font-bold text-red-600">transfer</span> this applicant to a different account?
                <br>
            `;
            break;
            
        default:
            titleElement.textContent = 'Confirm Fail Action';
            messageElement.innerHTML = `
                This applicant has <span class="font-bold text-red-600">failed</span> the current stage.
            `;
    }
}

function hideFailConfirmation() {
    hideModal('failConfirmationModal');
}

async function submitFailAction() {
    const failReason = document.getElementById('fail_reason').value;
    const interviewer = document.getElementById('fail_interviewer').value;
    const notes = document.getElementById('fail_notes').value;
    
    try {
        let requestData = {
            fail_reason: failReason,
            interviewer: interviewer,
            notes: notes
        };
        
        if (failReason === 'missed') {
            const newInterviewTime = document.getElementById('new_interview_time').value;
            if (!newInterviewTime) {
                showFailModalError('Please select a new interview time');
                return;
            }
            requestData.new_interview_time = newInterviewTime;
        } else if (failReason === 'transfer_account') {
            const assignedAccount = document.getElementById('transfer_assigned_account').value;
            const newStatus = document.getElementById('transfer_status').value;
            const schedule = document.getElementById('transfer_schedule').value;
            
            if (!assignedAccount || !newStatus || !schedule) {
                showFailModalError('Please fill in all transfer account fields');
                return;
            }
            
            requestData.transfer_data = {
                assigned_account: assignedAccount,
                new_status: newStatus,
                schedule: schedule
            };
        }
        
        const response = await fetch(`/demo/${currentDemoId}/fail`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to process failure action');
        }

        const data = await response.json();
        if (data.success) {
            hideFailConfirmation();
            hideFailOptionsModal();
            closeEditModal();
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to process failure action');
        }
    } catch (error) {
        console.error('Error:', error);
        showFailModalError('Failed to process failure action: ' + error.message);
    }
}

// ============================================================================
// PASS/FAIL CONFIRMATION & DEMO MODAL FUNCTIONS
// ============================================================================

function showPassFailConfirmation() {
    showModal('passFailConfirmationModal');
}

function getNextActionText(nextStatus) {
    switch(nextStatus) {
        case 'screening': return 'Move to Screening stage';
        case 'demo': return 'Move to Demo stage';
        case 'training': return 'Move to Training stage';
        case 'onboarding': return 'Move to Onboarding stage';
        default: return 'Update status';
    }
}

function hidePassFailConfirmation() {
    hideModal('passFailConfirmationModal');
}

async function confirmPassFailAction() {
    try {
        const response = await fetch(`/demo/${currentDemoId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: 'pass_or_fail'
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to process action');
        }

        const data = await response.json();
        if (data.success) {
            hidePassFailConfirmation();
            closeEditModal();
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to process action');
        }
    } catch (error) {
        console.error('Error:', error);
        showModalError('Failed to process action: ' + error.message);
    }
}

function handleModalAction(action) {
    console.log('Handling action:', action);
}

function hideDemoConfirmationModal() {
    hideModal('demoConfirmationModal');
}

function showDemoFailConfirmation() {
    // Preserve the demo ID
    const preservedId = currentDemoId;
    const preservedApplicantId = currentDemoApplicantId;
    
    // Hide the main demo confirmation modal
    hideModal('demoConfirmationModal');
    
    // Restore IDs
    currentDemoId = preservedId;
    currentDemoApplicantId = preservedApplicantId;
    
    // Store current account
    const currentAccount = window.currentDemoApplicantData?.account || '';
    const currentAccountField = document.getElementById('demo_current_account');
    if (currentAccountField) {
        currentAccountField.value = currentAccount;
    }
    
    // Filter out current account from dropdown
    const accountDropdown = document.getElementById('demo_transfer_account');
    if (accountDropdown && currentAccount) {
        const options = accountDropdown.querySelectorAll('option');
        options.forEach(option => {
            if (option.value && option.value.toLowerCase().replace(/\s+/g, '_') === currentAccount.toLowerCase().replace(/\s+/g, '_')) {
                option.style.display = 'none';
            } else {
                option.style.display = '';
            }
        });
    }
    
    // Reset the fail form
    const failReasonSelect = document.getElementById('demo_fail_reason');
    const notesField = document.getElementById('demo_fail_notes');
    if (failReasonSelect) failReasonSelect.value = '';
    if (notesField) notesField.value = '';
    
    // Hide conditional sections
    const newTimeSection = document.getElementById('demo_new_time_section');
    const transferSection = document.getElementById('demo_transfer_section');
    if (newTimeSection) newTimeSection.style.display = 'none';
    if (transferSection) transferSection.style.display = 'none';
    
    // Show the fail modal
    showModal('demoFailConfirmationModal');
}

function hideDemoFailConfirmation() {
    hideModal('demoFailConfirmationModal');
}

function toggleDemoFailFields() {
    const failReason = document.getElementById('demo_fail_reason').value;
    const newTimeSection = document.getElementById('demo_new_time_section');
    const transferSection = document.getElementById('demo_transfer_section');
    
    // Show/hide new demo time section
    if (newTimeSection) {
        newTimeSection.style.display = (failReason === 'missed') ? 'block' : 'none';
    }
    
    // Show/hide transfer account section
    if (transferSection) {
        transferSection.style.display = (failReason === 'transfer_account') ? 'block' : 'none';
    }
}

async function submitDemoFailAction() {
    const failReason = document.getElementById('demo_fail_reason').value;
    const notes = document.getElementById('demo_fail_notes').value;
    
    if (!failReason) {
        alert('Please select a failure reason');
        return;
    }
    
    try {
        console.log('Submitting demo fail for ID:', currentDemoId, 'Reason:', failReason);
        
        let requestData = {
            status: 'failed',
            fail_reason: failReason,
            notes: notes || 'Demo failed - ' + failReason
        };
        
        // Handle missed demo - reschedule
        if (failReason === 'missed') {
            const newDemoTime = document.getElementById('demo_new_time').value;
            if (!newDemoTime) {
                alert('Please select a new demo time');
                return;
            }
            // Keep status as demo but update schedule
            requestData.status = 'demo';
            requestData.next_status = 'demo';
            requestData.next_schedule = newDemoTime;
            requestData.notes = notes || 'Demo missed - rescheduled';
        }
        
        // Handle transfer account
        if (failReason === 'transfer_account') {
            const transferAccount = document.getElementById('demo_transfer_account').value;
            const transferStatus = document.getElementById('demo_transfer_status').value;
            const transferSchedule = document.getElementById('demo_transfer_schedule').value;
            
            if (!transferAccount || !transferStatus || !transferSchedule) {
                alert('Please fill all transfer account fields');
                return;
            }
            
            requestData.status = transferStatus;
            requestData.assigned_account = transferAccount;
            requestData.next_status = transferStatus;
            requestData.next_schedule = transferSchedule;
            requestData.notes = notes || `Transferred to ${transferAccount} account - ${transferStatus}`;
        }
        
        const response = await fetch(`/demo/${currentDemoId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to process failure action');
        }

        const data = await response.json();
        if (data.success) {
            console.log('Demo fail action successful, reloading page...');
            hideDemoFailConfirmation();
            hideDemoConfirmationModal();
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to process failure action');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to process failure action: ' + error.message);
    }
}

function testModal() {
    console.log('Test modal function called');
}

function loadDemoApplicantData() {
    console.log('Loading demo applicant data');
}

function showDemoPassConfirmation() {
    showModal('demoPassConfirmationModal');
}

function hideDemoPassConfirmation() {
    hideModal('demoPassConfirmationModal');
}

async function confirmDemoPass() {
    try {
        console.log('Confirming demo pass for ID:', currentDemoId);
        
        const response = await fetch(`/demo/${currentDemoId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                phase: 'onboarding',
                status: 'passed',
                notes: 'Demo passed - moved to onboarding stage'
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to process demo pass');
        }

        const data = await response.json();
        if (data.success) {
            console.log('Demo pass successful, reloading page...');
            hideDemoPassConfirmation();
            hideDemoConfirmationModal();
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to process demo pass');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to process demo pass: ' + error.message);
    }
}

function handleSuccessAction(action) {
    console.log('Handling success action:', action);
}

// ============================================================================
// OTHER MODAL FUNCTIONS
// ============================================================================

function hideNextStepModal() {
    hideModal('nextStepModal');
}

async function submitNextStepForm() {
    try {
        const response = await fetch(`/demo/${currentDemoId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                next_status: document.getElementById('next_status').value,
                next_schedule: document.getElementById('next_schedule').value,
                notes: document.getElementById('next_notes').value
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to update status');
        }

        const data = await response.json();
        if (data.success) {
            hideNextStepModal();
            closeEditModal();
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to update status');
        }
    } catch (error) {
        console.error('Error:', error);
        showModalError('Failed to update status: ' + error.message);
    }
}

function hidePassConfirmModal() {
    hideModal('passApplicantConfirmModal');
}

async function confirmPassSubmit() {
    try {
        const response = await fetch(`/demo/${currentDemoId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                next_status: 'onboarding'
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to update status');
        }

        const data = await response.json();
        if (data.success) {
            hidePassConfirmModal();
            showModal('successApplicantModal');
        } else {
            throw new Error(data.message || 'Failed to update status');
        }
    } catch (error) {
        console.error('Error:', error);
        showModalError('Failed to update status: ' + error.message);
    }
}

function hideSuccessApplicantModal() {
    hideModal('successApplicantModal');
}

function registerApplicant() {
    hideSuccessApplicantModal();
    
    const assignedAccount = document.getElementById('modal_assigned_account').value;
    const personalEmail = document.getElementById('modal_email').value;
    
    document.getElementById('reg_assigned_account').value = assignedAccount;
    document.getElementById('personal_email').value = personalEmail;
    
    showModal('employeeRegistrationModal');
}

function hideEmployeeRegistrationModal() {
    hideModal('employeeRegistrationModal');
}

async function submitEmployeeRegistration() {
    const form = document.getElementById('employeeRegistrationForm');
    const registerButton = document.querySelector('#employeeRegistrationModal button[onclick="submitEmployeeRegistration()"]');
    
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        showModalError('Passwords do not match!');
        return;
    }

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    if (registerButton) {
        registerButton.disabled = true;
        registerButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }

    try {
        const formData = new FormData();
        formData.append('name', document.getElementById('name').value);
        formData.append('personal_email', document.getElementById('personal_email').value);
        formData.append('password', password);
        formData.append('username', document.getElementById('username').value);
        formData.append('assigned_account', document.getElementById('reg_assigned_account').value);
        formData.append('status', 'active');
        formData.append('_token', getCsrfToken());
        
        const response = await fetch('/demos/' + currentDemoId + '/register-tutor', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Server returned ' + response.status);
        }

        const data = await response.json();

        if (data.success) {
            hideEmployeeRegistrationModal();
            showModal('registrationSuccessModal');
            setTimeout(() => window.location.reload(), 2000);
        } else {
            throw new Error(data.message || 'Failed to register tutor');
        }
    } catch (error) {
        console.error('Error:', error);
        showModalError(error.message || 'An error occurred while registering the tutor');
        
        if (registerButton) {
            registerButton.disabled = false;
            registerButton.innerHTML = 'Complete Registration';
        }
    }
}

// ============================================================================
// DEMO DETAILS CONFIRMATION
// ============================================================================

function showDemoDetailsConfirmation(demoId, applicantName) {
    currentDemoDetailsId = demoId;
    const nameElement = document.getElementById('demoDetailsApplicantName');
    if (nameElement) nameElement.textContent = applicantName;
    showModal('demoDetailsConfirmationModal');
}

function hideDemoDetailsConfirmation() {
    hideModal('demoDetailsConfirmationModal');
    currentDemoDetailsId = null;
}

function proceedToApplicantDetails() {
    if (currentDemoDetailsId) {
        window.location.href = `/hiring-onboarding/applicant/${currentDemoDetailsId}/uneditable`;
    }
}

// ============================================================================
// DEMO PASS/FAIL MODAL (Review Demo)
// ============================================================================

function showDemoPassFailModal(demoId, applicantName, email, phone, account, schedule, notes) {
    console.log('showDemoPassFailModal called with:', { demoId, applicantName, email, phone, account, schedule, notes });
    
    currentDemoId = demoId;
    currentDemoApplicantId = demoId;
    
    // Populate the demo confirmation modal with applicant info
    const nameEl = document.getElementById('demoApplicantName');
    const emailEl = document.getElementById('demoApplicantEmail');
    const accountEl = document.getElementById('demoApplicantAccount');
    const scheduleEl = document.getElementById('demoApplicantSchedule');
    const notesEl = document.getElementById('demoApplicantNotes');
    
    console.log('Elements found:', { nameEl, emailEl, accountEl, scheduleEl, notesEl });
    
    if (nameEl) nameEl.textContent = applicantName || 'N/A';
    if (emailEl) emailEl.textContent = email || 'N/A';
    if (accountEl) accountEl.textContent = account || 'N/A';
    if (scheduleEl) scheduleEl.textContent = schedule ? formatDateToWords(schedule) : 'N/A';
    if (notesEl) notesEl.textContent = notes || 'No notes available';
    
    // Store data for later use
    window.currentDemoApplicantData = {
        id: demoId,
        name: applicantName,
        email: email,
        account: account,
        schedule: schedule,
        notes: notes
    };
    
    console.log('About to show modal: demoConfirmationModal');
    showModal('demoConfirmationModal');
}

function hideDemoPassFailModal() {
    hideModal('demoConfirmationModal');
    currentDemoId = null;
    currentDemoApplicantId = null;
}

// ============================================================================
// ONBOARDING PASS/FAIL MODAL
// ============================================================================

function showOnboardingPassFailModal(onboardingId, applicantName, email, phone, account, schedule, notes) {
    console.log('Opening onboarding pass/fail modal for:', applicantName);
    currentOnboardingId = onboardingId;
    
    // Populate applicant info
    document.getElementById('onboardingApplicantName').textContent = applicantName;
    document.getElementById('onboardingApplicantEmail').textContent = email || '—';
    document.getElementById('onboardingApplicantAccount').textContent = account || '—';
    document.getElementById('onboardingApplicantSchedule').textContent = schedule ? formatDateToWords(schedule) : '—';
    
    const notesEl = document.getElementById('onboardingApplicantNotes');
    if (notesEl) notesEl.textContent = notes || 'No notes available';
    
    // Store data for later use
    window.currentOnboardingApplicantData = {
        id: onboardingId,
        name: applicantName,
        email: email,
        account: account,
        schedule: schedule,
        notes: notes
    };
    
    window.currentApplicantInfo = {
        id: onboardingId,
        name: applicantName,
        account: account,
        schedule: schedule,
        email: email
    };
    
    showModal('onboardingPassFailModal');
}

function hideOnboardingPassFailModal() {
    hideModal('onboardingPassFailModal');
    currentOnboardingId = null;
}

function showOnboardingFailModal() {
    const preservedId = currentOnboardingId;
    hideOnboardingPassFailModal();
    currentOnboardingId = preservedId;
    
    const form = document.getElementById('onboardingFailForm');
    if (form) form.reset();
    
    document.getElementById('onboarding_new_demo_time_section').style.display = 'none';
    showModal('onboardingFailModal');
}

function hideOnboardingFailModal() {
    hideModal('onboardingFailModal');
}

function toggleOnboardingFailFields() {
    const failReason = document.getElementById('onboarding_fail_reason').value;
    const newDemoTimeSection = document.getElementById('onboarding_new_demo_time_section');
    
    if (newDemoTimeSection) {
        newDemoTimeSection.style.display = failReason === 'missed' ? 'block' : 'none';
    }
    
    // Update modal header and button based on action
    const modalHeader = document.querySelector('#onboardingFailModal .bg-\\[\\#0E335D\\]');
    const submitButton = document.querySelector('#onboardingFailModal button[onclick="confirmOnboardingFailSubmit()"]');
    
    if (failReason === 'missed') {
        // Change to reschedule styling
        if (modalHeader) {
            modalHeader.className = 'flex justify-between items-center px-6 py-3 bg-[#2A5382] rounded-t-lg';
            const headerText = modalHeader.querySelector('h2');
            if (headerText) headerText.textContent = 'Reschedule Onboarding';
        }
        if (submitButton) {
            submitButton.className = 'bg-[#2A5382] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
            submitButton.textContent = 'Reschedule';
        }
    } else if (failReason === 'declined') {
        // Change to declined styling
        if (modalHeader) {
            modalHeader.className = 'flex justify-between items-center px-6 py-3 bg-[#0E335D] rounded-t-lg';
            const headerText = modalHeader.querySelector('h2');
            if (headerText) headerText.textContent = 'Archive Applicant';
        }
        if (submitButton) {
            submitButton.className = 'bg-red-500 text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
            submitButton.textContent = 'Archive';
        }
    } else {
        // Default styling
        if (modalHeader) {
            modalHeader.className = 'flex justify-between items-center px-6 py-3 bg-[#0E335D] rounded-t-lg';
            const headerText = modalHeader.querySelector('h2');
            if (headerText) headerText.textContent = 'Onboarding Action';
        }
        if (submitButton) {
            submitButton.className = 'bg-red-500 text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
            submitButton.textContent = 'Submit';
        }
    }
}

function showOnboardingFailErrorMessage(message) {
    const errorDiv = document.getElementById('onboardingFailErrorMessage');
    const errorText = document.getElementById('onboardingFailErrorText');
    if (errorDiv && errorText) {
        errorText.textContent = message;
        errorDiv.style.display = 'block';
    }
}

function hideOnboardingFailErrorMessage() {
    const errorDiv = document.getElementById('onboardingFailErrorMessage');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

function confirmOnboardingFailSubmit() {
    // Hide any previous errors
    hideOnboardingFailErrorMessage();
    
    // Validate form
    const failReason = document.getElementById('onboarding_fail_reason').value;
    const interviewer = document.getElementById('onboarding_fail_interviewer').value;
    
    if (!failReason) {
        showOnboardingFailErrorMessage('Please select an action');
        return;
    }
    
    if (!interviewer) {
        showOnboardingFailErrorMessage('Please provide an interviewer name');
        return;
    }
    
    // If missed, validate new interview time
    if (failReason === 'missed') {
        const newInterviewTime = document.getElementById('onboarding_new_interview_time');
        if (newInterviewTime && !newInterviewTime.value) {
            showOnboardingFailErrorMessage('Please select a new onboarding date/time');
            return;
        }
    }
    
    // Update confirmation message and styling based on action
    const confirmationText = document.getElementById('onboardingConfirmationText');
    const confirmationModal = document.getElementById('onboardingFailConfirmationModal');
    const confirmationHeader = confirmationModal?.querySelector('.bg-\\[\\#0E335D\\]');
    const confirmButton = confirmationModal?.querySelector('button[onclick="submitOnboardingFail()"]');
    
    if (confirmationText) {
        if (failReason === 'missed') {
            confirmationText.innerHTML = 'Are you sure you want to <strong>RESCHEDULE</strong> this onboarding to a new date/time?';
            
            // Update confirmation modal styling for reschedule
            if (confirmationHeader) {
                confirmationHeader.className = 'flex justify-between items-center px-6 py-3 bg-[#2A5382] rounded-t-lg';
                const headerText = confirmationHeader.querySelector('h2');
                if (headerText) headerText.textContent = 'Confirm Reschedule';
            }
            if (confirmButton) {
                confirmButton.className = 'bg-[#2A5382] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
                confirmButton.textContent = 'Confirm Reschedule';
            }
        } else if (failReason === 'declined') {
            confirmationText.innerHTML = 'Are you sure you want to mark this onboarding as <strong>DECLINED</strong> and archive the applicant?';
            
            // Update confirmation modal styling for declined
            if (confirmationHeader) {
                confirmationHeader.className = 'flex justify-between items-center px-6 py-3 bg-[#0E335D] rounded-t-lg';
                const headerText = confirmationHeader.querySelector('h2');
                if (headerText) headerText.textContent = 'Confirm Archive';
            }
            if (confirmButton) {
                confirmButton.className = 'bg-red-500 text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
                confirmButton.textContent = 'Confirm Archive';
            }
        }
    }
    
    // Show confirmation modal
    showModal('onboardingFailConfirmationModal');
}

function hideOnboardingFailConfirmation() {
    hideModal('onboardingFailConfirmationModal');
}

function showOnboardingPassModal() {
    const preservedId = currentOnboardingId;
    hideOnboardingPassFailModal();
    currentOnboardingId = preservedId;
    
    if (window.currentApplicantInfo) {
        // Populate display fields
        const nameEl = document.getElementById('passTutorName');
        const emailEl = document.getElementById('passTutorEmail');
        const accountEl = document.getElementById('passAssignedAccount');
        
        if (nameEl) nameEl.textContent = window.currentApplicantInfo.name || '—';
        if (emailEl) emailEl.textContent = window.currentApplicantInfo.email || '—';
        if (accountEl) accountEl.textContent = window.currentApplicantInfo.account || '—';
        
        // Populate form fields if they exist
        const passEmailInput = document.getElementById('pass_email');
        const passAccountInput = document.getElementById('pass_account');
        if (passEmailInput) passEmailInput.value = window.currentApplicantInfo.email || '';
        if (passAccountInput) passAccountInput.value = window.currentApplicantInfo.account || '';
    }
    
    // Generate system ID and default password
    generateLocalCredentials();
    
    // Auto-generate unique username and email from backend
    generateUniqueUsername();
    generateUniqueEmail();
    
    showModal('onboardingPassModal');
}

function hideOnboardingPassModal() {
    hideModal('onboardingPassModal');
}

function showOnboardingPassConfirmation() {
    const preservedId = currentOnboardingId;
    hideOnboardingPassFailModal();
    currentOnboardingId = preservedId;
    showModal('onboardingPassConfirmationModal');
}

function hideOnboardingPassConfirmation() {
    hideModal('onboardingPassConfirmationModal');
}

function confirmOnboardingPass() {
    const preservedId = currentOnboardingId;
    hideOnboardingPassConfirmation();
    currentOnboardingId = preservedId;
    showOnboardingPassModal();
}

// ============================================================================
// USERNAME & CREDENTIALS GENERATION
// ============================================================================

async function generatePassUsername() {
    if (!currentOnboardingId) {
        console.error('No onboarding ID available');
        return;
    }
    
    try {
        const response = await fetch(`/demo/${currentOnboardingId}/generate-username`);
        if (!response.ok) throw new Error('Failed to generate username');
        
        const data = await response.json();
        
        const systemIdEl = document.getElementById('pass_system_id');
        const usernameEl = document.getElementById('pass_username');
        const passwordEl = document.getElementById('pass_password');
        
        if (systemIdEl && data.system_id) systemIdEl.value = data.system_id;
        if (usernameEl && data.username) usernameEl.value = data.username;
        if (passwordEl && data.password) passwordEl.value = data.password;
    } catch (error) {
        console.error('Error generating username:', error);
        generateLocalCredentials();
    }
}

async function generateLocalCredentials() {
    const passwordEl = document.getElementById('pass_password');
    if (passwordEl) passwordEl.value = 'OGSConnect2025';
    
    // Generate system ID from backend
    await generateSystemId();
}

async function generateSystemId() {
    const systemIdEl = document.getElementById('pass_system_id');
    if (!systemIdEl) return;
    
    systemIdEl.value = 'Generating...';
    
    try {
        const response = await fetch('/demos/generate-tutor-id', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) throw new Error('Failed to generate system ID');
        
        const data = await response.json();
        
        if (data.success && data.tutorID) {
            systemIdEl.value = data.tutorID;
        } else {
            throw new Error(data.error || 'Failed to generate system ID');
        }
    } catch (error) {
        console.error('Error generating system ID:', error);
        // Fallback to timestamp-based ID
        const timestamp = Date.now();
        systemIdEl.value = 'OGS-T' + String(timestamp).slice(-4);
    }
}

async function generateUniqueUsername() {
    if (!currentOnboardingId) {
        console.error('No onboarding ID available');
        return;
    }
    
    const usernameEl = document.getElementById('pass_username');
    if (!usernameEl) return;
    
    // Show loading state
    const originalValue = usernameEl.value;
    usernameEl.value = 'Generating...';
    usernameEl.disabled = true;
    
    try {
        // Send current username to avoid generating the same one
        const requestBody = {
            current_username: originalValue || null
        };
        
        const response = await fetch(`/demos/${currentOnboardingId}/generate-unique-username`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify(requestBody),
            credentials: 'same-origin'
        });
        
        if (!response.ok) throw new Error('Failed to generate username');
        
        const data = await response.json();
        
        if (data.success && data.username) {
            usernameEl.value = data.username;
            console.log('Generated unique username:', data.username);
        } else {
            throw new Error(data.error || 'Failed to generate username');
        }
    } catch (error) {
        console.error('Error generating username:', error);
        usernameEl.value = originalValue;
        alert('Failed to generate username. Please try again or enter manually.');
    } finally {
        usernameEl.disabled = false;
    }
}

async function generateUniqueEmail() {
    if (!currentOnboardingId) {
        console.error('No onboarding ID available');
        return;
    }
    
    const emailEl = document.getElementById('pass_company_email');
    const usernameEl = document.getElementById('pass_username');
    if (!emailEl) return;
    
    // Show loading state
    const originalValue = emailEl.value;
    emailEl.value = 'Generating...';
    emailEl.disabled = true;
    
    try {
        // Send current email and username to avoid generating the same one
        const requestBody = {
            current_email: originalValue || null
        };
        
        // Send current username if available
        if (usernameEl && usernameEl.value && usernameEl.value !== 'Generating...') {
            requestBody.username = usernameEl.value;
        }
        
        const response = await fetch(`/demos/${currentOnboardingId}/generate-unique-email`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify(requestBody),
            credentials: 'same-origin'
        });
        
        if (!response.ok) throw new Error('Failed to generate email');
        
        const data = await response.json();
        
        if (data.success && data.email) {
            emailEl.value = data.email;
            console.log('Generated unique email:', data.email);
        } else {
            throw new Error(data.error || 'Failed to generate email');
        }
    } catch (error) {
        console.error('Error generating email:', error);
        emailEl.value = originalValue;
        alert('Failed to generate email. Please try again or enter manually.');
    } finally {
        emailEl.disabled = false;
    }
}

// ============================================================================
// FORM SUBMISSIONS
// ============================================================================

async function submitOnboardingPassForm() {
    const submitButton = document.querySelector('button[onclick="submitOnboardingPassForm();"]');
    if (submitButton && submitButton.disabled) return;
    
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }
    
    if (!currentOnboardingId) {
        alert('Error: No onboarding ID available. Please refresh and try again.');
        if (submitButton) submitButton.disabled = false;
        return;
    }
    
    try {
        const formData = {
            system_id: document.getElementById('pass_system_id').value,
            username: document.getElementById('pass_username').value,
            company_email: document.getElementById('pass_company_email').value,
            password: document.getElementById('pass_password').value,
            interviewer: document.getElementById('onboarding_pass_interviewer').value,
            notes: document.getElementById('pass_notes').value,
            _token: getCsrfToken()
        };
        
        const response = await fetch(`/demos/${currentOnboardingId}/register-tutor`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify(formData),
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Registration failed');
        }
        
        if (data.success) {
            hideOnboardingPassModal();
            alert('Tutor registered successfully!');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(data.message || 'Registration failed');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error: ' + error.message);
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Complete Registration';
        }
    }
}

async function submitOnboardingFail() {
    // Hide confirmation modal
    hideOnboardingFailConfirmation();
    
    if (!currentOnboardingId) {
        showOnboardingFailErrorMessage('Error: No onboarding ID available');
        return;
    }
    
    const failReason = document.getElementById('onboarding_fail_reason').value;
    const interviewer = document.getElementById('onboarding_fail_interviewer').value;
    const notes = document.getElementById('onboarding_pass_notes').value;
    
    if (!failReason || !interviewer) {
        showOnboardingFailErrorMessage('Please fill in all required fields');
        return;
    }
    
    try {
        const formData = {
            fail_reason: failReason,
            interviewer: interviewer,
            notes: notes,
            _token: getCsrfToken()
        };
        
        if (failReason === 'missed') {
            const newInterviewTime = document.getElementById('onboarding_new_interview_time');
            if (newInterviewTime) {
                formData.new_demo_time = newInterviewTime.value;
            }
        }
        
        const response = await fetch(`/demo/${currentOnboardingId}/fail`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to process failure');
        }
        
        if (data.success) {
            hideOnboardingFailModal();
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to process failure');
        }
    } catch (error) {
        console.error('Error:', error);
        showOnboardingFailErrorMessage('Error: ' + error.message);
    }
}

// ============================================================================
// ONBOARDING ACTIONS
// ============================================================================

/**
 * Show confirmation modal for moving applicant to onboarding
 */
function showOnboardingConfirmationModal(applicationId, applicantName) {
    currentOnboardingId = applicationId;
    
    const modal = document.getElementById('onboardingConfirmationModal');
    if (!modal) {
        console.error('Onboarding confirmation modal not found');
        return;
    }
    
    const applicantNameEl = document.getElementById('onboardingApplicantName');
    if (applicantNameEl) {
        applicantNameEl.textContent = applicantName || 'this applicant';
    }
    
    modal.classList.remove('hidden');
}

/**
 * Move applicant to onboarding
 */
async function moveToOnboarding(applicationId) {
    if (!applicationId) {
        console.error('No application ID provided');
        return;
    }
    
    try {
        const response = await fetch(`/hiring-onboarding/applicant/${applicationId}/pass`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                _token: getCsrfToken()
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to move to onboarding');
        }
        
        if (data.success) {
            // Close modal and reload
            const modal = document.getElementById('onboardingConfirmationModal');
            if (modal) modal.classList.add('hidden');
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to move to onboarding');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    }
}

/**
 * Open archive/reschedule modal
 */
function openArchiveModal(applicationId) {
    currentOnboardingId = applicationId;
    
    const modal = document.getElementById('archiveModal');
    if (!modal) {
        console.error('Archive modal not found');
        return;
    }
    
    // Clear previous values
    const reasonField = document.getElementById('archive_reason');
    if (reasonField) reasonField.value = '';
    
    modal.classList.remove('hidden');
}

/**
 * Archive application with reason
 */
async function archiveApplication(applicationId, reason) {
    if (!applicationId) {
        console.error('No application ID provided');
        return;
    }
    
    if (!reason) {
        alert('Please provide a reason for archiving');
        return;
    }
    
    try {
        const response = await fetch(`/hiring-onboarding/applicant/${applicationId}/archive-reschedule`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                reason: reason,
                _token: getCsrfToken()
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to archive application');
        }
        
        if (data.success) {
            // Close modal and reload
            const modal = document.getElementById('archiveModal');
            if (modal) modal.classList.add('hidden');
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to archive application');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    }
}

// ============================================================================
// INITIALIZE
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Screening Modals JavaScript loaded successfully');
});
