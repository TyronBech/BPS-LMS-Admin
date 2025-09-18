@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Maintenance</h1>
<h5 class="mb-1 text-xl font-bold tracking-tight">Employee Information</h5>
<div class="flex flex-col items-center max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
  @if($employee->profile_image)
    <img class="rounded-full w-48" src="data:image/{{ $mimeType }};base64, {{ $employee->profile_image }}" alt="Employee Image">
  @else
    <img class="hidden rounded-full w-48 dark:block" src="{{ asset('img/User-dark.png') }}" alt="Employee Image">
    <img class="rounded-full w-48 dark:hidden" src="{{ asset('img/User-light.png') }}" alt="Employee Image">
  @endif
  <h5 class="text-2xl text-center font-bold tracking-tight text-gray-900 dark:text-white">{{ $employee->first_name }} {{ $employee->middle_name ?? '' }} {{ $employee->last_name }} {{ $employee->suffix ?? '' }}</h5>
  <p class="mb-3 font-normal text-sm text-gray-700 dark:text-gray-400">{{ $employee->employees->employee_id }}</p>
  <ul>
    <li><span class="font-semibold">RFID:</span> {{ $employee->rfid }}</li>
    <li><span class="font-semibold">Position:</span> {{ $employee->employees->employee_role }}</li>
    <li><span class="font-semibold">Gender:</span> {{ $employee->gender }}</li>
    <li><span class="font-semibold">Email:</span> {{ $employee->email }}</li>
  </ul>
</div>
<a href="{{ route('maintenance.users') }}" class="inline-flex items-center px-3 py-3 mt-3 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
  Back
  <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
  </svg>
</a>
@endsection