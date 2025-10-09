<!-- Archive Modal -->
<div id="archiveModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 60;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-auto relative">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-3 bg-[#F65353] rounded-t-xl">
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
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F29090] focus:border-transparent"
                    placeholder="Enter interviewer name"
                    required>
            </div>
            
            <!-- Special Status Field - Only Declined and Not Recommended -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Archive Reason:</label>
                <div class="relative">
                    <select name="special_status" 
                        id="archive_special_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F29090] focus:border-transparent appearance-none bg-white"
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
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F29090] focus:border-transparent resize-none"
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
    <div class="bg-white rounded-lg shadow-lg w-96">
        <!-- Body -->
        <div class="p-6 text-center">
            <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-orange-500" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-6 4h8" />
                </svg>
            </div>
            
            <h3 class="font-bold text-lg" id="archiveConfirmationTitle">Confirm Archive</h3>
            <p class="text-gray-600 mt-2" id="archiveConfirmationMessage">
                Please confirm your action.
            </p>
            <div class="flex justify-center mb-4" id="archiveConfirmationIcon">
                <!-- Default icon, will be replaced by JS -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-orange-500" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-6 4h8" />
                </svg>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="flex justify-center gap-4 pb-6">
            <button onclick="hideArchiveConfirmation()"
                class="bg-gray-200 text-gray-700 px-6 py-2 rounded-full font-semibold hover:bg-gray-300 transition">
                Cancel
            </button>
            <button onclick="submitArchiveForm()"
                class="bg-[#F29090] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">
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
            titleElement.textContent = 'Applicant Declined';
            messageElement.innerHTML = `
                This applicant has been <span class="font-bold text-red-600">declined</span> and will be moved to the Archive.
                <br><br>
                <strong>Interviewer:</strong> ${interviewer}
                ${notes ? `<br><strong>Notes:</strong> ${notes}` : ''}
            `;
            iconElement.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            `;
            break;
            
        case 'not_recommended':
            titleElement.textContent = 'Applicant Not Recommended';
            messageElement.innerHTML = `
                This applicant has been marked as <span class="font-bold text-red-600">not recommended</span> and will be moved to the Archive.
                <br><br>
                <strong>Interviewer:</strong> ${interviewer}
                ${notes ? `<br><strong>Notes:</strong> ${notes}` : ''}
            `;
            iconElement.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            `;
            break;
            
        default:
            titleElement.textContent = 'Confirm Archive';
            messageElement.innerHTML = `
                This applicant will be archived and moved to the Archive section.
                <br><br>
                <strong>Interviewer:</strong> ${interviewer}
                ${notes ? `<br><strong>Notes:</strong> ${notes}` : ''}
            `;
            iconElement.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-orange-500" fill="none"
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
