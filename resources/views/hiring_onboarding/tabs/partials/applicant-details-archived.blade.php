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
    <h1 class="text-xl font-bold text-gray-800 ">Archived Applicant Details</h1>
    <a href="{{ route('hiring_onboarding.index', ['tab' => 'archive']) }}"
       class="flex items-center space-x-2 px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-medium 
              hover:bg-gray-700 transition duration-200">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Archive</span>
    </a>
</div>

<!-- Progress Bar -->
<div x-show="showProgress" x-cloak class="fixed top-0 left-0 w-full h-1 bg-gray-200 z-50">
    <div x-ref="progressBar" class="h-full bg-blue-600 transition-all duration-2000 ease-out" style="width: 0%;"></div>
</div>

<!-- Form Container -->
<div class="bg-white  rounded-lg shadow-sm p-4 sm:p-6" x-data="{ 
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
                window.location.href = '{{ route('hiring_onboarding.index', ['tab' => 'archive']) }}';
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
                window.location.href = '{{ route('hiring_onboarding.index', ['tab' => 'archive']) }}';
            }, 2000);
        });
    }
}">

    <!-- Page Indicator -->
    <div class="flex items-center justify-between mb-6 pb-4 border-b">
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-600">Page</span>
            <span class="text-lg font-bold text-[#0E335D]" x-text="currentPage"></span>
            <span class="text-sm font-medium text-gray-600">of</span>
            <span class="text-lg font-bold text-[#0E335D]" x-text="totalPages"></span>
        </div>
        
        <!-- Page Navigation Dots -->
        <div class="flex gap-2">
            <template x-for="page in totalPages" :key="page">
                <button @click="goToPage(page)" 
                    class="w-3 h-3 rounded-full transition-all"
                    :class="currentPage === page ? 'bg-[#65DB7F] w-8' : 'bg-gray-300 hover:bg-gray-400'">
                </button>
            </template>
        </div>
    </div>

    <!-- Page 1: Personal Information -->
    <div x-show="currentPage === 1" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-4"
         x-transition:enter-end="opacity-100 transform translate-x-0">
        <h3 class="font-semibold text-[#0E335D] text-xl mb-6 flex items-center">
            <i class="fas fa-user mr-2 text-[#65DB7F]"></i>
            Personal Information
        </h3>

        <div class="grid md:grid-cols-3 gap-6">
                        <!-- Row 1 -->
                        <div class="flex flex-col space-y-1">
                            <label class="text-xs font-medium text-gray-600  uppercase">First Name</label>
                            <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md">
                                {{ $archivedApplication->first_name }}
                            </div>
                        </div>
                        <div class="flex flex-col space-y-1">
                            <label class="text-xs font-medium text-gray-600  uppercase">Middle Name</label>
                            <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md">
                                {{ $archivedApplication->middle_name ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="flex flex-col space-y-1">
                            <label class="text-xs font-medium text-gray-600  uppercase">Last Name</label>
                            <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md">
                                {{ $archivedApplication->last_name }}
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div class="flex flex-col space-y-1">
                            <label class="text-xs font-medium text-gray-600  uppercase">Birth Date</label>
                            <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md">
                                {{ $archivedApplication->birth_date ? $archivedApplication->birth_date->format('F d, Y') : 'N/A' }}
                            </div>
                        </div>
                        <div class="flex flex-col space-y-1">
                            <label class="text-xs font-medium text-gray-600  uppercase">Contact Number</label>
                            <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md">
                                {{ $archivedApplication->contact_number }}
                            </div>
                        </div>
                        <div class="flex flex-col space-y-1">
                            <label class="text-xs font-medium text-gray-600  uppercase">Email</label>
                            <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md break-all">
                                {{ $archivedApplication->email }}
                            </div>
                        </div>

                        <!-- Row 3 -->
                        <div class="flex flex-col space-y-1 md:col-span-3">
                            <label class="text-xs font-medium text-gray-600  uppercase">Address</label>
                            <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md">
                                {{ $archivedApplication->address }}
                            </div>
                        </div>
                        
                        <div class="flex flex-col space-y-1 md:col-span-3">
                            <label class="text-xs font-medium text-gray-600  uppercase">MS Teams</label>
                            <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md">
                                {{ $archivedApplication->ms_teams ?? 'Not provided' }}
                            </div>
                        </div>
                    </div>
    </div>

    <!-- Page 2: Education & Requirements -->
    <div x-show="currentPage === 2" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-4"
         x-transition:enter-end="opacity-100 transform translate-x-0">
        <h3 class="font-semibold text-[#0E335D] text-xl mb-6 flex items-center">
            <i class="fas fa-graduation-cap mr-2 text-[#65DB7F]"></i>
            Education & Work Background
        </h3>
        
        <div class="grid md:grid-cols-2 gap-6">
            <!-- Highest Educational Attainment -->
            <div class="flex flex-col space-y-1">
                <label class="text-xs font-medium text-gray-600  uppercase">
                    Highest Educational Attainment <span class="text-red-500">*</span>
                </label>
                <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md">
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
                </div>
            </div>

            <!-- ESL Teaching Experience -->
            <div class="flex flex-col space-y-1">
                <label class="text-xs font-medium text-gray-600  uppercase">
                    ESL Teaching Experience <span class="text-red-500">*</span>
                </label>
                <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md">
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
                </div>
            </div>
        </div>
    </div>

    <!-- Page 3: Requirements & Referral -->
    <div x-show="currentPage === 3" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-4"
         x-transition:enter-end="opacity-100 transform translate-x-0">
        <h3 class="font-semibold text-[#0E335D] text-xl mb-6 flex items-center">
            <i class="fas fa-file-alt mr-2 text-[#65DB7F]"></i>
            Requirements & Setup
        </h3>
        
        <div class="space-y-6">
            <!-- Document Links -->
            <div class="grid md:grid-cols-2 gap-6">
                <div class="flex flex-col space-y-1">
                    <label class="text-xs font-medium text-gray-600  uppercase">
                        Resume Link (GDrive / GDocs) <span class="text-red-500">*</span>
                    </label>
                    <a href="{{ $archivedApplication->resume_link }}" target="_blank" 
                       class="text-base font-medium text-blue-600 hover:text-blue-800 px-3 py-2 bg-gray-50 rounded-md break-all">
                        {{ $archivedApplication->resume_link }}
                    </a>
                </div>
                <div class="flex flex-col space-y-1">
                    <label class="text-xs font-medium text-gray-600  uppercase">
                        Intro Video (GDrive Link) <span class="text-red-500">*</span>
                    </label>
                    <a href="{{ $archivedApplication->intro_video }}" target="_blank" 
                       class="text-base font-medium text-blue-600 hover:text-blue-800 px-3 py-2 bg-gray-50 rounded-md break-all">
                        {{ $archivedApplication->intro_video }}
                    </a>
                </div>
            </div>

            <!-- Work Setup -->
            <div class="flex flex-col space-y-1">
                <label class="text-xs font-medium text-gray-600  uppercase">Work Setup <span class="text-red-500">*</span></label>
                <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md">
                    {{ $archivedApplication->work_type === 'work_from_home' ? 'Work from Home' : 'Work at Site' }}
                </div>
            </div>

            <!-- Device & Network Requirements -->
            @if($archivedApplication->work_type === 'work_from_home')
            <div class="grid md:grid-cols-3 gap-6">
                <div class="flex flex-col space-y-1">
                    <label class="text-xs font-medium text-gray-600  uppercase">Ookla Speedtest (GDrive Link)</label>
                    @if($archivedApplication->speedtest)
                        <a href="{{ $archivedApplication->speedtest }}" target="_blank" 
                           class="text-base font-medium text-blue-600 hover:text-blue-800 px-3 py-2 bg-gray-50 rounded-md break-all">
                            {{ $archivedApplication->speedtest }}
                        </a>
                    @else
                        <div class="text-base font-medium text-gray-500 px-3 py-2 bg-gray-50 rounded-md">Not provided</div>
                    @endif
                </div>
                <div class="flex flex-col space-y-1">
                    <label class="text-xs font-medium text-gray-600  uppercase">Main Device Specs (dxdiag)</label>
                    @if($archivedApplication->main_device)
                        <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md break-all border border-gray-200">
                            {{ $archivedApplication->main_device }}
                        </div>
                    @else
                        <div class="text-base font-medium text-gray-500 px-3 py-2 bg-gray-50 rounded-md">Not provided</div>
                    @endif
                </div>
                <div class="flex flex-col space-y-1">
                    <label class="text-xs font-medium text-gray-600  uppercase">Backup Device Specs (dxdiag)</label>
                    @if($archivedApplication->backup_device)
                        <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md break-all border border-gray-200">
                            {{ $archivedApplication->backup_device }}
                        </div>
                    @else
                        <div class="text-base font-medium text-gray-500 px-3 py-2 bg-gray-50 rounded-md">Not provided</div>
                    @endif
                </div>
            </div>
            @endif

            <!-- How Did You Hear About Us -->
            <div class="flex flex-col space-y-1">
                <label class="text-xs font-medium text-gray-600  uppercase">How Did You Hear About Us? <span class="text-red-500">*</span></label>
                <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md">
                    {{ $archivedApplication->source === 'fb_boosting' ? 'FB Boosting' : 'Referral' }}
                </div>
            </div>

            @if($archivedApplication->source === 'referral')
            <div class="flex flex-col space-y-1">
                <label class="text-xs font-medium text-gray-600  uppercase">Referrer Name <span class="text-red-500">*</span></label>
                <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md">
                    {{ $archivedApplication->referrer_name }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Page 4: Work Preferences -->
    <div x-show="currentPage === 4" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-4"
         x-transition:enter-end="opacity-100 transform translate-x-0">
        <h3 class="font-semibold text-[#0E335D] text-xl mb-6 flex items-center">
            <i class="fas fa-clock mr-2 text-[#65DB7F]"></i>
            Work Preferences
        </h3>
        
        <div class="space-y-6">
            <!-- Working Availability -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Working Availability</h4>
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div class="flex flex-col space-y-1">
                        <label class="text-xs font-medium text-gray-600  uppercase">Start Time</label>
                        <div class="text-sm text-gray-900  px-3 py-2 bg-white rounded-md">
                            {{ $archivedApplication->start_time }}
                        </div>
                    </div>
                    <div class="flex flex-col space-y-1">
                        <label class="text-xs font-medium text-gray-600  uppercase">End Time</label>
                        <div class="text-sm text-gray-900  px-3 py-2 bg-white rounded-md">
                            {{ $archivedApplication->end_time }}
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col space-y-1">
                    <label class="text-xs font-medium text-gray-600  uppercase mb-2">Days Available</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-md">
                            <input type="checkbox" {{ (is_array($archivedApplication->days) && in_array('monday', $archivedApplication->days)) ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm font-medium">Monday</span>
                        </label>
                        <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-md">
                            <input type="checkbox" {{ (is_array($archivedApplication->days) && in_array('tuesday', $archivedApplication->days)) ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm font-medium">Tuesday</span>
                        </label>
                        <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-md">
                            <input type="checkbox" {{ (is_array($archivedApplication->days) && in_array('wednesday', $archivedApplication->days)) ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm font-medium">Wednesday</span>
                        </label>
                        <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-md">
                            <input type="checkbox" {{ (is_array($archivedApplication->days) && in_array('thursday', $archivedApplication->days)) ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm font-medium">Thursday</span>
                        </label>
                        <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-md">
                            <input type="checkbox" {{ (is_array($archivedApplication->days) && in_array('friday', $archivedApplication->days)) ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm font-medium">Friday</span>
                        </label>
                        <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-md">
                            <input type="checkbox" {{ (is_array($archivedApplication->days) && in_array('saturday', $archivedApplication->days)) ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm font-medium">Saturday</span>
                        </label>
                        <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-md">
                            <input type="checkbox" {{ (is_array($archivedApplication->days) && in_array('sunday', $archivedApplication->days)) ? 'checked' : '' }} disabled class="rounded">
                            <span class="text-sm font-medium">Sunday</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Platform Familiarity -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Platform Familiarity</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-md">
                        <input type="checkbox" {{ (is_array($archivedApplication->platforms) && in_array('classin', $archivedApplication->platforms)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm font-medium">ClassIn</span>
                    </label>
                    <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-md">
                        <input type="checkbox" {{ (is_array($archivedApplication->platforms) && in_array('zoom', $archivedApplication->platforms)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm font-medium">Zoom</span>
                    </label>
                    <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-md">
                        <input type="checkbox" {{ (is_array($archivedApplication->platforms) && in_array('voov', $archivedApplication->platforms)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm font-medium">Voov</span>
                    </label>
                    <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-md">
                        <input type="checkbox" {{ (is_array($archivedApplication->platforms) && in_array('ms_teams', $archivedApplication->platforms)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm font-medium">MS Teams</span>
                    </label>
                    <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-md">
                        <input type="checkbox" {{ (is_array($archivedApplication->platforms) && in_array('others', $archivedApplication->platforms)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm font-medium">Others</span>
                    </label>
                </div>
            </div>

            <!-- Can Teach -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Can Teach</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-md">
                        <input type="checkbox" {{ (is_array($archivedApplication->can_teach) && in_array('kids', $archivedApplication->can_teach)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm font-medium">Kids</span>
                    </label>
                    <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-md">
                        <input type="checkbox" {{ (is_array($archivedApplication->can_teach) && in_array('teenager', $archivedApplication->can_teach)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm font-medium">Teenager</span>
                    </label>
                    <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-md">
                        <input type="checkbox" {{ (is_array($archivedApplication->can_teach) && in_array('adults', $archivedApplication->can_teach)) ? 'checked' : '' }} disabled class="rounded">
                        <span class="text-sm font-medium">Adults</span>
                    </label>
                </div>
            </div>

            <!-- Preferred Interview Time -->
            <div class="flex flex-col space-y-1">
                <label class="text-xs font-medium text-gray-600  uppercase">Preferred Time for Interview Call</label>
                <div class="text-sm text-gray-900  px-3 py-2 bg-gray-50 rounded-md">
                    {{ \Carbon\Carbon::parse($archivedApplication->interview_time)->format('F d, Y - h:i A') }}
                </div>
            </div>

            <!-- Application Notes -->
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h4 class="text-sm font-semibold text-gray-700 mb-3 uppercase flex items-center">
                    <i class="fas fa-sticky-note mr-2 text-yellow-500"></i>
                    Application Notes
                </h4>
                <div class="text-sm text-gray-900 px-3 py-2 bg-white rounded-md min-h-[60px]">
                    @if(!empty($archivedApplication->notes))
                        {{ $archivedApplication->notes }}
                    @else
                        <span class="text-gray-500 italic">No notes available</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="flex justify-between items-center mt-8 pt-6 border-t">
        <button @click="prevPage()" :disabled="currentPage === 1" 
            class="px-6 py-3 rounded-lg font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            :class="currentPage === 1 ? 'bg-gray-300 text-gray-500' : 'bg-[#0E335D] text-white hover:opacity-90'">
            <i class="fas fa-chevron-left mr-2"></i>
            Previous
        </button>
        
        <div class="text-sm font-medium text-gray-600">
            Page <span class="text-[#0E335D] font-bold" x-text="currentPage"></span> of <span class="text-[#0E335D] font-bold" x-text="totalPages"></span>
        </div>
        
        <button @click="nextPage()" :disabled="currentPage === totalPages" 
            class="px-6 py-3 rounded-lg font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            :class="currentPage === totalPages ? 'bg-gray-300 text-gray-500' : 'bg-[#65DB7F] text-white hover:opacity-90'">
            Next
            <i class="fas fa-chevron-right ml-2"></i>
        </button>
    </div>

</div>



