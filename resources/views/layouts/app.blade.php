<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'GLS Scheduling')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex">
        <!-- Sidebar -->
        <div class="group h-full bg-white shadow-lg border-r border-gray-200 transition-all duration-300 w-20 hover:w-72">
            @include('layouts.sidebar')
        </div>

        <!-- Main Content -->
        <main class="flex-1 bg-gray-50 p-6">
            @yield('content')
        </main>
    </div>
</body>
</html>
