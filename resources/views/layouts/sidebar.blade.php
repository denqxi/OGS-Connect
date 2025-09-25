<div
    class="group fixed top-0 left-0 h-screen bg-white shadow-lg border-r border-gray-200 w-20 hover:w-[calc(18rem-30px)] transition-all duration-500 ease-in-out">

    <!-- Logo Section -->
    <div class="p-4 border-b border-gray-200 flex items-center">
        <div class="flex-shrink-0 flex items-center justify-center">
            <img src="{{ asset('images/logo.png') }}" alt="GLS Scheduling"
                class="h-12 w-12 object-contain transition-all duration-500 ease-in-out">
        </div>

        <div
            class="opacity-0 group-hover:opacity-100 transition-opacity duration-500 ease-in-out ml-3 flex flex-col whitespace-nowrap">
            <div class="text-lg font-bold text-[#0E335D]">OUTSOURCING</div>
            <div class="text-xs font-semibold text-[#0E335D]">GLOBAL SOLUTIONS</div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="mt-6">
        <ul class="space-y-2 px-4">
            <!-- Dashboard -->
            <li class="min-h-[56px] flex items-center">
                <a href="/dashboard"
                    class="flex items-center space-x-3 w-full px-4 py-3 rounded-lg transition-all duration-300 ease-in-out text-[#0E335D] hover:text-[#0B294A] active:text-[#0E335D]/80 hover:bg-gray-100">
                    <i class="fas fa-home text-lg"></i>
                    <span
                        class="font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-500 ease-in-out whitespace-nowrap overflow-hidden">
                        Dashboard
                    </span>
                </a>
            </li>

            <!-- Hiring & Onboarding -->
            <li class="min-h-[56px] flex items-center">
                <a href="/hiring-onboarding"
                    class="flex items-center space-x-3 w-full px-4 py-3 rounded-lg transition-all duration-300 ease-in-out text-[#0E335D] hover:text-[#0B294A] active:text-[#0E335D]/80 hover:bg-gray-100">
                    <i class="fas fa-user-check text-lg"></i>
                    <span
                        class="font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-500 ease-in-out whitespace-nowrap overflow-hidden">
                        Hiring & Onboarding
                    </span>
                </a>
            </li>

            <!-- Employees -->
            <li class="min-h-[56px] flex items-center">
                <a href="/employees"
                    class="flex items-center space-x-3 w-full px-4 py-3 rounded-lg transition-all duration-300 ease-in-out text-[#0E335D] hover:text-[#0B294A] active:text-[#0E335D]/80 hover:bg-gray-100">
                    <i class="fas fa-users text-lg"></i>
                    <span
                        class="font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-500 ease-in-out whitespace-nowrap overflow-hidden">
                        Employees
                    </span>
                </a>
            </li>
            <!-- GLS Scheduling -->
            <li class="min-h-[56px] flex items-center">
                <a href="/scheduling"
                    class="flex items-center space-x-3 w-full px-4 py-3 text-white bg-[#234D7C] rounded-lg transition-all duration-300 ease-in-out hover:bg-[#033f92] active:bg-[#023873]">
                    <i class="fas fa-calendar-alt text-lg"></i>
                    <span
                        class="font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-500 ease-in-out whitespace-nowrap overflow-hidden">
                        GLS Scheduling
                    </span>
                </a>
            </li>
        </ul>
    </nav>
</div>
