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
            <div class="flex items-center justify-between">
            <h3 class="text-base sm:text-lg md:text-xl font-semibold text-[#0E335D] border-b-2 border-[#0E335D] pb-2">
                Personal Information</h3>
                <button id="editPersonalInfoBtn" type="button"
                    class="px-4 py-2 bg-[#0E335D] text-white text-xs sm:text-sm rounded-full hover:bg-gray-800 transform transition duration-200 hover:scale-105">
                    <span id="personalInfoToggleText">Edit Information</span>
                </button>
            </div>

            <div id="personalInfoMessage" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

            <form method="POST" action="{{ route('supervisor.personal-info.update') }}" id="personalInfoForm" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">First Name *</label>
                        <input type="text" name="sfname" id="sfname" required readonly
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D] bg-gray-50"
                            value="{{ old('sfname', $supervisor->sfname ?? 'John') }}">
                        @error('sfname')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">Last Name *</label>
                        <input type="text" name="slname" id="slname" required readonly
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D] bg-gray-50"
                            value="{{ old('slname', $supervisor->slname ?? 'Doe') }}">
                        @error('slname')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">Birth Date</label>
                        <input type="date" name="birth_date" id="birth_date" readonly
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D] bg-gray-50"
                            value="{{ old('birth_date', $supervisor->birth_date ?? '') }}">
                        @error('birth_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">Address</label>
                        <input type="text" name="saddress" id="saddress" readonly
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D] bg-gray-50"
                            value="{{ old('saddress', $supervisor->saddress ?? 'Manila, Philippines') }}">
                        @error('saddress')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">Contact Number</label>
                        <input type="text" name="sconNum" id="sconNum" readonly
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D] bg-gray-50"
                            value="{{ old('sconNum', $supervisor->sconNum ?? '+639123456789') }}">
                        @error('sconNum')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">Email</label>
                        <input type="text" name="semail" id="semail" readonly
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D] bg-gray-50"
                            value="{{ old('semail', $supervisor->semail ?? 'admin@ogsconnect.com') }}">
                        @error('semail')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">MS Teams Account</label>
                        <input type="email" name="steams" id="steams" readonly
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D] bg-gray-50"
                            value="{{ old('steams', $supervisor->steams ?? '') }}" placeholder="Enter your MS Teams email">
                        @error('steams')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-xs sm:text-sm text-[#0E335D]">Shift</label>
                        <select name="sshift" id="sshift" readonly
                            class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D] bg-gray-50">
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
                
                <!-- Form Actions -->
                <div class="px-4 py-3">
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancelPersonalInfoBtn" class="hidden px-4 py-2 bg-gray-500 text-white text-sm rounded-full hover:bg-gray-600 transform transition duration-200">
                            Cancel
                        </button>
                        <button type="button" id="savePersonalInfoBtn" class="hidden px-6 py-3 bg-[#0E335D] text-white text-sm rounded-full hover:bg-gray-800 transform transition duration-200 hover:scale-105">
                            Save Changes
                    </button>
                    </div>
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
                    <div class="pb-3">
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

                        <div class="pb-3">
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
                    <div class="pb-3">
                        <label class="block text-sm font-medium text-[#0E335D] mb-2">Notes</label>
                        <textarea name="notes" rows="3"
                            class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-transparent"
                            placeholder="Any additional notes about payment...">{{ old('notes', $paymentInfo->notes ?? '') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    <div class="px-4 py-3">
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-payment-form"
                            class="px-4 py-2 bg-gray-500 text-white text-sm rounded-full hover:bg-gray-600 transform transition duration-200">
                            Cancel
                        </button>
                        <button type="button" id="update-payment-btn"
                            class="px-6 py-3 bg-[#0E335D] text-white text-sm rounded-full hover:bg-gray-800 transform transition duration-200 hover:scale-105">
                            Update Payment Information
                        </button>
                        </div>
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
                <form method="POST" action="{{ route('supervisor.password.update') }}" id="passwordForm" class="space-y-4">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs sm:text-sm text-[#0E335D] mb-2">Current Password *</label>
                            <div class="relative">
                                <input type="password" name="current_password" id="currentPassword" required
                                    class="w-full border border-gray-300 rounded-lg p-2 pr-10 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D]"
                                placeholder="Enter your current password">
                                <button type="button" id="toggleCurrentPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye" id="currentPasswordIcon"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm text-[#0E335D] mb-2">New Password *</label>
                            <div class="relative">
                                <input type="password" name="new_password" id="newPassword" required
                                    class="w-full border border-gray-300 rounded-lg p-2 pr-10 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D]"
                                placeholder="Enter your new password">
                                <button type="button" id="toggleNewPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye" id="newPasswordIcon"></i>
                                </button>
                            </div>
                            @error('new_password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm text-[#0E335D] mb-2">Confirm New Password *</label>
                            <div class="relative">
                                <input type="password" name="new_password_confirmation" id="confirmPassword" required
                                    class="w-full border border-gray-300 rounded-lg p-2 pr-10 text-xs sm:text-sm focus:ring-2 focus:ring-[#0E335D]"
                                placeholder="Confirm your new password">
                                <button type="button" id="toggleConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                                </button>
                            </div>
                            @error('new_password_confirmation')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <!-- Password Match Indicator -->
                            <div id="passwordMatchIndicator" class="mt-2 text-sm hidden">
                                <div class="flex items-center">
                                    <i id="passwordMatchIcon" class="mr-2"></i>
                                    <span id="passwordMatchText"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" id="updatePasswordBtn"
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

                <form method="POST" action="{{ route('supervisor.security-questions.update') }}" id="securityQuestionsForm" class="space-y-4">
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
                        <button type="button" id="updateSecurityQuestionsBtn"
                            class="px-4 py-2 sm:px-6 sm:py-2 bg-[#0E335D] text-white text-xs sm:text-sm rounded-full hover:bg-gray-800 transform transition duration-200 hover:scale-105 w-full sm:w-auto">
                            Update Security Questions
                        </button>
                    </div>
                </form>
            </div>
        </div>



    </div>

    <script src="{{ asset('js/supervisor-profile.js') }}"></script>
@endsection
