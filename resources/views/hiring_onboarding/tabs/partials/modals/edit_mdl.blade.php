{{-- 
    ============================================================================
    SCREENING MODALS - Blade Template
    ============================================================================
    Contains all modal dialogs for the hiring & onboarding screening process
    
    JavaScript functionality is in: public/js/screening-modals.js
    Controller: App\Http\Controllers\ApplicationFormController
    
    @author OGS Connect
    @version 1.0.0
    ============================================================================
--}}

@php
    // Common dropdown data
    $accounts = ['tutlo', 'talk915', 'gl5', 'babilala'];
    $statuses = ['screening', 'training', 'demo', 'onboarding'];
@endphp

{{-- ======================================================================== --}}
{{-- EDIT SCREENING MODAL                                                    --}}
{{-- ======================================================================== --}}
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
                    <svg class="h-5 w-5 text-red-100" viewBox="0 0 20 20" fill="currentColor">
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
                        <option value="">Select an Account</option>
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

{{-- ======================================================================== --}}
{{-- FAIL CONFIRMATION MODAL                                                 --}}
{{-- ======================================================================== --}}
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

{{-- ======================================================================== --}}
{{-- PASS MODAL - Move applicant to next stage                               --}}
{{-- ======================================================================== --}}
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
                <input type="text" name="interviewer" id="pass_interviewer" required readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none bg-gray-50 text-gray-700 cursor-not-allowed"
                    placeholder="Enter interviewer name"
                    value="{{ Auth::guard('supervisor')->check() ? Auth::guard('supervisor')->user()->full_name : (auth()->user()->name ?? 'Unknown') }}">
            </div>


            <!-- Assign Account Field (Hidden - will use current account) -->
            <input type="hidden" name="assigned_account" id="pass_assigned_account">

            <!-- Next Status Field -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-medium mb-2">Next Status:</label>
                <div class="relative">
                    <select name="next_status" id="pass_next_status" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none appearance-none bg-white"
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
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none"
                    min="{{ now()->format('Y-m-d\TH:i') }}">
            </div>

            <!-- Notes Field -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2">Notes:</label>
                <textarea name="notes" id="pass_notes" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none resize-none"
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

{{-- ======================================================================== --}}
{{-- PASS CONFIRMATION MODAL                                                  --}}
{{-- ======================================================================== --}}
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

{{-- ======================================================================== --}}
{{-- PASS/FAIL CONFIRMATION MODAL                                             --}}
{{-- ======================================================================== --}}
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

{{-- ======================================================================== --}}
{{-- NEXT STEP MODAL                                                          --}}
{{-- ======================================================================== --}}
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
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none" required>
                </select>
            </div>

            <!-- Next Schedule -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Next Schedule:</label>
                <input type="datetime-local" id="next_schedule" name="next_schedule"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none"
                    min="{{ now()->format('Y-m-d\TH:i') }}" required>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Notes:</label>
                <textarea id="next_notes" name="next_notes" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none resize-none"></textarea>
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

{{-- ======================================================================== --}}
{{-- PASS APPLICANT CONFIRMATION MODAL                                        --}}
{{-- ======================================================================== --}}
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

{{-- ======================================================================== --}}
{{-- SUCCESS MODAL                                                            --}}
{{-- ======================================================================== --}}
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

{{-- ======================================================================== --}}
{{-- EMPLOYEE REGISTRATION MODAL                                              --}}
{{-- ======================================================================== --}}
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

{{-- ======================================================================== --}}
{{-- REGISTRATION SUCCESS MODAL                                               --}}
{{-- ======================================================================== --}}
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

{{-- ======================================================================== --}}
{{-- FAIL OPTIONS MODAL                                                       --}}
{{-- ======================================================================== --}}
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
                <input type="text" id="fail_interviewer" name="fail_interviewer" required readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none bg-gray-50 text-gray-700 cursor-not-allowed"
                    placeholder="Enter interviewer name"
                    value="{{ Auth::guard('supervisor')->check() ? Auth::guard('supervisor')->user()->full_name : (auth()->user()->name ?? 'Unknown') }}">
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

{{-- ======================================================================== --}}
{{-- DEMO DETAILS CONFIRMATION MODAL                                          --}}
{{-- ======================================================================== --}}
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

{{-- ======================================================================== --}}
{{-- DEMO CONFIRMATION MODAL                                                  --}}
{{-- ======================================================================== --}}
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

{{-- ======================================================================== --}}
{{-- DEMO PASS CONFIRMATION MODAL                                             --}}
{{-- ======================================================================== --}}
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

{{-- ======================================================================== --}}
{{-- DEMO FAIL CONFIRMATION MODAL                                             --}}
{{-- ======================================================================== --}}
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

{{-- ======================================================================== --}}
{{-- ONBOARDING PASS/FAIL MODAL                                               --}}
{{-- ======================================================================== --}}
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

{{-- ======================================================================== --}}
{{-- ONBOARDING PASS CONFIRMATION MODAL                                       --}}
{{-- ======================================================================== --}}
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

{{-- ======================================================================== --}}
{{-- ONBOARDING FAIL MODAL                                                    --}}
{{-- ======================================================================== --}}
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
                            <input type="text" id="onboarding_fail_interviewer" name="onboarding_fail_interviewer" required readonly
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none bg-gray-50 text-gray-700 cursor-not-allowed"
                                placeholder="Enter interviewer name"
                                value="{{ Auth::guard('supervisor')->check() ? Auth::guard('supervisor')->user()->full_name : (auth()->user()->name ?? 'Unknown') }}">
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

{{-- ======================================================================== --}}
{{-- ONBOARDING PASS MODAL - Tutor Registration                              --}}
{{-- ======================================================================== --}}
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
                    <input type="text" id="onboarding_pass_interviewer" name="pass_interviewer" required readonly
                        class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none bg-gray-50 text-gray-700 cursor-not-allowed"
                        placeholder="Enter interviewer name"
                        value="{{ Auth::guard('supervisor')->check() ? Auth::guard('supervisor')->user()->full_name : (auth()->user()->name ?? 'Unknown') }}">
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

{{-- ============================================================================ --}}
{{-- JAVASCRIPT - External file for better organization                          --}}
{{-- All modal functions are now in: public/js/screening-modals.js               --}}
{{-- ============================================================================ --}}
<script src="{{ asset('js/screening-modals.js') }}"></script>
