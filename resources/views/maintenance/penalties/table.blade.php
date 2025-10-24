@use('App\Enum\PermissionsEnum')
<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
      <tr>
        <th scope="col" class="px-6 py-3">Type</th>
        <th scope="col" class="px-6 py-3 hidden sm:table-cell">Description</th>
        <th scope="col" class="px-6 py-3 hidden md:table-cell">Rate</th>
        <th scope="col" class="px-6 py-3 hidden md:table-cell">Accrues Daily</th>
        <th scope="col" class="px-6 py-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rules as $item)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
          <div class="text-base font-semibold">{{ $item->type }}</div>
          <div class="font-normal text-gray-500 sm:hidden">{{ $item->description ?? 'None' }}</div>
        </th>
        <td class="px-6 py-4 hidden sm:table-cell">{{ $item->description ?? 'None' }}</td>
        <td class="px-6 py-4 hidden md:table-cell">{{ $item->rate }}</td>
        <td class="px-6 py-4 hidden md:table-cell">{{ $item->per_day ? 'Yes' : 'No' }}</td>
        <td class="px-6 py-4">
          <div class="flex items-center space-x-2">
            @can(PermissionsEnum::EDIT_PENALTY_RULES)
            <button type="button" data-modal-target="edit-penalty-rule-modal" data-modal-toggle="edit-penalty-rule-modal" data-rule='@json($item)' class="editBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">Edit</button>
            @endcan
            @can(PermissionsEnum::DELETE_PENALTY_RULES)
            <button type="button" data-modal-target="delete-penalty-rule-modal" data-modal-toggle="delete-penalty-rule-modal" value="{{ $item->id }}" class="deleteBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800">Delete</button>
            @endcan
          </div>
        </td>
      </tr>
      @empty
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
        <td colspan="5" class="px-6 py-4 text-center">No penalty rules found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
<!-- Edit modal -->
<div id="edit-penalty-rule-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-2xl max-h-full">
    <!-- Modal content -->
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          Edit Rule
        </h3>
        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="edit-penalty-rule-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <!-- Modal body -->
      <form action="{{ route('maintenance.update-penalty-rule') }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="edit_rule_id" id="edit_rule_id" value="" />
        <div class="p-4 md:p-5 space-y-4">
          <div>
            <label for="edit_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Type:</label>
            <input type="text" id="edit_type" name="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g., Late Return" required>
          </div>
          <div>
            <label for="edit_description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description:</label>
            <textarea id="edit_description" name="description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Description"></textarea>
          </div>
          <div>
            <label for="edit_rate" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Rate:</label>
            <input type="number" id="edit_rate" name="rate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="10" min="0" max="1000" required />
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Accrues Daily:</label>
            <div class="flex items-center mb-2">
              <input id="edit_per_day-1" type="radio" value="1" name="per_day" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
              <label for="edit_per_day-1" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Yes</label>
            </div>
            <div class="flex items-center">
              <input id="edit_per_day-2" type="radio" value="0" name="per_day" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
              <label for="edit_per_day-2" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">No</label>
            </div>
          </div>
        </div>
        <!-- Modal footer -->
        <div class="flex items-center justify-end p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
          <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Update</button>
          <button data-modal-hide="edit-penalty-rule-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Delete modal -->
<div id="delete-penalty-rule-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-penalty-rule-modal">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this penalty rule?</h3>
        <form action="{{ route('maintenance.delete-penalty-rule') }}" method="POST" id="delete-rule-form">
          @csrf
          @method('DELETE')
          <input type="hidden" name="delete_rule_id" id="delete_rule_id" value="" />
          <button data-modal-hide="delete-penalty-rule-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="delete-penalty-rule-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.editBtn');
    const editModal = {
      id: document.getElementById('edit_rule_id'),
      type: document.getElementById('edit_type'),
      description: document.getElementById('edit_description'),
      rate: document.getElementById('edit_rate'),
      perDayYes: document.getElementById('edit_per_day-1'),
      perDayNo: document.getElementById('edit_per_day-2')
    };

    editButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        const rule = JSON.parse(this.dataset.rule);
        editModal.id.value = rule.id;
        editModal.type.value = rule.type;
        editModal.description.value = rule.description;
        editModal.rate.value = rule.rate;
        if (rule.per_day) {
          editModal.perDayYes.checked = true;
        } else {
          editModal.perDayNo.checked = true;
        }
      });
    });

    const deleteButtons = document.querySelectorAll('.deleteBtn');
    const deleteInputID = document.getElementById('delete_rule_id');
    deleteButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        deleteInputID.value = this.value;
      });
    });
  });
</script>