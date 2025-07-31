<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Transaction Table</h2>
  <form method="GET" class="m-2">
    <label for="perPage" class="mr-2 text-sm font-medium text-gray-700">Show</label>
    <input type="hidden" name="start" value="{{ request('start') }}">
    <input type="hidden" name="end" value="{{ request('end') }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <input type="hidden" name="type" value="{{ request('type') }}">
    <select name="perPage" id="perPage" onchange="this.form.submit()" class="border border-gray-300 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2, dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
      <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
      <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
      <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
    </select>
    <span class="ml-2 text-sm text-gray-600">entries per page</span>
  </form>
  <table class="overflow-x-auto w-dvw m-4 bg-white dark:bg-gray-800">
    <thead id="today-header" class="bg-blue-400 text-left font-bold text-slate-200 border-2 border-slate-300 dark:border-slate-700">
      <th class="px-2 border-r border-slate-300 dark:border-slate-700">Accession</th>
      <th class="px-2 min-w-[600px] border-r border-slate-300 dark:border-slate-700">Title</th>
      <th class="px-2 min-w-64 border-r border-slate-300 dark:border-slate-700">Name</th>
      @if($type === 'Reserved' || $type === 'All')
      <th class="px-2 min-w-36 border-r border-slate-300 dark:border-slate-700">Reserved Date</th>
      <th class="px-2 min-w-36 border-r border-slate-300 dark:border-slate-700">Pickup Deadline</th>
      @endif
      @if($type === 'Borrowed' || $type === 'All')
      <th class="px-2 min-w-36 border-r border-slate-300 dark:border-slate-700">Borrowed</th>
      <th class="px-2 min-w-36 border-r border-slate-300 dark:border-slate-700">Due</th>
      <th class="px-2 min-w-36 border-r border-slate-300 dark:border-slate-700">Returned</th>
      @endif
      <th class="px-2 min-w-40 border-r border-slate-300 dark:border-slate-700">Transaction Type</th>
      <th class="px-2 min-w-44 border-r border-slate-300 dark:border-slate-700">Status</th>
    </thead>
    <tbody id="students-activity">
      @forelse($data as $item)
      @if($item->user && $item->book)
        <tr class="text-left border-2 border-slate-300 dark:border-slate-700">
          <td class="pb-1 px-2 border-r border-slate-300 dark:border-slate-700">{{ $item->book->accession }}</td>
          <td class="pb-1 px-2 border-r border-slate-300 dark:border-slate-700">{{ $item->book->title }}</td>
          <td class="pb-1 px-2 border-r border-slate-300 dark:border-slate-700">{{ $item->user->last_name }}, {{ $item->user->first_name }} {{ $item->user->middle_name }}</td>
          @if($type === 'Reserved' || $type === 'All')
          <td class="pb-1 px-2 border-r border-slate-300 dark:border-slate-700">{{ $item->reserved_date ?? '-' }}</td>
          <td class="pb-1 px-2 border-r border-slate-300 dark:border-slate-700">{{ $item->pickup_deadline ?? '-' }}</td>
          @endif
          @if($type === 'Borrowed' || $type === 'All')
          <td class="pb-1 px-2 border-r border-slate-300 dark:border-slate-700">{{ $item->date_borrowed ?? '-' }}</td>
          <td class="pb-1 px-2 border-r border-slate-300 dark:border-slate-700">{{ $item->due_date ?? '-' }}</td>
          <td class="pb-1 px-2 border-r border-slate-300 dark:border-slate-700">{{ $item->return_date ?? '-' }}</td>
          @endif
          <td class="pb-1 px-2 border-r border-slate-300 dark:border-slate-700">{{ $item->transaction_type }}</td>
          <td class="pb-1 px-2 border-r border-slate-300 dark:border-slate-700">{{ $item->status }}</td>
        </tr>
      @endif
      @empty
        <tr>
          @if($type === 'All')
          <td colspan="10" class="text-center">No data found.</td>
          @elseif($type === 'Borrowed')
          <td colspan="8" class="text-center">No data found.</td>
          @elseif($type === 'Reserved')
          <td colspan="7" class="text-center">No data found.</td>
          @endif
        </tr>
      @endforelse
    </tbody>
  </table>
  <div class="m-4">
    {{ $data->links() }}
  </div>
</div>