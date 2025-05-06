<form action="{{ route('inventory.update') }}" method="POST" class="container flex flex-col overflow-x-auto border-collapse border-2 border-slate-900 mt-12 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  @csrf
  @method('PATCH')
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Books Inventory</h2>
  <table id="inventory-record" class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead id="today-header" class="bg-blue-400 font-bold text-slate-200">
      <th>Accession</th>
      <th>Call Number</th>
      <th>Title</th>
      <th>Author</th>
      <th>Condition</th>
      <th>Action</th>
    </thead>
    <tbody class="text-center">
      @forelse($books as $book)
      <tr>
        <td class="mt-1">{{ $book->accession }}</td>
        <td class="mt-1">{{ $book->call_number }}</td>
        <td class="mt-1">{{ $book->title }}</td>
        <td class="mt-1">{{ $book->author }}</td>
        <td class="mt-1 mx-2">
          <select name="condition[{{ $book->accession }}]" id="condition" class="w-full p-2 border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            @foreach($conditions as $condition)
            @if($condition == $book->condition_status)
            <option value="{{ $condition }}" selected>{{ $condition }}</option>
            @else
            <option value="{{ $condition }}">{{ $condition }}</option>
            @endif
            @endforeach
          </select>
        </td>
        <td class="mt-1">
          <button type="button" data-modal-toggle="popup-modal"  class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">Delete</button>
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