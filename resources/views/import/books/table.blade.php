<div class="w-full border-collapse border-2 border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-slate-800 dark:border-slate-700">
  <h2 class="text-center mb-2 mt-4 font-semibold text-2xl">Spreadsheet Contents</h2>
  <div class="overflow-x-auto p-4">
    <table class="min-w-full table-auto bg-white dark:bg-gray-800">
      <thead class="bg-blue-400 font-bold text-slate-200">
        <tr>
          <th class="px-6 py-3 text-left">Accession</th>
          <th class="px-6 py-3 text-left">Call Number</th>
          <th class="px-6 py-3 text-left">Title</th>
          <th class="px-6 py-3 text-left">Authors</th>
          <th class="px-6 py-3 text-left">Book Type</th>
          <th class="px-6 py-3 text-left">Description</th>
          <th class="px-6 py-3 text-left">Edition</th>
          <th class="px-6 py-3 text-left">Publication</th>
          <th class="px-6 py-3 text-left">Publisher</th>
          <th class="px-6 py-3 text-left">Copyright</th>
          <th class="px-6 py-3 text-left">Category</th>
          <th class="px-6 py-3 text-left">URL</th>
        </tr>
      </thead>
      <tbody class="text-left">
        @forelse($data as $index => $item)
        <tr>
          <td class="px-2 py-2"><input type="text" name="books[{{ $index }}][accession]" value="{{ $item['accession'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="books[{{ $index }}][call_number]" value="{{ $item['call_number'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="books[{{ $index }}][title]" value="{{ $item['title'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[250px]"></td>
          <td class="px-2 py-2"><input type="text" name="books[{{ $index }}][authors]" value="{{ $item['authors'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[200px]"></td>
          <td class="px-2 py-2"><input type="text" name="books[{{ $index }}][book_type]" value="{{ $item['book_type'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[100px]"></td>
          <td class="px-2 py-2"><input type="text" name="books[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[300px]"></td>
          <td class="px-2 py-2"><input type="text" name="books[{{ $index }}][edition]" value="{{ $item['edition'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[100px]"></td>
          <td class="px-2 py-2"><input type="text" name="books[{{ $index }}][place_of_publication]" value="{{ $item['place_of_publication'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[200px]"></td>
          <td class="px-2 py-2"><input type="text" name="books[{{ $index }}][publisher]" value="{{ $item['publisher'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[200px]"></td>
          <td class="px-2 py-2"><input type="text" name="books[{{ $index }}][copyrights]" value="{{ $item['copyrights'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[100px]"></td>
          <td class="px-2 py-2"><input type="text" name="books[{{ $index }}][category]" value="{{ $item['category'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
          <td class="px-2 py-2"><input type="text" name="books[{{ $index }}][digital_copy_url]" value="{{ $item['digital_copy_url'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[250px]"></td>
        </tr>
        @empty
        <tr>
          <td colspan="12" class="text-center py-4">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>