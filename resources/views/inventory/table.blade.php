<div class="container mx-auto mt-12 mb-4">
  <div class="flex flex-col rounded-lg bg-white dark:bg-gray-800 shadow-sm">
    <h2 class="text-center mb-4 mt-4 font-semibold text-2xl dark:text-white">Books Inventory</h2>
    <form id="inventory-form" action="{{ route('inventory.dashboard') }}" method="POST" class="flex flex-col">
      @csrf
      <div class="flex items-center m-2">
        <label for="perPage" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
        <select name="perPage" id="perPage" onchange="submitInventoryForm()" class="border border-gray-300 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
          <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
        </select>
        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">entries per page</span>
      </div>
      <div class="overflow-x-auto">
        <table id="inventory-record" class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 hidden md:table-header-group">
            <tr class="text-center">
              <th scope="col" class="px-6 py-3">Accession</th>
              <th scope="col" class="px-6 py-3">Call Number</th>
              <th scope="col" class="px-6 py-3">Title</th>
              <th scope="col" class="px-6 py-3">Author</th>
              <th scope="col" class="px-6 py-3">Remarks</th>
              <th scope="col" class="px-6 py-3">Condition</th>
              <th scope="col" class="px-6 py-3">Action</th>
            </tr>
          </thead>
          <tbody class="text-center">
            @forelse($inventory as $item)
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 block md:table-row mb-4 md:mb-0">
              <td class="px-6 py-4 block md:table-cell text-right md:text-center"><span class="float-left font-bold md:hidden">Accession</span>{{ $item->book->accession }}</td>
              <td class="px-6 py-4 block md:table-cell text-right md:text-center"><span class="float-left font-bold md:hidden">Call Number</span>{{ $item->book->call_number }}</td>
              <td class="px-6 py-4 block md:table-cell text-right md:text-center">
                <span class="float-left font-bold md:hidden">Title</span>
                <div class="inline-block md:block md:max-w-[12rem] lg:max-w-xs break-words md:mx-auto">
                  {{ $item->book->title }}
                </div>
              </td>
              <td class="px-6 py-4 block md:table-cell text-right md:text-center">
                <span class="float-left font-bold md:hidden">Author</span>
                <div class="inline-block md:block md:max-w-[12rem] lg:max-w-xs break-words md:mx-auto">
                  {{ optional($item->book)->author ?? 'N/A' }}
                </div>
              </td>
              <td class="px-6 py-4 block md:table-cell text-right md:text-center">
                <span class="float-left font-bold md:hidden">Remarks</span>
                <select name="remarks[{{ $item->book->accession }}]" id="remarks" class="w-1/2 md:w-full p-2 border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                  @foreach($remarks as $remark)
                  <option value="{{ $remark }}" @if($remark == $item->book->remarks) selected @endif>{{ $remark }}</option>
                  @endforeach
                </select>
              </td>
              <td class="px-6 py-4 block md:table-cell text-right md:text-center">
                <span class="float-left font-bold md:hidden">Condition</span>
                <select name="condition[{{ $item->book->accession }}]" id="condition" class="w-1/2 md:w-full p-2 border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                  @foreach($conditions as $condition)
                  <option value="{{ $condition }}" @if($condition == $item->book->condition_status) selected @endif>{{ $condition }}</option>
                  @endforeach
                </select>
              </td>
              <td class="px-6 py-4 block md:table-cell text-right md:text-center">
                <span class="float-left font-bold md:hidden">Action</span>
                <button type="button" data-modal-target="popup-modal" data-modal-toggle="popup-modal" value="{{ $item->book->accession }}" class="deleteBtn skip-loader focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">Delete</button>
              </td>
            </tr>
            @empty
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
              <td colspan="8" class="text-center py-4" id="no-data">No data found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
        <div id="pagination-links" class="p-4">
          {{ $inventory->withQueryString()->links() }}
        </div>
      </div>
      @if(count($inventory) > 0)
      <button type="submit" formaction="{{ route('inventory.update') }}" formmethod="POST" class="text-white max-w-36 self-center bg-primary-500 hover:bg-primary-700 focus:ring-4 focus:ring-primary-700 font-medium rounded-lg text-sm px-5 py-2.5 my-4 dark:bg-primary-500 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">Save</button>
      @endif
    </form>
  </div>
</div>
<script>
    function submitInventoryForm(url) {
        const form = document.getElementById('inventory-form');
        if (url) {
            form.action = url;
        }
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const paginationContainer = document.getElementById('pagination-links');
        if (paginationContainer) {
            paginationContainer.addEventListener('click', function(e) {
                const target = e.target.closest('a');
                if (target) {
                    e.preventDefault();
                    submitInventoryForm(target.href);
                }
            });
        }
    });
</script>