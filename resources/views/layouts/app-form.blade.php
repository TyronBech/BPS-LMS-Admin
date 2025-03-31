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

<body class="relative bg-blue-50 dark:bg-gray-900">
  <header>
      <div class="bg-bpsBlue border-gray-200 dark:bg-bpsBlue min-h-36 pt-5">
        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto">
          <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 rtl:space-x-reverse">
            <img class="rounded-full w-24 h-24" src="{{ asset('img/BPSLogo.png') }}" alt="School Logo">
            <div class="flex flex-col justify-center">
              <h1 class="lg:text-2xl md:text-lg text-white font-semibold text-center">Bicutan Parochial School</h1>
              <hr class="h-px bg-gray-200 border-0">
              <h1 class="lg:text-xl md:text-md text-white font-semibold text-center">Library Management System</h1>
            </div>
          </a>
        </div>
      </div>
  </header>
  <main class="container relative mx-auto my-4 px-2 font-sans flex-col">
    @include('layouts.toast')
    @yield('content')
  </main>
  @include('layouts.footer')
  @yield('scripts')
</body>

</html>