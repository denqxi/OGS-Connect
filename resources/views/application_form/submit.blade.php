<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OGS - Application Submitted</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-center px-4
    bg-[url('{{ asset('images/application/successphone.png') }}')] 
    sm:bg-[url('{{ asset('images/application/successbg.png') }}')] 
    bg-cover">

    <div class="flex flex-col items-center text-center space-y-6 max-w-lg w-full">

        <!-- Image -->
        <img src="{{ asset('images/application/conf.png') }}" alt="Logo"
            class="w-20 h-20 sm:w-28 sm:h-28 object-contain">

        <!-- Header -->
        <h1 class="text-2xl sm:text-4xl font-bold text-[#1F2937]">
            Application Submitted!
        </h1>

        <!-- Subtext -->
        <p class="text-sm sm:text-base text-[#374151] leading-relaxed px-2">
            @if(session('success'))
                {{ session('success') }}
            @else
                Your application has been submitted successfully. 
                We'll review your details and contact you soon for the next step. 
                Thank you for applying to OGS!
            @endif
        </p>

        <!-- Button -->
        <a href="{{ route('landing') }}"
            class="w-full sm:w-64 py-2 text-center rounded-full bg-[#65DB7F] text-white font-semibold text-sm sm:text-base hover:bg-[#3CB45C] hover:scale-105 transition-transform duration-200 inline-block">
            Back Home
        </a>

    </div>

</body>

</html>
