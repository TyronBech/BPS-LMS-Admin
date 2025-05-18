<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Report Table for Materials</h2>
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
        <td class="pb-1">{{ $item->legend }}</td>
        <td class="pb-1 text-center border-r border-slate-900 dark:border-gray-600">{{ $item->name }}</td>
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
      <tr class="border-t border-slate-900 dark:border-gray-600">
        <td class="pb-1 text-right border-r pr-3 border-slate-900 dark:border-gray-600" colspan="2">Total:</td>
        <td class="pb-1 text-center">{{ $data->sum('previous_inventory') }}</td>
        <td class="pb-1 text-center">{{ $data->sum('newly_acquired') }}</td>
        <td class="pb-1 text-center">{{ $data->sum('discarded') }}</td>
        <td class="pb-1 text-center">{{ $data->sum('present_inventory') }}</td>
      </tr>
    </tbody>
  </table>
</div>
<form action="{{ route('report.summary-export') }}" method="POST">
  @csrf
  <button type="submit" id="submit" name="submit" value="pdf" class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white text-sm font-bold py-1 px-4 rounded h-12 mt-2 mb-2 ml-4 mr-4 w-20">Export PDF</button>
  <button type="submit" id="submit" name="submit" value="excel" class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white text-sm font-bold py-1 px-4 rounded h-12 mt-2 mb-2 ml-4 mr-4 w-20">Export Excel</button>
</form>