<div class="p-3 md:p-6">
  <!-- Entries Per Page Selector -->
  <form method="GET" class="flex items-center mb-4">
    <label for="perPage" class="mr-2 text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
    <select
      name="perPage"
      id="perPage"
      onchange="this.form.submit()"
      class="border border-gray-300 text-xs md:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-1.5 md:p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
      <option value="10" {{ request('perPage', 10) == 10 ? 'selected' : '' }}>10</option>
      <option value="25" {{ request('perPage', 10) == 25 ? 'selected' : '' }}>25</option>
      <option value="50" {{ request('perPage', 10) == 50 ? 'selected' : '' }}>50</option>
      <option value="100" {{ request('perPage', 10) == 100 ? 'selected' : '' }}>100</option>
    </select>
    <span class="ml-2 text-xs md:text-sm text-gray-600 dark:text-gray-400">entries</span>
  </form>

  @if($pendingRequests->isEmpty())
  <!-- Empty State -->
  <div class="text-center py-12 md:py-16">
    <svg class="mx-auto h-16 w-16 md:h-24 md:w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>
    <h3 class="mt-4 text-lg md:text-xl font-semibold text-gray-900 dark:text-white">No Pending Requests</h3>
    <p class="mt-2 text-sm md:text-base text-gray-500 dark:text-gray-400 px-4">There are no extension requests waiting for approval at this time.</p>
  </div>
  @else
  <!-- Mobile Card View (sm and below) -->
  <div class="block lg:hidden space-y-4">
    @foreach($pendingRequests as $request)
    <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4 shadow-sm">
      <!-- Student Info -->
      <div class="mb-3">
        <div class="flex items-start justify-between mb-2">
          <div class="flex-1">
            <h3 class="font-semibold text-base text-gray-900 dark:text-white">
              {{ $request->user->first_name }} {{ $request->user->last_name }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 break-all">
              {{ $request->user->email }}
            </p>
          </div>
        </div>
      </div>

      <!-- Book Info -->
      <div class="mb-3 pb-3 border-b border-gray-200 dark:border-gray-600">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Book:</p>
        <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ $request->book->title }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">Acc: {{ $request->book->accession }}</p>
      </div>

      <!-- Dates -->
      <div class="grid grid-cols-2 gap-3 mb-3">
        <div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Current Due:</p>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
            {{ \Carbon\Carbon::parse($request->due_date)->format('M d, Y') }}
          </span>
        </div>
        <div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Requested:</p>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200">
            {{ \Carbon\Carbon::parse($request->requested_due_date)->format('M d, Y') }}
          </span>
        </div>
      </div>

      <!-- Reason -->
      <div class="mb-3">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Reason:</p>
        <p class="text-xs text-gray-600 dark:text-gray-300">
          {{ $request->extension_reason ?? 'Not provided' }}
        </p>
      </div>

      <!-- Submitted Time -->
      <div class="mb-3">
        <p class="text-xs text-gray-500 dark:text-gray-400">
          Submitted {{ $request->created_at->diffForHumans() }}
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row gap-2">
        <button
          type="button"
          data-modal-target="approve-modal-{{ $request->id }}"
          data-modal-toggle="approve-modal-{{ $request->id }}"
          class="skip-loader flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          Approve
        </button>
        <button
          type="button"
          data-modal-target="reject-modal-{{ $request->id }}"
          data-modal-toggle="reject-modal-{{ $request->id }}"
          class="skip-loader flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
          Reject
        </button>
      </div>
    </div>

    <!-- Modals for mobile -->

    @endforeach
  </div>

  <!-- Desktop Table View (lg and above) -->
  <div class="hidden lg:block overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-300">
      <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
        <tr>
          <th scope="col" class="px-3 xl:px-4 py-3">Student Name</th>
          <th scope="col" class="px-3 xl:px-4 py-3">Book Title</th>
          <th scope="col" class="px-3 xl:px-4 py-3 text-center">Current Due</th>
          <th scope="col" class="px-3 xl:px-4 py-3 text-center">Requested Due</th>
          <th scope="col" class="px-3 xl:px-4 py-3">Reason</th>
          <th scope="col" class="px-3 xl:px-4 py-3 text-center">Submitted</th>
          <th scope="col" class="px-3 xl:px-4 py-3 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($pendingRequests as $request)
        <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
          <!-- Student Name -->
          <td class="px-3 xl:px-4 py-4">
            <div class="flex flex-col">
              <span class="font-semibold text-gray-900 dark:text-white text-sm">
                {{ $request->user->first_name }} {{ $request->user->last_name }}
              </span>
              <span class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">
                {{ $request->user->email }}
              </span>
            </div>
          </td>

          <!-- Book Title -->
          <td class="px-3 xl:px-4 py-4">
            <div class="flex flex-col">
              <span class="font-semibold text-gray-900 dark:text-white text-sm">
                {{ Str::limit($request->book->title, 30) }}
              </span>
              <span class="text-xs text-gray-500 dark:text-gray-400">
                Acc: {{ $request->book->accession }}
              </span>
            </div>
          </td>

          <!-- Current Due Date -->
          <td class="px-3 xl:px-4 py-4 text-center">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 whitespace-nowrap">
              {{ \Carbon\Carbon::parse($request->due_date)->format('M d, Y') }}
            </span>
          </td>

          <!-- Requested Due Date -->
          <td class="px-3 xl:px-4 py-4 text-center">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200 whitespace-nowrap">
              {{ \Carbon\Carbon::parse($request->requested_due_date)->format('M d, Y') }}
            </span>
          </td>

          <!-- Reason -->
          <td class="px-3 xl:px-4 py-4 max-w-xs">
            <span class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
              {{ $request->extension_reason ?? 'Not provided' }}
            </span>
          </td>

          <!-- Submitted -->
          <td class="px-3 xl:px-4 py-4 text-center text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
            {{ $request->created_at->diffForHumans() }}
          </td>

          <!-- Actions -->
          <td class="px-3 xl:px-4 py-4">
            <div class="flex flex-col xl:flex-row gap-2">
              <button
                type="button"
                data-modal-target="approve-modal-{{ $request->id }}"
                data-modal-toggle="approve-modal-{{ $request->id }}"
                class="skip-loader flex items-center justify-center gap-1 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition-colors duration-200 whitespace-nowrap">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="hidden xl:inline">Approve</span>
              </button>
              <button
                type="button"
                data-modal-target="reject-modal-{{ $request->id }}"
                data-modal-toggle="reject-modal-{{ $request->id }}"
                class="skip-loader flex items-center justify-center gap-1 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition-colors duration-200 whitespace-nowrap">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span class="hidden xl:inline">Reject</span>
              </button>
            </div>
          </td>
        </tr>

        <!-- Modals for desktop -->
        @include('maintenance.reservations.modals', ['request' => $request])
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="mt-4 md:mt-6">
    {{ $pendingRequests->appends(request()->query())->links() }}
  </div>
  @endif
</div>