<div
    class="group fixed top-0 left-0 h-screen bg-white shadow-lg border-r border-gray-200 w-20 hover:w-[calc(18rem-30px)] transition-all duration-500 ease-in-out z-50">

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
                <a href="{{ route('dashboard') }}"
                    class="sidebar-nav-item sidebar-hover-effect flex items-center space-x-3 w-full px-4 py-3 rounded-lg transition-all duration-300 ease-in-out transform hover:scale-105 active:scale-95 {{ Route::is('dashboard') ? 'text-white bg-[#234D7C] hover:bg-[#033f92] active:bg-[#023873] shadow-md border-l-4 border-white scale-105 active' : 'text-[#0E335D] hover:text-[#0B294A] active:text-[#0E335D]/80 hover:bg-gray-100 hover:shadow-sm' }}">
                    <i class="sidebar-icon fas fa-home text-lg"></i>
                    <span
                        class="sidebar-text font-medium opacity-0 group-hover:opacity-100 transition-all duration-500 ease-in-out whitespace-nowrap overflow-hidden transform translate-x-2 group-hover:translate-x-0">
                        Dashboard
                    </span>
                </a>
            </li>

            <!-- Hiring & Onboarding -->
            <li class="min-h-[56px] flex items-center">
                <a href="/hiring-onboarding"
                    class="sidebar-nav-item sidebar-hover-effect flex items-center space-x-3 w-full px-4 py-3 rounded-lg transition-all duration-300 ease-in-out transform hover:scale-105 active:scale-95 {{ request()->is('hiring-onboarding*') ? 'text-white bg-[#234D7C] hover:bg-[#033f92] active:bg-[#023873] shadow-md border-l-4 border-white scale-105 active' : 'text-[#0E335D] hover:text-[#0B294A] active:text-[#0E335D]/80 hover:bg-gray-100 hover:shadow-sm' }}">
                    <i class="sidebar-icon fas fa-user-check text-lg"></i>
                    <span
                        class="sidebar-text font-medium opacity-0 group-hover:opacity-100 transition-all duration-500 ease-in-out whitespace-nowrap overflow-hidden transform translate-x-2 group-hover:translate-x-0">
                        Hiring & Onboarding
                    </span>
                </a>
            </li>

            <!-- Employees -->
            <li class="min-h-[56px] flex items-center">
                <a href="{{ route('employees.index') }}"
                    class="sidebar-nav-item sidebar-hover-effect flex items-center space-x-3 w-full px-4 py-3 rounded-lg transition-all duration-300 ease-in-out transform hover:scale-105 active:scale-95 {{ request()->is('employees*') ? 'text-white bg-[#234D7C] hover:bg-[#033f92] active:bg-[#023873] shadow-md border-l-4 border-white scale-105 active' : 'text-[#0E335D] hover:text-[#0B294A] active:text-[#0E335D]/80 hover:bg-gray-100 hover:shadow-sm' }}">
                    <i class="sidebar-icon fas fa-users text-lg"></i>
                    <span
                        class="sidebar-text font-medium opacity-0 group-hover:opacity-100 transition-all duration-500 ease-in-out whitespace-nowrap overflow-hidden transform translate-x-2 group-hover:translate-x-0">
                        Employees
                    </span>
                </a>
            </li>
            <!-- GLS Scheduling -->
            <li class="min-h-[56px] flex items-center">
                <a href="/scheduling"
                    class="sidebar-nav-item sidebar-hover-effect flex items-center space-x-3 w-full px-4 py-3 rounded-lg transition-all duration-300 ease-in-out transform hover:scale-105 active:scale-95 {{ Route::is('schedules.*') || Route::is('class-scheduling') || request()->is('scheduling*') || request()->is('class-scheduling*') ? 'text-white bg-[#234D7C] hover:bg-[#033f92] active:bg-[#023873] shadow-md border-l-4 border-white scale-105 active' : 'text-[#0E335D] hover:text-[#0B294A] active:text-[#0E335D]/80 hover:bg-gray-100 hover:shadow-sm' }}">
                    <i class="sidebar-icon fas fa-calendar-alt text-lg"></i>
                    <span
                        class="sidebar-text font-medium opacity-0 group-hover:opacity-100 transition-all duration-500 ease-in-out whitespace-nowrap overflow-hidden transform translate-x-2 group-hover:translate-x-0">
                        GLS Scheduling
                    </span>
                </a>
            </li>
        </ul>
    </nav>
</div>
