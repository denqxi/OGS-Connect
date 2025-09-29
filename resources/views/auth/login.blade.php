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
<body class="min-h-screen bg-gradient-to-b from-emerald-300 to-[#7CA6D7] flex">

  <!-- Left Divider -->
  <div class="flex-1 flex flex-col items-center justify-center pl-12 pt-28">
    
    <!-- Home page button fixed top-left -->
    <div class="fixed top-8 left-8 z-50">
      <a href="{{ url('/') }}"
        class="flex items-center text-ogs-navy font-bold hover:text-slate-700 transition-colors">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 19l-7-7 7-7"></path>
        </svg>
        Home page
      </a>
    </div>

    <!-- Welcome Text -->
    <div class="w-[714px] h-36 text-blue-950 text-6xl font-extrabold">
      WELCOME TO <br/>OGS CONNECT
    </div>
    <div class="w-[662px] h-11 justify-start text-green-900 text-2xl font-medium">
      A Centralized Management System
    </div>
    <img class="w-[580px] h-[580px]" src="{{ asset('images/login-image.png') }}" />
  </div>

  <!-- Right Divider (Login Form) -->
  <div class="flex-1 flex items-center justify-center relative">

    <!-- Login Card -->
    <div class="h-[850px] w-[1000px] max-w-xl bg-neutral-100 rounded-3xl shadow-2xl p-16 flex flex-col justify-center">
      <div class="w-full flex flex-col items-center mb-12">
        <!-- Header text -->
        <div class="text-center mt-2 flex flex-row">
          <div>
            <img class="w-20 h-20" src="{{ asset('images/logo.png') }}" alt="Logo" />
          </div>
          <div>
            <span class="text-blue-950 text-2xl font-bold block">
              OUTSOURCING
            </span>
            <span class="text-blue-950 text-base font-bold block">
              GLOBAL SOLUTIONS
            </span>
          </div>
        </div>
      </div>

      <div class="w-[520px] h-11 justify-start text-blue-950 text-4xl font-bold">
        Log in to your Account
      </div>
      <p class="text-neutral-800 text-sm font-medium mb-8 leading-relaxed">
        Provide your login details below to securely access your OGS Connect account.
      </p>

      <!-- Login Form -->
      <form method="POST" action="{{ route('login') }}" class="space-y-3 md:space-y-4 flex-1 flex flex-col justify-center">
        @csrf
        
        <!-- Error Messages -->
        @if ($errors->any())
          <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                  Login Failed
                </h3>
                <div class="mt-1 text-sm text-red-700">
                  @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        @endif
        
        <!-- Unified ID/Email Field -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 7.89a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
          </div>
          <input type="text" name="login_id" placeholder="Supervisor/Tutor ID or Email" required
                  class="w-full pl-12 pr-4 py-5 bg-white border border-stone-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent transition-all duration-200">
        </div>
        <!-- Password -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
          </div>
          <input type="password" name="password" placeholder="Password" required
                  class="w-full pl-12 pr-12 py-5 bg-white border border-stone-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-mint focus:border-transparent transition-all duration-200">
          <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
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
        <div class="flex justify-end">
          <a href="#" class="text-ogs-navy font-semibold text-xs md:text-sm hover:underline">Forgot Password?</a>
        </div>
        <button type="submit"
                class="w-full py-5 rounded-xl bg-mint hover:bg-teal text-ogs-navy font-bold text-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98]">
          LOG IN
        </button>
      </form>
      <script>
        function togglePasswordField(btn) {
          const input = btn.parentElement.parentElement.querySelector('input[type="password"], input[type="text"]');
          if (input.type === 'password') {
            input.type = 'text';
            btn.querySelector('svg').classList.add('text-mint');
          } else {
            input.type = 'password';
            btn.querySelector('svg').classList.remove('text-mint');
          }
        }
      </script>
    </div>
  </div>

  <!-- Mobile Layout -->
  <div class="md:hidden flex flex-col items-center px-6 relative h-screen overflow-hidden">
    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12 w-auto mt-6">

    <div class="text-blue-950 text-2xl font-extrabold leading-snug mt-2 text-center">
      WELCOME TO <br/>OGS CONNECT
    </div>
    <div class="text-green-900 text-sm font-medium mb-4 text-center">
      A Centralized Management System
    </div>

    <div class="relative w-full flex flex-col items-center">
      <img class="w-52 h-52 object-contain" src="{{ asset('images/login-image.png') }}" alt="Login Illustration"/>
      <div class="absolute top-1/2 translate-y-0 w-[90%] bg-white/80 backdrop-blur-md rounded-2xl p-6 shadow-xl">
        <div class="text-blue-950 text-lg font-bold mb-2 text-center">Log in to your Account</div>
        
        <!-- Error Messages for Mobile -->
        @if ($errors->any())
          <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-3">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-4 w-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div class="ml-2">
                <h3 class="text-xs font-medium text-red-800">
                  Login Failed
                </h3>
                <div class="mt-1 text-xs text-red-700">
                  @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        @endif
        
        <form class="space-y-3">
          <!-- Email -->
          <div class="relative">
            <i class="fa-solid fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <input type="email" placeholder="Email" required
                   class="w-full pl-10 pr-4 py-3 bg-white border border-stone-300 rounded-lg focus:outline-none text-sm">
          </div>

          <!-- Password -->
          <div class="relative">
            <i class="fa-solid fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <input type="password" id="passwordMobile" placeholder="Password" required
                   class="w-full pl-10 pr-10 py-3 bg-white border border-stone-300 rounded-lg focus:outline-none text-sm">
            <i class="fa-solid fa-eye absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 cursor-pointer" onclick="togglePassword('passwordMobile', this)"></i>
          </div>

          <div class="flex justify-end">
            <a href="#" class="text-ogs-navy font-semibold text-xs hover:underline">Forgot Password?</a>
          </div>

          <button type="submit"
                  class="w-full py-3 rounded-full bg-mint hover:bg-teal text-ogs-navy font-bold text-sm shadow-md">
            LOG IN
          </button>
        </form>
      </div>
    </div>
  </div>

  <script>
    function togglePassword(inputId, icon) {
      const input = document.getElementById(inputId);
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }
  </script>

</body>
</html>