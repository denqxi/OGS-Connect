<!-- Success/Error Messages -->
@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<!-- Back Button -->
<div class="mb-4 flex justify-end">
    <a href="{{ route('hiring_onboarding.index', ['tab' => request('tab', 'demo')]) }}"
       class="flex items-center space-x-2 px-4 py-2 bg-[#606979] text-white rounded-full text-sm font-medium 
              hover:bg-[#4f5a66] transform transition duration-200 hover:scale-105"
       style="width: 200px; justify-content: center;">
        <i class="fas fa-arrow-left"></i>
        <span>Back</span>
    </a>
</div>

<!-- Form Header -->
<div class="bg-[#65DB7F] shadow-lg text-[#0E335D] font-bold text-center text-2xl rounded-md py-3 mb-6">
    APPLICANT DETAILS 
</div>

<!-- Progress Bar -->
<div x-show="showProgress" x-cloak class="fixed top-0 left-0 w-full h-1 bg-gray-200 z-50">
    <div x-ref="progressBar" class="h-full bg-blue-600 transition-all duration-2000 ease-out" style="width: 0%;"></div>
</div>

<!-- Form Container -->
<div class="bg-white rounded-xl shadow-md p-6 sm:p-10" x-data="{ 
    showModal: false, 
    showProgress: false,
    showFailModal: false,
    showPassModal: false,
    closeFailModal() {
        this.showProgress = true;
        this.showFailModal = false;
        this.$nextTick(() => {
            this.$refs.progressBar.style.width = '0%';
            setTimeout(() => { this.$refs.progressBar.style.width = '100%'; }, 10);
            setTimeout(() => {
                window.location.href = '{{ route('hiring_onboarding.applicant.showUneditable', $demo->id) }}?tab={{ request('tab', 'demo') }}';
            }, 2000);
        });
    },
    closePassModal() {
        this.showProgress = true;
        this.showPassModal = false;
        this.$nextTick(() => {
            this.$refs.progressBar.style.width = '0%';
            setTimeout(() => { this.$refs.progressBar.style.width = '100%'; }, 10);
            setTimeout(() => {
                window.location.href = '{{ route('hiring_onboarding.applicant.showUneditable', $demo->id) }}?tab={{ request('tab', 'demo') }}';
            }, 2000);
        });
    }
}">

    <form action="#" method="POST" class="space-y-6">
        <!-- Personal Information -->
        <div>
            <h3 class="font-semibold text-[#0E335D] text-lg mb-3">Personal Information</h3>
            <div class="grid md:grid-cols-3 gap-4 items-start">
                <!-- Row 1 -->
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">First Name <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="{{ $demo->first_name }}" class="p-2 border rounded-md w-full" readonly>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Last Name <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="{{ $demo->last_name }}" class="p-2 border rounded-md w-full" readonly>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Birth Date <span
                            class="text-red-600">*</span></label>
                    <input type="date" value="{{ $demo->birth_date ? $demo->birth_date->format('Y-m-d') : '' }}" class="p-2 border rounded-md w-full" readonly>
                </div>

                <!-- Row 2 -->
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Address <span class="text-red-600">*</span></label>
                    <input type="text" value="{{ $demo->address }}" class="p-2 border rounded-md w-full" readonly>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Contact Number <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="{{ $demo->contact_number }}" class="p-2 border rounded-md w-full" readonly>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Email <span class="text-red-600">*</span></label>
                    <input type="email" value="{{ $demo->email }}" class="p-2 border rounded-md w-full" readonly>
                </div>

                <!-- Row 3 -->
                <div class="flex flex-col md:col-span-3">
                    <label class="text-sm font-normal text-gray-500">MS Teams (e.g., live:.cid...)</label>
                    <input type="text" value="{{ $demo->ms_teams ?? 'Not provided' }}" class="p-2 border rounded-md w-full" readonly>
                </div>
            </div>
        </div>

        <hr class="my-10">

        <!-- Education & Work Background -->
        <div>
            <h3 class="font-semibold text-[#0E335D] text-lg mb-3">Education & Work Background</h3>
            <div class="grid md:grid-cols-2 gap-4 items-start">
                <!-- Highest Educational Attainment -->
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Highest Educational Attainment <span
                            class="text-red-600">*</span></label>
                    <select class="p-2 border rounded-md w-full" disabled>
                        <option value="{{ $demo->education }}" selected>
                            @switch($demo->education)
                                @case('shs')
                                    Senior High School
                                    @break
                                @case('college_undergrad')
                                    College Undergraduate
                                    @break
                                @case('bachelor')
                                    Bachelor's Degree
                                    @break
                                @case('master')
                                    Master's Degree
                                    @break
                                @case('doctorate')
                                    Doctorate
                                    @break
                                @default
                                    {{ ucfirst($demo->education) }}
                            @endswitch
                        </option>
                    </select>
                </div>

                <!-- ESL Teaching Experience -->
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">ESL Teaching Experience <span
                            class="text-red-600">*</span></label>
                    <select class="p-2 border rounded-md w-full" disabled>
                        <option value="{{ $demo->esl_experience }}" selected>
                            @switch($demo->esl_experience)
                                @case('na')
                                    No Experience
                                    @break
                                @case('1-2')
                                    1-2 years
                                    @break
                                @case('3-4')
                                    3-4 years
                                    @break
                                @case('5plus')
                                    5+ years
                                    @break
                                @default
                                    {{ ucfirst($demo->esl_experience) }}
                            @endswitch
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Requirements -->
        <div>
            <h3 class="font-semibold text-[#0E335D] text-lg mb-3">Requirements</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Resume Link (GDrive / GDocs) <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="{{ $demo->resume_link }}"
                        class="p-2 border rounded-md w-full" readonly>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Intro Video (GDrive Link) <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="{{ $demo->intro_video }}"
                        class="p-2 border rounded-md w-full" readonly>
                </div>

                <!-- Work Setup -->
                <div class="md:col-span-2 mt-2">
                    <label class="text-sm font-normal text-gray-500 font-semibold">Work Setup:</label>
                </div>
                <div class="flex space-x-6 md:col-span-2">
                    <label class="inline-flex items-center">
                        <input type="radio" name="workSetup" value="work_from_home" 
                               {{ $demo->work_type === 'work_from_home' ? 'checked' : '' }} disabled>
                        <span class="ml-2">Work from Home</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="workSetup" value="work_at_site" 
                               {{ $demo->work_type === 'work_at_site' ? 'checked' : '' }} disabled>
                        <span class="ml-2">Work at Site</span>
                    </label>
                </div>

                <div class="grid md:grid-cols-3 gap-4 mt-4 md:col-span-2">
                    <div class="flex flex-col">
                        <label class="text-sm font-normal text-gray-500">Ookla Speedtest (GDrive Link)</label>
                        <input id="speedtestField" type="text" value="{{ $demo->speedtest ?? 'Not provided' }}"
                            class="p-2 border rounded-md w-full" readonly>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-normal text-gray-500">Main Device Specs (dxdiag Screenshot)</label>
                        <input id="mainDeviceField" type="text" value="{{ $demo->main_device ?? 'Not provided' }}"
                            class="p-2 border rounded-md w-full" readonly>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-normal text-gray-500">Backup Device Specs (dxdiag
                            Screenshot)</label>
                        <input id="backupDeviceField" type="text"
                            value="{{ $demo->backup_device ?? 'Not provided' }}" class="p-2 border rounded-md w-full" readonly>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- How Did You Hear About Us? -->
        <div>
            <h3 class="font-semibold text-[#0E335D] text-lg mb-3">How Did You Hear About Us?</h3>
            <div class="flex flex-col md:flex-row md:items-center md:space-x-4">
                <div class="flex space-x-6 mb-4 md:mb-0">
                    <label class="inline-flex items-center">
                        <input type="radio" {{ $demo->source === 'fb_boosting' ? 'checked' : '' }} disabled>
                        <span class="ml-2">FB Boosting</span>
                    </label> 
                    <label class="inline-flex items-center">
                        <input type="radio" {{ $demo->source === 'referral' ? 'checked' : '' }} disabled>
                        <span class="ml-2">Referral</span>
                    </label>
                </div>
                @if($demo->source === 'referral')
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Referrer Name <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="{{ $demo->referrer_name }}" class="p-2 border rounded-md w-64" readonly>
                </div>
                @endif
            </div>
        </div>

        <hr class="my-4">

        <!-- Work Preference -->
        <div>
            <h3 class="font-semibold text-[#0E335D] text-lg mb-3">Work Preference</h3>
            <div class="grid md:grid-cols-3 gap-6">

                <!-- Column 1: Working Availability -->
                <div class="flex flex-col h-full">
                    <label class="text-sm font-normal text-gray-500 mb-2">Working Availability</label>
                    <div class="p-4 border rounded-lg shadow-lg flex-1">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <select class="p-2 border rounded-md w-full" disabled>
                                <option selected>{{ $demo->start_time }}</option>
                            </select>
                            <select class="p-2 border rounded-md w-full" disabled>
                                <option selected>{{ $demo->end_time }}</option>
                            </select>
                        </div>
                        <div class="text-sm font-medium text-gray-700 mb-2">Days Available:</div>
                        <div class="grid grid-cols-4 gap-2 mb-2">
                            <label><input type="checkbox" {{ in_array('monday', $demo->days) ? 'checked' : '' }} disabled> Mon</label>
                            <label><input type="checkbox" {{ in_array('tuesday', $demo->days) ? 'checked' : '' }} disabled> Tue</label>
                            <label><input type="checkbox" {{ in_array('wednesday', $demo->days) ? 'checked' : '' }} disabled> Wed</label>
                            <label><input type="checkbox" {{ in_array('thursday', $demo->days) ? 'checked' : '' }} disabled> Thu</label>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <label><input type="checkbox" {{ in_array('friday', $demo->days) ? 'checked' : '' }} disabled> Fri</label>
                            <label><input type="checkbox" {{ in_array('saturday', $demo->days) ? 'checked' : '' }} disabled> Sat</label>
                            <label><input type="checkbox" {{ in_array('sunday', $demo->days) ? 'checked' : '' }} disabled> Sun</label>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Platform Familiarity + Preferred Time -->
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <label class="text-sm font-normal text-gray-500 mb-2">Platform Familiarity</label>
                        <div class="p-4 border rounded-lg shadow-lg mb-4">
                            <div class="grid grid-cols-3 gap-2 mb-2">
                                <label><input type="checkbox" {{ in_array('classin', $demo->platforms) ? 'checked' : '' }} disabled> ClassIn</label>
                                <label><input type="checkbox" {{ in_array('zoom', $demo->platforms) ? 'checked' : '' }} disabled> Zoom</label>
                                <label><input type="checkbox" {{ in_array('voov', $demo->platforms) ? 'checked' : '' }} disabled> Voov</label>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <label><input type="checkbox" {{ in_array('ms_teams', $demo->platforms) ? 'checked' : '' }} disabled> MS Teams</label>
                                <label><input type="checkbox" {{ in_array('others', $demo->platforms) ? 'checked' : '' }} disabled> Others</label>
                            </div>
                        </div>

                        <label class="text-sm font-normal text-gray-500 mb-2">Preferred Schedule (Date & Time)</label>
                        <div class="p-4 border rounded-lg shadow-lg">
                            <input type="datetime-local" value="{{ $demo->interview_time }}"
                                class="p-2 border rounded-md w-full" readonly>
                        </div>
                    </div>
                </div>

                <!-- Column 3: Can Teach + CALL Button -->
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <label class="text-sm font-normal text-gray-500 mb-2">Can Teach</label>
                        <div class="p-4 border rounded-lg shadow-lg space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <label><input type="checkbox" {{ in_array('kids', $demo->can_teach) ? 'checked' : '' }} disabled> Kids</label>
                                <label><input type="checkbox" {{ in_array('teenager', $demo->can_teach) ? 'checked' : '' }} disabled> Teenager</label>
                            </div>
                            <div>
                                <label><input type="checkbox" {{ in_array('adults', $demo->can_teach) ? 'checked' : '' }} disabled> Adults</label>
                            </div>
                        </div>
                    </div>

                    <!-- Status Information -->
                    <div class="mt-6 mb-4 p-4 bg-gray-50 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Current Status:</span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $demo->statusColor() }}">
                            @switch($demo->status)
                                    @case('no_answer')
                                        No Answer
                                        @break
                                    @case('re_schedule')
                                        Re-schedule
                                        @break
                                    @case('declined')
                                        Declined
                                        @break
                                    @case('not_recommended')
                                        Not Recommended
                                        @break
                                    @default
                                        {{ ucwords(str_replace('_', ' ', $demo->status)) }}
                                @endswitch
                            </span>
                        </div>
                        @if($demo->attempt_count > 0)
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Attempt Count:</span>
                            <span class="text-sm font-bold text-gray-900">{{ $demo->attempt_count }}/3</span>
                        </div>
                        @endif
                        @if($demo->interviewer)
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Last Interviewer:</span>
                            <span class="text-sm text-gray-900">{{ $demo->interviewer }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if ($demo->status === 'demo')
        <div class="mt-6 border-t pt-4 text-center">
            <p class="text-gray-600 text-sm mb-3">
                Review the applicant's demo performance and finalize the hiring decision below.
            </p>
            <div class="flex justify-center gap-4">
                <button type="button" onclick="showNotHiredConfirmation()"
                    class="bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">
                    FAILED
                </button>
                <button type="button" onclick="showHiredConfirmation()"
                    class="bg-[#65DB7F] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">
                    PASSED
                </button>
            </div>
        </div>
    @endif

    <!-- Not Hired Confirmation Modal -->
    <div id="notHiredModal" style="display: none; position: fixed; inset: 0; z-index: 100;"
        class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
            <div class="flex justify-between items-center px-6 py-3 bg-[#F65353] rounded-t-lg" id="failModalHeader">
                <h2 class="text-white font-bold text-lg" id="failModalTitle">Demo Failed - Reason</h2>
                <button onclick="hideNotHiredConfirmation()" class="text-white text-2xl font-bold hover:opacity-75">&times;</button>
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
                <!-- Interviewer -->
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Interviewer:</label>
                    <input type="text" id="fail_interviewer" name="fail_interviewer" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]"
                        placeholder="Enter interviewer name" value="{{ $demo->interviewer ?? '' }}">
                </div>

                <!-- Failure Reason -->
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Failure Reason:</label>
                    <select id="fail_reason" name="fail_reason" onchange="toggleFailFields()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]">
                        <option value="" disabled selected>Select a reason</option>
                        <option value="missed">Missed Demo</option>
                        <option value="declined">Declined</option>
                        <option value="not_recommended">Not Recommended</option>
                        <option value="transfer_account">Transfer Account</option>
                    </select>
                </div>

                <!-- New Demo Time (for missed) -->
                <div id="new_demo_time_section" style="display: none;">
                    <label class="block text-gray-700 text-sm font-medium mb-2">New Demo Time:</label>
                    <input type="datetime-local" id="new_demo_time" name="new_demo_time"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]"
                        min="{{ now()->format('Y-m-d\TH:i') }}">
                </div>

                <!-- Transfer Account Fields -->
                <div id="transfer_account_section" style="display: none;" class="space-y-4">
                    <!-- Interviewer field moved to top of transfer account section -->
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2">Interviewer:</label>
                        <input type="text" id="transfer_interviewer" name="transfer_interviewer" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter interviewer name" value="{{ $demo->interviewer ?? '' }}">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2">New Assigned Account:</label>
                        <select id="transfer_assigned_account" name="transfer_assigned_account"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="" disabled selected>Select a different account</option>
                            @php
                                $currentAccount = $demo->assigned_account ?? '';
                                $allAccounts = ['tutlo', 'talk915', 'gl5', 'babilala'];
                                $availableAccounts = array_filter($allAccounts, function($account) use ($currentAccount) {
                                    return $account !== $currentAccount;
                                });
                            @endphp
                            @foreach($availableAccounts as $account)
                                <option value="{{ $account }}">{{ ucwords(str_replace('_', ' ', $account)) }}</option>
                            @endforeach
                        </select>
                    </div>

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

                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2">New Schedule:</label>
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
                    <button type="button" onclick="hideNotHiredConfirmation()"
                        class="bg-gray-200 text-gray-700 px-6 py-2 rounded-full font-semibold hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button type="button" onclick="submitFailAction()"
                        class="bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hired Confirmation Modal -->
    <div id="hiredModal" style="display: none; position: fixed; inset: 0; z-index: 100;"
        class="flex justify-center items-center w-full h-full bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg w-96">
            <div class="p-6 text-center">
                <div class="flex justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-green-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-bold text-lg text-gray-800">Confirmation Passing</h3>
                <p class="text-gray-600 mt-2">
                    Are you sure you want to <span class="font-bold text-green-600">Pass</span> this applicant?<br>
                    They will be moved to the <span class="font-semibold text-blue-600">onboarding</span> stage.
                </p>
            </div>
            <div class="flex justify-center gap-4 pb-6">
                <button onclick="hideHiredConfirmation()"
                    class="bg-gray-200 text-gray-700 px-6 py-2 rounded-full font-semibold hover:bg-gray-300 transition">
                    Cancel
                </button>
                <form action="{{ route('applicants.finalize', $demo->id) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="decision" value="success">
                    <button type="submit"
                        class="bg-[#65DB7F] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition">
                        Confirm Pass
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Error handling functions
    function showFailModalError(message) {
        const errorDiv = document.getElementById('failModalError');
        const errorMessage = document.getElementById('failModalErrorMessage');
        
        errorMessage.textContent = message;
        errorDiv.classList.remove('hidden');
    }

    function hideFailModalError() {
        document.getElementById('failModalError').classList.add('hidden');
    }

    function showNotHiredConfirmation() {
        // Reset form
        document.getElementById('fail_reason').value = '';
        document.getElementById('fail_interviewer').value = '{{ $demo->interviewer ?? "" }}';
        document.getElementById('transfer_interviewer').value = '{{ $demo->interviewer ?? "" }}';
        document.getElementById('new_demo_time').value = '';
        document.getElementById('transfer_assigned_account').value = '';
        document.getElementById('transfer_status').value = '';
        document.getElementById('transfer_schedule').value = '';
        document.getElementById('fail_notes').value = '';
        
        // Hide all conditional sections
        document.getElementById('new_demo_time_section').style.display = 'none';
        document.getElementById('transfer_account_section').style.display = 'none';
        
        // Reset colors and title to default (red)
        const modalHeader = document.getElementById('failModalHeader');
        const modalTitle = document.getElementById('failModalTitle');
        const submitButton = document.querySelector('#notHiredModal button[onclick="submitFailAction()"]');
        if (modalHeader) {
            modalHeader.className = 'flex justify-between items-center px-6 py-3 bg-[#F65353] rounded-t-lg';
        }
        if (modalTitle) {
            modalTitle.textContent = 'Demo Failed - Reason';
        }
        if (submitButton) {
            submitButton.className = 'bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
        }
        
        // Hide any previous errors
        hideFailModalError();
        
        document.getElementById('notHiredModal').style.display = 'flex';
    }

    function hideNotHiredConfirmation() {
        document.getElementById('notHiredModal').style.display = 'none';
    }

    function toggleFailFields() {
        const failReason = document.getElementById('fail_reason').value;
        const newTimeSection = document.getElementById('new_demo_time_section');
        const transferSection = document.getElementById('transfer_account_section');
        const modalHeader = document.getElementById('failModalHeader');
        const modalTitle = document.getElementById('failModalTitle');
        const submitButton = document.querySelector('#notHiredModal button[onclick="submitFailAction()"]');
        
        // Get input elements for styling
        const failReasonSelect = document.getElementById('fail_reason');
        const failNotesTextarea = document.getElementById('fail_notes');
        const newDemoTimeInput = document.getElementById('new_demo_time');
        const failInterviewerInput = document.getElementById('fail_interviewer');
        const transferInterviewerInput = document.getElementById('transfer_interviewer');
        const transferAccountSelect = document.getElementById('transfer_assigned_account');
        const transferStatusSelect = document.getElementById('transfer_status');
        const transferScheduleInput = document.getElementById('transfer_schedule');
        
        // Hide all sections first
        newTimeSection.style.display = 'none';
        transferSection.style.display = 'none';
        
        // Change header, title, and button colors based on fail reason
        if (failReason === 'transfer_account') {
            modalHeader.className = 'flex justify-between items-center px-6 py-3 bg-blue-500 rounded-t-lg';
            modalTitle.textContent = 'Transfer Account';
            submitButton.className = 'bg-blue-500 text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
            
            // Update input field focus colors to blue
            failReasonSelect.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
            failNotesTextarea.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none';
            newDemoTimeInput.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
            failInterviewerInput.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
            transferInterviewerInput.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
            transferAccountSelect.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
            transferStatusSelect.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
            transferScheduleInput.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500';
        } else {
            modalHeader.className = 'flex justify-between items-center px-6 py-3 bg-[#F65353] rounded-t-lg';
            modalTitle.textContent = 'Demo Failed - Reason';
            submitButton.className = 'bg-[#F65353] text-white px-6 py-2 rounded-full font-semibold hover:opacity-90 transition';
            
            // Update input field focus colors to red
            failReasonSelect.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
            failNotesTextarea.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353] resize-none';
            newDemoTimeInput.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
            failInterviewerInput.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
            transferInterviewerInput.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
            transferAccountSelect.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
            transferStatusSelect.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
            transferScheduleInput.className = 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#F65353]';
        }
        
        // Show relevant section based on selection
        if (failReason === 'missed') {
            newTimeSection.style.display = 'block';
        } else if (failReason === 'transfer_account') {
            transferSection.style.display = 'block';
        }
    }

    async function submitFailAction() {
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
            const newTime = document.getElementById('new_demo_time').value;
            if (!newTime) {
                showFailModalError('Please select a new demo time for missed demo');
                return;
            }
        } else if (failReason === 'transfer_account') {
            const transferInterviewer = document.getElementById('transfer_interviewer').value;
            const transferAccount = document.getElementById('transfer_assigned_account').value;
            const transferStatus = document.getElementById('transfer_status').value;
            const transferSchedule = document.getElementById('transfer_schedule').value;
            
            if (!transferInterviewer || !transferAccount || !transferStatus || !transferSchedule) {
                showFailModalError('Please fill in all transfer account fields');
                return;
            }
        }
        
        try {
            let requestData = {
                fail_reason: failReason,
                interviewer: interviewer,
                notes: notes
            };
            
            // Add specific data based on failure reason
            if (failReason === 'missed') {
                const newTime = document.getElementById('new_demo_time').value;
                requestData.new_interview_time = newTime;
            } else if (failReason === 'transfer_account') {
                const transferInterviewer = document.getElementById('transfer_interviewer').value;
                const transferAccount = document.getElementById('transfer_assigned_account').value;
                const transferStatus = document.getElementById('transfer_status').value;
                const transferSchedule = document.getElementById('transfer_schedule').value;
                
                // Use the transfer interviewer for transfer account actions
                requestData.interviewer = transferInterviewer;
                requestData.transfer_data = {
                    assigned_account: transferAccount,
                    new_status: transferStatus,
                    schedule: transferSchedule
                };
            }
            
            const response = await fetch(`/demo/{{ $demo->id }}/fail`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
                hideNotHiredConfirmation();
                // Redirect back to For Demo tab
                window.location.href = '{{ route("hiring_onboarding.index", ["tab" => "demo"]) }}';
            } else {
                throw new Error(data.message || 'Failed to process failure action');
            }
        } catch (error) {
            console.error('Error:', error);
            showFailModalError('Failed to process failure action: ' + error.message);
        }
    }

    function showHiredConfirmation() {
        document.getElementById('hiredModal').style.display = 'flex';
    }

    function hideHiredConfirmation() {
        document.getElementById('hiredModal').style.display = 'none';
    }
    </script>
</div>
