@extends('layouts.app-form')
@section('content')
<div class="relative max-w-lg mx-auto my-8 sm:my-12 md:my-16 lg:my-20 h-auto bg-white shadow-md rounded-lg p-6 sm:p-8 md:p-10 dark:bg-gray-800">
  
  {{-- Lockout Overlay --}}
  @if (session('lockout_time'))
  <div id="lockout-overlay" class="absolute inset-0 z-50 flex flex-col items-center justify-center bg-gray-50/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-lg border border-gray-200 dark:border-gray-700 transition-all duration-300">
      <div class="text-center p-8 animate-pulse">
          <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100 dark:bg-red-900/30 mb-6 shadow-sm">
              <svg class="h-10 w-10 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Temporarily Locked</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-xs mx-auto">
              Too many failed login attempts. For security, please wait before trying again.
          </p>
          <div class="flex flex-col items-center justify-center">
              <div class="text-5xl font-black text-gray-800 dark:text-white font-mono tracking-wider mb-1" id="countdown">
                  {{ session('lockout_time') }}
              </div>
              <span class="text-xs font-medium text-gray-400 uppercase tracking-widest">Seconds Remaining</span>
          </div>
      </div>
  </div>
  @endif

  <form action="{{ route('login.store') }}" method="POST">
    @csrf
    <h1 class="text-xl sm:text-2xl md:text-2xl font-semibold text-gray-900 dark:text-white text-center">Log in to your account</h1>
    
    <div class="my-4 sm:my-5">
      <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your email</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="admin@gmail.com" autofocus required />
      @error('email')
      <div id="email-error" class="p-3 sm:p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
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

  const lockoutTime = <?php echo session('lockout_time', 0); ?>;
  if (lockoutTime > 0) {
    document.addEventListener('DOMContentLoaded', function() {
      let timeLeft = Number(lockoutTime);
      const countdownEl = document.getElementById('countdown');
      const overlayEl = document.getElementById('lockout-overlay');
      const emailErrorEl = document.getElementById('email-error'); // Select the error message
      const submitBtn = document.querySelector('button[type="submit"]');
      const inputs = document.querySelectorAll('input');

      // Disable form elements
      submitBtn.disabled = true;
      inputs.forEach(input => input.disabled = true);

      const timer = setInterval(function() {
        timeLeft--;
        if (countdownEl) countdownEl.textContent = timeLeft;

        if (timeLeft <= 0) {
          clearInterval(timer);
          // Re-enable form elements
          submitBtn.disabled = false;
          inputs.forEach(input => input.disabled = false);
          
          // Hide email error message
          if (emailErrorEl) {
              emailErrorEl.style.display = 'none';
          }

          // Fade out overlay
          if (overlayEl) {
              overlayEl.style.opacity = '0';
              setTimeout(() => {
                  overlayEl.style.display = 'none';
              }, 300); // Match the duration of the CSS transition
          }
        }
      }, 1000);
    });
  }
</script>
@endsection