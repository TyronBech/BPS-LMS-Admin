<header class="sticky top-0 z-50">
  <nav class="bg-blue-700 border-gray-200 dark:bg-blue-950">
    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto">
      <a href="{{ route('main-welcome') }}" class="flex items-center space-x-3 rtl:space-x-reverse">
        <img class="rounded-full w-36 h-36" src="{{ asset('img/BPSLogo.png') }}" alt="School Logo">
        <div class="flex flex-col justify-center">
          <h1 class="lg:text-2xl md:text-lg text-white font-semibold text-center dark:text-white">Bicutan Parochial School</h1>
          <hr class="h-px bg-gray-200 border-0 dark:bg-white">
          <h1 class="lg:text-xl md:text-md text-white font-semibold text-center dark:text-white">Library Management System</h1>
        </div>
      </a>
      <div class="hidden w-full lg:block lg:w-auto pt-4" id="navbar-dropdown">
        <ul class="flex flex-col font-medium p-4 lg:p-0 mt-4 mx-8 mb-4 border border-gray-100 rounded-lg bg-blue-700 lg:space-x-8 rtl:space-x-reverse lg:flex-row lg:mt-0 lg:border-0 lg:bg-blue-700 dark:bg-blue-950">
          <li>
            <a href="#" class="block py-2 px-3 text-white bg-blue-700 rounded hover:underline lg:bg-transparent lg:text-white lg:p-0" aria-current="page">Services</a>
          </li>
          <li>
            <a href="#" class="block py-2 px-3 text-white rounded hover:underline lg:hover:bg-transparent lg:border-0 lg:hover:text-blue-100 lg:p-0">About</a>
          </li>
          <li>
            <form action="{{ route('login') }}" method="GET">
              @csrf
              <button class="block py-2 px-3 text-white rounded hover:underline lg:hover:bg-transparent lg:border-0 lg:hover:text-blue-100 lg:p-0" type="submit">
                Log In
              </button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>