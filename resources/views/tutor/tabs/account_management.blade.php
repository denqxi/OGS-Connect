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
            @if(isset($securityQuestion1) && $securityQuestion1)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Security Question 1</label>
                        <div class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-200">
                            {{ $securityQuestion1 }}
                        </div>
                    </div>

                    @if(isset($securityQuestion2) && $securityQuestion2)
                        <div>
                            <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Security Question 2</label>
                            <div class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-200">
                                {{ $securityQuestion2 }}
                            </div>
                        </div>
                    @endif
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">Questions are set. If you want to change them, click <strong>Update Security Questions</strong>.</p>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">You have no security questions set. Click <strong>Update Security Questions</strong> to add them.</p>
                </div>
            @endif
        </div>

        <!-- Update Security Questions Button -->
        <div class="flex justify-center md:justify-end">
            <button id="updateSecurityQuestionsBtn"
                class="bg-gray-700 dark:bg-gray-600 text-white font-semibold px-6 py-2 rounded-full hover:bg-gray-800 dark:hover:bg-gray-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                Update Security Questions
            </button>
        </div>

        <!-- Security Questions Update Form (Hidden Modal) -->
        <div id="securityQuestionsUpdateForm" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Update Security Questions</h3>
                
                <form id="updateSecurityForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Security Question 1</label>
                        <select name="security_question_1" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            <option value="">Select a question...</option>
                            <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                            <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                            <option value="What city were you born in?">What city were you born in?</option>
                            <option value="What was your favorite subject in school?">What was your favorite subject in school?</option>
                            <option value="What is the name of your childhood best friend?">What is the name of your childhood best friend?</option>
                            <option value="What was your first car?">What was your first car?</option>
                            <option value="What is your favorite color?">What is your favorite color?</option>
                            <option value="What was the name of your elementary school?">What was the name of your elementary school?</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Answer</label>
                        <input type="text" name="security_answer_1" placeholder="Enter your answer" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500" />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Security Question 2</label>
                        <select name="security_question_2" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            <option value="">Select a question...</option>
                            <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                            <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                            <option value="What city were you born in?">What city were you born in?</option>
                            <option value="What was your favorite subject in school?">What was your favorite subject in school?</option>
                            <option value="What is the name of your childhood best friend?">What is the name of your childhood best friend?</option>
                            <option value="What was your first car?">What was your first car?</option>
                            <option value="What is your favorite color?">What is your favorite color?</option>
                            <option value="What was the name of your elementary school?">What was the name of your elementary school?</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Answer</label>
                        <input type="text" name="security_answer_2" placeholder="Enter your answer" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500" />
                    </div>

                    <div id="securityUpdateError" class="hidden bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-600"></div>

                    <div class="flex gap-3 justify-end pt-4">
                        <button type="button" id="cancelSecurityUpdate" class="px-4 py-2 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-gray-700 dark:bg-gray-600 text-white rounded-md hover:bg-gray-800 dark:hover:bg-gray-500">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script src="{{ asset('js/tutor-account.js') }}"></script>
