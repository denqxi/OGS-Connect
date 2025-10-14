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
    <a href="{{ route('hiring_onboarding.index') }}"
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
                window.location.href = '{{ route('hiring_onboarding.applicant.show', $application->id) }}';
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
                window.location.href = '{{ route('hiring_onboarding.applicant.show', $application->id) }}';
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
                    <input type="text" value="{{ $application->first_name }}" class="p-2 border rounded-md w-full" readonly>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Last Name <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="{{ $application->last_name }}" class="p-2 border rounded-md w-full" readonly>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Birth Date <span
                            class="text-red-600">*</span></label>
                    <input type="date" value="{{ $application->birth_date ? $application->birth_date->format('Y-m-d') : '' }}" class="p-2 border rounded-md w-full" readonly>
                </div>

                <!-- Row 2 -->
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Address <span class="text-red-600">*</span></label>
                    <input type="text" value="{{ $application->address }}" class="p-2 border rounded-md w-full" readonly>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Contact Number <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="{{ $application->contact_number }}" class="p-2 border rounded-md w-full" readonly>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Email <span class="text-red-600">*</span></label>
                    <input type="email" value="{{ $application->email }}" class="p-2 border rounded-md w-full" readonly>
                </div>

                <!-- Row 3 -->
                <div class="flex flex-col md:col-span-3">
                    <label class="text-sm font-normal text-gray-500">MS Teams (e.g., live:.cid...)</label>
                    <input type="text" value="{{ $application->ms_teams ?? 'Not provided' }}" class="p-2 border rounded-md w-full" readonly>
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
                        <option value="{{ $application->education }}" selected>
                            @switch($application->education)
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
                                    {{ ucfirst($application->education) }}
                            @endswitch
                        </option>
                    </select>
                </div>

                <!-- ESL Teaching Experience -->
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">ESL Teaching Experience <span
                            class="text-red-600">*</span></label>
                    <select class="p-2 border rounded-md w-full" disabled>
                        <option value="{{ $application->esl_experience }}" selected>
                            @switch($application->esl_experience)
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
                                    {{ ucfirst($application->esl_experience) }}
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
                    <input type="text" value="{{ $application->resume_link }}"
                        class="p-2 border rounded-md w-full" readonly>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Intro Video (GDrive Link) <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="{{ $application->intro_video }}"
                        class="p-2 border rounded-md w-full" readonly>
                </div>

                <!-- Work Setup -->
                <div class="md:col-span-2 mt-2">
                    <label class="text-sm font-normal text-gray-500 font-semibold">Work Setup:</label>
                </div>
                <div class="flex space-x-6 md:col-span-2">
                    <label class="inline-flex items-center">
                        <input type="radio" name="work_type" value="work_from_home" 
                               {{ $application->work_type === 'work_from_home' ? 'checked' : '' }} disabled>
                        <span class="ml-2">Work from Home</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="work_type" value="work_at_site" 
                               {{ $application->work_type === 'work_at_site' ? 'checked' : '' }} disabled>
                        <span class="ml-2">Work at Site</span>
                    </label>
                </div>

                <div class="grid md:grid-cols-3 gap-4 mt-4 md:col-span-2">
                    <div class="flex flex-col">
                        <label class="text-sm font-normal text-gray-500">Ookla Speedtest (GDrive Link)</label>
                        <input id="speedtestField" type="text" value="{{ $application->speedtest ?? 'Not provided' }}"
                            class="p-2 border rounded-md w-full" readonly>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-normal text-gray-500">Main Device Specs (dxdiag Screenshot)</label>
                        <input id="mainDeviceField" type="text" value="{{ $application->main_device ?? 'Not provided' }}"
                            class="p-2 border rounded-md w-full" readonly>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-normal text-gray-500">Backup Device Specs (dxdiag
                            Screenshot)</label>
                        <input id="backupDeviceField" type="text"
                            value="{{ $application->backup_device ?? 'Not provided' }}" class="p-2 border rounded-md w-full" readonly>
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
                        <input type="radio" name="source" value="fb_boosting" {{ $application->source === 'fb_boosting' ? 'checked' : '' }} disabled>
                        <span class="ml-2">FB Boosting</span>
                    </label> 
                    <label class="inline-flex items-center">
                        <input type="radio" name="source" value="referral" {{ $application->source === 'referral' ? 'checked' : '' }} disabled>
                        <span class="ml-2">Referral</span>
                    </label>
                </div>
                @if($application->source === 'referral')
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Referrer Name <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="{{ $application->referrer_name }}" class="p-2 border rounded-md w-64" readonly>
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
                                <option selected>{{ $application->start_time }}</option>
                            </select>
                            <select class="p-2 border rounded-md w-full" disabled>
                                <option selected>{{ $application->end_time }}</option>
                            </select>
                        </div>
                        <div class="text-sm font-medium text-gray-700 mb-2">Days Available:</div>
                        <div class="grid grid-cols-4 gap-2 mb-2">
                            <label><input type="checkbox" {{ (is_array($application->days) && in_array('monday', $application->days)) ? 'checked' : '' }} disabled> Mon</label>
                            <label><input type="checkbox" {{ (is_array($application->days) && in_array('tuesday', $application->days)) ? 'checked' : '' }} disabled> Tue</label>
                            <label><input type="checkbox" {{ (is_array($application->days) && in_array('wednesday', $application->days)) ? 'checked' : '' }} disabled> Wed</label>
                            <label><input type="checkbox" {{ (is_array($application->days) && in_array('thursday', $application->days)) ? 'checked' : '' }} disabled> Thu</label>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <label><input type="checkbox" {{ (is_array($application->days) && in_array('friday', $application->days)) ? 'checked' : '' }} disabled> Fri</label>
                            <label><input type="checkbox" {{ (is_array($application->days) && in_array('saturday', $application->days)) ? 'checked' : '' }} disabled> Sat</label>
                            <label><input type="checkbox" {{ (is_array($application->days) && in_array('sunday', $application->days)) ? 'checked' : '' }} disabled> Sun</label>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Platform Familiarity + Preferred Time -->
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <label class="text-sm font-normal text-gray-500 mb-2">Platform Familiarity</label>
                        <div class="p-4 border rounded-lg shadow-lg mb-4">
                            <div class="grid grid-cols-3 gap-2 mb-2">
                                <label><input type="checkbox" {{ (is_array($application->platforms) && in_array('classin', $application->platforms)) ? 'checked' : '' }} disabled> ClassIn</label>
                                <label><input type="checkbox" {{ (is_array($application->platforms) && in_array('zoom', $application->platforms)) ? 'checked' : '' }} disabled> Zoom</label>
                                <label><input type="checkbox" {{ (is_array($application->platforms) && in_array('voov', $application->platforms)) ? 'checked' : '' }} disabled> Voov</label>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <label><input type="checkbox" {{ (is_array($application->platforms) && in_array('ms_teams', $application->platforms)) ? 'checked' : '' }} disabled> MS Teams</label>
                                <label><input type="checkbox" {{ (is_array($application->platforms) && in_array('others', $application->platforms)) ? 'checked' : '' }} disabled> Others</label>
                            </div>
                        </div>

                        <label class="text-sm font-normal text-gray-500 mb-2">Preferred Time for Interview Call</label>
                        <div class="p-4 border rounded-lg shadow-lg">
                            <input type="datetime-local" value="{{ $application->interview_time }}"
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
                                <label><input type="checkbox" {{ (is_array($application->can_teach) && in_array('kids', $application->can_teach)) ? 'checked' : '' }} disabled> Kids</label>
                                <label><input type="checkbox" {{ (is_array($application->can_teach) && in_array('teenager', $application->can_teach)) ? 'checked' : '' }} disabled> Teenager</label>
                            </div>
                            <div>
                                <label><input type="checkbox" {{ (is_array($application->can_teach) && in_array('adults', $application->can_teach)) ? 'checked' : '' }} disabled> Adults</label>
                            </div>
                        </div>
                    </div>

                    <!-- Status Information -->
                    <div class="mt-6 mb-4 p-4 bg-gray-50 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Current Status:</span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $application->statusColor() }}">
                            @switch($application->status)
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
                                        {{ ucwords(str_replace('_', ' ', $application->status)) }}
                                @endswitch
                            </span>
                        </div>
                        @if($application->attempt_count > 0)
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Attempt Count:</span>
                            <span class="text-sm font-bold text-gray-900">{{ $application->attempt_count }}/3</span>
                        </div>
                        @endif
                        @if($application->interviewer)
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Last Interviewer:</span>
                            <span class="text-sm text-gray-900">{{ $application->interviewer }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- CALL BUTTON slightly higher from bottom -->
                    <div class="flex mt-6 mb-4">
                        <button type="button" @click="showModal = true"
                            class="w-full px-6 py-2 rounded-full bg-[#636363] text-white hover:opacity-90">
                            CALL
                        </button>
                    </div>
                    <div>
                        
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Include Call Modal -->
    @include('hiring_onboarding.tabs.partials.modals.call_mdl')
</div>

