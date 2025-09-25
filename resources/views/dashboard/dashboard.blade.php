@extends('layouts.app')

@section('title', 'OGS Connect')

@section('content')
    <!-- Header -->
    @include('layouts.header', ['pageTitle' => 'Dashboard'])
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
