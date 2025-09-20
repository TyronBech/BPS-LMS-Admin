@use('App\Models\User')
@use('App\Models\Book')
@use('App\Models\Transaction')
@use('App\Models\StudentDetail')
@use('App\Models\EmployeeDetail')
@use('App\Models\VisitorDetail')
<div class="flex flex-col border-collapse border-2 border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Report Table for User Audits</h2>
  <form method="GET" class="m-2">
    <label for="perPage" class="mr-2 text-sm font-medium text-gray-700">Show</label>
    <input type="hidden" name="search" value="{{ request('types') }}">
    <input type="hidden" name="start" value="{{ request('start') }}">
    <input type="hidden" name="end" value="{{ request('end') }}">
    <select name="perPage" id="perPage" onchange="this.form.submit()" class="border border-gray-300 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2, dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
      <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
      <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
      <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
      <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
      <option value="250" {{ $perPage == 250 ? 'selected' : '' }}>250</option>
      <option value="500" {{ $perPage == 500 ? 'selected' : '' }}>500</option>
    </select>
    <span class="ml-2 text-sm text-gray-600">entries per page</span>
  </form>
  <div class="overflow-x-auto">
    <table class="table-auto w-full overflow-x-auto m-4 bg-white dark:bg-gray-800">
      <thead class="bg-blue-400 text-left font-bold text-slate-200 border-2 border-slate-300 dark:border-slate-700">
        <th class="pl-2 border-r w-[250px] border-slate-300 dark:border-slate-700">Record</th>
        <th class="pl-2 border-r w-[200px] border-slate-300 dark:border-slate-700">Source</th>
        <th class="pl-2 border-r w-[200px] border-slate-300 dark:border-slate-700">Field Changed</th>
        <th class="pl-2 border-r w-[300px] border-slate-300 dark:border-slate-700">Old Value</th>
        <th class="pl-2 border-r w-[300px] border-slate-300 dark:border-slate-700">New Value</th>
        <th class="pl-2 border-r w-[200px] border-slate-300 dark:border-slate-700">Change By</th>
        <th class="pl-2 border-r w-[120px] border-slate-300 dark:border-slate-700">Change Type</th>
        <th class="pl-2 border-r w-[150px] border-slate-300 dark:border-slate-700">Date</th>
        <th class="pl-2 border-r w-[150px] border-slate-300 dark:border-slate-700">Time</th>
      </thead>
      <tbody>
        @forelse($data as $item)
        <tr class="text-left border-2 border-slate-300 dark:border-slate-700">
          <!-- Record -->
          @if($item->source_table == User::getTableName() || $item->source_table == EmployeeDetail::getTableName() || $item->source_table == StudentDetail::getTableName() || VisitorDetail::getTableName())
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->user->last_name ?? 'user data' }}, {{ $item->user->first_name ?? '' }} {{ $item->user->middle_name ?? '' }}</td>
          @elseif($item->source_table == Book::getTableName())
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->book->title ?? 'book data' }}</td>
          @elseif($item->source_table == Transaction::getTableName())
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->transaction->book->title ?? 'transaction data' }}</td>
          @elseif($item->source_table == VisitorDetail::getTableName())
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">Visitor Data</td>
          @else
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">Session Record</td>
          @endif
          @if($item->source_table == User::getTableName() || $item->source_table == EmployeeDetail::getTableName() || $item->source_table == StudentDetail::getTableName() || VisitorDetail::getTableName())
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">User</td>
          @elseif($item->source_table == Book::getTableName())
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">Books</td>
          @elseif($item->source_table == Transaction::getTableName())
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">Transaction</td>
          @elseif($item->source_table == 'sessions')
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">Authentication</td>
          @endif
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ str_replace('_', ' ', $item->field_changed) ?? $item->field_changed ?? 'unknown field' }}</td>
          @if($item->field_changed == 'password')
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700 italic">hidden</td>
          @else
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700 overflow-clip">{{ $item->old_value ?? 'null' }}</td>
          @endif
          @if($item->field_changed == 'password')
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700 italic">hidden</td>
          @else
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700 overflow-clip">{{ $item->new_value ?? 'null' }}</td>
          @endif
          <!-- Changed By -->
          @if($item->changedBy)
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->changedBy->last_name }}, {{ $item->changedBy->first_name }} {{ $item->changedBy->middle_name ?? '' }}</td>
          @else
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">System</td>
          @endif
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->action_type ?? 'null' }}</td>
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') ?? 'null' }}</td>
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ \Carbon\Carbon::parse($item->created_at)->format('g:i A') ?? 'null' }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="9" class="text-center">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="m-4">
    {{ $data->links() }}
  </div>
</div>