<form action="{{ route('inventory.update') }}" method="POST" class="container flex flex-col overflow-x-auto border-collapse border-2 border-slate-900 mt-12 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  @csrf
  @method('PATCH')
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Books Inventory</h2>
  <table id="inventory-record" class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead id="today-header" class="bg-blue-400 font-bold text-slate-200">
      <th>Accession</th>
      <th>Call Number</th>
      <th>Barcode</th>
      <th>Title</th>
      <th>Author</th>
      <th>Remarks</th>
      <th>Condition</th>
    </thead>
    <tbody class="text-center">
      @forelse($inventory as $item)
      <tr>
        <td class="pb-1">{{ $item->book->accession }}</td>
        <td class="pb-1">{{ $item->book->call_number }}</td>
        <td class="pb-1">{{ $item->book->barcode }}</td>
        <td class="pb-1">{{ $item->book->title }}</td>
        <td class="pb-1">{{ $item->book->author }}</td>
        <td class="pb-1 mx-2">
          <select id="remarks" name="remarks[{{ $item->book->accession }}]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            @foreach($remarks as $value)
            @if($item->book->remarks == $value)
            <option value="{{ $value }}" selected>{{ $value }}</option>
            @else
            <option value="{{ $value }}">{{ $value }}</option>
            @endif
            @endforeach
          </select>
        </td>
        <td class="pb-1 mx-2">
          <select id="condition" name="condition[{{ $item->book->accession }}]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            @foreach($conditions as $value)
            @if($item->book->condition_status == $value)
            <option value="{{ $value }}" selected>{{ $value }}</option>
            @else
            <option value="{{ $value }}">{{ $value }}</option>
            @endif
            @endforeach
          </select>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="8" class="text-center py-1.5" id="no-data">No data found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <button type="submit" class="text-white max-w-36 self-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Save</button>
</form>