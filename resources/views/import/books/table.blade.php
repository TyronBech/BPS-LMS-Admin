<div class="container flex flex-col border-collapse overflow-x-auto border-2 border-slate-900 mt-2 mb-4 rounded-lg bg-white">
  <h2 class="text-center mb-2 mt-4 font-semibold text-2xl">Spreadsheet Contents</h2>
  <table class="table-fixed m-4 bg-white">
    <thead class="bg-blue-400 font-bold text-slate-200">
      <tr>
        <th>Accession</th>
        <th>Call Number</th>
        <th>Title</th>
        <th>Authors</th>
        <th>Edition</th>
        <th>Publication</th>
        <th>Publisher</th>
        <th>Copyright</th>
        <th>Category</th>
        <th>Barcode</th>
        <th>URL</th>
      </tr>
    </thead>
    <tbody class="text-center">
      @forelse($data as $item)
        <tr>
          <td>{{ $item['accession'] }}</td>
          <td>{{ $item['call_number'] }}</td>
          <td>{{ $item['title'] }}</td>
          <td>{{ $item['authors'] }}</td>
          <td>{{ $item['edition'] ?? '-' }}</td>
          <td>{{ $item['place_of_publication'] }}</td>
          <td>{{ $item['publisher'] }}</td>
          <td>{{ $item['copyrights'] }}</td>
          <td>{{ $item['category'] }}</td>
          <td>{{ $item['barcode'] }}</td>
          <td>{{ $item['digital_copy_url'] ?? '-' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="9">No data found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>