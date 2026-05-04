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
      <div class="mt-3 grid grid-cols-1 lg:grid-cols-12 gap-3">
        <div class="lg:col-span-10 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Payment Summary</h3>

          <div class="divide-y divide-gray-200 dark:divide-gray-700 border-y border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-2 py-1.5 text-sm text-gray-800 dark:text-gray-200">
              <span>Penalty Amount</span>
              <span class="text-right font-medium">₱ {{ number_format($summary['penalty_amount'], 2) }}</span>
            </div>
            <div class="grid grid-cols-2 py-1.5 text-sm text-gray-700 dark:text-gray-300">
              <span>Amount Discounted</span>
              <span class="text-right font-medium">₱ {{ number_format($summary['discounted_amount'], 2) }}</span>
            </div>
            <div class="grid grid-cols-2 py-1.5 text-sm text-gray-700 dark:text-gray-300">
              <span>Amount Waived</span>
              <span class="text-right font-medium">₱ {{ number_format($summary['waived_amount'], 2) }}</span>
            </div>
            <div class="grid grid-cols-2 py-1.5 text-sm text-gray-700 dark:text-gray-300">
              <span>Not Paid Amount</span>
              <span class="text-right font-medium">₱ {{ number_format($summary['unpaid_amount'], 2) }}</span>
            </div>
            <div class="grid grid-cols-2 py-1.5 text-sm text-gray-700 dark:text-gray-300">
              <span>Other Amount</span>
              <span class="text-right font-medium">₱ {{ number_format($summary['other_amount'], 2) }}</span>
            </div>
          </div>

          <div class="grid grid-cols-2 py-2 border-b border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white mt-1">
            <span class="text-base sm:text-lg font-semibold">Paid Collectible</span>
            <span class="text-base sm:text-lg font-semibold text-right">₱ {{ number_format($summary['paid_collectible'] ?? 0, 2) }}</span>
          </div>
          <div class="grid grid-cols-2 py-2 text-orange-600 dark:text-orange-400">
            <span class="text-base sm:text-lg font-semibold">Unpaid Collectible</span>
            <span class="text-base sm:text-lg font-semibold text-right">₱ {{ number_format($summary['unpaid_collectible'] ?? $summary['current_balance'] ?? 0, 2) }}</span>
          </div>
        </div>

        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3">
          <div class="rounded-lg flex flex-col justify-center border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3 text-center">
            <div class="text-xl sm:text-2xl font-bold text-green-600 dark:text-green-400">₱ {{ number_format($summary['paid_collectible'] ?? ($summary['paid_related_total'] ?? 0), 2) }}</div>
            <div class="text-xs text-green-700 dark:text-green-300">Payment Recorded</div>
          </div>

          <div class="rounded-lg flex flex-col justify-center border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3 text-center">
            <div class="text-xl sm:text-2xl font-bold text-orange-600 dark:text-orange-400">₱ {{ number_format($summary['current_balance'] ?? $summary['unpaid_collectible'] ?? ($summary['remaining'] ?? 0), 2) }}</div>
            <div class="text-xs text-orange-700 dark:text-orange-300">Payment Pending</div>
          </div>
        </div>
      </div>
    </div>
    <div class="p-4">
      {{ $data->withQueryString()->links() }}
    </div>
  </div>
</div>
