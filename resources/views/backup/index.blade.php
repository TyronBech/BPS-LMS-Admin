@use('App\Enum\PermissionsEnum')
@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="max-w-4xl mx-auto bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center p-6">

      {{-- Image Column --}}
      <div class="md:col-span-1 flex justify-center">
        <img class="object-cover w-40 h-40 rounded-lg" src="{{ asset('gif/Database.gif') }}" alt="Database Image">
      </div>

      {{-- Content Column --}}
      <div class="md:col-span-2 flex flex-col justify-center text-center md:text-left">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Database Backups</h5>
        <p class="mb-4 font-normal text-gray-700 dark:text-gray-400">Create and manage backups of your database to ensure your data is safe and can be restored when needed.</p>

        @can(PermissionsEnum::CREATE_BACKUPS)
        <div class="flex flex-col items-center md:items-start space-y-3">
          <form action="{{ route('backup.create') }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 focus:outline-none text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
              <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M4 4a2 2 0 1 0 0 4h16a2 2 0 1 0 0-4H4Zm0 6h16v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8Zm10.707 5.707a1 1 0 0 0-1.414-1.414l-.293.293V12a1 1 0 1 0-2 0v2.586l-.293-.293a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l2-2Z" clip-rule="evenodd" />
              </svg>
              Create Backup
            </button>
          </form>
          <p class="text-xs text-gray-500 dark:text-gray-400">Note: A new backup is created automatically 3 times every 24 hours. Backups are deleted after 7 days.</p>
        </div>
        @endcan
      </div>
    </div>
  </div>

  <div class="mt-8">
    @include('backup.table')
  </div>
</div>

{{-- Download Toast --}}
<div id="download-toast" class="hidden items-center absolute top-4 z-10 right-5 w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg dark:text-gray-400 dark:bg-gray-800 shadow-md" role="alert">
  <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-primary-500 bg-primary-100 rounded-lg dark:bg-primary-800 dark:text-primary-200">
    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
      <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0V6a1 1 0 0 1 2 0v5Z" />
    </svg>
    <span class="sr-only">Info icon</span>
  </div>
  <div class="ms-3 text-sm font-normal">Download will start shortly. The password will be sent to your email.</div>
  <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700 shadow-md" data-dismiss-target="#download-toast" aria-label="Close">
    <span class="sr-only">Close</span>
    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
    </svg>
  </button>
</div>
@endsection