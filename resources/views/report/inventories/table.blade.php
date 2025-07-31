<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Report Table for Inventory</h2>
    <form method="GET" class="m-2">
    <label for="perPage" class="mr-2 text-sm font-medium text-gray-700">Show</label>
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
    <thead id="today-header" class="bg-blue-400 text-left font-bold text-slate-200 border-2 border-slate-300 dark:border-slate-700">
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Accession</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Call Number</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Title</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Author</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Last Inventory</th>
    </thead>
    <tbody id="students-activity">
      @forelse($data as $item)
      <tr class="text-left border-2 border-slate-300 dark:border-slate-700">
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->book->accession }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->book->call_number }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->book->title }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->book->author }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->checked_at ?? 'Pending' }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="8" class="text-center">No data found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="m-4">
    {{ $data->links() }}
  </div>
</div>