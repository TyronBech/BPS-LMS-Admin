<header class="sticky top-0 z-50">
  <nav class="bg-primary-500 border-gray-200 dark:bg-primary-500">
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
      <a href="{{ route('main-welcome') }}" class="flex items-center space-x-3 rtl:space-x-reverse">
        <img class="rounded-full w-16 h-16 md:w-20 md:h-20" src="{{ $settings->getOrgLogoBase64Attribute() }}" alt="School Logo">
        <div class="flex flex-col justify-center">
          <h1 class="text-sm md:text-lg lg:text-xl text-white font-semibold text-start">{{ $settings->org_name ?? 'School Name' }}</h1>
          <hr class="h-px my-1 bg-gray-200 border-0">
          <h1 class="text-xs md:text-base lg:text-lg text-white font-semibold text-start">{{ config('app.name') }} - Library Management System</h1>
        </div>
      </a>
      <button data-collapse-toggle="navbar-dropdown" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-100 rounded-lg lg:hidden hover:bg-gray-100/20 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600" aria-controls="navbar-dropdown" aria-expanded="false">
        <span class="sr-only">Open main menu</span>
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15" />
        </svg>
      </button>
      <div class="hidden w-full lg:block lg:w-auto" id="navbar-dropdown">
        <ul class="flex flex-col font-medium p-4 lg:p-0 mt-4 border border-gray-100 rounded-lg bg-primary-500 lg:flex-row lg:space-x-8 rtl:space-x-reverse lg:mt-0 lg:border-0 lg:bg-primary-500 dark:bg-primary-500 lg:dark:bg-primary-500 dark:border-gray-700">
          <li>
            <a href="#services" class="block py-2 px-3 text-white rounded hover:bg-tertiary-500 lg:hover:bg-transparent lg:border-0 lg:hover:text-tertiary-500 lg:p-0 dark:text-white lg:dark:hover:text-tertiary-500 dark:hover:bg-tertiary-500 dark:hover:text-white lg:dark:hover:bg-transparent" aria-current="page">Services</a>
          </li>
          <li>
            <a href="#about" class="block py-2 px-3 text-white rounded hover:bg-tertiary-500 lg:hover:bg-transparent lg:border-0 lg:hover:text-tertiary-500 lg:p-0 dark:text-white lg:dark:hover:text-tertiary-500 dark:hover:bg-tertiary-500 dark:hover:text-white lg:dark:hover:bg-transparent">About</a>
          </li>
          <li>
            <form action="{{ route('login') }}" method="GET">
              @csrf
              <button class="block w-full text-left py-2 px-3 text-white rounded hover:bg-tertiary-500 lg:hover:bg-transparent lg:border-0 lg:hover:text-tertiary-500 lg:p-0 dark:text-white lg:dark:hover:text-tertiary-500 dark:hover:bg-tertiary-500 dark:hover:text-white lg:dark:hover:bg-transparent" type="submit">
                Login
              </button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>
