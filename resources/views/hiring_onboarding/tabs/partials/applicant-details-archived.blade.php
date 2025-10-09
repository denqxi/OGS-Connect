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
    <a href="{{ route('hiring_onboarding.index', ['tab' => 'archive']) }}"
       class="flex items-center space-x-2 px-4 py-2 bg-[#606979] text-white rounded-full text-sm font-medium 
              hover:bg-[#4f5a66] transform transition duration-200 hover:scale-105"
       style="width: 200px; justify-content: center;">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Archive</span>
    </a>
</div>

<!-- Form Header -->
<div class="bg-[#F29090] shadow-lg text-[#7A1F1F] font-bold text-center text-2xl rounded-md py-3 mb-6">
    ARCHIVED APPLICANT DETAILS 
</div>

<!-- Form Container -->
<div class="bg-white rounded-xl shadow-md p-6 sm:p-10">

    <form action="#" method="POST" class="space-y-6">
        <!-- Personal Information -->
        <div>
            <h3 class="font-semibold text-[#0E335D] text-lg mb-3">Personal Information</h3>
            <div class="grid md:grid-cols-3 gap-4 items-start">
                <!-- Row 1 -->
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">First Name <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="{{ $archivedApplication->first_name }}" class="p-2 border rounded-md w-full" readonly>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Last Name <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="{{ $archivedApplication->last_name }}" class="p-2 border rounded-md w-full" readonly>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Birth Date <span
                            class="text-red-600">*</span></label>
                    <input type="date" value="{{ $archivedApplication->birth_date ? $archivedApplication->birth_date->format('Y-m-d') : '' }}" class="p-2 border rounded-md w-full" readonly>
                </div>

                <!-- Row 2 -->
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Address <span class="text-red-600">*</span></label>
                    <input type="text" value="{{ $archivedApplication->address }}" class="p-2 border rounded-md w-full" readonly>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Contact Number <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="{{ $archivedApplication->contact_number }}" class="p-2 border rounded-md w-full" readonly>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Email <span class="text-red-600">*</span></label>
                    <input type="email" value="{{ $archivedApplication->email }}" class="p-2 border rounded-md w-full" readonly>
                </div>

                <!-- Row 3 -->
                <div class="flex flex-col md:col-span-3">
                    <label class="text-sm font-normal text-gray-500">MS Teams (e.g., live:.cid...)</label>
                    <input type="text" value="{{ $archivedApplication->ms_teams ?? 'Not provided' }}" class="p-2 border rounded-md w-full" readonly>
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
                        <option value="{{ $archivedApplication->education }}" selected>
                            @switch($archivedApplication->education)
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
                                    {{ ucfirst($archivedApplication->education) }}
                            @endswitch
                        </option>
                    </select>
                </div>

                <!-- ESL Teaching Experience -->
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">ESL Teaching Experience <span
                            class="text-red-600">*</span></label>
                    <select class="p-2 border rounded-md w-full" disabled>
                        <option value="{{ $archivedApplication->esl_experience }}" selected>
                            @switch($archivedApplication->esl_experience)
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
                                    {{ ucfirst($archivedApplication->esl_experience) }}
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
                    <input type="text" value="{{ $archivedApplication->resume_link }}"
                        class="p-2 border rounded-md w-full" readonly>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Intro Video (GDrive Link) <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="{{ $archivedApplication->intro_video }}"
                        class="p-2 border rounded-md w-full" readonly>
                </div>

                <!-- Work Setup -->
                <div class="md:col-span-2 mt-2">
                    <label class="text-sm font-normal text-gray-500 font-semibold">Work Setup:</label>
                </div>
                <div class="flex space-x-6 md:col-span-2">
                    <label class="inline-flex items-center">
                        <input type="radio" name="workSetup" value="work_from_home" 
                               {{ $archivedApplication->work_type === 'work_from_home' ? 'checked' : '' }} disabled>
                        <span class="ml-2">Work from Home</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="workSetup" value="work_at_site" 
                               {{ $archivedApplication->work_type === 'work_at_site' ? 'checked' : '' }} disabled>
                        <span class="ml-2">Work at Site</span>
                    </label>
                </div>

                <div class="grid md:grid-cols-3 gap-4 mt-4 md:col-span-2">
                    <div class="flex flex-col">
                        <label class="text-sm font-normal text-gray-500">Ookla Speedtest (GDrive Link)</label>
                        <input id="speedtestField" type="text" value="{{ $archivedApplication->speedtest ?? 'Not provided' }}"
                            class="p-2 border rounded-md w-full" readonly>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-normal text-gray-500">Main Device Specs (dxdiag Screenshot)</label>
                        <input id="mainDeviceField" type="text" value="{{ $archivedApplication->main_device ?? 'Not provided' }}"
                            class="p-2 border rounded-md w-full" readonly>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-normal text-gray-500">Backup Device Specs (dxdiag
                            Screenshot)</label>
                        <input id="backupDeviceField" type="text"
                            value="{{ $archivedApplication->backup_device ?? 'Not provided' }}" class="p-2 border rounded-md w-full" readonly>
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
                        <input type="radio" {{ $archivedApplication->source === 'fb_boosting' ? 'checked' : '' }} disabled>
                        <span class="ml-2">FB Boosting</span>
                    </label> 
                    <label class="inline-flex items-center">
                        <input type="radio" {{ $archivedApplication->source === 'referral' ? 'checked' : '' }} disabled>
                        <span class="ml-2">Referral</span>
                    </label>
                </div>
                @if($archivedApplication->source === 'referral')
                <div class="flex flex-col">
                    <label class="text-sm font-normal text-gray-500">Referrer Name <span
                            class="text-red-600">*</span></label>
                    <input type="text" value="{{ $archivedApplication->referrer_name }}" class="p-2 border rounded-md w-64" readonly>
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
                                <option selected>{{ $archivedApplication->start_time }}</option>
                            </select>
                            <select class="p-2 border rounded-md w-full" disabled>
                                <option selected>{{ $archivedApplication->end_time }}</option>
                            </select>
                        </div>
                        <div class="text-sm font-medium text-gray-700 mb-2">Days Available:</div>
                        <div class="grid grid-cols-4 gap-2 mb-2">
                            <label><input type="checkbox" {{ in_array('monday', $archivedApplication->days) ? 'checked' : '' }} disabled> Mon</label>
                            <label><input type="checkbox" {{ in_array('tuesday', $archivedApplication->days) ? 'checked' : '' }} disabled> Tue</label>
                            <label><input type="checkbox" {{ in_array('wednesday', $archivedApplication->days) ? 'checked' : '' }} disabled> Wed</label>
                            <label><input type="checkbox" {{ in_array('thursday', $archivedApplication->days) ? 'checked' : '' }} disabled> Thu</label>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <label><input type="checkbox" {{ in_array('friday', $archivedApplication->days) ? 'checked' : '' }} disabled> Fri</label>
                            <label><input type="checkbox" {{ in_array('saturday', $archivedApplication->days) ? 'checked' : '' }} disabled> Sat</label>
                            <label><input type="checkbox" {{ in_array('sunday', $archivedApplication->days) ? 'checked' : '' }} disabled> Sun</label>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Platform Familiarity + Preferred Time -->
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <label class="text-sm font-normal text-gray-500 mb-2">Platform Familiarity</label>
                        <div class="p-4 border rounded-lg shadow-lg mb-4">
                            <div class="grid grid-cols-3 gap-2 mb-2">
                                <label><input type="checkbox" {{ in_array('classin', $archivedApplication->platforms) ? 'checked' : '' }} disabled> ClassIn</label>
                                <label><input type="checkbox" {{ in_array('zoom', $archivedApplication->platforms) ? 'checked' : '' }} disabled> Zoom</label>
                                <label><input type="checkbox" {{ in_array('voov', $archivedApplication->platforms) ? 'checked' : '' }} disabled> Voov</label>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <label><input type="checkbox" {{ in_array('ms_teams', $archivedApplication->platforms) ? 'checked' : '' }} disabled> MS Teams</label>
                                <label><input type="checkbox" {{ in_array('others', $archivedApplication->platforms) ? 'checked' : '' }} disabled> Others</label>
                            </div>
                        </div>

                        <label class="text-sm font-normal text-gray-500 mb-2">Preferred Schedule (Date & Time)</label>
                        <div class="p-4 border rounded-lg shadow-lg">
                            <input type="datetime-local" value="{{ $archivedApplication->interview_time ? $archivedApplication->interview_time->format('Y-m-d\TH:i') : '' }}"
                                class="p-2 border rounded-md w-full" readonly>
                        </div>
                    </div>
                </div>

                <!-- Column 3: Can Teach + Archive Information -->
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <label class="text-sm font-normal text-gray-500 mb-2">Can Teach</label>
                        <div class="p-4 border rounded-lg shadow-lg space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <label><input type="checkbox" {{ in_array('kids', $archivedApplication->can_teach) ? 'checked' : '' }} disabled> Kids</label>
                                <label><input type="checkbox" {{ in_array('teenager', $archivedApplication->can_teach) ? 'checked' : '' }} disabled> Teenager</label>
                            </div>
                            <div>
                                <label><input type="checkbox" {{ in_array('adults', $archivedApplication->can_teach) ? 'checked' : '' }} disabled> Adults</label>
                            </div>
                        </div>
                    </div>

                    <!-- Archive Information -->
                    <div class="mt-6 mb-4 p-4 bg-red-50 rounded-lg border border-red-200">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Final Status:</span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold 
                                @php
                                    $statusColors = [
                                        'declined' => 'bg-red-500 text-white',
                                        'not_recommended' => 'bg-orange-500 text-white',
                                        'no_answer_3_attempts' => 'bg-gray-500 text-white',
                                        're_schedule' => 'bg-blue-500 text-white'
                                    ];
                                    $statusColor = $statusColors[$archivedApplication->final_status] ?? 'bg-gray-500 text-white';
                                @endphp
                                {{ $statusColor }}">
                                {{ ucwords(str_replace('_', ' ', $archivedApplication->final_status)) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Archived At:</span>
                            <span class="text-sm text-gray-900">{{ $archivedApplication->archived_at->format('Y-m-d H:i') }}</span>
                        </div>
                        @if($archivedApplication->attempt_count > 0)
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Attempt Count:</span>
                            <span class="text-sm font-bold text-gray-900">{{ $archivedApplication->attempt_count }}/3</span>
                        </div>
                        @endif
                        @if($archivedApplication->interviewer)
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Last Interviewer:</span>
                            <span class="text-sm text-gray-900">{{ $archivedApplication->interviewer }}</span>
                        </div>
                        @endif
                        @if($archivedApplication->notes)
                        <div class="mt-2">
                            <span class="text-sm font-medium text-gray-700">Notes:</span>
                            <p class="text-sm text-gray-900 mt-1 p-2 bg-gray-100 rounded">{{ $archivedApplication->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
