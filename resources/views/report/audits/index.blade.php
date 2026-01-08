@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4">
  <h1 class="font-semibold text-center text-4xl p-5">Report Document For Audits</h1>
  <form action="{{ route('report.audit-trail-search') }}" method="POST">
    @csrf
    <div class="flex flex-col md:flex-row md:flex-wrap md:items-end md:justify-center gap-4 mb-4">

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

      {{-- Type Select --}}
      <div class="w-full md:w-auto">
        <label for="types" class="sr-only">Select Type</label>
        <select id="types" name="types" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          <option value="ALL" {{ $types === 'ALL' ? 'selected' : '' }}>ALL</option>
          <option value="INSERT" {{ $types === 'INSERT' ? 'selected' : '' }}>INSERT</option>
          <option value="UPDATE" {{ $types === 'UPDATE' ? 'selected' : '' }}>UPDATE</option>
          <option value="DELETE" {{ $types === 'DELETE' ? 'selected' : '' }}>DELETE</option>
          <option value="LOGIN" {{ $types === 'LOGIN' ? 'selected' : '' }}>LOGIN</option>
          <option value="LOGOUT" {{ $types === 'LOGOUT' ? 'selected' : '' }}>LOGOUT</option>
        </select>
      </div>

      {{-- Table Type Select --}}
      <div class="w-full md:w-auto">
        <label for="tableType" class="sr-only">Select Table</label>
        <select id="tableType" name="tableType" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          <option value="All" {{ $tableType === 'All' ? 'selected' : '' }}>All</option>
          <option value="Users" {{ $tableType === 'Users' ? 'selected' : '' }}>Users</option>
          <option value="Books" {{ $tableType === 'Books' ? 'selected' : '' }}>Books</option>
          <option value="Transactions" {{ $tableType === 'Transactions' ? 'selected' : '' }}>Transactions</option>
          <option value="Sessions" {{ $tableType === 'Sessions' ? 'selected' : '' }}>Sessions</option>
        </select>
      </div>

      {{-- Action Buttons --}}
      <div class="flex flex-wrap items-center justify-center gap-2">
        <button type="submit" name="submit" value="find" class="bg-primary-500 hover:bg-primary-700 active:bg-primary-900 text-white font-bold py-2 px-4 rounded w-full sm:w-auto">Find</button>
      </div>
    </div>
  </form>
  @include('report.audits.table')
</div>
@endsection