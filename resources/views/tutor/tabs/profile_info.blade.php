<div class="bg-white dark:bg-gray-900 rounded-xl p-4 space-y-6">

    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between p-4 rounded-lg shadow-lg
           bg-gradient-to-r from-blue-100 via-green-100 to-green-200
           dark:from-gray-800 dark:via-gray-800 dark:to-gray-700">
        <div class="flex items-center space-x-4">
            @php
                $defaultPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($tutor->full_name ?? 'Tutor') . '&color=0E335D&background=BCE6D4&size=128';
                $profilePhoto = $tutor->profile_photo ?? null;
                $photoUrl = $profilePhoto ? asset('storage/' . $profilePhoto) : $defaultPhoto;
            @endphp
            <div class="relative">
                <img id="tutorProfilePhoto" src="{{ $photoUrl }}"
                    alt="Profile" class="w-20 h-20 rounded-full object-cover border-2 border-[#0E335D]">
                <label class="absolute -bottom-1 -right-1 cursor-pointer" title="Change Profile Photo">
                    <input type="file" id="tutorProfilePhotoInput" class="hidden" accept="image/*">
                    <div class="flex items-center justify-center bg-[#0E335D] hover:bg-[#184679] text-white rounded-full shadow-md transform transition duration-200 hover:scale-105 w-7 h-7">
                        <i class="fas fa-camera text-white text-xs"></i>
                    </div>
                </label>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-[#0E335D] dark:text-[#CFE2F3]">{{ $tutor->first_name ?? '' }} {{ $tutor->middle_name ?? '' }} {{ $tutor->last_name ?? 'N/A' }}</h2>
                <p class="text-gray-700 dark:text-gray-300 text-sm">{{ $tutor->email ?? 'N/A' }}</p>
                <p class="text-gray-600 dark:text-gray-400 text-xs">{{ $tutor->applicant->email ?? 'N/A' }}</p>
                <p class="text-gray-600 dark:text-gray-400 text-xs">{{ $tutor->tutorID ?? 'N/A' }}</p>
            </div>
        </div>
        <div class="mt-4 md:mt-0 text-left md:text-right w-full md:w-40">
            <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Your Account:</label>
            <div class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-200 font-medium">
                {{ strtoupper($tutor->account?->account_name ?? 'NO ACCOUNT') }}
            </div>
        </div>
    </div>


    <hr class="border-gray-300 dark:border-gray-700">

    <!-- Personal Information -->
    <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold text-gray-700 dark:text-gray-200">Personal Information</h3>
        <div class="flex gap-2">
            <button id="editPersonalInfoBtn" 
                class="bg-[#F39C12] hover:bg-[#D97706] text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                <i class="fas fa-edit mr-1"></i>Edit Information
            </button>
            <button id="savePersonalInfoBtn" 
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed hidden">
                <i class="fas fa-save mr-1"></i>Save Changes
            </button>
            <button id="cancelPersonalInfoBtn" 
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 hidden">
                <i class="fas fa-times mr-1"></i>Cancel
            </button>
        </div>
    </div>
    
    <div id="personalInfoMessage" class="hidden mb-4 p-3 rounded-lg text-sm"></div>
    
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Row 1 -->
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">First Name <span
                        class="text-red-500">*</span></label>
                <input type="text" id="firstName" value="{{ $tutor->first_name ?? '' }}" required readonly
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200 
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Middle Name</label>
                <input type="text" id="middleName" value="{{ $tutor->middle_name ?? '' }}" readonly
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200 
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Last Name <span
                        class="text-red-500">*</span></label>
                <input type="text" id="lastName" value="{{ $tutor->last_name ?? '' }}" required readonly
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200 
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Date of Birth <span
                        class="text-red-500">*</span></label>
                <input type="date" id="dateOfBirth" value="{{ $tutor->date_of_birth ?? '' }}" required readonly
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>

            <!-- Row 2 -->
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Address <span
                        class="text-red-500">*</span></label>
                <input type="text" id="address" value="{{ $tutor->tutorDetails->address ?? '' }}" required readonly
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200 
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Email <span
                        class="text-red-500">*</span></label>
                <input type="email" id="email" value="{{ $tutor->email ?? '' }}" required readonly
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Contact Number <span
                        class="text-red-500">*</span></label>
                <input type="text" id="phoneNumber" value="{{ $tutor->phone_number ?? '' }}" required readonly
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>

            <!-- Row 3 (aligned under Address column only) -->
            <div class="md:col-start-1 md:col-span-1">
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">MS Teams ID <span
                        class="text-red-500">*</span></label>
                <input type="text" id="msTeamsId" value="{{ $tutor->tutorDetails->ms_teams_id ?? '' }}" required readonly
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
        </div>
    </div>

    <hr class="border-gray-300 dark:border-gray-700">

    <!-- Work Availability -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-700 dark:text-gray-200">Work Availability</h3>
        <div class="flex gap-2">
            <button id="saveAvailabilityBtn" 
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
                <i class="fas fa-save mr-1"></i>Save Changes
            </button>
            <button id="resetAvailabilityBtn" 
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                <i class="fas fa-undo mr-1"></i>Reset
            </button>
        </div>
    </div>
    
    <div id="availabilityMessage" class="hidden mb-4 p-3 rounded-lg text-sm"></div>
    
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">
        <!-- Account Boxes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="availabilityContainer">
            <!-- Account boxes will be dynamically loaded here -->
            <div class="col-span-full text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Loading availability data...</p>
                </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('js/tutor-profile.js') }}"></script>