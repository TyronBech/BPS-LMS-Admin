<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Report Table for Penalties</h2>
  <table class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead class="bg-blue-400 text-left font-bold text-slate-200 border-2 border-slate-300 dark:border-slate-700">
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Name</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Accession</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Book</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Borrowed</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Due</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Returned</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Violation</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Amount</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Status</th>
    </thead>
    <tbody>
      @forelse($data as $item)
      <tr class="text-left border-2 border-slate-300 dark:border-slate-700">
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->transaction->user->first_name }} {{ $item->transaction->user->last_name }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->transaction->book->accession }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->transaction->book->title }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->transaction->date_borrowed }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->transaction->due_date ?? '-' }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->transaction->return_date ?? '-' }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->penaltyRule->type }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->amount }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->transaction->penalty_status }}</td>
      @empty
      <tr>
        <td colspan="9" class="text-center">No data found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>