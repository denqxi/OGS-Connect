<!DOCTYPE html>
<html lang="en" x-data="darkModeHandler()" :class="{ 'dark': darkMode }" x-cloak>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tutor Portal</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {},
            }
        }
    </script>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- AlpineJS -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 font-sans transition-colors duration-300">

    <!-- Header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div
            class="bg-white dark:bg-gray-800 shadow-md rounded-xl px-4 py-3 flex items-center justify-between mb-4 md:mb-6 transition-colors duration-300">

            <!-- Left Logo -->
            <div class="flex items-center">
                <img src="{{ asset('images/logo.png') }}" alt="GLS Scheduling" class="h-8 md:h-12 ms-2 object-contain">
                <div class="ml-2 md:ml-3">
                    <div class="text-sm md:text-lg font-bold text-[#0E335D] dark:text-gray-200">OUTSOURCING</div>
                    <div class="text-xs md:text-sm font-bold text-gray-600 dark:text-gray-400">GLOBAL SOLUTIONS</div>
                </div>
            </div>

            <!-- Right Controls -->
            <div class="flex items-center space-x-2 md:space-x-4 relative" x-data="{ open: false }">

                <!-- Name -->
                <span
                    class="text-sm md:text-base font-semibold text-gray-800 dark:text-gray-200 uppercase hidden sm:inline">{{ $tutor->full_name ?? 'TUTOR' }}</span>

                <!-- Notifications -->
                <button
                    class="relative p-1 md:p-2 text-[#0E335D] dark:text-gray-200 rounded-lg transition-transform duration-200 hover:text-[#0B284A] hover:dark:text-white hover:scale-110"
                    title="View notifications and updates">
                    <i class="fas fa-bell text-lg md:text-xl"></i>
                    <span class="absolute -top-1 -right-1 w-2 h-2 md:w-3 md:h-3 bg-red-500 rounded-full"></span>
                </button>

                <!-- Burger Menu -->
                <div class="relative">
                    <button @click="open = !open"
                        class="p-1 md:p-2 text-[#0E335D] dark:text-gray-200 rounded-lg transition-transform duration-200 hover:text-[#0B284A] hover:dark:text-white hover:scale-110"
                        title="Open menu options">
                        <i class="fas fa-bars text-lg md:text-xl"></i>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open" x-cloak @click.away="open = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-36 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50">

                        <!-- Dark / Light Mode -->
                        <button @click="toggleDarkMode()"
                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                            title="Switch between dark and light theme">
                            <i :class="darkMode ? 'fas fa-sun mr-2' : 'fas fa-moon mr-2'"></i>
                            <span x-text="darkMode ? 'Light Mode' : 'Dark Mode'"></span>
                        </button>

                        <div class="border-t border-gray-200 dark:border-gray-700"></div>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('tutor.logout') }}" class="w-full">
                            @csrf
                            <button type="submit"
                                class="flex items-center w-full px-4 py-2 text-sm text-red-500 hover:bg-red-500/20 dark:text-red-400 dark:hover:bg-red-500/20 transition-colors duration-200"
                                title="Sign out of your tutor account">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4 md:mb-6">
        <div
            class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-2 md:px-4 rounded-xl shadow-sm overflow-x-auto transition-colors duration-300">
            @php $activeTab = request('tab', 'profile'); @endphp
            <nav class="flex md:space-x-8 no-scrollbar relative">

                <!-- Profile Info -->
                <a href="{{ route('tutor.portal', ['tab' => 'profile']) }}"
                    class="flex-shrink-0 py-3 md:py-4 px-3 md:px-4 relative
                    {{ $activeTab == 'profile' ? 'after:absolute after:-bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-[#0E335D] dark:after:bg-[#AFC3E0] after:rounded-full after:transition-all after:duration-300' : '' }}">
                    <div
                        class="flex flex-col md:flex-row items-center md:space-x-2 font-medium text-base md:text-sm
                        text-[#0E335D] dark:text-[#AFC3E0] hover:text-[#0E335D] dark:hover:text-[#D1E2F0]
                        transform hover:scale-105 transition-transform duration-200">
                        <i class="fas fa-id-card text-xl md:text-lg"></i>
                        <span class="hidden sm:inline">Profile Info</span>
                    </div>
                </a>

                <!-- Payment Details -->
                <a href="{{ route('tutor.portal', ['tab' => 'payment']) }}"
                    class="flex-shrink-0 py-3 md:py-4 px-3 md:px-4 relative
                    {{ $activeTab == 'payment' ? 'after:absolute after:-bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-[#D35400] dark:after:bg-[#F0B37A] after:rounded-full after:transition-all after:duration-300' : '' }}">
                    <div
                        class="flex flex-col md:flex-row items-center md:space-x-2 font-medium text-base md:text-sm
                           text-[#D35400] dark:text-[#F0B37A] hover:text-[#D35400] dark:hover:text-[#F8C99E]
                           transform hover:scale-105 transition-transform duration-200">
                        <i class="fas fa-credit-card text-xl md:text-lg"></i>
                        <span class="hidden sm:inline">Payment Details</span>
                    </div>
                </a>

                <!-- Account Management -->
                <a href="{{ route('tutor.portal', ['tab' => 'account']) }}"
                    class="flex-shrink-0 py-3 md:py-4 px-3 md:px-4 relative
                    {{ $activeTab == 'account' ? 'after:absolute after:-bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-[#4B5563] dark:after:bg-[#D1D5DB] after:rounded-full after:transition-all after:duration-300' : '' }}">
                    <div
                        class="flex flex-col md:flex-row items-center md:space-x-2 font-medium text-base md:text-sm
                        text-[#4B5563] dark:text-[#D1D5DB] hover:text-[#4B5563] dark:hover:text-[#E5E7EB]
                        transform hover:scale-105 transition-transform duration-200">
                        <i class="fas fa-user-cog text-xl md:text-lg"></i>
                        <span class="hidden sm:inline">Account Management</span>
                    </div>
                </a>


            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-4 md:p-6 mb-6 transition-colors duration-300">
            @if ($activeTab == 'profile')
                @include('tutor.tabs.profile_info')
            @elseif ($activeTab == 'payment')
                @include('tutor.tabs.payment_details')
            @elseif ($activeTab == 'account')
                @include('tutor.tabs.account_management')
            @endif
        </div>
    </div>

    <script>
        function darkModeHandler() {
            return {
                darkMode: localStorage.getItem('darkMode') === 'true', // load from storage
                toggleDarkMode() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('darkMode', this.darkMode); // save to storage
                }
            }
        }
    </script>


</body>

</html>
