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

<!-- Header with Back Button -->
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-gray-800 ">Applicant Details</h1>
    <a href="{{ route('hiring_onboarding.index') }}"
       class="flex items-center space-x-2 px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-medium 
              hover:bg-gray-700 transition duration-200">
        <i class="fas fa-arrow-left"></i>
        <span>Back to List</span>
    </a>
</div>

<!-- Progress Bar -->
<div x-show="showProgress" x-cloak class="fixed top-0 left-0 w-full h-1 bg-gray-200 z-50">
    <div x-ref="progressBar" class="h-full bg-blue-600 transition-all duration-2000 ease-out" style="width: 0%;"></div>
</div>

<!-- Form Container -->
<div class="bg-white rounded-lg shadow-sm p-4 sm:p-6" x-data="{ 
    currentPage: 1,
    totalPages: 4,
    showModal: false, 
    showProgress: false,
    showFailModal: false,
    showPassModal: false,
    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    },
    prevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    },
    goToPage(page) {
        this.currentPage = page;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    closeFailModal() {
        this.showProgress = true;
        this.showFailModal = false;
        this.$nextTick(() => {
            this.$refs.progressBar.style.width = '0%';
            setTimeout(() => { this.$refs.progressBar.style.width = '100%'; }, 10);
            setTimeout(() => {
                window.location.href = '{{ route('hiring_onboarding.applicant.show', $application->application_id) }}';
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
                window.location.href = '{{ route('hiring_onboarding.applicant.show', $application->application_id) }}';
            }, 2000);
        });
    }
}">

    <!-- Page Indicator -->
    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200">
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600">Page</span>
            <span class="text-base font-semibold text-gray-900" x-text="currentPage"></span>
            <span class="text-sm text-gray-600">of</span>
            <span class="text-base font-semibold text-gray-900" x-text="totalPages"></span>
        </div>
        
        <!-- Page Navigation Dots -->
        <div class="flex gap-2">
            <template x-for="page in totalPages" :key="page">
                <button @click="goToPage(page)" 
                    class="w-2.5 h-2.5 rounded-full transition-all"
                    :class="currentPage === page ? 'bg-green-500 w-6' : 'bg-gray-300 hover:bg-gray-400'">
                </button>
            </template>
        </div>
    </div>

    <!-- Page 1: Personal Information -->
    <div x-show="currentPage === 1" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-4"
         x-transition:enter-end="opacity-100 transform translate-x-0">
        <h3 class="font-semibold text-gray-900 text-lg mb-4 flex items-center border-b border-gray-200 pb-2">
            <i class="fas fa-user mr-2 text-green-500"></i>
            Personal Information
        </h3>

        <div class="grid md:grid-cols-3 gap-4">
                        <!-- Row 1 -->
                        <div class="flex flex-col space-y-1">
                            <label class="text-xs font-medium text-gray-600 uppercase">First Name</label>
                            <div class="text-sm text-gray-900 px-3 py-2 bg-gray-50 rounded-md">
                                {{ $application->first_name }}
                            </div>
                        </div>
                        <div class="flex flex-col space-y-1">
                            <label class="text-xs font-medium text-gray-600  uppercase">Middle Name</label>
                            <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50  rounded-md">
                                {{ $application->middle_name ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="flex flex-col space-y-1">
                            <label class="text-xs font-medium text-gray-600  uppercase">Last Name</label>
                            <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50  rounded-md">
                                {{ $application->last_name }}
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div class="flex flex-col space-y-1">
                            <label class="text-xs font-medium text-gray-600  uppercase">Birth Date</label>
                            <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50  rounded-md">
                                {{ $application->birth_date ? $application->birth_date->format('F d, Y') : 'N/A' }}
                            </div>
                        </div>
                        <div class="flex flex-col space-y-1">
                            <label class="text-xs font-medium text-gray-600  uppercase">Contact Number</label>
                            <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50  rounded-md">
                                {{ $application->contact_number }}
                            </div>
                        </div>
                        <div class="flex flex-col space-y-1">
                            <label class="text-xs font-medium text-gray-600  uppercase">Email</label>
                            <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50  rounded-md break-all">
                                {{ $application->email }}
                            </div>
                        </div>

                        <!-- Row 3 -->
                        <div class="flex flex-col space-y-1 md:col-span-3">
                            <label class="text-xs font-medium text-gray-600  uppercase">Address</label>
                            <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50  rounded-md">
                                {{ $application->address }}
                            </div>
                        </div>
                        
                        <div class="flex flex-col space-y-1 md:col-span-3">
                            <label class="text-xs font-medium text-gray-600  uppercase">MS Teams</label>
                            <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50  rounded-md">
                                {{ $application->ms_teams ?? 'Not provided' }}
                            </div>
                        </div>
                    </div>
    </div>

    <!-- Page 2: Education & Work Background -->
    <div x-show="currentPage === 2" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-4"
         x-transition:enter-end="opacity-100 transform translate-x-0">
        <h3 class="font-semibold text-gray-900  text-lg mb-4 flex items-center border-b border-gray-200  pb-2">
            <i class="fas fa-graduation-cap mr-2 text-green-500"></i>
            Education & Work Background
        </h3>
        
        <div class="grid md:grid-cols-2 gap-4">
            <!-- Highest Educational Attainment -->
            <div class="flex flex-col space-y-1">
                <label class="text-xs font-medium text-gray-600  uppercase">
                    Highest Educational Attainment <span class="text-red-500">*</span>
                </label>
                <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50  rounded-md">
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
                </div>
            </div>

            <!-- ESL Teaching Experience -->
            <div class="flex flex-col space-y-1">
                <label class="text-xs font-medium text-gray-600  uppercase">
                    ESL Teaching Experience <span class="text-red-500">*</span>
                </label>
                <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50  rounded-md">
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
                </div>
            </div>
        </div>
    </div>

    <!-- Page 3: Requirements & Referral -->
    <div x-show="currentPage === 3" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-4"
         x-transition:enter-end="opacity-100 transform translate-x-0">
        <h3 class="font-semibold text-gray-900  text-lg mb-4 flex items-center border-b border-gray-200  pb-2">
            <i class="fas fa-file-alt mr-2 text-green-500"></i>
            Requirements & Setup
        </h3>
        
        <div class="space-y-4">
            <!-- Document Links -->
            <div class="grid md:grid-cols-2 gap-4">
                <div class="flex flex-col space-y-1">
                    <label class="text-xs font-medium text-gray-600  uppercase">
                        Resume Link (GDrive / GDocs) <span class="text-red-500">*</span>
                    </label>
                    <a href="{{ $application->resume_link }}" target="_blank" 
                       class="text-sm text-blue-600 hover:text-blue-700 px-3 py-2 bg-gray-50  rounded-md break-all">
                        {{ $application->resume_link }}
                    </a>
                </div>
                <div class="flex flex-col space-y-1">
                    <label class="text-xs font-medium text-gray-600  uppercase">
                        Intro Video (GDrive Link) <span class="text-red-500">*</span>
                    </label>
                    <a href="{{ $application->intro_video }}" target="_blank" 
                       class="text-sm text-blue-600 hover:text-blue-700 px-3 py-2 bg-gray-50  rounded-md break-all">
                        {{ $application->intro_video }}
                    </a>
                </div>
            </div>

            <!-- Work Setup -->
            <div class="flex flex-col space-y-1">
                <label class="text-xs font-medium text-gray-600  uppercase">Work Setup <span class="text-red-500">*</span></label>
                <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50  rounded-md">
                    {{ $application->work_type === 'work_from_home' ? 'Work from Home' : 'Work at Site' }}
                </div>
            </div>

            <!-- Device & Network Requirements -->
            @if($application->work_type === 'work_from_home')
            <div class="grid md:grid-cols-3 gap-4">
                <div class="flex flex-col space-y-1">
                    <label class="text-xs font-medium text-gray-600  uppercase">Ookla Speedtest (GDrive Link)</label>
                    @if($application->speedtest)
                        <a href="{{ $application->speedtest }}" target="_blank" 
                           class="text-sm text-blue-600 hover:text-blue-700 px-3 py-2 bg-gray-50  rounded-md break-all">
                            {{ $application->speedtest }}
                        </a>
                    @else
                        <div class="text-sm text-gray-500  px-3 py-2 bg-gray-50  rounded-md">Not provided</div>
                    @endif
                </div>
                <div class="flex flex-col space-y-1">
                    <label class="text-xs font-medium text-gray-600  uppercase">Main Device Specs (dxdiag)</label>
                    @if($application->main_device)
                        <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50  rounded-md break-all">
                            {{ $application->main_device }}
                        </div>
                    @else
                        <div class="text-sm text-gray-500  px-3 py-2 bg-gray-50  rounded-md">Not provided</div>
                    @endif
                </div>
                <div class="flex flex-col space-y-1">
                    <label class="text-xs font-medium text-gray-600  uppercase">Backup Device Specs (dxdiag)</label>
                    @if($application->backup_device)
                        <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50  rounded-md break-all">
                            {{ $application->backup_device }}
                        </div>
                    @else
                        <div class="text-sm text-gray-500  px-3 py-2 bg-gray-50  rounded-md">Not provided</div>
                    @endif
                </div>
            </div>
            @endif

            <!-- How Did You Hear About Us -->
            <div class="flex flex-col space-y-1">
                <label class="text-xs font-medium text-gray-600  uppercase">How Did You Hear About Us? <span class="text-red-500">*</span></label>
                <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50  rounded-md">
                    {{ $application->source === 'fb_boosting' ? 'FB Boosting' : 'Referral' }}
                </div>
            </div>

            @if($application->source === 'referral')
            <div class="flex flex-col space-y-1">
                <label class="text-xs font-medium text-gray-600  uppercase">Referrer Name <span class="text-red-500">*</span></label>
                <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50  rounded-md">
                    {{ $application->referrer_name }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Page 4: Work Preferences -->
    <div x-show="currentPage === 4" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-4"
         x-transition:enter-end="opacity-100 transform translate-x-0">
        <h3 class="font-semibold text-gray-900  text-lg mb-4 flex items-center border-b border-gray-200  pb-2">
            <i class="fas fa-clock mr-2 text-green-500"></i>
            Work Preferences
        </h3>
        
        <div class="space-y-4">
            <!-- Working Availability -->
            <div class="bg-gray-50  p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700  mb-3 uppercase">Working Availability</h4>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div class="flex flex-col space-y-1">
                        <label class="text-xs font-medium text-gray-600  uppercase">Start Time</label>
                        <div class="text-sm text-gray-900  px-3 py-2 bg-white  rounded-md">
                            {{ $application->start_time ? \Carbon\Carbon::parse($application->start_time)->format('h:i A') : 'N/A' }}
                        </div>
                    </div>
                    <div class="flex flex-col space-y-1">
                        <label class="text-xs font-medium text-gray-600  uppercase">End Time</label>
                        <div class="text-sm text-gray-900  px-3 py-2 bg-white  rounded-md">
                            {{ $application->end_time ? \Carbon\Carbon::parse($application->end_time)->format('h:i A') : 'N/A' }}
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col space-y-1">
                    <label class="text-xs font-medium text-gray-600  uppercase mb-2">Days Available</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <label class="flex items-center gap-2 bg-white  px-3 py-1.5 rounded-md">
                            <input type="checkbox" {{ (is_array($application->days) && in_array('monday', $application->days)) ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm text-gray-700 ">Monday</span>
                        </label>
                        <label class="flex items-center gap-2 bg-white  px-3 py-1.5 rounded-md">
                            <input type="checkbox" {{ (is_array($application->days) && in_array('tuesday', $application->days)) ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm text-gray-700 ">Tuesday</span>
                        </label>
                        <label class="flex items-center gap-2 bg-white  px-3 py-1.5 rounded-md">
                            <input type="checkbox" {{ (is_array($application->days) && in_array('wednesday', $application->days)) ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm text-gray-700 ">Wednesday</span>
                        </label>
                        <label class="flex items-center gap-2 bg-white  px-3 py-1.5 rounded-md">
                            <input type="checkbox" {{ (is_array($application->days) && in_array('thursday', $application->days)) ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm text-gray-700 ">Thursday</span>
                        </label>
                        <label class="flex items-center gap-2 bg-white  px-3 py-1.5 rounded-md">
                            <input type="checkbox" {{ (is_array($application->days) && in_array('friday', $application->days)) ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm text-gray-700 ">Friday</span>
                        </label>
                        <label class="flex items-center gap-2 bg-white  px-3 py-1.5 rounded-md">
                            <input type="checkbox" {{ (is_array($application->days) && in_array('saturday', $application->days)) ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm text-gray-700 ">Saturday</span>
                        </label>
                        <label class="flex items-center gap-2 bg-white  px-3 py-1.5 rounded-md">
                            <input type="checkbox" {{ (is_array($application->days) && in_array('sunday', $application->days)) ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm text-gray-700 ">Sunday</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Platform Familiarity -->
            <div class="bg-gray-50  p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700  mb-3 uppercase">Platform Familiarity</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    <label class="flex items-center gap-2 bg-white  px-3 py-1.5 rounded-md">
                        <input type="checkbox" {{ (is_array($application->platforms) && in_array('classin', $application->platforms)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm text-gray-700 ">ClassIn</span>
                    </label>
                    <label class="flex items-center gap-2 bg-white  px-3 py-1.5 rounded-md">
                        <input type="checkbox" {{ (is_array($application->platforms) && in_array('zoom', $application->platforms)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm text-gray-700 ">Zoom</span>
                    </label>
                    <label class="flex items-center gap-2 bg-white  px-3 py-1.5 rounded-md">
                        <input type="checkbox" {{ (is_array($application->platforms) && in_array('voov', $application->platforms)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm text-gray-700 ">Voov</span>
                    </label>
                    <label class="flex items-center gap-2 bg-white  px-3 py-1.5 rounded-md">
                        <input type="checkbox" {{ (is_array($application->platforms) && in_array('ms_teams', $application->platforms)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm text-gray-700 ">MS Teams</span>
                    </label>
                    <label class="flex items-center gap-2 bg-white  px-3 py-1.5 rounded-md">
                        <input type="checkbox" {{ (is_array($application->platforms) && in_array('others', $application->platforms)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm text-gray-700 ">Others</span>
                    </label>
                </div>
            </div>

            <!-- Can Teach -->
            <div class="bg-gray-50  p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700  mb-3 uppercase">Can Teach</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    <label class="flex items-center gap-2 bg-white  px-3 py-1.5 rounded-md">
                        <input type="checkbox" {{ (is_array($application->can_teach) && in_array('kids', $application->can_teach)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm text-gray-700 ">Kids</span>
                    </label>
                    <label class="flex items-center gap-2 bg-white  px-3 py-1.5 rounded-md">
                        <input type="checkbox" {{ (is_array($application->can_teach) && in_array('teenager', $application->can_teach)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm text-gray-700 ">Teenager</span>
                    </label>
                    <label class="flex items-center gap-2 bg-white  px-3 py-1.5 rounded-md">
                        <input type="checkbox" {{ (is_array($application->can_teach) && in_array('adults', $application->can_teach)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm text-gray-700 ">Adults</span>
                    </label>
                </div>
            </div>

            <!-- Preferred Interview Time -->
            <div class="flex flex-col space-y-1">
                <label class="text-xs font-medium text-gray-600  uppercase">Preferred Time for Interview Call</label>
                <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50  rounded-md">
                    {{ \Carbon\Carbon::parse($application->interview_time)->format('F d, Y - h:i A') }}
                </div>
            </div>

            <!-- Application Notes -->
            <div class="bg-gray-50  p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700  mb-3 uppercase flex items-center">
                    <i class="fas fa-sticky-note mr-2 text-yellow-500"></i>
                    Application Notes
                </h4>
                <div class="text-sm text-gray-900  px-3 py-2 bg-white  rounded-md min-h-[60px]">
                    @if(!empty($application->notes))
                        {{ $application->notes }}
                    @else
                        <span class="text-gray-500  italic">No notes available</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200 ">
        <button @click="prevPage()" :disabled="currentPage === 1" 
            class="px-5 py-2 rounded-md text-sm font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            :class="currentPage === 1 ? 'bg-gray-300  text-gray-500 ' : 'bg-gray-700 text-white hover:bg-gray-800'">
            <i class="fas fa-chevron-left mr-2"></i>
            Previous
        </button>
        
        <div class="text-sm text-gray-600 ">
            Page <span class="text-gray-900  font-semibold" x-text="currentPage"></span> of <span class="text-gray-900  font-semibold" x-text="totalPages"></span>
        </div>
        
        <button @click="nextPage()" :disabled="currentPage === totalPages" 
            class="px-5 py-2 rounded-md text-sm font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            :class="currentPage === totalPages ? 'bg-gray-300  text-gray-500 ' : 'bg-green-500 text-white hover:bg-green-600'">
            Next
            <i class="fas fa-chevron-right ml-2"></i>
        </button>
    </div>

    <!-- Application Status & Call Section -->
    <div class="bg-white  rounded-lg shadow-sm mt-4 overflow-hidden border-t border-gray-200 ">
        <div class="p-4">
            <div class="flex items-center justify-between">
                <!-- Status Information (Left Side) -->
                <div class="flex items-center gap-6">
                    <div>
                        <p class="text-xs text-gray-500  mb-2">Current Status</p>
                        <div class="flex items-center gap-2">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-500',
                                    'rejected' => 'bg-red-500',
                                    'no_answer' => 'bg-orange-500',
                                    're_schedule' => 'bg-purple-500',
                                    'declined' => 'bg-red-500',
                                    'not_recommended' => 'bg-red-500',
                                    'passed' => 'bg-green-500',
                                    'completed' => 'bg-blue-500',
                                ];
                                $statusColor = $statusColors[$application->status] ?? 'bg-gray-500';
                            @endphp
                            <span class="w-2.5 h-2.5 rounded-full {{ $statusColor }}"></span>
                            <span class="text-sm font-medium text-gray-700 ">
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
                    </div>

                    @if($application->attempt_count > 0)
                    <div class="border-l pl-6">
                        <p class="text-xs text-gray-500 mb-1">Attempts</p>
                        @php($remaining = max(0, 3 - $application->attempt_count))
                        <p class="text-sm font-medium text-gray-900">{{ $application->attempt_count }} of 3 ({{ $remaining }} left)</p>
                    </div>
                    @endif

                    @if($application->interviewer)
                    <div class="border-l pl-6">
                        <p class="text-xs text-gray-500 mb-1">Last Interviewer</p>
                        <p class="text-sm font-medium text-gray-900">{{ $application->interviewer }}</p>
                    </div>
                    @endif
                </div>

                <!-- Call Button (Right Side) -->
                <button type="button" @click="showModal = true"
                    class="px-6 py-2 rounded-md bg-blue-800 text-white text-sm font-medium hover:bg-blue-900 transition-all">
                    <i class="fas fa-phone-alt mr-2"></i>
                    Call Applicant
                </button>
            </div>
        </div>
    </div>

    <!-- Include Call Modal -->
    @include('hiring_onboarding.tabs.partials.modals.call_mdl')
</div>

