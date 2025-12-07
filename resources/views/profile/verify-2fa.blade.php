@extends('layouts.app-form')
@section('content')
<div class="relative max-w-lg mx-auto my-8 sm:my-12 md:my-16 lg:my-20 h-auto bg-white shadow-md rounded-lg p-6 sm:p-8 md:p-10 dark:bg-gray-800">

  <!-- Header -->
  <div class="text-center mb-6">
    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 mb-4 shadow-lg">
      <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
      </svg>
    </div>
    <h1 class="text-xl sm:text-2xl md:text-2xl font-semibold text-gray-900 dark:text-white">Verify Profile Update</h1>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Enter the 6-digit code sent to your email to confirm changes</p>
  </div>

  <form action="{{ route('profile.2fa.verify.code') }}" method="POST" id="2faForm">
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
        class="bg-gray-50 border border-gray-300 text-gray-900 text-lg font-mono text-center tracking-widest rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
        placeholder="000000"
        required />
      @error('code')
      <div class="p-3 sm:p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-red-900/20 dark:text-red-400" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>

    <button type="submit" class="w-full text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-semibold rounded-lg text-sm px-5 py-3 text-center transition-all duration-200 dark:from-blue-600 dark:to-blue-700 dark:hover:from-blue-700 dark:hover:to-blue-800 dark:focus:ring-blue-800">
      Verify & Update
    </button>
  </form>

  <!-- Back to Profile -->
  <div class="mt-6 text-center">
    <a href="{{ route('profile') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white inline-flex items-center">
      Cancel Update
    </a>
  </div>
</div>

<script>
  (function() {
    const input = document.getElementById('code');
    if (!input) return;

    // Prevent copy, paste, and cut
    input.addEventListener('paste', e => e.preventDefault());
    input.addEventListener('copy', e => e.preventDefault());
    input.addEventListener('cut', e => e.preventDefault());

    input.addEventListener('input', function() {
      this.value = this.value.replace(/\D/g, '').slice(0, 6);
      if (this.value.length === 6) document.getElementById('2faForm').submit();
    });
  })();
</script>
@endsection