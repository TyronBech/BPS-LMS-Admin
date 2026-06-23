<div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700">
    <form id="import-form" action="{{ route('import.upload-materials') }}" method="POST" class="w-full">
        @csrf
        <div class="bg-slate-50 dark:bg-slate-900 px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Spreadsheet Content Preview</span>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 text-[10px] font-bold rounded-full">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ $newCount }} New
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 text-[10px] font-bold rounded-full">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 00-1 1v12a1 1 0 001 1h10a1 1 0 001-1V3a1 1 0 00-1-1H4zm3 10a1 1 0 11-2 0 1 1 0 012 0zm4-3a1 1 0 100-2H5a1 1 0 000 2h6z" clip-rule="evenodd"/></svg>
                        {{ $existingCount }} Existing
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <label for="perPage" class="text-[11px] font-bold text-slate-500 uppercase">Show</label>
                    <select name="perPage" id="perPage" onchange="submitImportForm()" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs rounded p-1 focus:ring-primary-500">
                        <option value="10" @if(isset($perPage) && $perPage==10) selected @endif>10</option>
                        <option value="25" @if(isset($perPage) && $perPage==25) selected @endif>25</option>
                        <option value="50" @if(isset($perPage) && $perPage==50) selected @endif>50</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="p-4 space-y-8">
            {{-- NEW MATERIALS --}}
            @if($new)
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 text-[10px] font-black uppercase tracking-wider rounded-full border border-green-200 dark:border-green-800">
                        New Materials ({{ $newPaginatedData->total() }})
                    </span>
                    <div class="h-px bg-slate-100 dark:bg-slate-700 flex-grow"></div>
                </div>

                <div class="overflow-x-auto rounded border border-slate-100 dark:border-slate-700">
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
                            @forelse($newPaginatedData as $index => $item)
                            <tr class="border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-900/50">
                                <!-- Identity -->
                                <td class="p-2 space-y-1 align-top">
                                    <input type="text" name="materials[{{ $index }}][accession]" value="{{ $item['accession'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7 font-mono" placeholder="Accession">
                                    <textarea name="materials[{{ $index }}][title]" rows="2" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 leading-tight" placeholder="Title">{{ $item['title'] ?? '' }}</textarea>
                                    <div>
                                        <label class="text-[9px] font-bold text-slate-400">Parallel Title</label>
                                        <input type="text" name="materials[{{ $index }}][parallel_title]" value="{{ $item['parallel_title'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="Parallel Title">
                                    </div>
                                </td>

                                <!-- Authors Group -->
                                <td class="p-2 bg-secondary-50/20 dark:bg-slate-900/20 align-top">
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <div class="col-span-2">
                                            <label class="text-[9px] font-bold text-slate-400">Main Author</label>
                                            <input type="text" name="materials[{{ $index }}][authors][Main author]" value="{{ $item['authors']['Main author'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                        <div>
                                            <label class="text-[9px] font-bold text-slate-400">Corporate</label>
                                            <input type="text" name="materials[{{ $index }}][authors][Corporate author]" value="{{ $item['authors']['Corporate author'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                        <div>
                                            <label class="text-[9px] font-bold text-slate-400">Contributors</label>
                                            <input type="text" name="materials[{{ $index }}][authors][Contributors]" value="{{ $item['authors']['Contributors'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                        <div class="col-span-2">
                                            <label class="text-[9px] font-bold text-slate-400">Added Authors</label>
                                            <input type="text" name="materials[{{ $index }}][authors][Added authors]" value="{{ $item['authors']['Added authors'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                    </div>
                                </td>

                                <!-- Description Group -->
                                <td class="p-2 bg-tertiary-50/20 dark:bg-slate-900/10 align-top">
                                    <div class="grid grid-cols-3 gap-1.5">
                                        <div class="col-span-2">
                                            <label class="text-[9px] font-bold text-slate-400">Phys. Description</label>
                                            <input type="text" name="materials[{{ $index }}][description][Description]" value="{{ $item['description']['Description'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                        <div>
                                            <label class="text-[9px] font-bold text-slate-400">Extent</label>
                                            <input type="text" name="materials[{{ $index }}][description][Extent]" value="{{ $item['description']['Extent'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                        <div class="col-span-3">
                                            <label class="text-[9px] font-bold text-slate-400">Notes & Abstract</label>
                                            <div class="flex gap-1">
                                                <input type="text" name="materials[{{ $index }}][description][Content notes]" value="{{ $item['description']['Content notes'] ?? '' }}" placeholder="Notes" class="w-1/2 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                                <input type="text" name="materials[{{ $index }}][description][Abstract]" value="{{ $item['description']['Abstract'] ?? '' }}" placeholder="Abstract" class="w-1/2 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                            </div>
                                        </div>
                                        <div class="col-span-3">
                                            <div class="flex gap-1">
                                                <div class="w-1/2">
                                                    <label class="text-[9px] font-bold text-slate-400">Reviews</label>
                                                    <input type="text" name="materials[{{ $index }}][description][Reviews]" value="{{ $item['description']['Reviews'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                                </div>
                                                <div class="w-1/2">
                                                    <label class="text-[9px] font-bold text-slate-400">Acc Material</label>
                                                    <input type="text" name="materials[{{ $index }}][description][Acc Material]" value="{{ $item['description']['Acc Material'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-span-3">
                                            <label class="text-[9px] font-bold text-slate-400">Series</label>
                                            <input type="text" name="materials[{{ $index }}][description][Series]" value="{{ $item['description']['Series'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="Series">
                                        </div>
                                    </div>
                                </td>

                                <!-- Identifiers -->
                                <td class="p-2 space-y-1.5 align-top">
                                    <div>
                                        <label class="text-[9px] font-bold text-slate-400">ISBN</label>
                                        <input type="text" name="materials[{{ $index }}][isbn]" value="{{ $item['isbn'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                    </div>
                                    <div>
                                        <label class="text-[9px] font-bold text-slate-400">Call No.</label>
                                        <input type="text" name="materials[{{ $index }}][call_number]" value="{{ $item['call_number'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                    </div>
                                    <div>
                                        <label class="text-[9px] font-bold text-slate-400">Edition</label>
                                        <input type="text" name="materials[{{ $index }}][edition]" value="{{ $item['edition'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                    </div>
                                </td>

                                <!-- System -->
                                <td class="p-2 space-y-1.5 align-top">
                                    <div class="flex gap-1">
                                        <div class="w-1/2">
                                            <label class="text-[9px] font-bold text-slate-400">Type</label>
                                            <input type="text" name="materials[{{ $index }}][book_type]" value="{{ $item['book_type'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                        <div class="w-1/2">
                                            <label class="text-[9px] font-bold text-slate-400">Category</label>
                                            <input type="text" name="materials[{{ $index }}][category]" value="{{ $item['category'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                    </div>
                                    <div class="flex gap-1">
                                        <div class="w-1/2">
                                            <label class="text-[9px] font-bold text-slate-400">Location</label>
                                            <input type="text" name="materials[{{ $index }}][location]" value="{{ $item['location'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                        <div class="w-1/2">
                                            <label class="text-[9px] font-bold text-slate-400">Languages</label>
                                            <input type="text" name="materials[{{ $index }}][languages]" value="{{ $item['languages'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="e.g. English">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-[9px] font-bold text-slate-400">Subjects</label>
                                        <input type="text" name="materials[{{ $index }}][subject]" value="{{ $item['subject'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="e.g. Science;Physics or Science, Physics">
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400 italic">No new records to preview.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 text-xs">
                    {{ $newPaginatedData->appends(request()->except('new'))->links() }}
                </div>
            </div>
            @endif

            {{-- EXISTING MATERIALS --}}
            @if($existing)
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 text-[10px] font-black uppercase tracking-wider rounded-full border border-amber-200 dark:border-amber-800">
                        Existing Materials ({{ $existingPaginatedData->total() }})
                    </span>
                    <div class="h-px bg-slate-100 dark:bg-slate-700 flex-grow"></div>
                </div>

                <div class="overflow-x-auto rounded border border-slate-100 dark:border-slate-700">
                    <table class="w-full text-left border-collapse min-w-[1200px]">
                        <thead>
                            <tr class="bg-slate-700 text-white text-[10px] uppercase tracking-wider font-black">
                                <th class="px-3 py-2 border-r border-slate-600 min-w-[120px]">Accession & Title</th>
                                <th class="px-3 py-2 border-r border-slate-600 min-w-[300px]">Authors & Contributors</th>
                                <th class="px-3 py-2 border-r border-slate-600 min-w-[400px]">Material Description</th>
                                <th class="px-3 py-2 border-r border-slate-600 min-w-[150px]">IDs & Pub</th>
                                <th class="px-3 py-2 min-w-[200px]">System Info</th>
                            </tr>
                        </thead>
                        <tbody class="text-[11px]">
                            @forelse($existingPaginatedData as $index => $item)
                            <tr class="border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-900/50">
                                <!-- Identity -->
                                <td class="p-2 space-y-1 align-top">
                                    <input type="text" name="materials[{{ $index }}][accession]" value="{{ $item['accession'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7 font-mono" placeholder="Accession">
                                    <textarea name="materials[{{ $index }}][title]" rows="2" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 leading-tight" placeholder="Title">{{ $item['title'] ?? '' }}</textarea>
                                    <div>
                                        <label class="text-[9px] font-bold text-slate-400">Parallel Title</label>
                                        <input type="text" name="materials[{{ $index }}][parallel_title]" value="{{ $item['parallel_title'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="Parallel Title">
                                    </div>
                                </td>

                                <!-- Authors Group -->
                                <td class="p-2 bg-secondary-50/20 dark:bg-slate-900/20 align-top">
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <div class="col-span-2">
                                            <label class="text-[9px] font-bold text-slate-400">Main Author</label>
                                            <input type="text" name="materials[{{ $index }}][authors][Main author]" value="{{ $item['authors']['Main author'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                        <div>
                                            <label class="text-[9px] font-bold text-slate-400">Corporate</label>
                                            <input type="text" name="materials[{{ $index }}][authors][Corporate author]" value="{{ $item['authors']['Corporate author'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                        <div>
                                            <label class="text-[9px] font-bold text-slate-400">Contributors</label>
                                            <input type="text" name="materials[{{ $index }}][authors][Contributors]" value="{{ $item['authors']['Contributors'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                        <div class="col-span-2">
                                            <label class="text-[9px] font-bold text-slate-400">Added Authors</label>
                                            <input type="text" name="materials[{{ $index }}][authors][Added authors]" value="{{ $item['authors']['Added authors'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                    </div>
                                </td>

                                <!-- Description Group -->
                                <td class="p-2 bg-tertiary-50/20 dark:bg-slate-900/10 align-top">
                                    <div class="grid grid-cols-3 gap-1.5">
                                        <div class="col-span-2">
                                            <label class="text-[9px] font-bold text-slate-400">Phys. Description</label>
                                            <input type="text" name="materials[{{ $index }}][description][Description]" value="{{ $item['description']['Description'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                        <div>
                                            <label class="text-[9px] font-bold text-slate-400">Extent</label>
                                            <input type="text" name="materials[{{ $index }}][description][Extent]" value="{{ $item['description']['Extent'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                        <div class="col-span-3">
                                            <label class="text-[9px] font-bold text-slate-400">Notes & Abstract</label>
                                            <div class="flex gap-1">
                                                <input type="text" name="materials[{{ $index }}][description][Content notes]" value="{{ $item['description']['Content notes'] ?? '' }}" placeholder="Notes" class="w-1/2 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                                <input type="text" name="materials[{{ $index }}][description][Abstract]" value="{{ $item['description']['Abstract'] ?? '' }}" placeholder="Abstract" class="w-1/2 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                            </div>
                                        </div>
                                        <div class="col-span-3">
                                            <div class="flex gap-1">
                                                <div class="w-1/2">
                                                    <label class="text-[9px] font-bold text-slate-400">Reviews</label>
                                                    <input type="text" name="materials[{{ $index }}][description][Reviews]" value="{{ $item['description']['Reviews'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                                </div>
                                                <div class="w-1/2">
                                                    <label class="text-[9px] font-bold text-slate-400">Acc Material</label>
                                                    <input type="text" name="materials[{{ $index }}][description][Acc Material]" value="{{ $item['description']['Acc Material'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-span-3">
                                            <label class="text-[9px] font-bold text-slate-400">Series</label>
                                            <input type="text" name="materials[{{ $index }}][description][Series]" value="{{ $item['description']['Series'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="Series">
                                        </div>
                                    </div>
                                </td>

                                <!-- Identifiers -->
                                <td class="p-2 space-y-1.5 align-top">
                                    <div>
                                        <label class="text-[9px] font-bold text-slate-400">ISBN</label>
                                        <input type="text" name="materials[{{ $index }}][isbn]" value="{{ $item['isbn'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                    </div>
                                    <div>
                                        <label class="text-[9px] font-bold text-slate-400">Call No.</label>
                                        <input type="text" name="materials[{{ $index }}][call_number]" value="{{ $item['call_number'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                    </div>
                                    <div>
                                        <label class="text-[9px] font-bold text-slate-400">Edition</label>
                                        <input type="text" name="materials[{{ $index }}][edition]" value="{{ $item['edition'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                    </div>
                                </td>

                                <!-- System -->
                                <td class="p-2 space-y-1.5 align-top">
                                    <div class="flex gap-1">
                                        <div class="w-1/2">
                                            <label class="text-[9px] font-bold text-slate-400">Type</label>
                                            <input type="text" name="materials[{{ $index }}][book_type]" value="{{ $item['book_type'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                        <div class="w-1/2">
                                            <label class="text-[9px] font-bold text-slate-400">Category</label>
                                            <input type="text" name="materials[{{ $index }}][category]" value="{{ $item['category'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                    </div>
                                    <div class="flex gap-1">
                                        <div class="w-1/2">
                                            <label class="text-[9px] font-bold text-slate-400">Location</label>
                                            <input type="text" name="materials[{{ $index }}][location]" value="{{ $item['location'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                                        </div>
                                        <div class="w-1/2">
                                            <label class="text-[9px] font-bold text-slate-400">Languages</label>
                                            <input type="text" name="materials[{{ $index }}][languages]" value="{{ $item['languages'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="e.g. English">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-[9px] font-bold text-slate-400">Subjects</label>
                                        <input type="text" name="materials[{{ $index }}][subject]" value="{{ $item['subject'] ?? '' }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="e.g. Science;Physics or Science, Physics">
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-400 italic">No existing records to preview.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 text-xs">
                    {{ $existingPaginatedData->appends(request()->except('existing'))->links() }}
                </div>
            </div>
            @endif
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
        document.querySelectorAll('.pagination a, .pagination-links a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                submitImportForm(this.href);
            });
        });
    });
</script>
