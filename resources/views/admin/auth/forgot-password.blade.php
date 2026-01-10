<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>BPS Library Management System</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="icon" href="{{ asset('img/BPSLogo.png') }}">
  <style>
    :root {
      --loader-dot-default: {{ ($settings->theme_colors ?? [])['tertiary'] ?? '#C27803' }};
      <?php
        use App\Helpers\ColorHelper;

        $colors = [
            'primary' => ($settings->theme_colors ?? [])['primary'] ?? '#20246c',
            'secondary' => ($settings->theme_colors ?? [])['secondary'] ?? '#EBF5FF',
            'tertiary' => ($settings->theme_colors ?? [])['tertiary'] ?? '#C27803',
        ];
      ?>

      <?php foreach($colors as $name => $hex): ?>
        <?php foreach(ColorHelper::generatePalette($hex) as $shade => $rgbValue): ?>
          --color-<?= $name ?>-<?= $shade ?>: <?= $rgbValue ?>;
        <?php endforeach; ?>
      <?php endforeach; ?>
    }
  </style>
</head>

<body class="relative bg-secondary-500 dark:bg-gray-900">
  <header>
    <div class="bg-primary-500 border-gray-200 dark:bg-primary-500 min-h-36 pt-5">
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
    <div class="flex flex-col items-center justify-center h-[calc(100vh-17rem)] w-full">
      <img class="w-20 h-20 block mb-4 dark:hidden" src="{{ asset('img/OwlQuery.png') }}" alt="OwlQuery">
      <img class="hidden dark:block w-20 h-20 mb-4" src="{{ asset('img/OwlQuery Dark.png') }}" alt="OwlQuery">
      <div class="block max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <p class="font-normal mb-4 text-gray-700 dark:text-gray-400">Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.</p>
        @if(session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
          Email sent successfully!
        </div>
        @endif
        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
          @csrf
          <div class="mb-5">
            <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email:</label>
            <input type="email" id="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-400 dark:focus:border-primary-400" placeholder="sample@me.com" value="{{ old('email') }}" required autofocus />
          </div>
          @error('email')
          <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">{{ $message }}</span>
          </div>
          @enderror
          <div class="flex justify-end w-full">
            <button type="submit" class="text-white uppercase bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:ring-primary-400 font-medium rounded-lg text-xs px-5 py-2.5 mb-2 dark:bg-primary-400 dark:hover:bg-primary-500 focus:outline-none dark:focus:ring-primary-500">Send Link</button>
          </div>
        </form>
      </div>
    </div>
  </main>
  @include('layouts.footer')
</body>

</html>