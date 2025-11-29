@extends('layouts.app')
@section('title', 'OGS Connect/Payroll')

@section('content')
    @include('layouts.header', ['pageTitle' => 'Payroll'])

    <div class="bg-white border-b border-gray-200 px-4 md:px-6 rounded-xl shadow-sm mb-6">
        <nav class="flex overflow-x-auto md:space-x-8 no-scrollbar">
            <a href="{{ route('payroll.index', ['tab' => 'payroll']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
           {{ request('tab', 'payroll') == 'payroll' ? 'border-b-2 border-[#0E335D] text-[#0E335D]' : 'text-[#0E335D] hover:text-[#0E335D]/70' }} 
           font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-user-clock"></i>
                <span class="hidden sm:inline">Employee Payroll</span>
            </a>
        </nav>
    </div>

    <div>
        <div class="max-w-full mx-auto">
            <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                @if (request('tab', 'payroll') == 'payroll')
                    <div class="mb-4">
                        <form method="GET" action="{{ route('payroll.index') }}">
                            <div class="flex items-center space-x-2">
                                <div class="flex-1">
                                    <input type="search" name="search" value="{{ request('search') }}"
                                        class="w-full rounded border-gray-200 shadow-sm px-3 py-2" placeholder="Search name, tutor id, email...">
                                </div>
                                <div>
                                    <button type="submit" class="px-4 py-2 bg-[#0E335D] text-white rounded">Search</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    @include('payroll.partials.employee-payroll')
                @endif
        </div>
    </div>
@endsection
