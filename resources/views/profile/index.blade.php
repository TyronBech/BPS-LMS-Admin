@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Profile</h1>
<div class="w-8/12 p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
  <div class="grid grid-cols-2 md:grid-cols-1 lg:grid-cols-2 sm:grid-cols-1">
    <div>
      @if($user->profile_image === null)
      <img class="hidden rounded-full w-72 h-72 m-auto my-8 dark:block" src="{{ asset('img/User-dark.png') }}" alt="Profile Image">
      <img class="rounded-full w-72 h-72 m-auto my-8 dark:hidden" src="{{ asset('img/User-light.png') }}" alt="Profile Image">
      @else
      <img class="rounded-full w-72 h-72 m-auto my-8" src="data:image/jpeg;base64, {{ $user->profile_image }}" alt="Profile Image">
      @endif
      <div class="flex flex-col justify-center m-auto w-7/12 mb-2">
        <span>
          <h2 class="text-2xl font-bold text-center">{{ $user->first_name }} {{ $user->last_name }}</h2>
        </span>
        <hr class="h-px bg-gray-200 border-0 dark:bg-gray-700">
        <span>
          <h2 class="text-xs font-semibold text-center">Name</h2>
        </span>
      </div>
      <div class="flex flex-col justify-center m-auto w-5/12">
        @if($user->privileges->user_type === 'student')
        <h2 class="text-lg font-semibold text-center">{{ $user->students->id_number }}</h2>
        <hr class="h-px bg-gray-200 border-0 dark:bg-gray-700">
        <span>
          <h2 class="text-xs font-semibold text-center">ID Number</h2>
        </span>
        @elseif($user->privileges->user_type === 'employee')
        <h2 class="text-lg font-semibold text-center">{{ $user->employees->employee_id }}</h2>
        <hr class="h-px bg-gray-200 border-0 dark:bg-gray-700">
        <span>
          <h2 class="text-xs font-semibold text-center">Employee ID</h2>
        </span>
        @endif
      </div>
    </div>
    <div class="flex flex-col justify-center m-auto py-5 w-11/12">
      <h6 class="mb-5 text-lg font-bold tracking-tight">Personal Information</h6>
      <form action="{{ route('profile.update') }}" method="POST" class="min-w-xl mx-3">
        @csrf
        @method('PATCH')
        <div class="relative z-0 w-full mb-5 group">
          <input type="text" name="first_name" id="first_name" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " value="{{ $user->first_name }}" />
          <label for=first_name" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Name:</label>
          @error('first_name')
          <div class="p-1.5 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">{{ $message }}</span>
          </div>
          @enderror
        </div>
        <div class="relative z-0 w-full mb-5 group">
          <input type="text" name="middle_name" id="middle_name" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " value="{{ $user->middle_name }}" />
          <label for=middle_name" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Middle Name:</label>
          @error('middle_name')
          <div class="p-1.5 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">{{ $message }}</span>
          </div>
          @enderror
        </div>
        <div class="relative z-0 w-full mb-5 group">
          <input type="text" name="last_name" id="last_name" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " value="{{ $user->last_name }}" />
          <label for=last_name" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Last Name:</label>
          @error('last_name')
          <div class="p-1.5 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">{{ $message }}</span>
          </div>
          @enderror
        </div>
        <div class="relative z-0 w-full mb-5 group">
          @if($user->privileges->user_type === 'student')
          <input type="text" name="user_id" id="user_id" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " value="{{ $user->students->id_number }}" />
          <label for=user_id" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">ID Number:</label>
          @elseif($user->privileges->user_type === 'employee')
          <input type="text" name="user_id" id="user_id" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " value="{{ $user->employees->employee_id }}" />
          <label for=user_id" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Employee ID:</label>
          @endif
          @error('user_id')
          <div class="p-1.5 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">{{ $message }}</span>
          </div>
          @enderror
        </div>
        <div class="relative z-0 w-full mb-5 group">
          <input type="email" name="email" id="email" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder="name@example.com" value="{{ $user->email }}" />
          <label for=email" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">email:</label>
          @error('email')
          <div class="p-1.5 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">{{ $message }}</span>
          </div>
          @enderror
        </div>
        <!-- Current Password -->
        <div class="relative z-0 w-full mb-5 group">
          <input type="password" name="current_password" id="current_password" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" value="{{ old('current_password') }}" autocomplete="current-password" placeholder=" " />
          <label for="current_password" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Current Password:</label>
          <!-- Toggle Button -->
          <button type="button" id="toggleCurrent_password" class="absolute right-0 top-1/2 -translate-y-1/2 px-3 text-gray-500 dark:text-white" aria-label="Toggle current password">
            <span id="openEye_current" class="hidden">
              <svg class="w-5 h-5 text-gray-800 dark:text-white" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-width="1.5" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6 4.03-6 9-6 9 4.8 9 6Z" />
                <path stroke="currentColor" stroke-width="1.5" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
            </span>
            <span id="closedEye_current">
              <svg class="w-5 h-5 text-gray-800 dark:text-white" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                  d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
            </span>
          </button>
          @error('current_password')
          <div class="p-1.5 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">{{ $message }}</span>
          </div>
          @enderror
        </div>
        <!-- New Password -->
        <div class="relative z-0 w-full mb-5 group">
          <input type="password" name="new_password" id="new_password" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" value="{{ old('new_password') }}" placeholder=" " />
          <label for="new_password" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">New Password:</label>
          <!-- Toggle Button -->
          <button type="button" id="toggleNew_password" class="absolute right-0 top-1/2 -translate-y-1/2 px-3 text-gray-500 dark:text-white" aria-label="Toggle new password">
            <span id="openEye_new" class="hidden">
              <svg class="w-5 h-5 text-gray-800 dark:text-white" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-width="1.5" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6 4.03-6 9-6 9 4.8 9 6Z" />
                <path stroke="currentColor" stroke-width="1.5" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
            </span>
            <span id="closedEye_new">
              <svg class="w-5 h-5 text-gray-800 dark:text-white" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                  d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
            </span>
          </button>
          @error('new_password')
          <div class="p-1.5 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">{{ $message }}</span>
          </div>
          @enderror
        </div>
        <!-- Confirm Password -->
        <div class="relative z-0 w-full mb-5 group">
          <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" value="{{ old('new_password_confirmation') }}" placeholder=" " />
          <label for="new_password_confirmation" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Confirm Password:</label>
          <!-- Toggle Button -->
          <button type="button" id="toggleNew_password_confirmation" class="absolute right-0 top-1/2 -translate-y-1/2 px-3 text-gray-500 dark:text-white" aria-label="Toggle confirm password">
            <span id="openEye_confirm" class="hidden">
              <svg class="w-5 h-5 text-gray-800 dark:text-white" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-width="1.5" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6 4.03-6 9-6 9 4.8 9 6Z" />
                <path stroke="currentColor" stroke-width="1.5" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
            </span>
            <span id="closedEye_confirm">
              <svg class="w-5 h-5 text-gray-800 dark:text-white" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                  d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
            </span>
          </button>
          @error('new_password_confirmation')
          <div class="p-1.5 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">{{ $message }}</span>
          </div>
          @enderror
        </div>
        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Submit</button>
      </form>
    </div>
  </div>
</div>
<script>
  // Toggle password visibility
  document.getElementById('toggleCurrent_password').addEventListener('click', function () {
    const passwordInput = document.getElementById('current_password');
    const openEye = document.getElementById('openEye_current');
    const closedEye = document.getElementById('closedEye_current');
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    openEye.classList.toggle('hidden', !isPassword);
    closedEye.classList.toggle('hidden', isPassword);
  });

  document.getElementById('toggleNew_password').addEventListener('click', function () {
    const passwordInput = document.getElementById('new_password');
    const openEye = document.getElementById('openEye_new');
    const closedEye = document.getElementById('closedEye_new');
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    openEye.classList.toggle('hidden', !isPassword);
    closedEye.classList.toggle('hidden', isPassword);
  });

  document.getElementById('toggleNew_password_confirmation').addEventListener('click', function () {
    const passwordInput = document.getElementById('new_password_confirmation');
    const openEye = document.getElementById('openEye_confirm');
    const closedEye = document.getElementById('closedEye_confirm');
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    openEye.classList.toggle('hidden', !isPassword);
    closedEye.classList.toggle('hidden', isPassword);
  });
  document.addEventListener('DOMContentLoaded', function () {
    const currentPassword = document.getElementById('current_password');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('new_password_confirmation');

    function toggleRequiredFields() {
      const hasCurrentPassword = currentPassword.value.trim() !== '';
      newPassword.required = hasCurrentPassword;
      confirmPassword.required = hasCurrentPassword;
    }

    currentPassword.addEventListener('input', toggleRequiredFields);
  });
</script>
@endsection