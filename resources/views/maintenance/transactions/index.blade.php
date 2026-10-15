@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="w-full p-4 sm:p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Material Circulations</h5>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    {{-- Search and Filter Form --}}
    <form action="{{ route('maintenance.circulations') }}" method="GET" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4 auto-search-form skip-loader">
      <input type="hidden" name="perPage" value="{{ $perPage }}">
      <div class="flex-grow w-full md:w-auto">
        <label for="search" class="sr-only">Search</label>
        <div class="relative">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
            </svg>
          </div>
          <input type="text" id="search" name="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 skip-loader" placeholder="Search by user (name, email, ID) or book borrowed (title, accession)..." value="{{ request('search', $search ?? '') }}" autocomplete="off">
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
        <select id="status_filter" name="status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full sm:w-48 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 skip-loader">
          <option value="">All Statuses</option>
          @foreach ($transactionStatuses as $statusOption)
          <option value="{{ $statusOption }}" {{ request('status', $status ?? '') === $statusOption ? 'selected' : '' }}>{{ $statusOption }}</option>
          @endforeach
        </select>

        <select id="type_filter" name="transaction_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full sm:w-48 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 skip-loader">
          <option value="">All Types</option>
          @foreach ($transactionTypes as $typeOption)
          <option value="{{ $typeOption }}" {{ request('transaction_type', $transactionType ?? '') === $typeOption ? 'selected' : '' }}>{{ $typeOption }}</option>
          @endforeach
        </select>

        <div class="flex gap-2">
          <button type="button" class="btn-clear-filters skip-loader flex-grow sm:flex-grow-0 justify-center items-center inline-flex p-2.5 text-sm font-medium text-gray-900 bg-white rounded-lg border border-gray-300 hover:bg-gray-100 hover:text-primary-700 focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700 transition-colors" title="Clear Filters" data-clear-url="{{ route('maintenance.circulations') }}">
            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span class="sr-only">Clear Filters</span>
          </button>
        </div>
      </div>
    </form>

    <div id="table-container" class="skip-loader">
      @include('maintenance.transactions.table')
    </div>
  </div>
</div>
@endsection