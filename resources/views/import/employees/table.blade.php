<div class="container flex flex-col border-collapse overflow-x-auto border-2 border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-slate-800 dark:border-slate-700">
  <h2 class="text-center mb-2 mt-4 font-semibold text-2xl">Spreadsheet Contents</h2>
  @if($new)
  <div class="inline-flex items-center justify-center w-full">
    <hr class="w-full h-px m-4 bg-gray-500 border-0 dark:bg-gray-700">
    <span class="absolute px-3 font-medium text-gray-900 -translate-x-1/2 bg-white left-1/2 dark:text-green-400 dark:bg-gray-800">New Faculties & Staffs: {{ count($newData) }}</span>
  </div>
  <table class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead class="bg-blue-400 font-bold text-slate-200">
      <tr>
        <th>RFID</th>
        <th>First Name</th>
        <th>Middle Name</th>
        <th>Last Name</th>
        <th>Suffix</th>
        <th>Gender</th>
        <th>Email</th>
        <th>Employee ID</th>
        <th>Position</th>
      </tr>
    </thead>
    <tbody class="text-center">
      @forelse($newData as $item)
        <tr>
          <td>{{ $item['rfid'] }}</td>
          <td>{{ $item['first_name'] }}</td>
          <td>{{ $item['middle_name'] ?? '-' }}</td>
          <td>{{ $item['last_name'] }}</td>
          <td>{{ $item['suffix'] ?? '-' }}</td>
          <td>{{ $item['gender'] }}</td>
          <td>{{ $item['email'] }}</td>
          <td>{{ $item['employee_id'] }}</td>
          <td>{{ $item['employee_role'] }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="10">No data found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
  @endif
  @if($existing)
  <div class="inline-flex items-center justify-center w-full">
    <hr class="w-full h-px m-4 bg-gray-500 border-0 dark:bg-gray-700">
    <span class="absolute px-3 font-medium text-gray-900 -translate-x-1/2 bg-white left-1/2 dark:text-yellow-400 dark:bg-gray-800">Existing Faculties & Staffs: {{ count($existingData) }}</span>
  </div>
  <table class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead class="bg-blue-400 font-bold text-slate-200">
      <tr>
        <th>RFID</th>
        <th>First Name</th>
        <th>Middle Name</th>
        <th>Last Name</th>
        <th>Suffix</th>
        <th>Gender</th>
        <th>Email</th>
        <th>Employee ID</th>
        <th>Position</th>
      </tr>
    </thead>
    <tbody class="text-center">
      @forelse($existingData as $item)
        <tr>
          <td>{{ $item['rfid'] }}</td>
          <td>{{ $item['first_name'] }}</td>
          <td>{{ $item['middle_name'] ?? '-' }}</td>
          <td>{{ $item['last_name'] }}</td>
          <td>{{ $item['suffix'] ?? '-' }}</td>
          <td>{{ $item['gender'] }}</td>
          <td>{{ $item['email'] }}</td>
          <td>{{ $item['employee_id'] }}</td>
          <td>{{ $item['employee_role'] }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="10">No data found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
  @endif
</div>