@extends('layouts.admin-app')
@section('content')
@use(Spatie\Permission\Models\Role)
@php
$adminID = null;
@endphp
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <h1 class="font-semibold text-center text-3xl md:text-4xl mb-8">Maintenance</h1>
  <div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Add Admin</h5>
      <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 mt-4 sm:mt-0">
        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
        </svg>
        Back
      </a>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    {{-- Search Form --}}
    <form action="{{ route('maintenance.search-user') }}" class="max-w-2xl mx-auto" method="POST">
      @csrf
      <div class="mb-5">
        <label for="user-info" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Search for a user to make an admin</label>
        <div class="flex items-center">
          <input type="text" id="user-info" name="user-info" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Enter Name, Email, or RFID" required focus="true" value="{{ old('user-info') }}">
          <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 ms-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Search</button>
        </div>
        @error('user-info')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
      </div>
    </form>

    {{-- Results and Assign Role Form --}}
    @if(isset($searched))
      @if($searched)
        <hr class="h-px my-6 bg-gray-200 border-0 dark:bg-gray-700">
        <form action="{{ route('maintenance.store-admin') }}" class="max-w-4xl mx-auto" method="POST">
          @csrf
          <h6 class="mb-4 text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Search Results</h6>
          <div class="space-y-4">
            @php
            $adminID = $searched->rfid;
            @endphp
            <div class="p-4 border rounded-lg dark:border-gray-600">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- User Info --}}
                <div class="md:col-span-2">
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $searched->last_name }}, {{ $searched->first_name }} {{ $searched->middle_name }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ $searched->email }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">RFID: {{ $searched->rfid }}</p>
                </div>
                {{-- Role Selection --}}
                <div>
                  <label for="role" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Assign Role</label>
                  <select name="role" id="role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                    <option value="" disabled selected>Select a Role</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>
          <input type="hidden" name="adminID" value="{{ $adminID }}">
          <div class="flex justify-end mt-6">
            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Submit</button>
          </div>
        </form>
      @else
        <div class="text-center py-4 text-gray-500 dark:text-gray-400">
          No users found.
        </div>
      @endif
    @endif
  </div>
</div>
@endsection