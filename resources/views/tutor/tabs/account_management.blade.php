<!-- Account Management Content -->
<div class="space-y-8">

    <!-- Section 1: Security / Account Details -->
    <h3 class="text-lg md:text-xl font-semibold text-gray-700 dark:text-gray-200">Security / Account Details</h3>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">

        <!-- System ID -->
        <div>
            <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">System ID</label>
            <input type="text" value="{{ $tutor->tutorID ?? 'N/A' }}" readonly
                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-200 cursor-not-allowed">
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">System ID is automatically assigned and cannot be changed.</p>
        </div>

        <!-- Username -->
        <div>
            <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Username</label>
            <input type="text" value="{{ $tutor->username ?? 'N/A' }}" readonly
                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-200 cursor-not-allowed">
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Username is automatically assigned and cannot be changed.</p>
        </div>

        <!-- Email -->
        <div>
            <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Email Address</label>
            <input type="email" value="{{ $tutor->email ?? 'N/A' }}" readonly
                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-200 cursor-not-allowed">
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Email address is managed by the system administrator.</p>
        </div>
    </div>

    <hr class="border-gray-200 dark:border-gray-700">

    <!-- Section 2: Change Password (Optional) -->
    <h3 class="text-lg md:text-xl font-semibold text-gray-700 dark:text-gray-200">Change Password</h3>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Current Password</label>
                <div class="relative">
                    <input type="password" id="currentPassword" placeholder="Enter your current password"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 pr-10 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none transition-all duration-200">
                    <button type="button" id="toggleCurrentPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-eye" id="currentPasswordIcon"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">New Password</label>
                <div class="relative">
                    <input type="password" id="newPassword" placeholder="Enter your new password"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 pr-10 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none transition-all duration-200">
                    <button type="button" id="toggleNewPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-eye" id="newPasswordIcon"></i>
                    </button>
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Confirm New Password</label>
                <div class="relative">
                    <input type="password" id="confirmPassword" placeholder="Confirm your new password"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 pr-10 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none transition-all duration-200">
                    <button type="button" id="toggleConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                    </button>
                </div>
                <!-- Password Match Indicator -->
                <div id="passwordMatchIndicator" class="mt-2 text-sm hidden">
                    <div class="flex items-center">
                        <i id="passwordMatchIcon" class="mr-2"></i>
                        <span id="passwordMatchText"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Change Message -->
        <div id="passwordMessage" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

        <!-- Update Password Button -->
        <div class="flex justify-center md:justify-end">
            <button id="updatePasswordBtn"
                class="bg-gray-700 dark:bg-gray-600 text-white font-semibold px-6 py-2 rounded-full hover:bg-gray-800 dark:hover:bg-gray-500 transition-colors duration-200">
                Update Password
            </button>
        </div>
    </div>

    <hr class="border-gray-200 dark:border-gray-700">

    <!-- Section 3: Authentication Questions -->
    <h3 class="text-lg md:text-xl font-semibold text-gray-700 dark:text-gray-200">Authentication Questions</h3>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">

        <p class="text-sm text-gray-500 dark:text-gray-400">These questions are used to verify your identity when you need to recover your account or reset your password. Please choose questions and answers that only you would know.</p>

        <div id="securityQuestionsContainer">
            <!-- Security questions will be dynamically loaded here -->
            <div class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-600 mx-auto"></div>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Loading security questions...</p>
            </div>
        </div>

        <!-- Update Security Questions Button -->
        <div class="flex justify-center md:justify-end">
            <button id="updateSecurityQuestionsBtn"
                class="bg-gray-700 dark:bg-gray-600 text-white font-semibold px-6 py-2 rounded-full hover:bg-gray-800 dark:hover:bg-gray-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                Update Security Questions
            </button>
        </div>
    </div>

</div>

<script src="{{ asset('js/tutor-account.js') }}"></script>
