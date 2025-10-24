<div class="w-full border-collapse border-2 border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-slate-800 dark:border-slate-700">
  <h2 class="text-center mb-2 mt-4 font-semibold text-2xl">Spreadsheet Contents</h2>
  @if($new)
  <div class="inline-flex items-center justify-center w-full">
    <hr class="w-full h-px m-4 bg-gray-500 border-0 dark:bg-gray-700">
    <span class="absolute px-3 font-medium text-gray-900 -translate-x-1/2 bg-white left-1/2 dark:text-green-400 dark:bg-gray-800">New Faculties & Staffs: {{ count($newData) }}</span>
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
        @forelse($newData as $index => $item)
        <tr>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $index }}][rfid]" value="{{ $item['rfid'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $index }}][first_name]" value="{{ $item['first_name'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $index }}][middle_name]" value="{{ $item['middle_name'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $index }}][last_name]" value="{{ $item['last_name'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $index }}][suffix]" value="{{ $item['suffix'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[80px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $index }}][gender]" value="{{ $item['gender'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[100px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $index }}][email]" value="{{ $item['email'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[250px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $index }}][employee_id]" value="{{ $item['employee_id'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="new_employees[{{ $index }}][employee_role]" value="{{ $item['employee_role'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[200px]"></td>
        </tr>
        @empty
        <tr>
          <td colspan="9" class="py-4 text-center">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @endif
  @if($existing)
  <div class="inline-flex items-center justify-center w-full">
    <hr class="w-full h-px m-4 bg-gray-500 border-0 dark:bg-gray-700">
    <span class="absolute px-3 font-medium text-gray-900 -translate-x-1/2 bg-white left-1/2 dark:text-yellow-400 dark:bg-gray-800">Existing Faculties & Staffs: {{ count($existingData) }}</span>
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
        @forelse($existingData as $index => $item)
        <tr>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $index }}][rfid]" value="{{ $item['rfid'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $index }}][first_name]" value="{{ $item['first_name'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $index }}][middle_name]" value="{{ $item['middle_name'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $index }}][last_name]" value="{{ $item['last_name'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $index }}][suffix]" value="{{ $item['suffix'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[80px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $index }}][gender]" value="{{ $item['gender'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[100px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $index }}][email]" value="{{ $item['email'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[250px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $index }}][employee_id]" value="{{ $item['employee_id'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="existing_employees[{{ $index }}][employee_role]" value="{{ $item['employee_role'] }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[200px]"></td>
        </tr>
        @empty
        <tr>
          <td colspan="9" class="py-4 text-center">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @endif
</div>