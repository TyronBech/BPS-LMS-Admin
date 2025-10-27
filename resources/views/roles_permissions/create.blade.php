@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <h1 class="font-semibold text-center text-3xl md:text-4xl mb-8">Maintenance</h1>
  <div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Add Role</h5>
      <a href="{{ route('maintenance.roles-and-permissions.management') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 mt-4 sm:mt-0">
        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
        </svg>
        Back
      </a>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">
    <form action="{{ route('maintenance.roles-and-permissions.store-role') }}" method="POST">
      @csrf
      <h6 class="mb-4 text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Role Information</h6>
      <div class="mb-5">
        <label for="role" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Role Name:</label>
        <input type="text" id="role" name="role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Admin" value="{{ old('role') }}" required>
        @error('role')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
      </div>

      <h6 class="mb-4 text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Assign Permissions</h6>
      <div class="mb-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-3">
        @foreach($permissions as $permission)
        <div class="flex items-center">
          <input id="{{ $permission->id }}" name="permissions[]" type="checkbox" value="{{ $permission->name }}" data-permission="{{ $permission->name }}" class="perm-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
          <label for="{{ $permission->id }}" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
            {{ $permission->name }}
          </label>
        </div>
        @endforeach
      </div>
      @error('permissions')
      <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
      @enderror

      <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Note: <span class="font-medium">View</span> is required to add, edit, or delete permissions in maintenance.</p>

      <div class="flex justify-end">
        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Submit</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const restrictions = {
      "View Users Maintenance": ["Add Users", "Edit Users", "Delete Users"],
      "View Books Maintenance": ["Add Books", "Edit Books", "Delete Books"],
      "View Book Categories Maintenance": ["Add Categories", "Edit Categories", "Delete Categories"],
      "View Privileges Maintenance": ["Add Privileges", "Edit Privileges", "Delete Privileges"],
      "View Penalty Rules Maintenance": ["Add Penalty Rule", "Edit Penalty Rule", "Delete Penalty Rule"],
      "View Transactions Maintenance": ["Edit Transactions"]
    };

    function toggleRestrictions(view, actions) {
      const viewCheckbox = document.querySelector(`[data-permission='${view}']`);
      const isChecked = viewCheckbox.checked;

      actions.forEach(action => {
        const actionCheckbox = document.querySelector(`[data-permission='${action}']`);
        if (actionCheckbox) {
          const label = actionCheckbox.nextElementSibling; // get <label> beside it
          actionCheckbox.disabled = !isChecked;

          if (!isChecked) {
            actionCheckbox.checked = false;
            actionCheckbox.classList.add("opacity-50", "cursor-not-allowed");
            if (label) label.classList.add("opacity-50", "cursor-not-allowed");
          } else {
            actionCheckbox.classList.remove("opacity-50", "cursor-not-allowed");
            if (label) label.classList.remove("opacity-50", "cursor-not-allowed");
          }
        }
      });
    }

    // Attach listeners
    Object.entries(restrictions).forEach(([view, actions]) => {
      const viewCheckbox = document.querySelector(`[data-permission='${view}']`);
      if (viewCheckbox) {
        // Run once at load
        toggleRestrictions(view, actions);
        // Listen for changes
        viewCheckbox.addEventListener("change", () => toggleRestrictions(view, actions));
      }
    });
  });
</script>
@endsection