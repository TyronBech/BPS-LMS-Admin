@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <h1 class="font-semibold text-center text-3xl md:text-4xl mb-8">Profile</h1>
  <div class="max-w-5xl mx-auto p-4 sm:p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

      {{-- Left Column: Profile Image and Basic Info --}}
      <div class="lg:col-span-1 flex flex-col items-center text-center lg:border-r lg:border-gray-200 dark:lg:border-gray-700 lg:pr-8">
        @if($user->profile_image === null)
        <img class="hidden rounded-full w-40 h-40 md:w-48 md:h-48 object-cover mb-4 shadow-md dark:block" src="{{ asset('img/User-dark.png') }}" alt="Profile Image">
        <img class="rounded-full w-40 h-40 md:w-48 md:h-48 object-cover mb-4 shadow-md dark:hidden" src="{{ asset('img/User-light.png') }}" alt="Profile Image">
        @else
        <img class="rounded-full w-40 h-40 md:w-48 md:h-48 object-cover mb-4 shadow-md" src="data:image/jpeg;base64, {{ $user->profile_image }}" alt="Profile Image">
        @endif

        <h5 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $user->first_name }} {{ $user->middle_name ?? '' }} {{ $user->last_name }}</h5>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
          @if($user->privileges->user_type === 'student')
          Student
          @elseif($user->privileges->user_type === 'employee')
          {{ $user->employees->employee_role }}
          @else
          Visitor
          @endif
        </p>

        <div class="w-full max-w-xs space-y-3">
          @if($user->privileges->user_type === 'student')
          <div class="text-center">
            <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $user->students->id_number }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">ID Number</p>
          </div>
          @elseif($user->privileges->user_type === 'employee')
          <div class="text-center">
            <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $user->employees->employee_id }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Employee ID</p>
          </div>
          @endif
        </div>
      </div>

      {{-- Right Column: Form --}}
      <div class="lg:col-span-2">
        <h6 class="mb-6 text-lg font-bold tracking-tight text-gray-900 dark:text-white">Personal Information</h6>
        <form action="{{ route('profile.update') }}" method="POST">
          @csrf
          @method('PATCH')
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            {{-- First Name --}}
            <div class="relative z-0 w-full group">
              <input type="text" name="first_name" id="first_name" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " value="{{ old('first_name', $user->first_name) }}" required />
              <label for="first_name" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">First Name</label>
              @error('first_name')
              <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
              @enderror
            </div>

            {{-- Middle Name --}}
            <div class="relative z-0 w-full group">
              <input type="text" name="middle_name" id="middle_name" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " value="{{ old('middle_name', $user->middle_name) }}" />
              <label for="middle_name" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Middle Name</label>
              @error('middle_name')
              <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
              @enderror
            </div>

            {{-- Last Name --}}
            <div class="relative z-0 w-full group">
              <input type="text" name="last_name" id="last_name" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " value="{{ old('last_name', $user->last_name) }}" required />
              <label for="last_name" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Last Name</label>
              @error('last_name')
              <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
              @enderror
            </div>

            {{-- Suffix --}}
            <div class="relative z-0 w-full group">
              <input type="text" name="suffix" id="suffix" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " value="{{ old('suffix', $user->suffix) }}" />
              <label for="suffix" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Suffix</label>
              @error('suffix')
              <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
              @enderror
            </div>

            {{-- Email --}}
            <div class="relative z-0 w-full sm:col-span-2 group">
              <input type="email" name="email" id="email" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " value="{{ old('email', $user->email) }}" required />
              <label for="email" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Email address</label>
              @error('email')
              <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
              @enderror
            </div>

            {{-- Current Password --}}
            <div class="relative z-0 w-full sm:col-span-2 group">
              <input type="password" name="current_password" id="current_password" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " autocomplete="current-password" />
              <label for="current_password" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Current Password</label>
              @error('current_password')
              <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
              @enderror
            </div>

            {{-- New Password --}}
            <div class="relative z-0 w-full group">
              <input type="password" name="new_password" id="new_password" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " autocomplete="new-password" />
              <label for="new_password" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">New Password</label>
              @error('new_password')
              <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
              @enderror
            </div>

            {{-- Confirm New Password --}}
            <div class="relative z-0 w-full group">
              <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " autocomplete="new-password" />
              <label for="new_password_confirmation" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Confirm password</label>
            </div>
          </div>

          <div class="flex justify-end mt-8">
            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  // ...existing code...
</script>
@endsection