@extends('layouts.admin-app')
@section('content')
@use('App\Enum\RolesEnum')
@use('App\Enum\PermissionsEnum')
<h1 class="font-semibold text-center text-4xl p-5">Home</h1>
<div class="flex items-center justify-center">
  <a href="#" class="block max-w-xl p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
    <svg class="w-7 h-7 text-gray-500 dark:text-gray-300 mb-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
      <path d="M18 5h-.7c.229-.467.349-.98.351-1.5a3.5 3.5 0 0 0-3.5-3.5c-1.717 0-3.215 1.2-4.331 2.481C8.4.842 6.949 0 5.5 0A3.5 3.5 0 0 0 2 3.5c.003.52.123 1.033.351 1.5H2a2 2 0 0 0-2 2v3a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1V7a2 2 0 0 0-2-2ZM8.058 5H5.5a1.5 1.5 0 0 1 0-3c.9 0 2 .754 3.092 2.122-.219.337-.392.635-.534.878Zm6.1 0h-3.742c.933-1.368 2.371-3 3.739-3a1.5 1.5 0 0 1 0 3h.003ZM11 13H9v7h2v-7Zm-4 0H2v5a2 2 0 0 0 2 2h3v-7Zm6 0v7h3a2 2 0 0 0 2-2v-5h-5Z" />
    </svg>
    <p class="font-normal text-gray-700 dark:text-gray-300">Welcome! {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Role</h5>
    @role(RolesEnum::SUPER_ADMIN)
    <p class="font-normal text-gray-700 dark:text-gray-300">You are a super admin.</p>
    @endrole
    @role(RolesEnum::ADMIN)
    <p class="font-normal text-gray-700 dark:text-gray-300">You are a admin.</p>
    @endrole
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Permissions</h5>
    @can(PermissionsEnum::MODIFY_ADMIN)
    <p class="font-normal text-gray-700 dark:text-gray-300">You can modify admins.</p>
    @endcan
    @can(PermissionsEnum::CREATE_USERS)
    <p class="font-normal text-gray-700 dark:text-gray-300">You can create users.</p>
    @endcan
    @can(PermissionsEnum::EDIT_USERS)
    <p class="font-normal text-gray-700 dark:text-gray-300">You can edit users.</p>
    @endcan
    @can(PermissionsEnum::DELETE_USERS)
    <p class="font-normal text-gray-700 dark:text-gray-300">You can delete users.</p>
    @endcan
    @can(PermissionsEnum::CREATE_BOOKS)
    <p class="font-normal text-gray-700 dark:text-gray-300">You can create books.</p>
    @endcan
    @can(PermissionsEnum::EDIT_BOOKS)
    <p class="font-normal text-gray-700 dark:text-gray-300">You can edit books.</p>
    @endcan
    @can(PermissionsEnum::DELETE_BOOKS)
    <p class="font-normal text-gray-700 dark:text-gray-300">You can delete books.</p>
    @endcan
  </a>
</div>
@endsection