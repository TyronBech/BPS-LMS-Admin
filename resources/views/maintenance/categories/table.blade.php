@use('App\Enum\PermissionsEnum')
<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
  <form method="GET" class="flex items-center m-2">
    <label for="perPage" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
    <input type="number" name="perPage" id="perPage" min="1" max="500" onchange="this.form.submit()" value="{{ old('perPage', $perPage) }}" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-400 focus:border-primary-400 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">entries per page</span>
  </form>
  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
      <tr>
        <th scope="col" class="px-6 py-3">Name</th>
        <th scope="col" class="px-6 py-3 hidden sm:table-cell">Legend</th>
        <th scope="col" class="px-6 py-3 hidden md:table-cell">Duration of Borrow (Days)</th>
        <th scope="col" class="px-6 py-3">Borrowable</th>
        <th scope="col" class="px-6 py-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($categories as $item)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
          <div class="text-base font-semibold">{{ $item->name }}</div>
          <div class="font-normal text-gray-500 sm:hidden">{{ $item->legend }}</div>
        </th>
        <td class="px-6 py-4 hidden sm:table-cell">{{ $item->legend }}</td>
        <td class="px-6 py-4 hidden md:table-cell">{{ (int) $item->borrow_duration_days === 0 ? 'Cannot be borrowed' : $item->borrow_duration_days }}</td>
        <td class="px-6 py-4">
          @if((int) $item->borrow_duration_days === 0)
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Not Borrowable</span>
          @else
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Borrowable</span>
          @endif
        </td>
        <td class="px-6 py-4">
          <div class="flex items-center space-x-2">
            @can(PermissionsEnum::EDIT_CATEGORIES)
            <button type="button" data-modal-target="edit-category-modal" data-modal-toggle="edit-category-modal" data-category='@json($item)' class="editBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">Edit</button>
            @endcan
            @can(PermissionsEnum::DELETE_CATEGORIES)
            <button type="button" data-modal-target="delete-category-modal" data-modal-toggle="delete-category-modal" value="{{ $item->id }}" class="deleteBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800">Delete</button>
            @endcan
          </div>
        </td>
      </tr>
      @empty
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
        <td colspan="5" class="px-6 py-4 text-center">No categories found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="p-4">
    {{ $categories->withQueryString()->links() }}
  </div>
</div>
<!-- Edit modal -->
<div id="edit-category-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-lg max-h-full">
    <!-- Modal content -->
    <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          Edit Category
        </h3>
        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="edit-category-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <!-- Modal body -->
      <form action="{{ route('maintenance.update-category') }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="edit_category_id" id="edit_category_id" value="" />
        <div class="p-4 md:p-5 space-y-4">
          <div>
            <label for="edit_legend" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Legend:</label>
            <input type="text" name="legend" id="edit_legend" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., FIL" />
          </div>
          <div>
            <label for="edit_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name:</label>
            <input type="text" name="name" id="edit_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., Filipino" required />
          </div>
          <div class="flex w-full items-center space-x-4">
            <div class="flex items-center space-x-2">
              <input type="hidden" name="can_borrow_edit" id="can_borrow_edit_input" value="1">
              <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" id="can_borrow_edit_switch" class="sr-only" />
                <div id="can_borrow_edit_track" class="w-11 h-6 bg-gray-200 rounded-full relative transition-colors">
                  <span id="can_borrow_edit_knob" class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform"></span>
                </div>
              </label>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Can Be Borrowed</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Uncheck to make this category not borrowable.</p>
            </div>
          </div>

          <div id="borrow_duration_days_edit_wrapper">
            <label for="borrow_duration_days_edit" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Duration of Borrow (Days):</label>
            <input type="number" id="borrow_duration_days_edit" name="borrow_duration_days_edit" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 5" min="1" max="999" required />
          </div>
        </div>
        <!-- Modal footer -->
        <div class="flex items-center justify-end p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
          <button type="submit" class="text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">Update</button>
          <button data-modal-hide="edit-category-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Delete modal -->
<div id="delete-category-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-category-modal">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this Category?</h3>
        <form action="{{ route('maintenance.delete-category') }}" method="POST" id="delete-category-form">
          @csrf
          @method('DELETE')
          <input type="hidden" name="delete_category_id" id="delete_category_id" value="" />
          <button data-modal-hide="delete-category-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="delete-category-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.editBtn');
    const editModal = {
      id: document.getElementById('edit_category_id'),
      legend: document.getElementById('edit_legend'),
      name: document.getElementById('edit_name'),
      duration: document.getElementById('borrow_duration_days_edit'),
      hiddenInput: document.getElementById('can_borrow_edit_input'),
      switchEl: document.getElementById('can_borrow_edit_switch'),
      track: document.getElementById('can_borrow_edit_track'),
      knob: document.getElementById('can_borrow_edit_knob'),
      durationWrapper: document.getElementById('borrow_duration_days_edit_wrapper')
    };

    const updateEditSwitchUI = () => {
      if (!editModal.hiddenInput || !editModal.switchEl || !editModal.track || !editModal.knob) return;
      const borrowable = editModal.hiddenInput.value === '1';
      editModal.switchEl.checked = borrowable;
      if (editModal.durationWrapper) editModal.durationWrapper.classList.toggle('hidden', !borrowable);
      if (editModal.duration) editModal.duration.required = borrowable;
      if (!borrowable && editModal.duration) editModal.duration.value = 0;
      else if (borrowable && editModal.duration && (!editModal.duration.value || Number(editModal.duration.value) < 1)) editModal.duration.value = 1;

      if (borrowable) {
        editModal.track.classList.remove('bg-gray-200');
        editModal.track.classList.add('bg-primary-500');
        editModal.knob.style.transform = 'translateX(20px)';
      } else {
        editModal.track.classList.remove('bg-primary-500');
        editModal.track.classList.add('bg-gray-200');
        editModal.knob.style.transform = 'translateX(0)';
      }
    };

    if (editModal.switchEl) {
      editModal.switchEl.addEventListener('change', function() {
        if (!editModal.hiddenInput) return;
        editModal.hiddenInput.value = editModal.switchEl.checked ? '1' : '0';
        updateEditSwitchUI();
      });
    }

    editButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        const category = JSON.parse(this.dataset.category);
        const categoryDuration = Number(category.borrow_duration_days ?? 0);
        const isBorrowable = categoryDuration > 0;

        editModal.id.value = category.id;
        editModal.legend.value = category.legend;
        editModal.name.value = category.name;
        if (editModal.hiddenInput) editModal.hiddenInput.value = isBorrowable ? '1' : '0';
        if (editModal.duration) editModal.duration.value = isBorrowable ? categoryDuration : 0;
        updateEditSwitchUI();
      });
    });

    const deleteButtons = document.querySelectorAll('.deleteBtn');
    const deleteInputID = document.getElementById('delete_category_id');
    deleteButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        deleteInputID.value = this.value;
      });
    });
  });
</script>
