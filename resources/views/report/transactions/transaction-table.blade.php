<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Transaction Table</h2>
  <table class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead id="today-header" class="bg-blue-400 font-bold text-slate-200">
      <th>Accession</th>
      <th>Title</th>
      <th>Name</th>
      <th>Borrowed</th>
      <th>Due</th>
      <th>Returned</th>
      <th>Status</th>
    </thead>
    <tbody id="students-activity" class="text-center">
      @forelse($data as $item)
      @if($item->users && $item->books)
        <tr>
          <td class="pb-1">{{ $item->books->accession }}</td>
          <td class="pb-1">{{ $item->books->title }}</td>
          <td class="pb-1">{{ $item->users->last_name }}, {{ $item->users->first_name }} {{ $item->users->middle_name }}</td>
          <td class="pb-1">{{ $item->date_borrowed }}</td>
          <td class="pb-1">{{ $item->due_date }}</td>
          <td class="pb-1">{{ $item->return_date ? $item->return_date : '-' }}</td>
          @if($item->transaction_type == 'Borrow')
            <td class="text-red-600 pb-1 dark:text-red-400">{{ $item->transaction_type }}</td>
          @elseif($item->transaction_type == 'Return')
            <td class="text-green-600 pb-1 dark:text-green-400">{{ $item->transaction_type }}</td> 
          @endif
        </tr>
      @endif
      @empty
        <tr>
          <td colspan="8">No data found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>