<!-- Confirm Pass Modal -->
<div x-show="showPassModal" x-cloak
    style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 60;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50"
    x-transition>
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-auto relative">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-3 bg-[#65DB7F] rounded-t-xl">
            <h2 class="text-white text-lg font-bold">Confirm Pass</h2>
            <button type="button"
                @click="closePassModal()"
                class="text-white text-2xl font-bold hover:opacity-75">&times;
            </button>
        </div>
        
    <form id="passForm" action="{{ route('hiring_onboarding.applicant.pass', $application->id) }}" method="POST">
        @csrf
        @method('PATCH')
        <!-- Content -->
        <div class="px-6 py-6">
            <!-- Interviewer Field -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Interviewer:</label>
                <input type="text" 
                    name="interviewer"
                    id="interviewer"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#65DB7F] focus:border-transparent"
                    placeholder="Enter interviewer name" 
                    value="{{ Auth::guard('supervisor')->check() ? Auth::guard('supervisor')->user()->full_name : '' }}"
                    required>
            </div>


            <!-- Assign Account Field -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Assign Account:</label>
                <div class="relative">
                    <select 
                        name="assigned_account"
                        id="assigned_account"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#65DB7F] focus:border-transparent appearance-none bg-white">
                        <option value="" selected disabled>Select an Account</option>
                        <option value="tutlo">Tutlo</option>
                        <option value="talk915">Talk915</option>
                        <option value="gl5">GL5</option>
                        <option value="babilala">BabiLala</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                        <svg class="w-4 h-4 fill-current text-gray-400" viewBox="0 0 20 20">
                            <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Current Status in Demo Field -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Current Status in Demo:</label>
                <div class="relative">
                    <select 
                        name="next_status" 
                        id="next_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#65DB7F] focus:border-transparent appearance-none bg-white"
                        onchange="toggleDemoSchedule()">
                        <option value="" selected disabled>Select Current Status</option>
                        <option value="screening">Screening</option>
                        <option value="training">Training</option>
                        <option value="demo">Demo</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                        <svg class="w-4 h-4 fill-current text-gray-400" viewBox="0 0 20 20">
                            <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <!-- Demo Schedule Field -->
            <div id="demo_schedule_field" class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Demo Schedule:</label>
                <input type="datetime-local" 
                    name="demo_schedule"
                    id="demo_schedule"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#65DB7F] focus:border-transparent"
                    min="{{ now()->format('Y-m-d\TH:i') }}">
            </div>

            <!-- Notes Field -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2">Notes:</label>
                <textarea rows="4" name="notes"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#65DB7F] focus:border-transparent resize-none"
                    placeholder="Enter reason for the chosen status..."></textarea>
            </div>
            
            <!-- Submit Button -->
            <div class="flex justify-center">
                <button type="button" onclick="showPassConfirmation()"
                    class="bg-[#65DB7F] text-white px-8 py-2 rounded-full font-bold hover:opacity-90 transition-opacity">
                    Confirm Pass
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Confirmation Modal -->
<div id="passConfirmationModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 70;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-96">
        <!-- Body -->
        <div class="p-6 text-center">
            <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-green-500" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            
            <h3 class="font-bold text-lg">Applicant Passed</h3>
            <p class="text-gray-600 mt-2">
                This applicant has been <span class="font-bold text-green-600">passed</span> and moved to the Demo stage.
            </p>
        </div>

        <!-- Footer Buttons -->
        <div class="flex justify-center gap-4 pb-6">
            <button onclick="submitPassForm()"
                class="bg-[#65DB7F] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
// Initialize the demo schedule field on page load
document.addEventListener('DOMContentLoaded', function() {
    const demoScheduleField = document.getElementById('demo_schedule_field');
    const demoScheduleInput = document.getElementById('demo_schedule');
    
    // Show the field by default since everyone who passes goes to demo
    demoScheduleField.style.display = 'block';
    demoScheduleInput.required = true;
});

function toggleDemoSchedule() {
    const nextStatus = document.getElementById('next_status').value;
    const demoScheduleField = document.getElementById('demo_schedule_field');
    const demoScheduleInput = document.getElementById('demo_schedule');
    
    // Always show demo schedule since everyone who passes goes to demo
    demoScheduleField.style.display = 'block';
    demoScheduleInput.required = true;
}

function showPassConfirmation() {
    // Get the form element
    const form = document.getElementById('passForm');
    if (form) {
        // Check if form is valid
        if (form.checkValidity()) {
            // Show confirmation modal
            document.getElementById('passConfirmationModal').style.display = 'flex';
        } else {
            // Show validation errors
            form.reportValidity();
        }
    }
}

function hidePassConfirmation() {
    document.getElementById('passConfirmationModal').style.display = 'none';
}

function submitPassForm() {
    // Get the form element
    const form = document.getElementById('passForm');
    if (form) {
        // Show loading state
        const confirmButton = document.querySelector('button[onclick="submitPassForm()"]');
        if (confirmButton) {
            confirmButton.disabled = true;
            confirmButton.innerHTML = 'Processing...';
        }
        
        // Submit the form
        form.submit();
    }
}
</script>