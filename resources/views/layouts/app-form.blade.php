<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ trim(($settings->org_initial ?? 'BPS') . ' ' . config('app.name')) }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="icon" href="{{ $settings->getOrgLogoBase64Attribute() }}">
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
    <div class="bg-primary-500 border-gray-200 dark:bg-primary-500 min-h-20 sm:min-h-24 md:min-h-28 lg:min-h-30 py-3 sm:py-4 md:py-5">
      <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto px-4 sm:px-6">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 rtl:space-x-reverse">
          <img class="rounded-full w-16 h-16 md:w-20 md:h-20" src="{{ $settings->getOrgLogoBase64Attribute() }}" alt="School Logo">
          <div class="flex flex-col justify-center">
            <h1 class="text-sm md:text-lg lg:text-xl text-white font-semibold text-start">{{ $settings->org_name ?? 'School Name' }}</h1>
            <hr class="h-px bg-gray-200 border-0 my-1">
            <h1 class="text-xs md:text-base lg:text-lg text-white font-semibold text-start">{{ config('app.name') }} - Library Management System</h1>
          </div>
        </a>
      </div>
    </div>
  </header>
  <main class="container relative mx-auto my-4 px-2 sm:px-4 md:px-6 lg:px-8 font-sans flex-col">
    @include('layouts.toast')
    @yield('content')
  </main>
  @include('layouts.footer')
  @yield('scripts')
</body>

</html>
