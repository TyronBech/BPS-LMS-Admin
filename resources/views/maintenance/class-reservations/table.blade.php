@use('App\Enum\PermissionsEnum')
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
        <th scope="col" class="px-6 py-3">Requestor Name</th>
        <th scope="col" class="px-6 py-3">Date & Time</th>
        <th scope="col" class="px-6 py-3">Purpose</th>
        <th scope="col" class="px-6 py-3">Submitted</th>
        @if($activeTab !== 'Pending')
          <th scope="col" class="px-6 py-3">Resolution Details</th>
        @endif
        @if($activeTab === 'Pending')
          <th scope="col" class="px-6 py-3 text-center sticky right-0 z-10 bg-gray-50 dark:bg-gray-700">Actions</th>
        @endif
      </tr>
    </thead>
    <tbody>
      @forelse($reservations as $reservation)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 group">
        <!-- User Info -->
        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
          <div class="font-semibold text-sm">{{ $reservation->user->first_name }} {{ $reservation->user->last_name }}</div>
          <div class="text-xs text-gray-500 dark:text-gray-400">{{ $reservation->user->email }}</div>
        </td>

        <!-- Date & Time -->
        <td class="px-6 py-4">
          <div class="font-semibold text-sm text-gray-900 dark:text-white">
            {{ $reservation->reservation_date ? $reservation->reservation_date->format('M d, Y') : 'N/A' }}
          </div>
          <div class="text-xs text-gray-500 dark:text-gray-400">
            {{ \Carbon\Carbon::parse($reservation->start_time)->format('h:i A') }}
            @if($reservation->end_time)
              - {{ \Carbon\Carbon::parse($reservation->end_time)->format('h:i A') }}
            @endif
          </div>
        </td>

        <!-- Purpose -->
        <td class="px-6 py-4 max-w-xs truncate" title="{{ $reservation->purpose }}">
          {{ $reservation->purpose ?? 'No purpose provided' }}
        </td>

        <!-- Submitted -->
        <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400">
          {{ $reservation->created_at->diffForHumans() }}
        </td>

        <!-- Resolution Details -->
        @if($activeTab !== 'Pending')
          <td class="px-6 py-4">
            @if($reservation->status === 'Approved')
              <div class="text-xs text-green-600 dark:text-green-400 font-semibold">
                Approved by {{ $reservation->approver ? ($reservation->approver->first_name . ' ' . $reservation->approver->last_name) : 'Admin' }}
              </div>
              <div class="text-[10px] text-gray-400">
                {{ $reservation->approved_at ? $reservation->approved_at->format('M d, Y h:i A') : '' }}
              </div>
              @if($reservation->remarks)
                <div class="text-[11px] text-gray-500 mt-1 max-w-xs truncate" title="{{ $reservation->remarks }}">
                  {{ $reservation->remarks }}
                </div>
              @endif
            @elseif($reservation->status === 'Rejected')
              <div class="text-xs text-red-600 dark:text-red-400 font-semibold">Rejected</div>
              <div class="text-[10px] text-gray-400">
                {{ $reservation->rejected_at ? $reservation->rejected_at->format('M d, Y h:i A') : '' }}
              </div>
              @if($reservation->remarks)
                <div class="text-[11px] text-gray-500 mt-1 max-w-xs truncate" title="{{ $reservation->remarks }}">
                  {{ $reservation->remarks }}
                </div>
              @endif
            @elseif($reservation->status === 'Cancelled')
              <div class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Cancelled</div>
              @if($reservation->remarks)
                <div class="text-[11px] text-gray-500 mt-1 max-w-xs truncate" title="{{ $reservation->remarks }}">
                  {{ $reservation->remarks }}
                </div>
              @endif
            @endif
          </td>
        @endif

        <!-- Actions -->
        @if($activeTab === 'Pending')
          <td class="px-6 py-4 text-center sticky right-0 z-10 bg-white dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-600">
            <div class="flex items-center justify-center space-x-2">
              @can(PermissionsEnum::LIBRARY_RESERVATION_APPROVAL)
              <button
                type="button"
                data-modal-target="approve-modal-{{ $reservation->id }}"
                data-modal-toggle="approve-modal-{{ $reservation->id }}"
                class="skip-loader inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-green-800">
                Approve
              </button>
              <button
                type="button"
                data-modal-target="reject-modal-{{ $reservation->id }}"
                data-modal-toggle="reject-modal-{{ $reservation->id }}"
                class="skip-loader inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800">
                Reject
              </button>
              @endcan
            </div>
          </td>
        @endif
      </tr>
      <!-- Render the verification modals inline for this item -->
      @include('maintenance.class-reservations.modals', ['reservation' => $reservation])
      @empty
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
        <td colspan="{{ $activeTab === 'Pending' ? 5 : 5 }}" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
          No {{ strtolower($activeTab) }} class reservation requests found.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>

  @if($reservations->isNotEmpty())
  <div class="p-4 border-t dark:border-gray-700">
    {{ $reservations->appends(request()->query())->links() }}
  </div>
  @endif
</div>
