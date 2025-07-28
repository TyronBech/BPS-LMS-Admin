@use('App\Enum\PermissionsEnum')
<div id="checked-books" class="hidden flex-row">
  <h5 id="selectedHeader" class="text-sm font-bold tracking-tight border-2 rounded-lg px-5 py-2 me-2 mb-2">Selected</h5>
  @can(PermissionsEnum::DELETE_BOOKS, 'admin')
  <button data-modal-target="bulk-delete-book-modal" data-modal-toggle="bulk-delete-book-modal" class="bulkDeleteBookBtn focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 me-2 mb-2" type="button" value="">
    Delete
  </button>
  @endcan
</div>
<div class="mx-auto px-2 font-sans flex-col">
  <form method="GET" class="mb-4">
    <label for="perPage" class="mr-2 text-sm font-medium text-gray-700">Show</label>
    <select name="perPage" id="perPage" onchange="this.form.submit()" class="border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2, dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
      <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
      <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
      <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
    </select>
    <span class="ml-2 text-sm text-gray-600">entries per page</span>
  </form>
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right whitespace-nowrap table-auto">
      <thead class="text-xs py-2 text-gray-700 uppercase bg-gray-300 text-center dark:bg-gray-500 dark:text-white">
        <tr>
          <th scope="col" class="p-2 text-center">
            <input id="selectAll" type="checkbox" value="" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
            <label for="selectAll" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300"></label>
          </th>
          <th scope="col" class="p-2 text-center">Accession</th>
          <th scope="col" class="p-2 text-center">Title</th>
          <th scope="col" class="p-2 text-center">Barcode</th>
          <th scope="col" class="p-2 text-center">Remarks</th>
          <th scope="col" class="p-2 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($books as $item)
        <tr class="bg-white border-b text-center dark:bg-gray-800 dark:border-gray-600">
          <td>
            <div class="flex items-center ml-6">
              <input id="bookCheck" type="checkbox" value="{{ $item->id }}" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
              <label for="bookCheck" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300"></label>
            </div>
          </td>
          <td class="max-w-40 h-14">{{ $item->accession }}</td>
          <td class="max-w-72 overflow-hidden text-ellipsis">{{ $item->title }}</td>
          <td class="max-w-60 text-start">
            <img src="data:image/jpg;base64,{{ $item->barcode }}" alt="barcode" />
          </td>
          <td class="max-w-36">{{ $item->remarks }}</td>
          <td class="pb-1 flex justify-center">
            <a href="{{ route('maintenance.view-book', ['accession' => $item->accession]) }}" id="viewBtn" name="viewBtn" class="focus:outline-none text-white bg-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2 dark:focus:ring-yellow-900">View</a>
            @can(PermissionsEnum::EDIT_BOOKS, 'admin')
            <a href="{{ route('maintenance.edit-book', ['id' => $item->id]) }}" id="editBtn" name="editBtn" class="text-white bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2">Edit</a>
            @endcan
            @can(PermissionsEnum::DELETE_BOOKS, 'admin')
            <button data-modal-target="delete-book-modal" data-modal-toggle="delete-book-modal" class="deleteBookBtn focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2" type="button" value="{{ $item->id }}">
              Delete
            </button>
            @endcan
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="11" class="text-center py-1.5">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
    <div class="m-4">
      {{ $books->links() }}
    </div>
  </div>
</div>
<div id="delete-book-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-book-modal">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this book?</h3>
        <form action="{{ route('maintenance.delete-book') }}" method="POST" class="flex items-center justify-center">
          @csrf
          @method('DELETE')
          <input type="hidden" name="id" id="delete_book_id" value="" />
          <button data-modal-hide="delete-book-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="delete-book-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<div id="bulk-delete-book-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="bulk-delete-book-modal">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete these books?</h3>
        <form action="{{ route('maintenance.bulk-delete-book') }}" method="POST" class="flex items-center justify-center">
          @csrf
          @method('DELETE')
          <input type="hidden" name="ids" id="bulk-delete_book_ids" value="" />
          <button id="bulkDeleteBookBtn" data-modal-hide="bulk-delete-book-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="bulk-delete-book-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  const deleteBookBtn = document.querySelectorAll('.deleteBookBtn');
  const deleteBookID = document.getElementById('delete_book_id');
  deleteBookBtn.forEach(btn => {
    btn.addEventListener('click', function(event) {
      const bookId = event.target.value;
      deleteBookID.value = bookId;
    });
  });
  const bookCheck = document.querySelectorAll('#bookCheck');
  let checkedBooks = 0;
  const checkedBooksContainer = document.getElementById('checked-books');
  const bulkDeleteBookIds = document.getElementById('bulk-delete_book_ids');
  const bulkDeleteBookBtn = document.getElementById('bulkDeleteBookBtn');
  const selectedHeader = document.getElementById('selectedHeader');
  const selectAllCheckbox = document.getElementById('selectAll');
  bulkDeleteBookIds.value = '';
  bulkDeleteBookBtn.value = '';
  const selectedIds = new Set();
  bookCheck.forEach(check => {
    check.addEventListener('change', function(event) {
      const bookId = event.target.value;
      if (event.target.checked) {
        selectedIds.add(bookId);
        checkedBooks++;
      } else {
        selectedIds.delete(bookId);
        checkedBooks--;
        selectAllCheckbox.checked = false;
      }
      bulkDeleteBookIds.value = Array.from(selectedIds).join(',');
      bulkDeleteBookBtn.value = Array.from(selectedIds).join(',');
      if (checkedBooks > 0) {
        checkedBooksContainer.classList.replace('hidden', 'flex');
        selectedHeader.textContent = `Selected (${checkedBooks})`;
      } else {
        checkedBooksContainer.classList.replace('flex', 'hidden');
      }
    });
  });
  selectAllCheckbox.addEventListener('change', function(event) {
    checkedBooks = 0;
    bookCheck.forEach(check => {
      check.checked = event.target.checked;
      const bookId = check.value;
      if (event.target.checked) {
        selectedIds.add(bookId);
        checkedBooks++;
      } else {
        selectedIds.delete(bookId);
        checkedBooks = 0;
      }
      bulkDeleteBookIds.value = Array.from(selectedIds).join(',');
      bulkDeleteBookBtn.value = Array.from(selectedIds).join(',');
      if (checkedBooks > 0) {
        checkedBooksContainer.classList.replace('hidden', 'flex');
        selectedHeader.textContent = `Selected (${checkedBooks})`;
      } else {
        checkedBooksContainer.classList.replace('flex', 'hidden');
      }
    });
  });
</script>