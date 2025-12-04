@extends('layouts.app')

@section('title', 'OGS Connect - Applicant Details')

@section('content')
    <!-- Header -->
    @include('layouts.header', ['pageTitle' => 'Hiring & Onboarding'])

    <!-- Main Content -->
    <div class="max-w-full mx-auto">
        <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
            @include('hiring_onboarding.tabs.partials.applicant-details-unedited')
        </div>
    </div>

    <!-- Include Modals -->
    @include('hiring_onboarding.tabs.partials.modals.edit_mdl')

@endsection
