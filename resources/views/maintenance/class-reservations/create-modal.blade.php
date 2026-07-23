<!-- Create Reservation Modal -->
<div id="create-reservation-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-2xl max-h-full">
    <!-- Modal content -->
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          Add New Reservation (Auto-Approve)
        </h3>
        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="create-reservation-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <!-- Modal body -->
      <form action="{{ route('maintenance.class-reservations.store') }}" method="POST" class="p-4 md:p-5">
        @csrf
        <div class="grid gap-4 mb-4 grid-cols-2">
          <div class="col-span-2 sm:col-span-1">
            <label for="user_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">User <span class="text-red-500">*</span></label>
            <select id="user_id" name="user_id" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
              <option value="" disabled selected>Select User</option>
              @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})</option>
              @endforeach
            </select>
          </div>
          <div class="col-span-2 sm:col-span-1">
            <label for="faculty_user_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Faculty Sponsor</label>
            <select id="faculty_user_id" name="faculty_user_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
              <option value="" selected>No Sponsor</option>
              @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})</option>
              @endforeach
            </select>
          </div>
          <div class="col-span-2 sm:col-span-1">
            <label for="reservation_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date <span class="text-red-500">*</span></label>
            <input type="date" name="reservation_date" id="reservation_date" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          </div>
          <div class="col-span-2 sm:col-span-1 flex flex-col gap-2">
            <div class="flex gap-2">
              <div class="w-full">
                <label for="start_time" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Start Time <span class="text-red-500">*</span></label>
                <input type="time" name="start_time" id="start_time" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
              </div>
              <div class="w-full">
                <label for="end_time" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">End Time <span class="text-red-500">*</span></label>
                <input type="time" name="end_time" id="end_time" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
              </div>
            </div>
            <p id="conflict-error" class="hidden text-sm text-red-600 dark:text-red-500">Conflicting schedule detected.</p>
          </div>
          <div class="col-span-2">
            <label for="purpose" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Purpose <span class="text-red-500">*</span></label>
            <textarea id="purpose" name="purpose" rows="4" required maxlength="500" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="State the purpose of this class reservation"></textarea>
          </div>
        </div>
        <div class="flex items-center space-x-4 border-t border-gray-200 rounded-b dark:border-gray-600 pt-4">
          <button type="submit" id="submit-reservation-btn" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
            Add Reservation
          </button>
          <button data-modal-toggle="create-reservation-modal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('reservation_date');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const conflictError = document.getElementById('conflict-error');
    const submitBtn = document.getElementById('submit-reservation-btn');

    function checkConflict() {
        const date = dateInput.value;
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;

        // Reset errors
        conflictError.classList.add('hidden');
        startTimeInput.classList.remove('border-red-500', 'text-red-900', 'focus:ring-red-500', 'focus:border-red-500');
        endTimeInput.classList.remove('border-red-500', 'text-red-900', 'focus:ring-red-500', 'focus:border-red-500');
        dateInput.classList.remove('border-red-500', 'text-red-900', 'focus:ring-red-500', 'focus:border-red-500');
        submitBtn.disabled = false;

        if (date && startTime && endTime) {
            // Ensure start time is before end time
            if (startTime >= endTime) {
                conflictError.textContent = 'End time must be after start time.';
                conflictError.classList.remove('hidden');
                startTimeInput.classList.add('border-red-500', 'text-red-900', 'focus:ring-red-500', 'focus:border-red-500');
                endTimeInput.classList.add('border-red-500', 'text-red-900', 'focus:ring-red-500', 'focus:border-red-500');
                submitBtn.disabled = true;
                return;
            }

            fetch(`{{ route('maintenance.class-reservations.check-conflict') }}?date=${date}&start_time=${startTime}&end_time=${endTime}`)
                .then(response => response.json())
                .then(data => {
                    if (data.conflict) {
                        conflictError.textContent = 'Conflicting schedule detected with an approved reservation.';
                        conflictError.classList.remove('hidden');
                        startTimeInput.classList.add('border-red-500', 'text-red-900', 'focus:ring-red-500', 'focus:border-red-500');
                        endTimeInput.classList.add('border-red-500', 'text-red-900', 'focus:ring-red-500', 'focus:border-red-500');
                        dateInput.classList.add('border-red-500', 'text-red-900', 'focus:ring-red-500', 'focus:border-red-500');
                        submitBtn.disabled = true;
                    }
                })
                .catch(error => console.error('Error checking conflict:', error));
        }
    }

    dateInput.addEventListener('change', checkConflict);
    startTimeInput.addEventListener('change', checkConflict);
    endTimeInput.addEventListener('change', checkConflict);
});
</script>
