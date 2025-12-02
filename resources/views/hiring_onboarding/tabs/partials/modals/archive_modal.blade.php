<!-- Archive Modal -->
<div id="archiveModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 60;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-auto relative">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-3 bg-[#1E40AF] rounded-t-xl">
            <h2 class="text-white text-lg font-bold">Archive Applicant</h2>
            <button type="button"
                onclick="closeArchiveModal()"
                class="text-white text-2xl font-bold hover:opacity-75">&times;
            </button>
        </div>
        
        <!-- Content -->
        <form id="archiveForm" action="" method="POST" class="px-6 py-6">
            @csrf
            @method('PATCH')
            
            <!-- Interviewer Field -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Interviewer:</label>
                <input type="text" 
                    name="interviewer"
                    id="archive_interviewer"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 bg-gray-50"
                    value="{{ Auth::guard('supervisor')->check() ? Auth::guard('supervisor')->user()->full_name : '' }}"
                    readonly
                    required>
            </div>
            
            <!-- Special Status Field - Only Declined and Not Recommended -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Archive Reason:</label>
                <div class="relative">
                    <select name="special_status" 
                        id="archive_special_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:border-transparent appearance-none bg-white"
                        required>
                        <option value="" disabled selected>-Select Reason-</option>
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
            
            <!-- Notes Field -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2">Notes:</label>
                <textarea name="notes" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:border-transparent resize-none"
                    placeholder="Enter reason for archiving this applicant..."></textarea>
            </div>
            
            <!-- Confirm Archive Button -->
            <div class="flex justify-center">
                <button type="button" onclick="showArchiveConfirmation()"
                    class="bg-[#F65353] text-white px-8 py-2 rounded-full font-bold hover:opacity-90 transition-opacity">
                    Confirm Archive
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Archive Confirmation Modal -->
<div id="archiveConfirmationModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 70;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4">
        <!-- Header -->
        <div class="bg-[#1E40AF] rounded-t-xl px-6 py-4">
            <h3 class="text-white text-xl font-bold text-center" id="archiveConfirmationTitle">Confirm Archive</h3>
        </div>
        
        <!-- Body -->
        <div class="p-6">
            <!-- Icon -->
            <div class="flex justify-center mb-4" id="archiveConfirmationIcon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-blue-500" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-6 4h8" />
                </svg>
            </div>
            
            <!-- Message -->
            <div class="text-center mb-4">
                <p class="text-gray-700 text-sm leading-relaxed" id="archiveConfirmationMessage">
                    Please confirm your action.
                </p>
            </div>
            
            <!-- Warning Message -->
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-red-700 font-medium">
                        Warning: This action cannot be undone. The applicant cannot be restored once archived.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="flex justify-center gap-4 px-6 pb-6">
            <button onclick="hideArchiveConfirmation()"
                class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Cancel
            </button>
            <button onclick="submitArchiveForm()"
                class="bg-[#1E40AF] text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-[#1E3A8A] transition-colors">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
function showArchiveConfirmation() {
    // Get the form element
    const form = document.getElementById('archiveForm');
    if (form) {
        // Check if form is valid
        if (form.checkValidity()) {
            // Get the selected archive reason and form data
            const archiveReason = document.getElementById('archive_special_status').value;
            const interviewer = document.getElementById('archive_interviewer').value;
            const notes = document.querySelector('textarea[name="notes"]').value;
            
            // Update confirmation modal based on archive reason
            updateArchiveConfirmationModal(archiveReason, interviewer, notes);
            
            // Show confirmation modal
            document.getElementById('archiveConfirmationModal').style.display = 'flex';
        } else {
            // Show validation errors
            form.reportValidity();
        }
    }
}

function updateArchiveConfirmationModal(reason, interviewer, notes) {
    const titleElement = document.getElementById('archiveConfirmationTitle');
    const messageElement = document.getElementById('archiveConfirmationMessage');
    const iconElement = document.getElementById('archiveConfirmationIcon');
    
    switch(reason) {
        case 'declined':
            titleElement.textContent = 'Archive Applicant - Declined';
            messageElement.innerHTML = `
                This applicant has been marked as <span class="font-bold text-blue-700">declined</span> and will be permanently moved to the Archive.
                <br><br>
                ${notes ? `<br><strong class="text-gray-800">Notes:</strong> <span class="text-gray-700">${notes}</span>` : ''}
            `;
            iconElement.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            `;
            break;
            
        case 'not_recommended':
            titleElement.textContent = 'Archive Applicant - Not Recommended';
            messageElement.innerHTML = `
                This applicant has been marked as <span class="font-bold text-blue-700">not recommended</span> and will be permanently moved to the Archive.
                <br><br>
                ${notes ? `<br><strong class="text-gray-800">Notes:</strong> <span class="text-gray-700">${notes}</span>` : ''}
            `;
            iconElement.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            `;
            break;
            
        default:
            titleElement.textContent = 'Confirm Archive';
            messageElement.innerHTML = `
                This applicant will be permanently archived and moved to the Archive section.
                <br><br>
                ${notes ? `<br><strong class="text-gray-800">Notes:</strong> <span class="text-gray-700">${notes}</span>` : ''}
            `;
            iconElement.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-blue-500" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-6 4h8" />
                </svg>
            `;
    }
}

function hideArchiveConfirmation() {
    document.getElementById('archiveConfirmationModal').style.display = 'none';
}

function submitArchiveForm() {
    // Get the form element
    const form = document.getElementById('archiveForm');
    if (form) {
        // Show loading state
        const confirmButton = document.querySelector('button[onclick="submitArchiveForm()"]');
        if (confirmButton) {
            confirmButton.disabled = true;
            confirmButton.innerHTML = 'Processing...';
        }
        
        // Submit the form
        form.submit();
    }
}

function closeArchiveModal() {
    document.getElementById('archiveModal').style.display = 'none';
}

function openArchiveModal(applicantId) {
    setArchiveFormAction(applicantId);
    document.getElementById('archiveModal').style.display = 'flex';
}
</script>
