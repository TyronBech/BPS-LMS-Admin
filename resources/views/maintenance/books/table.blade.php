@use('App\Enum\PermissionsEnum')
<div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 justify-between items-center mb-4 w-full">
  <div class="w-full md:w-auto">
    <div id="checked-books" class="hidden flex-wrap items-center gap-2">
      <h5 id="selectedHeader" class="text-sm font-bold tracking-tight border-2 rounded-lg px-5 py-2">Selected</h5>
      @can(PermissionsEnum::DELETE_BOOKS, 'admin')
      <button data-modal-target="bulk-delete-book-modal" data-modal-toggle="bulk-delete-book-modal" class="bulkDeleteBookBtn focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2" type="button" value="">
        Delete
      </button>
      @endcan
      <form action="{{ route('maintenance.export-barcode') }}" method="GET" class="flex skip-loader">
        @csrf
        <input type="hidden" name="ids" id="export_barcode_ids" value="" />
        <button type="submit" title="Export Barcode" value="" class="exportBarcode text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
          Generate Barcode
        </button>
      </form>
    </div>
  </div>
  <form method="GET" class="flex items-center justify-end w-full md:w-auto">
    <label for="perPage" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
    <input type="hidden" name="search" value="{{ request('search', '') }}">
    <input type="hidden" name="category" value="{{ request('category', '') }}">
    <select name="perPage" id="perPage" onchange="this.form.submit()" class="border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
      <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
      <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
      <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
      <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
      <option value="250" {{ $perPage == 250 ? 'selected' : '' }}>250</option>
      <option value="500" {{ $perPage == 500 ? 'selected' : '' }}>500</option>
    </select>
    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">entries</span>
  </form>
</div>
<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
      <tr>
        <th scope="col" class="p-4">
          <div class="flex items-center">
            <input id="selectAll" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
            <label for="selectAll" class="sr-only">checkbox</label>
          </div>
        </th>
        <th scope="col" class="px-6 py-3">Title</th>
        <th scope="col" class="px-6 py-3 hidden md:table-cell">Accession</th>
        <th scope="col" class="px-6 py-3 hidden lg:table-cell">Call Number</th>
        <th scope="col" class="px-6 py-3 hidden xl:table-cell">Remarks</th>
        <th scope="col" class="px-6 py-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($books as $item)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
        <td class="w-4 p-4">
          <div class="flex items-center">
            <input id="bookCheck" type="checkbox" value="{{ $item->id }}" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
            <label for="bookCheck" class="sr-only">checkbox</label>
          </div>
        </td>
        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
          <div class="text-base font-semibold">{{ $item->title }}</div>
          <div class="font-normal text-gray-500 md:hidden">Acc: {{ $item->accession }}</div>
        </th>
        <td class="px-6 py-4 hidden md:table-cell">{{ $item->accession }}</td>
        <td class="px-6 py-4 hidden lg:table-cell">{{ $item->call_number }}</td>
        <td class="px-6 py-4 hidden xl:table-cell">{{ $item->remarks }}</td>
        <td class="px-6 py-4">
          <div class="flex items-center space-x-2">
            <a href="{{ route('maintenance.view-book', ['accession' => $item->accession]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-yellow-500 rounded-lg hover:bg-yellow-600 focus:ring-4 focus:outline-none focus:ring-yellow-300 dark:bg-yellow-400 dark:hover:bg-yellow-500 dark:focus:ring-yellow-800">View</a>
            @can(PermissionsEnum::EDIT_BOOKS, 'admin')
            <a href="{{ route('maintenance.edit-book', ['id' => $item->id]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">Edit</a>
            @endcan
            @can(PermissionsEnum::DELETE_BOOKS, 'admin')
            <button data-modal-target="delete-book-modal" data-modal-toggle="delete-book-modal" class="deleteBookBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800" type="button" value="{{ $item->id }}">
              Delete
            </button>
            @endcan
          </div>
        </td>
      </tr>
      @empty
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
        <td colspan="6" class="px-6 py-4 text-center">No books found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="m-4">
    {{ $books->links() }}
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
          <button data-modal-hide="delete-book-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">No, cancel</button>
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
          <button data-modal-hide="bulk-delete-book-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">No, cancel</button>
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
  const exportBarcodeButtons = document.querySelectorAll('.exportBarcode');
  const exportBarcodeIds = document.getElementById('export_barcode_ids');
  exportBarcodeButtons.forEach(button => {
    button.addEventListener('click', function() {
      exportBarcodeIds.value = Array.from(selectedIds).join(',');
    });
  });
</script>