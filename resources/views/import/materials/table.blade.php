<div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700">
    <form id="import-form" action="{{ route('import.upload-materials') }}" method="POST" class="w-full">
        @csrf
        <div class="bg-slate-50 dark:bg-slate-900 px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Spreadsheet Content Preview</span>
            <div class="flex items-center gap-2">
                <label for="perPage" class="text-[11px] font-bold text-slate-500 uppercase">Show</label>
                <select name="perPage" id="perPage" onchange="submitImportForm()" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs rounded p-1 focus:ring-primary-500">
                    <option value="10" @if(isset($perPage) && $perPage==10) selected @endif>10</option>
                    <option value="25" @if(isset($perPage) && $perPage==25) selected @endif>25</option>
                    <option value="50" @if(isset($perPage) && $perPage==50) selected @endif>50</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1200px]">
                <thead>
                    <tr class="bg-primary-600 text-white text-[10px] uppercase tracking-wider font-black">
                        <th class="px-3 py-2 border-r border-primary-500 min-w-[120px]">Accession & Title</th>
                        <th class="px-3 py-2 border-r border-primary-500 min-w-[300px]">Authors & Contributors</th>
                        <th class="px-3 py-2 border-r border-primary-500 min-w-[400px]">Material Description</th>
                        <th class="px-3 py-2 border-r border-primary-500 min-w-[150px]">IDs & Pub</th>
                        <th class="px-3 py-2 min-w-[200px]">System Info</th>
                    </tr>
                </thead>
                <tbody class="text-[11px]">
                    @php $startIndex = ($data->currentPage() - 1) * $data->perPage(); @endphp
                    @forelse($data as $index => $item)
                    @php $itemIndex = $startIndex + $index; @endphp
                    <tr class="border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-900/50">
                        <!-- Identity -->
                        <td class="p-2 space-y-1 align-top">
                            <input type="text" name="materials[{{ $itemIndex }}][accession]" value="{{ $item['accession'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7 font-mono" placeholder="Accession">
                            <textarea name="materials[{{ $itemIndex }}][title]" rows="2" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 leading-tight" placeholder="Title">{{ $item['title'] ?? '' }}</textarea>
                        </td>

                        <!-- Authors Group -->
                        <td class="p-2 bg-secondary-50/20 dark:bg-slate-900/20 align-top">
                            <div class="grid grid-cols-2 gap-1.5">
                                <div class="col-span-2">
                                    <label class="text-[9px] font-bold text-slate-400">Main Author</label>
                                    <input type="text" name="materials[{{ $itemIndex }}][authors][Main author]" value="{{ $item['authors']['Main author'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                </div>
                                <div>
                                    <label class="text-[9px] font-bold text-slate-400">Corporate</label>
                                    <input type="text" name="materials[{{ $itemIndex }}][authors][Corporate author]" value="{{ $item['authors']['Corporate author'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                </div>
                                <div>
                                    <label class="text-[9px] font-bold text-slate-400">Contributors</label>
                                    <input type="text" name="materials[{{ $itemIndex }}][authors][Contributors]" value="{{ $item['authors']['Contributors'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                </div>
                                <div class="col-span-2">
                                    <label class="text-[9px] font-bold text-slate-400">Added Authors</label>
                                    <input type="text" name="materials[{{ $itemIndex }}][authors][Added authors]" value="{{ $item['authors']['Added authors'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                </div>
                            </div>
                        </td>

                        <!-- Description Group -->
                        <td class="p-2 bg-tertiary-50/20 dark:bg-slate-900/10 align-top">
                            <div class="grid grid-cols-3 gap-1.5">
                                <div class="col-span-2">
                                    <label class="text-[9px] font-bold text-slate-400">Phys. Description</label>
                                    <input type="text" name="materials[{{ $itemIndex }}][description][Description]" value="{{ $item['description']['Description'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                </div>
                                <div>
                                    <label class="text-[9px] font-bold text-slate-400">Extent</label>
                                    <input type="text" name="materials[{{ $itemIndex }}][description][Extent]" value="{{ $item['description']['Extent'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                </div>
                                <div class="col-span-3">
                                    <label class="text-[9px] font-bold text-slate-400">Notes & Abstract</label>
                                    <div class="flex gap-1">
                                        <input type="text" name="materials[{{ $itemIndex }}][description][Content notes]" value="{{ $item['description']['Content notes'] ?? '' }}" placeholder="Notes" class="w-1/2 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        <input type="text" name="materials[{{ $itemIndex }}][description][Abstract]" value="{{ $item['description']['Abstract'] ?? '' }}" placeholder="Abstract" class="w-1/2 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                    </div>
                                </div>
                                <div class="col-span-3">
                                    <div class="flex gap-1">
                                        <div class="w-1/2">
                                            <label class="text-[9px] font-bold text-slate-400">Reviews</label>
                                            <input type="text" name="materials[{{ $itemIndex }}][description][Reviews]" value="{{ $item['description']['Reviews'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                        <div class="w-1/2">
                                            <label class="text-[9px] font-bold text-slate-400">Acc Material</label>
                                            <input type="text" name="materials[{{ $itemIndex }}][description][Acc Material]" value="{{ $item['description']['Acc Material'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Identifiers -->
                        <td class="p-2 space-y-1.5 align-top">
                            <div>
                                <label class="text-[9px] font-bold text-slate-400">ISBN</label>
                                <input type="text" name="materials[{{ $itemIndex }}][isbn]" value="{{ $item['isbn'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-slate-400">Call No.</label>
                                <input type="text" name="materials[{{ $itemIndex }}][call_number]" value="{{ $item['call_number'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-slate-400">Edition</label>
                                <input type="text" name="materials[{{ $itemIndex }}][edition]" value="{{ $item['edition'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                            </div>
                        </td>

                        <!-- System -->
                        <td class="p-2 space-y-1.5 align-top">
                            <div class="flex gap-1">
                                <div class="w-1/2">
                                    <label class="text-[9px] font-bold text-slate-400">Type</label>
                                    <input type="text" name="materials[{{ $itemIndex }}][book_type]" value="{{ $item['book_type'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                </div>
                                <div class="w-1/2">
                                    <label class="text-[9px] font-bold text-slate-400">Category</label>
                                    <input type="text" name="materials[{{ $itemIndex }}][category]" value="{{ $item['category'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <div class="w-1/2">
                                    <label class="text-[9px] font-bold text-slate-400">Location</label>
                                    <input type="text" name="materials[{{ $itemIndex }}][location]" value="{{ $item['location'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                </div>
                                <div class="w-1/2">
                                    <label class="text-[9px] font-bold text-slate-400">Languages</label>
                                    <input type="text" name="materials[{{ $itemIndex }}][languages]" value="{{ $item['languages'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="e.g. English">
                                </div>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold text-slate-400">Subjects</label>
                                <input type="text" name="materials[{{ $itemIndex }}][subject]" value="{{ $item['subject'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="e.g. Science;Physics or Science, Physics">
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400 italic">No records to preview.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 text-xs">
            {{ $data->appends(['perPage' => $perPage])->links() }}
        </div>
    </form>
</div>

<script>
    function submitImportForm(url) {
        const form = document.getElementById('import-form');
        if (url) form.action = url;
        form.submit();
    }
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                submitImportForm(this.href);
            });
        });
    });
</script>
