<!-- Confirm Fail Modal -->
<div x-show="showFailModal" x-cloak
    style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 60;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50"
    x-transition
    data-current-attempts="{{ $application->attempt_count ?? 0 }}">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-auto relative">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-3 bg-[#234D7C] rounded-t-xl">
            <h2 class="text-white text-lg font-bold">Fail Applicant</h2>
            <button type="button"
                @click="closeFailModal()"
                class="text-white text-2xl font-bold hover:opacity-75">&times;
            </button>
        </div>
        
        <!-- Content -->
        <form id="failForm" action="{{ route('hiring_onboarding.applicant.fail', $application->application_id) }}" method="POST" class="px-6 py-6">
            @csrf
            @method('PATCH')
            
            <!-- Interviewer Field -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Interviewer:</label>
                <input type="text" 
                    name="interviewer"
                    id="interviewer"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:border-transparent bg-gray-100"
                    placeholder="Enter interviewer name"
                    value="{{ Auth::guard('supervisor')->check() ? Auth::guard('supervisor')->user()->full_name : '' }}"
                    required readonly>
            </div>
            
            <!-- Special Status Field -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Status:</label>
                <div class="relative">
                    <select name="special_status" 
                        id="special_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:border-transparent appearance-none bg-white"
                        required
                        onchange="toggleInterviewTime()">
                        <option value="" disabled selected>-Select Status-</option>
                        <option value="no_answer">No Answer</option>
                        <option value="re_schedule">Re-schedule</option>
                        <option value="declined">Declined</option>
                        <option value="not_recommended">Not Recommended</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                        <svg class="w-4 h-4 fill-current text-gray-400" viewBox="0 0 20 20">
                            <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <!-- Interview Time Field (shown only when re_schedule is selected) -->
            <div id="interview_time_field" class="mb-4" style="display: none;">
                <label class="block text-gray-700 text-sm font-medium mb-2">New Interview Time:</label>
                <input type="datetime-local" 
                    name="interview_time"
                    id="interview_time"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:border-transparent"
                    min="{{ now()->format('Y-m-d\TH:i') }}">
            </div>
            
            <!-- Notes Field -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2">Notes:</label>
                <textarea name="notes" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:border-transparent resize-none"
                    placeholder="Enter reason for the chosen status..."></textarea>
            </div>
            
            <!-- Confirm Fail Button -->
            <div class="flex justify-center">
                <button type="button" onclick="showConfirmation()"
                    class="bg-[#F65353] text-white px-6 py-2 rounded-lg font-bold hover:opacity-90 transition-opacity">
                    Fail
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 70;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-96">
        <!-- Body -->
        <div class="p-6 text-center">
            <div class="flex justify-center mb-4" id="confirmationIcon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-red-500" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-6 4h8" />
                </svg>
            </div>
            
            <h3 class="font-bold text-lg" id="confirmationTitle">Confirm Action</h3>
            <p class="text-gray-600 mt-2" id="confirmationMessage">
                Please confirm your action.
            </p>
        </div>

        <!-- Footer Buttons -->
        <div class="flex justify-center gap-4 pb-6">
            <button onclick="hideConfirmation()"
                class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-semibold hover:bg-gray-300 transition">
                Cancel
            </button>
            <button onclick="submitForm()"
                class="bg-[#F65353] text-white px-6 py-2 rounded-lg font-semibold hover:opacity-90 transition">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
function toggleInterviewTime() {
    const specialStatus = document.getElementById('special_status').value;
    const interviewTimeField = document.getElementById('interview_time_field');
    const interviewTimeInput = document.getElementById('interview_time');
    const notesField = document.querySelector('textarea[name="notes"]');
    
    if (specialStatus === 're_schedule') {
        interviewTimeField.style.display = 'block';
        interviewTimeInput.required = true;
    } else {
        interviewTimeField.style.display = 'none';
        interviewTimeInput.required = false;
        interviewTimeInput.value = '';
    }
    
    // Auto-populate notes based on status
    if (notesField) {
        const statusNotes = {
            'no_answer': 'No answer on call attempt. Will try again.',
            're_schedule': 'Applicant requested to reschedule the interview.',
            'declined': 'Applicant declined the position.',
            'not_recommended': 'Applicant did not meet the requirements during interview.'
        };
        
        if (statusNotes[specialStatus]) {
            notesField.value = statusNotes[specialStatus];
        } else {
            notesField.value = '';
        }
    }
}

function showConfirmation() {
    // Get the form element
    const form = document.getElementById('failForm');
    if (form) {
        // Check if form is valid
        if (form.checkValidity()) {
            // Get the selected status
            const specialStatus = document.getElementById('special_status').value;
            const interviewer = document.getElementById('interviewer').value;
            const notes = document.querySelector('textarea[name="notes"]').value;
            const interviewTime = document.getElementById('interview_time').value;
            
            // Update confirmation modal based on status
            updateConfirmationModal(specialStatus, interviewer, notes, interviewTime);
            
            // Show confirmation modal
            document.getElementById('confirmationModal').style.display = 'flex';
        } else {
            // Show validation errors
            form.reportValidity();
        }
    }
}

function updateConfirmationModal(status, interviewer, notes, interviewTime) {
    const titleElement = document.getElementById('confirmationTitle');
    const messageElement = document.getElementById('confirmationMessage');
    const iconElement = document.getElementById('confirmationIcon');
    
    switch(status) {
        case 'declined':
            titleElement.textContent = 'Applicant Declined';
            messageElement.innerHTML = `
                This applicant has been <span class="font-bold text-gray-600">declined</span> and will be moved to the Archive.
                <br><br>
            `;
            iconElement.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            `;
            break;
            
        case 'not_recommended':
            titleElement.textContent = 'Applicant Not Recommended';
            messageElement.innerHTML = `
                This applicant has been marked as <span class="font-bold text-gray-600">not recommended</span> and will be moved to the Archive.
                <br><br>
            `;
            iconElement.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            `;
            break;
            
        case 'no_answer':
            const currentAttempts = getCurrentAttemptCount();
            const nextAttemptNumber = currentAttempts + 1; // this attempt about to be recorded
            const remainingAttempts = Math.max(0, 3 - nextAttemptNumber);
            
            titleElement.textContent = 'No Answer';
            messageElement.innerHTML = `
                This call attempt (#${nextAttemptNumber}) will be recorded.
                <br><br>
                <span class="font-bold">${remainingAttempts} attempt${remainingAttempts === 1 ? '' : 's'} remaining</span> before automatic archiving.
                <br><br>
            `;
            iconElement.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
            `;
            break;
            
        case 're_schedule':
            const formattedTime = interviewTime ? new Date(interviewTime).toLocaleString() : 'Not specified';
            titleElement.textContent = 'Interview Rescheduled';
            messageElement.innerHTML = `
                This applicant has been rescheduled for a new interview.
                <br><br>
                <strong>New Interview Time:</strong> <br> <span class=" text-gray-500">${formattedTime}</span>
                <br><br>
            `;
            iconElement.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            `;
            break;
            
        default:
            titleElement.textContent = 'Confirm Action';
            messageElement.textContent = 'Please confirm your action.';
    }
}

function getCurrentAttemptCount() {
    // Get the current attempt count from the data attribute
    const modal = document.querySelector('[data-current-attempts]');
    return modal ? parseInt(modal.getAttribute('data-current-attempts')) : 0;
}

function hideConfirmation() {
    document.getElementById('confirmationModal').style.display = 'none';
}

function submitForm() {
    // Get the form element
    const form = document.getElementById('failForm');
    if (form) {
        // Show loading state
        const confirmButton = document.querySelector('button[onclick="submitForm()"]');
        if (confirmButton) {
            confirmButton.disabled = true;
            confirmButton.innerHTML = 'Processing...';
        }
        
        // Submit the form
        form.submit();
    }
}
</script>