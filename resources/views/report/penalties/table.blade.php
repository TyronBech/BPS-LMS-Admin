<div id="tabular" class="container mx-auto mt-2 mb-4">
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="p-4">
      <h2 class="text-center mb-4 font-semibold text-2xl dark:text-white">Report Table for Penalties</h2>
      <form method="GET" class="flex items-center">
        <label for="perPage" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="start" value="{{ request('start') }}">
        <input type="hidden" name="end" value="{{ request('end') }}">
        <select name="perPage" id="perPage" onchange="this.form.submit()" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-400 focus:border-primary-400 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
        </select>
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
            <td class="px-6 py-4 whitespace-nowrap">{{ $item->due ?? '-' }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ $item->returned ?? '-' }}</td>
            <td class="px-6 py-4">{{ ucwords($item->violation) ?? '-' }}</td>
            <td class="px-6 py-4 whitespace-nowrap">₱ {{ number_format($item->total, 2) }}</td>
            <td class="px-6 py-4">{{ ucwords($item->status) }}</td>
          </tr>
          @empty
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            <td colspan="9" class="px-6 py-4 text-center">No data found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-4">
      {{ $data->withQueryString()->links() }}
    </div>
  </div>
</div>