<div class="container flex flex-col overflow-x-auto border-collapse border-2 border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Book Circulation Table</h2>
  <table class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead id="today-header" class="bg-blue-400 text-left font-bold text-slate-200 border-2 border-slate-300 dark:border-slate-700">
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Accession</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Call Number</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Title</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Category</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Availability</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Condition</th>
    </thead>
    <tbody id="students-activity">
      @forelse($data as $item)
        <tr class="text-left border-2 border-slate-300 dark:border-slate-700">
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->accession }}</td>
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->call_number }}</td>
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->title }}</td>
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->category->name }}</td>
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->availability_status }}</td>
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->condition_status }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="5">No data found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>