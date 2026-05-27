@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  
  <div class="w-full p-4 sm:p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Gallery</h5>
    </div>
    
    <!-- Tabs -->
    <div class="text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:text-gray-400 dark:border-gray-700 mb-4">
        <ul class="flex flex-wrap -mb-px">
            <li class="me-2">
                <a href="{{ route('maintenance.library-website.gallery', ['tab' => 'photo']) }}" class="inline-block p-4 border-b-2 rounded-t-lg {{ $tab === 'photo' ? 'text-primary-600 border-primary-600 active dark:text-primary-500 dark:border-primary-500' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }}">Photo Albums</a>
            </li>
            <li class="me-2">
                <a href="{{ route('maintenance.library-website.gallery', ['tab' => 'video']) }}" class="inline-block p-4 border-b-2 rounded-t-lg {{ $tab === 'video' ? 'text-primary-600 border-primary-600 active dark:text-primary-500 dark:border-primary-500' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }}">Video Albums</a>
            </li>
        </ul>
    </div>

    <!-- Toolbar (Search & Add) -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <form action="{{ route('maintenance.library-website.gallery') }}" method="GET" class="flex items-center w-full sm:w-auto auto-search-form">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <label for="gallery-search" class="sr-only">Search</label>
        <div class="relative w-full">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
            </svg>
          </div>
          <input type="text" id="gallery-search" name="search" value="{{ old('search', $search) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Search albums" />
        </div>
        <button type="button" data-clear-url="{{ route('maintenance.library-website.gallery', ['tab' => $tab]) }}" class="btn-clear-filters skip-loader p-2.5 ms-2 text-sm font-medium text-gray-900 bg-white rounded-lg border border-gray-300 hover:bg-gray-100 hover:text-primary-700 focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700" title="Clear Filters">
          <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          <span class="sr-only">Clear Filters</span>
        </button>
      </form>

      @can(PermissionsEnum::ADD_GALLERY)
      <div class="mt-4 sm:mt-0">
        @if($tab === 'photo')
            <a href="{{ route('maintenance.library-website.gallery.create-photo-album') }}" class="w-full sm:w-auto inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
                Add Photo Album
            </a>
        @else
            <a href="{{ route('maintenance.library-website.gallery.create-video-album') }}" class="w-full sm:w-auto inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
                Add Video Album
            </a>
        @endif
      </div>
      @endcan
    </div>

    <!-- Table Container -->
    <div id="table-container">
        @if($tab === 'photo')
            @include('maintenance.library-website.gallery.photo-albums-table')
        @else
            @include('maintenance.library-website.gallery.video-albums-table')
        @endif
    </div>
  </div>
</div>
@endsection
