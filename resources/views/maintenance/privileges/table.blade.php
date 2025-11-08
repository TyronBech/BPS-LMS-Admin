@use('App\Enum\PermissionsEnum')
<form method="GET" class="flex items-center m-2">
  <label for="perPage" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
  <select name="perPage" id="perPage" onchange="this.form.submit()" class="border border-gray-300 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
  </select>
  <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">entries per page</span>
</form>
<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
      <tr>
        <th scope="col" class="px-6 py-3">User Type</th>
        <th scope="col" class="px-6 py-3 hidden sm:table-cell">Category</th>
        <th scope="col" class="px-6 py-3 hidden md:table-cell">Duration</th>
        <th scope="col" class="px-6 py-3 hidden lg:table-cell">Max Books</th>
        <th scope="col" class="px-6 py-3 hidden lg:table-cell">Renewal Limit</th>
        <th scope="col" class="px-6 py-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($privileges as $item)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
          <div class="text-base font-semibold">{{ $item->user_type }}</div>
          <div class="font-normal text-gray-500 sm:hidden">{{ $item->category }}</div>
        </th>
        <td class="px-6 py-4 hidden sm:table-cell">{{ $item->category }}</td>
        <td class="px-6 py-4 hidden md:table-cell">{{ $item->duration_type }}</td>
        <td class="px-6 py-4 hidden lg:table-cell">{{ $item->max_book_allowed }}</td>
        <td class="px-6 py-4 hidden lg:table-cell">{{ $item->renewal_limit }}</td>
        <td class="px-6 py-4">
          <div class="flex items-center space-x-2">
            @can(PermissionsEnum::EDIT_PRIVILEGES)
            <button type="button" data-modal-target="edit-privilege-modal" data-modal-toggle="edit-privilege-modal" data-privilege='@json($item)' class="editBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">Edit</button>
            @endcan
            @can(PermissionsEnum::DELETE_PRIVILEGES)
            <button type="button" data-modal-target="delete-privilege-modal" data-modal-toggle="delete-privilege-modal" value="{{ $item->id }}" class="deleteBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800">Delete</button>
            @endcan
          </div>
        </td>
      </tr>
      @empty
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
        <td colspan="6" class="px-6 py-4 text-center">No privileges found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
<!-- Edit modal -->
<div id="edit-privilege-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-2xl max-h-full">
    <!-- Modal content -->
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          Edit Privilege
        </h3>
        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="edit-privilege-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <!-- Modal body -->
      <form action="{{ route('maintenance.update-privilege') }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="edit_privilege_id" id="edit_privilege_id" value="" />
        <div class="p-4 md:p-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <label for="edit_duration_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Duration Type:</label>
              <select id="edit_duration_type" name="duration_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                <option disabled>Choose an option</option>
                @foreach ($durations as $duration)
                <option value="{{ $duration }}">{{ $duration }}</option>
                @endforeach
              </select>
              @error('duration_type')
              <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
              @enderror
            </div>
            <div>
              <label for="max_book_allowed_update" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Max Books Allowed:</label>
              <input type="number" id="max_book_allowed_update" name="max_book_allowed_update" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g., 5" min="0" required>
              @error('max_book_allowed_update')
              <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
              @enderror
            </div>
            <div>
              <label for="renewal_limit_update" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Renewal Limit:</label>
              <input type="number" id="renewal_limit_update" name="renewal_limit_update" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g., 5" min="0" required>
              @error('renewal_limit_update')
              <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
              @enderror
            </div>
          </div>
        </div>
        <div class="flex items-center justify-end p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
          <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Update</button>
          <button data-modal-hide="edit-privilege-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancel</button>
        </div>
      </form>
    </div>
  </div>
  <div class="p-4">
    {{ $privileges->withQueryString()->links() }}
  </div>
</div>
<!-- Delete modal -->
<div id="delete-privilege-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-privilege-modal">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this privilege?</h3>
        <form action="{{ route('maintenance.delete-privilege') }}" method="POST" id="delete-privilege-form">
          @csrf
          @method('DELETE')
          <input type="hidden" name="delete_privilege_id" id="delete_privilege_id" value="" />
          <button data-modal-hide="delete-privilege-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="delete-privilege-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.editBtn');
    const editModal = {
      id: document.getElementById('edit_privilege_id'),
      user_type: document.getElementById('edit_user_type'),
      category: document.getElementById('edit_category'),
      duration_type: document.getElementById('edit_duration_type'),
      max_book_allowed: document.getElementById('max_book_allowed_update'),
      renewal_limit: document.getElementById('renewal_limit_update')
    };

    editButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        const privilege = JSON.parse(this.dataset.privilege);
        editModal.id.value = privilege.id;
        editModal.user_type.value = privilege.user_type;
        editModal.category.value = privilege.category;
        editModal.duration_type.value = privilege.duration_type;
        editModal.max_book_allowed.value = privilege.max_book_allowed;
        editModal.renewal_limit.value = privilege.renewal_limit;
      });
    });

    const deleteButtons = document.querySelectorAll('.deleteBtn');
    const deleteInputID = document.getElementById('delete_privilege_id');
    deleteButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        deleteInputID.value = this.value;
      });
    });
  });
</script>