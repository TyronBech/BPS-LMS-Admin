@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <h1 class="font-semibold text-center text-3xl md:text-4xl mb-8">Student Information</h1>

  <div class="max-w-md mx-auto bg-white border border-gray-200 rounded-lg shadow-lg dark:bg-gray-800 dark:border-gray-700 p-6">
    <div class="flex flex-col items-center">

      {{-- Profile Image --}}
      @if($student->profile_image && $mimeType)
      <img class="rounded-full w-32 h-32 md:w-40 md:h-40 object-cover mb-4 shadow-md" src="data:{{ $mimeType }};base64, {{ $student->profile_image }}" alt="Student Image">
      @else
      <img class="hidden rounded-full w-32 h-32 md:w-40 md:h-40 object-cover mb-4 shadow-md dark:block" src="{{ asset('img/User-dark.png') }}" alt="Student Image">
      <img class="rounded-full w-32 h-32 md:w-40 md:h-40 object-cover mb-4 shadow-md dark:hidden" src="{{ asset('img/User-light.png') }}" alt="Student Image">
      @endif

      {{-- Name and ID --}}
      <h5 class="text-2xl md:text-3xl font-bold text-center tracking-tight text-gray-900 dark:text-white">{{ $student->first_name }} {{ $student->middle_name ?? '' }} {{ $student->last_name }} {{ $student->suffix ?? '' }}</h5>
      <p class="mb-6 font-normal text-gray-700 dark:text-gray-400">{{ $student->students->id_number }}</p>

      {{-- Details List --}}
      <div class="w-full text-left space-y-3 text-sm md:text-base">
        <div class="flex justify-between border-b border-gray-200 dark:border-gray-700 py-2">
          <span class="font-semibold text-gray-800 dark:text-gray-300">RFID:</span>
          <span class="text-gray-600 dark:text-gray-400">{{ $student->rfid }}</span>
        </div>
        <div class="flex justify-between border-b border-gray-200 dark:border-gray-700 py-2">
          <span class="font-semibold text-gray-800 dark:text-gray-300">Grade Level:</span>
          <span class="text-gray-600 dark:text-gray-400">{{ $student->students->level }}</span>
        </div>
        <div class="flex justify-between border-b border-gray-200 dark:border-gray-700 py-2">
          <span class="font-semibold text-gray-800 dark:text-gray-300">Section:</span>
          <span class="text-gray-600 dark:text-gray-400">{{ $student->students->section }}</span>
        </div>
        <div class="flex justify-between border-b border-gray-200 dark:border-gray-700 py-2">
          <span class="font-semibold text-gray-800 dark:text-gray-300">Gender:</span>
          <span class="text-gray-600 dark:text-gray-400">{{ $student->gender }}</span>
        </div>
        <div class="flex justify-between py-2">
          <span class="font-semibold text-gray-800 dark:text-gray-300">Email:</span>
          <span class="text-gray-600 dark:text-gray-400 truncate">{{ $student->email }}</span>
        </div>
      </div>
    </div>
  </div>

  <div class="flex justify-center mt-6">
    <a href="{{ request('return_to', route('maintenance.users')) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-700 rounded-lg hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
      <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
      </svg>
      Back to Users
    </a>
  </div>
</div>
@endsection