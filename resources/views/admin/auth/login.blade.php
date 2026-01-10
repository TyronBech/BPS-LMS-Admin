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

  {{-- 2FA Modal Overlay --}}
  <div id="2fa-modal" class="hidden absolute inset-0 z-40 flex-col items-center justify-center bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-lg border border-gray-200 dark:border-gray-700 transition-all duration-300">
    <div class="text-center p-6 sm:p-8 w-full max-w-md">
      <!-- Header -->
      <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 mb-4 shadow-lg">
        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" />
        </svg>
      </div>

      <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Check Your Email</h3>
      <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
        We've sent a 6-digit verification code to your email address. Please check your inbox to complete the login process.
      </p>

      <!-- Info Alert -->
      <div class="mb-6 p-4 bg-secondary-500 border-l-4 border-primary-500 dark:bg-primary-900/20 dark:border-primary-400 text-left">
        <div class="flex items-start">
          <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div class="ml-3">
            <p class="text-xs text-primary-700 dark:text-primary-300">
              <strong class="font-semibold">Security Tip:</strong> The code will expire in 10 minutes. If you don't see the email, check your spam folder.
            </p>
          </div>
        </div>
      </div>

      <!-- Loading Animation -->
      <div class="flex items-center justify-center mb-6">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 dark:border-primary-400"></div>
        <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Redirecting to verification page...</span>
      </div>

      <!-- Action Buttons -->
      <div class="space-y-3">
        <button type="button" id="2fa-continue-btn" class="w-full text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-semibold rounded-lg text-sm px-5 py-3 text-center transition-all duration-200 dark:from-primary-600 dark:to-primary-700 dark:hover:from-primary-700 dark:hover:to-primary-800 dark:focus:ring-primary-800 shadow-md hover:shadow-lg">
          <svg class="inline w-5 h-5 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
          Continue to Verification
        </button>

        <button type="button" id="2fa-cancel-btn" class="w-full text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
          <svg class="inline w-4 h-4 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Cancel Login
        </button>
      </div>

      <!-- Help Text -->
      <p class="mt-6 text-xs text-gray-500 dark:text-gray-400">
        Having trouble? Contact support at
        <a href="mailto:owlquery.tech@gmail.com" class="text-primary-600 hover:underline dark:text-primary-400">owlquery.tech@gmail.com</a>
      </p>
    </div>
  </div>

  <form action="{{ route('login.store') }}" method="POST" id="login-form">
    @csrf
    <h1 class="text-xl sm:text-2xl md:text-2xl font-semibold text-gray-900 dark:text-white text-center">Log in to your account</h1>

    <div class="my-4 sm:my-5">
      <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your email</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="admin@gmail.com" autofocus value="{{ old('email') }}" required />
      @error('email')
      <div id="email-error" class="p-3 sm:p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-4 sm:mb-5 relative">
      <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Your password</label>
      <input type="password" id="password" name="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full pr-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ old('password') }}" placeholder="••••••••" required />
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
        <input id="remember" name="remember" type="checkbox" class="w-4 h-4 border border-gray-300 rounded-sm bg-gray-50 focus:ring-3 focus:ring-primary-400 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-primary-500 dark:ring-offset-gray-400 dark:focus:ring-offset-gray-400 accent-primary-500" />
      </div>
      <label for="remember" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Remember me</label>
    </div>
    <button type="submit" class="text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">Submit</button>
  </form>
  <form action="{{ route('password.request') }}" method="GET">
    @csrf
    <button type="submit" class="mt-2 text-sm font-medium text-primary-600 hover:underline dark:text-primary-50">Forgot Password</button>
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

  // Lockout Timer
  const lockoutTime = <?php echo session('lockout_time', 0); ?>;
  if (lockoutTime > 0) {
    document.addEventListener('DOMContentLoaded', function() {
      let timeLeft = Number(lockoutTime);
      const countdownEl = document.getElementById('countdown');
      const overlayEl = document.getElementById('lockout-overlay');
      const emailErrorEl = document.getElementById('email-error');
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
            }, 300);
          }
        }
      }, 1000);
    });
  }

  // 2FA Modal Handling
  const show2faModal = <?php echo json_encode((bool)session('show_2fa_modal')) ?>;
  if (show2faModal) {
    document.addEventListener('DOMContentLoaded', function() {
      const modal = document.getElementById('2fa-modal');
      const continueBtn = document.getElementById('2fa-continue-btn');
      const cancelBtn = document.getElementById('2fa-cancel-btn');

      // Show modal
      modal.classList.remove('hidden');
      modal.classList.add('flex');

      // Auto-redirect after 3 seconds
      setTimeout(function() {
        window.location.href = "{{ route('login.2fa') }}";
      }, 3000);

      // Manual continue
      continueBtn.addEventListener('click', function() {
        window.location.href = "{{ route('login.2fa') }}";
      });

      // Cancel button
      cancelBtn.addEventListener('click', function() {
        // Clear session and reload
        fetch("{{ route('login.2fa.cancel') }}", {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
          },
        }).then(() => {
          window.location.href = "{{ route('login') }}";
        });
      });
    });
  }
</script>
@endsection