<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OGS - Application Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-cover bg-center px-4 
    bg-[url('{{ asset('images/application/cancelphone.png') }}')] 
    sm:bg-[url('{{ asset('images/application/cancelbg.png') }}')]">

    <div class="flex flex-col items-center text-center space-y-6 max-w-lg w-full">

        <!-- Image -->
        <img src="{{ asset('images/application/sad.png') }}" alt="sad"
            class="w-20 h-20 sm:w-28 sm:h-28 object-contain">

        <!-- Header -->
        <h1 class="text-2xl sm:text-4xl font-bold text-[#1F2937]">
            Application Cancelled
        </h1>

        <!-- Subtext -->
        <p class="text-sm sm:text-base text-[#374151] leading-relaxed px-2">
            Your application has been cancelled. No information was saved. 
            You can start again anytime — we’d love to see you join OGS!
        </p>

        <!-- Button -->
        <a href="{{ route('landing') }}"
            class="w-full sm:w-64 py-2 text-center rounded-full bg-[#9CA3AF] text-white font-semibold text-sm sm:text-base hover:bg-[#7B8790] hover:scale-105 transition-transform duration-200 inline-block">
            Back Home
        </a>

    </div>

</body>

</html>
