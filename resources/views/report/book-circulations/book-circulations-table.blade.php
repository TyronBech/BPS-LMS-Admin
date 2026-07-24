<div id="tabular" class="container mx-auto mt-2 mb-4">
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
    <div class="p-4">
      <h2 class="text-center mb-4 font-semibold text-2xl dark:text-white">Accession List Table</h2>
      <form method="GET" class="flex items-center">
        <label for="perPage" class="mr-2 text-xs font-medium text-gray-500 dark:text-gray-400">Show</label>
        <input type="hidden" name="barcode" value="{{ old('barcode', request('barcode')) }}">
        <input type="hidden" name="title" value="{{ old('title', request('title')) }}">
        <input type="hidden" name="availability" value="{{ old('availability', request('availability')) }}">
        <input type="number" name="perPage" id="perPage" min="1" max="500" onchange="this.form.submit()" value="{{ old('perPage', $perPage) }}" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-500 focus:border-primary-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
        <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">entries per page</span>
      </form>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-xs text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th scope="col" class="px-4 py-2">Accession</th>
            <th scope="col" class="px-4 py-2">Author</th>
            <th scope="col" class="px-4 py-2">Title</th>
            <th scope="col" class="px-4 py-2">Publication</th>
            <th scope="col" class="px-4 py-2">Publisher</th>
            <th scope="col" class="px-4 py-2">Call Number</th>
            <th scope="col" class="px-4 py-2">ISBN</th>
            <th scope="col" class="px-4 py-2">Copyright</th>
            <th scope="col" class="px-4 py-2">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($data as $item)
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            <td class="px-4 py-2">{{ $item->accession }}</td>
            <td class="px-4 py-2">{{ $item->author ?? 'N/A' }}</td>
            <th scope="row" class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
              {{ $item->title }}
            </th>
            <td class="px-4 py-2">{{ $item->place_of_publication ?? 'N/A' }}</td>
            <td class="px-4 py-2">{{ $item->publisher ?? 'N/A' }}</td>
            <td class="px-4 py-2">{{ $item->call_number ?? 'N/A' }}</td>
            <td class="px-4 py-2">{{ $item->isbn ?? 'N/A' }}</td>
            <td class="px-4 py-2">{{ $item->copyrights ?? 'N/A' }}</td>
            <td class="px-4 py-2">{{ $item->remarks ?? 'N/A' }}</td>
          </tr>
          @empty
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            <td colspan="9" class="px-4 py-2 text-center">No data found.</td>
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