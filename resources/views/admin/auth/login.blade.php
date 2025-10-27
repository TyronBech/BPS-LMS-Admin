@extends('layouts.app-form')
@section('content')
<div class="max-w-lg mx-auto my-8 sm:my-12 md:my-16 lg:my-20 h-auto bg-white shadow-md rounded-lg p-6 sm:p-8 md:p-10 dark:bg-gray-800">
  <form action="{{ route('login') }}" method="POST">
    @csrf
    <h1 class="text-xl sm:text-2xl md:text-2xl font-semibold text-gray-900 dark:text-white text-center">Log in to your account</h1>
    <div class="my-4 sm:my-5">
      <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your email</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="admin@gmail.com" autofocus required />
      @error('email')
      <div class="p-3 sm:p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-4 sm:mb-5 relative">
      <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your password</label>
      <input type="password" id="password" name="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pr-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" value="{{ old('password') }}" placeholder="••••••••" required />
      <!-- Toggle Password Visibility Button -->
      <button type="button" id="togglePassword" class="absolute right-3 top-[38px] text-gray-500 dark:text-white" aria-label="Toggle password visibility">
        <!-- Closed Eye Icon (Hidden Initially) -->
        <span id="openEye" class="hidden">
          <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-800 dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-width="2"
              d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" />
            <path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
          </svg>
        </span>
        <!-- Open Eye Icon (Default Visible) -->
        <span id="closedEye">
          <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-800 dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
          </svg>
        </span>
      </button>
      @error('password')
      <div class="p-3 sm:p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="flex items-start mb-4 sm:mb-5">
      <div class="flex items-center h-5">
        <input id="remember" name="remember" type="checkbox" class="w-4 h-4 border border-gray-300 rounded-sm bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800" />
      </div>
      <label for="remember" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Remember me</label>
    </div>
    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Submit</button>
  </form>
  <form action="{{ route('password.request') }}" method="GET">
    @csrf
    <button type="submit" class="mt-2 text-sm font-medium text-blue-600 hover:underline dark:text-blue-500">Forgot Password</button>
  </form>
</div>
<script>
  const togglePassword = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  const openEye = document.getElementById('openEye');
  const closedEye = document.getElementById('closedEye');

  togglePassword.addEventListener('click', function() {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';

    // Toggle icons
    openEye.classList.toggle('hidden', !isPassword);
    closedEye.classList.toggle('hidden', isPassword);
  });
</script>
@endsection