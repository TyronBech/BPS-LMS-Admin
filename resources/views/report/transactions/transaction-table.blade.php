<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Transaction Table</h2>
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
      <th class="px-2 border-r border-slate-300 dark:border-slate-700">Transaction Type</th>
      <th class="px-2 border-r border-slate-300 dark:border-slate-700">Status</th>
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
          <td colspan="7" class="text-center">No data found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>