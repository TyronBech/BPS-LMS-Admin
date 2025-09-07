<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">List of Database Backups</h2>
  <div class="mx-auto px-2 font-sans flex-col">
    <div class="relative mb-5 overflow-x-auto shadow-md sm:rounded-lg">
      <table class="w-full text-sm text-left rtl:text-right whitespace-nowrap table-auto">
        <thead class="text-xs py-2 text-gray-700 uppercase bg-gray-300 text-center dark:bg-gray-500 dark:text-white">
          <tr>
            <th scope="col" class="p-2 text-center min-w-32">Filename</th>
            <th scope="col" class="p-2 text-center min-w-32">Type</th>
            <th scope="col" class="p-2 text-center min-w-32">Size</th>
            <th scope="col" class="p-2 text-center min-w-32">Date</th>
            <th scope="col" class="p-2 text-center min-w-32">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white border-b text-center dark:bg-gray-800 dark:border-gray-600">
          @forelse($backups as $item)
          <tr>
            <td class="p-4">{{ $item['filename'] }}</td>
            <td class="p-4">{{ $item['type'] }}</td>
            <td class="p-4">{{ $item['size'] }}</td>
            <td class="p-4">{{ $item['created'] }}</td>
            <td class="p-4">
              <form action="{{ route('backup.download', ['filename' => $item['filename']]) }}" method="POST" class="inline-block">
                @csrf
                <button type="submit" value="download" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">Download</button>
              </form>
              <form action="{{ route('backup.destroy', ['filename' => $item['filename']]) }}" method="POST" class="inline-block">
                @csrf
                @method('DELETE')
                <button type="submit" value="delete" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">Delete</button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center py-1.5">No data found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>