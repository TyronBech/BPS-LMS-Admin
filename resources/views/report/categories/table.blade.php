<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Report Table for Users</h2>
  <table class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead id="today-header" class="bg-blue-400 text-center font-bold text-slate-200">
      <th>Legend</th>
      <th>Description</th>
      <th>Previous Inventory</th>
      <th>Newly Acquired</th>
      <th>Discarded</th>
      <th>Present Inventory</th>
    </thead>
    <tbody id="students-activity">
      @forelse($data as $item)
        <tr class="text-center">
          <td class="pb-1 text-center">{{ $item->legend }}</td>
          <td class="pb-1 text-center">{{ $item->name }}</td>
          <td class="pb-1 text-center">{{ $item->previous_inventory }}</td>
          <td class="pb-1 text-center">{{ $item->newly_acquired }}</td>
          <td class="pb-1 text-center">{{ $item->discarded }}</td>
          <td class="pb-1 text-center">{{ $item->present_inventory }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="text-center">No data found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>