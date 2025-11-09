<div class="w-full border-collapse border-2 border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-slate-800 dark:border-slate-700">
  <h2 class="text-center mb-2 mt-4 font-semibold text-2xl">Spreadsheet Contents</h2>
  <div class="p-4">
    <div class="flex items-center">
      <label for="perPage" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
      <select name="perPage" id="perPage" onchange="submitImportForm()" class="border border-gray-300 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
        <option value="10" @if(isset($perPage) && $perPage == 10) selected @endif>10</option>
        <option value="25" @if(isset($perPage) && $perPage == 25) selected @endif>25</option>
        <option value="50" @if(isset($perPage) && $perPage == 50) selected @endif>50</option>
      </select>
      <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">entries per page</span>
    </div>
  </div>
  @if($new)
  <div class="inline-flex items-center justify-center w-full">
    <hr class="w-full h-px m-4 bg-gray-500 border-0 dark:bg-gray-700">
    <span class="absolute px-3 font-medium text-gray-900 -translate-x-1/2 bg-white left-1/2 dark:text-green-400 dark:bg-gray-800">New Faculties & Staffs: {{ $newPaginatedData->total() }}</span>
  </div>
  <div class="overflow-x-auto p-4">
    <table class="min-w-full table-auto bg-white dark:bg-gray-800">
      <thead class="bg-blue-400 font-bold text-slate-200">
        <tr>
          <th class="px-6 py-3 text-left">RFID</th>
          <th class="px-6 py-3 text-left">First Name</th>
          <th class="px-6 py-3 text-left">Middle Name</th>
          <th class="px-6 py-3 text-left">Last Name</th>
          <th class="px-6 py-3 text-left">Suffix</th>
          <th class="px-6 py-3 text-left">Gender</th>
          <th class="px-6 py-3 text-left">Email</th>
          <th class="px-6 py-3 text-left">Employee ID</th>
          <th class="px-6 py-3 text-left">Position</th>
        </tr>
      </thead>
      <tbody class="text-left">
        @php $startIndex = ($newPaginatedData->currentPage() - 1) * $newPaginatedData->perPage(); @endphp
        @forelse($newPaginatedData as $index => $item)
        @php $itemIndex = $startIndex + $index; @endphp
        <tr>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $itemIndex }}][rfid]" value="{{ $item['rfid'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $itemIndex }}][first_name]" value="{{ $item['first_name'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $itemIndex }}][middle_name]" value="{{ $item['middle_name'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $itemIndex }}][last_name]" value="{{ $item['last_name'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $itemIndex }}][suffix]" value="{{ $item['suffix'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[80px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $itemIndex }}][gender]" value="{{ $item['gender'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[100px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $itemIndex }}][email]" value="{{ $item['email'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[250px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $itemIndex }}][employee_id]" value="{{ $item['employee_id'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $itemIndex }}][employee_role]" value="{{ $item['employee_role'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[200px]"></td>
        </tr>
        @empty
        <tr>
          <td colspan="9" class="py-4 text-center">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
    <div class="mt-4 pagination-links">
        {{ $newPaginatedData->appends(request()->except('new'))->links() }}
    </div>
  </div>
  @endif
  @if($existing)
  <div class="inline-flex items-center justify-center w-full">
    <hr class="w-full h-px m-4 bg-gray-500 border-0 dark:bg-gray-700">
    <span class="absolute px-3 font-medium text-gray-900 -translate-x-1/2 bg-white left-1/2 dark:text-yellow-400 dark:bg-gray-800">Existing Faculties & Staffs: {{ $existingPaginatedData->total() }}</span>
  </div>
  <div class="overflow-x-auto p-4">
    <table class="min-w-full table-auto bg-white dark:bg-gray-800">
      <thead class="bg-blue-400 font-bold text-slate-200">
        <tr>
          <th class="px-6 py-3 text-left">RFID</th>
          <th class="px-6 py-3 text-left">First Name</th>
          <th class="px-6 py-3 text-left">Middle Name</th>
          <th class="px-6 py-3 text-left">Last Name</th>
          <th class="px-6 py-3 text-left">Suffix</th>
          <th class="px-6 py-3 text-left">Gender</th>
          <th class="px-6 py-3 text-left">Email</th>
          <th class="px-6 py-3 text-left">Employee ID</th>
          <th class="px-6 py-3 text-left">Position</th>
        </tr>
      </thead>
      <tbody class="text-left">
        @php $startIndex = ($existingPaginatedData->currentPage() - 1) * $existingPaginatedData->perPage(); @endphp
        @forelse($existingPaginatedData as $index => $item)
        @php $itemIndex = $startIndex + $index; @endphp
        <tr>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $itemIndex }}][rfid]" value="{{ $item['rfid'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $itemIndex }}][first_name]" value="{{ $item['first_name'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $itemIndex }}][middle_name]" value="{{ $item['middle_name'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $itemIndex }}][last_name]" value="{{ $item['last_name'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $itemIndex }}][suffix]" value="{{ $item['suffix'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[80px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $itemIndex }}][gender]" value="{{ $item['gender'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[100px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $itemIndex }}][email]" value="{{ $item['email'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[250px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $itemIndex }}][employee_id]" value="{{ $item['employee_id'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $itemIndex }}][employee_role]" value="{{ $item['employee_role'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[200px]"></td>
        </tr>
        @empty
        <tr>
          <td colspan="9" class="py-4 text-center">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
    <div class="mt-4 pagination-links">
        {{ $existingPaginatedData->appends(request()->except('existing'))->links() }}
    </div>
  </div>
  @endif
</div>

<script>
  function submitImportForm(url) {
    const form = document.getElementById('import-form');
    if (url) {
      form.action = url;
    }
    form.submit();
  }

  document.addEventListener('DOMContentLoaded', function() {
    const paginationContainers = document.querySelectorAll('.pagination-links');
    paginationContainers.forEach(container => {
        container.addEventListener('click', function(e) {
            const target = e.target.closest('a');
            if (target) {
              e.preventDefault();
              submitImportForm(target.href);
            }
        });
    });
  });
</script>