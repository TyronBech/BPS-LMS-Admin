@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>

  <!-- Statistics Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
    <!-- Pending Reservations Card -->
    <div class="bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-lg shadow-md p-4 md:p-6 text-white transform hover:scale-105 transition-transform duration-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-yellow-100 text-xs md:text-sm font-medium mb-1">Pending Reservations</p>
          <h3 class="text-2xl md:text-3xl font-bold">{{ $pendingReservationsCount ?? 0 }}</h3>
        </div>
        <div class="bg-white bg-opacity-30 rounded-full p-3 shadow-sm">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Pending Extensions Card -->
    <div class="bg-gradient-to-br from-orange-400 to-orange-500 rounded-lg shadow-md p-4 md:p-6 text-white transform hover:scale-105 transition-transform duration-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-orange-100 text-xs md:text-sm font-medium mb-1">Pending Extensions</p>
          <h3 class="text-2xl md:text-3xl font-bold">{{ $pendingExtensionsCount ?? 0 }}</h3>
        </div>
        <div class="bg-white bg-opacity-30 rounded-full p-3 shadow-sm">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Approved This Month Card -->
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-md p-4 md:p-6 text-white transform hover:scale-105 transition-transform duration-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-green-100 text-xs md:text-sm font-medium mb-1">Approved This Month</p>
          <h3 class="text-2xl md:text-3xl font-bold">{{ $approvedCount ?? 0 }}</h3>
        </div>
        <div class="bg-white bg-opacity-30 rounded-full p-3 shadow-sm">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Active Borrowings Card -->
    <div class="bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg shadow-md p-4 md:p-6 text-white transform hover:scale-105 transition-transform duration-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-primary-100 text-xs md:text-sm font-medium mb-1">Active Borrowings</p>
          <h3 class="text-2xl md:text-3xl font-bold">{{ $activeBorrowings ?? 0 }}</h3>
        </div>
        <div class="bg-white bg-opacity-30 rounded-full p-3 shadow-sm">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <!-- Master Maintenance Card Wrapper -->
  <div class="w-full p-4 sm:p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-3">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Reservation & Extension Approvals</h5>
    </div>

    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    <!-- Toggle buttons -->
    <div class="mb-4" role="tablist" aria-label="Choose request type">
      <div class="inline-flex rounded-md shadow-sm border border-gray-200 dark:border-gray-700" role="group">
        <a href="{{ route('maintenance.reservations', ['tab' => 'reservations', 'search' => request('search')]) }}" class="px-4 py-2 text-sm font-medium rounded-l-md focus:outline-none {{ $activeTab === 'reservations' ? 'bg-primary-500 text-white dark:bg-primary-400' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }} border-r border-gray-200 dark:border-gray-700">
          Book Reservations
          @if($pendingReservationsCount > 0)
            <span class="ms-1.5 px-2 py-0.5 text-xs font-bold rounded-full bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400">
              {{ $pendingReservationsCount }}
            </span>
          @endif
        </a>
        <a href="{{ route('maintenance.reservations', ['tab' => 'extensions', 'search' => request('search')]) }}" class="px-4 py-2 text-sm font-medium rounded-r-md focus:outline-none {{ $activeTab === 'extensions' ? 'bg-primary-500 text-white dark:bg-primary-400' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }}">
          Extension Requests
          @if($pendingExtensionsCount > 0)
            <span class="ms-1.5 px-2 py-0.5 text-xs font-bold rounded-full bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400">
              {{ $pendingExtensionsCount }}
            </span>
          @endif
        </a>
      </div>
    </div>

    <!-- Search Form -->
    <form action="{{ route('maintenance.search-extension') }}" method="GET" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4 auto-search-form">
      <input type="hidden" name="tab" value="{{ $activeTab }}">
      <div class="flex-grow w-full md:w-auto">
        <label for="search" class="sr-only">Search</label>
        <div class="relative">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
            </svg>
          </div>
          <input type="text" id="search" name="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Search by student name, email, or book title" value="{{ request('search') }}" autocomplete="off">
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
        <button type="submit" class="w-full sm:w-auto p-2.5 text-sm font-medium text-white bg-primary-500 rounded-lg border border-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
          Search
        </button>
        <button type="button" class="btn-clear-filters w-full sm:w-auto p-2.5 text-sm font-medium text-gray-900 bg-white rounded-lg border border-gray-300 hover:bg-gray-100 hover:text-primary-700 focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700" title="Clear Filters">
          Clear Filters
        </button>
      </div>
    </form>

    <div id="table-container">

      @include('maintenance.reservations.table')

    </div>
  </div>
</div>
@endsection