@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Roles and Permissions</h1>
<div class="flex justify-center">
  @foreach($roles_and_permissions as $role)
  <div class="flex flex-col w-1/4 p-6 mx-3 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $role->name }}</h5>
    <hr class="mb-2 border-gray-600 dark:border-gray-500">
    <ul class="my-auto font-normal text-gray-700 dark:text-gray-400">
      @foreach($role->permissions as $permission)
      <li class="flex flex-row my-2 font-normal text-gray-700 dark:text-gray-400">
        <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16.881V7.119a1 1 0 0 1 1.636-.772l5.927 4.881a1 1 0 0 1 0 1.544l-5.927 4.88A1 1 0 0 1 8 16.882Z" />
        </svg>
        {{ $permission->name }}
      </li>
      @endforeach
    </ul>
    <div class="items-center mt-auto mx-auto sm:flex sm:flex-col lg:flex-row">
      <a href="#" id="editBtn" name="editBtn" class="text-white bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 me-2 lg:my-2 sm:my-1">Edit</a>
      <a href="#" id="deleteBtn" name="deleteBtn" class="focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 me-2 lg:my-2 sm:my-1">Delete</a>
    </div>
  </div>
  @endforeach
</div>
@endsection