<!-- Approve Modal -->
<div id="approve-modal-{{ $request->id }}" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="approve-modal-{{ $request->id }}">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-green-400 w-12 h-12 dark:text-green-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 10 2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        
        @if($request->transaction_type === 'Reserved')
          <h3 class="mb-5 text-base md:text-lg font-normal text-gray-500 dark:text-gray-400 px-2">
            Are you sure you want to approve this book reservation request for
            <strong class="text-gray-900 dark:text-white">{{ $request->user->first_name }} {{ $request->user->last_name }}</strong>?
          </h3>
          <p class="mb-3 text-sm text-gray-600 dark:text-gray-400 px-2">
            Book: <strong class="break-words text-gray-900 dark:text-white">{{ $request->book->title }}</strong>
          </p>
          
          @if($request->book->availability_status === 'Available')
            <!-- Flow for Available -->
            <div class="mb-5 p-3.5 bg-green-50 border border-green-200 rounded-xl dark:bg-green-900/10 dark:border-green-800">
              <div class="flex items-center justify-between gap-2">
                <div class="text-left">
                  <span class="text-[10px] font-bold uppercase tracking-wider text-green-700 dark:text-green-400 block">Current State</span>
                  <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">Available</p>
                </div>
                <div class="text-gray-400 dark:text-gray-600">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                  </svg>
                </div>
                <div class="text-right">
                  <span class="text-[10px] font-bold uppercase tracking-wider text-green-700 dark:text-green-400 block">System Result</span>
                  <p class="text-sm font-semibold text-green-700 dark:text-green-400">Ready for Pickup</p>
                </div>
              </div>
              <p class="mt-2.5 text-xs text-left text-gray-500 dark:text-gray-400 border-t border-green-200/50 dark:border-green-800/50 pt-2 font-normal">
                This material is available in the library. Approval will mark it as <span class="font-semibold text-green-700 dark:text-green-400">"Available for pick up"</span>, giving the user 3 days to collect it.
              </p>
            </div>
          @else
            <!-- Flow for Unavailable -->
            <div class="mb-5 p-3.5 bg-amber-50 border border-amber-200 rounded-xl dark:bg-amber-900/10 dark:border-amber-800">
              <div class="flex items-center justify-between gap-2">
                <div class="text-left">
                  <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 block">Current State</span>
                  <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">Borrowed/Unavailable</p>
                </div>
                <div class="text-gray-400 dark:text-gray-600">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                  </svg>
                </div>
                <div class="text-right">
                  <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 block">System Result</span>
                  <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">Placed in Queue</p>
                </div>
              </div>
              <p class="mt-2.5 text-xs text-left text-gray-500 dark:text-gray-400 border-t border-amber-200/50 dark:border-amber-800/50 pt-2 font-normal">
                This material is currently borrowed. Approval will place the user in the <span class="font-semibold text-amber-700 dark:text-amber-400">Reservation Queue</span>. They will be notified automatically when it becomes available.
              </p>
            </div>
          @endif
        @else
          <h3 class="mb-5 text-base md:text-lg font-normal text-gray-500 dark:text-gray-400 px-2">
            Are you sure you want to approve this extension request for
            <strong class="text-gray-900 dark:text-white">{{ $request->user->first_name }} {{ $request->user->last_name }}</strong>?
          </h3>
          <p class="mb-5 text-sm text-gray-600 dark:text-gray-400 px-2">
            Book: <strong class="break-words">{{ $request->book->title }}</strong><br>
            New Due Date: <strong>{{ \Carbon\Carbon::parse($request->requested_due_date)->format('F d, Y') }}</strong>
          </p>
        @endif

        <form action="{{ route('maintenance.approve-extension', $request->id) }}" method="POST" class="inline">
          @csrf
          <button type="submit" class="text-white bg-green-600 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, Approve
          </button>
        </form>
        <button data-modal-hide="approve-modal-{{ $request->id }}" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 shadow-md">
          Cancel
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal-{{ $request->id }}" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="reject-modal-{{ $request->id }}">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-red-400 w-12 h-12 dark:text-red-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        
        @if($request->transaction_type === 'Reserved')
          <h3 class="mb-5 text-base md:text-lg font-normal text-gray-500 dark:text-gray-400 px-2">
            Reject book reservation request for
            <strong class="text-gray-900 dark:text-white">{{ $request->user->first_name }} {{ $request->user->last_name }}</strong>?
          </h3>
        @else
          <h3 class="mb-5 text-base md:text-lg font-normal text-gray-500 dark:text-gray-400 px-2">
            Reject extension request for
            <strong class="text-gray-900 dark:text-white">{{ $request->user->first_name }} {{ $request->user->last_name }}</strong>?
          </h3>
        @endif

        <form action="{{ route('maintenance.reject-extension', $request->id) }}" method="POST">
          @csrf
          <div class="mb-4 text-left">
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
              Rejection Reason <span class="text-red-500">*</span>
            </label>
            <textarea
              name="rejection_reason"
              rows="4"
              required
              maxlength="500"
              @if($request->transaction_type === 'Reserved')
                placeholder="Please provide a clear reason for rejecting this reservation request..."
              @else
                placeholder="Please provide a clear reason for rejecting this extension request..."
              @endif
              class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-red-500 focus:border-red-500 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-red-500 dark:focus:border-red-500"></textarea>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum 500 characters</p>
          </div>
          <button type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Confirm Rejection
          </button>
          <button data-modal-hide="reject-modal-{{ $request->id }}" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 shadow-md">
            Cancel
          </button>
        </form>
      </div>
    </div>
  </div>
</div>