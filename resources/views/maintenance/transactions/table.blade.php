@use('App\Enum\PermissionsEnum')
<div class="mx-auto px-2 font-sans flex-col">
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right whitespace-nowrap table-auto">
      <thead class="text-xs py-2 text-gray-700 uppercase bg-gray-300 text-center dark:bg-gray-500 dark:text-white">
        <tr>
          <th scope="col" class="p-2 text-center">User</th>
          <th scope="col" class="p-2 text-center">Book</th>
          <th scope="col" class="p-2 text-center">Borrowed Date</th>
          <th scope="col" class="p-2 text-center">Due Date</th>
          <th scope="col" class="p-2 text-center">Returned Date</th>
          <th scope="col" class="p-2 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($transactions as $item)
        <tr class="bg-white border-b text-center dark:bg-gray-800 dark:border-gray-600">
          <td class="max-w-40 h-14">{{ $item->user->first_name }} {{ $item->user->last_name }}</td>
          <td class="max-w-72 overflow-hidden text-ellipsis">{{ $item->book->title }}</td>
          <td class="max-w-60">{{ $item->date_borrowed }}</td>
          <td class="max-w-36">{{ $item->due_date ?? '-' }}</td>
          <td class="max-w-36">{{ $item->returned_date ?? 'Not Returned' }}</td>
          <td class="pb-1 flex justify-center">
            <form action="{{ route('maintenance.show-transactions') }}" method="GET" class="inline-block">
              @csrf
              <button type="submit" id="viewBtn" name="viewBtn" value="{{ $item->id }}" class="focus:outline-none text-white bg-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2 dark:focus:ring-yellow-900">View</button>
            </form>
            @if(auth()->user()->can(PermissionsEnum::EDIT_TRANSACTIONS))
            <button type="button" data-modal-target="edit-transaction-modal" data-modal-toggle="edit-transaction-modal" value="{{ $item->id }}" class="editBtn focus:outline-none text-white bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2">Edit</button>
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
<div id="edit-transaction-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <!-- Modal content -->
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          Edit Transaction
        </h3>
        <button type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="edit-transaction-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <!-- Modal body -->
      <div class="p-4 md:p-5">
        <form class="space-y-4" action="{{ route('maintenance.update-penalty-rule') }}" method="POST">
          @csrf
          @method('PUT')
          <div class="mb-5">
            <label for="type" class="block mb-2 text-sm font-medium">Type:</label>
            <input type="text" id="type" name="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Late" required>
            @error('type')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <div class="mb-5">
            <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description:</label>
            <textarea id="description" name="description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Description"></textarea>
            @error('description')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <div class="mb-5">
            <label for="rate" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Rate:</label>
            <input type="number" id="rate" name="rate" aria-describedby="helper-text-explanation" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="10" required />
            @error('rate')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <div class="mb-5">
            <label for="per_day" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Per Day:</label>
            <div class="relative flex items-center max-w-[8rem]">
              <button type="button" id="decrement-button" data-input-counter-decrement="per_day" class="bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-600 hover:bg-gray-200 border border-gray-300 rounded-s-lg p-3 h-11 focus:ring-gray-100 dark:focus:ring-gray-700 focus:ring-2 focus:outline-none">
                <svg class="w-3 h-3 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16" />
                </svg>
              </button>
              <input type="text" id="per_day" name="per_day" data-input-counter data-input-counter-min="0" data-input-counter-max="99" aria-describedby="helper-text-explanation" class="bg-gray-50 border-x-0 border-gray-300 h-11 text-center text-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500 block w-full py-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="10" required />
              <button type="button" id="increment-button" data-input-counter-increment="per_day" class="bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-600 hover:bg-gray-200 border border-gray-300 rounded-e-lg p-3 h-11 focus:ring-gray-100 dark:focus:ring-gray-700 focus:ring-2 focus:outline-none">
                <svg class="w-3 h-3 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16" />
                </svg>
              </button>
            </div>
            @error('per_day')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <input type="hidden" name="edit_rule_id" id="edit_rule_id" value="" />
          <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>