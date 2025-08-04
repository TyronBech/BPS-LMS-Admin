<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Report Table for Book Audits</h2>
  <table class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead class="bg-blue-400 text-left font-bold text-slate-200 border-2 border-slate-300 dark:border-slate-700">
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Title</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Field Changed</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Old Value</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">New Value</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Change Type</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Change By</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Date</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Time</th>
    </thead>
    <tbody>
      @forelse($data as $item)
      <tr class="text-left border-2 border-slate-300 dark:border-slate-700">
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->book->title ?? 'null' }}</td>
        @if($item->field_changed == 'category_id')
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">Category</td>
        @else
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->field_changed ?? 'null' }}</td>
        @endif
        @if($item->field_changed == 'category_id' && $item->oldCategory)
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->oldCategory->name ?? 'null' }}</td>
        @else
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->old_value ?? 'null' }}</td>
        @endif
        @if($item->field_changed == 'category_id' && $item->newCategory)
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->newCategory->name ?? 'null' }}</td>
        @else
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->new_value ?? 'null' }}</td>
        @endif
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->change_type ?? 'null' }}</td>
        @if($item->changedBy)
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->changedBy->last_name }}, {{ $item->changedBy->first_name }} {{ $item->changedBy->middle_name ?? '' }}</td>
        @else
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">System</td>
        @endif
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ \Carbon\Carbon::parse($item->changed_date)->format('Y-m-d') ?? 'null' }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ \Carbon\Carbon::parse($item->changed_date)->format('g:i A') ?? 'null' }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="8" class="text-center">No data found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>