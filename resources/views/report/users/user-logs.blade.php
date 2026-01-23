@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Report Document</h1>
  @include('report.users.graph')

  <form action="{{ route('report.user-search') }}" method="POST">
    @csrf
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-center gap-3 mb-4">
      {{-- Date Range Picker --}}
      <div id="date-range-picker" date-rangepicker class="flex flex-col sm:flex-row items-center justify-center gap-2">
        <div class="relative w-full sm:w-auto">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
            </svg>
          </div>
          <input id="datepicker-range-start" name="start" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Select date start" value="<?php echo $fromInputDate; ?>">
        </div>
        <span class="mx-4 text-gray-500 hidden sm:block">to</span>
        <div class="relative w-full sm:w-auto">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
            </svg>
          </div>
          <input id="datepicker-range-end" name="end" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Select date end" value="<?php echo $toInputDate; ?>">
        </div>
      </div>

      {{-- Search Input --}}
      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[200px]">
        <label for="search" class="block text-sm font-medium mb-1">Search</label>
        <input type="text" name="search" id="search" placeholder="Name..." class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="<?php echo $search; ?>">
      </div>

      {{-- User Type Select --}}
      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[180px]">
        <label for="user_type" class="block text-sm font-medium mb-1">Type</label>
        <select id="user_type" name="user_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          <option value="all" {{ $userType === 'all' ? 'selected' : '' }}>All</option>
          <option value="student" {{ $userType === 'student' ? 'selected' : '' }}>Students</option>
          <option value="employee" {{ $userType === 'employee' ? 'selected' : '' }}>Faculties & Staff</option>
          <option value="visitor" {{ $userType === 'visitor' ? 'selected' : '' }}>Visitors</option>
        </select>
      </div>

      {{-- Action Buttons --}}
      <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
        <button type="submit" name="submit" value="find" class="bg-primary-500 hover:bg-primary-400 active:bg-primary-400 text-white font-bold py-2 px-4 rounded whitespace-nowrap transition-colors dark:bg-primary-400 dark:hover:bg-primary-500 dark:active:bg-primary-500">Find</button>
        @can(PermissionsEnum::CREATE_REPORTS)
        <button type="submit" name="submit" value="pdf" class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white font-bold py-2 px-4 rounded whitespace-nowrap transition-colors">PDF</button>
        <button type="submit" name="submit" value="excel" class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white font-bold py-2 px-4 rounded whitespace-nowrap transition-colors">Excel</button>
        @endcan
      </div>

    </div>
  </form>

  <h1 class="text-md font-extrabold">PEAK HOUR: <small class="ms-2 font-semibold text-gray-500">{{ $peak_hour }}</small></h1>
  @include('report.users.user-logs-table')
</div>
@endsection
@section('scripts')
@endsection