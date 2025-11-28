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
    document.getElementById('modal_interviewer').value = data.interviewer || '';
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
    
    // Handle schedule
    const schedule = data.schedule || data.demo_schedule || data.interview_time || '';
    if (schedule) {
        const date = new Date(schedule);
        if (!isNaN(date.getTime())) {
            document.getElementById('modal_schedule').value = date.toISOString().slice(0, 16);
        }
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
        <br><strong>Assigned Account:</strong> ${assignedAccount}
        ${schedule ? `<br><strong>Schedule:</strong> ${new Date(schedule).toLocaleString()}` : ''}
        <br><br>
        <strong>Interviewer:</strong> ${interviewer}
        ${notes ? `<br><strong>Notes:</strong> ${notes}` : ''}
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
    if (failModalHeader) failModalHeader.className = 'flex justify-between items-center px-6 py-3 bg-[#F65353] rounded-t-lg';
    if (failSubmitButton) failSubmitButton.className = 'bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
    
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
    
    // Change colors based on fail reason
    if (failReason === 'transfer_account') {
        if (failModalHeader) failModalHeader.className = 'flex justify-between items-center px-6 py-3 bg-blue-500 rounded-t-lg';
        if (failSubmitButton) failSubmitButton.className = 'bg-blue-500 text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
    } else {
        if (failModalHeader) failModalHeader.className = 'flex justify-between items-center px-6 py-3 bg-[#F65353] rounded-t-lg';
        if (failSubmitButton) failSubmitButton.className = 'bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
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
                This applicant has <span class="font-bold text-orange-600">missed</span> their interview.
                <br><br>
                <strong>Action:</strong> Reschedule interview
                ${newInterviewTime ? `<br><strong>New Time:</strong> ${new Date(newInterviewTime).toLocaleString()}` : ''}
                <br><br>
                <strong>Interviewer:</strong> ${interviewer}
                ${notes ? `<br><strong>Notes:</strong> ${notes}` : ''}
            `;
            break;
            
        case 'declined':
            titleElement.textContent = 'Confirm Declined';
            messageElement.innerHTML = `
                This applicant has been <span class="font-bold text-red-600">declined</span> and will be moved to the Archive.
                <br><br>
                <strong>Interviewer:</strong> ${interviewer}
                ${notes ? `<br><strong>Notes:</strong> ${notes}` : ''}
            `;
            break;
            
        case 'not_recommended':
            titleElement.textContent = 'Confirm Not Recommended';
            messageElement.innerHTML = `
                This applicant has been marked as <span class="font-bold text-red-600">not recommended</span> and will be moved to the Archive.
                <br><br>
                <strong>Interviewer:</strong> ${interviewer}
                ${notes ? `<br><strong>Notes:</strong> ${notes}` : ''}
            `;
            break;
            
        case 'transfer_account':
            const assignedAccount = document.getElementById('transfer_assigned_account').value;
            const newStatus = document.getElementById('transfer_status').value;
            const schedule = document.getElementById('transfer_schedule').value;
            titleElement.textContent = 'Confirm Account Transfer';
            messageElement.innerHTML = `
                This applicant will be <span class="font-bold text-blue-600">transferred</span> to a different account.
                <br><br>
                <strong>New Account:</strong> ${assignedAccount}
                <br><strong>New Status:</strong> ${newStatus}
                ${schedule ? `<br><strong>Schedule:</strong> ${new Date(schedule).toLocaleString()}` : ''}
                <br><br>
                <strong>Interviewer:</strong> ${interviewer}
                ${notes ? `<br><strong>Notes:</strong> ${notes}` : ''}
            `;
            break;
            
        default:
            titleElement.textContent = 'Confirm Fail Action';
            messageElement.innerHTML = `
                This applicant has <span class="font-bold text-red-600">failed</span> the current stage.
                ${notes ? `<br><br><strong>Notes:</strong> ${notes}` : ''}
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
    const failReason = document.getElementById('demo_fail_reason').value;
    const interviewer = document.getElementById('demo_fail_interviewer').value;
    
    if (!failReason) {
        alert('Please select a failure reason');
        return;
    }
    
    if (!interviewer) {
        alert('Please enter interviewer name');
        return;
    }
    
    showModal('demoFailConfirmationModal');
}

function hideDemoFailConfirmation() {
    hideModal('demoFailConfirmationModal');
}

function toggleDemoFailFields() {
    const failReason = document.getElementById('demo_fail_reason').value;
    const newDemoTimeSection = document.getElementById('demo_new_demo_time_section');
    
    if (failReason === 'missed' && newDemoTimeSection) {
        newDemoTimeSection.style.display = 'block';
    } else if (newDemoTimeSection) {
        newDemoTimeSection.style.display = 'none';
    }
}

async function submitDemoFailAction() {
    const failReason = document.getElementById('demo_fail_reason').value;
    const interviewer = document.getElementById('demo_fail_interviewer').value;
    const notes = document.getElementById('demo_fail_notes').value;
    
    try {
        let requestData = {
            fail_reason: failReason,
            interviewer: interviewer,
            notes: notes
        };
        
        if (failReason === 'missed') {
            const newDemoTime = document.getElementById('demo_new_demo_time').value;
            if (!newDemoTime) {
                alert('Please select a new demo time');
                return;
            }
            requestData.new_demo_time = newDemoTime;
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
            hideDemoFailConfirmation();
            hideDemoConfirmationModal();
            closeEditModal();
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
        const response = await fetch(`/demo/${currentDemoId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: 'demo_pass'
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to process demo pass');
        }

        const data = await response.json();
        if (data.success) {
            hideDemoPassConfirmation();
            closeEditModal();
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
// ONBOARDING PASS/FAIL MODAL
// ============================================================================

function showOnboardingPassFailModal(onboardingId, applicantName, account, schedule, email) {
    currentOnboardingId = onboardingId;
    
    document.getElementById('onboardingApplicantName').textContent = applicantName;
    document.getElementById('onboardingAccount').textContent = account || '—';
    document.getElementById('onboardingSchedule').textContent = schedule || '—';
    
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
    
    newDemoTimeSection.style.display = failReason === 'missed' ? 'block' : 'none';
}

function showOnboardingPassModal() {
    const preservedId = currentOnboardingId;
    hideOnboardingPassFailModal();
    currentOnboardingId = preservedId;
    
    if (window.currentApplicantInfo) {
        document.getElementById('pass_email').value = window.currentApplicantInfo.email || '';
        document.getElementById('pass_account').value = window.currentApplicantInfo.account || '';
    }
    
    generatePassUsername();
    generateLocalCredentials();
    
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
        
        if (data.system_id) document.getElementById('pass_system_id').value = data.system_id;
        if (data.username) document.getElementById('pass_username').value = data.username;
        if (data.password) document.getElementById('pass_password').value = data.password;
    } catch (error) {
        console.error('Error generating username:', error);
        generateLocalCredentials();
    }
}

function generateLocalCredentials() {
    const timestamp = Date.now();
    const systemId = 'OGS-T' + String(timestamp).slice(-4);
    
    let username = '';
    if (window.currentApplicantInfo && window.currentApplicantInfo.name) {
        const names = window.currentApplicantInfo.name.trim().split(' ');
        username = names.map(n => n.charAt(0).toLowerCase()).join('') + String(timestamp).slice(-3);
    } else {
        username = 'user' + String(timestamp).slice(-4);
    }
    
    document.getElementById('pass_system_id').value = systemId;
    document.getElementById('pass_username').value = username;
    document.getElementById('pass_password').value = 'OGS2024!';
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
            password: document.getElementById('pass_password').value,
            personal_email: document.getElementById('pass_email').value,
            assigned_account: document.getElementById('pass_account').value,
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
        
        if (!response.ok) throw new Error('Registration failed');
        
        const data = await response.json();
        
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
    if (!currentOnboardingId) {
        alert('Error: No onboarding ID available');
        return;
    }
    
    const failReason = document.getElementById('onboarding_fail_reason').value;
    const interviewer = document.getElementById('onboarding_fail_interviewer').value;
    const notes = document.getElementById('onboarding_fail_notes').value;
    
    if (!failReason || !interviewer) {
        alert('Please fill in all required fields');
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
            formData.new_demo_time = document.getElementById('onboarding_new_demo_time').value;
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
        
        if (!response.ok) throw new Error('Failed to process failure');
        
        const data = await response.json();
        
        if (data.success) {
            hideOnboardingFailModal();
            alert('Onboarding failure processed successfully');
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to process failure');
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
