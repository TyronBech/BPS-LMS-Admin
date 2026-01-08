@use('App\Models\User')
@use('App\Models\Book')
@use('App\Models\Transaction')
@use('App\Models\StudentDetail')
@use('App\Models\EmployeeDetail')
@use('App\Models\VisitorDetail')
<div id="tabular" class="container mx-auto mt-2 mb-4">
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="p-4">
      <h2 class="text-center mb-4 font-semibold text-2xl dark:text-white">Report Table for User Audits</h2>
      <form method="GET" class="flex items-center">
        <label for="perPage" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
        <input type="hidden" name="types" value="{{ $types }}">
        <input type="hidden" name="tableType" value="{{ request('tableType') }}">
        <input type="hidden" name="start" value="{{ request('start') }}">
        <input type="hidden" name="end" value="{{ request('end') }}">
        <select name="perPage" id="perPage" onchange="this.form.submit()" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-500 focus:border-primary-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
          <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
          <option value="250" {{ $perPage == 250 ? 'selected' : '' }}>250</option>
          <option value="500" {{ $perPage == 500 ? 'selected' : '' }}>500</option>
        </select>
        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">entries per page</span>
      </form>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th scope="col" class="px-6 py-3 whitespace-nowrap">Record</th>
            <th scope="col" class="px-6 py-3 whitespace-nowrap">Source</th>
            <th scope="col" class="px-6 py-3 whitespace-nowrap">Field Changed</th>
            <th scope="col" class="px-6 py-3 whitespace-nowrap w-48">Old Value</th>
            <th scope="col" class="px-6 py-3 whitespace-nowrap w-48">New Value</th>
            <th scope="col" class="px-6 py-3 whitespace-nowrap">Change By</th>
            <th scope="col" class="px-6 py-3 whitespace-nowrap">Change Type</th>
            <th scope="col" class="px-6 py-3 whitespace-nowrap">Date</th>
            <th scope="col" class="px-6 py-3 whitespace-nowrap">Time</th>
          </tr>
        </thead>
        <tbody>
          @forelse($data as $item)
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            <!-- Record -->
            @if(in_array($item->source_table, [User::getTableName(), EmployeeDetail::getTableName(), StudentDetail::getTableName(), VisitorDetail::getTableName()]))
            @if($item->user)
            <td class="px-6 py-4 max-w-md">
              <div class="break-words">
                {{ $item->user->last_name ?? '' }}, {{ $item->user->first_name ?? '' }} {{ $item->user->middle_name ?? '' }}
              </div>
            </td>
            @elseif($item->visitor)
            <td class="px-6 py-4 max-w-md">
              <div class="break-words">
                {{ $item->visitor->last_name ?? '' }}, {{ $item->visitor->first_name ?? '' }} {{ $item->visitor->middle_name ?? '' }}
              </div>
            </td>
            @else
            <td class="px-6 py-4 max-w-md">
              <div class="break-words">user data</div>
            </td>
            @endif
            @elseif($item->source_table == Book::getTableName())
            <td class="px-6 py-4 max-w-md">
              <div class="break-words">
                {{ $item->book->title ?? 'book data' }}
              </div>
            </td>
            @elseif($item->source_table == Transaction::getTableName())
            <td class="px-6 py-4 max-w-md">
              <div class="break-words">
                {{ $item->transaction->book->title ?? 'transaction data' }}
              </div>
            </td>
            @elseif($item->source_table == VisitorDetail::getTableName())
            <td class="px-6 py-4 max-w-md">
              <div class="break-words">Visitor Data</div>
            </td>
            @else
            <td class="px-6 py-4 max-w-md">
              <div class="break-words">Session Record</div>
            </td>
            @endif
            @if($item->source_table == User::getTableName())
            <td class="px-6 py-4 whitespace-nowrap">User</td>
            @elseif($item->source_table == EmployeeDetail::getTableName())
            <td class="px-6 py-4 whitespace-nowrap">Employee</td>
            @elseif($item->source_table == StudentDetail::getTableName())
            <td class="px-6 py-4 whitespace-nowrap">Student</td>
            @elseif($item->source_table == VisitorDetail::getTableName())
            <td class="px-6 py-4 whitespace-nowrap">Visitor</td>
            @elseif($item->source_table == Book::getTableName())
            <td class="px-6 py-4 whitespace-nowrap">Book</td>
            @elseif($item->source_table == Transaction::getTableName())
            <td class="px-6 py-4 whitespace-nowrap">Transaction</td>
            @elseif($item->source_table == 'sessions')
            <td class="px-6 py-4 whitespace-nowrap">Authentication</td>
            @endif
            <td class="px-6 py-4 whitespace-nowrap">{{ str_replace('_', ' ', $item->field_changed) ?? $item->field_changed ?? 'unknown field' }}</td>
            @if($item->field_changed == 'password')
            <td class="px-6 py-4 whitespace-nowrap italic">hidden</td>
            @else
            <td class="px-6 py-4 max-w-xs">
              <div class="break-words">
                {{ $item->old_value ?? 'null' }}
              </div>
            </td>
            @endif
            @if($item->field_changed == 'password')
            <td class="px-6 py-4 whitespace-nowrap italic">hidden</td>
            @else
            <td class="px-6 py-4 max-w-xs">
              <div class="break-words">
                {{ $item->new_value ?? 'null' }}
              </div>
            </td>
            @endif
            <!-- Changed By -->
            @if($item->changedBy)
            <td class="px-6 py-4 whitespace-nowrap">{{ $item->changedBy->last_name }}, {{ $item->changedBy->first_name }} {{ $item->changedBy->middle_name ?? '' }}</td>
            @else
            <td class="px-6 py-4 whitespace-nowrap">System</td>
            @endif
            <td class="px-6 py-4 whitespace-nowrap">{{ $item->action_type ?? 'null' }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') ?? 'null' }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($item->created_at)->format('g:i A') ?? 'null' }}</td>
          </tr>
          @empty
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            <td colspan="9" class="px-6 py-4 text-center">No data found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-4">
      {{ $data->withQueryString()->links() }}
    </div>
  </div>
</div>