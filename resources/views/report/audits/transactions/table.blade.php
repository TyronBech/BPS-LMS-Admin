<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Report Table for Book Circulation Audits</h2>
  <form method="GET" class="m-2">
    <label for="perPage" class="mr-2 text-sm font-medium text-gray-700">Show</label>
    <input type="hidden" name="search" value="{{ request('type') }}">
    <input type="hidden" name="start" value="{{ request('start') }}">
    <input type="hidden" name="end" value="{{ request('end') }}">
    <select name="perPage" id="perPage" onchange="this.form.submit()" class="border border-gray-300 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2, dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
      <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
      <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
      <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
    </select>
    <span class="ml-2 text-sm text-gray-600">entries per page</span>
  </form>
  <table class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead class="bg-blue-400 text-left font-bold text-slate-200 border-2 border-slate-300 dark:border-slate-700">
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Name</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Book</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Field Changed</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Old Value</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">New Value</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Change Type</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Change By</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Date</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Time</th>
    </thead>
    <tbody>
      @forelse($data as $item)
      <tr class="text-left border-2 border-slate-300 dark:border-slate-700">
        @if($item->transaction)
        @if($item->transaction->user && $item->transaction->book)
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->transaction->user->last_name }}, {{ $item->transaction->user->first_name }} {{ $item->transaction->user->middle_name ?? '' }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->transaction->book->title ?? 'null' }}</td>
        @if($item->field_changed == 'user_id')
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">User</td>
        @elseif($item->field_changed == 'book_id')
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">Book</td>
        @else
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->field_changed ?? 'null' }}</td>
        @endif
        @if($item->field_changed == 'user_id' && $item->oldUser)
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->oldUser->last_name }}, {{ $item->oldUser->first_name }} {{ $item->oldUser->middle_name ?? '' }}</td>
        @elseif($item->field_changed == 'book_id' && $item->oldBook)
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->oldBook->title ?? 'null' }}</td>
        @else
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->old_value ?? 'null' }}</td>
        @endif
        @if($item->field_changed == 'user_id' && $item->newUser)
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->newUser->last_name }}, {{ $item->newUser->first_name }} {{ $item->newUser->middle_name ?? '' }}</td>
        @elseif($item->field_changed == 'book_id' && $item->newBook)
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->newBook->title ?? 'null' }}</td>
        @else
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->new_value ?? 'null' }}</td>
        @endif
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->change_type ?? 'null' }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->changed_by ?? 'null' }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ \Carbon\Carbon::parse($item->changed_date)->format('Y-m-d') ?? 'null' }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ \Carbon\Carbon::parse($item->changed_date)->format('g:i A') ?? 'null' }}</td>
        @endif
        @endif
      </tr>
      @empty
      <tr>
        <td colspan="9" class="text-center">No data found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="m-4">
    {{ $data->links() }}
  </div>
</div>