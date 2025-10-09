@php
    // Common arrays for reuse
    $times = [
        '12:00 AM', '1:00 AM', '2:00 AM', '3:00 AM', '4:00 AM', '5:00 AM', '6:00 AM',
        '7:00 AM', '8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM', '12:00 PM',
        '1:00 PM', '2:00 PM', '3:00 PM', '4:00 PM', '5:00 PM', '6:00 PM', '7:00 PM',
        '8:00 PM', '9:00 PM', '10:00 PM', '11:00 PM'
    ];

    $accounts = ['tutlo', 'talk915', 'gl5', 'babilala'];
    $statuses = ['screening', 'training', 'demo', 'onboarding'];
@endphp

<!-- Main Modal Container -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;"
     class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-auto relative">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-3 bg-[#0E335D] rounded-t-xl">
            <h2 class="text-white text-lg font-bold">Review Applicant Details</h2>
            <button type="button" onclick="closeEditModal()" class="text-white text-2xl font-bold hover:opacity-75">&times;</button>
        </div>
        
        <!-- Error Message Area -->
        <div id="modalError" class="hidden px-6 py-3 bg-red-50 border-l-4 border-red-400">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700" id="modalErrorMessage">
                        An error occurred while loading the data.
                    </p>
                </div>
            </div>
        </div>
        
        <form id="editForm" action="" method="POST">
            @csrf
            @method('PATCH')

            <!-- Content -->
            <div class="px-6 py-6 space-y-4">
                <!-- Interviewer -->
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Interviewer:</label>
                    <input type="text" name="interviewer" id="modal_interviewer" disabled readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-700 cursor-not-allowed"
                        placeholder="Enter interviewer name"
                        value="{{ Auth::guard('supervisor')->check() ? Auth::guard('supervisor')->user()->full_name : '' }}">
                </div>

                <!-- Email (hidden, for data passing) -->
                <input type="hidden" name="email" id="modal_email">

                <!-- Assigned Account -->
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Assigned Account:</label>
                    <select name="assigned_account" id="modal_assigned_account" disabled
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-700 cursor-not-allowed">
                        <option value="" selected disabled>Select an Account</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account }}">{{ ucwords(str_replace('_', ' ', $account)) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Hiring Status -->
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Current Status:</label>
                    <select name="hiring_status" id="modal_hiring_status" disabled
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-700 cursor-not-allowed">
                        <option value="" selected disabled>Select Status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Schedule (hidden for onboarding applicants) -->
                <div id="scheduleSection">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Schedule:</label>
                    <input type="datetime-local" name="schedule" id="modal_schedule" disabled
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-700 cursor-not-allowed">
                </div>

                <!-- Notes (hidden for onboarding applicants) -->
                <div id="notesSection">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Notes:</label>
                    <textarea name="notes" id="modal_notes" rows="3" disabled
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 resize-none cursor-not-allowed" 
                        placeholder="N/A"></textarea>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-center pt-2 space-x-4">
                    <button type="button" onclick="showFailOptionsModal()"
                        class="bg-[#F65353] text-white px-8 py-2 rounded-full font-bold hover:opacity-90 transition-opacity">
                        FAILED
                    </button>
                    <button type="button" onclick="showPassModal()"
                        class="bg-[#65DB7F] text-white px-8 py-2 rounded-full font-bold hover:opacity-90 transition-opacity">
                        PASSED
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Fail Confirmation Modal -->
<div id="failConfirmationModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 10006;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-96">
        <!-- Body -->
        <div class="p-6 text-center">
            <div class="flex justify-center mb-4" id="failConfirmationIcon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-red-500" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            
            <h3 class="font-bold text-lg" id="failConfirmationTitle">Confirm Fail Action</h3>
            <p class="text-gray-600 mt-2" id="failConfirmationMessage">
                Please confirm your action.
            </p>
        </div>

        <!-- Footer Buttons -->
        <div class="flex justify-center gap-4 pb-6">
            <button onclick="hideFailConfirmation()"
                class="bg-gray-200 text-gray-700 px-6 py-2 rounded-full font-semibold hover:bg-gray-300 transition">
                Cancel
            </button>
            <button onclick="submitFailAction()"
                class="bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">
                Confirm
            </button>
        </div>
    </div>
</div>

<!-- Pass Modal -->
<div id="passModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 10005;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-auto relative">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-3 bg-[#65DB7F] rounded-t-xl">
            <h2 class="text-white text-lg font-bold">Confirm Pass</h2>
            <button type="button" onclick="hidePassModal()" class="text-white text-2xl font-bold hover:opacity-75">&times;</button>
        </div>
        
        <!-- Error Message Area -->
        <div id="passModalError" class="hidden px-6 py-3 bg-red-50 border-l-4 border-red-400">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700" id="passModalErrorMessage">
                        An error occurred while processing the request.
                    </p>
                </div>
            </div>
        </div>
        
        <form id="passForm" action="" method="POST" class="px-6 py-6">
            @csrf
            @method('PATCH')
            
            <!-- Interviewer Field -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Interviewer:</label>
                <input type="text" name="interviewer" id="pass_interviewer" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#65DB7F] focus:border-transparent"
                    placeholder="Enter interviewer name">
            </div>


            <!-- Assign Account Field (Hidden - will use current account) -->
            <input type="hidden" name="assigned_account" id="pass_assigned_account">

            <!-- Next Status Field -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Next Status:</label>
                <div class="relative">
                    <select name="next_status" id="pass_next_status" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#65DB7F] focus:border-transparent appearance-none bg-white"
                        onchange="togglePassSchedule()">
                        <option value="" selected disabled>Select Next Status</option>
                        <!-- Options will be populated by JavaScript based on current status -->
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                        <svg class="w-4 h-4 fill-current text-gray-400" viewBox="0 0 20 20">
                            <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" fill-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <!-- Schedule Field -->
            <div id="pass_schedule_field" class="mb-4" style="display: none;">
                <label class="block text-gray-700 text-sm font-medium mb-2">Schedule:</label>
                <input type="datetime-local" name="next_schedule" id="pass_demo_schedule"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#65DB7F] focus:border-transparent"
                    min="{{ now()->format('Y-m-d\TH:i') }}">
            </div>

            <!-- Notes Field -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2">Notes:</label>
                <textarea name="notes" id="pass_notes" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#65DB7F] focus:border-transparent resize-none"
                    placeholder="Enter reason for passing..."></textarea>
            </div>
            
            <!-- Submit Button -->
            <div class="flex justify-center">
                <button type="button" onclick="showPassConfirmation()"
                    class="bg-[#65DB7F] text-white px-8 py-2 rounded-full font-bold hover:opacity-90 transition-opacity">
                    Confirm Pass
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Pass Confirmation Modal -->
<div id="passConfirmationModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 10006;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-96">
        <!-- Body -->
        <div class="p-6 text-center">
            <div class="flex justify-center mb-4" id="passConfirmationIcon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-green-500" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            
            <h3 class="font-bold text-lg" id="passConfirmationTitle">Confirm Pass</h3>
            <p class="text-gray-600 mt-2" id="passConfirmationMessage">
                Please confirm your action.
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

<!-- Pass/Fail Confirmation Modal -->
<div id="passFailConfirmationModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 10005;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-96">
        <!-- Body -->
        <div class="p-6 text-center">
            <div class="flex justify-center mb-4" id="passFailConfirmationIcon">
                <!-- Default icon, will be replaced by JS -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-green-500" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            
            <h3 class="font-bold text-lg" id="passFailConfirmationTitle">Confirm Action</h3>
            <p class="text-gray-600 mt-2" id="passFailConfirmationMessage">
                Please confirm your action.
            </p>
        </div>

        <!-- Footer Buttons -->
        <div class="flex justify-center gap-4 pb-6">
            <button onclick="confirmPassFailAction()"
                class="bg-[#65DB7F] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">
                Confirm
            </button>
        </div>
    </div>
</div>

<!-- Next Step Modal -->
<div id="nextStepModal" style="display: none; position: fixed; inset: 0; z-index: 10000;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center px-6 py-3 bg-[#0E335D] rounded-t-lg">
            <h2 class="text-white font-bold text-lg">Update Applicant Status</h2>
            <button onclick="hideNextStepModal()" class="text-white text-2xl font-bold hover:opacity-75">&times;</button>
        </div>

        <form id="nextStepForm" action="" method="POST" class="px-6 py-6 space-y-4">
            @csrf
            @method('PATCH')
            
            <!-- Next Hiring Status -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Next Status:</label>
                <select id="next_status" name="next_status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#65DB7F]" required>
                </select>
            </div>

            <!-- Next Schedule -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Next Schedule:</label>
                <input type="datetime-local" id="next_schedule" name="next_schedule"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#65DB7F]"
                    min="{{ now()->format('Y-m-d\TH:i') }}" required>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Notes:</label>
                <textarea id="next_notes" name="next_notes" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#65DB7F] resize-none"></textarea>
            </div>

            <!-- Buttons -->
            <div class="flex justify-center gap-4 pt-4">
                <button type="button" onclick="hideNextStepModal()"
                    class="bg-gray-200 text-gray-700 px-6 py-2 rounded-full font-semibold hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button type="button" onclick="submitNextStepForm()"
                    class="bg-[#65DB7F] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Pass Applicant Confirmation Modal -->
<div id="passApplicantConfirmModal" style="display: none; position: fixed; inset: 0; z-index: 10001;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-96">
        <div class="p-6 text-center">
            <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86l-7 12A1 1 0 004.1 17h15.8a1 1 0 00.81-1.5l-7-12a1 1 0 00-1.62 0z" />
                </svg>
            </div>
            <h3 class="font-bold text-lg">Pass Applicant</h3>
            <p class="text-gray-600 mt-2">Is the applicant qualified? This applicant will be for <span class="font-semibold">Hiring</span></p>
        </div>

        <div class="flex justify-center gap-4 pb-6">
            <button onclick="confirmPassSubmit()" class="bg-[#0E335D] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">Confirm</button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successApplicantModal" style="display: none; position: fixed; inset: 0; z-index: 10002;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-96">
        <div class="p-6 text-center">
            <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="font-bold text-lg">Success!</h3>
            <p class="text-gray-600 mt-2">Applicant is ready for registration.</p>
        </div>

        <div class="flex justify-center pb-6">
            <button onclick="registerApplicant()" class="bg-[#0E335D] text-white px-8 py-2 rounded-full font-semibold hover:opacity-90 transition">
                Register Applicant
            </button>
        </div>
    </div>
</div>

<!-- Employee Registration Modal -->
<div id="employeeRegistrationModal" style="display: none; position: fixed; inset: 0; z-index: 10003;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-3 bg-[#0E335D] rounded-t-lg">
            <h2 class="text-white font-bold text-lg">Register New Tutor</h2>
            <button onclick="hideEmployeeRegistrationModal()" class="text-white text-2xl font-bold hover:opacity-75">&times;</button>
        </div>

        <form id="employeeRegistrationForm" class="px-6 py-6 space-y-4">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            
            <!-- Full Name -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Full Name:</label>
                <input type="text" id="name" name="name" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#0E335D]"
                    placeholder="Enter full name">
            </div>

            <!-- Assigned Account (Read-only, auto-filled) -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Assigned Account:</label>
                <input type="text" id="reg_assigned_account" name="assigned_account" required readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-700 cursor-not-allowed">
            </div>

            <!-- Personal Email (Read-only, auto-filled) -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Personal Email:</label>
                <input type="email" id="personal_email" name="personal_email" required readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-700 cursor-not-allowed">
            </div>

            <!-- Username (Auto-generated) -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Username:</label>
                <input type="text" id="username" name="username" required readonly
                    value="OGS-T{{ str_pad(\App\Models\Tutor::count() + 1, 4, '0', STR_PAD_LEFT) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-700 cursor-not-allowed">
            </div>

            <!-- Password -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Password:</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#0E335D]"
                    placeholder="Enter password">
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#0E335D]"
                    placeholder="Confirm password">
            </div>

            <!-- Hidden Fields -->
            <input type="hidden" name="status" value="active">

            <!-- Submit Button -->
            <div class="flex justify-center pt-4">
                <button type="button" onclick="submitEmployeeRegistration()"
                    class="bg-[#0E335D] text-white px-8 py-2 rounded-full font-semibold hover:opacity-90 transition">
                    Complete Registration
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Registration Success Modal -->
<div id="registrationSuccessModal" style="display: none; position: fixed; inset: 0; z-index: 10004;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-96">
        <div class="p-6 text-center">
            <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="font-bold text-lg">Registration Successful!</h3>
            <p class="text-gray-600 mt-2">The employee has been successfully registered.</p>
        </div>
    </div>
</div>

<!-- Fail Options Modal -->
<div id="failOptionsModal" style="display: none; position: fixed; inset: 0; z-index: 10005;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
        <div id="failModalHeader" class="flex justify-between items-center px-6 py-3 bg-[#F65353] rounded-t-lg">
            <h2 class="text-white font-bold text-lg">Select Failure Reason</h2>
            <button onclick="hideFailOptionsModal()" class="text-white text-2xl font-bold hover:opacity-75">&times;</button>
        </div>
        
        <!-- Error Message Area -->
        <div id="failModalError" class="hidden px-6 py-3 bg-red-50 border-l-4 border-red-400">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700" id="failModalErrorMessage">
                        An error occurred while processing the request.
                    </p>
                </div>
            </div>
        </div>

        <div class="px-6 py-6 space-y-4">
            <!-- Interviewer Field - Always visible at the top -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Interviewer: <span class="text-red-500">*</span></label>
                <input type="text" id="fail_interviewer" name="fail_interviewer" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]"
                    placeholder="Enter interviewer name">
            </div>

            <!-- Failure Reason Selection -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Failure Reason:</label>
                <select id="fail_reason" name="fail_reason" onchange="toggleFailFields()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]">
                    <option value="" disabled selected>Select a reason</option>
                    <option value="missed">Missed Interview</option>
                    <option value="declined">Declined</option>
                    <option value="not_recommended">Not Recommended</option>
                    <option value="transfer_account">Transfer Account</option>
                </select>
            </div>

            <!-- New Interview Time (for missed) -->
            <div id="new_interview_time_section" style="display: none;">
                <label class="block text-gray-700 text-sm font-medium mb-2">New Interview Time:</label>
                <input type="datetime-local" id="new_interview_time" name="new_interview_time"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]"
                    min="{{ now()->format('Y-m-d\TH:i') }}">
            </div>

            <!-- Transfer Account Fields -->
            <div id="transfer_account_section" style="display: none;" class="space-y-6">
                <!-- Assigned Account -->
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">New Assigned Account:</label>
                    <select id="transfer_assigned_account" name="transfer_assigned_account"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="" disabled selected>Select a different account</option>
                        <!-- Options will be populated by JavaScript -->
                    </select>
                </div>

                <!-- New Status -->
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">New Status:</label>
                    <select id="transfer_status" name="transfer_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="" disabled selected>Select new status</option>
                        <option value="screening">Screening</option>
                        <option value="training">Training</option>
                        <option value="demo">Demo</option>
                    </select>
                </div>

                <!-- Schedule -->
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">New Interview Schedule:</label>
                    <input type="datetime-local" id="transfer_schedule" name="transfer_schedule"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        min="{{ now()->format('Y-m-d\TH:i') }}">
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Notes:</label>
                <textarea id="fail_notes" name="fail_notes" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353] resize-none"
                    placeholder="Enter notes about the failure reason..."></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center gap-4 pt-4">
                <button type="button" onclick="hideFailOptionsModal()"
                    class="bg-gray-200 text-gray-700 px-6 py-2 rounded-full font-semibold hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button id="failSubmitButton" type="button" onclick="showFailConfirmation()"
                    class="bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">
                    Submit
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Demo Details Confirmation Modal -->
<div id="demoDetailsConfirmationModal" style="display: none; position: fixed; inset: 0; z-index: 10005;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-96">
        <div class="flex justify-between items-center px-6 py-3 bg-[#2A5382] rounded-t-lg">
            <h2 class="text-white font-bold text-lg">Review Demo Performance</h2>
            <button onclick="hideDemoDetailsConfirmation()" class="text-white text-2xl font-bold hover:opacity-75">&times;</button>
        </div>

        <div class="p-6 text-center">
            <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-[#2A5382]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="font-bold text-lg text-[#2A5382] mb-2" id="demoDetailsApplicantName">Applicant Name</h3>
            <p class="text-gray-600 mb-4">
                Review the applicant's demo performance and make your final hiring decision.
            </p>
            <p class="text-sm text-gray-500 mb-6">
                You will be redirected to the applicant details page where you can scroll down to see the FAILED/PASSED buttons.
            </p>
        </div>

        <div class="flex justify-center gap-4 pb-6">
            <button onclick="hideDemoDetailsConfirmation()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-full font-semibold hover:bg-gray-300 transition">Cancel</button>
            <button onclick="proceedToApplicantDetails()" class="bg-[#2A5382] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">View Details</button>
        </div>
    </div>
</div>

<!-- Demo Confirmation Modal -->
<div id="demoConfirmationModal" style="display: none; position: fixed; inset: 0; z-index: 10005;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl">
        <div class="flex justify-between items-center px-6 py-3 bg-[#2A5382] rounded-t-lg">
            <h2 class="text-white font-bold text-lg">Review Demo & Confirm Final Decision</h2>
            <button onclick="hideDemoConfirmationModal()" class="text-white text-2xl font-bold hover:opacity-75">&times;</button>
        </div>

        <div class="px-6 py-6">
            <!-- Applicant Info -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-semibold text-lg text-gray-800 mb-2" id="demoApplicantName">Applicant Name</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-600">Email:</span>
                        <span class="text-gray-800" id="demoApplicantEmail">-</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Phone:</span>
                        <span class="text-gray-800" id="demoApplicantPhone">-</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Account:</span>
                        <span class="text-gray-800" id="demoApplicantAccount">-</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600">Demo Schedule:</span>
                        <span class="text-gray-800" id="demoApplicantSchedule">-</span>
                    </div>
                </div>
            </div>

            <!-- Decision Buttons -->
            <div class="text-center">
                <p class="text-gray-600 text-sm mb-4">
                    Review the applicant's demo performance and make your final hiring decision.
                </p>
                <div class="flex justify-center gap-4">
                    <button type="button" onclick="showDemoFailConfirmation()"
                        class="bg-[#F65353] text-white px-8 py-3 rounded-full font-bold hover:opacity-90 transition-opacity">
                        FAILED
                    </button>
                    <button type="button" onclick="showDemoPassConfirmation()"
                        class="bg-[#2A5382] text-white px-8 py-3 rounded-full font-bold hover:opacity-90 transition-opacity">
                        PASSED TO ONBOARDING
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Demo Pass Confirmation Modal -->
<div id="demoPassConfirmationModal" style="display: none; position: fixed; inset: 0; z-index: 10006;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-96">
        <div class="p-6 text-center">
            <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-[#2A5382]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="font-bold text-lg text-[#2A5382]">Confirm Pass to Onboarding</h3>
            <p class="text-gray-600 mt-2">This applicant has passed the demo and will be moved to onboarding stage.</p>
        </div>

        <div class="flex justify-center gap-4 pb-6">
            <button onclick="confirmDemoPass()" class="bg-[#2A5382] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">Confirm</button>
        </div>
    </div>
</div>

<!-- Demo Fail Confirmation Modal -->
<div id="demoFailConfirmationModal" style="display: none; position: fixed; inset: 0; z-index: 10006;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center px-6 py-3 bg-[#F65353] rounded-t-lg">
            <h2 class="text-white font-bold text-lg">Demo Failed - Reason</h2>
            <button onclick="hideDemoFailConfirmation()" class="text-white text-2xl font-bold hover:opacity-75">&times;</button>
        </div>

        <div class="px-6 py-6 space-y-4">
            <!-- Failure Reason -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Failure Reason:</label>
                <select id="demo_fail_reason" name="demo_fail_reason" onchange="toggleDemoFailFields()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]">
                    <option value="" disabled selected>Select a reason</option>
                    <option value="missed">Missed Demo</option>
                    <option value="declined">Declined</option>
                    <option value="not_recommended">Not Recommended</option>
                    <option value="transfer_account">Transfer Account</option>
                </select>
            </div>

            <!-- New Demo Time (for missed) -->
            <div id="demo_new_time_section" style="display: none;">
                <label class="block text-gray-700 text-sm font-medium mb-2">New Demo Time:</label>
                <input type="datetime-local" id="demo_new_time" name="demo_new_time"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]"
                    min="{{ now()->format('Y-m-d\TH:i') }}">
            </div>

            <!-- Transfer Account Fields -->
            <div id="demo_transfer_section" style="display: none;" class="space-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">New Assigned Account:</label>
                    <select id="demo_transfer_account" name="demo_transfer_account"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="" disabled selected>Select a different account</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account }}">{{ ucwords(str_replace('_', ' ', $account)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">New Status:</label>
                    <select id="demo_transfer_status" name="demo_transfer_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="" disabled selected>Select new status</option>
                        <option value="screening">Screening</option>
                        <option value="training">Training</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">New Schedule:</label>
                    <input type="datetime-local" id="demo_transfer_schedule" name="demo_transfer_schedule"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        min="{{ now()->format('Y-m-d\TH:i') }}">
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Notes:</label>
                <textarea id="demo_fail_notes" name="demo_fail_notes" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353] resize-none"
                    placeholder="Enter notes about the failure reason..."></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center gap-4 pt-4">
                <button type="button" onclick="hideDemoFailConfirmation()"
                    class="bg-gray-200 text-gray-700 px-6 py-2 rounded-full font-semibold hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button type="button" onclick="submitDemoFailAction()"
                    class="bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">
                    Submit
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Onboarding Pass/Fail Modal -->
<div id="onboardingPassFailModal" style="display: none; position: fixed; inset: 0; z-index: 10005;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-96">
        <div class="flex justify-between items-center px-6 py-3 bg-[#2A5382] rounded-t-lg">
            <h2 class="text-white font-bold text-lg">Update Hiring Stage</h2>
            <button onclick="hideOnboardingPassFailModal()" class="text-white text-2xl font-bold hover:opacity-75">&times;</button>
        </div>

        <div class="p-6 text-center">
            <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-[#2A5382]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="font-bold text-lg text-[#2A5382] mb-2" id="onboardingApplicantName">Applicant Name</h3>
            <div class="text-left bg-gray-50 p-4 rounded-lg mb-4">
                <p class="text-sm text-gray-600 mb-2"><strong>Account:</strong> <span id="onboardingAccount">—</span></p>
                <p class="text-sm text-gray-600"><strong>Schedule:</strong> <span id="onboardingSchedule">—</span></p>
            </div>
            <p class="text-gray-600 mb-6">
                Please select the final hiring decision for this applicant.
            </p>
        </div>

        <div class="flex justify-center gap-4 pb-6">
            <button onclick="showOnboardingFailModal()" class="bg-red-500 text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">Failed</button>
            <button onclick="showOnboardingPassConfirmation()" class="bg-green-500 text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">Passed</button>
        </div>
    </div>
</div>

<!-- Onboarding Pass Confirmation Modal -->
<div id="onboardingPassConfirmationModal" style="display: none; position: fixed; inset: 0; z-index: 10006;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-96">
        <div class="flex justify-between items-center px-6 py-3 bg-[#2A5382] rounded-t-lg">
            <h2 class="text-white font-bold text-lg">Confirm Hiring Decision</h2>
            <button onclick="hideOnboardingPassConfirmation()" class="text-white text-2xl font-bold hover:opacity-75">&times;</button>
        </div>

        <div class="p-6 text-center">
            <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-[#2A5382]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="font-bold text-lg text-[#2A5382] mb-2">Final Hiring Decision</h3>
            <p class="text-gray-600 mb-4">
                Are you sure you want to <strong>PASS</strong> this applicant for hiring?
            </p>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Warning:</strong> This action is <strong>NOT REVERSIBLE</strong>. The applicant will be moved to the hiring stage and registered as a tutor.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-center gap-4 pb-6">
            <button onclick="hideOnboardingPassConfirmation()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-full font-semibold hover:bg-gray-300 transition">Cancel</button>
            <button onclick="confirmOnboardingPass()" class="bg-[#2A5382] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">Confirm Pass</button>
        </div>
    </div>
</div>

<!-- Onboarding Fail Modal -->
<div id="onboardingFailModal" style="display: none; position: fixed; inset: 0; z-index: 10006;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-96">
        <div class="flex justify-between items-center px-6 py-3 bg-red-500 rounded-t-lg">
            <h2 class="text-white font-bold text-lg">Failed - Onboarding</h2>
            <button onclick="hideOnboardingFailModal()" class="text-white text-2xl font-bold hover:opacity-75">&times;</button>
        </div>

        <form id="onboardingFailForm">
            <div class="p-6">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Interviewer: <span class="text-red-500">*</span></label>
                            <input type="text" id="onboarding_fail_interviewer" name="onboarding_fail_interviewer" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                                placeholder="Enter interviewer name">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Failure Reason: <span class="text-red-500">*</span></label>
                            <select id="onboarding_fail_reason" name="onboarding_fail_reason" required onchange="toggleOnboardingFailFields()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="" disabled selected>Select failure reason</option>
                                <option value="missed">Missed Onboarding</option>
                                <option value="declined">Declined</option>
                            </select>
                        </div>

                <div id="onboarding_new_demo_time_section" style="display: none;" class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">New Demo Time: <span class="text-red-500">*</span></label>
                    <input type="datetime-local" id="onboarding_new_interview_time" name="onboarding_new_interview_time" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                        min="{{ now()->format('Y-m-d\TH:i') }}">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Notes:</label>
                    <textarea id="onboarding_fail_notes" name="onboarding_fail_notes" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                        placeholder="Enter additional notes..."></textarea>
                </div>
            </div>
        </form>

        <div class="flex justify-center gap-4 pb-6">
            <button onclick="hideOnboardingFailModal()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-full font-semibold hover:bg-gray-300 transition">Cancel</button>
            <button onclick="submitOnboardingFail()" class="bg-red-500 text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">Submit</button>
        </div>
    </div>
</div>

<!-- Onboarding Pass Modal - Tutor Registration -->
<div id="onboardingPassModal" style="display: none; position: fixed; inset: 0; z-index: 10007;"
    class="flex justify-center items-center w-full h-full bg-black bg-opacity-50 p-2 sm:p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-xs sm:max-w-md md:max-w-lg lg:max-w-2xl mx-2 sm:mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-6 py-3 bg-[#2A5382] rounded-t-lg">
            <h2 class="text-white font-bold text-lg">Update Hiring Stage - Passed</h2>
            <button onclick="hideOnboardingPassModal()" class="text-white text-2xl font-bold hover:opacity-75">&times;</button>
        </div>

        <form id="onboardingPassForm">
            <div class="p-3 sm:p-4 md:p-6">
                <!-- Applicant Information Display -->
                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg mb-4 sm:mb-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-2 sm:mb-3">Applicant Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Tutor Name:</label>
                            <p class="text-sm text-gray-800 font-medium" id="passTutorName">—</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Email:</label>
                            <p class="text-sm text-gray-800" id="passTutorEmail">—</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Assigned Account:</label>
                            <p class="text-sm text-gray-800" id="passAssignedAccount">—</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Hiring Status:</label>
                            <p class="text-sm text-green-600 font-medium">Hired</p>
                        </div>
                    </div>
                </div>

                <!-- Interviewer Field -->
                <div class="mb-3 sm:mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-1 sm:mb-2">Interviewer: <span class="text-red-500">*</span></label>
                    <input type="text" id="onboarding_pass_interviewer" name="pass_interviewer" required
                        class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#2A5382]"
                        placeholder="Enter interviewer name"
                        value="{{ Auth::guard('supervisor')->check() ? Auth::guard('supervisor')->user()->full_name : '' }}">
                </div>

                <!-- Tutor Registration Details -->
                <div class="border-t pt-3 sm:pt-4 md:pt-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Tutor Account Setup</h3>
                    
                    <!-- System ID (Auto-generated from tutors table PK) -->
                    <div class="mb-3 sm:mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-1 sm:mb-2">System ID:</label>
                        <input type="text" id="pass_system_id" name="pass_system_id" required readonly
                            class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md bg-gray-50 text-gray-700 cursor-not-allowed">
                        <p class="text-xs text-gray-500 mt-1">Auto-generated system ID (tutors table primary key)</p>
                    </div>

                    <!-- Username (Auto-generated - Disabled) -->
                    <div class="mb-3 sm:mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-1 sm:mb-2">Username:</label>
                        <input type="text" id="pass_username" name="pass_username" required readonly
                            class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md bg-gray-50 text-gray-700 cursor-not-allowed">
                        <p class="text-xs text-gray-500 mt-1">Auto-generated username for login</p>
                    </div>

                    <!-- Password (Default - Disabled) -->
                    <div class="mb-3 sm:mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-1 sm:mb-2">Default Password:</label>
                        <input type="text" name="pass_password" id="pass_password" readonly
                            class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md bg-gray-50 text-gray-700 cursor-not-allowed"
                            value="OGSConnect2025">
                        <p class="text-xs text-gray-500 mt-1">Default password for all new tutors</p>
                    </div>


                    <!-- Notes Field -->
                    <div class="mb-4 sm:mb-6">
                        <label class="block text-gray-700 text-sm font-medium mb-1 sm:mb-2">Notes:</label>
                        <textarea id="pass_notes" name="pass_notes" rows="2" sm:rows="3"
                            class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#2A5382] resize-none"
                            placeholder="Enter additional notes..."></textarea>
                    </div>
                </div>
            </div>
        </form>

        <div class="flex flex-col sm:flex-row justify-center gap-2 sm:gap-4 p-3 sm:p-4 md:p-6 pt-0">
            <button onclick="submitOnboardingPassForm();" class="bg-[#2A5382] text-white px-4 sm:px-6 py-2 rounded-full font-semibold hover:opacity-90 transition text-sm sm:text-base">Register Tutor</button>
        </div>
    </div>
</div>

<script>
// Global variables
let currentDemoId = null;
let assignmentHistory = [];

// Test function to verify JavaScript is working
function testButtonClick() {
    console.log('=== TEST: Button click function is working ===');
    alert('Button click test successful!');
}


// Available accounts for assignment
const availableAccounts = ['tutlo', 'talk915', 'gl5', 'babilala'];

// Helper function to get CSRF token
function getCsrfToken() {
    // Try to get from meta tag first
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    console.log('CSRF Token meta tag:', metaToken);
    if (metaToken) {
        console.log('CSRF Token found:', metaToken.content);
        return metaToken.content;
    }
    
    // Fallback to form token
    const formToken = document.querySelector('input[name="_token"]');
    if (formToken) {
        return formToken.value;
    }
    
    // Last resort - try to get from any form
    const anyToken = document.querySelector('input[name="_token"]');
    if (anyToken) {
        return anyToken.value;
    }
    
    throw new Error('CSRF token not found. Please refresh the page.');
}

// Load edit modal data via AJAX
async function loadEditModalData(demoId) {
    try {
        // Show loading state
        showModalError('Loading demo data...', 'info');
        
        const response = await fetch(`/demo/${demoId}/edit-data`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            }
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Failed to load demo data: ${response.status} - ${errorText}`);
        }

        const data = await response.json();
        hideModalError();
        openEditModal(demoId, data);
    } catch (error) {
        console.error('Error loading demo data:', error);
        showModalError('Failed to load demo data: ' + error.message, 'error');
    }
}

// Error handling functions
function showModalError(message, type = 'error') {
    const errorDiv = document.getElementById('modalError');
    const errorMessage = document.getElementById('modalErrorMessage');
    
    errorMessage.textContent = message;
    
    // Update styling based on type
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
    document.getElementById('modalError').classList.add('hidden');
}

function showModalSuccess(message) {
    const errorDiv = document.getElementById('modalError');
    const errorMessage = document.getElementById('modalErrorMessage');
    
    errorMessage.textContent = message;
    errorDiv.className = 'px-6 py-3 bg-green-50 border-l-4 border-green-400';
    errorMessage.className = 'text-sm text-green-700';
    
    errorDiv.classList.remove('hidden');
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        hideModalError();
    }, 3000);
}

// Pass Modal Error Functions
function showPassModalError(message) {
    const errorDiv = document.getElementById('passModalError');
    const errorMessage = document.getElementById('passModalErrorMessage');
    
    errorMessage.textContent = message;
    errorDiv.classList.remove('hidden');
}

function hidePassModalError() {
    document.getElementById('passModalError').classList.add('hidden');
}

// Fail Modal Error Functions
function showFailModalError(message) {
    const errorDiv = document.getElementById('failModalError');
    const errorMessage = document.getElementById('failModalErrorMessage');
    
    errorMessage.textContent = message;
    errorDiv.classList.remove('hidden');
}

function hideFailModalError() {
    document.getElementById('failModalError').classList.add('hidden');
}

// Pass Modal Functions
function showPassModal() {
    // Pre-fill interviewer from the main modal
    const interviewer = document.getElementById('modal_interviewer').value;
    document.getElementById('pass_interviewer').value = interviewer;
    
    // Pre-fill assigned account from the main modal (hidden field)
    const assignedAccount = document.getElementById('modal_assigned_account').value;
    document.getElementById('pass_assigned_account').value = assignedAccount;
    
    // Set form action
    document.getElementById('passForm').action = `/demo/${currentDemoId}/status`;
    
    // Update next status options based on current status
    updateNextStatusOptions();
    
    // Hide any previous errors
    hidePassModalError();
    
    // Show the modal
    document.getElementById('passModal').style.display = 'flex';
}

function hidePassModal() {
    document.getElementById('passModal').style.display = 'none';
    hidePassModalError(); // Hide any error messages
}

function updateNextStatusOptions() {
    const currentStatus = document.getElementById('modal_hiring_status').value;
    const nextStatusSelect = document.getElementById('pass_next_status');
    
    // Clear existing options (except the first one)
    nextStatusSelect.innerHTML = '<option value="" selected disabled>Select Next Status</option>';
    
    // Define available options based on current status
    let availableOptions = [];
    
    switch(currentStatus) {
        case 'screening':
            availableOptions = [
                { value: 'training', text: 'Training' },
                { value: 'demo', text: 'Demo' }
            ];
            break;
        case 'training':
            availableOptions = [
                { value: 'demo', text: 'Demo' }
            ];
            break;
        case 'demo':
            availableOptions = [
                { value: 'onboarding', text: 'Onboarding' }
            ];
            break;
        case 'onboarding':
            // No next status for onboarding
            availableOptions = [];
            break;
        default:
            availableOptions = [];
    }
    
    // Add options to select
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
    // Get the form data
    const interviewer = document.getElementById('pass_interviewer').value;
    const nextStatus = document.getElementById('pass_next_status').value;
    const schedule = document.getElementById('pass_demo_schedule').value;
    const notes = document.getElementById('pass_notes').value;
    
    // Basic validation - interviewer and next status are always required
    if (!interviewer) {
        showPassModalError('Please enter interviewer name');
        return;
    }
    
    if (!nextStatus) {
        showPassModalError('Please select next status');
        return;
    }
    
    // Validate based on next status
    if (nextStatus === 'demo') {
        if (!schedule) {
            showPassModalError('Please select a schedule for demo');
            return;
        }
    }
    
    // Get the form element
    const form = document.getElementById('passForm');
    if (form) {
        // Check if form is valid
        if (form.checkValidity()) {
            // Get additional form data
            const assignedAccount = document.getElementById('pass_assigned_account').value;
            
            // Update confirmation modal
            updatePassConfirmationModal(interviewer, assignedAccount, nextStatus, schedule, notes);
            
            // Show confirmation modal
            document.getElementById('passConfirmationModal').style.display = 'flex';
        } else {
            // Show validation errors
            form.reportValidity();
        }
    }
}

function updatePassConfirmationModal(interviewer, assignedAccount, nextStatus, schedule, notes) {
    const titleElement = document.getElementById('passConfirmationTitle');
    const messageElement = document.getElementById('passConfirmationMessage');
    const iconElement = document.getElementById('passConfirmationIcon');
    
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
    
    iconElement.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    `;
}

function hidePassConfirmation() {
    document.getElementById('passConfirmationModal').style.display = 'none';
}

async function submitPassForm() {
    const form = document.getElementById('passForm');
    if (form) {
        // Validate required fields before submitting
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
        
        // Show loading state
        const confirmButton = document.querySelector('button[onclick="submitPassForm()"]');
        if (confirmButton) {
            confirmButton.disabled = true;
            confirmButton.innerHTML = 'Processing...';
        }
        
        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    next_status: document.getElementById('pass_next_status').value,
                    next_schedule: document.getElementById('pass_demo_schedule').value,
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
            
            // Reset button state
            if (confirmButton) {
                confirmButton.disabled = false;
                confirmButton.innerHTML = 'Confirm';
            }
        }
    }
}

// Pass/Fail Confirmation Modal Functions
let pendingAction = null; // Store the pending action (success/fail)

function showPassFailConfirmation(action, status) {
    pendingAction = action;
    const titleElement = document.getElementById('passFailConfirmationTitle');
    const messageElement = document.getElementById('passFailConfirmationMessage');
    const iconElement = document.getElementById('passFailConfirmationIcon');
    const confirmButton = document.querySelector('button[onclick="confirmPassFailAction()"]');
    
    if (action === 'success') {
        titleElement.textContent = 'Confirm Pass';
        messageElement.innerHTML = `
            This applicant has <span class="font-bold text-green-600">passed</span> the current stage.
            <br><br>
            <strong>Current Status:</strong> ${status}
            <br><strong>Next Action:</strong> ${getNextActionText(status)}
        `;
        iconElement.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        `;
        confirmButton.className = 'bg-[#65DB7F] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
    } else if (action === 'fail') {
        titleElement.textContent = 'Confirm Fail';
        messageElement.innerHTML = `
            This applicant has <span class="font-bold text-red-600">failed</span> the current stage.
            <br><br>
            <strong>Current Status:</strong> ${status}
            <br><strong>Action:</strong> Will be moved to fail options
        `;
        iconElement.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        `;
        confirmButton.className = 'bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
    }
    
    document.getElementById('passFailConfirmationModal').style.display = 'flex';
}

function getNextActionText(status) {
    switch(status) {
        case 'screening': return 'Move to Demo stage';
        case 'demo': return 'Move to Onboarding stage';
        case 'training': return 'Move to Onboarding stage';
        case 'onboarding': return 'Complete registration process';
        default: return 'Update status';
    }
}

function hidePassFailConfirmation() {
    document.getElementById('passFailConfirmationModal').style.display = 'none';
    pendingAction = null;
}

async function confirmPassFailAction() {
    if (pendingAction === 'success') {
        const status = document.getElementById('modal_hiring_status').value;
        await handleSuccessAction(status);
    } else if (pendingAction === 'fail') {
        showFailOptionsModal();
    }
    hidePassFailConfirmation();
}

// Modal functions
function openEditModal(demoId, data) {
    console.log('Opening modal for demo ID:', demoId, 'with data:', data);
    currentDemoId = demoId;
    
    // Store assignment history (all accounts they've been assigned to)
    assignmentHistory = data.assignment_history || [];
    console.log('Assignment history loaded:', assignmentHistory);
    
    // Store data globally for use in pass modal
    window.currentModalData = data;
    
    // Populate form fields
    document.getElementById('editForm').action = `/demo/${demoId}`;
    document.getElementById('modal_interviewer').value = data.interviewer || '';
    document.getElementById('modal_email').value = data.email || '';
    
    // Time availability fields removed - no longer needed in review modal
    
    document.getElementById('modal_assigned_account').value = data.assigned_account || '';
    document.getElementById('modal_hiring_status').value = data.hiring_status || '';
    document.getElementById('modal_notes').value = data.notes || '';
    
    // Handle schedule
    const schedule = data.schedule || data.demo_schedule || data.interview_time || '';
    if (schedule) {
        const date = new Date(schedule);
        if (!isNaN(date.getTime())) {
            const localDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
            document.getElementById('modal_schedule').value = localDate.toISOString().slice(0, 16);
        }
    }
    
    // Show/hide sections based on status
    const status = data.hiring_status || '';
    const scheduleSection = document.getElementById('scheduleSection');
    const notesSection = document.getElementById('notesSection');
    
    if (status === 'onboarding') {
        scheduleSection.style.display = 'none';
        notesSection.style.display = 'none';
    } else {
        scheduleSection.style.display = 'block';
        notesSection.style.display = 'block';
    }
    
    // Show modal
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    hideModalError(); // Hide any error messages
    currentDemoId = null;
}

// Handle modal actions (PASSED/FAILED buttons) - Now handled by confirmation modal
async function handleModalAction(action) {
    if (!currentDemoId) {
        showModalError('No demo ID found');
        return;
    }
    
    const status = document.getElementById('modal_hiring_status').value;
    console.log('Handling action:', action, 'for status:', status);
    
    if (action === 'success') {
        await handleSuccessAction(status);
    }
}

// Fail Options Modal Functions
function showFailOptionsModal() {
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
    
    // Reset header and button colors to default (red)
    const failModalHeader = document.getElementById('failModalHeader');
    const failSubmitButton = document.getElementById('failSubmitButton');
    failModalHeader.className = 'flex justify-between items-center px-6 py-3 bg-[#F65353] rounded-t-lg';
    failSubmitButton.className = 'bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
    
    // Reset input field focus colors to default (red)
    const failReasonSelect = document.getElementById('fail_reason');
    const failNotesTextarea = document.getElementById('fail_notes');
    const newInterviewTimeInput = document.getElementById('new_interview_time');
    const failInterviewerInput = document.getElementById('fail_interviewer');
    const transferAccountSelect = document.getElementById('transfer_assigned_account');
    const transferStatusSelect = document.getElementById('transfer_status');
    const transferScheduleInput = document.getElementById('transfer_schedule');
    
    failReasonSelect.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
    failNotesTextarea.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353] resize-none';
    newInterviewTimeInput.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
    transferAccountSelect.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
    transferStatusSelect.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
    transferScheduleInput.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
    
    // Pre-filter accounts for transfer_account option
    preFilterTransferAccounts();
    
    // Hide any previous errors
    hideFailModalError();
    
    document.getElementById('failOptionsModal').style.display = 'flex';
}

function hideFailOptionsModal() {
    document.getElementById('failOptionsModal').style.display = 'none';
    hideFailModalError(); // Hide any error messages
}

function toggleFailFields() {
    const failReason = document.getElementById('fail_reason').value;
    const newInterviewTimeSection = document.getElementById('new_interview_time_section');
    const transferAccountSection = document.getElementById('transfer_account_section');
    const failModalHeader = document.getElementById('failModalHeader');
    const failSubmitButton = document.getElementById('failSubmitButton');
    
    // Get input elements
    const failReasonSelect = document.getElementById('fail_reason');
    const failNotesTextarea = document.getElementById('fail_notes');
    const newInterviewTimeInput = document.getElementById('new_interview_time');
    const failInterviewerInput = document.getElementById('fail_interviewer');
    const transferAccountSelect = document.getElementById('transfer_assigned_account');
    const transferStatusSelect = document.getElementById('transfer_status');
    const transferScheduleInput = document.getElementById('transfer_schedule');
    
    // Hide all sections first
    newInterviewTimeSection.style.display = 'none';
    transferAccountSection.style.display = 'none';
    
    // Change header and button colors based on fail reason
    if (failReason === 'transfer_account') {
        failModalHeader.className = 'flex justify-between items-center px-6 py-3 bg-blue-500 rounded-t-lg';
        failSubmitButton.className = 'bg-blue-500 text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
        
        // Update input field focus colors to blue
        failReasonSelect.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
        failNotesTextarea.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none';
        newInterviewTimeInput.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
        transferAccountSelect.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
        transferStatusSelect.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
        transferScheduleInput.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
    } else {
        failModalHeader.className = 'flex justify-between items-center px-6 py-3 bg-[#F65353] rounded-t-lg';
        failSubmitButton.className = 'bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
        
        // Update input field focus colors to red
        failReasonSelect.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
        failNotesTextarea.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353] resize-none';
        newInterviewTimeInput.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
        transferAccountSelect.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
        transferStatusSelect.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
        transferScheduleInput.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
    }
    
    // Show relevant section based on selection
    if (failReason === 'missed') {
        newInterviewTimeSection.style.display = 'block';
    } else if (failReason === 'transfer_account') {
        transferAccountSection.style.display = 'block';
        // Pre-fill interviewer from the main modal
        const interviewer = document.getElementById('modal_interviewer').value;
        document.getElementById('fail_interviewer').value = interviewer;
        // Pre-filter accounts based on assignment history
        preFilterTransferAccounts();
    }
}

function getAssignmentHistory() {
    // Return the assignment history from the global variable
    return assignmentHistory || [];
}

function preFilterTransferAccounts() {
    // Pre-filter the transfer account dropdown based on assignment history
    const currentAccount = document.getElementById('modal_assigned_account').value;
    const accountSelect = document.getElementById('transfer_assigned_account');
    const assignmentHistory = getAssignmentHistory();
    
    console.log('Pre-filtering accounts:');
    console.log('Current account:', currentAccount);
    console.log('Assignment history:', assignmentHistory);
    
    // Clear existing options (except the first one)
    accountSelect.innerHTML = '<option value="" disabled selected>Select a different account</option>';
    
    // Add only available accounts (not in assignment history and not current account)
    const blockedAccounts = [currentAccount, ...assignmentHistory];
    const availableOptions = availableAccounts.filter(account => !blockedAccounts.includes(account));
    
    console.log('Blocked accounts:', blockedAccounts);
    console.log('Available options:', availableOptions);
    
    availableOptions.forEach(account => {
        const option = document.createElement('option');
        option.value = account;
        option.textContent = account.charAt(0).toUpperCase() + account.slice(1);
        accountSelect.appendChild(option);
        console.log('Added option:', account);
    });
}

function showFailConfirmation() {
    const failReason = document.getElementById('fail_reason').value;
    const interviewer = document.getElementById('fail_interviewer').value;
    const notes = document.getElementById('fail_notes').value;
    
    // Basic validation - fail reason and interviewer are always required
    if (!failReason) {
        showFailModalError('Please select a failure reason');
        return;
    }
    
    if (!interviewer) {
        showFailModalError('Please enter interviewer name');
        return;
    }
    
    // Validate based on failure reason
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
    
    // Update confirmation modal based on fail reason
    updateFailConfirmationModal(failReason, interviewer, notes);
    
    // Show confirmation modal
    document.getElementById('failConfirmationModal').style.display = 'flex';
}

function updateFailConfirmationModal(failReason, interviewer, notes) {
    const titleElement = document.getElementById('failConfirmationTitle');
    const messageElement = document.getElementById('failConfirmationMessage');
    const iconElement = document.getElementById('failConfirmationIcon');
    const confirmButton = document.querySelector('button[onclick="submitFailAction()"]');
    
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
            iconElement.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            `;
            // Set button to red for missed
            if (confirmButton) {
                confirmButton.className = 'bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
            }
            break;
            
        case 'declined':
            titleElement.textContent = 'Confirm Declined';
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
            // Set button to red for declined
            if (confirmButton) {
                confirmButton.className = 'bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
            }
            break;
            
        case 'not_recommended':
            titleElement.textContent = 'Confirm Not Recommended';
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
            // Set button to red for not recommended
            if (confirmButton) {
                confirmButton.className = 'bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
            }
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
            iconElement.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
            `;
            // Set button to blue for transfer account
            if (confirmButton) {
                confirmButton.className = 'bg-blue-500 text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
            }
            break;
            
        default:
            titleElement.textContent = 'Confirm Fail Action';
            messageElement.innerHTML = `
                This applicant has <span class="font-bold text-red-600">failed</span> the current stage.
                ${notes ? `<br><br><strong>Notes:</strong> ${notes}` : ''}
            `;
            // Set button to red for default
            if (confirmButton) {
                confirmButton.className = 'bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
            }
    }
}

function hideFailConfirmation() {
    document.getElementById('failConfirmationModal').style.display = 'none';
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
        
        // Add specific data based on failure reason
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

async function handleSuccessAction(currentStatus) {
    if (currentStatus === 'onboarding') {
        document.getElementById('passApplicantConfirmModal').style.display = 'flex';
    } else if (currentStatus === 'demo') {
        // Handle demo stage - move to onboarding
        try {
        const response = await fetch(`/demo/${currentDemoId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: 'onboarding',
                notes: 'Passed demo stage'
            })
        });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to update status');
            }

            const data = await response.json();
            if (data.success) {
                closeEditModal();
                document.getElementById('passApplicantConfirmModal').style.display = 'flex';
            } else {
                throw new Error(data.message || 'Failed to update status');
            }
        } catch (error) {
            console.error('Error:', error);
            showModalError('Failed to update status: ' + error.message);
        }
    } else {
        // Handle other stages - show next step modal
        showNextStepModal(currentStatus);
    }
}

function showNextStepModal(currentStatus) {
    const nextStatusSelect = document.getElementById('next_status');
    nextStatusSelect.innerHTML = '';

    let options = [];
    if (currentStatus === 'screening') {
        options = [
            { value: 'training', text: 'Training' },
            { value: 'demo', text: 'Demo' }
        ];
    } else if (currentStatus === 'training') {
        options = [{ value: 'demo', text: 'Demo' }];
    }

    if (options.length > 0) {
        options.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.value;
            option.textContent = opt.text;
            nextStatusSelect.appendChild(option);
        });
    }

    document.getElementById('nextStepForm').action = `/demo/${currentDemoId}/status`;
    document.getElementById('nextStepModal').style.display = 'flex';
}

function hideNextStepModal() {
    document.getElementById('nextStepModal').style.display = 'none';
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

// Pass applicant confirmation
function hidePassConfirmModal() {
    document.getElementById('passApplicantConfirmModal').style.display = 'none';
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
            document.getElementById('successApplicantModal').style.display = 'flex';
        } else {
            throw new Error(data.message || 'Failed to update status');
        }
    } catch (error) {
        console.error('Error:', error);
        showModalError('Failed to update status: ' + error.message);
    }
}

function hideSuccessApplicantModal() {
    document.getElementById('successApplicantModal').style.display = 'none';
}

function registerApplicant() {
    hideSuccessApplicantModal();
    
    // Get values from the form
    const assignedAccount = document.getElementById('modal_assigned_account').value;
    const personalEmail = document.getElementById('modal_email').value;
    
    // Pre-fill the registration form
    document.getElementById('reg_assigned_account').value = assignedAccount;
    document.getElementById('personal_email').value = personalEmail;
    
    // Show the registration modal
    document.getElementById('employeeRegistrationModal').style.display = 'flex';
}

function hideEmployeeRegistrationModal() {
    document.getElementById('employeeRegistrationModal').style.display = 'none';
}

async function submitEmployeeRegistration() {
    const form = document.getElementById('employeeRegistrationForm');
    const registerButton = document.querySelector('#employeeRegistrationModal button[onclick="submitEmployeeRegistration()"]');
    
    // Validate passwords
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

    // Disable button and show loading state
    registerButton.disabled = true;
    registerButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    try {
        // Create FormData
        const formData = new FormData();
        
        // Add form fields
        formData.append('name', document.getElementById('name').value);
        formData.append('personal_email', document.getElementById('personal_email').value);
        formData.append('password', password);
        formData.append('username', document.getElementById('username').value);
        formData.append('assigned_account', document.getElementById('reg_assigned_account').value);
        formData.append('status', 'active');
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        
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
            document.getElementById('registrationSuccessModal').style.display = 'flex';
            // Reload the page after 2 seconds
            setTimeout(() => window.location.reload(), 2000);
        } else {
            throw new Error(data.message || 'Failed to register tutor');
        }
    } catch (error) {
        console.error('Error:', error);
        
        // Show error message to user
        const errorMessage = error.message || 'An error occurred while registering the tutor';
        showModalError(errorMessage);
        
        // Reset button state
        registerButton.disabled = false;
        registerButton.innerHTML = 'Complete Registration';
    }
}

// Test function
function testModal() {
    console.log('Test button clicked');
    openEditModal('test', {
        interviewer: 'Test Interviewer',
        email: 'test@example.com',
        assigned_account: 'tutlo',
        hiring_status: 'screening',
        schedule: '2024-01-01 10:00:00',
        notes: 'Test notes'
    });
}

// Demo Details Confirmation Modal
let currentDemoDetailsId = null;

function showDemoDetailsConfirmation(demoId, applicantName) {
    currentDemoDetailsId = demoId;
    
    // Set applicant name in modal
    document.getElementById('demoDetailsApplicantName').textContent = applicantName;
    
    // Show modal
    document.getElementById('demoDetailsConfirmationModal').style.display = 'flex';
}

function hideDemoDetailsConfirmation() {
    document.getElementById('demoDetailsConfirmationModal').style.display = 'none';
    currentDemoDetailsId = null;
}

function proceedToApplicantDetails() {
    if (currentDemoDetailsId) {
        // Redirect to applicant details page using the correct route
        window.location.href = `/hiring-onboarding/applicant/${currentDemoDetailsId}/uneditable`;
    }
}

// Demo Confirmation Modal Functions
let currentDemoApplicantId = null;

function showDemoConfirmationModal(demoId, applicantName) {
    currentDemoApplicantId = demoId;
    
    // Load applicant data
    loadDemoApplicantData(demoId, applicantName);
    
    // Show modal
    document.getElementById('demoConfirmationModal').style.display = 'flex';
}

function hideDemoConfirmationModal() {
    document.getElementById('demoConfirmationModal').style.display = 'none';
    currentDemoApplicantId = null;
}

async function loadDemoApplicantData(demoId, applicantName) {
    try {
        const response = await fetch(`/demo/${demoId}/edit-data`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            }
        });

        if (!response.ok) {
            throw new Error('Failed to load demo data');
        }

        const data = await response.json();
        
        // Populate applicant info
        document.getElementById('demoApplicantName').textContent = applicantName;
        document.getElementById('demoApplicantEmail').textContent = data.email || '-';
        document.getElementById('demoApplicantPhone').textContent = data.contact_number || '-';
        document.getElementById('demoApplicantAccount').textContent = data.assigned_account ? data.assigned_account.charAt(0).toUpperCase() + data.assigned_account.slice(1) : '-';
        
        // Format schedule
        const schedule = data.demo_schedule || data.schedule || '';
        if (schedule) {
            const date = new Date(schedule);
            document.getElementById('demoApplicantSchedule').textContent = date.toLocaleString();
        } else {
            document.getElementById('demoApplicantSchedule').textContent = '-';
        }
    } catch (error) {
        console.error('Error loading demo data:', error);
        showModalError('Failed to load demo data: ' + error.message);
    }
}

function showDemoPassConfirmation() {
    hideDemoConfirmationModal();
    document.getElementById('demoPassConfirmationModal').style.display = 'flex';
}

function hideDemoPassConfirmation() {
    document.getElementById('demoPassConfirmationModal').style.display = 'none';
}

async function confirmDemoPass() {
    try {
        const response = await fetch(`/demo/${currentDemoApplicantId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: 'onboarding',
                notes: 'Passed demo stage - ready for onboarding'
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to update status');
        }

        const data = await response.json();
        if (data.success) {
            hideDemoPassConfirmation();
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to update status');
        }
    } catch (error) {
        console.error('Error:', error);
        showModalError('Failed to update status: ' + error.message);
    }
}

function showDemoFailConfirmation() {
    hideDemoConfirmationModal();
    document.getElementById('demoFailConfirmationModal').style.display = 'flex';
}

function hideDemoFailConfirmation() {
    document.getElementById('demoFailConfirmationModal').style.display = 'none';
}

function toggleDemoFailFields() {
    const failReason = document.getElementById('demo_fail_reason').value;
    const newTimeSection = document.getElementById('demo_new_time_section');
    const transferSection = document.getElementById('demo_transfer_section');
    
    // Hide all sections first
    newTimeSection.style.display = 'none';
    transferSection.style.display = 'none';
    
    // Show relevant section based on selection
    if (failReason === 'missed') {
        newTimeSection.style.display = 'block';
    } else if (failReason === 'transfer_account') {
        transferSection.style.display = 'block';
    }
}

async function submitDemoFailAction() {
    const failReason = document.getElementById('demo_fail_reason').value;
    const notes = document.getElementById('demo_fail_notes').value;
    
    if (!failReason) {
        showModalError('Please select a failure reason');
        return;
    }
    
    try {
        let requestData = {
            fail_reason: failReason,
            notes: notes
        };
        
        // Add specific data based on failure reason
        if (failReason === 'missed') {
            const newTime = document.getElementById('demo_new_time').value;
            if (!newTime) {
                showModalError('Please select a new demo time');
                return;
            }
            requestData.new_demo_time = newTime;
        } else if (failReason === 'transfer_account') {
            const transferAccount = document.getElementById('demo_transfer_account').value;
            const transferStatus = document.getElementById('demo_transfer_status').value;
            const transferSchedule = document.getElementById('demo_transfer_schedule').value;
            
            if (!transferAccount || !transferStatus || !transferSchedule) {
                showModalError('Please fill in all transfer account fields');
                return;
            }
            
            requestData.transfer_data = {
                assigned_account: transferAccount,
                new_status: transferStatus,
                schedule: transferSchedule
            };
        }
        
        const response = await fetch(`/demo/${currentDemoApplicantId}/fail`, {
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
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to process failure action');
        }
    } catch (error) {
        console.error('Error:', error);
        showModalError('Failed to process failure action: ' + error.message);
    }
}

// Onboarding Pass/Fail Modal Functions
let currentOnboardingId = null;

function showOnboardingPassFailModal(onboardingId, applicantName, account, schedule, email) {
    console.log('=== showOnboardingPassFailModal called ===');
    console.log('Onboarding ID received:', onboardingId);
    console.log('Email received:', email);
    
    currentOnboardingId = onboardingId;
    console.log('Current Onboarding ID set to:', currentOnboardingId);
    
    // Set applicant info in modal
    document.getElementById('onboardingApplicantName').textContent = applicantName;
    document.getElementById('onboardingAccount').textContent = account || '—';
    document.getElementById('onboardingSchedule').textContent = schedule || '—';
    
    // Store applicant info for pass modal
    window.currentApplicantInfo = {
        name: applicantName,
        account: account || '—',
        schedule: schedule || '—',
        email: email || '—'
    };
    
    // Show modal
    document.getElementById('onboardingPassFailModal').style.display = 'flex';
}

function hideOnboardingPassFailModal() {
    document.getElementById('onboardingPassFailModal').style.display = 'none';
    currentOnboardingId = null;
}

function showOnboardingFailModal() {
    // Preserve the currentOnboardingId before hiding the modal
    const preservedId = currentOnboardingId;
    
    hideOnboardingPassFailModal();
    
    // Restore the ID after hiding the modal
    currentOnboardingId = preservedId;
    
    // Reset form fields
    const form = document.getElementById('onboardingFailForm');
    if (form) {
        form.reset();
    }
    
    // Set interviewer field to logged-in supervisor's name for fail modal
    const failInterviewerField = document.getElementById('onboarding_fail_interviewer');
    if (failInterviewerField) {
        @if(Auth::guard('supervisor')->check())
            failInterviewerField.value = '{{ Auth::guard("supervisor")->user()->full_name }}';
        @elseif(Auth::guard('web')->check())
            failInterviewerField.value = '{{ Auth::guard("web")->user()->full_name ?? Auth::guard("web")->user()->first_name . " " . Auth::guard("web")->user()->last_name }}';
        @else
            failInterviewerField.value = '';
        @endif
    }
    
    // Hide conditional sections
    document.getElementById('onboarding_new_demo_time_section').style.display = 'none';
    
    document.getElementById('onboardingFailModal').style.display = 'flex';
}

function hideOnboardingFailModal() {
    document.getElementById('onboardingFailModal').style.display = 'none';
}

function toggleOnboardingFailFields() {
    const failReason = document.getElementById('onboarding_fail_reason').value;
    const newDemoTimeSection = document.getElementById('onboarding_new_demo_time_section');
    const notesField = document.getElementById('onboarding_fail_notes');
    
    // Hide all sections first
    newDemoTimeSection.style.display = 'none';
    
    // Show relevant section based on selection
    if (failReason === 'missed') {
        newDemoTimeSection.style.display = 'block';
        // Set notes to "Missed" by default for missed onboarding
        if (notesField && !notesField.value) {
            notesField.value = 'Missed';
        }
    } else {
        // Clear notes if not missed
        if (notesField && notesField.value === 'Missed') {
            notesField.value = '';
        }
    }
}

function showOnboardingPassModal() {
    console.log('=== showOnboardingPassModal called ===');
    console.log('Current Onboarding ID before preserve:', currentOnboardingId);
    
    // Preserve the currentOnboardingId before hiding the modal
    const preservedId = currentOnboardingId;
    
    hideOnboardingPassFailModal();
    
    // Restore the ID after hiding the modal
    currentOnboardingId = preservedId;
    
    console.log('Current Onboarding ID after restore:', currentOnboardingId);
    
    // Set interviewer field to logged-in supervisor's name
    const interviewerField = document.getElementById('onboarding_pass_interviewer');
    const notesField = document.getElementById('pass_notes');
    const passwordField = document.getElementById('pass_password');
    
    if (interviewerField) {
        // Get the logged-in user's full name
        @if(Auth::guard('supervisor')->check())
            interviewerField.value = '{{ Auth::guard("supervisor")->user()->full_name }}';
        @elseif(Auth::guard('web')->check())
            interviewerField.value = '{{ Auth::guard("web")->user()->full_name ?? Auth::guard("web")->user()->first_name . " " . Auth::guard("web")->user()->last_name }}';
        @else
            interviewerField.value = '';
        @endif
    }
    if (notesField) {
        notesField.value = '';
    }
    if (passwordField) {
        passwordField.value = 'OGSConnect2025';
    }
    
    // Populate applicant information
    if (window.currentApplicantInfo) {
        document.getElementById('passTutorName').textContent = window.currentApplicantInfo.name;
        document.getElementById('passAssignedAccount').textContent = window.currentApplicantInfo.account;
        document.getElementById('passTutorEmail').textContent = window.currentApplicantInfo.email || '—';
    }
    
    // Generate username and system ID
    generatePassUsername();
    
    // Also generate local credentials as backup
    generateLocalCredentials();
    
    // Show modal
    document.getElementById('onboardingPassModal').style.display = 'flex';
}

function hideOnboardingPassModal() {
    document.getElementById('onboardingPassModal').style.display = 'none';
}

// Onboarding Pass Confirmation Functions
function showOnboardingPassConfirmation() {
    console.log('=== showOnboardingPassConfirmation called ===');
    console.log('Current Onboarding ID:', currentOnboardingId);
    
    // Preserve the currentOnboardingId before hiding the modal
    const preservedId = currentOnboardingId;
    
    // Hide the pass/fail modal first
    hideOnboardingPassFailModal();
    
    // Restore the ID after hiding the modal
    currentOnboardingId = preservedId;
    
    console.log('Current Onboarding ID after restore:', currentOnboardingId);
    
    // Show the confirmation modal
    document.getElementById('onboardingPassConfirmationModal').style.display = 'flex';
}

function hideOnboardingPassConfirmation() {
    document.getElementById('onboardingPassConfirmationModal').style.display = 'none';
}

function confirmOnboardingPass() {
    console.log('=== confirmOnboardingPass called ===');
    console.log('Current Onboarding ID before confirm:', currentOnboardingId);
    console.log('Proceeding to onboarding pass modal...');
    
    // Preserve the currentOnboardingId before hiding the confirmation modal
    const preservedId = currentOnboardingId;
    
    // Hide the confirmation modal
    hideOnboardingPassConfirmation();
    
    // Restore the ID after hiding the modal
    currentOnboardingId = preservedId;
    
    console.log('Current Onboarding ID after restore:', currentOnboardingId);
    
    // Show the actual onboarding pass modal
    showOnboardingPassModal();
}

async function generatePassUsername() {
    console.log('=== generatePassUsername called ===');
    console.log('Current Onboarding ID:', currentOnboardingId);
    
    if (!currentOnboardingId) {
        console.error('No onboarding ID available for username generation');
        return;
    }
    
    // First, try to get data from backend
    try {
        console.log('Fetching username from:', `/demo/${currentOnboardingId}/generate-username`);
        const response = await fetch(`/demo/${currentOnboardingId}/generate-username`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        });

        console.log('Response status:', response.status);
        
        if (response.ok) {
            const data = await response.json();
            console.log('Received data:', data);
            
            // Update system ID field (tutors table primary key)
            const systemIdField = document.getElementById('pass_system_id');
            if (systemIdField && data.system_id) {
                systemIdField.value = data.system_id;
                console.log('System ID set to:', data.system_id);
            }
            
            // Update username field
            const usernameField = document.getElementById('pass_username');
            if (usernameField && data.username) {
                usernameField.value = data.username;
                console.log('Username set to:', data.username);
            }
            
            // Update tutor information display
            if (data.tutor_name) {
                document.getElementById('passTutorName').textContent = data.tutor_name;
                console.log('Tutor name set to:', data.tutor_name);
            }
            if (data.tutor_email) {
                document.getElementById('passTutorEmail').textContent = data.tutor_email;
                console.log('Tutor email set to:', data.tutor_email);
            }
            if (data.assigned_account) {
                document.getElementById('passAssignedAccount').textContent = data.assigned_account;
                console.log('Assigned account set to:', data.assigned_account);
            }
        } else {
            console.error('Failed to generate username. Response status:', response.status);
            const errorText = await response.text();
            console.error('Error response:', errorText);
            // Fall through to local generation
            generateLocalCredentials();
        }
    } catch (error) {
        console.error('Error generating username:', error);
        // Fall through to local generation
        generateLocalCredentials();
    }
}

function generateLocalCredentials() {
    console.log('=== generateLocalCredentials called ===');
    
    // Generate system ID (tutorID) - format: OGS-T0001, OGS-T0002, etc.
    const timestamp = Date.now();
    const systemId = 'OGS-T' + String(timestamp).slice(-4);
    
    // Generate username from applicant name
    let username = '';
    if (window.currentApplicantInfo && window.currentApplicantInfo.name) {
        const name = window.currentApplicantInfo.name.toLowerCase();
        username = name.replace(/[^a-z0-9]/g, '');
        // Add timestamp suffix to ensure uniqueness
        username += String(timestamp).slice(-3);
    } else {
        username = 'tutor' + String(timestamp).slice(-4);
    }
    
    console.log('Generated credentials:', { systemId, username });
    
    // Update system ID field
    const systemIdField = document.getElementById('pass_system_id');
    console.log('System ID field found:', !!systemIdField);
    if (systemIdField) {
        systemIdField.value = systemId;
        console.log('Local System ID set to:', systemId);
    } else {
        console.error('System ID field not found!');
    }
    
    // Update username field
    const usernameField = document.getElementById('pass_username');
    console.log('Username field found:', !!usernameField);
    if (usernameField) {
        usernameField.value = username;
        console.log('Local Username set to:', username);
    } else {
        console.error('Username field not found!');
    }
    
    // Ensure password field has default value
    const passwordField = document.getElementById('pass_password');
    console.log('Password field found:', !!passwordField);
    if (passwordField) {
        passwordField.value = 'OGSConnect2025';
        console.log('Password set to default:', passwordField.value);
    }
    
    // Force a small delay to ensure DOM is ready
    setTimeout(() => {
        console.log('Final field values after timeout:');
        console.log('System ID:', document.getElementById('pass_system_id')?.value);
        console.log('Username:', document.getElementById('pass_username')?.value);
        console.log('Password:', document.getElementById('pass_password')?.value);
    }, 100);
}

async function submitOnboardingPassForm() {
    console.log('=== submitOnboardingPassForm function called ===');
    
    // Prevent multiple submissions
    const submitButton = document.querySelector('button[onclick="submitOnboardingPassForm();"]');
    if (submitButton && submitButton.disabled) {
        console.log('Form already being submitted, ignoring duplicate call');
        return;
    }
    
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Registering...';
    }
    
    // Check if we have a valid onboarding ID
    if (!currentOnboardingId) {
        console.error('No currentOnboardingId found!');
        showModalError('Error: No applicant selected. Please try again.');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = 'Register Tutor';
        }
        return;
    }
    
    try {
        console.log('Starting tutor registration...');
        console.log('Current Onboarding ID:', currentOnboardingId);
        
        const form = document.getElementById('onboardingPassForm');
        if (!form) {
            console.error('Form not found!');
            showModalError('Error: Form not found. Please refresh the page.');
            return;
        }
        
        console.log('Form found:', form);
        console.log('Form validity:', form.checkValidity());
        
        // Get form values manually to ensure we have them
        const interviewerField = document.getElementById('onboarding_pass_interviewer');
        const passwordField = document.getElementById('pass_password');
        const notesField = document.getElementById('pass_notes');
        
        console.log('Field elements found:', {
            interviewerField: !!interviewerField,
            passwordField: !!passwordField,
            notesField: !!notesField
        });
        
        const interviewer = interviewerField ? interviewerField.value : '';
        const password = passwordField ? passwordField.value : '';
        const notes = notesField ? notesField.value : '';
        
        console.log('Manual form values:', { interviewer, password, notes });
        console.log('Interviewer field value length:', interviewer.length);
        
        if (!interviewer || !interviewer.trim()) {
            console.error('Interviewer field is empty or invalid');
            showModalError('Please enter the interviewer name.');
            // Focus on the interviewer field
            if (interviewerField) {
                interviewerField.focus();
            }
            // Re-enable button
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Register Tutor';
            }
            return;
        }
        
        if (!password || !password.trim()) {
            console.error('Password field is empty or invalid');
            showModalError('Please enter a password.');
            // Focus on the password field
            if (passwordField) {
                passwordField.focus();
            }
            // Re-enable button
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Register Tutor';
            }
            return;
        }
        
        const data = {
            interviewer: interviewer.trim(),
            password: password.trim(),
            notes: notes.trim()
        };
        
        console.log('Final data to send:', data);

        console.log('Sending request to:', `/demos/${currentOnboardingId}/register-tutor`);
        
        // Add CSRF token debugging
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.log('CSRF Token from meta tag:', csrfToken);
        console.log('CSRF Token from function:', getCsrfToken());
        
        const response = await fetch(`/demos/${currentOnboardingId}/register-tutor`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response text:', errorText);
            let errorData;
            try {
                errorData = JSON.parse(errorText);
            } catch (e) {
                errorData = { message: errorText };
            }
            console.error('Error response:', errorData);
            throw new Error(errorData.message || 'Failed to register tutor');
        }

        const result = await response.json();
        console.log('Success response:', result);
        
        if (result.success) {
            hideOnboardingPassModal();
            showModalSuccess('Tutor registered successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(result.message || 'Failed to register tutor');
        }
    } catch (error) {
        console.error('Error:', error);
        showModalError('Failed to register tutor: ' + error.message);
        // Re-enable button
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = 'Register Tutor';
        }
    }
}

async function submitOnboardingFail() {
    if (!currentOnboardingId) {
        showModalError('Error: No applicant selected. Please try again.');
        return;
    }
    
    const failReason = document.getElementById('onboarding_fail_reason').value;
    const interviewer = document.getElementById('onboarding_fail_interviewer').value;
    const notes = document.getElementById('onboarding_fail_notes').value;
    
    if (!failReason || !interviewer) {
        showModalError('Please fill in all required fields');
        return;
    }
    
    // Validate form using HTML5 validation
    const form = document.getElementById('onboardingFailForm');
    if (form && !form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    try {
        let requestData = {
            fail_reason: failReason,
            interviewer: interviewer,
            notes: notes || (failReason === 'missed' ? 'Missed' : '')
        };
        
        // Add specific data based on failure reason
        if (failReason === 'missed') {
            const newDemoTime = document.getElementById('onboarding_new_interview_time').value;
            if (!newDemoTime) {
                showModalError('Please select a new demo time');
                return;
            }
            requestData.new_interview_time = newDemoTime;
            requestData.keep_status = 'onboarding'; // Keep status as onboarding
        }
        
        const response = await fetch(`/demo/${currentOnboardingId}/fail`, {
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
            hideOnboardingFailModal();
            hideOnboardingPassFailModal();
            window.location.reload();
        } else {
            throw new Error(data.message || 'Failed to process failure action');
        }
    } catch (error) {
        console.error('Error in submitOnboardingFail:', error);
        showModalError('Failed to process failure action: ' + error.message);
    }
}
</script>