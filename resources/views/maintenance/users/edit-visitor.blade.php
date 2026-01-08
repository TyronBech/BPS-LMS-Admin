@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <h1 class="font-semibold text-center text-3xl md:text-4xl mb-8">Maintenance</h1>
  <div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Visitor</h5>
      <a href="{{ request('return_to', route('maintenance.users')) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-700 rounded-lg hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 mt-4 sm:mt-0">
        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
        </svg>
        Back
      </a>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">
    <form action="{{ route('maintenance.update-visitor') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <input type="hidden" id="id" name="id" value="{{ $user->id }}">
      <h6 class="mb-4 text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Visitor Information</h6>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label for="first-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">First Name:</label>
          <input type="text" id="first-name" name="first-name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Juan" value="{{ $user->first_name ?? old('first-name') }}" required>
          @error('first-name')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="middle-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Middle Name:</label>
          <input type="text" id="middle-name" name="middle-name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Santos" value="{{ $user->middle_name ?? old('middle-name') }}">
          @error('middle-name')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="last-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Last Name:</label>
          <input type="text" id="last-name" name="last-name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Dela Cruz" value="{{ $user->last_name ?? old('last-name') }}" required>
          @error('last-name')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="suffix" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Suffix:</label>
          <input type="text" id="suffix" name="suffix" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Jr." value="{{ $user->suffix ?? old('suffix') }}">
          @error('suffix')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="gender" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Select gender:</label>
          <select id="gender" name="gender" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
            <option disabled>Choose an option</option>
            <option value="Male" {{ ($user->gender ?? old('gender')) == 'Male' ? 'selected' : '' }}>Male</option>
            <option value="Female" {{ ($user->gender ?? old('gender')) == 'Female' ? 'selected' : '' }}>Female</option>
            <option value="Prefer not to say" {{ ($user->gender ?? old('gender')) == 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
          </select>
          @error('gender')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="school_org" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">School/Organization:</label>
          <input type="text" id="school_org" name="school_org" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="BPSU" value="{{ $user->visitors->school_org ?? old('school_org') }}" required>
          @error('school_org')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div class="md:col-span-2">
          <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email Address:</label>
          <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
              <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 16">
                <path d="m10.036 8.278 9.258-7.79A1.979 1.979 0 0 0 18 0H2A1.987 1.987 0 0 0 .641.541l9.395 7.737Z" />
                <path d="M11.241 9.817c-.36.275-.801.425-1.255.427-.428 0-.845-.138-1.187-.395L0 2.6V14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2.5l-8.759 7.317Z" />
              </svg>
            </div>
            <input type="email" id="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="juandelacruz@gmail.com" value="{{ $user->email ?? old('email') }}" required>
          </div>
          @error('email')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div class="md:col-span-2">
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="profile-image">Profile Image:</label>
          <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" id="profile-image" name="profile-image" type="file">
          @error('profile-image')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
      </div>
      <div class="flex justify-end mt-6">
        <button type="submit" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">Update</button>
      </div>
    </form>
  </div>
</div>
@endsection