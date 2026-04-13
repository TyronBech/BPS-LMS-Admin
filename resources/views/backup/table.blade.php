<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
  <h2 class="text-lg md:text-xl font-bold tracking-tight text-gray-900 dark:text-white p-4 bg-white dark:bg-gray-800">
    List of Database Backups
  </h2>

  <!-- Per-page selector -->
  <div class="px-4 py-2 bg-white dark:bg-gray-800 flex items-center justify-start">
    <form method="GET" action="{{ url()->current() }}" class="flex items-center space-x-2">
      @foreach(request()->except('perPage', 'page') as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
      @endforeach
      <input type="hidden" name="page" value="1">
      <label for="perPage" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Show</label>
      <input type="number" name="perPage" id="perPage" min="1" max="500" onchange="this.form.submit()" value="{{ old('perPage', request('perPage', 10)) }}" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-400 focus:border-primary-400 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
      <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">entries per page</span>
    </form>
  </div>

  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
      <tr>
        <th scope="col" class="px-6 py-3">Filename</th>
        <th scope="col" class="px-6 py-3 hidden sm:table-cell">Type</th>
        <th scope="col" class="px-6 py-3 hidden md:table-cell">Size</th>
        <th scope="col" class="px-6 py-3 hidden lg:table-cell">Date</th>
        <th scope="col" class="px-6 py-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($backups as $item)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
          <div class="text-base font-semibold">{{ $item['filename'] }}</div>
          <div class="font-normal text-gray-500 lg:hidden">{{ $item['created'] }}</div>
        </th>
        <td class="px-6 py-4 hidden sm:table-cell">{{ $item['type'] }}</td>
        <td class="px-6 py-4 hidden md:table-cell">{{ $item['size'] }}</td>
        <td class="px-6 py-4 hidden lg:table-cell">{{ $item['created'] }}</td>
        <td class="px-6 py-4">
          <div class="flex items-center space-x-2">
            <form action="{{ route('backup.download', ['filename' => $item['filename']]) }}" method="POST" class="skip-loader inline-block">
              @csrf
              <button type="submit" value="download" class="skip-loader inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-green-800">Download</button>
            </form>
            <form action="{{ route('backup.destroy', ['filename' => $item['filename']]) }}" method="POST" class="skip-loader inline-block">
              @csrf
              @method('DELETE')
              <button type="button" data-modal-target="delete-modal-{{ $loop->index }}" data-modal-toggle="delete-modal-{{ $loop->index }}" class="skip-loader inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800">Delete</button>

              <!-- Delete Modal -->
              <div id="delete-modal-{{ $loop->index }}" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative p-4 w-full max-w-md max-h-full">
                  <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
                    <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-modal-{{ $loop->index }}">
                      <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                      </svg>
                      <span class="sr-only">Close modal</span>
                    </button>
                    <div class="p-4 md:p-5 text-center">
                      <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                      </svg>
                      <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this backup?</h3>
                      <button type="submit" value="delete" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
                        Yes, I'm sure
                      </button>
                      <button data-modal-hide="delete-modal-{{ $loop->index }}" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-400 dark:hover:bg-gray-700 shadow-md">No, cancel</button>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
        <td colspan="5" class="px-6 py-4 text-center">No backups found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>

  <!-- Pagination footer -->
  <div class="flex flex-col md:flex-row md:items-center justify-end gap-3 px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
    <div class="text-sm text-gray-700 dark:text-gray-300">
    <div>
      {{ $backups->onEachSide(1)->links() }}
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-modal-toggle]').forEach(function(toggleBtn) {
      const modalId = toggleBtn.getAttribute('data-modal-target');
      const modal = document.getElementById(modalId);

      if (modal) {
        const closeButtons = modal.querySelectorAll('[data-modal-hide]');

        toggleBtn.addEventListener('click', function() {
          modal.classList.remove('hidden');
          modal.classList.add('flex'); // Use flex to center content
        });

        closeButtons.forEach(function(closeBtn) {
          closeBtn.addEventListener('click', function() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
          });
        });

        // Close modal on outside click
        modal.addEventListener('click', function(event) {
          if (event.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
          }
        });
      }
    });

    // Download toast script
    const downloadToast = document.getElementById('download-toast');
    if (downloadToast) {
      const dismissButton = downloadToast.querySelector('[data-dismiss-target="#download-toast"]');

      document.querySelectorAll('button[value="download"]').forEach(function(downloadBtn) {
        downloadBtn.addEventListener('click', function() {
          downloadToast.classList.remove('hidden');
          downloadToast.classList.add('flex');
        });
      });

      if (dismissButton) {
        dismissButton.addEventListener('click', function() {
          downloadToast.classList.add('hidden');
          downloadToast.classList.remove('flex');
        });
      }
    }
  });
</script>