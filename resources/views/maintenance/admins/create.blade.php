@extends('layouts.admin-app')
@section('content')
@use(Spatie\Permission\Models\Role)
@php
  $adminID = null;
@endphp
<h1 class="font-semibold text-center text-4xl p-5">Maintenance</h1>
<div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
  <div class="flex justify-between">
    <h5 class="mb-1 text-2xl font-bold tracking-tight">Add Admin</h5>
    <a href="{{ route('maintenance.admins') }}" class="inline-flex items-center px-3 py-1 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
      Back
      <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
      </svg>
    </a>
  </div>
  <hr class="h-px my-3 bg-gray-200 border-0">
  <form action="{{ route('maintenance.search-user') }}" class="max-w-2xl mx-auto" method="POST">
    @csrf
    <div class="mb-5">
      <label for="user-info" class="block mb-2 text-sm font-medium">Search for the user</label>
      <div class="flex items-center">
        <input type="text" id="user-info" name="user-info" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Search...." required>
        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 ms-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Search</button>
      </div>
      @error('user-info')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
  </form>
  <form action="{{ route('maintenance.store-admin') }}" class="max-w-2xl mx-auto" method="POST">
    @csrf
    <table class="w-full text-sm text-left rtl:text-right">
      <thead class="text-xs py-2 text-gray-700 uppercase bg-gray-300 text-center dark:bg-gray-500 dark:text-white">
        <tr>
          <th scope="col" class="p-2 text-center">RFID</th>
          <th scope="col" class="p-2 text-center">Name</th>
          <th scope="col" class="p-2 text-center">Email</th>
          <th scope="col" class="p-2 text-center">Role</th>
        </tr>
      </thead>
      <tbody>
        @forelse($searched as $item)
        @php
          $adminID = $item->rfid;
        @endphp
        <tr class="bg-white border-b text-center dark:bg-gray-800 dark:border-gray-600">
          <td>{{ $item->rfid }}</td>
          <td>{{ $item->last_name }}, {{ $item->first_name }} {{ $item->middle_name }}</td>
          <td>{{ $item->email }}</td>
          <td>
            <select name="role" id="role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
              <option value="None" selected>Select Role</option>
              @foreach($roles as $role)
              @if($item->hasRole($role->name))
              <option value="{{ $role->id }}" selected>{{ $role->name }}</option>
              @else
              <option value="{{ $role->id }}">{{ $role->name }}</option>
              @endif
              @endforeach
            </select>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="4" class="text-center py-1.5">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
    <input type="hidden" name="adminID" value="{{ $adminID }}">
    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 mt-5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Submit</button>
  </form>
</div>
@endsection