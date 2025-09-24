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
<body class="min-h-screen bg-gradient-to-b from-emerald-300 to-[#7CA6D7] flex flex-col md:flex-row relative overflow-hidden">

  <!-- Home page button fixed top-left -->
  <div class="fixed top-4 left-4 md:top-8 md:left-8 z-50">
    <a href="{{ url('/') }}"
      class="flex items-center text-ogs-navy font-bold hover:text-slate-700 transition-colors text-sm md:text-base">
      <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 19l-7-7 7-7"></path>
      </svg>
      Home page
    </a>
  </div>

  <!-- Left Divider -->
  <div class="flex-1 flex flex-col items-center justify-center px-6 pt-16 md:pl-12 md:pt-28 text-center md:text-left relative">
    <!-- Welcome Text + Image (Desktop) -->
    <div class="hidden md:block z-10">
      <div class="text-blue-950 text-2xl md:text-5xl font-extrabold leading-snug mb-2">
        WELCOME TO <br/>OGS CONNECT
      </div>
      <div class="text-green-900 text-base md:text-xl font-medium mb-6">
        A Centralized Management System
      </div>
    </div>
    <!-- Desktop Image -->
    <img class="hidden md:block w-[500px] h-[500px] object-contain mt-4" 
         src="{{ asset('images/login-image.png') }}" alt="Login Illustration"/>
  </div>

  <!-- Right Divider (Login Form Desktop) -->
  <div class="hidden md:flex flex-1 items-center justify-center px-4 py-10 md:py-0">
    <div class="w-full max-w-md md:max-w-lg bg-neutral-100 rounded-2xl md:rounded-3xl shadow-2xl p-6 sm:p-10 md:p-12 flex flex-col justify-between" style="height: auto; max-height: 550px;">

      <div class="w-full flex flex-col items-center mb-6">
        <div class="text-center mt-2 flex flex-row items-center space-x-3">
          <img class="w-12 h-12 md:w-14 md:h-14" src="{{ asset('images/logo.png') }}" alt="Logo" />
          <div>
            <span class="text-blue-950 text-lg md:text-xl font-bold block">
              OUTSOURCING
            </span>
            <span class="text-blue-950 text-xs md:text-sm font-bold block">
              GLOBAL SOLUTIONS
            </span>
          </div>
        </div>
      </div>

      <div class="text-blue-950 text-xl md:text-2xl font-bold mb-2 text-center">
        Log in to your Account
      </div>
      <p class="text-neutral-800 text-sm md:text-base font-medium mb-4 leading-relaxed text-center">
        Provide your login details below to securely access your OGS Connect account.
      </p>

      <!-- Login Form -->
      <form class="space-y-3 md:space-y-4 flex-1 flex flex-col justify-center">
        <!-- Email -->
        <div class="relative">
          <i class="fa-solid fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
          <input type="email" placeholder="Email" required
                 class="w-full pl-10 pr-4 py-3 bg-white border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint text-sm md:text-base">
        </div>

        <!-- Password -->
        <div class="relative">
          <i class="fa-solid fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
          <input type="password" id="passwordDesktop" placeholder="Password" required
                 class="w-full pl-10 pr-10 py-3 bg-white border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-mint text-sm md:text-base">
          <i class="fa-solid fa-eye absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 cursor-pointer" onclick="togglePassword('passwordDesktop', this)"></i>
        </div>

        <div class="flex justify-end">
          <a href="#" class="text-ogs-navy font-semibold text-xs md:text-sm hover:underline">Forgot Password?</a>
        </div>

        <button type="submit"
                class="w-full py-3 md:py-4 rounded-full bg-mint hover:bg-teal text-ogs-navy font-bold text-base md:text-lg shadow-lg hover:shadow-xl transition-all duration-200">
          LOG IN
        </button>
      </form>
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
