<div class="w-full border-collapse border-2 border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-slate-800 dark:border-slate-700">
    <form id="import-form" action="{{ route('import.upload-books') }}" method="POST" class="w-full">
        @csrf
        <h2 class="text-center mb-2 mt-4 font-semibold text-2xl">Spreadsheet Contents</h2>
        <div class="p-4">
            <div class="flex items-center">
                <label for="perPage" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
                <select name="perPage" id="perPage" onchange="submitImportForm()" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-500 focus:border-primary-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    <option value="10" @if(isset($perPage) && $perPage==10) selected @endif>10</option>
                    <option value="25" @if(isset($perPage) && $perPage==25) selected @endif>25</option>
                    <option value="50" @if(isset($perPage) && $perPage==50) selected @endif>50</option>
                </select>
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">entries per page</span>
            </div>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="min-w-full table-auto bg-white dark:bg-gray-800">
                <thead class="bg-primary-400 font-bold text-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-left">Accession</th>
                        <th class="px-6 py-3 text-left">Author</th>
                        <th class="px-6 py-3 text-left">Title</th>
                        <th class="px-6 py-3 text-left">Publication</th>
                        <th class="px-6 py-3 text-left">Publisher</th>
                        <th class="px-6 py-3 text-left">Call Number</th>
                        <th class="px-6 py-3 text-left">ISBN</th>
                        <th class="px-6 py-3 text-left">Subject</th>
                        <th class="px-6 py-3 text-left">Description</th>
                        <th class="px-6 py-3 text-left">Copyrights</th>
                        <th class="px-6 py-3 text-left">Book Type</th>
                        <th class="px-6 py-3 text-left">Category</th>
                        <th class="px-6 py-3 text-left">URL</th>
                    </tr>
                </thead>
                <tbody class="text-left">
                    @php
                    $startIndex = ($data->currentPage() - 1) * $data->perPage();
                    @endphp
                    @forelse($data as $index => $item)
                    @php
                    $itemIndex = $startIndex + $index;
                    @endphp
                    <tr>
                        <td class="px-2 py-2"><input type="text" name="books[{{ $itemIndex }}][accession]" value="{{ $item['accession'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
                        <td class="px-2 py-2"><input type="text" name="books[{{ $itemIndex }}][author]" value="{{ $item['author'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[200px]"></td>
                        <td class="px-2 py-2"><input type="text" name="books[{{ $itemIndex }}][title]" value="{{ $item['title'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[250px]"></td>
                        <td class="px-2 py-2"><input type="text" name="books[{{ $itemIndex }}][place_of_publication]" value="{{ $item['place_of_publication'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[200px]"></td>
                        <td class="px-2 py-2"><input type="text" name="books[{{ $itemIndex }}][publisher]" value="{{ $item['publisher'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[200px]"></td>
                        <td class="px-2 py-2"><input type="text" name="books[{{ $itemIndex }}][call_number]" value="{{ $item['call_number'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
                        <td class="px-2 py-2"><input type="text" name="books[{{ $itemIndex }}][isbn]" value="{{ $item['isbn'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
                        <td class="px-2 py-2"><input type="text" name="books[{{ $itemIndex }}][subject]" value="{{ $item['subject'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[200px]"></td>
                        <td class="px-2 py-2"><input type="text" name="books[{{ $itemIndex }}][description]" value="{{ $item['description'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[300px]"></td>
                        <td class="px-2 py-2"><input type="text" name="books[{{ $itemIndex }}][copyrights]" value="{{ $item['copyrights'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[100px]"></td>
                        <td class="px-2 py-2"><input type="text" name="books[{{ $itemIndex }}][book_type]" value="{{ $item['book_type'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[100px]"></td>
                        <td class="px-2 py-2"><input type="text" name="books[{{ $itemIndex }}][category]" value="{{ $item['category'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[150px]"></td>
                        <td class="px-2 py-2"><input type="text" name="books[{{ $itemIndex }}][digital_copy_url]" value="{{ $item['digital_copy_url'] ?? '' }}" class="p-2 border-0 w-full bg-white dark:bg-gray-800 focus:border-b-2 hover:border-b-2 min-w-[250px]"></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="text-center py-4">No data found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div id="pagination-links" class="mt-4">
                {{ $data->appends(['perPage' => $perPage])->links() }}
            </div>
        </div>
    </form>
</div>

<script>
    function submitImportForm(url) {
        const form = document.getElementById('import-form');
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
                    submitImportForm(target.href);
                }
            });
        }
    });
</script>