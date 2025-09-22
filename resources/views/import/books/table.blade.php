<div class="flex flex-col border-collapse overflow-x-auto border-2 border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-slate-800 dark:border-slate-700">
  <h2 class="text-center mb-2 mt-4 font-semibold text-2xl">Spreadsheet Contents</h2>
  <table class="min-w-full overflow-x-auto table-fixed m-4 bg-white dark:bg-gray-800">
    <thead class="bg-blue-400 font-bold text-slate-200">
      <tr>
        <th>Accession</th>
        <th>Call Number</th>
        <th>Title</th>
        <th>Authors</th>
        <th>Book Type</th>
        <th>Description</th>
        <th>Edition</th>
        <th>Publication</th>
        <th>Publisher</th>
        <th>Copyright</th>
        <th>Category</th>
        <th>URL</th>
      </tr>
    </thead>
    <tbody class="text-center">
      @forelse($data as $index => $item)
        <tr>
          <td><input type="text" name="books[{{ $index }}][accession]" value="{{ $item['accession'] ?? '' }}" class="p-1 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2"></td>
          <td><input type="text" name="books[{{ $index }}][call_number]" value="{{ $item['call_number'] ?? '' }}" class="p-1 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2"></td>
          <td><input type="text" name="books[{{ $index }}][title]" value="{{ $item['title'] ?? '' }}" class="p-1 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2"></td>
          <td><input type="text" name="books[{{ $index }}][authors]" value="{{ $item['authors'] ?? '' }}" class="p-1 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2"></td>
          <td><input type="text" name="books[{{ $index }}][book_type]" value="{{ $item['book_type'] ?? '' }}" class="p-1 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2"></td>
          <td><input type="text" name="books[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" class="p-1 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2"></td>
          <td><input type="text" name="books[{{ $index }}][edition]" value="{{ $item['edition'] ?? '' }}" class="p-1 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2"></td>
          <td><input type="text" name="books[{{ $index }}][place_of_publication]" value="{{ $item['place_of_publication'] ?? '' }}" class="p-1 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2"></td>
          <td><input type="text" name="books[{{ $index }}][publisher]" value="{{ $item['publisher'] ?? '' }}" class="p-1 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2"></td>
          <td><input type="text" name="books[{{ $index }}][copyrights]" value="{{ $item['copyrights'] ?? '' }}" class="p-1 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2"></td>
          <td><input type="text" name="books[{{ $index }}][category]" value="{{ $item['category'] ?? '' }}" class="p-1 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2"></td>
          <td><input type="text" name="books[{{ $index }}][digital_copy_url]" value="{{ $item['digital_copy_url'] ?? '' }}" class="p-1 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2"></td>
        </tr>
      @empty
        <tr>
          <td colspan="10">No data found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>