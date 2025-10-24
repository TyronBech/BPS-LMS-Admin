<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
  <h2 class="text-lg md:text-xl font-bold tracking-tight text-gray-900 dark:text-white p-4 bg-white dark:bg-gray-800">
    List of Database Backups
  </h2>
  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
      <tr>
        <th scope="col" class="px-6 py-3">Filename</th>
        <th scope="col" class="px-6 py-3 hidden sm:table-cell">Type</th>
        <th scope="col" class="px-6 py-3 hidden md:table-cell">Size</th>
        <th scope="col" class="px-6 py-3 hidden lg:table-cell">Date</th>
        <th scope="col" class="px-6 py-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($backups as $item)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
          <div class="text-base font-semibold">{{ $item['filename'] }}</div>
          <div class="font-normal text-gray-500 lg:hidden">{{ $item['created'] }}</div>
        </th>
        <td class="px-6 py-4 hidden sm:table-cell">{{ $item['type'] }}</td>
        <td class="px-6 py-4 hidden md:table-cell">{{ $item['size'] }}</td>
        <td class="px-6 py-4 hidden lg:table-cell">{{ $item['created'] }}</td>
        <td class="px-6 py-4">
          <div class="flex items-center space-x-2">
            <form action="{{ route('backup.download', ['filename' => $item['filename']]) }}" method="POST" class="inline-block">
              @csrf
              <button type="submit" value="download" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-green-800">Download</button>
            </form>
            <form action="{{ route('backup.destroy', ['filename' => $item['filename']]) }}" method="POST" class="inline-block">
              @csrf
              @method('DELETE')
              <button type="submit" value="delete" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800">Delete</button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
        <td colspan="5" class="px-6 py-4 text-center">No backups found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>