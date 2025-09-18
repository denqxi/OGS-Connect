<div class="group fixed top-0 left-0 h-screen bg-white shadow-lg border-r border-gray-200 transition-all duration-300 w-20 hover:w-72">

    <!-- Logo Section -->
    <div class="p-6 border-b border-gray-200 flex items-center space-x-3">
        <img src="{{ asset('images/logo.png') }}" alt="GLS Scheduling" class="w-10 h-10 object-contain">
        <!-- Text appears only when expanded -->
        <div class="leading-tight opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <div class="text-blue-950 text-xl font-extrabold">OUTSOURCING</div>
            <div class="text-blue-950 text-sm font-semibold tracking-wide">GLOBAL SOLUTIONS</div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="mt-6">
        <ul class="space-y-2 px-2">
            <li>
                <a href="{{ url('/dashboard') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                    <i class="fas fa-home text-lg"></i>
                    <span class="font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-300">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/hiring') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                    <i class="fas fa-user-plus text-lg"></i>
                    <span class="font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-300">Hiring</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/onboarding') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                    <i class="fas fa-clipboard-check text-lg"></i>
                    <span class="font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-300">Onboarding</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/employees') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                    <i class="fas fa-users text-lg"></i>
                    <span class="font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-300">Employees</span>
                </a>
            </li>
            <li>
                <a href="{{ url('/scheduling') }}" class="flex items-center space-x-3 px-4 py-3 text-white bg-slate-700 rounded-lg">
                    <i class="fas fa-calendar-alt text-lg"></i>
                    <span class="font-medium opacity-0 group-hover:opacity-100 transition-opacity duration-300">GLS Scheduling</span>
                </a>
            </li>
        </ul>
    </nav>
</div>
