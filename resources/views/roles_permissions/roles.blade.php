@extends('layouts.admin-app')
@section('content')
@php
$roleID = null;
@endphp
<h1 class="font-semibold text-center text-4xl p-5">Maintenance</h1>
<div class="container mx-auto p-4 mt-4 border bg-white shadow border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-600">
  <h1 class="font-semibold text-center text-4xl p-5">Roles</h1>
  <hr class="mb-2 border-gray-600 dark:border-gray-500">
  <form action="{{ route('maintenance.roles-and-permissions.create-role') }}" method="GET">
    @csrf
    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Add new role</button>
  </form>
  <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 mt-3">
    @foreach($roles_with_permissions as $role)
    <div class="flex flex-col w-full p-6 max-h-96 bg-white border-2 border-gray-300 rounded-lg shadow dark:bg-gray-700 dark:border-gray-500">
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $role->name }}</h5>
      <hr class="mb-2 border-gray-600 dark:border-gray-500">
      <ul class="font-normal overflow-y-auto text-gray-700 dark:text-gray-400">
        @foreach($role->permissions as $permission)
        <li class="flex flex-row my-2 font-normal text-gray-700 dark:text-gray-200">
          {{ $permission->name }}
        </li>
        @endforeach
      </ul>
      <hr class="mb-2 border-gray-600 dark:border-gray-500">
      <span class="font-bold text-gray-700 dark:text-white">Assigned:</span>
      <ul class="font-normal text-gray-700 dark:text-gray-400">
        @foreach($admins as $admin)
        @if($admin->hasRole($role->name))
        <li class="flex flex-row my-2 font-normal text-gray-700 dark:text-gray-200">
          {{ $admin->last_name }}, {{ $admin->first_name }} {{ $admin->middle_name }}
        </li>
        @endif
        @endforeach
      </ul>
      <div class="items-center mt-auto mx-auto sm:flex sm:flex-col lg:flex-row">
        <a href="{{ route('maintenance.roles-and-permissions.edit-role', $role->id) }}" id="editBtn" name="editBtn" class="text-white bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 me-2 lg:my-2 sm:my-1">Edit</a>
        @php
        $roleID = ['id' => $role->id];
        @endphp
        @if($role->name != 'Super Admin')
          <button data-modal-target="popup-modal" data-modal-toggle="popup-modal" class="focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2" type="button">Delete</button>
        @endif
      </div>
    </div>
    @endforeach
  </div>
  <h1 class="font-semibold text-center text-4xl p-5">Permissions</h1>
  <hr class="mb-2 border-gray-600 dark:border-gray-500">
  <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 mt-3">
    @foreach($permissions_with_roles as $permission)
    <div class="flex flex-col w-full p-6 bg-white border-2 border-gray-300 rounded-lg shadow dark:bg-gray-700 dark:border-gray-500">
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $permission->name }}</h5>
      <hr class="mb-2 border-gray-600 dark:border-gray-500">
      <ul class="font-normal text-gray-700 dark:text-gray-400">
        @foreach($permission->roles as $role)
        <li class="flex flex-row my-2 font-normal text-gray-700 dark:text-gray-200">
          {{ $role->name }}
        </li>
        @endforeach
      </ul>
    </div>
    @endforeach
  </div>
</div>
<div id="popup-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="popup-modal">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this role?</h3>
        <form action="{{ route('maintenance.roles-and-permissions.delete-role', $roleID) }}" method="POST" class="flex items-center justify-center">
          @csrf
          @method('DELETE')
          <button data-modal-hide="popup-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="popup-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection