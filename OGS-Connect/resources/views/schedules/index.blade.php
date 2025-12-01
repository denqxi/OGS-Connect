@extends('layouts.app')

@section('title', 'OGS Connect')

@section('content')
    <!-- Top Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 px-4 md:px-6 py-3 md:py-4 flex items-center justify-between">
        <h1 class="text-lg md:text-2xl font-semibold text-gray-800">GLS Scheduling</h1>
        <div class="flex items-center space-x-3 md:space-x-4">
            <button class="p-2 text-gray-600 hover:text-gray-800">
                <i class="fas fa-sun text-lg md:text-xl"></i>
            </button>
            <button class="p-2 text-gray-600 hover:text-gray-800 relative">
                <i class="fas fa-bell text-lg md:text-xl"></i>
                <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
            </button>
            <div class="w-7 h-7 md:w-8 md:h-8 bg-gray-300 rounded-full overflow-hidden">
                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=32&h=32&fit=crop&crop=face"
                     alt="Profile" class="w-full h-full object-cover">
            </div>
        </div>
    </header>

    <!-- Tab Navigation -->
    <div class="bg-white border-b border-gray-200 px-4 md:px-6">
        <nav class="flex overflow-x-auto md:space-x-8 no-scrollbar">
            <a href="{{ route('schedules.index', ['tab' => 'employee']) }}"
               class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 {{ request('tab','employee') == 'employee' ? 'border-b-2 border-slate-700 text-slate-700' : 'text-gray-500 hover:text-gray-700' }} font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-user-clock"></i>
                <span class="hidden sm:inline">Employee Availability</span>
            </a>
            <a href="{{ route('schedules.index', ['tab' => 'class']) }}"
               class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 {{ request('tab') == 'class' ? 'border-b-2 border-slate-700 text-slate-700' : 'text-gray-500 hover:text-gray-700' }} font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-calendar-plus"></i>
                <span class="hidden sm:inline">Class Scheduling</span>
            </a>
            <a href="{{ route('schedules.index', ['tab' => 'history']) }}"
               class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 {{ request('tab') == 'history' ? 'border-b-2 border-slate-700 text-slate-700' : 'text-gray-500 hover:text-gray-700' }} font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-history"></i>
                <span class="hidden sm:inline">Schedule History</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="p-4 md:p-6 flex-1">
        <div class="bg-white rounded-lg shadow-sm">
            @if(request('tab','employee') == 'employee')
                @include('schedules.tabs.employee-availability')
            @elseif(request('tab') == 'class')
                @include('schedules.tabs.class-scheduling')
            @elseif(request('tab') == 'history')
                @include('schedules.tabs.schedule-history')
            @endif
        </div>
    </div>
@endsection
