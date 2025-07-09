<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Report Table for Computer Use</h2>
  <table class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead id="today-header" class="bg-blue-400 text-left font-bold text-slate-200 border-2 border-slate-300 dark:border-slate-700">
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Name</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Level</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Section</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Date</th>
      <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Time</th>
    </thead>
    <tbody id="students-activity">
      @forelse($data as $item)
      @if($item->user && $item->user->students)
      <tr class="text-left border-2 border-slate-300 dark:border-slate-700">
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->user->last_name }}, {{ $item->user->first_name }} {{ $item->user->middle_name ?? '' }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->user->students->level }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ $item->user->students->section }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ \Carbon\Carbon::parse($item->time_in)->format('Y-m-d') }}</td>
        <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">{{ \Carbon\Carbon::parse($item->time_in)->format('g:i A') }}</td>
      </tr>
      @endif
      @empty
      <tr>
        <td colspan="8" class="text-center">No data found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>