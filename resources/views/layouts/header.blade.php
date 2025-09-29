<div class="bg-white shadow-md rounded-xl px-6 py-2.5 flex items-center justify-between mb-6">
    <h1 class="text-base md:text-lg font-bold text-[#0E335D]">@yield('page-title', 'GLS Scheduling')</h1>
    
    <div class="flex items-center space-x-3 md:space-x-4">
        <!-- Theme Toggle -->
        <button class="p-2 text-[#0E335D] hover:text-[#0B284A] rounded-lg transition-colors duration-300">
            <i class="fas fa-sun text-lg md:text-xl"></i>
        </button>

        <!-- Notifications -->
        <button class="p-2 text-[#0E335D] hover:text-[#0B284A] rounded-lg relative transition-colors duration-300">
            <i class="fas fa-bell text-lg md:text-xl"></i>
            <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
        </button>

        <!-- Profile Image with Dropdown -->
        <div x-data="{ open: false }" class="relative w-10 h-10 md:w-10 cursor-pointer">
            <img @click="open = !open"
                 src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=32&h=32&fit=crop&crop=face"
                 alt="Profile" class="w-full h-full object-cover rounded-full">

            <!-- Dropdown -->
            <div x-show="open" @click.away="open = false"
                 class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                <a href="#profile" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-user mr-2"></i> Profile
                </a>
                <div class="border-t border-gray-200"></div>
                <form method="POST" action="{{ route('supervisor.logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-500 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js for dropdown -->
<script src="//unpkg.com/alpinejs" defer></script>
