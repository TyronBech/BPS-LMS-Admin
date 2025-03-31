<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>BPS Library Management System</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="icon" href="{{ asset('img/BPSLogo.png') }}"> 
</head>
<body class="relative bg-blue-50 dark:bg-gray-900 text-gray-900 dark:text-white">
  @include('layouts.admin-header')
  <main class="container relative mx-auto px-2 font-sans flex flex-col items-center">
    @include('layouts.toast')
    @yield('content')
  </main>
  @include('layouts.footer')
  @yield('scripts')
</body>
</html>