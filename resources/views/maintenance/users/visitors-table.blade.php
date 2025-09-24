@use('App\Enum\PermissionsEnum')
@use('App\Enum\RolesEnum')
@php
$visitorID = null;
$increment = 0;
@endphp
<div class="container mx-auto px-2 font-sans flex-col">
  <div class="flex space-x-4 justify-between items-center mb-4 w-full">
    <div class="justify-start flex items-center">
      <div id="checked-visitors" class="hidden flex-row">
        <h5 id="selectedVisitorHeader" class="text-sm font-bold tracking-tight border-2 rounded-lg px-5 py-2 me-2">Selected</h5>
        @can(PermissionsEnum::DELETE_USERS, 'admin')
        <button data-modal-target="bulk-delete-visitor-modal" data-modal-toggle="bulk-delete-visitor-modal" class="bulkDeleteVisitorBtn focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 me-2" type="button" value="">
          Delete
        </button>
        @endcan
      </div>
    </div>
    <form method="GET" class="justify-end m-2">
      <input type="hidden" name="search" value="{{ request('search', '') }}">
      <label for="perPage" class="mr-2 text-sm font-medium text-gray-700">Show</label>
      <select name="perVisitorPage" id="perPage" onchange="this.form.submit()" class="border border-gray-300 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2, dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
        <option value="10" {{ $perVisitorPage == 10 ? 'selected' : '' }}>10</option>
        <option value="25" {{ $perVisitorPage == 25 ? 'selected' : '' }}>25</option>
        <option value="50" {{ $perVisitorPage == 50 ? 'selected' : '' }}>50</option>
      </select>
      <span class="ml-2 text-sm text-gray-600">entries per page</span>
    </form>
  </div>
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right whitespace-nowrap table-auto">
      <thead class="text-xs py-2 text-gray-700 uppercase bg-gray-300 text-center dark:bg-gray-500 dark:text-white">
        <tr>
          <th scope="col" class="p-2 text-center max-w-3">
            <div class="flex items-center ml-4">
              <input id="selectAllVisitors" type="checkbox" value="" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
              <label for="selectAllVisitors" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300"></label>
            </div>
          </th>
          <th scope="col" class="p-2 text-center min-w-44">Name</th>
          <th scope="col" class="p-2 text-center min-w-24">Gender</th>
          <th scope="col" class="p-2 text-center min-w-24">Email</th>
          <th scope="col" class="p-2 text-center min-w-11">Organization</th>
          <th scope="col" class="p-2 text-center min-w-11">Purpose</th>
          <th scope="col" class="p-2 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($visitors as $item)
        <tr class="bg-white border-b text-center dark:bg-gray-800 dark:border-gray-600">
          @php
          $increment++;
          @endphp
          <td class="py-4 pl-2">
            <div class="flex items-center ml-4">
              <input id="visitorCheck" type="checkbox" value="{{ $item->id }}" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
              <label for="visitorCheck" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300"></label>
            </div>
          </td>
          <td class="py-4">{{ $item->first_name }} {{ $item->middle_name ?? '' }} {{ $item->last_name }} {{ $item->suffix ?? '' }}</td>
          <td class="py-4">{{ $item->gender }}</td>
          <td class="py-4">{{ $item->email }}</td>
          <td class="py-4">{{ $item->visitors->school_org }}</td>
          <td class="py-4">{{ $item->visitors->purpose }}</td>
          <td class="py-4 flex justify-center">            
            @can(PermissionsEnum::EDIT_USERS, 'admin')
            <a href="{{ route('maintenance.edit-visitor', $item->id) }}" id="editBtn" name="editBtn" class="text-white bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 me-2">Edit</a>
            @endcan
            @can(PermissionsEnum::DELETE_USERS, 'admin')
            <button class="deleteVisitorBtn focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 me-2" type="button" data-modal-target="delete-visitor-modal" data-modal-toggle="delete-visitor-modal" value="{{ $item->id }}">Delete</button>
            @endcan
          </td>
        </tr>
        @endforeach
        @if($increment == 0)
        <tr>
          <td colspan="10" class="text-center py-1.5">No data found.</td>
        </tr>
        @endif
      </tbody>
    </table>
    <div class="m-4">
      {{ $visitors->withQueryString()->fragment('visitorHeader')->links() }}
    </div>
  </div>
</div>
<div id="delete-visitor-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-visitor-modal">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this visitor?</h3>
        <form action="{{ route('maintenance.delete-user') }}" method="POST">
          @csrf
          @method('DELETE')
          <input type="hidden" name="id" id="delete_visitor_id" value="" />
          <button data-modal-hide="delete-visitor-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="delete-visitor-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<div id="bulk-delete-visitor-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="bulk-delete-visitor-modal">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete these visitors?</h3>
        <form action="{{ route('maintenance.bulk-delete-visitor') }}" method="POST" class="flex items-center justify-center">
          @csrf
          @method('DELETE')
          <input type="hidden" name="visitor_ids" id="bulk-delete_visitor_ids" value="" />
          <button id="bulkDeleteVisitorBtn" data-modal-hide="bulk-delete-visitor-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="bulk-delete-visitor-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  const deleteVisitorBtn = document.querySelectorAll('.deleteVisitorBtn');
  const deleteVisitorID = document.getElementById('delete_visitor_id');
  deleteVisitorBtn.forEach(btn => {
    btn.addEventListener('click', function(event) {
      const visitorId = event.target.value;
      deleteVisitorID.value = visitorId;
    });
  });
  let checkedVisitors = 0;
  const visitorCheck = document.querySelectorAll('#visitorCheck');
  const checkedVisitorsContainer = document.getElementById('checked-visitors');
  const bulkDeleteVisitorIds = document.getElementById('bulk-delete_visitor_ids');
  const bulkDeleteVisitorBtn = document.getElementById('bulkDeleteVisitorBtn');
  const selectedVisitorHeader = document.getElementById('selectedVisitorHeader');
  const selectAllVisitorsCheckbox = document.getElementById('selectAllVisitors');
  bulkDeleteVisitorIds.value = '';
  bulkDeleteVisitorBtn.value = '';
  const selectedVisitorIds = new Set();
  visitorCheck.forEach(checkbox => {
    checkbox.addEventListener('change', () => {
      const visitorId = event.target.value;
      if (checkbox.checked) {
        selectedVisitorIds.add(visitorId);
        checkedVisitors++;
      } else {
        selectedVisitorIds.delete(visitorId);
        checkedVisitors--;
        selectAllVisitorsCheckbox.checked = false;
      }
      bulkDeleteVisitorIds.value = Array.from(selectedVisitorIds).join(',');
      console.log(bulkDeleteVisitorIds.value);
      bulkDeleteVisitorBtn.value = Array.from(selectedVisitorIds).join(',');
      console.log(bulkDeleteVisitorBtn.value);
      if (checkedVisitors > 0) {
        checkedVisitorsContainer.classList.replace('hidden', 'flex');
        selectedVisitorHeader.textContent = `Selected (${checkedVisitors})`;
      } else {
        checkedVisitorsContainer.classList.replace('flex', 'hidden');
      }
    });
  });
  selectAllVisitorsCheckbox.addEventListener('change', () => {
    checkedVisitors = 0;
    visitorCheck.forEach(checkbox => {
      checkbox.checked = event.target.checked;
      const visitorId = checkbox.value;
      if (selectAllVisitorsCheckbox.checked) {
        selectedVisitorIds.add(visitorId);
        checkedVisitors++;
      } else {
        selectedVisitorIds.delete(visitorId);
        checkedVisitors = 0;
      }
      bulkDeleteVisitorIds.value = Array.from(selectedVisitorIds).join(',');
      bulkDeleteVisitorBtn.value = Array.from(selectedVisitorIds).join(',');
      if (checkedVisitors > 0) {
        checkedVisitorsContainer.classList.replace('hidden', 'flex');
        selectedVisitorHeader.textContent = `Selected (${checkedVisitors})`;
      } else {
        checkedVisitorsContainer.classList.replace('flex', 'hidden');
      }
    });
  });
</script>