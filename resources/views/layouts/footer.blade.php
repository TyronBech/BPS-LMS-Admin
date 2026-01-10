<footer class="bg-white dark:bg-gray-900 mt-10 border-t border-gray-200 dark:border-gray-700">
  <div class="mx-auto w-full max-w-screen-xl px-4 py-6 lg:py-8">
    <div class="md:flex md:justify-between md:gap-6 lg:gap-8">
      <div class="mb-6 md:mb-0 md:max-w-xs lg:max-w-md">
        <a href="{{ $settings->social_links['website'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="flex items-center skip-loader">
          <img src="{{ $settings->org_logo_base64 ?? '' }}" class="h-12 w-12 md:h-16 md:w-16 me-3 rounded-full flex-shrink-0" alt="{{ $settings->org_name ?? 'Library Management System' }} Logo" />
          <div class="min-w-0">
            <span class="self-center text-sm md:text-lg font-semibold dark:text-white break-words">{{ $settings->org_name ?? 'Library Management System' }}</span>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 break-words">{{ $settings->org_address ?? '123 Main St, City, Country' }}</p>
          </div>
        </a>
        <p class="mt-4 text-xs md:text-sm text-gray-500 dark:text-gray-400">
          Library Management System Admin Panel for managing school library attendance and resources.
        </p>
      </div>
      <div class="grid grid-cols-2 gap-6 sm:gap-6 sm:grid-cols-3 flex-1">
        <div>
          <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Official Links</h2>
          <ul class="text-gray-500 dark:text-gray-400 font-medium">
            <li class="mb-4">
              <a href="{{ $settings->social_links['website'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="hover:underline skip-loader">Official Website</a>
            </li>
            <li>
              <a href="https://e-library.bps.edu.ph/" target="_blank" rel="noopener noreferrer" class="hover:underline skip-loader">E-Library</a>
            </li>
          </ul>
        </div>
        <div>
          <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Follow us</h2>
          <ul class="text-gray-500 dark:text-gray-400 font-medium">
            <li class="mb-4">
              <a href="{{ $settings->social_links['facebook'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="hover:underline skip-loader">Facebook</a>
            </li>
            <li class="mb-4">
              <a href="{{ $settings->social_links['instagram'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="hover:underline skip-loader">Instagram</a>
            </li>
            <li class="mb-4">
              <a href="{{ $settings->social_links['twitter'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="hover:underline skip-loader">X (Twitter)</a>
            </li>
            <li>
              <a href="{{ $settings->social_links['youtube'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="hover:underline skip-loader">YouTube</a>
            </li>
          </ul>
        </div>
        <div>
          <h2 class="mb-6 text-sm font-semibold text-gray-900 uppercase dark:text-white">Contact Us</h2>
          <ul class="text-gray-500 dark:text-gray-400 font-medium">
            <li class="mb-4">
              <a href="tel:{{ $settings->contact_number ?? '#' }}" class="hover:underline skip-loader">{{ $settings->contact_number ?? 'N/A' }}</a>
            </li>
            <li class="mb-4">
              <a href="mailto:{{ $settings->email ?? '#' }}" class="hover:underline skip-loader">{{ $settings->email ?? 'N/A' }}</a>
            </li>
            <li>
              <a href="mailto:owlquery.tech@gmail.com" class="hover:underline skip-loader">owlquery.tech@gmail.com</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <hr class="my-6 border-gray-200 sm:mx-auto dark:border-gray-700 lg:my-8" />
    <div class="sm:flex sm:items-center sm:justify-between">
      <span class="text-sm text-gray-500 sm:text-center dark:text-gray-400">&copy; {{ date('Y') }} <a href="mailto:owlquery.tech@gmail.com" class="hover:underline skip-loader">OwlQuery Group</a>. All Rights Reserved.
      </span>
      <div class="flex mt-4 sm:justify-center sm:mt-0">
        <a href="{{ $settings->social_links['facebook'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-gray-900 dark:hover:text-white skip-loader">
          <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 8 19">
            <path fill-rule="evenodd" d="M6.135 3H8V0H6.135a4.147 4.147 0 0 0-4.142 4.142V6H0v3h2v9.938h3V9h2.021l.592-3H5V3.591A.6.6 0 0 1 5.592 3h.543Z" clip-rule="evenodd" />
          </svg>
          <span class="sr-only">Facebook page</span>
        </a>
        <a href="{{ $settings->social_links['instagram'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-gray-900 dark:hover:text-white ms-5 skip-loader">
          <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 21 21">
            <path fill-rule="evenodd" d="M10.5 1.052a9.45 9.45 0 0 0-9.45 9.45c0 5.223 4.227 9.45 9.45 9.45s9.45-4.227 9.45-9.45-4.227-9.45-9.45-9.45Zm0 16.906a7.456 7.456 0 1 1 0-14.912 7.456 7.456 0 0 1 0 14.912Z" clip-rule="evenodd" />
            <path fill-rule="evenodd" d="M10.5 5.277a5.225 5.225 0 1 0 0 10.45 5.225 5.225 0 0 0 0-10.45Zm0 8.451a3.225 3.225 0 1 1 0-6.45 3.225 3.225 0 0 1 0 6.45Z" clip-rule="evenodd" />
            <path d="M16.35 5.7a1.05 1.05 0 1 1-2.1 0 1.05 1.05 0 0 1 2.1 0Z" />
          </svg>
          <span class="sr-only">Instagram page</span>
        </a>
        <a href="{{ $settings->social_links['twitter'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-gray-900 dark:hover:text-white ms-5 skip-loader">
          <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 17">
            <path fill-rule="evenodd" d="M19.973 2.426a8.16 8.16 0 0 1-2.35.634 4.067 4.067 0 0 0 1.798-2.26 8.213 8.213 0 0 1-2.6.98A4.056 4.056 0 0 0 14.01 0c-2.234 0-4.044 1.792-4.044 4.005 0 .31.035.612.1.906A11.51 11.51 0 0 1 1.745.91a4.01 4.01 0 0 0-.544 2.02c0 1.39.716 2.616 1.8 3.339a4.033 4.033 0 0 1-1.83-.5v.05c0 1.94 1.392 3.56 3.24 3.93a4.07 4.07 0 0 1-1.82.07 4.04 4.04 0 0 0 3.77 2.78A8.13 8.13 0 0 1 .7 14.95a11.47 11.47 0 0 0 6.22 1.8c7.46 0 11.54-6.1 11.54-11.42q0-.26-.01-.52a8.2 8.2 0 0 0 2.02-2.09Z" clip-rule="evenodd" />
          </svg>
          <span class="sr-only">X page</span>
        </a>
        <a href="{{ $settings->social_links['youtube'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="text-gray-500 hover:text-gray-900 dark:hover:text-white ms-5 skip-loader">
          <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 14">
            <path fill-rule="evenodd" d="M19.7 3.03a2.48 2.48 0 0 0-1.75-1.75C16.22.8 10 .8 10 .8s-6.22 0-7.95.48A2.48 2.48 0 0 0 .3 3.03C0 4.56 0 7 0 7s0 2.44.3 3.97a2.48 2.48 0 0 0 1.75 1.75C3.78 13.2 10 13.2 10 13.2s6.22 0 7.95-.48a2.48 2.48 0 0 0 1.75-1.75c.3-1.53.3-3.97.3-3.97s0-2.44-.3-3.97ZM8 9.56V4.44L13.03 7 8 9.56Z" clip-rule="evenodd" />
          </svg>
          <span class="sr-only">YouTube channel</span>
        </a>
      </div>
    </div>
  </div>
</footer>