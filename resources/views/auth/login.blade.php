<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
          @if ($errors->any())
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
                    <p>Invalid credentials.</p>
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
                    class="w-full pl-10 sm:pl-12 pr-4 py-3 sm:py-4 lg:py-5 bg-white border border-stone-300 rounded-lg sm:rounded-xl focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent transition-all duration-200 text-sm sm:text-base @error('login_id') border-red-500 @enderror"
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
                    class="w-full pl-10 sm:pl-12 pr-10 sm:pr-12 py-3 sm:py-4 lg:py-5 bg-white border border-stone-300 rounded-lg sm:rounded-xl focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent transition-all duration-200 text-sm sm:text-base @error('password') border-red-500 @enderror">
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
            <div>
              <label for="reset_username" class="block text-sm font-medium text-gray-700 mb-2">
                Email or ID <span class="text-red-500">*</span>
              </label>
              <div class="relative">
              <input type="text" 
                     id="reset_username" 
                     name="username" 
                     value="{{ old('username') }}"
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent @error('username') border-red-500 @enderror"
                     placeholder="Enter your email or ID">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <i class="fas fa-user text-gray-400"></i>
                </div>
              </div>
              @error('username')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="user_type" class="block text-sm font-medium text-gray-700 mb-2">
                Account Type <span class="text-red-500">*</span>
              </label>
              <div id="user_type_display" class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-700 mb-2">
                <span class="text-sm">Account type will be detected automatically</span>
              </div>
              <input type="hidden" id="user_type" name="user_type" value="">
            </div>

            <div>
              <label for="security_question" class="block text-sm font-medium text-gray-700 mb-2">
                Security Question <span class="text-red-500">*</span>
              </label>
              <div id="security_question_display" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                <span class="text-sm">Please enter your username and account type first</span>
              </div>
              <input type="hidden" id="security_question" name="security_question" value="">
              @error('security_question')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="security_answer1" class="block text-sm font-medium text-gray-700 mb-2">
                Your Answer <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input type="password" 
                       id="security_answer1" 
                       name="security_answer1" 
                       value="{{ old('security_answer1') }}"
                       class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent @error('security_answer1') border-red-500 @enderror"
                       placeholder="Enter your answer">
                <button type="button" onclick="togglePasswordField(this)" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                </button>
              </div>
              @error('security_answer1')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="security_question2" class="block text-sm font-medium text-gray-700 mb-2">
                Second Security Question <span class="text-red-500">*</span>
              </label>
              <div id="security_question_display2" class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-700 mb-2">
                <span class="text-sm">Please enter your email/ID and account type first</span>
              </div>
              <input type="hidden" id="security_question2" name="security_question2" value="">
            </div>

            <div>
              <label for="security_answer2" class="block text-sm font-medium text-gray-700 mb-2">
                Your Answer <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input type="password" 
                       id="security_answer2" 
                       name="security_answer2" 
                       value="{{ old('security_answer2') }}"
                       class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent @error('security_answer2') border-red-500 @enderror"
                       placeholder="Enter your answer">
                <button type="button" onclick="togglePasswordField(this)" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                </button>
              </div>
              @error('security_answer2')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
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

    // Add event listeners for security question fetching
    document.addEventListener('DOMContentLoaded', function() {
      const usernameField = document.getElementById('reset_username');
      const userTypeField = document.getElementById('user_type');
      const loginIdField = document.getElementById('login_id');
      
      if (usernameField) {
        usernameField.addEventListener('input', fetchSecurityQuestion);
      }
      
      if (userTypeField) {
        userTypeField.addEventListener('change', fetchSecurityQuestion);
      }
      
      // Add validation for login ID field
      if (loginIdField) {
        loginIdField.addEventListener('input', validateLoginId);
        loginIdField.addEventListener('blur', validateLoginId);
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
        mainDescription.textContent = 'Answer the security question below to reset your password.';
        debugLog('Title and description updated');
      } else {
        debugLog('Title or description element not found');
      }
      
      // Change submit button
      const submitButton = document.getElementById('submitButton');
      if (submitButton) {
        submitButton.innerHTML = '<i class="fas fa-check mr-2"></i>VERIFY & RESET';
        submitButton.type = 'button';
        submitButton.onclick = function(e) {
          e.preventDefault();
          debugLog('Reset button clicked');
          verifyAndReset();
        };
        debugLog('Submit button changed to reset mode');
      } else {
        debugLog('Submit button not found');
      }
      
      // Focus on first reset field
      const resetUsername = document.getElementById('reset_username');
      if (resetUsername) {
        resetUsername.focus();
        debugLog('Focus set to reset username field');
      } else {
        debugLog('Reset username field not found');
      }
      
      // Fetch security question
      debugLog('Fetching security question...');
      fetchSecurityQuestion();
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
      
      // Focus on login field
      const loginField = document.querySelector('input[name="login_id"]');
      if (loginField) {
        loginField.focus();
        debugLog('Focus set to login field');
      }
      
      // Clear stored password reset data
      window.passwordResetUser = null;
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

    // Function to fetch security question for user
    function fetchSecurityQuestion() {
      const username = document.getElementById('reset_username').value;
      const userType = document.getElementById('user_type').value;
      
      if (!username) {
        document.getElementById('user_type_display').innerHTML = '<span class="text-sm">Account type will be detected automatically</span>';
        document.getElementById('user_type').value = '';
        document.getElementById('security_question_display').innerHTML = '<span class="text-sm">Please enter your email/ID first</span>';
        document.getElementById('security_question_display2').innerHTML = '<span class="text-sm">Please enter your email/ID first</span>';
        document.getElementById('security_question').value = '';
        document.getElementById('security_question2').value = '';
        return;
      }

      debugLog('Detecting account type and fetching security questions for:', { username });

      // Make AJAX request to detect account type and get security questions
      fetch('/api/get-security-question', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          username: username
        })
      })
      .then(response => response.json())
      .then(data => {
        debugLog('Security question response:', data);
        
        if (data.success && data.user_type && data.questions && data.questions.length >= 2) {
          // Update account type display
          const accountTypeText = data.user_type === 'tutor' ? 'Tutor' : 'Supervisor';
          document.getElementById('user_type_display').innerHTML = `<span class="text-sm font-medium text-green-600">${accountTypeText}</span>`;
          document.getElementById('user_type').value = data.user_type;
          
          // First question
          document.getElementById('security_question_display').innerHTML = `<span class="text-sm font-medium">${data.questions[0]}</span>`;
          document.getElementById('security_question').value = data.questions[0];
          
          // Second question
          document.getElementById('security_question_display2').innerHTML = `<span class="text-sm font-medium">${data.questions[1]}</span>`;
          document.getElementById('security_question2').value = data.questions[1];
        } else if (data.success === false && data.message) {
          // Show error message
          document.getElementById('user_type_display').innerHTML = '<span class="text-sm text-red-600">Account not found</span>';
          document.getElementById('user_type').value = '';
          document.getElementById('security_question_display').innerHTML = '<span class="text-sm text-red-600">' + data.message + '</span>';
          document.getElementById('security_question_display2').innerHTML = '<span class="text-sm text-red-600">' + data.message + '</span>';
          document.getElementById('security_question').value = '';
          document.getElementById('security_question2').value = '';
        } else {
          document.getElementById('user_type_display').innerHTML = '<span class="text-sm text-red-600">Account not found</span>';
          document.getElementById('user_type').value = '';
          document.getElementById('security_question_display').innerHTML = '<span class="text-sm text-red-600">No security questions found for this account</span>';
          document.getElementById('security_question_display2').innerHTML = '<span class="text-sm text-red-600">No security questions found for this account</span>';
          document.getElementById('security_question').value = '';
          document.getElementById('security_question2').value = '';
        }
      })
      .catch(error => {
        debugLog('Error fetching security questions:', error);
        document.getElementById('user_type_display').innerHTML = '<span class="text-sm text-red-600">Error detecting account</span>';
        document.getElementById('user_type').value = '';
        document.getElementById('security_question_display').innerHTML = '<span class="text-sm text-red-600">Error loading security questions</span>';
        document.getElementById('security_question_display2').innerHTML = '<span class="text-sm text-red-600">Error loading security questions</span>';
        document.getElementById('security_question').value = '';
        document.getElementById('security_question2').value = '';
      });
    }

    function verifyAndReset() {
      debugLog('Starting password reset verification');
      
      // Get form elements with null checks
      const usernameEl = document.getElementById('reset_username');
      const userTypeEl = document.getElementById('user_type');
      const securityQuestionEl = document.getElementById('security_question');
      const securityAnswerEl = document.getElementById('security_answer1');
      const securityQuestion2El = document.getElementById('security_question2');
      const securityAnswer2El = document.getElementById('security_answer2');
      
      // Check if all elements exist
      if (!usernameEl || !userTypeEl || !securityQuestionEl || !securityAnswerEl || !securityQuestion2El || !securityAnswer2El) {
        debugLog('Missing form elements:', {
          usernameEl: !!usernameEl,
          userTypeEl: !!userTypeEl,
          securityQuestionEl: !!securityQuestionEl,
          securityAnswerEl: !!securityAnswerEl,
          securityQuestion2El: !!securityQuestion2El,
          securityAnswer2El: !!securityAnswer2El
        });
        alert('Form elements not found. Please refresh the page and try again.');
        return;
      }
      
      const username = usernameEl.value;
      const userType = userTypeEl.value;
      const securityQuestion = securityQuestionEl.value;
      const securityAnswer = securityAnswerEl.value;
      const securityQuestion2 = securityQuestion2El.value;
      const securityAnswer2 = securityAnswer2El.value;

      debugLog('Form values:', {
        username: username,
        userType: userType,
        securityQuestion: securityQuestion,
        securityAnswer: securityAnswer,
        securityQuestion2: securityQuestion2,
        securityAnswer2: securityAnswer2
      });

      // Validate all fields
      if (!username || !userType || !securityQuestion || !securityAnswer || !securityQuestion2 || !securityAnswer2) {
        debugLog('Validation failed - missing fields');
        alert('Please fill in all fields.');
        return;
      }

      debugLog('Validation passed, switching to password reset mode');

      // Store user info for password reset
      window.passwordResetUser = {
        username: username,
        userType: userType,
        securityQuestion: securityQuestion,
        securityAnswer: securityAnswer,
        securityQuestion2: securityQuestion2,
        securityAnswer2: securityAnswer2
      };

      // Switch to password reset fields
      switchToPasswordResetMode();
    }
  </script>

</body>
</html>