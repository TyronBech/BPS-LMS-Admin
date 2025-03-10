<div class="container flex flex-col overflow-x-auto border-collapse border-2 border-slate-900 mt-12 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Books Inventory</h2>
  <table id="inventory-record" class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead id="today-header" class="bg-blue-400 font-bold text-slate-200">
      <th>Last Updated</th>
      <th>Accession</th>
      <th>Call Number</th>
      <th>Barcode</th>
      <th>Title</th>
      <th>Author</th>
      <th>Condition</th>
    </thead>
    <tbody class="text-center">
      @forelse($inventory as $item)
      <tr>
        <td class="pb-1">{{ $item->checked_at }}</td>
        <td class="pb-1">{{ $item->book->accession }}</td>
        <td class="pb-1">{{ $item->book->call_number }}</td>
        <td class="pb-1">{{ $item->book->barcode }}</td>
        <td class="pb-1">{{ $item->book->title }}</td>
        <td class="pb-1">{{ $item->book->author }}</td>
        <td class="pb-1">{{ $item->book->condition_status }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="8" class="text-center py-1.5">No data found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>