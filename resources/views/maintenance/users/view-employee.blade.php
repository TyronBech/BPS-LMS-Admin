@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <h1 class="font-semibold text-center text-3xl md:text-4xl mb-8">Employee Information</h1>

  <div class="max-w-md mx-auto bg-white border border-gray-200 rounded-lg shadow-lg dark:bg-gray-800 dark:border-gray-700 p-6">
    <div class="flex flex-col items-center">

      {{-- Profile Image --}}
      @if($employee->profile_image && $mimeType)
      <img class="rounded-full w-32 h-32 md:w-40 md:h-40 object-cover mb-4 shadow-md" src="data:{{ $mimeType }};base64, {{ $employee->profile_image }}" alt="Employee Image">
      @else
      <img class="hidden rounded-full w-32 h-32 md:w-40 md:h-40 object-cover mb-4 shadow-md dark:block" src="{{ asset('img/User-dark.png') }}" alt="Employee Image">
      <img class="rounded-full w-32 h-32 md:w-40 md:h-40 object-cover mb-4 shadow-md dark:hidden" src="{{ asset('img/User-light.png') }}" alt="Employee Image">
      @endif

      {{-- Name and ID --}}
      <h5 class="text-2xl md:text-3xl font-bold text-center tracking-tight text-gray-900 dark:text-white">{{ $employee->first_name }} {{ $employee->middle_name ?? '' }} {{ $employee->last_name }} {{ $employee->suffix ?? '' }}</h5>
      <p class="mb-6 font-normal text-gray-700 dark:text-gray-400">{{ $employee->employees->employee_id }}</p>

      {{-- Details List --}}
      <div class="w-full text-left space-y-3 text-sm md:text-base">
        <div class="flex justify-between border-b border-gray-200 dark:border-gray-700 py-2">
          <span class="font-semibold text-gray-800 dark:text-gray-300">RFID:</span>
          <span class="text-gray-600 dark:text-gray-400">{{ $employee->rfid }}</span>
        </div>
        <div class="flex justify-between border-b border-gray-200 dark:border-gray-700 py-2">
          <span class="font-semibold text-gray-800 dark:text-gray-300">Position:</span>
          <span class="text-gray-600 dark:text-gray-400">{{ $employee->employees->employee_role }}</span>
        </div>
        <div class="flex justify-between border-b border-gray-200 dark:border-gray-700 py-2">
          <span class="font-semibold text-gray-800 dark:text-gray-300">Gender:</span>
          <span class="text-gray-600 dark:text-gray-400">{{ $employee->gender }}</span>
        </div>
        <div class="flex justify-between py-2">
          <span class="font-semibold text-gray-800 dark:text-gray-300">Email:</span>
          <span class="text-gray-600 dark:text-gray-400 truncate">{{ $employee->email }}</span>
        </div>
      </div>
    </div>
  </div>

  <div class="flex justify-center mt-6">
    <a href="{{ request('return_to', route('maintenance.users')) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
      <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
      </svg>
      Back to Users
    </a>
  </div>
</div>
@endsection