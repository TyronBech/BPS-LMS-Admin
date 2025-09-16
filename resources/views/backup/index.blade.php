@use('App\Enum\PermissionsEnum')
@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Maintenance</h1>
<div class="flex flex-col my-10 items-center bg-white border border-gray-200 rounded-lg shadow-sm md:flex-row md:max-w-xl dark:border-gray-700 dark:bg-gray-800">
  <div class="flex flex-col justify-between p-4 leading-normal">
    <img class="object-cover mx-4 w-full rounded-t-lg h-96 md:h-auto md:w-48 md:rounded-none md:rounded-s-lg" src="{{ asset('img/database.png') }}" alt="Database Image">
    <form action="{{ route('backup.create') }}" method="POST" class="mx-4 w-full">
      @csrf
      <button type="submit" class="flex flex-row items-center focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-4 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">
        Create Backup
        <svg class="w-8 h-8 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
          <path fill-rule="evenodd" d="M4 4a2 2 0 1 0 0 4h16a2 2 0 1 0 0-4H4Zm0 6h16v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8Zm10.707 5.707a1 1 0 0 0-1.414-1.414l-.293.293V12a1 1 0 1 0-2 0v2.586l-.293-.293a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l2-2Z" clip-rule="evenodd" />
        </svg>
      </button>
    </form>
  </div>
  <div class="flex flex-col justify-between p-4 leading-normal">
    <h5 class="mb-2 text-2xl uppercase font-bold tracking-tight text-gray-900 dark:text-white">Database Backups</h5>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Create and manage backups of your database to ensure your data is safe and can be restored when needed.</p>
    @can(PermissionsEnum::CREATE_BACKUPS)
    <div class="flex flex-row justify-center items-center">
    </div>
    <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">Note: There will be a new backup every 24 hours but you can also create a backup manually. The backup will be deleted after 7 days.</p>
    @endcan
  </div>
</div>
@include('backup.table')
@endsection