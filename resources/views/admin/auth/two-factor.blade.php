@extends('layouts.app-form')
@section('content')
<div class="relative max-w-lg mx-auto my-8 sm:my-12 md:my-16 lg:my-20 h-auto bg-white shadow-md rounded-lg p-6 sm:p-8 md:p-10 dark:bg-gray-800">

  <!-- Header -->
  <div class="text-center mb-6">
    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 mb-4 shadow-lg">
      <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
      </svg>
    </div>
    <h1 class="text-xl sm:text-2xl md:text-2xl font-semibold text-gray-900 dark:text-white">Two-Factor Authentication</h1>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Enter the 6-digit code sent to your email</p>
  </div>

  <!-- Info Alert -->
  <div class="mb-6 p-4 bg-secondary-500 border-l-4 border-primary-500 dark:bg-primary-900/20 dark:border-primary-400">
    <div class="flex items-start">
      <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
      </svg>
      <div class="ml-3">
        <p class="text-sm text-primary-700 dark:text-primary-300">
          <strong class="font-semibold">Security Check:</strong> A verification code has been sent to your registered email address. Please check your inbox and enter the code below.
        </p>
      </div>
    </div>
  </div>

  <form action="{{ route('login.2fa.verify') }}" method="POST" id="2faForm">
    @csrf
    <!-- OTP Input -->
    <div class="mb-6">
      <label for="code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Verification Code</label>
      <input
        type="text"
        id="code"
        name="code"
        maxlength="6"
        inputmode="numeric"
        pattern="[0-9]{6}"
        autocomplete="off"
        autocapitalize="off"
        autocorrect="off"
        spellcheck="false"
        class="bg-gray-50 border border-gray-300 text-gray-900 text-lg font-mono text-center tracking-widest rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-3.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
        placeholder="000000"
        required
        onpaste="return false"
        ondrop="return false"
        oncopy="return false"
        oncut="return false"
        oncontextmenu="return false" />
      <p id="pasteGuard" class="hidden mt-2 text-xs text-amber-600">
        Pasting is disabled. Please type the code manually.
      </p>
      @error('code')
      <div class="p-3 sm:p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-red-900/20 dark:text-red-400" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>

    <button type="submit" class="w-full text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-semibold rounded-lg text-sm px-5 py-3 text-center transition-all duration-200 dark:from-primary-600 dark:to-primary-700 dark:hover:from-primary-700 dark:hover:to-primary-800 dark:focus:ring-primary-800">
      Verify Code
    </button>
  </form>

  <!-- Divider -->
  <div class="relative my-6">
    <div class="absolute inset-0 flex items-center">
      <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
    </div>
    <div class="relative flex justify-center text-xs">
      <span class="bg-white px-2 text-gray-500 dark:bg-gray-800 dark:text-gray-400">OR</span>
    </div>
  </div>

  <!-- Resend Code Form -->
  <form action="{{ route('login.2fa.resend') }}" method="POST" id="resendForm">
    @csrf
    <button
      type="submit"
      class="w-full text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
      <svg class="inline w-4 h-4 mr-2 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>
      Resend Code
    </button>
  </form>

  <!-- Back to Login -->
  <div class="mt-6 text-center">
    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white inline-flex items-center">
      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
      Back to Login
    </a>
  </div>

  <!-- Security Notice -->
  <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg dark:bg-gray-700/50 dark:border-gray-600">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
      <svg class="w-4 h-4 mr-2 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
      </svg>
      Security Tips
    </h3>
    <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1.5">
      <li class="flex items-start">
        <span class="text-green-600 dark:text-green-400 mr-2">✓</span>
        <span>Never share your verification code with anyone</span>
      </li>
      <li class="flex items-start">
        <span class="text-green-600 dark:text-green-400 mr-2">✓</span>
        <span>The code is valid for 10 minutes only</span>
      </li>
      <li class="flex items-start">
        <span class="text-green-600 dark:text-green-400 mr-2">✓</span>
        <span>If you didn't request this, change your password immediately</span>
      </li>
    </ul>
  </div>
</div>

<script>
  (function() {
    const input = document.getElementById('code');
    const guard = document.getElementById('pasteGuard');
    if (!input) return;

    const showGuard = () => {
      if (!guard) return;
      guard.classList.remove('hidden');
      clearTimeout(window.__pgTimer);
      window.__pgTimer = setTimeout(() => guard.classList.add('hidden'), 2000);
    };

    // Block paste, drop, context menu on the input
    ['paste', 'drop', 'contextmenu', 'copy', 'cut'].forEach(ev =>
      input.addEventListener(ev, e => {
        e.preventDefault();
        showGuard();
      })
    );

    // Block keyboard paste shortcuts while focused
    document.addEventListener('keydown', e => {
      const focused = document.activeElement === input;
      const k = (e.key || '').toLowerCase();
      if (!focused) return;
      if ((e.ctrlKey || e.metaKey) && k === 'v') {
        e.preventDefault();
        showGuard();
      }
      if (e.shiftKey && k === 'insert') {
        e.preventDefault();
        showGuard();
      }
    });

    // Only digits, auto-submit at 6 chars
    input.addEventListener('input', function() {
      this.value = this.value.replace(/\D/g, '').slice(0, 6);
      if (this.value.length === 6) document.getElementById('2faForm').submit();
    });
  })();
</script>
@endsection