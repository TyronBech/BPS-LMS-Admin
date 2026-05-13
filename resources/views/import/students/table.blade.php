<div class="bg-white dark:bg-slate-800 shadow-lg rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700">
  <form id="import-form" action="{{ route('import.upload-students') }}" method="POST" class="w-full">
    @csrf
    <div class="bg-slate-50 dark:bg-slate-900 px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
      <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Student Import Preview</span>
      <div class="flex items-center gap-2">
        <label for="perPage" class="text-[11px] font-bold text-slate-500 uppercase">Show</label>
        <select name="perPage" id="perPage" onchange="submitImportForm()" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs rounded p-1 focus:ring-primary-500">
          <option value="10" @if(isset($perPage) && $perPage==10) selected @endif>10</option>
          <option value="25" @if(isset($perPage) && $perPage==25) selected @endif>25</option>
          <option value="50" @if(isset($perPage) && $perPage==50) selected @endif>50</option>
        </select>
      </div>
    </div>

    <div class="p-4 space-y-8">
      @if($new)
      <div class="space-y-3">
        <div class="flex items-center gap-3">
          <span class="px-3 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 text-[10px] font-black uppercase tracking-wider rounded-full border border-green-200 dark:border-green-800">
            New Students ({{ $newPaginatedData->total() }})
          </span>
          <div class="h-px bg-slate-100 dark:bg-slate-700 flex-grow"></div>
        </div>

        <div class="overflow-x-auto rounded border border-slate-100 dark:border-slate-700">
          <table class="w-full text-left border-collapse min-w-[1000px]">
            <thead>
              <tr class="bg-primary-600 text-white text-[10px] uppercase tracking-wider font-black">
                <th class="px-3 py-2 border-r border-primary-500 w-[150px]">RFID & ID</th>
                <th class="px-3 py-2 border-r border-primary-500">Full Name Details</th>
                <th class="px-3 py-2 border-r border-primary-500 w-[100px]">Gender</th>
                <th class="px-3 py-2 border-r border-primary-500">Email Address</th>
                <th class="px-3 py-2 w-[180px]">Academic Info</th>
              </tr>
            </thead>
            <tbody class="text-[11px]">
              @php $startIndex = ($newPaginatedData->currentPage() - 1) * $newPaginatedData->perPage(); @endphp
              @forelse($newPaginatedData as $index => $item)
              @php $itemIndex = $startIndex + $index; @endphp
              <tr class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                <td class="p-2 space-y-1 bg-slate-50/30 dark:bg-slate-900/10">
                  <input type="text" name="new_students[{{ $itemIndex }}][rfid]" value="{{ $item['rfid'] }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[10px] p-1 h-6 font-mono" placeholder="RFID">
                  <input type="text" name="new_students[{{ $itemIndex }}][id_number]" value="{{ $item['id_number'] }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[10px] p-1 h-6 font-mono" placeholder="ID Number">
                </td>
                <td class="p-2">
                  <div class="grid grid-cols-4 gap-1">
                    <input type="text" name="new_students[{{ $itemIndex }}][last_name]" value="{{ $item['last_name'] }}" class="col-span-1 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="Last Name">
                    <input type="text" name="new_students[{{ $itemIndex }}][first_name]" value="{{ $item['first_name'] }}" class="col-span-1 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="First Name">
                    <input type="text" name="new_students[{{ $itemIndex }}][middle_name]" value="{{ $item['middle_name'] }}" class="col-span-1 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="Middle">
                    <input type="text" name="new_students[{{ $itemIndex }}][suffix]" value="{{ $item['suffix'] ?? '' }}" class="col-span-1 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="Sfx">
                  </div>
                </td>
                <td class="p-2">
                  <input type="text" name="new_students[{{ $itemIndex }}][gender]" value="{{ $item['gender'] }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7 text-center">
                </td>
                <td class="p-2">
                  <input type="text" name="new_students[{{ $itemIndex }}][email]" value="{{ $item['email'] }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                </td>
                <td class="p-2 bg-secondary-50/10 dark:bg-slate-900/20">
                  <div class="flex gap-1">
                    <input type="text" name="new_students[{{ $itemIndex }}][grade_level]" value="{{ $item['grade_level'] }}" class="w-1/3 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7 text-center" placeholder="Lvl">
                    <input type="text" name="new_students[{{ $itemIndex }}][section]" value="{{ $item['section'] }}" class="w-2/3 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="Section">
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="py-8 text-center text-slate-400 italic">No new students found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="pagination-links mt-2">
          {{ $newPaginatedData->appends(request()->except('new'))->links() }}
        </div>
      </div>
      @endif

      @if($existing)
      <div class="space-y-3">
        <div class="flex items-center gap-3">
          <span class="px-3 py-1 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 text-[10px] font-black uppercase tracking-wider rounded-full border border-amber-200 dark:border-amber-800">
            Existing Students ({{ $existingPaginatedData->total() }})
          </span>
          <div class="h-px bg-slate-100 dark:bg-slate-700 flex-grow"></div>
        </div>

        <div class="overflow-x-auto rounded border border-slate-100 dark:border-slate-700">
          <table class="w-full text-left border-collapse min-w-[1000px]">
            <thead>
              <tr class="bg-slate-700 text-white text-[10px] uppercase tracking-wider font-black">
                <th class="px-3 py-2 border-r border-slate-600 w-[150px]">RFID & ID</th>
                <th class="px-3 py-2 border-r border-slate-600">Full Name Details</th>
                <th class="px-3 py-2 border-r border-slate-600 w-[100px]">Gender</th>
                <th class="px-3 py-2 border-r border-slate-600">Email Address</th>
                <th class="px-3 py-2 w-[180px]">Academic Info</th>
              </tr>
            </thead>
            <tbody class="text-[11px]">
              @php $startIndex = ($existingPaginatedData->currentPage() - 1) * $existingPaginatedData->perPage(); @endphp
              @forelse($existingPaginatedData as $index => $item)
              @php $itemIndex = $startIndex + $index; @endphp
              <tr class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                <td class="p-2 space-y-1 bg-slate-50/30 dark:bg-slate-900/10">
                  <input type="text" name="existing_students[{{ $itemIndex }}][rfid]" value="{{ $item['rfid'] }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[10px] p-1 h-6 font-mono" placeholder="RFID">
                  <input type="text" name="existing_students[{{ $itemIndex }}][id_number]" value="{{ $item['id_number'] }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[10px] p-1 h-6 font-mono" placeholder="ID Number">
                </td>
                <td class="p-2">
                  <div class="grid grid-cols-4 gap-1">
                    <input type="text" name="existing_students[{{ $itemIndex }}][last_name]" value="{{ $item['last_name'] }}" class="col-span-1 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="Last Name">
                    <input type="text" name="existing_students[{{ $itemIndex }}][first_name]" value="{{ $item['first_name'] }}" class="col-span-1 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="First Name">
                    <input type="text" name="existing_students[{{ $itemIndex }}][middle_name]" value="{{ $item['middle_name'] }}" class="col-span-1 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="Middle">
                    <input type="text" name="existing_students[{{ $itemIndex }}][suffix]" value="{{ $item['suffix'] ?? '' }}" class="col-span-1 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="Sfx">
                  </div>
                </td>
                <td class="p-2">
                  <input type="text" name="existing_students[{{ $itemIndex }}][gender]" value="{{ $item['gender'] }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7 text-center">
                </td>
                <td class="p-2">
                  <input type="text" name="existing_students[{{ $itemIndex }}][email]" value="{{ $item['email'] }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7">
                </td>
                <td class="p-2 bg-secondary-50/10 dark:bg-slate-900/20">
                  <div class="flex gap-1">
                    <input type="text" name="existing_students[{{ $itemIndex }}][grade_level]" value="{{ $item['grade_level'] }}" class="w-1/3 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7 text-center" placeholder="Lvl">
                    <input type="text" name="existing_students[{{ $itemIndex }}][section]" value="{{ $item['section'] }}" class="w-2/3 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-[11px] p-1 h-7" placeholder="Section">
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="py-8 text-center text-slate-400 italic">No existing students found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="pagination-links mt-2">
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
    document.querySelectorAll('.pagination-links a').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        submitImportForm(this.href);
      });
    });
  });
</script>