<div id="tabular" class="container mx-auto mt-2 mb-4">
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
    <div class="p-4">
      <h2 class="text-center mb-4 font-semibold text-2xl dark:text-white">Circulation Table</h2>
      <form method="GET" class="flex items-center">
        <label for="perPage" class="mr-2 text-xs font-medium text-gray-500 dark:text-gray-400">Show</label>
        <input type="hidden" name="start" value="{{ old('start', request('start')) }}">
        <input type="hidden" name="end" value="{{ old('end', request('end')) }}">
        <input type="hidden" name="search" value="{{ old('search', request('search')) }}">
        <input type="hidden" name="type" value="{{ old('type', request('type')) }}">
        <input type="hidden" name="user_type" value="{{ request('user_type', 'student') }}">
        <input type="number" name="perPage" id="perPage" min="1" max="500" onchange="this.form.submit()" value="{{ old('perPage', $perPage) }}" min="1" max="500" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-500 focus:border-primary-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
        <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">entries per page</span>
      </form>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-xs text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th scope="col" class="px-4 py-2">Accession</th>
            <th scope="col" class="px-4 py-2">Title</th>
            <th scope="col" class="px-4 py-2 whitespace-nowrap">Name</th>
            @php $ut = request('user_type', 'student'); @endphp
            @if($ut === 'student')
              <th scope="col" class="px-4 py-2 whitespace-nowrap">Grade & Section</th>
            @else
              <th scope="col" class="px-4 py-2 whitespace-nowrap">Position</th>
            @endif
            <th scope="col" class="px-4 py-2 whitespace-nowrap">Reserved Date</th>
            <th scope="col" class="px-4 py-2 whitespace-nowrap">Pickup Deadline</th>
            <th scope="col" class="px-4 py-2 whitespace-nowrap">Borrowed</th>
            <th scope="col" class="px-4 py-2 whitespace-nowrap">Due</th>
            <th scope="col" class="px-4 py-2 whitespace-nowrap">Returned</th>
            <th scope="col" class="px-4 py-2">Transaction Type</th>
            <th scope="col" class="px-4 py-2">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($data as $item)
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            <td class="px-4 py-2">{{ $item->book->accession }}</td>
            <th scope="row" class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
              {{ $item->book->title }}
            </th>
            <td class="px-4 py-2 whitespace-nowrap">{{ $item->user->last_name }}, {{ $item->user->first_name }} {{ $item->user->middle_name }}</td>
            <td class="px-4 py-2 whitespace-nowrap">
              @if($item->user->students)
                {{ $item->user->students->level }} - {{ $item->user->students->section }}
              @elseif($item->user->employees)
                {{ $item->user->employees->employee_role }}
              @else
                N/A
              @endif
            </td>
            <td class="px-4 py-2 whitespace-nowrap">{{ $item->reserved_date ? \Carbon\Carbon::parse($item->reserved_date)->format('M j, Y') : 'Not Reserved' }}</td>
            <td class="px-4 py-2 whitespace-nowrap">{{ $item->pickup_deadline ? \Carbon\Carbon::parse($item->pickup_deadline)->format('M j, Y') : 'No Pickup Deadline' }}</td>
            <td class="px-4 py-2 whitespace-nowrap">{{ $item->date_borrowed ? \Carbon\Carbon::parse($item->date_borrowed)->format('M j, Y') : 'Not Borrowed' }}</td>
            <td class="px-4 py-2 whitespace-nowrap">{{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('M j, Y') : 'No Due Date' }}</td>
            <td class="px-4 py-2 whitespace-nowrap">{{ $item->return_date ? \Carbon\Carbon::parse($item->return_date)->format('M j, Y') : 'Unreturned' }}</td>
            <td class="px-4 py-2">{{ ucwords($item->transaction_type) ?? 'No Type' }}</td>
            <td class="px-4 py-2">{{ ucwords($item->status) ?? 'No Status' }}</td>
          </tr>
          @empty
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            <td colspan="11" class="px-4 py-2 text-center">No data found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-4">
      {{ $data->withQueryString()->fragment('tabular')->links() }}
    </div>
  </div>
</div>

@if(isset($chartLabels) && isset($chartCounts))
<div id="chart-data-bridge" 
     data-labels="{{ json_encode($chartLabels) }}" 
     data-counts="{{ json_encode($chartCounts) }}" 
     style="display: none;">
</div>
@endif
