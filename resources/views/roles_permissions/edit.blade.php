@extends('layouts.admin-app')
@section('content')
@use('App\Enum\RolesEnum')
<h1 class="font-semibold text-center text-4xl p-5">Maintenance</h1>
<div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow  dark:bg-gray-900 dark:border-gray-600">
  <div class="flex justify-between">
    <h5 class="mb-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Role</h5>
    <a href="{{ route('maintenance.roles-and-permissions.management') }}" class="inline-flex items-center px-3 py-1 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
      Back
      <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
      </svg>
    </a>
  </div>
  <hr class="h-px my-3 bg-gray-200 border-0">
  <form action="{{ route('maintenance.roles-and-permissions.update-role') }}" class="max-w-2xl mx-auto" method="POST">
    @csrf
    @method('PUT')
    <input type="hidden" name="role_id" value="{{ $role->id }}">
    <h6 class="mb-1 text-xl font-semibold tracking-tight text-gray-800 dark:text-white">Role Information</h6>
    <div class="mb-5">
      <label for="role" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Role Name:</label>
      @if($role->name == RolesEnum::SUPER_ADMIN->value)
      <div class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
        <p>{{ $role->name }}</p>
      </div>
      @else
      <input type="text" id="role" name="role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Admin" value="{{ $role->name }}">
      @endif
      @error('role')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5 flex flex-col">
      @foreach($permissions as $permission)
      <div class="flex items-center mb-4">
        @if($role->hasPermissionTo($permission->name))
        <input id="{{ $permission->id }}" name="permissions[]" type="checkbox" value="{{ $permission->name }}" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" checked>
        @else
        <input id="{{ $permission->id }}" name="permissions[]" type="checkbox" value="{{ $permission->name }}" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
        @endif
        <label for="{{ $permission->id }}" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{ $permission->name }}</label>
      </div>
      @error('permissions.' . $loop->index)
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
      @endforeach
    </div>
    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Submit</button>
  </form>
</div>
@endsection