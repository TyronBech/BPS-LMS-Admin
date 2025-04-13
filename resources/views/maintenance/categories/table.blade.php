<div class="mx-auto px-2 font-sans flex-col">
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right whitespace-nowrap table-auto">
      <thead class="text-xs py-2 text-gray-700 uppercase bg-gray-300 text-center dark:bg-gray-500 dark:text-white">
        <tr>
          <th scope="col" class="p-2 text-center">Legend</th>
          <th scope="col" class="p-2 text-center">Name</th>
          <th scope="col" class="p-2 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($categories as $key => $value)
        <tr class="bg-white border-b text-center dark:bg-gray-800 dark:border-gray-600">
          <td class="min-w-40 h-14">{{ $key }}</td>
          <td class="min-w-40 h-14">{{ $value }}</td>
          <td class="pb-1 flex justify-center">
            <a href="{{ route('maintenance.edit-book', $key) }}" id="editBtn" name="editBtn" class="text-white bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2">Edit</a>
            <button data-modal-target="popup-modal" data-modal-toggle="popup-modal" class="focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2" type="button">
              Delete
            </button>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="3" class="text-center py-1.5">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
