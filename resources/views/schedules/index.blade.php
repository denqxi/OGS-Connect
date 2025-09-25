@extends('layouts.app')

@section('title', 'OGS Connect')

@section('content')
    <!-- Header -->
    @include('layouts.header', ['pageTitle' => 'GLS Scheduling'])

    <!-- Tab Navigation -->
    <div class="bg-white border-b border-gray-200 px-4 md:px-6 rounded-xl shadow-sm mb-6">
        <nav class="flex overflow-x-auto md:space-x-8 no-scrollbar">
            <!-- Employee Availability - Blue -->
            <a href="{{ route('schedules.index', ['tab' => 'employee']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ request('tab', 'employee') == 'employee' ? 'border-b-2 border-[#0E335D] text-[#0E335D]' : 'text-[#0E335D] hover:text-[#0E335D]/70' }} 
           font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-user-clock"></i>
                <span class="hidden sm:inline">Employee Availability</span>
            </a>

            <!-- Class Scheduling - Darker Green -->
            <a href="{{ route('schedules.index', ['tab' => 'class']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ request('tab') == 'class' ? 'border-b-2 border-[#4AC066] text-[#4AC066]' : 'text-[#4AC066] hover:text-[#4AC066]/70' }} 
           font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-calendar-plus"></i>
                <span class="hidden sm:inline">Class Scheduling</span>
            </a>

            <!-- Schedule History - Darker Orange -->
            <a href="{{ route('schedules.index', ['tab' => 'history']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ request('tab') == 'history' ? 'border-b-2 border-[#D97B15] text-[#D97B15]' : 'text-[#D97B15] hover:text-[#D97B15]/70' }} 
           font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-history"></i>
                <span class="hidden sm:inline">Schedule History</span>
            </a>
        </nav>
    </div>


    <!-- Main Content -->
    <div>
        <div class="max-w-full mx-auto">
            <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                @if (request('tab', 'employee') == 'employee')
                    @include('schedules.tabs.employee-availability')
                @elseif(request('tab') == 'class')
                    @include('schedules.tabs.class-scheduling')
                @elseif(request('tab') == 'history')
                    @include('schedules.tabs.schedule-history')
                @endif
            </div>
        </div>
    </div>

@endsection
