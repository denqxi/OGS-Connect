<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>OGS Connect - Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'mint': '#65DB7F',
            'mint-light': '#7EFFF5',
            'teal': '#26A69A',
            'ogs-navy': '#0A2A4A'   
          }
        }
      }
    }
  </script>
</head>
<body class="min-h-screen bg-gradient-to-b from-emerald-300 to-[#7CA6D7]">

  <!-- Home page button fixed top-left -->
  <div class="fixed top-4 left-4 z-50">
    <a href="{{ url('/') }}"
      class="flex items-center text-ogs-navy font-bold hover:text-slate-700 transition-colors bg-white/80 backdrop-blur-sm px-3 py-2 rounded-lg shadow-lg">
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 19l-7-7 7-7"></path>
      </svg>
      <span class="text-sm">Home</span>
    </a>
  </div>

  <!-- Main Container -->
  <div class="min-h-screen flex flex-col lg:flex-row">
    
    <!-- Left Section - Welcome Content -->
    <div class="flex-1 flex flex-col items-center justify-center px-4 py-8 lg:px-12 lg:pt-28 relative">
      <!-- Welcome Text -->
      <div class="text-center lg:text-left mb-8 lg:mb-12">
        <div class="text-blue-950 text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight">
          WELCOME TO<br/>OGS CONNECT
        </div>
        <div class="text-green-900 text-lg sm:text-xl md:text-2xl font-medium mt-4">
          A Centralized Management System
        </div>
      </div>
      
      <!-- Image -->
      <div class="w-full max-w-md lg:max-w-lg xl:max-w-xl">
        <img class="w-full h-auto" src="{{ asset('images/login-image.png') }}" alt="OGS Connect" />
      </div>
    </div>

    <!-- Right Section - Login Form -->
    <div class="flex-1 flex items-center justify-center px-4 py-8 lg:px-8 relative">
      <!-- Login Card -->
      <div class="w-full max-w-md lg:max-w-lg xl:max-w-xl bg-white/95 backdrop-blur-md rounded-2xl lg:rounded-3xl shadow-2xl p-6 sm:p-8 lg:p-12 xl:p-16 lg:relative lg:z-10 -mt-80 sm:-mt-82 md:-mt-86 lg:mt-0">
        <!-- Logo and Header -->
        <div class="w-full flex flex-col items-center mb-8 lg:mb-12">
          <div class="text-center flex flex-col sm:flex-row items-center gap-4 mb-6">
            <img class="w-16 h-16 sm:w-20 sm:h-20" src="{{ asset('images/logo.png') }}" alt="Logo" />
            <div class="text-center sm:text-left">
              <span class="text-blue-950 text-xl sm:text-2xl font-bold block">
                OUTSOURCING
              </span>
              <span class="text-blue-950 text-sm sm:text-base font-bold block">
                GLOBAL SOLUTIONS
              </span>
            </div>
          </div>
        </div>

        <!-- Title and Description -->
        <div class="w-full text-center lg:text-left mb-6 lg:mb-8">
          <h1 class="text-blue-950 text-2xl sm:text-3xl lg:text-4xl font-bold mb-3" id="mainTitle">
            Log in to your Account
          </h1>
          <p class="text-neutral-800 text-sm sm:text-base font-medium leading-relaxed" id="mainDescription">
            Provide your login details below to securely access your OGS Connect account.
          </p>
        </div>

        <!-- Dynamic Form Container -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4 sm:space-y-5">
          @csrf
          
          <!-- Error Messages -->
          @if (isset($errors) && $errors->any())
            <div id="loginErrorAlert" class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-red-800">
                    Login Failed
                  </h3>
                  <div class="mt-1 text-sm text-red-700">
                    <p>{{ $errors->first('login_id') }}</p>
                    @if($errors->has('remaining_attempts'))
                      @if($errors->has('attempt_message') && !empty($errors->first('attempt_message')))
                        <p class="mt-2 font-medium">
                          <i class="fas fa-exclamation-triangle mr-1"></i>
                          {{ $errors->first('attempt_message') }}
                        </p>
                      @elseif(!$errors->has('attempt_message'))
                        <p class="mt-2 font-medium">
                          <i class="fas fa-exclamation-triangle mr-1"></i>
                          Remaining attempts: <span class="font-bold">{{ $errors->first('remaining_attempts') }}</span>
                        </p>
                      @endif
                    @elseif($errors->has('throttled'))
                      <p class="mt-2 font-medium">
                        <i class="fas fa-lock mr-1"></i>
                        Account temporarily locked due to too many failed attempts.
                      </p>
                      <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-sm text-blue-800">
                        💡 Having trouble logging in? Try using the "Forgot Password?" button below to reset your password.
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          @endif
          
          <!-- Login Mode Fields -->
          <div id="loginFields" class="space-y-4">
          <!-- Unified ID/Email Field -->
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
              <svg class="h-5 w-5 sm:h-6 sm:w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 8l7.89 7.89a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
            </div>
            <input type="text" name="login_id" id="login_id" placeholder="Supervisor/Tutor ID or Email" required
                    class="w-full pl-10 sm:pl-12 pr-4 py-3 sm:py-4 lg:py-5 bg-white border border-stone-300 rounded-lg sm:rounded-xl focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent transition-all duration-200 text-sm sm:text-base @if(isset($errors) && $errors->has('login_id')) border-red-500 @endif"
                    value="{{ old('login_id') }}"
                    pattern="^(OGS-[ST]\d{4}|[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})$"
                    title="Please enter a valid ID or email address.">
            <div id="login_id_validation" class="mt-1 text-sm text-red-600 hidden">
              Please enter a valid ID or email address.
            </div>
          </div>
          
          <!-- Password -->
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
              <svg class="h-5 w-5 sm:h-6 sm:w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
              </svg>
            </div>
            <input type="password" name="password" id="password" placeholder="Password" required
                    class="w-full pl-10 sm:pl-12 pr-10 sm:pr-12 py-3 sm:py-4 lg:py-5 bg-white border border-stone-300 rounded-lg sm:rounded-xl focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent transition-all duration-200 text-sm sm:text-base @if(isset($errors) && $errors->has('password')) border-red-500 @endif">
            <div class="absolute inset-y-0 right-0 pr-3 sm:pr-4 flex items-center">
              <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" onclick="togglePasswordField(this)">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
              </button>
            </div>
          </div>
          
            <div class="text-right">
              <button type="button" onclick="switchToResetMode()" class="text-ogs-navy font-semibold text-xs sm:text-sm hover:underline">
                <i class="fas fa-key mr-1"></i>
                Forgot Password?
              </button>
            </div>
          </div>

          <!-- Reset Mode Fields (Hidden by default) -->
          <div id="resetFields" class="hidden space-y-4">
            <!-- CSRF Token -->
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            
            <!-- Success Message for Reset Mode -->
            @if (session('status'))
              <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">
                      {{ session('status') }}
                    </p>
                  </div>
                </div>
              </div>
            @endif
            
            <div>
              <label for="reset_username" class="block text-sm font-medium text-gray-700 mb-2">
                Username or Email <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input type="text" 
                       id="reset_username" 
                       name="username" 
                       value="{{ old('username') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent @if(isset($errors) && $errors->has('username')) border-red-500 @endif"
                       placeholder="Enter your username or email address">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <i class="fas fa-envelope text-gray-400"></i>
                </div>
              </div>
              @if(isset($errors) && $errors->has('username'))
                <p class="mt-1 text-sm text-red-600">{{ $errors->first('username') }}</p>
              @endif
              <p class="mt-1 text-xs text-gray-500">
                Enter your Tutor/Supervisor ID or email address to receive a password reset link
              </p>
            </div>

            <!-- Email Verification Success Message -->
            @if (session('status'))
              <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">
                      {{ session('status') }}
                    </p>
                  </div>
                </div>
              </div>
            @endif

            <div class="text-right">
              <button type="button" onclick="switchToLoginMode()" class="text-ogs-navy font-semibold text-xs sm:text-sm hover:underline">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Login
              </button>
            </div>
          </div>

          <!-- New Password Reset Fields (from email link) -->
          <div id="newPasswordFields" class="hidden space-y-4">
            <!-- CSRF Token -->
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="token" id="reset_token" value="">
            <input type="hidden" name="email" id="reset_email" value="">
            
            <!-- User Info Display -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <i class="fas fa-user text-blue-400"></i>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-blue-800">
                    Setting new password for: <span id="resetUserEmail" class="font-mono"></span>
                  </p>
                </div>
              </div>
            </div>

            <!-- New Password Field -->
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                New Password <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input type="password" 
                       id="new_password_field" 
                       name="password" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent"
                       placeholder="Enter your new password"
                       minlength="8"
                       oninput="validateNewPassword()"
                       required>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" onclick="togglePasswordField(this)">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                </div>
              </div>
              <div id="password-strength" class="mt-1 text-xs"></div>
            </div>

            <!-- Confirm Password Field -->
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                Confirm New Password <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input type="password" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent"
                       placeholder="Confirm your new password"
                       minlength="8"
                       oninput="validatePasswordMatch()"
                       required>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" onclick="togglePasswordField(this)">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                </div>
              </div>
              <div id="password-match" class="mt-1 text-xs"></div>
            </div>

            <div class="text-right">
              <button type="button" onclick="switchToLoginMode()" class="text-ogs-navy font-semibold text-xs sm:text-sm hover:underline">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Login
              </button>
            </div>
          </div>

          <!-- Password Reset Fields (Hidden by default) -->
          <div id="passwordResetFields" class="hidden space-y-4">
            <!-- User Info Display -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-green-800">
                    Security question verified! Please set your new password.
                  </p>
                </div>
              </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <i class="fas fa-user text-blue-400"></i>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-blue-800" id="userInfoDisplay">
                    <!-- User info will be populated here -->
                  </p>
                </div>
              </div>
            </div>

            <!-- New Password Field -->
            <div>
              <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                New Password <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input type="password" 
                       id="new_password" 
                       name="new_password" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent"
                       placeholder="Enter your new password"
                       oninput="validatePasswordMatch()">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" onclick="togglePasswordField(this)">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                </div>
              </div>
              <div id="password-strength" class="mt-1 text-xs"></div>
            </div>

            <!-- Confirm Password Field -->
            <div>
              <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                Confirm New Password <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent"
                       placeholder="Confirm your new password"
                       oninput="validatePasswordMatch()">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" onclick="togglePasswordField(this)">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                </div>
              </div>
              <div id="password-match" class="mt-1 text-xs"></div>
            </div>

            <div class="text-right">
              <button type="button" onclick="switchToLoginMode()" class="text-ogs-navy font-semibold text-xs sm:text-sm hover:underline">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Login
              </button>
            </div>
          </div>
          
          <!-- Dynamic Submit Button -->
          <button type="submit" id="submitButton"
                  class="w-full py-3 sm:py-4 lg:py-5 rounded-lg sm:rounded-xl bg-mint hover:bg-teal text-ogs-navy font-bold text-base sm:text-lg lg:text-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98]">
            LOG IN
          </button>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Debug logging function
    function debugLog(message, data = null) {
      console.log(`[LOGIN DEBUG] ${message}`, data || '');
    }

    // Initialize debugging
    debugLog('Login page loaded');
    debugLog('Current form action:', '{{ route("login") }}');
    debugLog('CSRF token present:', document.querySelector('input[name="_token"]') ? 'Yes' : 'No');

    // Add event listeners for form validation
    document.addEventListener('DOMContentLoaded', function() {
      const loginIdField = document.getElementById('login_id');
      
      // Add validation for login ID field
      if (loginIdField) {
        loginIdField.addEventListener('input', validateLoginId);
        loginIdField.addEventListener('blur', validateLoginId);
      }

      // Initially remove required attributes from hidden password reset fields
      const newPasswordField = document.getElementById('new_password_field');
      const confirmPasswordField = document.getElementById('password_confirmation');
      if (newPasswordField) newPasswordField.removeAttribute('required');
      if (confirmPasswordField) confirmPasswordField.removeAttribute('required');

      // Check if we should show reset mode (from forgot-password redirect)
      @if(session('show_reset'))
        switchToResetMode();
      @endif

      // Check if we're coming from a password reset link (URL parameters)
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('reset_mode') === 'true') {
        const resetToken = urlParams.get('token');
        const resetEmail = urlParams.get('email');
        if (resetToken && resetEmail) {
          switchToNewPasswordMode(resetToken, resetEmail);
        }
      }
    });

    // Function to validate login ID field
    function validateLoginId() {
      const loginIdField = document.getElementById('login_id');
      const validationDiv = document.getElementById('login_id_validation');
      
      if (!loginIdField || !validationDiv) return;
      
      const value = loginIdField.value.trim();
      
      // Clear previous validation state
      loginIdField.classList.remove('border-red-500', 'border-green-500');
      validationDiv.classList.add('hidden');
      
      if (value === '') {
        return; // Don't show error for empty field (required attribute will handle it)
      }
      
      // Validate ID format (OGS-S1001, OGS-T1001, etc.)
      const idPattern = /^OGS-[ST]\d{4}$/;
      // Validate email format
      const emailPattern = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
      
      if (idPattern.test(value) || emailPattern.test(value)) {
        // Valid input
        loginIdField.classList.add('border-green-500');
      } else {
        // Invalid input
        loginIdField.classList.add('border-red-500');
        validationDiv.classList.remove('hidden');
      }
    }

    function togglePasswordField(btn) {
      const input = btn.parentElement.parentElement.querySelector('input[type="password"], input[type="text"]');
      if (input.type === 'password') {
        input.type = 'text';
        btn.querySelector('svg').classList.add('text-mint');
        debugLog('Password field shown');
      } else {
        input.type = 'password';
        btn.querySelector('svg').classList.remove('text-mint');
        debugLog('Password field hidden');
      }
    }

    function switchToResetMode() {
      debugLog('Switching to reset mode');
      
      // Check if elements exist before manipulating them
      const loginFields = document.getElementById('loginFields');
      const resetFields = document.getElementById('resetFields');
      
      if (!loginFields || !resetFields) {
        debugLog('Missing elements for reset mode:', {
          loginFields: !!loginFields,
          resetFields: !!resetFields
        });
        alert('Form elements not found. Please refresh the page and try again.');
        return;
      }
      
      // Hide login fields
      loginFields.classList.add('hidden');
      debugLog('Login fields hidden');
      
      // Hide any error messages from login
      const errorAlert = document.getElementById('loginErrorAlert');
      if (errorAlert) {
        errorAlert.style.display = 'none';
        debugLog('Login error messages hidden');
      }
      
      // Show reset fields
      resetFields.classList.remove('hidden');
      debugLog('Reset fields shown');
      
      // Change title and description
      const mainTitle = document.getElementById('mainTitle');
      const mainDescription = document.getElementById('mainDescription');
      if (mainTitle && mainDescription) {
        mainTitle.textContent = 'Reset Your Password';
        mainDescription.textContent = 'Enter your username or email address to receive a password reset link.';
        debugLog('Title and description updated');
      } else {
        debugLog('Title or description element not found');
      }
      
      // Change submit button to email reset
      const submitButton = document.getElementById('submitButton');
      if (submitButton) {
        submitButton.innerHTML = '<i class="fas fa-envelope mr-2"></i>SEND RESET EMAIL';
        submitButton.type = 'button';
        submitButton.onclick = function(e) {
          e.preventDefault();
          debugLog('Send reset email button clicked');
          sendResetEmail();
        };
        debugLog('Submit button changed to email reset mode');
      } else {
        debugLog('Submit button not found');
      }
      
      // Focus on username field
      const resetUsername = document.getElementById('reset_username');
      if (resetUsername) {
        resetUsername.focus();
        debugLog('Focus set to reset username field');
      } else {
        debugLog('Reset username field not found');
      }
      
      // Clear any existing form data
      clearAllFormFields();
    }

    function switchToNewPasswordMode(resetToken, resetEmail) {
      debugLog('Switching to new password mode from reset link');
      debugLog('Reset token:', resetToken);
      debugLog('Reset email:', resetEmail);
      
      if (!resetToken || !resetEmail) {
        debugLog('No reset token or email provided');
        return;
      }
      
      // Hide all other fields
      const loginFields = document.getElementById('loginFields');
      const resetFields = document.getElementById('resetFields');
      const passwordResetFields = document.getElementById('passwordResetFields');
      const newPasswordFields = document.getElementById('newPasswordFields');
      
      if (loginFields) loginFields.classList.add('hidden');
      if (resetFields) resetFields.classList.add('hidden');
      if (passwordResetFields) passwordResetFields.classList.add('hidden');
      
      // Remove required attributes from hidden login fields to prevent form validation errors
      const loginIdField = document.getElementById('login_id');
      const loginPasswordField = document.getElementById('password');
      if (loginIdField) {
        loginIdField.removeAttribute('required');
        debugLog('Removed required from login_id field');
      }
      if (loginPasswordField) {
        loginPasswordField.removeAttribute('required');
        debugLog('Removed required from login password field');
      }
      
      // Show new password fields
      if (newPasswordFields) {
        newPasswordFields.classList.remove('hidden');
        // Add required attributes to the fields
        const newPasswordField = document.getElementById('new_password_field');
        const confirmPasswordField = document.getElementById('password_confirmation');
        if (newPasswordField) newPasswordField.setAttribute('required', 'required');
        if (confirmPasswordField) confirmPasswordField.setAttribute('required', 'required');
        debugLog('New password fields shown');
      } else {
        debugLog('New password fields not found');
        return;
      }
      
      // Set token and email in hidden fields
      const tokenField = document.getElementById('reset_token');
      const emailField = document.getElementById('reset_email');
      const emailDisplay = document.getElementById('resetUserEmail');
      
      if (tokenField) tokenField.value = resetToken;
      if (emailField) emailField.value = resetEmail;
      if (emailDisplay) emailDisplay.textContent = resetEmail;
      
      // Change title and description
      const mainTitle = document.getElementById('mainTitle');
      const mainDescription = document.getElementById('mainDescription');
      if (mainTitle && mainDescription) {
        mainTitle.textContent = 'Set New Password';
        mainDescription.textContent = 'Please enter your new password below.';
        debugLog('Title and description updated for new password mode');
      }
      
      // Update submit button
      const submitButton = document.getElementById('submitButton');
      if (submitButton) {
        submitButton.innerHTML = '<i class="fas fa-lock mr-2"></i>UPDATE PASSWORD';
        submitButton.type = 'submit';
        submitButton.onclick = null; // Remove any existing onclick handlers
        debugLog('Submit button changed to update password mode');
      }
      
      // Focus on new password field
      const newPasswordField = document.getElementById('new_password_field');
      if (newPasswordField) {
        newPasswordField.focus();
        debugLog('Focus set to new password field');
      }
      
      // Update form action
      const form = document.querySelector('form');
      if (form) {
        form.action = '/reset-password';
        debugLog('Form action updated to password reset store route');
      }
    }

    function switchToPasswordResetMode() {
      debugLog('Switching to password reset mode');
      
      // Hide reset fields
      const resetFields = document.getElementById('resetFields');
      if (resetFields) {
        resetFields.classList.add('hidden');
        debugLog('Reset fields hidden');
      }
      
      // Show password reset fields
      const passwordResetFields = document.getElementById('passwordResetFields');
      if (passwordResetFields) {
        passwordResetFields.classList.remove('hidden');
        debugLog('Password reset fields shown');
      }
      
      // Update user info display
      const userInfoDisplay = document.getElementById('userInfoDisplay');
      if (userInfoDisplay && window.passwordResetUser) {
        const userType = window.passwordResetUser.userType.charAt(0).toUpperCase() + window.passwordResetUser.userType.slice(1);
        userInfoDisplay.innerHTML = `<strong>${userType}:</strong> ${window.passwordResetUser.username}`;
        debugLog('User info display updated');
      }
      
      // Change title and description
      const mainTitle = document.getElementById('mainTitle');
      const mainDescription = document.getElementById('mainDescription');
      if (mainTitle && mainDescription) {
        mainTitle.textContent = 'Set New Password';
        mainDescription.textContent = 'Please enter your new password below.';
        debugLog('Title and description updated');
      }
      
      // Change submit button
      const submitButton = document.getElementById('submitButton');
      if (submitButton) {
        submitButton.innerHTML = '<i class="fas fa-key mr-2"></i>UPDATE PASSWORD';
        submitButton.type = 'button';
        submitButton.onclick = function(e) {
          e.preventDefault();
          debugLog('Update password button clicked');
          updatePassword();
        };
        debugLog('Submit button changed to update password mode');
      }
      
      // Focus on new password field
      const newPasswordField = document.getElementById('new_password');
      if (newPasswordField) {
        newPasswordField.focus();
        debugLog('Focus set to new password field');
      }
    }

    function switchToLoginMode() {
      debugLog('Switching to login mode');
      
      // Show login fields
      const loginFields = document.getElementById('loginFields');
      if (loginFields) {
        loginFields.classList.remove('hidden');
        debugLog('Login fields shown');
      }
      
      // Restore required attributes to login fields
      const loginIdField = document.getElementById('login_id');
      const loginPasswordField = document.getElementById('password');
      if (loginIdField) {
        loginIdField.setAttribute('required', 'required');
        debugLog('Restored required to login_id field');
      }
      if (loginPasswordField) {
        loginPasswordField.setAttribute('required', 'required');
        debugLog('Restored required to login password field');
      }
      
      // Show any error messages from login
      const errorAlert = document.getElementById('loginErrorAlert');
      if (errorAlert) {
        errorAlert.style.display = 'block';
        debugLog('Login error messages shown');
      }
      
      // Hide reset fields
      const resetFields = document.getElementById('resetFields');
      if (resetFields) {
        resetFields.classList.add('hidden');
        debugLog('Reset fields hidden');
      }
      
      // Hide password reset fields
      const passwordResetFields = document.getElementById('passwordResetFields');
      if (passwordResetFields) {
        passwordResetFields.classList.add('hidden');
        debugLog('Password reset fields hidden');
      }
      
      // Hide new password fields and remove required attributes
      const newPasswordFields = document.getElementById('newPasswordFields');
      if (newPasswordFields) {
        newPasswordFields.classList.add('hidden');
        // Remove required attributes from hidden fields
        const newPasswordField = document.getElementById('new_password_field');
        const confirmPasswordField = document.getElementById('password_confirmation');
        if (newPasswordField) newPasswordField.removeAttribute('required');
        if (confirmPasswordField) confirmPasswordField.removeAttribute('required');
        debugLog('New password fields hidden and required attributes removed');
      }
      
      // Change title and description back
      const mainTitle = document.getElementById('mainTitle');
      const mainDescription = document.getElementById('mainDescription');
      if (mainTitle && mainDescription) {
        mainTitle.textContent = 'Log in to your Account';
        mainDescription.textContent = 'Provide your login details below to securely access your OGS Connect account.';
        debugLog('Title and description reset to login');
      }
      
      // Change submit button back
      const submitButton = document.getElementById('submitButton');
      if (submitButton) {
        submitButton.innerHTML = 'LOG IN';
        submitButton.type = 'submit';
        submitButton.onclick = null;
        debugLog('Submit button reset to login mode');
      }
      
      // Reset form action to login
      const form = document.querySelector('form');
      if (form) {
        form.action = '/login';
        form.method = 'POST';
        debugLog('Form action reset to login');
      }
      
      // Focus on login field
      const loginField = document.querySelector('input[name="login_id"]');
      if (loginField) {
        loginField.focus();
        debugLog('Focus set to login field');
      }
      
      // Clear stored password reset data
      window.passwordResetUser = null;
      
      // Clear all form fields
      clearAllFormFields();
    }

    function updatePassword() {
      debugLog('Starting password update');
      
      const newPassword = document.getElementById('new_password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      
      // Validate passwords
      if (!newPassword || !confirmPassword) {
        alert('Please fill in both password fields.');
        return;
      }
      
      if (newPassword !== confirmPassword) {
        alert('Passwords do not match. Please check the confirmation field.');
        document.getElementById('confirm_password').focus();
        return;
      }
      
      if (newPassword.length < 8) {
        alert('Password must be at least 8 characters long.');
        document.getElementById('new_password').focus();
        return;
      }
      
      if (!window.passwordResetUser) {
        alert('Session expired. Please try again.');
        switchToLoginMode();
        return;
      }
      
      debugLog('Password validation passed, submitting update');
      
      // Create form to submit password update
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '{{ route("password.reset.store") }}';
      
      // Add CSRF token
      const csrfToken = document.createElement('input');
      csrfToken.type = 'hidden';
      csrfToken.name = '_token';
      csrfToken.value = '{{ csrf_token() }}';
      form.appendChild(csrfToken);
      
      // Add form data
      const formData = [
        { name: 'password', value: newPassword },
        { name: 'password_confirmation', value: confirmPassword },
        { name: 'username', value: window.passwordResetUser.username },
        { name: 'user_type', value: window.passwordResetUser.userType }
      ];
      
      formData.forEach(field => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = field.name;
        input.value = field.value;
        form.appendChild(input);
        debugLog(`Added field: ${field.name}`);
      });
      
      document.body.appendChild(form);
      debugLog('Submitting password update form');
      form.submit();
    }

    function validatePasswordMatch() {
      const newPassword = document.getElementById('new_password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      const passwordStrengthDiv = document.getElementById('password-strength');
      const passwordMatchDiv = document.getElementById('password-match');
      
      // Password strength validation
      if (newPassword.length > 0) {
        let strengthMessage = '';
        let strengthColor = '';
        
        if (newPassword.length < 8) {
          strengthMessage = 'Password must be at least 8 characters';
          strengthColor = 'text-red-600';
        } else if (newPassword.length < 12) {
          strengthMessage = 'Good password length';
          strengthColor = 'text-yellow-600';
        } else {
          strengthMessage = 'Strong password length';
          strengthColor = 'text-green-600';
        }
        
        passwordStrengthDiv.innerHTML = `<span class="${strengthColor}">${strengthMessage}</span>`;
      } else {
        passwordStrengthDiv.innerHTML = '';
      }
      
      // Password match validation
      if (confirmPassword.length > 0) {
        let matchMessage = '';
        let matchColor = '';
        
        if (newPassword === confirmPassword) {
          if (newPassword.length >= 8) {
            matchMessage = '✓ Passwords match';
            matchColor = 'text-green-600';
          } else {
            matchMessage = '✓ Passwords match (but too short)';
            matchColor = 'text-yellow-600';
          }
        } else {
          matchMessage = '✗ Passwords do not match';
          matchColor = 'text-red-600';
        }
        
        passwordMatchDiv.innerHTML = `<span class="${matchColor}">${matchMessage}</span>`;
      } else {
        passwordMatchDiv.innerHTML = '';
      }
    }

    function clearAllFormFields() {
      debugLog('Clearing all form fields');
      
      // Clear login fields
      const loginIdField = document.getElementById('login_id');
      const passwordField = document.getElementById('password');
      if (loginIdField) {
        loginIdField.value = '';
        loginIdField.classList.remove('border-red-500', 'border-green-500');
      }
      if (passwordField) {
        passwordField.value = '';
      }
      
      // Clear reset fields
      const resetUsernameField = document.getElementById('reset_username');
      if (resetUsernameField) resetUsernameField.value = '';
      
      // Clear password reset fields
      const newPasswordField = document.getElementById('new_password');
      const confirmPasswordField = document.getElementById('confirm_password');
      if (newPasswordField) newPasswordField.value = '';
      if (confirmPasswordField) confirmPasswordField.value = '';
      
      // Clear password strength indicators
      const passwordStrengthDiv = document.getElementById('password-strength');
      const passwordMatchDiv = document.getElementById('password-match');
      if (passwordStrengthDiv) passwordStrengthDiv.innerHTML = '';
      if (passwordMatchDiv) passwordMatchDiv.innerHTML = '';
      
      debugLog('All form fields cleared');
    }

    // Simple error message function for email verification
    function showErrorMessage(message) {
      // Create error message element
      const errorDiv = document.createElement('div');
      errorDiv.className = 'error-message mt-2 p-3 bg-red-50 border border-red-200 rounded-md';
      errorDiv.innerHTML = `
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-800">${message}</p>
          </div>
        </div>
      `;
      
      // Insert error message after the form
      const resetFields = document.getElementById('resetFields');
      if (resetFields) {
        resetFields.appendChild(errorDiv);
      }
    }

    // Send password reset email
    function sendResetEmail() {
      const username = document.getElementById('reset_username').value;
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      
      debugLog('CSRF Token:', csrfToken);
      
      if (!username) {
        showErrorMessage('Please enter your username or email.');
        return;
      }
      
      debugLog('Sending password reset email...');
      
      // Clear any existing error messages
      const existingErrors = document.querySelectorAll('.error-message');
      existingErrors.forEach(error => error.remove());
      
      // Show loading state
      const submitButton = document.getElementById('submitButton');
      const originalText = submitButton.innerHTML;
      submitButton.disabled = true;
      submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>SENDING EMAIL...';
      
      // For emails, let the server auto-detect the user type
      let userType = 'auto'; // Server will determine this for emails
      if (username.startsWith('OGS-S')) {
        userType = 'supervisor';
      } else if (username.startsWith('OGS-T')) {
        userType = 'tutor';
      } else if (username.includes('@')) {
        userType = 'auto'; // Server will search both tables and determine correct type
      }
      
      const fetchUrl = '{{ route("password.email") }}';
      debugLog('Fetch URL:', fetchUrl);
      
      const requestBody = JSON.stringify({
        email: username,
        user_type: userType
      });
      debugLog('Request body:', requestBody);
      
      fetch(fetchUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: requestBody
      })
      .then(response => {
        debugLog('Response status:', response.status);
        if (response.ok) {
          return response.json();
        } else if (response.status === 422) {
          // Validation errors
          return response.json().then(data => {
            const validationMessage = data.errors?.username?.[0] || data.message || 'Validation failed';
            throw new Error(validationMessage);
          });
        } else {
          return response.json().then(data => {
            throw new Error(data.message || 'Failed to send reset email');
          }).catch(() => {
            throw new Error('Failed to send reset email. Please try again.');
          });
        }
      })
      .then(data => {
        debugLog('Email sent successfully');
        
        // Show success message
        const successDiv = document.createElement('div');
        successDiv.className = 'mt-4 p-4 bg-green-50 border border-green-200 rounded-lg transition-all duration-500 ease-in-out';
        successDiv.innerHTML = `
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-green-800">
                Password reset email sent! Please check your email inbox and follow the instructions to reset your password.
              </p>
            </div>
          </div>
        `;
        
        const resetFields = document.getElementById('resetFields');
        resetFields.appendChild(successDiv);
        
        // Clear the input field but keep the form visible
        document.getElementById('reset_username').value = '';
        
        // Optionally scroll to the success message
        successDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        
        // Auto-hide the success message after 5 seconds
        setTimeout(() => {
          successDiv.style.opacity = '0';
          successDiv.style.transform = 'translateY(-10px)';
          setTimeout(() => {
            if (successDiv.parentNode) {
              successDiv.parentNode.removeChild(successDiv);
            }
          }, 500); // Wait for fade out transition to complete
        }, 5000); // Show for 5 seconds
      })
      .catch(error => {
        debugLog('Email send error:', error.message);
        showErrorMessage(error.message);
      })
      .finally(() => {
        // Restore button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
      });
    }

    // Update password function
    function updatePassword() {
      const password = document.getElementById('new_password_field').value;
      const passwordConfirmation = document.getElementById('password_confirmation').value;
      const token = document.getElementById('reset_token').value;
      const email = document.getElementById('reset_email').value;
      
      debugLog('Updating password...');
      debugLog('Token:', token);
      debugLog('Email:', email);
      
      // Validate passwords
      if (!password || !passwordConfirmation) {
        showErrorMessage('Please fill in both password fields.');
        return;
      }
      
      if (password !== passwordConfirmation) {
        showErrorMessage('Passwords do not match.');
        return;
      }
      
      if (password.length < 8) {
        showErrorMessage('Password must be at least 8 characters long.');
        return;
      }
      
      if (!token || !email) {
        showErrorMessage('Reset token or email missing. Please request a new reset link.');
        return;
      }
      
      // Clear any existing error messages
      const existingErrors = document.querySelectorAll('.error-message');
      existingErrors.forEach(error => error.remove());
      
      // Show loading state
      const submitButton = document.getElementById('submitButton');
      const originalText = submitButton.innerHTML;
      submitButton.disabled = true;
      submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>UPDATING...';
      
      // Create form data and submit using regular form submission
      const form = document.querySelector('form');
      
      // Update form action and method
      form.action = '/reset-password';
      form.method = 'POST';
      
      // Set the hidden field values
      document.getElementById('reset_token').value = token;
      document.getElementById('reset_email').value = email;
      
      // Submit the form
      form.submit();
    }

    // Validation functions for new password form
    function validateNewPassword() {
      const passwordField = document.getElementById('new_password_field');
      const strengthDiv = document.getElementById('password-strength');
      
      if (!passwordField || !strengthDiv) return;
      
      const password = passwordField.value;
      
      // Clear previous validation
      passwordField.classList.remove('border-red-500', 'border-green-500');
      strengthDiv.innerHTML = '';
      
      if (password.length === 0) return;
      
      let strength = 0;
      let messages = [];
      
      // Length check
      if (password.length >= 8) {
        strength++;
        messages.push('<span class="text-green-600">✓ At least 8 characters</span>');
      } else {
        messages.push('<span class="text-red-600">✗ At least 8 characters required</span>');
      }
      
      // Uppercase check
      if (/[A-Z]/.test(password)) {
        strength++;
        messages.push('<span class="text-green-600">✓ Contains uppercase letter</span>');
      } else {
        messages.push('<span class="text-yellow-600">• Add uppercase letter for stronger password</span>');
      }
      
      // Lowercase check
      if (/[a-z]/.test(password)) {
        strength++;
        messages.push('<span class="text-green-600">✓ Contains lowercase letter</span>');
      } else {
        messages.push('<span class="text-yellow-600">• Add lowercase letter for stronger password</span>');
      }
      
      // Number check
      if (/\d/.test(password)) {
        strength++;
        messages.push('<span class="text-green-600">✓ Contains number</span>');
      } else {
        messages.push('<span class="text-yellow-600">• Add number for stronger password</span>');
      }
      
      // Special character check
      if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
        strength++;
        messages.push('<span class="text-green-600">✓ Contains special character</span>');
      } else {
        messages.push('<span class="text-yellow-600">• Add special character for stronger password</span>');
      }
      
      // Update strength indicator
      strengthDiv.innerHTML = messages.join('<br>');
      
      // Update field border based on minimum requirements
      if (password.length >= 8) {
        passwordField.classList.add('border-green-500');
        passwordField.classList.remove('border-red-500');
      } else {
        passwordField.classList.add('border-red-500');
        passwordField.classList.remove('border-green-500');
      }
      
      // Also validate password match when password changes
      validatePasswordMatch();
    }

    function validatePasswordMatch() {
      const passwordField = document.getElementById('new_password_field');
      const confirmField = document.getElementById('password_confirmation');
      const matchDiv = document.getElementById('password-match');
      
      if (!passwordField || !confirmField || !matchDiv) return;
      
      const password = passwordField.value;
      const confirm = confirmField.value;
      
      // Clear previous validation
      confirmField.classList.remove('border-red-500', 'border-green-500');
      matchDiv.innerHTML = '';
      
      if (confirm.length === 0) return;
      
      if (password === confirm && confirm.length >= 8) {
        confirmField.classList.add('border-green-500');
        confirmField.classList.remove('border-red-500');
        matchDiv.innerHTML = '<span class="text-green-600">✓ Passwords match</span>';
      } else if (password !== confirm) {
        confirmField.classList.add('border-red-500');
        confirmField.classList.remove('border-green-500');
        matchDiv.innerHTML = '<span class="text-red-600">✗ Passwords do not match</span>';
      } else if (confirm.length < 8) {
        confirmField.classList.add('border-red-500');
        confirmField.classList.remove('border-green-500');
        matchDiv.innerHTML = '<span class="text-red-600">✗ Password must be at least 8 characters</span>';
      }
    }

    // Ensure form submission works correctly for login
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('form');
      if (form) {
        form.addEventListener('submit', function(e) {
          // Get form mode
          const loginFields = document.getElementById('loginFields');
          const resetFields = document.getElementById('resetFields');
          const newPasswordFields = document.getElementById('newPasswordFields');
          
          // Check if we're in login mode (loginFields visible, others hidden)
          if (loginFields && !loginFields.classList.contains('hidden')) {
            debugLog('Form submitting in login mode');
            
            // Ensure password field value is captured
            const passwordField = document.getElementById('password');
            if (passwordField) {
              debugLog('Password field value:', passwordField.value ? 'has value' : 'empty');
              // Force the field to be enabled and named correctly
              passwordField.disabled = false;
              passwordField.name = 'password';
            }
            
            // Disable other password fields to prevent conflicts
            const otherPasswordFields = form.querySelectorAll('input[type="password"]:not(#password)');
            otherPasswordFields.forEach(field => {
              field.disabled = true;
              debugLog('Disabled other password field:', field.name || field.id);
            });
          }
          
          // Check if we're in new password mode
          else if (newPasswordFields && !newPasswordFields.classList.contains('hidden')) {
            debugLog('Form submitting in new password mode');
            
            // Ensure new password fields are enabled and properly named
            const newPasswordField = document.getElementById('new_password_field');
            const confirmPasswordField = document.getElementById('password_confirmation');
            
            if (newPasswordField) {
              newPasswordField.disabled = false;
              newPasswordField.name = 'password';
              debugLog('New password field enabled');
            }
            
            if (confirmPasswordField) {
              confirmPasswordField.disabled = false;
              confirmPasswordField.name = 'password_confirmation';
              debugLog('Confirm password field enabled');
            }
            
            // Disable login password field to prevent conflicts
            const loginPasswordField = document.getElementById('password');
            if (loginPasswordField) {
              loginPasswordField.disabled = true;
              debugLog('Login password field disabled');
            }
          }
        });
      }
    });
  </script>

</body>
</html>