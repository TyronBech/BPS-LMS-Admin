<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="icon" href="{{ asset('img/BPSLogo.png') }}">
  <title>404 Not Found | BPS LMS</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap');

    .rubik-mono-one {
      font-family: "Rubik Mono One", system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
    }

    @keyframes float {

      0%,
      100% {
        transform: translateY(0);
      }

      50% {
        transform: translateY(-6px);
      }
    }

    .float {
      animation: float 4s ease-in-out infinite;
    }
  </style>
</head>

<body class="bg-gradient-to-br from-sky-50 to-indigo-100 dark:from-gray-950 dark:to-slate-900 text-gray-800 dark:text-slate-100 min-h-screen">
  <main class="min-h-screen flex items-center justify-center px-6 py-12">
    <div class="w-full max-w-3xl">
      <!-- Card -->
      <div class="relative overflow-hidden rounded-2xl border border-slate-200/70 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/70 shadow-xl">
        <!-- Brand bar -->
        <div class="flex items-center justify-between px-6 py-4 bg-slate-900 text-white dark:bg-slate-800">
          <div class="flex items-center gap-3">
            <img src="{{ asset('img/BPSLogo.png') }}" alt="BPS Logo" class="h-8 w-8 rounded-sm">
            <span class="text-sm font-semibold">BPS Library Management System</span>
          </div>
          <span class="text-xs opacity-80">Error 404</span>
        </div>

        <!-- Content -->
        <div class="p-8 sm:p-10">
          <div class="grid grid-cols-1 sm:grid-cols-5 gap-8 items-center">
            <!-- Illustration -->
            <div class="sm:col-span-2 mx-auto float">
              <svg width="220" height="160" viewBox="0 0 440 320" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                class="drop-shadow-sm">
                <defs>
                  <linearGradient id="g1" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#38bdf8" />
                    <stop offset="100%" stop-color="#6366f1" />
                  </linearGradient>
                </defs>
                <rect x="20" y="40" rx="18" ry="18" width="260" height="200" fill="url(#g1)" opacity="0.15" />
                <rect x="40" y="60" rx="14" ry="14" width="260" height="200" fill="url(#g1)" opacity="0.25" />
                <rect x="60" y="80" rx="16" ry="16" width="260" height="200" fill="url(#g1)" opacity="0.35" />
                <g class="rubik-mono-one" fill="none" stroke="url(#g1)" stroke-width="10" stroke-linecap="round"
                  stroke-linejoin="round">
                  <!-- Left 4 -->
                  <path d="M95 220 v-60 h-30 v-20 l50 -60 h20 v60 h15 v20 h-15 v60 z" />
                  <!-- 0 -->
                  <ellipse cx="170" cy="150" rx="45" ry="70"></ellipse>
                  <!-- Right 4 -->
                  <path d="M220 220 v-60 h-30 v-20 l50 -60 h20 v60 h15 v20 h-15 v60 z" />
                </g>
              </svg>
            </div>

            <!-- Copy -->
            <div class="sm:col-span-3">
              <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">
                Page not found
              </h1>
              <p class="mt-3 text-slate-600 dark:text-slate-300">
                The page you’re looking for doesn’t exist or may have been moved. Please check the URL, or use the options
                below to continue.
              </p>

              <!-- Actions -->
              <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <a href="{{ url('/') }}"
                  class="inline-flex items-center justify-center rounded-lg bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900 px-5 py-3 text-sm font-semibold shadow hover:opacity-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-slate-400 dark:focus-visible:ring-slate-700">
                  Go to Home
                </a>
                <button type="button" onclick="history.back()"
                  class="inline-flex items-center justify-center rounded-lg px-5 py-3 text-sm font-semibold border border-transparent hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none">
                  Go back
                </button>
              </div>

              <!-- Meta -->
              <p class="mt-6 text-xs text-slate-500 dark:text-slate-400">
                Error code: 404 • If you believe this is an error, please contact your administrator.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Subtle footer -->
      <div class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400">
        © {{ date('Y') }} BPS Library Management System
      </div>
    </div>
  </main>

  <span class="sr-only">404 - Page not found</span>
</body>

</html>