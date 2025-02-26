<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>BPS Library Management System</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative bg-blue-50 dark:bg-gray-900">
  @include('layouts.welcome-layout-header')
  <main class="container relative mx-auto px-2 font-sans flex-col">
    @yield('content')
  </main>
  @include('layouts.footer')
  @yield('scripts')
</body>
</html>