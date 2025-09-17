<div class="w-75 bg-white shadow-lg border-r border-gray-200 fixed h-screen">
    <!-- Logo Section -->
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center space-x-3">
                <img src="{{ asset('images/logo.png') }}" 
                    alt="GLS Scheduling" 
                    class="w-full h-full object-contain">
            <div class="leading-tight">
                <div class="text-blue-950 text-2xl font-extrabold font-['Inter']">OUTSOURCING</div>
                <div class="text-blue-950 text-sm font-semibold font-['Inter'] tracking-wide">GLOBAL SOLUTIONS</div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="mt-6">
        <ul class="space-y-2 px-4">
            <li><a href="{{ url('/dashboard') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                <i class="fas fa-home text-lg"></i><span class="font-medium">Dashboard</span>
            </a></li>
            <li><a href="{{ url('/hiring') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                <i class="fas fa-user-plus text-lg"></i><span class="font-medium">Hiring</span>
            </a></li>
            <li><a href="{{ url('/onboarding') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                <i class="fas fa-clipboard-check text-lg"></i><span class="font-medium">Onboarding</span>
            </a></li>
            <li><a href="{{ url('/employees') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                <i class="fas fa-users text-lg"></i><span class="font-medium">Employees</span>
            </a></li>
            <li><a href="{{ url('/scheduling') }}" class="flex items-center space-x-3 px-4 py-3 text-white bg-slate-700 rounded-lg">
                <i class="fas fa-calendar-alt text-lg"></i><span class="font-medium">GLS Scheduling</span>
            </a></li>
        </ul>
    </nav>
</div>
