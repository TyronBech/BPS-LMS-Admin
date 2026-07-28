@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Class Reservations Report</h1>
  <form action="{{ route('report.class-reservations-search') }}" method="POST" class="auto-search-form">
    @csrf
    <div class="flex flex-col md:flex-row md:flex-wrap md:items-end md:justify-center gap-4 mb-4">

      {{-- Date Range Picker --}}
      <div id="date-range-picker" date-rangepicker class="flex flex-col sm:flex-row items-end justify-center gap-2 w-full md:w-auto">
        <div class="flex flex-col w-full sm:w-auto">
          <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Start Date</label>
          <div class="relative w-full">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
              </svg>
            </div>
            <input id="datepicker-range-start" name="start" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Select date start" value="{{ old('start', $fromInputDate) }}">
          </div>
        </div>
        <span class="mx-2 text-gray-500 hidden sm:block mb-3">to</span>
        <div class="flex flex-col w-full sm:w-auto">
          <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">End Date</label>
          <div class="relative w-full">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
              </svg>
            </div>
            <input id="datepicker-range-end" name="end" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Select date end" value="{{ old('end', $toInputDate) }}">
          </div>
        </div>
      </div>

      {{-- Status Filter --}}
      <div class="flex flex-col w-full md:w-auto md:flex-1 md:max-w-[200px]">
        <label for="status" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Status</label>
        <select name="status" id="status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
          <option value="All" {{ $status == 'All' ? 'selected' : '' }}>All Status</option>
          <option value="Pending" {{ $status == 'Pending' ? 'selected' : '' }}>Pending</option>
          <option value="Approved" {{ $status == 'Approved' ? 'selected' : '' }}>Approved</option>
          <option value="Rejected" {{ $status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
          <option value="Cancelled" {{ $status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
      </div>

      {{-- Action Buttons --}}
      <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
        <button type="button" data-clear-url="{{ route('report.class-reservations') }}" class="btn-clear-filters bg-white hover:bg-gray-100 text-gray-900 border border-gray-300 font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-600 text-sm w-full sm:w-auto" title="Clear Filters">Clear</button>
        @can(PermissionsEnum::CREATE_REPORTS)
        <button type="submit" name="submit" value="pdf" class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors text-sm w-full sm:w-auto">PDF</button>
        <button type="submit" name="submit" value="excel" class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors text-sm w-full sm:w-auto">Excel</button>
        @endcan
      </div>
    </div>
  </form>
  <div id="table-container">
    @include('report.class-reservations.table')
  </div>
</div>
@endsection
@section('scripts')
@endsection
