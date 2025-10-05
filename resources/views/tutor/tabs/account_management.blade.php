<!-- Account Management Content -->
<div class="space-y-8">

    <!-- Section 1: Security / Account Details -->
    <h3 class="text-lg md:text-xl font-semibold text-gray-700 dark:text-gray-200">Security / Account Details</h3>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">

        <!-- System ID -->
        <div>
            <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">System ID</label>
            <input type="text" value="OGS-S1001" readonly
                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-200 cursor-not-allowed">
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">System ID is automatically assigned and cannot be changed.</p>
        </div>
    </div>

    <hr class="border-gray-200 dark:border-gray-700">

    <!-- Section 2: Change Password (Optional) -->
    <h3 class="text-lg md:text-xl font-semibold text-gray-700 dark:text-gray-200">Change Password</h3>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Current Password</label>
                <input type="password" placeholder="Enter your current password"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">New Password</label>
                <input type="password" placeholder="Enter your new password"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
            </div>

            <div class="md:col-span-2">
                <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Confirm New Password</label>
                <input type="password" placeholder="Confirm your new password"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
            </div>
        </div>

        <!-- Update Password Button -->
        <div class="flex justify-center md:justify-end">
            <button
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

        <div class="space-y-4">
            <!-- Security Question 1 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                <div>
                    <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Security Question 1</label>
                    <select
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
                        <option selected>What is your favorite color?</option>
                        <option>What was the name of your first pet?</option>
                        <option>What is your mother’s maiden name?</option>
                        <option>What was your first car?</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Answer 1</label>
                    <input type="text" placeholder="Enter your answer"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
                </div>
            </div>

            <!-- Security Question 2 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                <div>
                    <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Security Question 2</label>
                    <select
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
                        <option selected>What city were you born in?</option>
                        <option>What is the name of your favorite teacher?</option>
                        <option>What was your childhood nickname?</option>
                        <option>What is your favorite food?</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-600 dark:text-gray-300 font-medium mb-1">Answer 2</label>
                    <input type="text" placeholder="Enter your answer"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-gray-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Update Security Questions Button -->
        <div class="flex justify-center md:justify-end">
            <button
                class="bg-gray-700 dark:bg-gray-600 text-white font-semibold px-6 py-2 rounded-full hover:bg-gray-800 dark:hover:bg-gray-500 transition-colors duration-200">
                Update Security Questions
            </button>
        </div>
    </div>

</div>
