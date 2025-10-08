@extends('layouts.app')

@section('title', 'Tutor Details - OGS Connect')

@section('content')
    <!-- Header -->
    @include('layouts.header', ['pageTitle' => 'Tutor Details'])

    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('employees.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Employees
        </a>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <!-- Header Section -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">{{ $tutor->full_name ?? 'N/A' }}</h1>
                        <p class="text-blue-100 mt-2">{{ $tutor->email ?? 'N/A' }}</p>
                        <div class="flex items-center mt-4 space-x-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                {{ $tutor->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($tutor->status) }}
                            </span>
                            <span class="text-blue-100">Tutor ID: {{ $tutor->tutorID ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold">{{ $tutor->accounts->count() }}</div>
                        <div class="text-blue-100">Active Accounts</div>
                    </div>
                </div>
            </div>

            <!-- Content Sections -->
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Personal Information -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-user mr-2 text-blue-600"></i>
                            Personal Information
                        </h2>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Full Name:</span>
                                <span class="font-medium">{{ $tutor->full_name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="font-medium">{{ $tutor->email ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Phone Number:</span>
                                <span class="font-medium">{{ $tutor->phone_number ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date of Birth:</span>
                                <span class="font-medium">{{ $tutor->date_of_birth ? \Carbon\Carbon::parse($tutor->date_of_birth)->format('M j, Y') : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date Hired:</span>
                                <span class="font-medium">{{ $tutor->created_at ? $tutor->created_at->format('M j, Y') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-credit-card mr-2 text-green-600"></i>
                            Payment Information
                        </h2>
                        @if($tutor->paymentInformation)
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Payment Method:</span>
                                    <span class="font-medium">{{ $tutor->paymentInformation->payment_method_uppercase ?? 'N/A' }}</span>
                                </div>
                                @if($tutor->paymentInformation->payment_method === 'bank')
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Bank Name:</span>
                                        <span class="font-medium">{{ $tutor->paymentInformation->bank_name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Account Number:</span>
                                        <span class="font-medium">{{ $tutor->paymentInformation->account_number ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Account Name:</span>
                                        <span class="font-medium">{{ $tutor->paymentInformation->account_name ?? 'N/A' }}</span>
                                    </div>
                                @elseif($tutor->paymentInformation->payment_method === 'gcash')
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">GCash Number:</span>
                                        <span class="font-medium">{{ $tutor->paymentInformation->gcash_number ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">GCash Name:</span>
                                        <span class="font-medium">{{ $tutor->paymentInformation->gcash_name ?? 'N/A' }}</span>
                                    </div>
                                @elseif($tutor->paymentInformation->payment_method === 'paypal')
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">PayPal Email:</span>
                                        <span class="font-medium">{{ $tutor->paymentInformation->paypal_email ?? 'N/A' }}</span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-gray-500 italic">No payment information available</p>
                        @endif
                    </div>
                </div>

                <!-- Account Information -->
                <div class="mt-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-briefcase mr-2 text-purple-600"></i>
                        Account Information
                    </h2>
                    @if($tutor->accounts->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($tutor->accounts as $account)
                                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-semibold text-gray-800">{{ $account->account_name }}</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $account->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($account->status) }}
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        @if($account->account_name === 'GLS')
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">GLS ID:</span>
                                                <span class="font-medium">{{ $account->gls_id ?? 'N/A' }}</span>
                                            </div>
                                        @endif
                                        
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Available Time:</span>
                                            <span class="font-medium">{{ $account->formatted_available_time ?? 'N/A' }}</span>
                                        </div>
                                        
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Available Days:</span>
                                            <span class="font-medium">{{ $account->formatted_available_days ?? 'N/A' }}</span>
                                        </div>
                                        
                                        @if($account->account_name === 'Talk915')
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">MS Teams ID:</span>
                                                <span class="font-medium">{{ $account->ms_teams_id ?? 'N/A' }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 italic">No account information available</p>
                    @endif
                </div>

                <!-- Additional Details -->
                @if($tutor->tutorDetails)
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-orange-600"></i>
                            Additional Details
                        </h2>
                        <div class="bg-gray-50 rounded-lg p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Address:</span>
                                        <span class="font-medium">{{ $tutor->tutorDetails->address ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Educational Attainment:</span>
                                        <span class="font-medium">{{ $tutor->tutorDetails->educational_attainment ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">ESL Teaching Experience:</span>
                                        <span class="font-medium">{{ $tutor->tutorDetails->esl_teaching_experience ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Work Setup:</span>
                                        <span class="font-medium">{{ $tutor->tutorDetails->work_setup ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">First Day of Teaching:</span>
                                        <span class="font-medium">{{ $tutor->tutorDetails->first_day_of_teaching ? \Carbon\Carbon::parse($tutor->tutorDetails->first_day_of_teaching)->format('M j, Y') : 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
