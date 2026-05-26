<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dark Mode Auto Load -->
    <script>
        if (localStorage.theme === 'dark' ||
            (!('theme' in localStorage) &&
                window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 transition-colors duration-300">

<div class="min-h-screen">

    <!-- NAVIGATION -->
    @include('layouts.navigation')

    <!-- HEADER -->
    @isset($header)
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-gray-900 dark:text-white">
                {{ $header }}
            </div>
        </header>
    @endisset

    <!-- PAGE CONTENT -->
    <main class="p-6">
        <div class="max-w-7xl mx-auto">
            {{ $slot }}
        </div>
    </main>

</div>

<!-- DARK MODE TOGGLE SCRIPT -->
<script>
    function toggleTheme() {
        const html = document.documentElement;

        if (html.classList.contains('dark')) {
            html.classList.remove('dark');
            localStorage.theme = 'light';
        } else {
            html.classList.add('dark');
            localStorage.theme = 'dark';
        }
    }
</script>

</body>
</html>