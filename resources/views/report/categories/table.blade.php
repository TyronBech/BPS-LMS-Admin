@use('App\Enum\PermissionsEnum')
<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Report Table for Materials</h2>
  <table class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead id="today-header" class="bg-blue-400 border-2 text-left font-bold text-slate-200 border-slate-300 dark:border-slate-700">
      <th>Legend</th>
      <th>Description</th>
      <th>Previous Inventory</th>
      <th>Newly Acquired</th>
      <th>Discarded</th>
      <th>Present Inventory</th>
    </thead>
    <tbody id="students-activity">
      @forelse($data as $item)
      <tr class="text-left text-slate-200 border-2 border-slate-300 dark:border-slate-700">
        <td class="pl-2 border-2 border-slate-300 dark:border-slate-700">{{ $item->legend }}</td>
        <td class="pl-2 border-2 border-slate-300 dark:border-slate-700 text-left">{{ $item->name }}</td>
        <td class="pl-2 border-2 border-slate-300 dark:border-slate-700 text-left">{{ $item->previous_inventory }}</td>
        <td class="pl-2 border-2 border-slate-300 dark:border-slate-700 text-left">{{ $item->newly_acquired }}</td>
        <td class="pl-2 border-2 border-slate-300 dark:border-slate-700 text-left">{{ $item->discarded }}</td>
        <td class="pl-2 border-2 border-slate-300 dark:border-slate-700 text-left">{{ $item->present_inventory }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="8" class="text-center">No data found.</td>
      </tr>
      @endforelse
      <tr class="border-2 border-slate-900 dark:border-gray-600">
        <td class="pb-1 pl-2 pr-2 text-right border-2 border-slate-300 dark:border-slate-700" colspan="2">Total:</td>
        <td class="pb-1 pl-2 border-2 border-slate-300 dark:border-slate-700">{{ $data->sum('previous_inventory') }}</td>
        <td class="pb-1 pl-2 border-2 border-slate-300 dark:border-slate-700">{{ $data->sum('newly_acquired') }}</td>
        <td class="pb-1 pl-2 border-2 border-slate-300 dark:border-slate-700">{{ $data->sum('discarded') }}</td>
        <td class="pb-1 pl-2 border-2 border-slate-300 dark:border-slate-700">{{ $data->sum('present_inventory') }}</td>
      </tr>
    </tbody>
  </table>
</div>
<form action="{{ route('report.summary-export') }}" method="POST">
  @csrf
  @if(auth()->user()->can(PermissionsEnum::CREATE_REPORTS))
  <button type="submit" id="submit" name="submit" value="pdf" class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white text-sm font-bold py-1 px-4 rounded h-12 mt-2 mb-2 ml-4 mr-4 w-20">Export PDF</button>
  <button type="submit" id="submit" name="submit" value="excel" class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white text-sm font-bold py-1 px-4 rounded h-12 mt-2 mb-2 ml-4 mr-4 w-20">Export Excel</button>
  @endif
</form>