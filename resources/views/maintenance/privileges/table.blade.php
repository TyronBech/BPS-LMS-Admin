@use('App\Enum\PermissionsEnum')
<div class="mx-auto px-2 font-sans flex-col">
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right whitespace-nowrap table-auto">
      <thead class="text-xs py-2 text-gray-700 uppercase bg-gray-300 text-center dark:bg-gray-500 dark:text-white">
        <tr>
          <th scope="col" class="p-2 text-center">User Type</th>
          <th scope="col" class="p-2 text-center">Category</th>
          <th scope="col" class="p-2 text-center">Duration Type</th>
          <th scope="col" class="p-2 text-center">Max Book Allowed</th>
          <th scope="col" class="p-2 text-center">Renewal Limit</th>
          <th scope="col" class="p-2 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($privileges as $item)
        <tr class="bg-white border-b text-center dark:bg-gray-800 dark:border-gray-600">
          <td class="min-w-40 h-14">{{ $item->user_type }}</td>
          <td class="min-w-40 h-14">{{ $item->category }}</td>
          <td class="min-w-40 h-14">{{ $item->duration_type }}</td>
          <td class="min-w-40 h-14">{{ $item->max_book_allowed }}</td>
          <td class="min-w-40 h-14">{{ $item->renewal_limit }}</td>
          <td class="pb-1 flex justify-center">
            @if(auth()->user()->can(PermissionsEnum::EDIT_PRIVILEGES))
            <button type="button" data-modal-target="edit-privilege-modal" data-modal-toggle="edit-privilege-modal" value="{{ $item->id }}" class="editBtn focus:outline-none text-white bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2">Edit</button>
            @endif
            @if(auth()->user()->can(PermissionsEnum::DELETE_PRIVILEGES))
            <button type="button" data-modal-target="delete-privilege-modal" data-modal-toggle="delete-privilege-modal" value="{{ $item->id }}" class="deleteBtn focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2">Delete</button>
            @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="3" class="text-center py-1.5">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
<!-- Edit modal -->
<div id="edit-privilege-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <!-- Modal content -->
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          Edit Privilege
        </h3>
        <button type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="edit-privilege-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <!-- Modal body -->
      <div class="p-4 md:p-5">
        <form class="space-y-4" action="{{ route('maintenance.update-privilege') }}" method="POST">
          @csrf
          @method('PUT')
          <div class="mb-5">
            <label for="user_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">User Type:</label>
            <select id="user_type" name="user_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
              <option selected disabled>Choose a type</option>
              <option value="employee">Employee</option>
              <option value="student">Student</option>
              <option value="visitor">Visitor</option>
            </select>
            @error('user_type')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <div class="mb-5">
            <label for="category" class="block mb-2 text-sm font-medium">Category:</label>
            <input type="text" id="category" name="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Teacher" required>
            @error('category')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <div class="mb-5">
            <label for="duration_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Select a type of duration:</label>
            <select id="duration_type" name="duration_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
              <option selected disabled>Choose an option</option>
              @foreach ($durations as $duration)
              <option value="{{ $duration }}">{{ $duration }}</option>
              @endforeach
            </select>
            @error('duration_type')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <div class="mb-5">
            <label for="max_book_allowed_update" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Max Book Allowed to Borrow:</label>
            <div class="relative flex items-center max-w-[8rem]">
              <button type="button" id="decrement-button" data-input-counter-decrement="max_book_allowed_update" class="bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-600 hover:bg-gray-200 border border-gray-300 rounded-s-lg p-3 h-11 focus:ring-gray-100 dark:focus:ring-gray-700 focus:ring-2 focus:outline-none">
                <svg class="w-3 h-3 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16" />
                </svg>
              </button>
              <input type="text" id="max_book_allowed_update" name="max_book_allowed_update" data-input-counter data-input-counter-min="0" data-input-counter-max="999" aria-describedby="helper-text-explanation" class="bg-gray-50 border-x-0 border-gray-300 h-11 text-center text-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500 block w-full py-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="999" value="5" required />
              <button type="button" id="increment-button" data-input-counter-increment="max_book_allowed_update" class="bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-600 hover:bg-gray-200 border border-gray-300 rounded-e-lg p-3 h-11 focus:ring-gray-100 dark:focus:ring-gray-700 focus:ring-2 focus:outline-none">
                <svg class="w-3 h-3 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16" />
                </svg>
              </button>
            </div>
            @error('max_book_allowed_update')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <div class="mb-5">
            <label for="renewal_limit_update" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Renewal Limit:</label>
            <div class="relative flex items-center max-w-[8rem]">
              <button type="button" id="decrement-button" data-input-counter-decrement="renewal_limit_update" class="bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-600 hover:bg-gray-200 border border-gray-300 rounded-s-lg p-3 h-11 focus:ring-gray-100 dark:focus:ring-gray-700 focus:ring-2 focus:outline-none">
                <svg class="w-3 h-3 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16" />
                </svg>
              </button>
              <input type="text" id="renewal_limit_update" name="renewal_limit_update" data-input-counter data-input-counter-min="0" data-input-counter-max="999" aria-describedby="helper-text-explanation" class="bg-gray-50 border-x-0 border-gray-300 h-11 text-center text-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500 block w-full py-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="999" value="5" required />
              <button type="button" id="increment-button" data-input-counter-increment="renewal_limit_update" class="bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-600 hover:bg-gray-200 border border-gray-300 rounded-e-lg p-3 h-11 focus:ring-gray-100 dark:focus:ring-gray-700 focus:ring-2 focus:outline-none">
                <svg class="w-3 h-3 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16" />
                </svg>
              </button>
            </div>
            @error('renewal_limit_update')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
            <p id="helper-text-explanation" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Please select a 3 digit number from 0 to 9.</p>
          </div>
          <input type="hidden" name="edit_privilege_id" id="edit_privilege_id" value="" />
          <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Submit</button>
        </form>
      </div>
    </div>
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
          <button data-modal-hide="delete-privilege-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  const editButtons           = document.querySelectorAll('.editBtn');
  const editInputID           = document.getElementById('edit_privilege_id');
  const user_type             = document.getElementById('user_type');
  const category              = document.getElementById('category');
  const duration_type         = document.getElementById('duration_type');
  const max_book_allowed      = document.getElementById('max_book_allowed_update');
  const renewal_limit         = document.getElementById('renewal_limit_update');
  editButtons.forEach(btn => {
    btn.addEventListener('click', function(event) {
      const privilegeId           = event.target.value;
      editInputID.value           = privilegeId;
      user_type.value             = event.target.parentElement.parentElement.children[0].textContent;
      category.value              = event.target.parentElement.parentElement.children[1].textContent;
      duration_type.value         = event.target.parentElement.parentElement.children[2].textContent;
      max_book_allowed.value      = event.target.parentElement.parentElement.children[3].textContent;
      renewal_limit.value         = event.target.parentElement.parentElement.children[4].textContent;
    });
  });
  const deleteButtons = document.querySelectorAll('.deleteBtn');
  const deleteInputID = document.getElementById('delete_privilege_id');
  deleteButtons.forEach(btn => {
    btn.addEventListener('click', function(event) {
      const privilegeId   = event.target.value;
      deleteInputID.value = privilegeId;
    });
  });
</script>