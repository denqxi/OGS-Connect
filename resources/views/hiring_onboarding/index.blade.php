@extends('layouts.app')

@section('title', 'OGS Connect')

@section('content')
    <!-- Header -->
    @include('layouts.header', ['pageTitle' => 'Hiring & Onboarding'])

    <!-- Tab Navigation -->
    <div class="bg-white border-b border-gray-200 px-4 md:px-6 rounded-xl shadow-sm mb-2">
        <nav class="flex overflow-x-auto md:space-x-8 no-scrollbar">

            <!-- New Applicant -->
            <a href="{{ route('hiring_onboarding.index', ['tab' => 'new']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ request('tab', 'new') == 'new' ? 'border-b-2 border-[#4ADE80] text-[#16A34A] font-bold' : 'text-gray-600 hover:text-[#1E40AF]' }} 
           text-sm md:text-base flex items-center space-x-2 transition-colors">
                <i class="fas fa-user-plus"></i>
                <span class="hidden sm:inline">New Applicant</span>
            </a>

            <!-- For Demo -->
            <a href="{{ route('hiring_onboarding.index', ['tab' => 'demo']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ request('tab') == 'demo' ? 'border-b-2 border-[#4ADE80] text-[#16A34A] font-bold' : 'text-gray-600 hover:text-[#1E40AF]' }} 
           text-sm md:text-base flex items-center space-x-2 transition-colors">
                <i class="fas fa-chalkboard-teacher"></i>
                <span class="hidden sm:inline">For Demo</span>
            </a>

            <!-- Onboarding -->
            <a href="{{ route('hiring_onboarding.index', ['tab' => 'onboarding']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ request('tab') == 'onboarding' ? 'border-b-2 border-[#4ADE80] text-[#16A34A] font-bold' : 'text-gray-600 hover:text-[#1E40AF]' }} 
           text-sm md:text-base flex items-center space-x-2 transition-colors">
                <i class="fas fa-briefcase"></i>
                <span class="hidden sm:inline">Onboarding</span>
            </a>

            <!-- Archive -->
            <a href="{{ route('hiring_onboarding.index', ['tab' => 'archive']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ request('tab') == 'archive' ? 'border-b-2 border-[#4ADE80] text-[#16A34A] font-bold' : 'text-gray-600 hover:text-[#1E40AF]' }} 
           text-sm md:text-base flex items-center space-x-2 transition-colors">
                <i class="fas fa-archive"></i>
                <span class="hidden sm:inline">Archive</span>
            </a>

        </nav>
    </div>

    <!-- Main Content -->
    <div>
        <div class="max-w-full mx-auto">
            <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                @if (request('tab', 'new') == 'new')
                    @include('hiring_onboarding.tabs.new-applicant')
                @elseif(request('tab') == 'demo')
                    @include('hiring_onboarding.tabs.for-demo')
                @elseif(request('tab') == 'onboarding')
                    @include('hiring_onboarding.tabs.onboarding')
                @elseif(request('tab') == 'archive')
                    @include('hiring_onboarding.tabs.archive')
                @endif
            </div>
        </div>
    </div>

    <!-- Include the edit modal globally for all tabs -->
    @include('hiring_onboarding.tabs.partials.modals.edit_mdl')

@endsection
