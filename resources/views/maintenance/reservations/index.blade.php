@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
  <!-- Page Header -->
  <div class="text-center mb-6 md:mb-8">
    <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Pending Extension Requests</h1>
    <p class="text-gray-600 dark:text-gray-400 text-sm md:text-base lg:text-lg px-4">Review and approve book extension requests from students</p>
  </div>

  <!-- Statistics Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
    <!-- Pending Requests Card -->
    <div class="bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-lg md:rounded-xl shadow-lg p-4 md:p-6 text-white transform hover:scale-105 transition-transform duration-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-yellow-100 text-xs md:text-sm font-medium mb-1">Pending Requests</p>
          <h3 class="text-3xl md:text-4xl font-bold">{{ $pendingExtensionCount ?? 0 }}</h3>
        </div>
        <div class="bg-white bg-opacity-30 rounded-full p-3 md:p-4">
          <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Approved This Month Card -->
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg md:rounded-xl shadow-lg p-4 md:p-6 text-white transform hover:scale-105 transition-transform duration-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-green-100 text-xs md:text-sm font-medium mb-1">Approved This Month</p>
          <h3 class="text-3xl md:text-4xl font-bold">{{ $approvedCount ?? 0 }}</h3>
        </div>
        <div class="bg-white bg-opacity-30 rounded-full p-3 md:p-4">
          <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Active Borrowings Card -->
    <div class="bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg md:rounded-xl shadow-lg p-4 md:p-6 text-white transform hover:scale-105 transition-transform duration-200 sm:col-span-2 lg:col-span-1">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-primary-100 text-xs md:text-sm font-medium mb-1">Active Borrowings</p>
          <h3 class="text-3xl md:text-4xl font-bold">{{ $activeBorrowings ?? 0 }}</h3>
        </div>
        <div class="bg-white bg-opacity-30 rounded-full p-3 md:p-4">
          <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <!-- Search and Filter Section -->
  <div class="bg-white dark:bg-gray-800 rounded-lg md:rounded-xl shadow-md p-4 md:p-6 mb-4 md:mb-6">
    <form action="{{ route('maintenance.search-extension') }}" method="GET" class="flex flex-col sm:flex-row gap-3 md:gap-4">
      <div class="flex-1">
        <input
          type="text"
          name="search"
          placeholder="Search by student name, email, or book title..."
          value="{{ request('search') }}"
          class="w-full px-3 md:px-4 py-2 md:py-3 text-sm md:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
      </div>
      <button
        type="submit"
        class="bg-primary-600 hover:bg-primary-700 text-white font-semibold px-6 md:px-8 py-2 md:py-3 text-sm md:text-base rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <span class="hidden sm:inline">Search</span>
      </button>
    </form>
  </div>

  <!-- Table Section -->
  <div class="bg-white dark:bg-gray-800 rounded-lg md:rounded-xl shadow-md overflow-hidden">
    <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-4 md:px-6 py-3 md:py-4">
      <h2 class="text-lg md:text-xl font-bold text-white flex items-center gap-2">
        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        Extension Requests
      </h2>
    </div>
    @include('maintenance.reservations.table')
  </div>
</div>
@endsection