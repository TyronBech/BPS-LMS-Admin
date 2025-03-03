@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Roles</h1>
<div class="flex justify-center">
  @foreach($roles_with_permissions as $role)
  <div class="flex flex-col w-1/4 p-6 mx-3 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $role->name }}</h5>
    <hr class="mb-2 border-gray-600 dark:border-gray-500">
    <ul class="font-normal text-gray-700 dark:text-gray-400">
      @foreach($role->permissions as $permission)
      <li class="flex flex-row my-2 font-normal text-gray-700 dark:text-gray-400">
        {{ $permission->name }}
      </li>
      @endforeach
    </ul>
    <hr class="mb-2 border-gray-600 dark:border-gray-500">
    <span class="font-bold text-gray-700 dark:text-gray-400">Assigned:</span>
    <ul class="font-normal text-gray-700 dark:text-gray-400">
      @foreach($admins as $admin)
      @if($admin->hasRole($role->name))
      <li class="flex flex-row my-2 font-normal text-gray-700 dark:text-gray-400">
        {{ $admin->last_name }}, {{ $admin->first_name }} {{ $admin->middle_name }}
      </li>
      @endif
      @endforeach
    </ul>
    <div class="items-center mt-auto mx-auto sm:flex sm:flex-col lg:flex-row">
      <a href="#" id="editBtn" name="editBtn" class="text-white bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 me-2 lg:my-2 sm:my-1">Edit</a>
      <a href="#" id="deleteBtn" name="deleteBtn" class="focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 me-2 lg:my-2 sm:my-1">Delete</a>
    </div>
  </div>
  @endforeach
</div>
<h1 class="font-semibold text-center text-4xl p-5">Permissions</h1>
<div class="flex justify-center">
  @foreach($permissions_with_roles as $permission)
  <div class="flex flex-col w-1/3 p-6 mx-2 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $permission->name }}</h5>
    <hr class="mb-2 border-gray-600 dark:border-gray-500">
    <ul class="font-normal text-gray-700 dark:text-gray-400">
      @foreach($permission->roles as $role)
      <li class="flex flex-row my-2 font-normal text-gray-700 dark:text-gray-400">
        {{ $role->name }}
      </li>
      @endforeach
    </ul>
  </div>
  @endforeach
</div>
@endsection