@extends('layouts.admin-app')
@section('content')
@php
$roleID = null;
@endphp
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <h1 class="font-semibold text-center text-3xl md:text-4xl p-5">Maintenance</h1>
  <div class="w-full p-4 sm:p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Roles</h5>
      <form action="{{ route('maintenance.roles-and-permissions.create-role') }}" method="GET" class="mt-4 sm:mt-0">
        @csrf
        <button type="submit" class="w-full sm:w-auto text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-400 dark:hover:bg-primary-500 focus:outline-none dark:focus:ring-primary-500">Add New Role</button>
      </form>
    </div>
    <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      @foreach($roles_with_permissions as $role)
      <div class="flex flex-col w-full p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $role->name }}</h5>
        <hr class="h-px my-2 bg-gray-200 border-0 dark:bg-gray-600">
        <span class="font-semibold text-gray-800 dark:text-white">Permissions:</span>
        <ul class="font-normal overflow-y-auto h-36 text-gray-700 dark:text-gray-400 my-2 space-y-1">
          @forelse($role->permissions as $permission)
          <li class="text-sm text-gray-700 dark:text-gray-300">
            - {{ $permission->name }}
          </li>
          @empty
          <li class="text-sm text-gray-500 dark:text-gray-400">No permissions assigned.</li>
          @endforelse
        </ul>
        <hr class="h-px my-2 bg-gray-200 border-0 dark:bg-gray-600">
        <span class="font-semibold text-gray-800 dark:text-white">Assigned Users:</span>
        <ul class="font-normal overflow-y-auto h-28 text-gray-700 dark:text-gray-400 my-2 space-y-1">
          @php $userCount = 0; @endphp
          @foreach($admins as $admin)
          @if($admin->hasRole($role->name))
          <li class="text-sm text-gray-700 dark:text-gray-300">
            - {{ $admin->last_name }}, {{ $admin->first_name }}
          </li>
          @php $userCount++; @endphp
          @endif
          @endforeach
          @if($userCount === 0)
          <li class="text-sm text-gray-500 dark:text-gray-400">No users assigned.</li>
          @endif
        </ul>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-2 mt-auto pt-4">
          <a href="{{ route('maintenance.roles-and-permissions.edit-role', $role->id) }}" id="editBtn" name="editBtn" class="w-full sm:w-auto text-center text-white bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">Edit</a>
          @if($role->name != 'Super Admin')
          <button data-modal-target="popup-modal" data-modal-toggle="popup-modal" class="deleteBtn w-full sm:w-auto focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5" type="button" value="{{ $role->id }}">Delete</button>
          @endif
        </div>
      </div>
      @endforeach
    </div>

    <div class="mt-12">
      <h5 id="permissions" class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mb-4">Permissions</h5>
      <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">
      <div class="flex justify-end mb-4">
        <form method="GET" class="flex items-center">
          <label for="perPage" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
          <select name="perPage" id="perPage" onchange="this.form.submit()" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-400 focus:border-primary-400 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
          </select>
          <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">entries per page</span>
        </form>
      </div>
      <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
              <th scope="col" class="px-6 py-3">No.</th>
              <th scope="col" class="px-6 py-3">Name</th>
              <th scope="col" class="px-6 py-3 text-center">Assigned Roles</th>
            </tr>
          </thead>
          <tbody>
            @forelse($permissions as $item)
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
              <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                {{ $permissions->firstItem() + $loop->index }}
              </th>
              <td class="px-6 py-4">{{ $item->name }}</td>
              <td class="px-6 py-4 text-center">{{ $item->roles->count() }}</td>
            </tr>
            @empty
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
              <td colspan="3" class="px-6 py-4 text-center">No permissions found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-4">
        {{ $permissions->withQueryString()->fragment('permissions')->links() }}
      </div>
    </div>
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
        <form action="{{ route('maintenance.roles-and-permissions.delete-role') }}" method="POST">
          @csrf
          @method('DELETE')
          <input type="hidden" name="deleteRole" id="deleteRole" value="">
          <button data-modal-hide="popup-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="popup-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const deleteBtns = document.querySelectorAll('.deleteBtn');
    const deleteRoleInput = document.getElementById('deleteRole');

    deleteBtns.forEach((btn) => {
      btn.addEventListener('click', () => {
        const roleID = btn.value;
        if (deleteRoleInput) {
          deleteRoleInput.value = roleID;
        }
      });
    });
  });
</script>
@endsection