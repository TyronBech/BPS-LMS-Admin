<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Report Table for Users</h2>
  <table class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead id="today-header" class="bg-blue-400 text-center font-bold text-slate-200">
      <th>Name</th>
      <th>Date</th>
      <th>Time in</th>
      <th>Time out</th>
      <th>Remarks</th>
    </thead>
    <tbody id="students-activity">
      @forelse($data as $item)
      @if($item->user)
      <tr class="text-center">
        <td class="pb-1 min-w-20">{{ $item->user->last_name }}, {{ $item->user->first_name }} {{ $item->user->middle_name ?? '' }}</td>
        <td class="pb-1">{{ \Carbon\Carbon::parse($item->time_in)->format('Y-m-d') }}</td>
        <td class="pb-1">{{ \Carbon\Carbon::parse($item->time_in)->format('g:i A') }}</td>
        @if($item->time_out)
        <td class="pb-1">{{ \Carbon\Carbon::parse($item->time_out)->format('g:i A') }}</td>
        @else
        <td class="pb-1">-</td>
        @endif
        <td class="pb-1">{{ $item->remarks ?? '' }}</td>
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