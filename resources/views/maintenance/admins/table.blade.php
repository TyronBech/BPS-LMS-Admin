@use('App\Enum\RolesEnum')
@use('Spatie\Permission\Models\Role')
@php
$adminID = null;
@endphp
<form method="GET" class="justify-end m-2 w-full sm:w-auto">
  <input type="hidden" name="search" value="{{ request('search', '') }}">
  <label for="perPage" class="mr-2 text-sm font-medium text-gray-500">Show</label>
  <input type="number" name="perPage" id="perPage" min="1" max="500" onchange="this.form.submit()" value="{{ old('perPage', $perPage) }}" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-400 focus:border-primary-400 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
  <span class="ml-2 text-sm text-gray-600">entries per page</span>
</form>
<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
      <tr>
        <th scope="col" class="px-6 py-3">Name</th>
        <th scope="col" class="px-6 py-3 hidden md:table-cell">Role</th>
        <th scope="col" class="px-6 py-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($admins as $admin)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
          <div class="text-base font-semibold">{{ $admin->last_name }}, {{ $admin->first_name }} {{ $admin->middle_name ? substr($admin->middle_name, 0, 1) . '.' : '' }}</div>
          <div class="font-normal text-gray-500">{{ $admin->email }}</div>
          <div class="font-normal text-gray-500 md:hidden mt-1">Role: {{ implode(', ', $admin->getRoleNames()->toArray()) }}</div>
        </th>
        <td class="px-6 py-4 hidden md:table-cell">
          {{ implode(', ', $admin->getRoleNames()->toArray()) }}
        </td>
        <td class="px-6 py-4 w-56">
          <div class="flex items-center space-x-2">
            <a href="{{ route('maintenance.edit-admin', ['id' => $admin->id, 'return_to' => request()->fullUrl()]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">Edit</a>
            @if(auth()->user()->hasRole(RolesEnum::SUPER_ADMIN) && auth()->user()->id != $admin->id)
            <button type="button" data-modal-target="delete-admin-modal" data-modal-toggle="delete-admin-modal" value="{{ $admin->id }}" class="deleteAdminBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800">Delete</button>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
        <td colspan="3" class="px-6 py-4 text-center">No admins found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="p-4">
    {{ $admins->withQueryString()->links('pagination::tailwind') }}
  </div>
</div>
<div id="delete-admin-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-admin-modal">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this admin?</h3>
        <form action="{{ route('maintenance.delete-admin') }}" method="POST">
          @csrf
          @method('DELETE')
          <input type="hidden" name="id" id="delete_admin_id" value="" />
          <button data-modal-hide="delete-admin-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="delete-admin-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const deleteAdminBtns = document.querySelectorAll('.deleteAdminBtn');
    const deleteAdminIDInput = document.getElementById('delete_admin_id');

    if (deleteAdminBtns.length > 0 && deleteAdminIDInput) {
      deleteAdminBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          const adminId = this.value;
          deleteAdminIDInput.value = adminId;
        });
      });
    }
  });
</script>