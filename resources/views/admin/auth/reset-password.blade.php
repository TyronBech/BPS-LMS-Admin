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
    @include('layouts.toast')
    <div class="flex flex-col items-center justify-center h-[calc(100vh-17rem)] w-full">
      <img class="w-20 h-20 block mb-4 dark:hidden" src="{{ asset('img/OwlQuery.png') }}" alt="OwlQuery">
      <img class="hidden dark:block w-20 h-20 mb-4" src="{{ asset('img/OwlQuery Dark.png') }}" alt="OwlQuery">
      <div class="block max-w-lg p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <form action="{{ route('password.store') }}" method="POST" class="w-96">
          @csrf
          <input type="hidden" name="token" value="{{ $request->route('token') }}">
          <div class="mb-5">
            <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email:</label>
            <input type="email" id="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="name@example.com" value="{{ old('email') }}" required autofocus />
            @error('email')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <!-- Password Field with Toggle -->
          <div class="mb-5 relative">
            <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password:</label>
            <input type="password" id="password" name="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pr-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ old('password') }}" required />
            <!-- Toggle button -->
            <button type="button" id="togglePassword" class="absolute right-3 top-[38px] text-gray-500 dark:text-white" aria-label="Toggle password visibility">
              <span id="openEye1" class="hidden">
                <svg class="w-6 h-6 text-gray-800 dark:text-white" fill="none" viewBox="0 0 24 24">
                  <path stroke="currentColor" stroke-width="2"
                    d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" />
                  <path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </span>
              <span id="closedEye1">
                <svg class="w-6 h-6 text-gray-800 dark:text-white" fill="none" viewBox="0 0 24 24">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </span>
            </button>
            @error('password')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <!-- Confirm Password Field with Toggle -->
          <div class="mb-5 relative">
            <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Confirm Password:</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pr-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ old('password_confirmation') }}" required />
            <!-- Toggle button -->
            <button type="button" id="togglePasswordConfirmation" class="absolute right-3 top-[38px] text-gray-500 dark:text-white" aria-label="Toggle confirm password visibility">
              <span id="openEye2" class="hidden">
                <svg class="w-6 h-6 text-gray-800 dark:text-white" fill="none" viewBox="0 0 24 24">
                  <path stroke="currentColor" stroke-width="2"
                    d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" />
                  <path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </span>
              <span id="closedEye2">
                <svg class="w-6 h-6 text-gray-800 dark:text-white" fill="none" viewBox="0 0 24 24">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
              </span>
            </button>
            @error('password_confirmation')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <div class="flex justify-end w-full">
            <button type="submit" class="text-white uppercase bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-xs px-5 py-2.5 mb-2 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">Reset Password</button>
          </div>
        </form>
      </div>
    </div>
  </main>
  @include('layouts.footer')
  <script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const openEye = document.getElementById('openEye1');
    const closedEye = document.getElementById('closedEye1');

    togglePassword.addEventListener('click', function() {
      const isPassword = passwordInput.type === 'password';
      passwordInput.type = isPassword ? 'text' : 'password';

      // Toggle icons
      openEye.classList.toggle('hidden', !isPassword);
      closedEye.classList.toggle('hidden', isPassword);
    });
    const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    const openEye2 = document.getElementById('openEye2');
    const closedEye2 = document.getElementById('closedEye2');

    togglePasswordConfirmation.addEventListener('click', function() {
      const isPassword = passwordConfirmationInput.type === 'password';
      passwordConfirmationInput.type = isPassword ? 'text' : 'password';

      // Toggle icons
      openEye2.classList.toggle('hidden', !isPassword);
      closedEye2.classList.toggle('hidden', isPassword);
    });
  </script>
</body>
</html>