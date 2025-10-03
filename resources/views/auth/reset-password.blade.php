<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OGS Connect - Reset Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'ogs-navy': '#0E335D',
            'mint': '#9DC9FD',
            'teal': '#7DD3FC'
          }
        }
      }
    }
  </script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-teal-50 min-h-screen">
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-8">
    <div class="text-center">
      <h1 class="text-blue-950 text-2xl sm:text-3xl lg:text-4xl font-bold mb-3">
        Set New Password
      </h1>
      <p class="text-neutral-800 text-sm sm:text-base font-medium leading-relaxed">
        Please enter your new password below.
      </p>
    </div>

    <form method="POST" action="{{ route('password.reset.store') }}" class="space-y-4 sm:space-y-5">
      @csrf
      
      <!-- Error Messages -->
      @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                Please correct the following errors:
              </h3>
              <div class="mt-2 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            </div>
          </div>
        </div>
      @endif

      <!-- Success Message -->
      @if (session('status'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-green-800">
                {{ session('status') }}
              </p>
            </div>
          </div>
        </div>
      @endif

      <!-- User Info Display -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-user text-blue-400"></i>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-blue-800">
              <strong>{{ ucfirst($user_type) }}:</strong> {{ $user_name }}
            </p>
            <p class="text-sm text-blue-600">
              {{ $username }}
            </p>
          </div>
        </div>
      </div>

      <!-- Hidden fields for form submission -->
      <input type="hidden" name="username" value="{{ $username }}">
      <input type="hidden" name="user_type" value="{{ $user_type }}">

      <!-- New Password Field -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
          New Password <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <input type="password" 
                 id="password" 
                 name="password" 
                 required
                 class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent @error('password') border-red-500 @enderror"
                 placeholder="Enter your new password">
          <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <button type="button" onclick="togglePasswordVisibility('password')" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-eye" id="password-eye"></i>
            </button>
          </div>
        </div>
        @error('password')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
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
                 required
                 class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent @error('password_confirmation') border-red-500 @enderror"
                 placeholder="Confirm your new password">
          <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <button type="button" onclick="togglePasswordVisibility('password_confirmation')" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-eye" id="password_confirmation-eye"></i>
            </button>
          </div>
        </div>
        @error('password_confirmation')
          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <!-- Submit Button -->
      <button type="submit"
              class="w-full py-3 sm:py-4 lg:py-5 rounded-lg sm:rounded-xl bg-mint hover:bg-teal text-ogs-navy font-bold text-base sm:text-lg lg:text-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98]">
        <i class="fas fa-key mr-2"></i>
        UPDATE PASSWORD
      </button>

      <!-- Back to Login -->
      <div class="text-center">
        <a href="{{ route('login') }}" class="text-ogs-navy font-semibold text-xs sm:text-sm hover:underline">
          <i class="fas fa-arrow-left mr-1"></i>
          Back to Login
        </a>
      </div>
    </form>
  </div>
</div>

<script>
  function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const eyeIcon = document.getElementById(fieldId + '-eye');
    
    if (field.type === 'password') {
      field.type = 'text';
      eyeIcon.classList.remove('fa-eye');
      eyeIcon.classList.add('fa-eye-slash');
    } else {
      field.type = 'password';
      eyeIcon.classList.remove('fa-eye-slash');
      eyeIcon.classList.add('fa-eye');
    }
  }
</script>
</body>
</html>
