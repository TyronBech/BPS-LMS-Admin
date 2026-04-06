<div class="container mx-auto mb-4 mt-12 px-4">
    <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
        <div class="flex flex-col gap-4 border-b border-gray-200 p-4 dark:border-gray-700 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold dark:text-white">
                    @if($inventoryActive)
                    Scanned Books
                    @else
                    Latest Saved Inventory
                    @endif
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if($inventoryActive)
                    Scanned books appear here immediately. Use Save to timestamp the scanned books and keep editing remarks or condition as needed.
                    @else
                    This table shows the saved books from the most recent completed inventory cycle.
                    @endif
                </p>
            </div>

            <form action="{{ route('inventory.dashboard') }}" method="GET" class="flex items-center gap-2">
                <label for="perPage" class="text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
                <select name="perPage" id="perPage" onchange="this.form.submit()" class="rounded-lg border border-gray-300 p-2 text-xs focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                </select>
                <span class="text-sm text-gray-600 dark:text-gray-400">entries per page</span>
            </form>
        </div>

        <form id="inventory-form" action="{{ route('inventory.update') }}" method="POST" class="flex flex-col">
            @csrf
            <input type="hidden" name="perPage" value="{{ $perPage }}">

            <div class="overflow-x-auto">
                <table id="inventory-record" class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="hidden bg-gray-50 text-center text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400 md:table-header-group">
                        <tr>
                            <th scope="col" class="px-6 py-3">Accession</th>
                            <th scope="col" class="px-6 py-3">Author</th>
                            <th scope="col" class="px-6 py-3">Title</th>
                            <th scope="col" class="px-6 py-3">Call Number</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Remarks</th>
                            <th scope="col" class="px-6 py-3">Condition</th>
                            @if($inventoryActive)
                            <th scope="col" class="px-6 py-3">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse($inventory as $item)
                        <tr class="mb-4 block border-b bg-white dark:border-gray-700 dark:bg-gray-800 md:mb-0 md:table-row">
                            <td class="block px-6 py-4 text-right md:table-cell md:text-center">
                                <span class="float-left font-bold md:hidden">Accession</span>{{ $item->book->accession }}
                            </td>
                            <td class="block px-6 py-4 text-right md:table-cell md:text-center">
                                <span class="float-left font-bold md:hidden">Author</span>
                                <div class="inline-block break-words md:block md:max-w-[12rem] md:mx-auto lg:max-w-xs">
                                    {{ $item->book->author ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="block px-6 py-4 text-right md:table-cell md:text-center">
                                <span class="float-left font-bold md:hidden">Title</span>
                                <div class="inline-block break-words md:block md:max-w-[12rem] md:mx-auto lg:max-w-xs">
                                    {{ $item->book->title }}
                                </div>
                            </td>
                            <td class="block px-6 py-4 text-right md:table-cell md:text-center">
                                <span class="float-left font-bold md:hidden">Call Number</span>{{ $item->book->call_number ?? 'N/A' }}
                            </td>
                            <td class="block px-6 py-4 text-right md:table-cell md:text-center">
                                <span class="float-left font-bold md:hidden">Status</span>
                                @if($item->checked_at)
                                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                    Saved {{ $item->checked_at->format('M d, Y h:i A') }}
                                </span>
                                @else
                                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                    Pending Save
                                </span>
                                @endif
                            </td>
                            <td class="block px-6 py-4 text-right md:table-cell md:text-center">
                                <span class="float-left font-bold md:hidden">Remarks</span>
                                @if($inventoryActive)
                                <select name="remarks[{{ $item->book->id }}]" class="w-1/2 rounded-lg border border-gray-200 p-2 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 md:w-full" @disabled(!$inventoryActive)>
                                    @foreach($remarks as $remark)
                                    <option value="{{ $remark }}" @selected($remark=="On Shelf")>{{ $remark }}</option>
                                    @endforeach
                                </select>
                                @else
                                <p class="inline-block w-1/2 text-gray-700 dark:text-gray-200 md:block md:w-full">{{ $item->book->remarks ?? 'N/A' }}</p>
                                @endif
                            </td>
                            <td class="block px-6 py-4 text-right md:table-cell md:text-center">
                                <span class="float-left font-bold md:hidden">Condition</span>
                                @if($inventoryActive)
                                <select name="condition[{{ $item->book->id }}]" class="w-1/2 rounded-lg border border-gray-200 p-2 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 md:w-full" @disabled(!$inventoryActive)>
                                    @foreach($conditions as $condition)
                                    <option value="{{ $condition }}" @selected($condition==old("condition.{$item->book->id}", $item->book->condition_status))>{{ $condition }}</option>
                                    @endforeach
                                </select>
                                @else
                                <p class="inline-block w-1/2 text-gray-700 dark:text-gray-200 md:block md:w-full">{{ $item->book->condition_status ?? 'N/A' }}</p>
                                @endif
                            </td>
                            @if($inventoryActive)
                            <td class="block px-6 py-4 text-right md:table-cell md:text-center">
                                <span class="float-left font-bold md:hidden">Action</span>
                                <button type="button" data-modal-target="reset-modal" data-modal-toggle="reset-modal" value="{{ $item->book->accession }}" class="deleteBtn skip-loader rounded-lg bg-red-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">
                                    Reset
                                </button>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr class="border-b bg-white dark:border-gray-700 dark:bg-gray-800">
                            <td colspan="{{ $inventoryActive ? 8 : 7 }}" class="py-4 text-center" id="no-data">
                                @if($inventoryActive)
                                No scanned books yet.
                                @else
                                No saved inventory data found.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $inventory->withQueryString()->links() }}
            </div>

            @if($inventoryActive && $stats['scanned'] > 0)
            <button type="submit" class="my-4 max-w-40 self-center rounded-lg bg-primary-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
                Save
            </button>
            @endif
        </form>
    </div>
</div>
