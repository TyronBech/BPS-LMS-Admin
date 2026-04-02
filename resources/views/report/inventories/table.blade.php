<div id="tabular" class="container mx-auto mt-2 mb-4">
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="p-4">
      <h2 class="text-center mb-4 font-semibold text-2xl dark:text-white">Report Table for Inventory</h2>
      <form method="GET" class="flex items-center">
        <label for="perPage" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
        <input type="hidden" name="start" value="{{ old('start', request('start')) }}">
        <input type="hidden" name="end" value="{{ old('end', request('end')) }}">
        <input type="number" name="perPage" id="perPage" min="1" max="500" onchange="this.form.submit()" value="{{ old('perPage', $perPage) }}" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-500 focus:border-primary-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">entries per page</span>
      </form>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th scope="col" class="px-6 py-3">Accession</th>
            <th scope="col" class="px-6 py-3">Author</th>
            <th scope="col" class="px-6 py-3">Title</th>
            <th scope="col" class="px-6 py-3">Remarks</th>
          </tr>
        </thead>
        <tbody>
          @forelse($data as $item)
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            <td class="px-6 py-4">{{ $item->accession ?? 'No Accession' }}</td>
            <td class="px-6 py-4">{{ $item->author ?? 'No Author' }}</td>
            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
              {{ $item->title ?? 'No Title' }}
            </th>
            <td class="px-6 py-4">{{ $item->remarks ?? 'No Remarks' }}</td>
          </tr>
          @empty
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            <td colspan="4" class="px-6 py-4 text-center">No data found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-4">
      {{ $data->withQueryString()->fragment('tabular')->links() }}
    </div>
  </div>
</div>
