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
        <th scope="col" class="px-6 py-3">User</th>
        <th scope="col" class="px-6 py-3 hidden sm:table-cell">Book Title</th>
        <th scope="col" class="px-6 py-3 hidden md:table-cell">Borrowed Date</th>
        <th scope="col" class="px-6 py-3 hidden lg:table-cell">Due Date</th>
        <th scope="col" class="px-6 py-3 hidden xl:table-cell">Returned Date</th>
        <th scope="col" class="px-6 py-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($transactions as $item)
      @if($item->book && $item->user)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
          <div class="text-base font-semibold">{{ $item->user->last_name }}, {{ $item->user->first_name }}</div>
          <div class="font-normal text-gray-500">{{ $item->user->email }}</div>
          <div class="font-normal text-gray-500 sm:hidden mt-1">Book: {{ $item->book->title }}</div>
        </th>
        <td class="px-6 py-4 hidden sm:table-cell">{{ $item->book->title }}</td>
        <td class="px-6 py-4 hidden md:table-cell">{{ \Carbon\Carbon::parse($item->date_borrowed)->format('Y-m-d') }}</td>
        <td class="px-6 py-4 hidden lg:table-cell">{{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('Y-m-d') : '-' }}</td>
        <td class="px-6 py-4 hidden xl:table-cell">{{ $item->return_date ? \Carbon\Carbon::parse($item->return_date)->format('Y-m-d') : 'Not Returned' }}</td>
        <td class="px-6 py-4">
          <div class="flex items-center space-x-2">
            <form action="{{ route('maintenance.show-circulations') }}" method="GET" class="inline-block">
              @csrf
              <button type="submit" name="viewBtn" value="{{ $item->id }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-yellow-500 rounded-lg hover:bg-yellow-600 focus:ring-4 focus:outline-none focus:ring-yellow-300 dark:bg-yellow-400 dark:hover:bg-yellow-500 dark:focus:ring-yellow-800">View</button>
            </form>
            @can(PermissionsEnum::EDIT_TRANSACTIONS)
            <button type="button" data-modal-target="edit-transaction-modal" data-modal-toggle="edit-transaction-modal" value="{{ $item->id }}" class="editBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">Edit</button>
            @endcan
          </div>
        </td>
      </tr>
      @endif
      @empty
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
        <td colspan="6" class="px-6 py-4 text-center">No transactions found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="p-4">
    {{ $transactions->withQueryString()->links() }}
  </div>
</div>
<!-- Edit modal -->
<div id="edit-transaction-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-2xl max-h-full">
    <!-- Modal content -->
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          Edit Circulation
        </h3>
        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="edit-transaction-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <!-- Modal body -->
      <form class="p-4 md:p-5" action="{{ route('maintenance.update-circulation') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="grid gap-6 mb-6 md:grid-cols-2">
          <div>
            <label for="due-datepicker" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Due Date:</label>
            <div class="relative">
              <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                </svg>
              </div>
              <input datepicker id="due-datepicker" type="text" name="due_date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Select date" required>
            </div>
            @error('due_date')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label for="pickup-datepicker" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Pickup Date:</label>
            <div class="relative">
              <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                </svg>
              </div>
              <input datepicker id="pickup-datepicker" type="text" name="pickup_date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Select date">
            </div>
            @error('pickup_date')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label for="transaction_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Circulation Type:</label>
            <select id="transaction_type" name="transaction_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
              <option selected disabled>Choose a type</option>
              @foreach($transactionTypes as $item)
              <option value="{{ $item }}">{{ $item }}</option>
              @endforeach
            </select>
            @error('transaction_type')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label for="status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status:</label>
            <select id="status" name="status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
              <option selected disabled>Choose a status</option>
              @foreach($transactionStatuses as $item)
              <option value="{{ $item }}">{{ $item }}</option>
              @endforeach
            </select>
            @error('status')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label for="penalty_status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Penalty Status:</label>
            <select id="penalty_status" name="penalty_status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
              <option selected disabled>Choose a status</option>
              @foreach($penaltyStatuses as $item)
              <option value="{{ $item }}">{{ $item }}</option>
              @endforeach
            </select>
            @error('penalty_status')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label for="penalty_total" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Penalty Total:</label>
            <input type="number" id="penalty_total" name="penalty_total" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Amount" min="0" max="1000" required />
            @error('penalty_total')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
          </div>
          <div class="md:col-span-2">
            <label for="book_condition" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Book Condition:</label>
            <select id="book_condition" name="book_condition" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
              <option selected disabled>Choose a condition</option>
              @foreach($conditions as $item)
              <option value="{{ $item }}">{{ $item }}</option>
              @endforeach
            </select>
            @error('book_condition')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
          </div>
          <div class="md:col-span-2">
            <label for="remarks" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Remarks:</label>
            <textarea id="remarks" name="remarks" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Remarks..."></textarea>
            @error('remarks')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
          </div>
        </div>
        <input type="hidden" name="edit_transaction_id" id="edit_transaction_id" value="" />
        <div class="flex justify-end">
          <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script type="module">
  document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.editBtn');
    const transactionTypeSelect = document.getElementById('transaction_type');
    const statusSelect = document.getElementById('status');
    
    // Define status options based on transaction type
    const statusOptions = {
      'Borrowed': ['Borrowed', 'Overdue', 'Renew'],
      'Returned': ['Completed', 'Lost', 'Missing'],
      'Reserved': ['Pending', 'Cancelled', 'Available for pickup']
    };

    // Function to update status dropdown based on transaction type
    function updateStatusOptions(transactionType, currentStatus = '') {
      // Clear existing options
      statusSelect.innerHTML = '<option selected disabled>Choose a status</option>';
      
      // Get available statuses for the selected transaction type
      const availableStatuses = statusOptions[transactionType] || [];
      
      // Populate status dropdown
      availableStatuses.forEach(status => {
        const option = document.createElement('option');
        option.value = status;
        option.textContent = status;
        if (status === currentStatus) {
          option.selected = true;
        }
        statusSelect.appendChild(option);
      });
    }

    // Add event listener to transaction type select
    transactionTypeSelect.addEventListener('change', function() {
      updateStatusOptions(this.value);
    });

    editButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        $.ajax({
          url: "{{ route('maintenance.retrieve-circulation') }}",
          type: "GET",
          data: {
            viewBtn: this.value
          },
          dataType: "json",
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          success: function(response) {
            const transaction = response.transaction;
            document.getElementById('edit_transaction_id').value = transaction.id;
            document.getElementById('due-datepicker').value = transaction.due_date ? new Date(transaction.due_date).toLocaleDateString('en-CA') : '';
            document.getElementById('pickup-datepicker').value = transaction.pickup_deadline ? new Date(transaction.pickup_deadline).toLocaleDateString('en-CA') : '';
            document.getElementById('transaction_type').value = transaction.transaction_type;
            document.getElementById('penalty_status').value = transaction.penalty_status || 'No Penalty';
            
            // Update status options based on transaction type, then set the current status
            updateStatusOptions(transaction.transaction_type, transaction.status);
            
            document.getElementById('book_condition').value = transaction.book_condition || '';
            document.getElementById('penalty_total').value = transaction.penalty_total || '';
            document.getElementById('remarks').value = transaction.remarks || '';
          },
          error: function(xhr, status, error) {
            console.error("Error fetching transaction data:", error);
          }
        });
      });
    });
  });
</script>