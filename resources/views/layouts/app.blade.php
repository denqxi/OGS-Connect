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
        <aside class="w-72 bg-white h-screen shadow-md">
            @include('layouts.sidebar')
        </aside>

        <!-- Main Content -->
        <main class="flex-1 bg-gray-50 p-6">
            @yield('content')
        </main>
    </div>
</body>
</html>
