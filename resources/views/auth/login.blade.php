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
<body class="h-screen overflow-hidden bg-gradient-to-b from-emerald-300 to-[#7CA6D7]">

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
  <div class="h-screen flex flex-col lg:flex-row">
    
    <!-- Left Section - Welcome Content (Hidden on mobile) -->
    <div class="hidden lg:flex flex-1 flex-col items-center justify-center px-8 relative">
      <!-- Welcome Text -->
      <div class="text-center lg:text-left mb-4">
        <div class="text-blue-950 text-3xl font-extrabold leading-tight">
          WELCOME TO<br/>OGS CONNECT
        </div>
        <div class="text-green-900 text-lg font-medium mt-2">
          A Centralized Management System
        </div>
      </div>
      
      <!-- Image -->
      <div class="w-full max-w-[300px]">
        <img class="w-full h-auto" src="{{ asset('images/login-image.png') }}" alt="OGS Connect" />
      </div>
    </div>

    <!-- Right Section - Login Form -->
    <div class="flex-1 flex items-center justify-center px-4 py-4 lg:py-6 lg:px-8 relative overflow-y-auto">
      <!-- Login Card -->
      <div class="w-full max-w-md bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl p-5 lg:p-8 my-4 flex flex-col max-h-[90vh]">
        <!-- Logo and Header -->
        <div class="w-full flex flex-col items-center mb-6">
          <!-- Mobile: Show welcome text with logo -->
          <div class="lg:hidden text-center mb-4">
            <div class="text-blue-950 text-2xl font-extrabold leading-tight mb-2">
              WELCOME TO<br/>OGS CONNECT
            </div>
            <div class="text-green-900 text-sm font-medium">
              A Centralized Management System
            </div>
          </div>
          
          <div class="text-center flex flex-col sm:flex-row items-center gap-3 mb-4">
            <img class="w-12 h-12 sm:w-16 sm:h-16" src="{{ asset('images/logo.png') }}" alt="Logo" />
            <div class="text-center sm:text-left">
              <span class="text-blue-950 text-lg sm:text-xl font-bold block">
                OUTSOURCING
              </span>
              <span class="text-blue-950 text-xs sm:text-sm font-bold block">
                GLOBAL SOLUTIONS
              </span>
            </div>
          </div>
        </div>

        <!-- Title and Description -->
        <div class="w-full text-center mb-5">
          <h1 class="text-blue-950 text-xl sm:text-2xl font-bold mb-2" id="mainTitle">
            Log in to your Account
          </h1>
          <p class="text-neutral-800 text-xs sm:text-sm font-medium" id="mainDescription">
            Enter your credentials to access OGS Connect
          </p>
        </div>

        <!-- Scrollable Content Wrapper -->
        <div class="flex-1 overflow-y-auto">
        <!-- Dynamic Form Container -->
        <form method="POST" action="{{ route('login') }}" class="space-y-3 flex flex-col min-h-full">
          @csrf
          
          <!-- Error Messages -->
          @if ($errors->any())
            <div id="loginErrorAlert" class="bg-red-50 border border-red-200 rounded-lg p-3 mb-3">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <svg class="h-4 w-4 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="ml-2">
                  <h3 class="text-xs font-medium text-red-800">
                    Login Failed
                  </h3>
                  <div class="mt-1 text-xs text-red-700">
                    <p>Invalid credentials.</p>
                  </div>
                </div>
              </div>
            </div>
          @endif
          
          <!-- Login Mode Fields -->
          <div id="loginFields" class="space-y-3">
          <!-- Unified ID/Email Field -->
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 8l7.89 7.89a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
            </div>
            <input type="text" name="login_id" id="login_id" placeholder="OGS ID or Email" required
                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent transition-all duration-200 text-sm @error('login_id') border-red-500 @enderror"
                    value="{{ old('login_id') }}"
                    pattern="^(OGS-[ST]\d{4}|[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})$"
                    title="Please enter a valid ID or email address.">
            <div id="login_id_validation" class="mt-1 text-xs text-red-600 hidden">
              Please enter a valid ID or email address.
            </div>
          </div>
          
          <!-- Password -->
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
              </svg>
            </div>
            <input type="password" name="password" id="password" placeholder="Password" required
                    class="w-full pl-10 pr-10 py-2.5 bg-white border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent transition-all duration-200 text-sm @error('password') border-red-500 @enderror">
            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-auto text-gray-400 hover:text-gray-600 transition-colors" onclick="togglePasswordField(this)">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
            </button>
          </div>
          
            <div class="flex items-center justify-end">
              <button type="button" onclick="switchToResetMode()" class="text-ogs-navy font-semibold text-xs hover:underline">
                <i class="fas fa-key mr-1"></i>
                Forgot Password?
              </button>
            </div>
          </div>
          <!-- Dynamic Submit Button -->
          <div class="pt-4 sticky bottom-0 bg-white">
            <button type="submit" id="submitButton"
                    class="w-full py-3 px-4 rounded-lg bg-mint hover:bg-teal text-ogs-navy font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98]">
              <i class="fas fa-sign-in-alt mr-2"></i>LOG IN
            </button>
          </div>
          <!-- Reset Mode Fields (Hidden by default) -->
          <div id="resetFields" class="hidden space-y-3">
            <div>
              <label for="reset_username" class="block text-xs font-medium text-gray-700 mb-1">
                Email or ID <span class="text-red-500">*</span>
              </label>
              <div class="relative">
              <input type="text" 
                     id="reset_username" 
                     name="username" 
                     value="{{ old('username') }}"
                     class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent @error('username') border-red-500 @enderror"
                     placeholder="Enter your email or ID">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <i class="fas fa-user text-xs text-gray-400"></i>
                </div>
              </div>
              @error('username')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="user_type" class="block text-xs font-medium text-gray-700 mb-1">
                Account Type <span class="text-red-500">*</span>
              </label>
              <div id="user_type_display" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-xs text-gray-700">
                <span class="text-xs">Account type will be detected automatically</span>
              </div>
              <input type="hidden" id="user_type" name="user_type" value="">
            </div>

            <div class="pt-2">
              <label class="block text-xs font-medium text-gray-700 mb-1">Verification Method</label>
              <div class="flex items-center gap-4 text-sm">
                <label class="flex items-center"><input type="radio" name="reset_method" value="security" checked class="mr-2"> Security Questions</label>
                <label class="flex items-center"><input type="radio" name="reset_method" value="otp" class="mr-2"> Email OTP</label>
              </div>
            </div>

            <!-- Security Questions Section -->
            <div id="securityQuestionsSection" class="space-y-3">
              <div>
                <label for="security_question" class="block text-xs font-medium text-gray-700 mb-1">
                  Security Question <span class="text-red-500">*</span>
                </label>
                <div id="security_question_display" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                  <span class="text-xs">Please enter your username first</span>
                </div>
                <input type="hidden" id="security_question" name="security_question" value="">
              </div>

              <div>
                <label for="security_answer1" class="block text-xs font-medium text-gray-700 mb-1">
                  Your Answer <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <input type="password" 
                         id="security_answer1" 
                         name="security_answer1" 
                         value="{{ old('security_answer1') }}"
                         class="w-full px-3 py-2 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent"
                         placeholder="Enter your answer">
                  <button type="button" onclick="togglePasswordField(this)" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg class="h-4 w-4 text-gray-400 hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                </div>
              </div>

              <div>
                <label for="security_question2" class="block text-xs font-medium text-gray-700 mb-1">
                  Second Security Question <span class="text-red-500">*</span>
                </label>
                <div id="security_question_display2" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-xs text-gray-700">
                  <span class="text-xs">Please enter your email/ID first</span>
                </div>
                <input type="hidden" id="security_question2" name="security_question2" value="">
              </div>

              <div>
                <label for="security_answer2" class="block text-xs font-medium text-gray-700 mb-1">
                  Your Answer <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <input type="password" 
                         id="security_answer2" 
                         name="security_answer2" 
                         value="{{ old('security_answer2') }}"
                         class="w-full px-3 py-2 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent"
                         placeholder="Enter your answer">
                  <button type="button" onclick="togglePasswordField(this)" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg class="h-4 w-4 text-gray-400 hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Back to Login Button (Shared) -->
            <div class="text-right pt-2">
              <button type="button" onclick="switchToLoginMode()" class="text-ogs-navy font-semibold text-xs hover:underline">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Login
              </button>
            </div>

          <!-- OTP Section (Hidden by default) -->
          <div id="otpSection" class="hidden space-y-3">
            <div class="text-center py-4">
              <i class="fas fa-envelope text-4xl text-blue-500 mb-2"></i>
              <p class="text-sm font-medium text-gray-700">Email OTP Verification</p>
            </div>
            <button type="button" id="sendOtpBtn" onclick="sendOtp()" class="w-full px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
              Send OTP to Email
            </button>
            <div id="otpStatus" class="text-sm text-center text-gray-600"></div>
            <div id="otpInputRow" class="hidden space-y-2">
              <label class="block text-xs font-medium text-gray-700">Enter 6-digit Code</label>
              <div class="flex gap-2">
                <input type="text" id="otp_code" maxlength="6" class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint" placeholder="000000">
                <button type="button" onclick="verifyOtp()" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Verify</button>
              </div>
            </div>
            <div class="text-right pt-2">
              <button type="button" onclick="switchToLoginMode()" class="text-ogs-navy font-semibold text-xs hover:underline">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Login
              </button>
            </div>
          </div>

          <!-- Password Reset Fields (Hidden by default) -->
          <div id="passwordResetFields" class="hidden space-y-3">
            <!-- User Info Display -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-2">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <i class="fas fa-check-circle text-xs text-green-400"></i>
                </div>
                <div class="ml-2">
                  <p class="text-xs font-medium text-green-800">
                    Security verified! Set new password.
                  </p>
                </div>
              </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-2">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <i class="fas fa-user text-xs text-blue-400"></i>
                </div>
                <div class="ml-2">
                  <p class="text-xs font-medium text-blue-800" id="userInfoDisplay">
                    <!-- User info will be populated here -->
                  </p>
                </div>
              </div>
            </div>

            <!-- New Password Field -->
            <div>
              <label for="new_password" class="block text-xs font-medium text-gray-700 mb-1">
                New Password <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input type="password" 
                       id="new_password" 
                       name="new_password" 
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent"
                       placeholder="Enter your new password"
                       oninput="validatePasswordMatch()">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" onclick="togglePasswordField(this)">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
              <label for="confirm_password" class="block text-xs font-medium text-gray-700 mb-1">
                Confirm New Password <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent"
                       placeholder="Confirm your new password"
                       oninput="validatePasswordMatch()">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" onclick="togglePasswordField(this)">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

            <div class="text-right pt-2">
              <button type="button" onclick="switchToLoginMode()" class="text-ogs-navy font-semibold text-xs hover:underline">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Login
              </button>
            </div>
          </div>

          <!-- Dynamic Submit Button -->
          <div class="pt-4 sticky bottom-0 bg-white">
            <button type="submit" id="submitButton"
                    class="w-full py-3 px-4 rounded-lg bg-mint hover:bg-teal text-ogs-navy font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98]">
              <i class="fas fa-sign-in-alt mr-2"></i>LOG IN
            </button>
          </div>
        </form>
        </div><!-- End scrollable wrapper -->
      </div>
    </div>
  </div>

  <script>
    // Debug logging function
    function debugLog(message, data = null) {
      console.log(`[LOGIN DEBUG] ${message}`, data || '');
    }

    // Show form error message (replaces alert)
    function showFormError(message) {
      let errorDiv = document.getElementById('formErrorMessage');
      if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'formErrorMessage';
        const form = document.querySelector('form');
        if (form) {
          form.parentElement.insertBefore(errorDiv, form);
        }
      }
      errorDiv.innerHTML = `<div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-3">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <svg class="h-4 w-4 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-2">
            <h3 class="text-xs font-medium text-red-800">Error</h3>
            <div class="mt-1 text-xs text-red-700">${message}</div>
          </div>
        </div>
      </div>`;
      errorDiv.style.display = 'block';
    }

    // Show success message
    function showSecurityMessage(message, type = 'success') {
      let msgDiv = document.getElementById('securitySuccessMessage');
      if (!msgDiv) {
        msgDiv = document.createElement('div');
        msgDiv.id = 'securitySuccessMessage';
        const form = document.querySelector('form');
        if (form) {
          form.parentElement.insertBefore(msgDiv, form);
        }
      }
      const bgColor = type === 'success' ? 'bg-green-50' : 'bg-yellow-50';
      const borderColor = type === 'success' ? 'border-green-200' : 'border-yellow-200';
      const textColor = type === 'success' ? 'text-green-800' : 'text-yellow-800';
      const msgColor = type === 'success' ? 'text-green-700' : 'text-yellow-700';
      
      msgDiv.innerHTML = `<div class="${bgColor} border ${borderColor} rounded-lg p-3 mb-3">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <svg class="h-4 w-4 ${textColor} mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="ml-2">
            <div class="text-xs ${msgColor}">${message}</div>
          </div>
        </div>
      </div>`;
      msgDiv.style.display = 'block';
      setTimeout(() => msgDiv.style.display = 'none', 3000);
    }

    // Initialize debugging
    debugLog('Login page loaded');
    debugLog('Current form action:', '{{ route("login") }}');
    debugLog('CSRF token present:', document.querySelector('input[name="_token"]') ? 'Yes' : 'No');

    // Add event listeners for security question fetching and method toggle
    document.addEventListener('DOMContentLoaded', function() {
      const usernameField = document.getElementById('reset_username');
      const userTypeField = document.getElementById('user_type');
      const loginIdField = document.getElementById('login_id');
      const methodRadios = document.getElementsByName('reset_method');
      
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

      // Add listeners to verification method radio buttons
      methodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
          const secQuestionsDiv = document.getElementById('securityQuestionsSection');
          const otpDiv = document.getElementById('otpSection');
          if (this.value === 'security') {
            if (secQuestionsDiv) secQuestionsDiv.classList.remove('hidden');
            if (otpDiv) otpDiv.classList.add('hidden');
          } else {
            if (secQuestionsDiv) secQuestionsDiv.classList.add('hidden');
            if (otpDiv) otpDiv.classList.remove('hidden');
          }
        });
      });
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
      
      // Accept both supervisor (OGS-S####) and tutor (OGS-T####) IDs
      const idPattern = /^OGS-[ST]\d{4}$/;
      
      // Validate email format
      const emailPattern = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
      
      if (idPattern.test(value) || emailPattern.test(value)) {
        // Valid input
        loginIdField.classList.add('border-green-500');
        validationDiv.classList.add('hidden');
      } else {
        // Invalid input
        loginIdField.classList.add('border-red-500');
        validationDiv.classList.remove('hidden');
      }
    }

    function togglePasswordField(btn) {
      const container = btn.closest('.relative');
      const input = container.querySelector('input[type="password"], input[type="text"]');
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
        showFormError('Form elements not found. Please refresh the page and try again.');
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
        mainDescription.textContent = 'Choose your verification method and follow the steps.';
        debugLog('Title and description updated');
      } else {
        debugLog('Title or description element not found');
      }
      
      // Change submit button
      const submitButton = document.getElementById('submitButton');
      if (submitButton) {
        submitButton.innerHTML = '<i class="fas fa-check mr-2"></i>VERIFY';
        submitButton.type = 'button';
        submitButton.onclick = function(e) {
          e.preventDefault();
          debugLog('Verify button clicked');
          verifyAndReset();
        };
        debugLog('Submit button changed to verify mode');
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
        showFormError('Please fill in both password fields.');
        return;
      }
      
      if (newPassword !== confirmPassword) {
        showFormError('Passwords do not match. Please check the confirmation field.');
        document.getElementById('confirm_password').focus();
        return;
      }
      
      if (newPassword.length < 8) {
        showFormError('Password must be at least 8 characters long.');
        document.getElementById('new_password').focus();
        return;
      }
      
      if (!window.passwordResetUser) {
        showFormError('Session expired. Please try again.');
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
      
      // Determine selected method
      const methodEls = document.getElementsByName('reset_method');
      let selectedMethod = 'security';
      for (const el of methodEls) { if (el.checked) { selectedMethod = el.value; break; } }

      if (selectedMethod === 'security') {
        verifySecurityQuestions();
      } else {
        // OTP flow: show OTP controls and trigger sending OTP
        const otpSection = document.getElementById('otpSection');
        if (otpSection) otpSection.classList.remove('hidden');
        sendOtp();
      }
    }

    function verifySecurityQuestions() {
      debugLog('Verifying security questions via AJAX');
      
      const usernameEl = document.getElementById('reset_username');
      const userTypeEl = document.getElementById('user_type');
      const securityQuestionEl = document.getElementById('security_question');
      const securityAnswerEl = document.getElementById('security_answer1');
      const securityQuestion2El = document.getElementById('security_question2');
      const securityAnswer2El = document.getElementById('security_answer2');
      
      if (!usernameEl || !userTypeEl || !securityQuestionEl || !securityAnswerEl || !securityQuestion2El || !securityAnswer2El) {
        showFormError('Form elements not found. Please refresh and try again.');
        return;
      }
      
      const username = usernameEl?.value;
      const userType = userTypeEl?.value;
      const securityQuestion = securityQuestionEl?.value;
      const securityAnswer = securityAnswerEl?.value;
      const securityQuestion2 = securityQuestion2El?.value;
      const securityAnswer2 = securityAnswer2El?.value;

      // Show errors inline for missing fields
      if (!username || !userType || !securityQuestion || !securityAnswer || !securityQuestion2 || !securityAnswer2) {
        const secQuestDiv = document.getElementById('securityQuestionsSection');
        let errorEl = document.getElementById('securityQuestionsError');
        if (!errorEl) {
          errorEl = document.createElement('div');
          errorEl.id = 'securityQuestionsError';
          errorEl.className = 'bg-red-50 border border-red-200 rounded-lg p-3 mb-3';
          if (secQuestDiv) secQuestDiv.insertBefore(errorEl, secQuestDiv.firstChild);
        }
        errorEl.innerHTML = '<p class="text-sm text-red-600">Please fill in all fields.</p>';
        return;
      }

      // Submit via AJAX to show errors inline
      fetch('{{ route("password.reset.request") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          username: username,
          user_type: userType,
          security_question: securityQuestion,
          security_answer1: securityAnswer,
          security_question2: securityQuestion2,
          security_answer2: securityAnswer2
        })
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          // Verified! Show password reset fields
          window.passwordResetUser = { username: username, userType: userType };
          switchToPasswordResetMode();
        } else {
          // Show errors inline
          const secQuestDiv = document.getElementById('securityQuestionsSection');
          let errorEl = document.getElementById('securityQuestionsError');
          if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.id = 'securityQuestionsError';
            errorEl.className = 'bg-red-50 border border-red-200 rounded-lg p-3 mb-3';
            if (secQuestDiv) secQuestDiv.insertBefore(errorEl, secQuestDiv.firstChild);
          }
          let msg = data.message || 'Verification failed.';
          errorEl.innerHTML = '<p class="text-sm text-red-600">' + msg + '</p>';
          debugLog('Verification error:', msg);
        }
      })
      .catch(err => {
        debugLog('AJAX error', err);
        showFormError('Server error. Please try again.');
      });
    }

    function sendOtp() {
      const username = document.getElementById('reset_username').value;
      const otpSection = document.getElementById('otpSection');
      const status = document.getElementById('otpStatus');
      
      if (!username) {
        if (status) status.innerHTML = '<p class="text-sm text-red-600">Please enter your email or ID first.</p>';
        return;
      }

      if (status) status.textContent = 'Sending OTP...';

      fetch('{{ route("password.reset.otp.send") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ username: username })
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          if (status) status.innerHTML = '<p class="text-sm text-green-600">✓ OTP sent to your email.</p>';
          const otpInputRow = document.getElementById('otpInputRow');
          if (otpInputRow) otpInputRow.classList.remove('hidden');
        } else {
          if (status) status.innerHTML = '<p class="text-sm text-red-600">' + (data.message || 'Failed to send OTP.') + '</p>';
        }
      })
      .catch(err => { debugLog('OTP send error', err); if (status) status.innerHTML = '<p class="text-sm text-red-600">Server error sending OTP.</p>'; });
    }

    function verifyOtp() {
      const username = document.getElementById('reset_username').value;
      const otp = document.getElementById('otp_code').value;
      const otpSection = document.getElementById('otpSection');
      const status = document.getElementById('otpStatus');
      
      if (!username || !otp) {
        if (status) status.innerHTML = '<p class="text-sm text-red-600">Please provide email/ID and OTP code.</p>';
        return;
      }

      // Submit via AJAX to verify OTP and set session
      fetch('{{ route("password.reset.otp.verify") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          username: username,
          otp: otp
        })
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          // OTP verified! Show password reset fields
          window.passwordResetUser = { username: username, userType: 'auto' };
          if (otpSection) otpSection.classList.add('hidden');
          switchToPasswordResetMode();
        } else {
          if (status) status.innerHTML = '<p class="text-sm text-red-600">' + (data.message || 'Invalid OTP. Please try again.') + '</p>';
        }
      })
      .catch(err => { debugLog('OTP verify error', err); if (status) status.innerHTML = '<p class="text-sm text-red-600">Server error. Please try again.</p>'; });
    }
  </script>

</body>
</html>