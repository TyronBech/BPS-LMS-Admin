@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>

  <!-- Statistics Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
    <!-- Pending Card -->
    <div class="bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-lg shadow-md p-4 md:p-6 text-white transform hover:scale-105 transition-transform duration-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-yellow-100 text-xs md:text-sm font-medium mb-1">Pending Reservations</p>
          <h3 class="text-2xl md:text-3xl font-bold">{{ $pendingCount ?? 0 }}</h3>
        </div>
        <div class="bg-white bg-opacity-30 rounded-full p-3 shadow-sm">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Approved Card -->
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-md p-4 md:p-6 text-white transform hover:scale-105 transition-transform duration-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-green-100 text-xs md:text-sm font-medium mb-1">Approved Reservations</p>
          <h3 class="text-2xl md:text-3xl font-bold">{{ $approvedCount ?? 0 }}</h3>
        </div>
        <div class="bg-white bg-opacity-30 rounded-full p-3 shadow-sm">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Rejected Card -->
    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-md p-4 md:p-6 text-white transform hover:scale-105 transition-transform duration-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-red-100 text-xs md:text-sm font-medium mb-1">Rejected Reservations</p>
          <h3 class="text-2xl md:text-3xl font-bold">{{ $rejectedCount ?? 0 }}</h3>
        </div>
        <div class="bg-white bg-opacity-30 rounded-full p-3 shadow-sm">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Cancelled Card -->
    <div class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-lg shadow-md p-4 md:p-6 text-white transform hover:scale-105 transition-transform duration-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-gray-100 text-xs md:text-sm font-medium mb-1">Cancelled Reservations</p>
          <h3 class="text-2xl md:text-3xl font-bold">{{ $cancelledCount ?? 0 }}</h3>
        </div>
        <div class="bg-white bg-opacity-30 rounded-full p-3 shadow-sm">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <!-- Master Maintenance Card Wrapper -->
  <div class="w-full p-4 sm:p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-3">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Class Room Reservations Approval</h5>
    </div>

    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    <!-- Toggle buttons / Tabs -->
    <div class="mb-4" role="tablist" aria-label="Choose request type">
      <div class="inline-flex rounded-md shadow-sm border border-gray-200 dark:border-gray-700" role="group">
        <a href="{{ route('maintenance.class-reservations', ['tab' => 'Pending', 'search' => request('search')]) }}" class="px-4 py-2 text-sm font-medium rounded-l-md focus:outline-none {{ $activeTab === 'Pending' ? 'bg-primary-500 text-white dark:bg-primary-400' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }} border-r border-gray-200 dark:border-gray-700">
          Pending
          @if($pendingCount > 0)
            <span class="ms-1.5 px-2 py-0.5 text-xs font-bold rounded-full bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400">
              {{ $pendingCount }}
            </span>
          @endif
        </a>
        <a href="{{ route('maintenance.class-reservations', ['tab' => 'Approved', 'search' => request('search')]) }}" class="px-4 py-2 text-sm font-medium focus:outline-none {{ $activeTab === 'Approved' ? 'bg-primary-500 text-white dark:bg-primary-400' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }} border-r border-gray-200 dark:border-gray-700">
          Approved
        </a>
        <a href="{{ route('maintenance.class-reservations', ['tab' => 'Rejected', 'search' => request('search')]) }}" class="px-4 py-2 text-sm font-medium focus:outline-none {{ $activeTab === 'Rejected' ? 'bg-primary-500 text-white dark:bg-primary-400' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }} border-r border-gray-200 dark:border-gray-700">
          Rejected
        </a>
        <a href="{{ route('maintenance.class-reservations', ['tab' => 'Cancelled', 'search' => request('search')]) }}" class="px-4 py-2 text-sm font-medium rounded-r-md focus:outline-none {{ $activeTab === 'Cancelled' ? 'bg-primary-500 text-white dark:bg-primary-400' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }}">
          Cancelled
        </a>
      </div>
    </div>

    <!-- Search Form -->
    <form action="{{ route('maintenance.class-reservations.search') }}" method="GET" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4 auto-search-form">
      <input type="hidden" name="tab" value="{{ $activeTab }}">
      <div class="flex-grow w-full md:w-auto">
        <label for="search" class="sr-only">Search</label>
        <div class="relative">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
            </svg>
          </div>
          <input type="text" id="search" name="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Search by name, email, purpose or date" value="{{ request('search') }}" autocomplete="off">
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
      @include('maintenance.class-reservations.table')
    </div>
  </div>
</div>
@endsection
