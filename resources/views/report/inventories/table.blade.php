<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Report Table for Inventory</h2>
  <table class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead id="today-header" class="bg-blue-400 text-center font-bold text-slate-200">
      <th>Accession</th>
      <th>Call Number</th>
      <th>Title</th>
      <th>Author</th>
      <th>Last Inventory</th>
    </thead>
    <tbody id="students-activity">
      @forelse($data as $item)
        <tr class="text-center">
          <td>{{ $item->book->accession }}</td>
          <td>{{ $item->book->call_number }}</td>
          <td>{{ $item->book->title }}</td>
          <td>{{ $item->book->author }}</td>
          <td>{{ $item->checked_at }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="text-center">No data found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>