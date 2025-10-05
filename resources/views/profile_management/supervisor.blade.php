@extends('layouts.app')

@section('title', 'Supervisor Profile')

@section('content')
    @include('layouts.header', ['pageTitle' => 'Supervisor Profile'])

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- Success Message -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">
                            Success
                        </h3>
                        <div class="mt-1 text-sm text-green-700">
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Error Message -->
        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            Error
                        </h3>
                        <div class="mt-1 text-sm text-red-700">
                            <p>{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Profile Overview -->
        <div
            class="bg-gradient-to-r from-[#BCE6D4] to-[#9DC9FD] shadow-md rounded-xl p-4 sm:p-6 flex flex-col md:flex-row items-start md:items-center justify-between space-y-4 md:space-y-0">

            <!-- Profile Info -->
            <div class="flex items-center space-x-4 md:space-x-6">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face"
                        alt="Profile"
                        class="w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 rounded-full object-cover border-4 border-white shadow-md">

                    <!-- Camera Icon outside -->
                    <label class="absolute -bottom-2 -right-2 cursor-pointer" title="Change Profile Photo"
                        onclick="document.getElementById('profilePhotoInput').click()">
                        <input type="file" id="profilePhotoInput" class="hidden">
                        <div
                            class="flex items-center justify-center bg-[#0E335D] hover:bg-[#184679] text-white rounded-full shadow-md transform transition duration-200 hover:scale-105 w-8 h-8 sm:w-10 sm:h-10">
                            <i class="fas fa-camera text-white text-sm sm:text-base"></i>
                        </div>
                    </label>
                </div>


                <div class="text-left">
                    <h2 class="text-lg sm:text-xl md:text-2xl font-semibold text-[#0E335D]">
                        {{ $supervisor->full_name ?? 'Supervisor Name' }}</h2>
                    <p class="text-xs sm:text-sm md:text-base text-[#0E335D]">
                        {{ $supervisor->semail ?? 'supervisor@email.com' }}</p>
                </div>
            </div>

            <!-- Assigned Role -->
            <div class="mt-2 md:mt-0 text-left md:text-right">
                <p class="text-xs sm:text-sm md:text-sm font-medium text-[#0E335D]">Assigned Role</p>
                <h3 class="text-sm sm:text-base md:text-lg font-semibold text-[#0E335D]">
                    {{ $supervisor->assigned_account ? $supervisor->assigned_account . ' Supervisor' : 'Supervisor' }}
                </h3>
                @if ($supervisor->assigned_account)
                    <p class="text-xs text-gray-500 mt-1">Managing {{ $supervisor->assigned_account }} tutors</p>
                @endif
            </div>
        </div>

        <!-- Personal Information -->
        <div class="bg-white shadow-md rounded-xl p-4 sm:p-6 space-y-4">
            <h3 class="text-base sm:text-lg md:text-xl font-semibold text-[#0E335D] border-b-2 border-[#0E335D] pb-2">
                Personal Information</h3>

            <form method="POST" action="{{ route('supervisor.personal-info.update') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">First Name *</label>
                        <input type="text" name="sfname" required
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D]"
                            value="{{ old('sfname', $supervisor->sfname ?? 'John') }}">
                        @error('sfname')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">Last Name *</label>
                        <input type="text" name="slname" required
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D]"
                            value="{{ old('slname', $supervisor->slname ?? 'Doe') }}">
                        @error('slname')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">Birth Date</label>
                        <input type="date" name="birth_date"
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D]"
                            value="{{ old('birth_date', $supervisor->birth_date ?? '') }}">
                        @error('birth_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">Address</label>
                        <input type="text" name="saddress"
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D]"
                            value="{{ old('saddress', $supervisor->saddress ?? 'Manila, Philippines') }}">
                        @error('saddress')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">Contact Number</label>
                        <input type="text" name="sconNum"
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D]"
                            value="{{ old('sconNum', $supervisor->sconNum ?? '+639123456789') }}">
                        @error('sconNum')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">Email</label>
                        <input type="text" name="semail"
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D]"
                            value="{{ old('semail', $supervisor->semail ?? 'admin@ogsconnect.com') }}">
                        @error('semail')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">MS Teams Account</label>
                        <input type="email" name="steams"
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D]"
                            value="{{ old('steams', $supervisor->steams ?? '') }}" placeholder="Enter your MS Teams email">
                        @error('steams')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">Shift</label>
                        <select name="sshift"
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D]">
                            <option value="">Select Shift</option>
                            <option value="Day Shift"
                                {{ old('sshift', $supervisor->sshift ?? '') == 'Day Shift' ? 'selected' : '' }}>Day Shift
                            </option>
                            <option value="Night Shift"
                                {{ old('sshift', $supervisor->sshift ?? '') == 'Night Shift' ? 'selected' : '' }}>Night
                                Shift</option>
                            <option value="Evening Shift"
                                {{ old('sshift', $supervisor->sshift ?? '') == 'Evening Shift' ? 'selected' : '' }}>Evening
                                Shift</option>
                            <option value="Morning Shift"
                                {{ old('sshift', $supervisor->sshift ?? '') == 'Morning Shift' ? 'selected' : '' }}>Morning
                                Shift</option>
                            <option value="Flexible"
                                {{ old('sshift', $supervisor->sshift ?? '') == 'Flexible' ? 'selected' : '' }}>Flexible
                            </option>
                        </select>
                        @error('sshift')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">System ID</label>
                        <div class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm bg-gray-50">
                            {{ $supervisor->supID ?? 'Not assigned' }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500">System ID is automatically assigned and cannot be changed.
                        </p>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                        class="mt-3 px-4 py-2 sm:px-6 sm:py-2 bg-[#0E335D] text-white text-xs sm:text-sm rounded-full hover:bg-gray-800 transform transition duration-200 hover:scale-105 w-full sm:w-auto">
                        Update Personal Information
                    </button>
                </div>
            </form>
        </div>

        <!-- Payment Information -->
        <div class="bg-white shadow-md rounded-xl p-4 sm:p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base sm:text-lg md:text-xl font-semibold text-[#0E335D] border-b-2 border-[#0E335D] pb-2">
                    Payment Information</h3>
                <button id="toggle-payment-form" type="button"
                    class="px-4 py-2 bg-[#0E335D] text-white text-xs sm:text-sm rounded-full hover:bg-gray-800 transform transition duration-200 hover:scale-105">
                    <span id="toggle-text">Edit Payment Info</span>
                </button>
            </div>

            @php
                $paymentInfo = $supervisor->paymentInformation ?? null;
            @endphp

            <!-- Payment Information Display -->
            <div id="payment-display" class="space-y-4">
                @if ($paymentInfo)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs sm:text-sm text-[#0E335D]">Payment Method</label>
                            <div class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm bg-gray-50">
                                {{ strtoupper($paymentInfo->payment_method ?? 'N/A') }}
                            </div>
                        </div>
                        <div>
                            <label class="text-xs sm:text-sm text-[#0E335D]">
                                @switch($paymentInfo->payment_method)
                                    @case('gcash')
                                        GCash Number
                                    @break

                                    @case('paymaya')
                                        PayMaya Number
                                    @break

                                    @case('paypal')
                                        PayPal Email
                                    @break

                                    @case('bank_transfer')
                                        Account Number
                                    @break

                                    @default
                                        Account Number
                                @endswitch
                            </label>
                            <div class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm bg-gray-50">
                                @switch($paymentInfo->payment_method)
                                    @case('gcash')
                                        {{ $paymentInfo->gcash_number ?? 'N/A' }}
                                    @break

                                    @case('paymaya')
                                        {{ $paymentInfo->paymaya_number ?? 'N/A' }}
                                    @break

                                    @case('paypal')
                                        {{ $paymentInfo->paypal_email ?? 'N/A' }}
                                    @break

                                    @case('bank_transfer')
                                        {{ $paymentInfo->account_number ?? 'N/A' }}
                                    @break

                                    @default
                                        {{ $paymentInfo->account_number ?? 'N/A' }}
                                @endswitch
                            </div>
                        </div>
                        @if ($paymentInfo->payment_method === 'bank_transfer')
                            <div>
                                <label class="text-xs sm:text-sm text-[#0E335D]">Bank Name</label>
                                <div class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm bg-gray-50">
                                    {{ $paymentInfo->bank_name ?? 'N/A' }}
                                </div>
                            </div>
                        @endif
                        @if ($paymentInfo->payment_method === 'bank_transfer' || $paymentInfo->account_name)
                            <div>
                                <label class="text-xs sm:text-sm text-[#0E335D]">Account Name</label>
                                <div class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm bg-gray-50">
                                    {{ $paymentInfo->account_name ?? 'N/A' }}
                                </div>
                            </div>
                        @endif
                        @if ($paymentInfo->notes)
                            <div class="md:col-span-2">
                                <label class="text-xs sm:text-sm text-[#0E335D]">Notes</label>
                                <div class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm bg-gray-50">
                                    {{ $paymentInfo->notes }}
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-credit-card text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600 mb-4">No payment information set up yet.</p>
                    </div>
                @endif
            </div>

            <!-- Payment Information Form -->
            <div id="payment-form" class="space-y-4" style="display: none;">
                <form id="payment-information-form" method="POST" action="{{ route('payment-information.store') }}">
                    @csrf

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-[#0E335D] mb-2">Payment Method *</label>
                        <select name="payment_method" id="payment_method" required
                            class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent">
                            <option value="gcash"
                                {{ old('payment_method', $paymentInfo->payment_method ?? 'gcash') == 'gcash' ? 'selected' : '' }}>
                                GCash</option>
                            <option value="paypal"
                                {{ old('payment_method', $paymentInfo->payment_method ?? '') == 'paypal' ? 'selected' : '' }}>
                                PayPal</option>
                            <option value="paymaya"
                                {{ old('payment_method', $paymentInfo->payment_method ?? '') == 'paymaya' ? 'selected' : '' }}>
                                PayMaya</option>
                            <option value="bank_transfer"
                                {{ old('payment_method', $paymentInfo->payment_method ?? '') == 'bank_transfer' ? 'selected' : '' }}>
                                Bank Transfer</option>
                            <option value="cash"
                                {{ old('payment_method', $paymentInfo->payment_method ?? '') == 'cash' ? 'selected' : '' }}>
                                Cash</option>
                        </select>
                        @error('payment_method')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bank Transfer Details -->
                    <div id="bank_details" class="space-y-4" style="display: none;">
                        <h4 class="text-sm font-medium text-[#0E335D]">Bank Transfer Details</h4>

                        <div>
                            <label class="block text-sm font-medium text-[#0E335D] mb-2">Bank Name</label>
                            <input type="text" name="bank_name"
                                value="{{ old('bank_name', $paymentInfo->bank_name ?? '') }}"
                                class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent">
                            @error('bank_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#0E335D] mb-2">Account Number</label>
                            <input type="text" name="account_number"
                                value="{{ old('account_number', $paymentInfo->account_number ?? '') }}"
                                class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent">
                            @error('account_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#0E335D] mb-2">Account Name</label>
                            <input type="text" name="account_name"
                                value="{{ old('account_name', $paymentInfo->account_name ?? '') }}"
                                class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent">
                            @error('account_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- PayPal Details -->
                    <div id="paypal_details" class="space-y-4" style="display: none;">
                        <h4 class="text-sm font-medium text-[#0E335D]">PayPal Details</h4>

                        <div>
                            <label class="block text-sm font-medium text-[#0E335D] mb-2">PayPal Email</label>
                            <input type="email" name="paypal_email"
                                value="{{ old('paypal_email', $paymentInfo->paypal_email ?? '') }}"
                                class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent">
                            @error('paypal_email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- GCash Details -->
                    <div id="gcash_details" class="space-y-4" style="display: none;">
                        <h4 class="text-sm font-medium text-[#0E335D]">GCash Details</h4>

                        <div>
                            <label class="block text-sm font-medium text-[#0E335D] mb-2">GCash Number</label>
                            <input type="text" name="gcash_number"
                                value="{{ old('gcash_number', $paymentInfo->gcash_number ?? '') }}"
                                class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent">
                            @error('gcash_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- PayMaya Details -->
                    <div id="paymaya_details" class="space-y-4" style="display: none;">
                        <h4 class="text-sm font-medium text-[#0E335D]">PayMaya Details</h4>

                        <div>
                            <label class="block text-sm font-medium text-[#0E335D] mb-2">PayMaya Number</label>
                            <input type="text" name="paymaya_number"
                                value="{{ old('paymaya_number', $paymentInfo->paymaya_number ?? '') }}"
                                class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent">
                            @error('paymaya_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-[#0E335D] mb-2">Notes</label>
                        <textarea name="notes" rows="3"
                            class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent"
                            placeholder="Any additional notes about payment...">{{ old('notes', $paymentInfo->notes ?? '') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-payment-form"
                            class="px-4 py-2 bg-gray-500 text-white text-sm rounded-full hover:bg-gray-600 transform transition duration-200">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-3 bg-[#0E335D] text-white text-sm rounded-full hover:bg-gray-800 transform transition duration-200 hover:scale-105">
                            Update Payment Information
                        </button>
                    </div>
                </form>
            </div>

            <!-- Success/Error Messages -->
            <div id="payment-messages" class="hidden"></div>
        </div>

        <!-- Security Section -->
        <div class="bg-white shadow-md rounded-xl p-4 sm:p-6 space-y-4">
            <h3 class="text-base sm:text-lg md:text-xl font-semibold text-[#0E335D] border-b-2 border-[#0E335D] pb-2">
                Security / Account Details</h3>

            <!-- System ID - Updated -->
            <div class="space-y-3">
                <h4 class="text-sm sm:text-base font-medium text-[#0E335D]">System ID</h4>
                <!-- Force refresh -->
                <div class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm bg-gray-50">
                    {{ $supervisor->supID ?? 'Not set' }}
                </div>
                <p class="text-xs text-gray-500">System ID is automatically assigned and cannot be changed.</p>
            </div>

            <!-- Change Password -->
            <div class="space-y-3">
                <h4 class="text-sm sm:text-base font-medium text-[#0E335D]">Change Password</h4>
                <form method="POST" action="{{ route('supervisor.password.update') }}" class="space-y-4">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs sm:text-sm text-[#0E335D] mb-2">Current Password *</label>
                            <input type="password" name="current_password" required
                                class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D]"
                                placeholder="Enter your current password">
                            @error('current_password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm text-[#0E335D] mb-2">New Password *</label>
                            <input type="password" name="new_password" required
                                class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D]"
                                placeholder="Enter your new password">
                            @error('new_password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm text-[#0E335D] mb-2">Confirm New Password *</label>
                            <input type="password" name="new_password_confirmation" required
                                class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D]"
                                placeholder="Confirm your new password">
                            @error('new_password_confirmation')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-4 py-2 sm:px-6 sm:py-2 bg-[#0E335D] text-white text-xs sm:text-sm rounded-full hover:bg-gray-800 transform transition duration-200 hover:scale-105 w-full sm:w-auto">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Authentication Questions -->
            <div class="space-y-3">
                <h4 class="text-sm sm:text-base font-medium text-[#0E335D]">Authentication Questions</h4>
                <p class="text-xs sm:text-sm text-gray-600">
                    These questions are used to verify your identity when you need to recover your account or reset your
                    password.
                    Please choose questions and answers that only you would know.
                </p>

                <form method="POST" action="{{ route('supervisor.security-questions.update') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs sm:text-sm text-[#0E335D] mb-2">Security Question 1 *</label>
                            <select name="security_question1" required
                                class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent">
                                <option value="">Select a security question</option>
                                <option value="What is your mother's maiden name?"
                                    {{ old('security_question1', $supervisor->securityQuestions->first()->question ?? '') == "What is your mother's maiden name?" ? 'selected' : '' }}>
                                    What is your mother's maiden name?</option>
                                <option value="What was the name of your first pet?"
                                    {{ old('security_question1', $supervisor->securityQuestions->first()->question ?? '') == 'What was the name of your first pet?' ? 'selected' : '' }}>
                                    What was the name of your first pet?</option>
                                <option value="What city were you born in?"
                                    {{ old('security_question1', $supervisor->securityQuestions->first()->question ?? '') == 'What city were you born in?' ? 'selected' : '' }}>
                                    What city were you born in?</option>
                                <option value="What was your favorite subject in school?"
                                    {{ old('security_question1', $supervisor->securityQuestions->first()->question ?? '') == 'What was your favorite subject in school?' ? 'selected' : '' }}>
                                    What was your favorite subject in school?</option>
                                <option value="What is the name of your childhood best friend?"
                                    {{ old('security_question1', $supervisor->securityQuestions->first()->question ?? '') == 'What is the name of your childhood best friend?' ? 'selected' : '' }}>
                                    What is the name of your childhood best friend?</option>
                                <option value="What was your first car?"
                                    {{ old('security_question1', $supervisor->securityQuestions->first()->question ?? '') == 'What was your first car?' ? 'selected' : '' }}>
                                    What was your first car?</option>
                                <option value="What is your favorite color?"
                                    {{ old('security_question1', $supervisor->securityQuestions->first()->question ?? '') == 'What is your favorite color?' ? 'selected' : '' }}>
                                    What is your favorite color?</option>
                                <option value="What was the name of your elementary school?"
                                    {{ old('security_question1', $supervisor->securityQuestions->first()->question ?? '') == 'What was the name of your elementary school?' ? 'selected' : '' }}>
                                    What was the name of your elementary school?</option>
                            </select>
                            @error('security_question1')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm text-[#0E335D] mb-2">Answer 1 *</label>
                            <input type="text" name="security_answer1" required
                                class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent"
                                placeholder="Enter your answer" value="{{ old('security_answer1', '') }}">
                            @error('security_answer1')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm text-[#0E335D] mb-2">Security Question 2 *</label>
                            <select name="security_question2" required
                                class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent">
                                <option value="">Select a security question</option>
                                <option value="What is your mother's maiden name?"
                                    {{ old('security_question2', $supervisor->securityQuestions->skip(1)->first()->question ?? '') == "What is your mother's maiden name?" ? 'selected' : '' }}>
                                    What is your mother's maiden name?</option>
                                <option value="What was the name of your first pet?"
                                    {{ old('security_question2', $supervisor->securityQuestions->skip(1)->first()->question ?? '') == 'What was the name of your first pet?' ? 'selected' : '' }}>
                                    What was the name of your first pet?</option>
                                <option value="What city were you born in?"
                                    {{ old('security_question2', $supervisor->securityQuestions->skip(1)->first()->question ?? '') == 'What city were you born in?' ? 'selected' : '' }}>
                                    What city were you born in?</option>
                                <option value="What was your favorite subject in school?"
                                    {{ old('security_question2', $supervisor->securityQuestions->skip(1)->first()->question ?? '') == 'What was your favorite subject in school?' ? 'selected' : '' }}>
                                    What was your favorite subject in school?</option>
                                <option value="What is the name of your childhood best friend?"
                                    {{ old('security_question2', $supervisor->securityQuestions->skip(1)->first()->question ?? '') == 'What is the name of your childhood best friend?' ? 'selected' : '' }}>
                                    What is the name of your childhood best friend?</option>
                                <option value="What was your first car?"
                                    {{ old('security_question2', $supervisor->securityQuestions->skip(1)->first()->question ?? '') == 'What was your first car?' ? 'selected' : '' }}>
                                    What was your first car?</option>
                                <option value="What is your favorite color?"
                                    {{ old('security_question2', $supervisor->securityQuestions->skip(1)->first()->question ?? '') == 'What is your favorite color?' ? 'selected' : '' }}>
                                    What is your favorite color?</option>
                                <option value="What was the name of your elementary school?"
                                    {{ old('security_question2', $supervisor->securityQuestions->skip(1)->first()->question ?? '') == 'What was the name of your elementary school?' ? 'selected' : '' }}>
                                    What was the name of your elementary school?</option>
                            </select>
                            @error('security_question2')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm text-[#0E335D] mb-2">Answer 2 *</label>
                            <input type="text" name="security_answer2" required
                                class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent"
                                placeholder="Enter your answer" value="{{ old('security_answer2', '') }}">
                            @error('security_answer2')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-4 py-2 sm:px-6 sm:py-2 bg-[#0E335D] text-white text-xs sm:text-sm rounded-full hover:bg-gray-800 transform transition duration-200 hover:scale-105 w-full sm:w-auto">
                            Update Security Questions
                        </button>
                    </div>
                </form>
            </div>
        </div>



    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide success/error messages after 5 seconds
            const alerts = document.querySelectorAll('.bg-green-50, .bg-red-50');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease-out';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, 5000);
            });

            // Payment Information Form Handling
            const toggleButton = document.getElementById('toggle-payment-form');
            const toggleText = document.getElementById('toggle-text');
            const paymentDisplay = document.getElementById('payment-display');
            const paymentForm = document.getElementById('payment-form');
            const cancelButton = document.getElementById('cancel-payment-form');
            const paymentFormElement = document.getElementById('payment-information-form');
            const paymentMessages = document.getElementById('payment-messages');

            // Toggle form visibility
            toggleButton.addEventListener('click', function() {
                if (paymentForm.style.display === 'none') {
                    paymentForm.style.display = 'block';
                    paymentDisplay.style.display = 'none';
                    toggleText.textContent = 'View Payment Info';
                    togglePaymentDetails(); // Show correct payment details
                } else {
                    paymentForm.style.display = 'none';
                    paymentDisplay.style.display = 'block';
                    toggleText.textContent = 'Edit Payment Info';
                }
            });

            // Cancel form
            cancelButton.addEventListener('click', function() {
                paymentForm.style.display = 'none';
                paymentDisplay.style.display = 'block';
                toggleText.textContent = 'Edit Payment Info';
                clearMessages();
            });

            // Handle form submission via AJAX
            paymentFormElement.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;

                // Show loading state
                submitButton.textContent = 'Updating...';
                submitButton.disabled = true;

                fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage('success', data.message);
                            // Update the display with new data
                            updatePaymentDisplay(data.payment_info);
                            // Hide form and show display
                            paymentForm.style.display = 'none';
                            paymentDisplay.style.display = 'block';
                            toggleText.textContent = 'Edit Payment Info';
                        } else {
                            showMessage('error', data.message ||
                                'An error occurred while updating payment information.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('error', 'An error occurred while updating payment information.');
                    })
                    .finally(() => {
                        // Reset button state
                        submitButton.textContent = originalText;
                        submitButton.disabled = false;
                    });
            });

            // Payment method change handler
            const paymentMethodSelect = document.getElementById('payment_method');
            paymentMethodSelect.addEventListener('change', togglePaymentDetails);

            function togglePaymentDetails() {
                const bankDetails = document.getElementById('bank_details');
                const paypalDetails = document.getElementById('paypal_details');
                const gcashDetails = document.getElementById('gcash_details');
                const paymayaDetails = document.getElementById('paymaya_details');

                // Hide all details first
                bankDetails.style.display = 'none';
                paypalDetails.style.display = 'none';
                gcashDetails.style.display = 'none';
                paymayaDetails.style.display = 'none';

                // Show relevant details based on selection
                const selectedMethod = paymentMethodSelect.value;
                switch (selectedMethod) {
                    case 'bank_transfer':
                        bankDetails.style.display = 'block';
                        break;
                    case 'paypal':
                        paypalDetails.style.display = 'block';
                        break;
                    case 'gcash':
                        gcashDetails.style.display = 'block';
                        break;
                    case 'paymaya':
                        paymayaDetails.style.display = 'block';
                        break;
                }
            }

            function showMessage(type, message) {
                clearMessages();
                const messageDiv = document.createElement('div');
                messageDiv.className = `p-4 rounded-lg mb-4 ${
                    type === 'success' 
                        ? 'bg-green-50 border border-green-200 text-green-800' 
                        : 'bg-red-50 border border-red-200 text-red-800'
                }`;
                messageDiv.innerHTML = `
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 ${type === 'success' ? 'text-green-400' : 'text-red-400'} mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'}"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">${message}</p>
                        </div>
                    </div>
                `;
                paymentMessages.appendChild(messageDiv);
                paymentMessages.classList.remove('hidden');

                // Auto-hide after 5 seconds
                setTimeout(() => {
                    messageDiv.style.transition = 'opacity 0.5s ease-out';
                    messageDiv.style.opacity = '0';
                    setTimeout(() => {
                        messageDiv.remove();
                        if (paymentMessages.children.length === 0) {
                            paymentMessages.classList.add('hidden');
                        }
                    }, 500);
                }, 5000);
            }

            function clearMessages() {
                paymentMessages.innerHTML = '';
                paymentMessages.classList.add('hidden');
            }

            function updatePaymentDisplay(paymentInfo) {
                // This function would update the display with the new payment information
                // For now, we'll just reload the page section or update the display manually
                // In a real implementation, you might want to update the DOM elements directly
                location.reload(); // Simple solution - reload the page to show updated data
            }

            // Initialize payment details display
            togglePaymentDetails();
        });
    </script>
@endsection
