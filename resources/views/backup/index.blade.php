@use('App\Enum\PermissionsEnum')
@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Maintenance</h1>
<div class="flex flex-col my-10 items-center bg-white border border-gray-200 rounded-lg shadow-sm md:flex-row md:max-w-xl hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
  <img class="object-cover m-4 w-full rounded-t-lg h-96 md:h-auto md:w-48 md:rounded-none md:rounded-s-lg" src="{{ asset('img/database.png') }}" alt="Database Image">
  <div class="flex flex-col justify-between p-4 leading-normal">
    <h5 class="mb-2 text-2xl uppercase font-bold tracking-tight text-gray-900 dark:text-white">Database Backups</h5>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Create and manage backups of your database to ensure your data is safe and can be restored when needed.</p>
    @can(PermissionsEnum::CREATE_BACKUPS)
    <div class="flex flex-row items-center">
      <form action="{{ route('backup.create') }}" method="POST">
        @csrf
        <button type="submit" class="flex flex-row items-center focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-4 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">
          Create Backup
          <svg class="w-8 h-8 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
            <path fill-rule="evenodd" d="M4 4a2 2 0 1 0 0 4h16a2 2 0 1 0 0-4H4Zm0 6h16v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8Zm10.707 5.707a1 1 0 0 0-1.414-1.414l-.293.293V12a1 1 0 1 0-2 0v2.586l-.293-.293a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l2-2Z" clip-rule="evenodd" />
          </svg>
        </button>
      </form>
      <form action="{{ route('backup.download') }}" method="POST">
        @csrf
        <button type="submit" class="flex flex-row items-center focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-4 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">
          Download Backup
          <svg class="w-8 h-8 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 15v2a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-2m-8 1V4m0 12-4-4m4 4 4-4" />
          </svg>
        </button>
      </form>
    </div>
    <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">Note: You only can download a backup if you have created one.</p>
    @endcan
  </div>
</div>
@endsection