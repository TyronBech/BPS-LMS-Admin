<div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700">
  <div class="bg-slate-50 dark:bg-slate-900 px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
    <span class="text-sm font-bold text-slate-700 dark:text-slate-300">User Images Import Preview</span>
    <div class="flex items-center gap-4">
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 text-[10px] font-bold rounded-full">
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
          {{ $matchedPaginatedData->total() }} Matched
        </span>
        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 text-[10px] font-bold rounded-full">
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
          {{ $unmatchedPaginatedData->total() }} Unmatched
        </span>
        @if($hasOversized)
        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 text-[10px] font-bold rounded-full">
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
          {{ count($oversized) }} Oversized
        </span>
        @endif
      </div>
    </div>
  </div>

  <div class="p-4 space-y-8">
    {{-- MATCHED IMAGES --}}
    @if($hasMatched)
    <div class="space-y-3">
      <div class="flex items-center gap-3">
        <span class="px-3 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 text-[10px] font-black uppercase tracking-wider rounded-full border border-green-200 dark:border-green-800">
          Matched Images ({{ $matchedPaginatedData->total() }})
        </span>
        <div class="h-px bg-slate-100 dark:bg-slate-700 flex-grow"></div>
      </div>

      <div class="overflow-x-auto rounded border border-slate-100 dark:border-slate-700">
        <table class="w-full text-left border-collapse min-w-[800px]">
          <thead>
            <tr class="bg-primary-600 text-white text-[10px] uppercase tracking-wider font-black">
              <th class="px-3 py-2 border-r border-primary-500 w-[60px]">#</th>
              <th class="px-3 py-2 border-r border-primary-500 w-[80px]">Preview</th>
              <th class="px-3 py-2 border-r border-primary-500">Filename</th>
              <th class="px-3 py-2 border-r border-primary-500">Matched User</th>
              <th class="px-3 py-2 border-r border-primary-500 w-[100px]">User Type</th>
              <th class="px-3 py-2 w-[100px]">File Size</th>
            </tr>
          </thead>
          <tbody class="text-[11px]">
            @php $startIndex = ($matchedPaginatedData->currentPage() - 1) * $matchedPaginatedData->perPage(); @endphp
            @forelse($matchedPaginatedData as $index => $item)
            <tr class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
              <td class="px-3 py-2 text-slate-400 font-mono text-center">{{ $startIndex + $index + 1 }}</td>
              <td class="px-3 py-2">
                @php
                  $imgBuffer = file_get_contents($item['path']);
                  $imgData = base64_encode($imgBuffer);
                  $finfo = new \finfo(FILEINFO_MIME_TYPE);
                  $mimeType = $finfo->buffer($imgBuffer);
                  unset($imgBuffer);
                @endphp
                <img src="data:{{ $mimeType }};base64,{{ $imgData }}" alt="{{ $item['filename'] }}" class="w-10 h-10 rounded-full object-cover border-2 border-green-200 dark:border-green-700">
              </td>
              <td class="px-3 py-2">
                <span class="font-mono text-slate-700 dark:text-slate-300">{{ $item['filename'] }}</span>
              </td>
              <td class="px-3 py-2">
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $item['user_name'] }}</span>
                <span class="block text-[10px] text-slate-400 font-mono">ID: {{ $item['id'] }}</span>
              </td>
              <td class="px-3 py-2 text-center">
                @if($item['user_type'] === 'Student')
                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 text-[10px] font-bold rounded-full">Student</span>
                @else
                <span class="px-2 py-0.5 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 text-[10px] font-bold rounded-full">Employee</span>
                @endif
              </td>
              <td class="px-3 py-2 text-center">
                <span class="text-slate-500 dark:text-slate-400">{{ $item['size_text'] }}</span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="py-8 text-center text-slate-400 italic">No matched images found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="pagination-links mt-2">
        {{ $matchedPaginatedData->appends(request()->except('matched'))->links() }}
      </div>
    </div>
    @endif

    {{-- OVERSIZED IMAGES --}}
    @if($hasOversized)
    <div class="space-y-3">
      <div class="flex items-center gap-3">
        <span class="px-3 py-1 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 text-[10px] font-black uppercase tracking-wider rounded-full border border-amber-200 dark:border-amber-800">
          Oversized Images — Exceeds 5MB ({{ count($oversized) }})
        </span>
        <div class="h-px bg-slate-100 dark:bg-slate-700 flex-grow"></div>
      </div>

      <div class="overflow-x-auto rounded border border-amber-100 dark:border-amber-800">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-amber-600 text-white text-[10px] uppercase tracking-wider font-black">
              <th class="px-3 py-2 border-r border-amber-500 w-[60px]">#</th>
              <th class="px-3 py-2 border-r border-amber-500">Filename</th>
              <th class="px-3 py-2 border-r border-amber-500">ID from Filename</th>
              <th class="px-3 py-2 w-[120px]">File Size</th>
            </tr>
          </thead>
          <tbody class="text-[11px]">
            @foreach($oversized as $index => $item)
            <tr class="border-b border-amber-50 dark:border-amber-900/50 hover:bg-amber-50/50">
              <td class="px-3 py-2 text-slate-400 font-mono text-center">{{ $index + 1 }}</td>
              <td class="px-3 py-2">
                <span class="font-mono text-slate-700 dark:text-slate-300">{{ $item['filename'] }}</span>
              </td>
              <td class="px-3 py-2">
                <span class="font-mono text-slate-500">{{ $item['id'] }}</span>
              </td>
              <td class="px-3 py-2 text-center">
                <span class="font-semibold text-red-600 dark:text-red-400">{{ $item['size_text'] }}</span>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <p class="text-xs text-amber-600 dark:text-amber-400 italic">
        These images exceed the 5MB limit and will be skipped during import. Please reduce their file size and try again.
      </p>
    </div>
    @endif

    {{-- UNMATCHED IMAGES --}}
    @if($hasUnmatched)
    <div class="space-y-3">
      <div class="flex items-center gap-3">
        <span class="px-3 py-1 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 text-[10px] font-black uppercase tracking-wider rounded-full border border-red-200 dark:border-red-800">
          Unmatched Images ({{ $unmatchedPaginatedData->total() }})
        </span>
        <div class="h-px bg-slate-100 dark:bg-slate-700 flex-grow"></div>
      </div>

      <div class="overflow-x-auto rounded border border-red-100 dark:border-red-800">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-700 text-white text-[10px] uppercase tracking-wider font-black">
              <th class="px-3 py-2 border-r border-slate-600 w-[60px]">#</th>
              <th class="px-3 py-2 border-r border-slate-600 w-[80px]">Preview</th>
              <th class="px-3 py-2 border-r border-slate-600">Filename</th>
              <th class="px-3 py-2 border-r border-slate-600">ID from Filename</th>
              <th class="px-3 py-2 border-r border-slate-600 w-[100px]">File Size</th>
              <th class="px-3 py-2 w-[200px]">Status</th>
            </tr>
          </thead>
          <tbody class="text-[11px]">
            @php $unmatchedStartIndex = ($unmatchedPaginatedData->currentPage() - 1) * $unmatchedPaginatedData->perPage(); @endphp
            @forelse($unmatchedPaginatedData as $index => $item)
            <tr class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-red-50/30">
              <td class="px-3 py-2 text-slate-400 font-mono text-center">{{ $unmatchedStartIndex + $index + 1 }}</td>
              <td class="px-3 py-2">
                @php
                  $imgBuffer = file_get_contents($item['path']);
                  $imgData = base64_encode($imgBuffer);
                  $finfo = new \finfo(FILEINFO_MIME_TYPE);
                  $mimeType = $finfo->buffer($imgBuffer);
                  unset($imgBuffer);
                @endphp
                <img src="data:{{ $mimeType }};base64,{{ $imgData }}" alt="{{ $item['filename'] }}" class="w-10 h-10 rounded-full object-cover border-2 border-red-200 dark:border-red-700 opacity-60">
              </td>
              <td class="px-3 py-2">
                <span class="font-mono text-slate-700 dark:text-slate-300">{{ $item['filename'] }}</span>
              </td>
              <td class="px-3 py-2">
                <span class="font-mono text-slate-500">{{ $item['id'] }}</span>
              </td>
              <td class="px-3 py-2 text-center">
                <span class="text-slate-500 dark:text-slate-400">{{ $item['size_text'] }}</span>
              </td>
              <td class="px-3 py-2">
                <span class="inline-flex items-center gap-1 text-red-600 dark:text-red-400 text-[10px] font-semibold">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                  No student or employee found with this ID
                </span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="py-8 text-center text-slate-400 italic">No unmatched images.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="pagination-links mt-2">
        {{ $unmatchedPaginatedData->appends(request()->except('unmatched'))->links() }}
      </div>
      <p class="text-xs text-red-500 dark:text-red-400 italic">
        These images will be skipped during import because their filenames do not match any existing Student ID or Employee ID.
      </p>
    </div>
    @endif
  </div>
</div>
