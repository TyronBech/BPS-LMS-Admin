@extends('layouts.admin-app')
@section('content')
@use('App\Enum\RolesEnum')
@use('App\Enum\PermissionsEnum')
<h1 class="font-semibold text-center text-4xl p-5">Home</h1>
<div class="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
  <div class="flex flex-col justify-between max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <div>
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Current Users</h5>
      <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Total number of users currently timed-in in the library.</p>
    </div>
    <div>
      <h1 id="timed-in-count" class="text-9xl text-center font-extrabold dark:text-gray-600"></h1>
    </div>
    <a href="#" class="inline-flex justify-center items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
      Refresh
      <svg class="w-6 h-6 ml-1 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4" />
      </svg>
    </a>
  </div>
  <div class="flex items-center justify-center">
    <a href="#" class="block max-w-xl p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
      <svg class="w-7 h-7 text-gray-500 dark:text-gray-300 mb-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
        <path d="M18 5h-.7c.229-.467.349-.98.351-1.5a3.5 3.5 0 0 0-3.5-3.5c-1.717 0-3.215 1.2-4.331 2.481C8.4.842 6.949 0 5.5 0A3.5 3.5 0 0 0 2 3.5c.003.52.123 1.033.351 1.5H2a2 2 0 0 0-2 2v3a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1V7a2 2 0 0 0-2-2ZM8.058 5H5.5a1.5 1.5 0 0 1 0-3c.9 0 2 .754 3.092 2.122-.219.337-.392.635-.534.878Zm6.1 0h-3.742c.933-1.368 2.371-3 3.739-3a1.5 1.5 0 0 1 0 3h.003ZM11 13H9v7h2v-7Zm-4 0H2v5a2 2 0 0 0 2 2h3v-7Zm6 0v7h3a2 2 0 0 0 2-2v-5h-5Z" />
      </svg>
      <p class="font-normal text-gray-700 dark:text-gray-300">Welcome! {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Role</h5>
      @foreach(auth()->user()->getRoleNames() as $role)
      <p class="font-normal text-gray-700 dark:text-gray-300">You are a {{ $role }}.</p>
      @endforeach
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Permissions</h5>
      <div class="overflow-y-auto max-h-48">
        @foreach(auth()->user()->getAllPermissions() as $permission)
        <p class="font-normal text-gray-700 dark:text-gray-300">You can {{ $permission->name }}</p>
        @endforeach
      </div>
    </a>
  </div>
</div>
<script>
  async function fetchActiveCount() {
    try {
      const response = await fetch("{{ route('fetch-current-count') }}");
      const data = await response.json();
      console.log('New active count:', data.active_count);
      document.getElementById('timed-in-count').textContent = data.active_count;
    } catch (error) {
      console.error('Error fetching active count:', error);
      document.getElementById('timed-in-count').textContent = 'ERR';
    }
  }

  // Fetch every 5 seconds
  setInterval(fetchActiveCount, 5000);

  // Fetch immediately on page load
  document.addEventListener('DOMContentLoaded', fetchActiveCount);
</script>
@endsection