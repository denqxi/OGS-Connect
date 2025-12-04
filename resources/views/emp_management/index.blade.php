@extends('layouts.app')

@section('title', 'OGS Connect')

@section('content')
    <!-- Header -->
    @include('layouts.header', ['pageTitle' => 'Employees'])

    <!-- Tab Navigation -->
    <div class="bg-white border-b border-gray-200 px-4 md:px-6 rounded-xl shadow-sm mb-2">
        @php $activeTab = request('tab', 'gls'); @endphp
        <nav class="flex overflow-x-auto md:space-x-8 no-scrollbar">

            <!-- GLS -->
            <a href="{{ route('employees.index', ['tab' => 'gls']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ $activeTab == 'gls' ? 'border-b-2 border-[#4ADE80] text-[#16A34A] font-bold' : 'text-gray-600 hover:text-[#1E40AF]' }} 
           text-sm md:text-base flex items-center space-x-2 transition-colors">
                <i class="fas fa-graduation-cap"></i>
                <span class="hidden sm:inline">GLS</span>
            </a>

            <!-- Tutlo -->
            <a href="{{ route('employees.index', ['tab' => 'tutlo']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ $activeTab == 'tutlo' ? 'border-b-2 border-[#4ADE80] text-[#16A34A] font-bold' : 'text-gray-600 hover:text-[#1E40AF]' }} 
           text-sm md:text-base flex items-center space-x-2 transition-colors">
                <i class="fas fa-comments"></i>
                <span class="hidden sm:inline">Tutlo</span>
            </a>

            <!-- Babilala -->
            <a href="{{ route('employees.index', ['tab' => 'babilala']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ $activeTab == 'babilala' ? 'border-b-2 border-[#4ADE80] text-[#16A34A] font-bold' : 'text-gray-600 hover:text-[#1E40AF]' }} 
           text-sm md:text-base flex items-center space-x-2 transition-colors">
                <i class="fas fa-book-open"></i>
                <span class="hidden sm:inline">Babilala</span>
            </a>

            <!-- Talk915 -->
            <a href="{{ route('employees.index', ['tab' => 'talk915']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ $activeTab == 'talk915' ? 'border-b-2 border-[#4ADE80] text-[#16A34A] font-bold' : 'text-gray-600 hover:text-[#1E40AF]' }} 
           text-sm md:text-base flex items-center space-x-2 transition-colors">
                <i class="fas fa-language"></i>
                <span class="hidden sm:inline">Talk915</span>
            </a>

            <!-- Supervisors -->
            <a href="{{ route('employees.index', ['tab' => 'supervisors']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ $activeTab == 'supervisors' ? 'border-b-2 border-[#4ADE80] text-[#16A34A] font-bold' : 'text-gray-600 hover:text-[#1E40AF]' }} 
           text-sm md:text-base flex items-center space-x-2 transition-colors">
                <i class="fas fa-user-tie"></i>
                <span class="hidden sm:inline">Supervisors</span>
            </a>

            <!-- Archive -->
            <a href="{{ route('employees.index', ['tab' => 'archive']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ $activeTab == 'archive' ? 'border-b-2 border-[#4ADE80] text-[#16A34A] font-bold' : 'text-gray-600 hover:text-[#1E40AF]' }} 
           text-sm md:text-base flex items-center space-x-2 transition-colors">
                <i class="fas fa-archive"></i>
                <span class="hidden sm:inline">Archive</span>
            </a>

        </nav>
    </div>

    <!-- Main Content -->
    <div>
        <div class="max-w-full mx-auto">
            <div class="bg-white rounded-lg shadow-sm">
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
