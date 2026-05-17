<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
  <div class="flex items-center justify-between w-full p-4 bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
    <form method="GET" class="flex items-center skip-loader">
      <input type="hidden" name="tab" value="{{ $activeTab }}">
      @if(request('search'))
        <input type="hidden" name="search" value="{{ request('search') }}">
      @endif
      <label for="perPage" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
      <input type="number" name="perPage" id="perPage" min="1" max="500" onchange="this.form.submit()" value="{{ old('perPage', request('perPage', 10)) }}" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-400 focus:border-primary-400 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
      <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">entries per page</span>
    </form>
  </div>

  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
      <tr>
        <th scope="col" class="px-6 py-3">Student Info</th>
        <th scope="col" class="px-6 py-3">Book Info</th>
        @if($activeTab === 'reservations')
          <th scope="col" class="px-6 py-3">Reserved Date</th>
          <th scope="col" class="px-6 py-3">Pickup Deadline</th>
          <th scope="col" class="px-6 py-3">Remarks</th>
        @else
          <th scope="col" class="px-6 py-3">Current Due</th>
          <th scope="col" class="px-6 py-3">Requested Due</th>
          <th scope="col" class="px-6 py-3">Reason</th>
        @endif
        <th scope="col" class="px-6 py-3">Submitted</th>
        <th scope="col" class="px-6 py-3 text-center">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($pendingRequests as $request)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
        <!-- Student Info -->
        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
          <div class="font-semibold text-sm">{{ $request->user->first_name }} {{ $request->user->last_name }}</div>
          <div class="text-xs text-gray-500 dark:text-gray-400">{{ $request->user->email }}</div>
        </td>

        <!-- Book Info -->
        <td class="px-6 py-4">
          <div class="font-semibold text-sm text-gray-900 dark:text-white">{{ Str::limit($request->book->title, 40) }}</div>
          <div class="text-xs text-gray-500 dark:text-gray-400">Accession: {{ $request->book->accession }}</div>
        </td>

        @if($activeTab === 'reservations')
          <!-- Reserved Date -->
          <td class="px-6 py-4">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200">
              {{ $request->reserved_date ? \Carbon\Carbon::parse($request->reserved_date)->format('M d, Y') : ($request->created_at ? $request->created_at->format('M d, Y') : 'N/A') }}
            </span>
          </td>

          <!-- Pickup Deadline -->
          <td class="px-6 py-4">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800 dark:bg-cyan-900/50 dark:text-cyan-200">
              {{ $request->pickup_deadline ? \Carbon\Carbon::parse($request->pickup_deadline)->format('M d, Y') : 'N/A' }}
            </span>
          </td>

          <!-- Remarks -->
          <td class="px-6 py-4 max-w-xs truncate">
            {{ $request->remarks ?? 'No remarks provided' }}
          </td>
        @else
          <!-- Current Due -->
          <td class="px-6 py-4">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200">
              {{ \Carbon\Carbon::parse($request->due_date)->format('M d, Y') }}
            </span>
          </td>

          <!-- Requested Due -->
          <td class="px-6 py-4">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800 dark:bg-cyan-900/50 dark:text-cyan-200">
              {{ \Carbon\Carbon::parse($request->requested_due_date)->format('M d, Y') }}
            </span>
          </td>

          <!-- Reason -->
          <td class="px-6 py-4 max-w-xs truncate">
            {{ $request->extension_reason ?? 'Not provided' }}
          </td>
        @endif

        <!-- Submitted -->
        <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400">
          {{ $request->updated_at->diffForHumans() }}
        </td>

        <!-- Actions -->
        <td class="px-6 py-4 text-center">
          <div class="flex items-center justify-center space-x-2">
            <button
              type="button"
              data-modal-target="approve-modal-{{ $request->id }}"
              data-modal-toggle="approve-modal-{{ $request->id }}"
              class="skip-loader inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-green-800">
              Approve
            </button>
            <button
              type="button"
              data-modal-target="reject-modal-{{ $request->id }}"
              data-modal-toggle="reject-modal-{{ $request->id }}"
              class="skip-loader inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800">
              Reject
            </button>
          </div>
        </td>
      </tr>
      <!-- Render the dynamic verification modals inline for this item -->
      @include('maintenance.reservations.modals', ['request' => $request])
      @empty
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
        <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
          No pending {{ $activeTab === 'reservations' ? 'reservation' : 'extension' }} requests found.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>

  @if($pendingRequests->isNotEmpty())
  <div class="p-4 border-t dark:border-gray-700">
    {{ $pendingRequests->appends(request()->query())->links() }}
  </div>
  @endif
</div>