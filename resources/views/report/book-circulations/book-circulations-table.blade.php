<div class="container flex flex-col overflow-x-auto border-collapse border-2 border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Book Circulation Table</h2>
  <table class="table-fixed m-4 bg-white dark:bg-gray-800">
    <thead id="today-header" class="bg-blue-400 font-bold text-slate-200">
      <th>Accession</th>
      <th>Call Number</th>
      <th>Barcode</th>
      <th>Title</th>
      <th>Availability</th>
      <th>Condition</th>
    </thead>
    <tbody id="students-activity" class="text-center">
      @forelse($data as $item)
        <tr>
          <td class="pb-1">{{ $item->accession }}</td>
          <td class="pb-1">{{ $item->call_number }}</td>
          <td class="pb-1">{{ $item->barcode }}</td>
          <td class="pb-1">{{ $item->title }}</td>
          @if($item->availability_status == 'Available')
            <td class="pb-1 text-green-500 dark:text-green-400">{{ $item->availability_status }}</td>
          @elseif($item->availability_status == 'Borrowed')
            <td class="pb-1 text-red-500 dark:text-red-400">{{ $item->availability_status }}</td>
          @elseif($item->availability_status == 'Reserved')
            <td class="pb-1 text-yellow-500 dark:text-yellow-400">{{ $item->availability_status }}</td>
          @elseif($item->availability_status == 'In Use')
            <td class="pb-1 text-blue-500 dark:text-blue-400">{{ $item->availability_status }}</td>
          @endif
          @if($item->condition_status == 'New')
            <td class="pb-1 text-blue-500 dark:text-blue-400">{{ $item->condition_status }}</td>
          @elseif($item->condition_status == 'Good')
            <td class="pb-1 text-green-500 dark:text-green-400">{{ $item->condition_status }}</td>
          @elseif($item->condition_status == 'Fair')
            <td class="pb-1 text-yellow-500 dark:text-yellow-400">{{ $item->condition_status }}</td>
          @elseif($item->condition_status == 'Poor')
            <td class="pb-1 text-red-500 dark:text-red-400">{{ $item->condition_status }}</td>
          @endif
        </tr>
      @empty
        <tr>
          <td colspan="6">No data found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>