@extends('layouts.app')

@section('title', 'OGS Connect')

@section('content')
    <!-- Header -->
    @include('layouts.header', ['pageTitle' => 'Employees'])

    <!-- Tab Navigation -->
    <div class="bg-white border-b border-gray-200 px-4 md:px-6 rounded-xl shadow-sm mb-6">
        @php $activeTab = request('tab', 'gls'); @endphp
        <nav class="flex overflow-x-auto md:space-x-8 no-scrollbar relative">

            <!-- GLS -->
            <a href="{{ route('employees.index', ['tab' => 'gls']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 font-medium text-sm md:text-base flex items-center space-x-2
                text-[#0E335D] hover:text-[#0E335D]/70 relative
                {{ $activeTab == 'gls' ? 'after:absolute after:-bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-[#0E335D] after:rounded-full after:transition-all after:duration-300' : '' }}">
                <i class="fas fa-graduation-cap"></i>
                <span class="hidden sm:inline">GLS</span>
            </a>

            <!-- Tutlo -->
            <a href="{{ route('employees.index', ['tab' => 'tutlo']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 font-medium text-sm md:text-base flex items-center space-x-2
                text-[#E6B800] hover:text-[#E6B800]/70 relative
                {{ $activeTab == 'tutlo' ? 'after:absolute after:-bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-[#E6B800] after:rounded-full after:transition-all after:duration-300' : '' }}">
                <i class="fas fa-comments"></i>
                <span class="hidden sm:inline">Tutlo</span>
            </a>

            <!-- Babilala -->
            <a href="{{ route('employees.index', ['tab' => 'babilala']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 font-medium text-sm md:text-base flex items-center space-x-2
                text-[#A78BFA] hover:text-[#A78BFA]/70 relative
                {{ $activeTab == 'babilala' ? 'after:absolute after:-bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-[#A78BFA] after:rounded-full after:transition-all after:duration-300' : '' }}">
                <i class="fas fa-book-open"></i>
                <span class="hidden sm:inline">Babilala</span>
            </a>

            <!-- Talk915 -->
            <a href="{{ route('employees.index', ['tab' => 'talk915']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 font-medium text-sm md:text-base flex items-center space-x-2
                text-[#128AD4] hover:text-[#128AD4]/70 relative
                {{ $activeTab == 'talk915' ? 'after:absolute after:-bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-[#128AD4] after:rounded-full after:transition-all after:duration-300' : '' }}">
                <i class="fas fa-language"></i>
                <span class="hidden sm:inline">Talk915</span>
            </a>

            <!-- Supervisors -->
            <a href="{{ route('employees.index', ['tab' => 'supervisors']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 font-medium text-sm md:text-base flex items-center space-x-2
                text-[#65DB7F] hover:text-[#65DB7F]/70 relative
                {{ $activeTab == 'supervisors' ? 'after:absolute after:-bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-[#65DB7F] after:rounded-full after:transition-all after:duration-300' : '' }}">
                <i class="fas fa-user-tie"></i>
                <span class="hidden sm:inline">Supervisors</span>
            </a>

            <!-- Archive -->
            <a href="{{ route('employees.index', ['tab' => 'archive']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ request('tab') == 'archive' ? 'border-b-2 border-[#E02F2F] text-[#E02F2F]' : 'text-[#E02F2F] hover:text-[#E02F2F]/70' }} 
           font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-archive"></i>
                <span class="hidden sm:inline">Archive</span>
            </a>

        </nav>
    </div>

    <!-- Main Content -->
    <div>
        <div class="max-w-full mx-auto">
            <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                @if ($activeTab == 'gls')
                    @include('emp_management.tabs.gls')
                @elseif($activeTab == 'tutlo')
                    @include('emp_management.tabs.tutlo')
                @elseif($activeTab == 'babilala')
                    @include('emp_management.tabs.babilala')
                @elseif($activeTab == 'talk915')
                    @include('emp_management.tabs.talk915')
                @elseif($activeTab == 'supervisors')
                    @include('emp_management.tabs.supervisors')
                @elseif($activeTab == 'archive')
                    @include('emp_management.tabs.employee_archive')
                @endif
            </div>
        </div>
    </div>

    <!-- Employee Details Modal -->
    @include('emp_management.partials.employee-modal')
@endsection
