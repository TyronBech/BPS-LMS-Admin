<div id="tabular" class="container mx-auto mt-2 mb-4">
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
    <div class="p-4">
      <h2 class="text-center mb-4 font-semibold text-2xl dark:text-white">Report Table for Class Reservations</h2>
      <form method="GET" class="flex flex-wrap items-center gap-4">
        <div class="flex items-center">
          <label for="perPage" class="mr-2 text-xs font-medium text-gray-500 dark:text-gray-400">Show</label>
          <input type="hidden" name="start" value="{{ old('start', request('start')) }}">
          <input type="hidden" name="end" value="{{ old('end', request('end')) }}">
          <input type="number" name="perPage" id="perPage" min="1" max="500" onchange="this.form.submit()" value="{{ old('perPage', $perPage) }}" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-500 focus:border-primary-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white w-20">
          <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">entries</span>
        </div>

        <div class="flex items-center">
          <label for="status" class="mr-2 text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
          <select name="status" id="status" onchange="this.form.submit()" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-500 focus:border-primary-500 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
            <option value="All" {{ $status == 'All' ? 'selected' : '' }}>All Status</option>
            <option value="Pending" {{ $status == 'Pending' ? 'selected' : '' }}>Pending</option>
            <option value="Approved" {{ $status == 'Approved' ? 'selected' : '' }}>Approved</option>
            <option value="Rejected" {{ $status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="Cancelled" {{ $status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
          </select>
        </div>
      </form>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-xs text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th scope="col" class="px-4 py-2">Date</th>
            <th scope="col" class="px-4 py-2">Time</th>
            <th scope="col" class="px-4 py-2">Requestor Name</th>
            <th scope="col" class="px-4 py-2">Purpose</th>
            <th scope="col" class="px-4 py-2">Status</th>
            <th scope="col" class="px-4 py-2">Submitted</th>
            <th scope="col" class="px-4 py-2">Action Date</th>
            <th scope="col" class="px-4 py-2">Remarks</th>
          </tr>
        </thead>
        <tbody>
          @forelse($data as $item)
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            <td class="px-4 py-2">{{ $item->reservation_date ? $item->reservation_date->format('M d, Y') : 'N/A' }}</td>
            <td class="px-4 py-2">
                {{ \Carbon\Carbon::parse($item->start_time)->format('h:i A') }}
                @if($item->end_time)
                  - {{ \Carbon\Carbon::parse($item->end_time)->format('h:i A') }}
                @endif
            </td>
            <th scope="row" class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
              {{ $item->user ? ($item->user->first_name . ' ' . $item->user->last_name) : 'N/A' }}
            </th>
            <td class="px-4 py-2 max-w-xs truncate" title="{{ $item->purpose }}">
              {{ $item->purpose }}
            </td>
            <td class="px-4 py-2">
              @if($item->status === 'Approved')
                <span class="text-green-600 dark:text-green-400 font-semibold">{{ $item->status }}</span>
              @elseif($item->status === 'Rejected')
                <span class="text-red-600 dark:text-red-400 font-semibold">{{ $item->status }}</span>
              @else
                <span class="font-semibold">{{ $item->status }}</span>
              @endif
            </td>
            <td class="px-4 py-2">{{ $item->created_at ? $item->created_at->format('M d, Y') : 'N/A' }}</td>
            <td class="px-4 py-2">
                @if($item->status === 'Approved' && $item->approved_at)
                    {{ $item->approved_at->format('M d, Y h:i A') }}
                @elseif($item->status === 'Rejected' && $item->rejected_at)
                    {{ $item->rejected_at->format('M d, Y h:i A') }}
                @else
                    -
                @endif
            </td>
            <td class="px-4 py-2 max-w-xs truncate" title="{{ $item->remarks }}">{{ $item->remarks ?? '-' }}</td>
          </tr>
          @empty
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            <td colspan="8" class="px-4 py-2 text-center">No data found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-4">
      @if($data instanceof \Illuminate\Pagination\LengthAwarePaginator && $data->isNotEmpty())
        {{ $data->appends(request()->query())->links() }}
      @endif
    </div>
  </div>
</div>
