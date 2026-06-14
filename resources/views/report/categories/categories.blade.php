@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Summary of {{ $settings->org_initial ?? 'BPS' }} Collections</h1>

  <form action="{{ route('report.summary') }}" method="GET" class="auto-search-form mb-6 p-4">
    <div class="flex flex-col md:flex-row items-end justify-center gap-4">
      {{-- Educational Level Select --}}
      <div class="flex flex-col w-full md:w-auto md:flex-1 md:max-w-[240px]">
        <label for="educational_level" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Educational Level</label>
        <select id="educational_level" name="educational_level" onchange="this.form.submit()" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          <option value="All" {{ $educationalLevel == 'All' ? 'selected' : '' }}>All Levels</option>
          <option value="Elementary" {{ $educationalLevel == 'Elementary' ? 'selected' : '' }}>Elementary</option>
          <option value="Junior High School" {{ $educationalLevel == 'Junior High School' ? 'selected' : '' }}>Junior High School</option>
          <option value="Senior High School" {{ $educationalLevel == 'Senior High School' ? 'selected' : '' }}>Senior High School</option>
        </select>
      </div>

      {{-- Category Type Select --}}
      <div class="flex flex-col w-full md:w-auto md:flex-1 md:max-w-[240px]">
        <label for="category_type" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Category Type</label>
        <select id="category_type" name="category_type" onchange="this.form.submit()" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          <option value="All" {{ $categoryType == 'All' ? 'selected' : '' }}>All Types</option>
          <option value="Print" {{ $categoryType == 'Print' ? 'selected' : '' }}>Print</option>
          <option value="Non-print" {{ $categoryType == 'Non-print' ? 'selected' : '' }}>Non-print</option>
          <option value="E-books" {{ $categoryType == 'E-books' ? 'selected' : '' }}>E-books</option>
        </select>
      </div>

      {{-- Action Buttons --}}
      <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
        <a href="{{ route('report.summary') }}" class="w-full sm:w-auto text-center bg-white hover:bg-gray-100 text-gray-900 border border-gray-300 font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-600 text-sm" title="Clear Filters">Clear</a>
      </div>
    </div>
  </form>

  <div id="table-container">

    @include('report.categories.table')

  </div>

  <div class="fixed z-50 bottom-10 left-10 flex flex-col sm:flex-row gap-2">
    @if($hasRollback)
    <button type="button" data-modal-target="confirm-rollback-modal" data-modal-toggle="confirm-rollback-modal" class="flex items-center text-white bg-yellow-500 hover:bg-yellow-600 focus:ring-4 focus:outline-none focus:ring-yellow-300 dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center shadow-lg transition-colors">
      <span class="hidden sm:inline">Rollback Update</span>
      <svg class="w-6 h-6 text-white sm:ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9h13a5 5 0 0 1 0 10H4m0 0l4-4m-4 4l4 4" />
      </svg>
    </button>
    @endif
    <button type="button" data-modal-target="confirm-update-matrix-modal" data-modal-toggle="confirm-update-matrix-modal" class="flex items-center text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500 font-medium rounded-lg text-sm px-5 py-2.5 text-center shadow-lg">
      <span class="hidden sm:inline">Update Matrix</span>
      <svg class="w-6 h-6 text-white sm:ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4" />
      </svg>
    </button>
  </div>

  <!-- Confirmation Modal -->
  <div id="confirm-update-matrix-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
      <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
        <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="confirm-update-matrix-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
        <div class="p-4 md:p-5 text-center">
          <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to update the summary matrix? This process may take a few moments.</h3>
          <form action="{{ route('report.summary-update') }}" method="POST">
            @csrf
            <button data-modal-hide="confirm-update-matrix-modal" type="submit" class="text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
              Yes, update
            </button>
            <button data-modal-hide="confirm-update-matrix-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">No, cancel</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  @if($hasRollback)
  <!-- Rollback Confirmation Modal -->
  <div id="confirm-rollback-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
      <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
        <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="confirm-rollback-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
        <div class="p-4 md:p-5 text-center">
          <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to rollback the category summary matrix to its previous state? This will restore the inventory numbers from the last update.</h3>
          <form action="{{ route('report.summary-rollback') }}" method="POST">
            @csrf
            <button data-modal-hide="confirm-rollback-modal" type="submit" class="text-white bg-yellow-500 hover:bg-yellow-600 focus:ring-4 focus:outline-none focus:ring-yellow-300 dark:bg-yellow-600 dark:hover:bg-yellow-700 dark:focus:ring-yellow-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center shadow-md">
              Yes, rollback
            </button>
            <button data-modal-hide="confirm-rollback-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">No, cancel</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  @endif
</div>
@endsection
