@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<h1 class="font-semibold text-center text-4xl p-5">Circulation History</h1>

<form action="{{ route('report.circulation-search') }}" method="POST" class="mb-4">
  @csrf
  <div class="container mx-auto px-4">
    <div class="flex flex-col md:flex-row md:flex-wrap items-center justify-center gap-4">

      {{-- Date Range Picker --}}
      <div id="date-range-picker" date-rangepicker class="flex flex-col sm:flex-row items-center gap-2 w-full md:w-auto">
        <div class="relative w-full sm:w-56">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
            </svg>
          </div>
          <input id="datepicker-range-start" name="start" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Select date start" value="<?php echo $fromInputDate; ?>">
        </div>

        <span class="mx-2 text-gray-500 hidden sm:inline">to</span>

        <div class="relative w-full sm:w-56">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
            </svg>
          </div>
          <input id="datepicker-range-end" name="end" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Select date end" value="<?php echo $toInputDate; ?>">
        </div>
      </div>

      {{-- Search Input --}}
      <div class="flex items-center w-full md:w-auto">
        <label for="name" class="block text-sm font-medium mr-2">Search:</label>
        <div class="w-full md:w-64">
          <input type="text" name="search" id="search" placeholder="Name..." class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" value="<?php echo $search; ?>">
        </div>
      </div>

      {{-- Type Select --}}
      <div class="w-full md:w-auto">
        <label for="type" class="sr-only">Type</label>
        <select id="type" name="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full md:w-48 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
          <option selected disabled>Select a Type</option>
          @foreach($availability as $typeOption)
          <option value="{{ $typeOption }}" {{ request('type') == $typeOption ? 'selected' : '' }}>{{ $typeOption }}</option>
          @endforeach
        </select>
      </div>

      {{-- Action Buttons --}}
      <div class="flex flex-wrap items-center gap-2 w-full md:w-auto justify-center md:justify-start">
        <button type="submit" id="submit" name="submit" value="find" class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-bold py-2 px-4 rounded w-full sm:w-auto">Find</button>
        @if(auth()->user()->can(PermissionsEnum::CREATE_REPORTS))
        <button type="submit" id="submit" name="submit" value="pdf" class="bg-red-500 hover:bg-red-700 text-white text-sm font-bold py-2 px-4 rounded w-full sm:w-auto">Export PDF</button>
        <button type="submit" id="submit" name="submit" value="excel" class="bg-green-500 hover:bg-green-700 text-white text-sm font-bold py-2 px-4 rounded w-full sm:w-auto">Export Excel</button>
        @endif
      </div>

    </div>
  </div>
</form>

@include('report.transactions.transaction-table')
@endsection
@section('scripts')
@endsection