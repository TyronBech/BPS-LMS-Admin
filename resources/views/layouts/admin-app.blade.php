<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="BPS Library Management System - Bicutan Parochial School's digital library platform for managing books, users, and resources with an easy-to-use online dashboard.">
  <meta name="keywords" content="BPS, Library, Management, System, Books Management, User Management, Admin Dashboard">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="author" content="OwlQuery">
  <link rel="icon" href="{{ asset('img/BPSLogo.png') }}">
  <title>BPS Library Management System</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    :root {
      <?php
        use App\Helpers\ColorHelper;
        use Illuminate\Support\Facades\Log;

        $colors = [
            'primary' => $settings->theme_colors['primary'] ?? '#20246c',
            'secondary' => $settings->theme_colors['secondary'] ?? '#3F83F8',
            'tertiary' => $settings->theme_colors['tertiary'] ?? '#C27803',
        ];
        Log::info('Theme Colors: ' . json_encode($colors));
      ?>

      <?php foreach($colors as $name => $hex): ?>
        <?php foreach(ColorHelper::generatePalette($hex) as $shade => $rgbValue): ?>
          --color-<?= $name ?>-<?= $shade ?>: <?= $rgbValue ?>;
        <?php endforeach; ?>
      <?php endforeach; ?>
    }
  </style>
</head>

<body class="relative bg-secondary-500 dark:bg-gray-900 text-gray-900 dark:text-white">
  @include('layouts.admin-header')
  <main id="main-app" class="container relative mx-auto px-2 font-sans flex flex-col items-center">
    <div id="form-loader" class="loader-overlay hidden">
      <div class="loader"></div>
    </div>
    @include('layouts.toast')
    @yield('content')
    @include('layouts.FAQs')
  </main>
  @include('layouts.footer')
  @yield('scripts')
</body>

</html>