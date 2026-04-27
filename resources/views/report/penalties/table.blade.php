<div id="tabular" class="container mx-auto mt-2 mb-4">
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
    <div class="p-4">
      <h2 class="text-center mb-4 font-semibold text-2xl dark:text-white">Report Table for Penalties</h2>
      <form method="GET" class="flex items-center">
        <label for="perPage" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
        <input type="hidden" name="search" value="{{ old('search', request('search')) }}">
        <input type="hidden" name="start" value="{{ old('start', request('start')) }}">
        <input type="hidden" name="end" value="{{ old('end', request('end')) }}">
        <input type="hidden" name="penalty_status" value="{{ old('penalty_status', request('penalty_status')) }}">
        <input type="number" name="perPage" id="perPage" min="1" max="500" onchange="this.form.submit()" value="{{ old('perPage', $perPage) }}" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-500 focus:border-primary-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">entries per page</span>
      </form>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th scope="col" class="px-6 py-3 whitespace-nowrap">Name</th>
            <th scope="col" class="px-6 py-3">Accession</th>
            <th scope="col" class="px-6 py-3">Book</th>
            <th scope="col" class="px-6 py-3 whitespace-nowrap">Borrowed</th>
            <th scope="col" class="px-6 py-3 whitespace-nowrap">Due</th>
            <th scope="col" class="px-6 py-3 whitespace-nowrap">Returned</th>
            <th scope="col" class="px-6 py-3">Violation</th>
            <th scope="col" class="px-6 py-3 whitespace-nowrap">Amount</th>
            <th scope="col" class="px-6 py-3">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($data as $item)
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
              {{ $item->user->first_name }} {{ $item->user->last_name }}
            </th>
            <td class="px-6 py-4">{{ $item->book->accession }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ $item->book->title }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ $item->borrowed }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ $item->due ?? 'No Due Date' }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ $item->returned ?? 'Unreturned' }}</td>
            <td class="px-6 py-4">{{ ucwords($item->violation) ?? 'No Violation' }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              @if($item->has_discount)
              <div class="flex items-start gap-2">
                <div>
                  <div class="text-gray-500 line-through">₱ {{ number_format($item->actual_total, 2) }}</div>
                  <div class="font-semibold text-green-600 dark:text-green-400">₱ {{ number_format($item->total, 2) }}</div>
                </div>
                <span class="text-xs font-medium text-green-600 dark:text-green-400 whitespace-nowrap">{{ $item->discount_percent_label }} discount</span>
              </div>
              @else
              ₱ {{ number_format($item->total, 2) ?? '0.00' }}
              @endif
            </td>
            <td class="px-6 py-4">{{ ucwords($item->status) ?? 'No Status' }}</td>
          </tr>
          @empty
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            <td colspan="9" class="px-6 py-4 text-center">No data found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="px-4 pb-2">
      <div class="max-w-sm mt-3 border-gray-300 dark:border-gray-600 pt-3 space-y-1 text-sm text-gray-700 dark:text-gray-300">
        @foreach($summary['rows'] as $summaryRow)
        <div class="flex items-center justify-between {{ $summaryRow['is_total'] ? 'font-semibold border-t border-gray-300 dark:border-gray-600 pt-2 mt-2' : '' }}">
          <span>{{ $summaryRow['label'] }}</span>
          <span>₱ {{ number_format($summaryRow['amount'], 2) }}</span>
        </div>
        @endforeach
      </div>
    </div>
    <div class="p-4">
      {{ $data->withQueryString()->links() }}
    </div>
  </div>
</div>
