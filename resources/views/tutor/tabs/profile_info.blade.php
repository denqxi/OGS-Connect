<div class="bg-white dark:bg-gray-900 rounded-xl p-4 space-y-6">

    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between p-4 rounded-lg shadow-lg
           bg-gradient-to-r from-blue-100 via-green-100 to-green-200
           dark:from-gray-800 dark:via-gray-800 dark:to-gray-700">
        <div class="flex items-center space-x-4">
            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=96&h=96&fit=crop&crop=face"
                alt="Profile" class="w-20 h-20 rounded-full object-cover border-2 border-[#0E335D]">
            <div>
                <h2 class="text-lg font-semibold text-[#0E335D] dark:text-[#CFE2F3]">Josh Daniel Collins</h2>
                <p class="text-gray-700 dark:text-gray-300 text-sm">id@gmail.com</p>
            </div>
        </div>
        <div class="mt-4 md:mt-0 text-left md:text-right w-full md:w-40">
            <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Your Account:</label>
            <select
                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 hover:border-[#0E335D] focus:outline-none focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D] transition-all duration-150">
                <option>GLS</option>
                <option>OGS</option>
            </select>
        </div>
    </div>


    <hr class="border-gray-300 dark:border-gray-700">

    <!-- Personal Information -->
    <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">Personal Information</h3>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Row 1 -->
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">First Name <span
                        class="text-red-500">*</span></label>
                <input type="text" value="Josh Daniel" required
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200 
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Last Name <span
                        class="text-red-500">*</span></label>
                <input type="text" value="Collins" required
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200 
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Date of Birth <span
                        class="text-red-500">*</span></label>
                <input type="date" value="1999-06-15" required
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>

            <!-- Row 2 -->
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Address <span
                        class="text-red-500">*</span></label>
                <input type="text" value="123 Maple Street, Quezon City" required
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200 
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Email <span
                        class="text-red-500">*</span></label>
                <input type="email" value="joshcollins@example.com" required
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Contact Number <span
                        class="text-red-500">*</span></label>
                <input type="text" value="+63 912 345 6789" required
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>

            <!-- Row 3 (aligned under Address column only) -->
            <div class="md:col-start-1 md:col-span-1">
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">MS Teams ID <span
                        class="text-red-500">*</span></label>
                <input type="text" value="josh.collins.glsteacher" required
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
        </div>
    </div>

    <hr class="border-gray-300 dark:border-gray-700">

    <!-- Work Availability -->
    <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-4">Work Availability</h3>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">
        <!-- Account Boxes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- GLS Account Box -->
            <div
                class="bg-[#E8F0FE] dark:bg-[#1E3A5F] rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center mb-3 space-x-2">
                    <i class="fas fa-graduation-cap text-[#0E335D] dark:text-[#E8F0FE]"></i>
                    <h4 class="font-semibold text-[#0E335D] dark:text-[#E8F0FE]">GLS</h4>
                </div>
                <div class="flex flex-wrap gap-2 mb-3">
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#0E335D] border-gray-300 dark:border-gray-500"><span>Mon</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#0E335D] border-gray-300 dark:border-gray-500"><span>Tue</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#0E335D] border-gray-300 dark:border-gray-500"><span>Wed</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#0E335D] border-gray-300 dark:border-gray-500"><span>Thu</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#0E335D] border-gray-300 dark:border-gray-500"><span>Fri</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#0E335D] border-gray-300 dark:border-gray-500"><span>Sat</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#0E335D] border-gray-300 dark:border-gray-500"><span>Sun</span>
                    </label>
                </div>
                <div class="flex gap-2">
                    <select
                        class="flex-1 border border-gray-200 dark:border-gray-500 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-gray-100 hover:border-[#0E335D] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D] transition-all duration-150">
                        <option>6:00 PM</option>
                    </select>
                    <select
                        class="flex-1 border border-gray-200 dark:border-gray-500 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-gray-100 hover:border-[#0E335D] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D] transition-all duration-150">
                        <option>5:00 AM</option>
                    </select>
                </div>
            </div>

            <!-- Babilala Account Box -->
            <div
                class="bg-[#F3E8FF] dark:bg-[#6B4EC9] rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center mb-3 space-x-2">
                    <i class="fas fa-book-open text-[#A78BFA] dark:text-[#F3E8FF]"></i>
                    <h4 class="font-semibold text-[#A78BFA] dark:text-[#F3E8FF]">Babilala</h4>
                </div>
                <div class="flex flex-wrap gap-2 mb-3">
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#A78BFA] border-gray-300 dark:border-gray-500"><span>Mon</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#A78BFA] border-gray-300 dark:border-gray-500"><span>Tue</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#A78BFA] border-gray-300 dark:border-gray-500"><span>Wed</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#A78BFA] border-gray-300 dark:border-gray-500"><span>Thu</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#A78BFA] border-gray-300 dark:border-gray-500"><span>Fri</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#A78BFA] border-gray-300 dark:border-gray-500"><span>Sat</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#A78BFA] border-gray-300 dark:border-gray-500"><span>Sun</span>
                    </label>
                </div>
                <div class="flex gap-2">
                    <select
                        class="flex-1 border border-gray-200 dark:border-gray-500 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-gray-100 hover:border-[#A78BFA] focus:border-[#A78BFA] focus:ring-1 focus:ring-[#A78BFA] transition-all duration-150">
                        <option>6:00 PM</option>
                    </select>
                    <select
                        class="flex-1 border border-gray-200 dark:border-gray-500 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-gray-100 hover:border-[#A78BFA] focus:border-[#A78BFA] focus:ring-1 focus:ring-[#A78BFA] transition-all duration-150">
                        <option>5:00 AM</option>
                    </select>
                </div>
            </div>

            <!-- Tutlo Account Box -->
            <div
                class="bg-[#FFF9E6] dark:bg-[#7A6400] rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center mb-3 space-x-2">
                    <i class="fas fa-comments text-[#E6B800] dark:text-[#FFF9E6]"></i>
                    <h4 class="font-semibold text-[#E6B800] dark:text-[#FFF9E6]">Tutlo</h4>
                </div>
                <div class="flex flex-wrap gap-2 mb-3">
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#E6B800] border-gray-300 dark:border-gray-500"><span>Mon</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#E6B800] border-gray-300 dark:border-gray-500"><span>Tue</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#E6B800] border-gray-300 dark:border-gray-500"><span>Wed</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#E6B800] border-gray-300 dark:border-gray-500"><span>Thu</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#E6B800] border-gray-300 dark:border-gray-500"><span>Fri</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#E6B800] border-gray-300 dark:border-gray-500"><span>Sat</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#E6B800] border-gray-300 dark:border-gray-500"><span>Sun</span>
                    </label>
                </div>
                <div class="flex gap-2">
                    <select
                        class="flex-1 border border-gray-200 dark:border-gray-500 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-gray-100 hover:border-[#E6B800] focus:border-[#E6B800] focus:ring-1 focus:ring-[#E6B800] transition-all duration-150">
                        <option>6:00 PM</option>
                    </select>
                    <select
                        class="flex-1 border border-gray-200 dark:border-gray-500 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-gray-100 hover:border-[#E6B800] focus:border-[#E6B800] focus:ring-1 focus:ring-[#E6B800] transition-all duration-150">
                        <option>5:00 AM</option>
                    </select>
                </div>
            </div>

            <!-- Talk915 Account Box -->
            <div
                class="bg-[#E6F4FF] dark:bg-[#005C8C] rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center mb-3 space-x-2">
                    <i class="fas fa-language text-[#128AD4] dark:text-[#A3D8FF]"></i>
                    <h4 class="font-semibold text-[#128AD4] dark:text-[#A3D8FF]">Talk915</h4>
                </div>
                <div class="flex flex-wrap gap-2 mb-4">
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#128AD4] border-gray-300 dark:border-gray-500"><span>Mon</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#128AD4] border-gray-300 dark:border-gray-500"><span>Tue</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#128AD4] border-gray-300 dark:border-gray-500"><span>Wed</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#128AD4] border-gray-300 dark:border-gray-500"><span>Thu</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#128AD4] border-gray-300 dark:border-gray-500"><span>Fri</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#128AD4] border-gray-300 dark:border-gray-500"><span>Sat</span>
                    </label>
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                            class="w-4 h-4 accent-[#128AD4] border-gray-300 dark:border-gray-500"><span>Sun</span>
                    </label>
                </div>
                <div class="flex gap-2">
                    <select
                        class="flex-1 border border-gray-200 dark:border-gray-500 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-gray-100 hover:border-[#128AD4] focus:border-[#128AD4] focus:ring-1 focus:ring-[#128AD4] transition-all duration-150">
                        <option>6:00 PM</option>
                    </select>
                    <select
                        class="flex-1 border border-gray-200 dark:border-gray-500 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-gray-100 hover:border-[#128AD4] focus:border-[#128AD4] focus:ring-1 focus:ring-[#128AD4] transition-all duration-150">
                        <option>5:00 AM</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <hr class="border-gray-300 dark:border-gray-700">

    <!-- Class Schedule -->
    <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-3">Class Schedule</h3>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Single Class Card -->
            <div
                class="bg-white dark:bg-gray-700 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                <div class="bg-[#0E335D] dark:bg-[#1E3A5F] text-white px-4 py-2 font-semibold text-center">
                    TAKADA | 3-SA | 8:40 AM
                </div>
                <div class="p-4 grid grid-cols-2 gap-2">
                    <div
                        class="bg-[#ECFDF5] dark:bg-gray-600 text-[#065F46] dark:text-[#D1FAE5] rounded-md px-3 py-2 text-center font-medium">
                        Fatherine</div>
                    <div
                        class="bg-[#ECFDF5] dark:bg-gray-600 text-[#065F46] dark:text-[#D1FAE5] rounded-md px-3 py-2 text-center font-medium">
                        Kath</div>
                    <div
                        class="bg-[#ECFDF5] dark:bg-gray-600 text-[#065F46] dark:text-[#D1FAE5] rounded-md px-3 py-2 text-center font-medium">
                        Melky</div>
                    <div
                        class="bg-[#ECFDF5] dark:bg-gray-600 text-[#065F46] dark:text-[#D1FAE5] rounded-md px-3 py-2 text-center font-medium">
                        Jody</div>
                </div>
                <div
                    class="px-4 py-2 bg-[#F3F4F6] dark:bg-gray-700 text-gray-500 dark:text-gray-300 text-xs text-center">
                    Slots: 4/4
                </div>
            </div>
            <!-- Single Class Card -->
            <div
                class="bg-white dark:bg-gray-700 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                <div class="bg-[#0E335D] dark:bg-[#1E3A5F] text-white px-4 py-2 font-semibold text-center">
                    TENSHIN | 3-SA | 9:40 AM
                </div>
                <div class="p-4 grid grid-cols-2 gap-2">
                    <div
                        class="bg-[#ECFDF5] dark:bg-gray-600 text-[#065F46] dark:text-[#D1FAE5] rounded-md px-3 py-2 text-center font-medium">
                        Fatherine</div>
                    <div
                        class="bg-[#ECFDF5] dark:bg-gray-600 text-[#065F46] dark:text-[#D1FAE5] rounded-md px-3 py-2 text-center font-medium">
                        Kath</div>
                    <div
                        class="bg-[#ECFDF5] dark:bg-gray-600 text-[#065F46] dark:text-[#D1FAE5] rounded-md px-3 py-2 text-center font-medium">
                        Melky</div>
                    <div
                        class="bg-[#ECFDF5] dark:bg-gray-600 text-[#065F46] dark:text-[#D1FAE5] rounded-md px-3 py-2 text-center font-medium">
                        Jody</div>
                </div>
                <div
                    class="px-4 py-2 bg-[#F3F4F6] dark:bg-gray-700 text-gray-500 dark:text-gray-300 text-xs text-center">
                    Slots: 4/4
                </div>
            </div>
            <!-- Single Class Card -->
            <div
                class="bg-white dark:bg-gray-700 shadow rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                <div class="bg-[#0E335D] dark:bg-[#1E3A5F] text-white px-4 py-2 font-semibold text-center">
                    SAKURAGAOKA | 5-SA | 10:40 AM
                </div>
                <div class="p-4 grid grid-cols-2 gap-2">
                    <div
                        class="bg-[#ECFDF5] dark:bg-gray-600 text-[#065F46] dark:text-[#D1FAE5] rounded-md px-3 py-2 text-center font-medium">
                        Fatherine</div>
                    <div
                        class="bg-[#ECFDF5] dark:bg-gray-600 text-[#065F46] dark:text-[#D1FAE5] rounded-md px-3 py-2 text-center font-medium">
                        Kath</div>
                    <div
                        class="bg-[#ECFDF5] dark:bg-gray-600 text-[#065F46] dark:text-[#D1FAE5] rounded-md px-3 py-2 text-center font-medium">
                        Melky</div>
                    <div
                        class="bg-[#ECFDF5] dark:bg-gray-600 text-[#065F46] dark:text-[#D1FAE5] rounded-md px-3 py-2 text-center font-medium">
                        Jody</div>
                </div>
                <div
                    class="px-4 py-2 bg-[#F3F4F6] dark:bg-gray-700 text-gray-500 dark:text-gray-300 text-xs text-center">
                    Slots: 4/4
                </div>
            </div>
            <!-- Repeat other class cards -->
        </div>
    </div>
</div>
