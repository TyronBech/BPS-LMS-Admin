<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Report Table for Penalties</h2>
  <table class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead class="bg-blue-400 text-center font-bold text-slate-200">
      <th>Name</th>
      <th>Accession</th>
      <th>Book</th>
      <th>Borrowed</th>
      <th>Due</th>
      <th>Returned</th>
      <th>Violation</th>
      <th>Amount</th>
      <th>Status</th>
    </thead>
    <tbody>
      @forelse($data as $item)
      <tr class="text-center">
        <td class="px-4 py-2">{{ $item->transaction->user->first_name }} {{ $item->transaction->user->last_name }}</td>
        <td class="px-4 py-2">{{ $item->transaction->book->accession }}</td>
        <td class="px-4 py-2">{{ $item->transaction->book->title }}</td>
        <td class="px-4 py-2">{{ $item->transaction->date_borrowed }}</td>
        <td class="px-4 py-2">{{ $item->transaction->due_date ?? '-' }}</td>
        <td class="px-4 py-2">{{ $item->transaction->return_date ?? '-' }}</td>
        <td class="px-4 py-2">{{ $item->penaltyRule->type }}</td>
        <td class="px-4 py-2">{{ $item->amount }}</td>
        <td class="px-4 py-2">{{ $item->transaction->penalty_status }}</td>
      @empty
      <tr>
        <td colspan="6" class="text-center">No data found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>