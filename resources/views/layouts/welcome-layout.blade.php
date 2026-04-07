<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $settings->org_initial ?? 'BPS' }} Library Management System</title>
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
  @include('layouts.welcome-layout-header')
  <main class="container relative mx-auto px-2 font-sans flex-col">
    @yield('content')
  </main>
  @include('layouts.footer')
  @yield('scripts')
</body>
</html>
