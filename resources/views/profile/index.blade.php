@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Profile</h1>
<div class="w-8/12 p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
  <div class="grid grid-cols-2 md:grid-cols-1 lg:grid-cols-2 sm:grid-cols-1">
    <div>
      <img class="rounded-full w-72 h-72 m-auto my-8" src="data:image/jpeg;base64, {{ $user->profile_image }}" alt="Profile Image">
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
          <input type="email" name="email" id="email" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " value="{{ $user->email }}" />
          <label for=email" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">email:</label>
          @error('email')
          <div class="p-1.5 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">{{ $message }}</span>
          </div>
          @enderror
        </div>
        <div class="relative z-0 w-full mb-5 group">
          <input type="password" name="current_password" id="current_password" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " />
          <label for=current_password" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Current Password:</label>
          @error('current_password')
          <div class="p-1.5 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">{{ $message }}</span>
          </div>
          @enderror
        </div>
        <div class="relative z-0 w-full mb-5 group">
          <input type="password" name="new_password" id="new_password" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " />
          <label for=new_password" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">New Password:</label>
          @error('new_password')
          <div class="p-1.5 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <span class="font-medium">{{ $message }}</span>
          </div>
          @enderror
        </div>
        <div class="relative z-0 w-full mb-5 group">
          <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " />
          <label for=new_password_confirmation" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Confirm Password:</label>
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
@endsection