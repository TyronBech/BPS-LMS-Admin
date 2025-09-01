@use('App\Enum\PermissionsEnum')
<div class="mx-auto px-2 font-sans flex-col">
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right whitespace-nowrap table-auto">
      <thead class="text-xs py-2 text-gray-700 uppercase bg-gray-300 text-center dark:bg-gray-500 dark:text-white">
        <tr>
          <th scope="col" class="p-2 text-center">Type</th>
          <th scope="col" class="p-2 text-center">Description</th>
          <th scope="col" class="p-2 text-center">Rate</th>
          <th scope="col" class="p-2 text-center">Accrues Daily</th>
          <th scope="col" class="p-2 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rules as $item)
        <tr class="bg-white border-b text-center dark:bg-gray-800 dark:border-gray-600">
          <td class="min-w-40 h-14">{{ $item->type }}</td>
          <td class="min-w-40 h-14">{{ $item->description ?? 'None' }}</td>
          <td class="min-w-40 h-14">{{ $item->rate }}</td>
          @if($item->per_day == 0)
          <td class="min-w-40 h-14">No</td>
          @else
          <td class="min-w-40 h-14">Yes</td>
          @endif
          <td class="pb-1 flex justify-center">
            @if(auth()->user()->can(PermissionsEnum::EDIT_PENALTY_RULES))
            <button type="button" data-modal-target="edit-penalty-rule-modal" data-modal-toggle="edit-penalty-rule-modal" value="{{ $item->id }}" class="editBtn focus:outline-none text-white bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2">Edit</button>
            @endif
            @if(auth()->user()->can(PermissionsEnum::DELETE_PENALTY_RULES))
            <button type="button" data-modal-target="delete-penalty-rule-modal" data-modal-toggle="delete-penalty-rule-modal" value="{{ $item->id }}" class="deleteBtn focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2">Delete</button>
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
<div id="edit-penalty-rule-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <!-- Modal content -->
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          Edit Rule
        </h3>
        <button type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="edit-penalty-rule-modal">
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
            <input type="number" id="rate" name="rate" aria-describedby="helper-text-explanation" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="10" min="0" max="1000" required />
            @error('rate')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <div class="mb-5">
            <label for="per_day" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Accrues Daily: <span class="text-xs italic font-normal">(Amount of penalty increments daily?)</span></label>
            <div class="flex items-center mb-4">
              <input id="per_day-1" type="radio" value="1" name="per_day"  class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
              <label for="per_day-1" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Yes</label>
            </div>
            <div class="flex items-center">
              <input checked id="per_day-2" type="radio" value="0" name="per_day" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
              <label for="per_day-2" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">No</label>
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
          <button data-modal-hide="delete-penalty-rule-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  const editButtons           = document.querySelectorAll('.editBtn');
  const editInputID           = document.getElementById('edit_rule_id');
  const editInputType         = document.getElementById('type');
  const editInputDescription  = document.getElementById('description');
  const editInputRate         = document.getElementById('rate');
  const per_day_1             = document.getElementById('per_day-1');
  const per_day_2             = document.getElementById('per_day-2');
  editButtons.forEach(btn => {
    btn.addEventListener('click', function(event) {
      const ruleId                = event.target.value;
      editInputID.value           = ruleId;
      editInputType.value         = event.target.parentElement.parentElement.children[0].textContent;
      editInputDescription.value  = event.target.parentElement.parentElement.children[1].textContent;
      editInputRate.value         = event.target.parentElement.parentElement.children[2].textContent;
      if (event.target.parentElement.parentElement.children[3].textContent == 'Yes') {
        per_day_1.checked = true;
        per_day_2.checked = false;
      } else {
        per_day_1.checked = false;
        per_day_2.checked = true;
      }
    });
  });
  const deleteButtons = document.querySelectorAll('.deleteBtn');
  const deleteInputID = document.getElementById('delete_rule_id');
  deleteButtons.forEach(btn => {
    btn.addEventListener('click', function(event) {
      const ruleId        = event.target.value;
      deleteInputID.value = ruleId;
    });
  });
</script>