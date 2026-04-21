@use('App\Enum\PermissionsEnum')
<div class="bg-white dark:bg-gray-800 rounded-lg mt-2 mb-4 shadow-md">
  <div class="p-4">
    <h2 class="text-center mb-4 font-semibold text-2xl dark:text-white">Report Table for Materials</h2>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
      <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
        <tr>
          <th scope="col" class="px-6 py-3">Legend</th>
          <th scope="col" class="px-6 py-3">Description</th>
          <th scope="col" class="px-6 py-3">Previous Inventory</th>
          <th scope="col" class="px-6 py-3">Newly Acquired</th>
          <th scope="col" class="px-6 py-3">Lost and Paid For</th>
          <th scope="col" class="px-6 py-3">Lost and Replaced</th>
          <th scope="col" class="px-6 py-3">Unreturned</th>
          <th scope="col" class="px-6 py-3">Missing</th>
          <th scope="col" class="px-6 py-3">Discarded</th>
          <th scope="col" class="px-6 py-3">Present Inventory</th>
        </tr>
      </thead>
      <tbody>
        @forelse($data as $item)
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
          <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $item->legend }}</th>
          <td class="px-6 py-4">{{ $item->name }}</td>
          <td class="px-6 py-4 text-center">{{ $item->previous_inventory }}</td>
          <td class="px-6 py-4 text-center">{{ $item->newly_acquired }}</td>
          <td class="px-6 py-4 text-center">{{ $item->lost_and_paid_for }}</td>
          <td class="px-6 py-4 text-center">{{ $item->lost_and_replaced }}</td>
          <td class="px-6 py-4 text-center">{{ $item->unreturned }}</td>
          <td class="px-6 py-4 text-center">{{ $item->missing }}</td>
          <td class="px-6 py-4 text-center">{{ $item->discarded }}</td>
          <td class="px-6 py-4 text-center">{{ $item->present_inventory }}</td>
        </tr>
        @empty
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
          <td colspan="10" class="px-6 py-4 text-center">No data found.</td>
        </tr>
        @endforelse
        <tr class="bg-gray-50 font-bold dark:bg-gray-700 dark:text-gray-300">
          <td class="px-6 py-4 text-right" colspan="2">Total:</td>
          <td class="px-6 py-4 text-center">{{ $data->sum('previous_inventory') }}</td>
          <td class="px-6 py-4 text-center">{{ $data->sum('newly_acquired') }}</td>
          <td class="px-6 py-4 text-center">{{ $data->sum('lost_and_paid_for') }}</td>
          <td class="px-6 py-4 text-center">{{ $data->sum('lost_and_replaced') }}</td>
          <td class="px-6 py-4 text-center">{{ $data->sum('unreturned') }}</td>
          <td class="px-6 py-4 text-center">{{ $data->sum('missing') }}</td>
          <td class="px-6 py-4 text-center">{{ $data->sum('discarded') }}</td>
          <td class="px-6 py-4 text-center">{{ $data->sum('present_inventory') }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<form action="{{ route('report.summary-export') }}" method="POST">
  @csrf
  @if(auth()->user()->can(PermissionsEnum::CREATE_REPORTS))
  <div class="flex flex-col sm:flex-row justify-end gap-2 mt-4">
    <button type="submit" name="submit" value="pdf" class="w-full sm:w-auto bg-red-500 hover:bg-red-700 active:bg-red-900 text-white font-bold py-2 px-4 rounded">PDF</button>
    <button type="submit" name="submit" value="excel" class="w-full sm:w-auto bg-green-500 hover:bg-green-700 active:bg-green-900 text-white font-bold py-2 px-4 rounded">Excel</button>
  </div>
  @endif
</form>
