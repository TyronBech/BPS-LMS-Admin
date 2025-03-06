<div class="mx-auto px-2 font-sans flex-col">
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right whitespace-nowrap table-auto">
      <thead class="text-xs py-2 text-gray-700 uppercase bg-gray-300 text-center dark:bg-gray-500 dark:text-white">
        <tr>
          <th scope="col" class="p-2 text-center">Accession</th>
          <th scope="col" class="p-2 text-center">Call Number</th>
          <th scope="col" class="p-2 text-center">Title</th>
          <th scope="col" class="p-2 text-center">Authors</th>
          <th scope="col" class="p-2 text-center">Category</th>
          <th scope="col" class="p-2 text-center">Edition</th>
          <th scope="col" class="p-2 text-center">Publication</th>
          <th scope="col" class="p-2 text-center">Publisher</th>
          <th scope="col" class="p-2 text-center">Copyright</th>
          <th scope="col" class="p-2 text-center">Remarks</th>
          <th scope="col" class="p-2 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($books as $item)
        <tr class="bg-white border-b text-center dark:bg-gray-800 dark:border-gray-600">
          <td class="min-w-40">{{ $item->accession }}</td>
          <td class="min-w-36">{{ $item->call_number }}</td>
          <td class="min-w-72">{{ $item->title }}</td>
          <td class="min-w-80">{{ $item->author }}</td>
          <td class="min-w-40">{{ $item->category->name }}</td>
          <td class="min-w-36">{{ $item->edition }}</td>
          <td class="min-w-72">{{ $item->place_of_publication }}</td>
          <td class="min-w-80">{{ $item->publisher }}</td>
          <td class="min-w-40">{{ $item->copyrights }}</td>
          <td class="min-w-36">{{ $item->remarks }}</td>
          <td class="pb-1 flex justify-center">
            <a href="{{ route('maintenance.edit-book', $item->accession) }}" id="editBtn" name="editBtn" class="text-white bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2">Edit</a>
            <a href="{{ route('maintenance.delete-book', $item->book_id) }}" id="deleteBtn" name="deleteBtn" class="focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2" onclick="return confirm('Are you sure you want to delete this data?')">Delete</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="10" class="text-center py-1.5">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>