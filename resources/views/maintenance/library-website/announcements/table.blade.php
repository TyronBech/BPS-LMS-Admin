@use('App\Enum\PermissionsEnum')
<form method="GET" action="{{ route('maintenance.library-website.announcements') }}" class="justify-end m-2 w-full sm:w-auto">
  <input type="hidden" name="search" value="{{ request('search', '') }}">
  <label for="perPage" class="mr-2 text-sm font-medium text-gray-500">Show</label>
  <input type="number" name="perPage" id="perPage" min="1" max="500" onchange="this.form.submit()" value="{{ old('perPage', $perPage) }}" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-400 focus:border-primary-400 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
  <span class="ml-2 text-sm text-gray-600">entries per page</span>
</form>
<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
      <tr>
        <th scope="col" class="px-6 py-3">Title</th>
        <th scope="col" class="px-6 py-3 hidden md:table-cell">Category</th>
        <th scope="col" class="px-6 py-3 hidden lg:table-cell">Priority</th>
        <th scope="col" class="px-6 py-3 hidden lg:table-cell">Date</th>
        <th scope="col" class="px-6 py-3 hidden md:table-cell">Status</th>
        <th scope="col" class="px-6 py-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($announcements as $announcement)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
          <div class="text-base font-semibold">{{ $announcement->title }}</div>
          <div class="font-normal text-gray-500 text-xs mt-0.5">{{ Str::limit(strip_tags($announcement->content), 80) }}</div>
          <div class="font-normal text-gray-500 md:hidden mt-1 text-xs">
            {{ $announcement->category }}
            @if($announcement->is_featured)
            &bull; <span class="text-yellow-500">Featured</span>
            @endif
          </div>
        </th>
        <td class="px-6 py-4 hidden md:table-cell">
          <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
            {{ $announcement->category }}
          </span>
        </td>
        <td class="px-6 py-4 hidden lg:table-cell">
          @if($announcement->priority === 'high')
            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">High</span>
          @else
            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">Normal</span>
          @endif
        </td>
        <td class="px-6 py-4 hidden lg:table-cell text-xs">
          {{ $announcement->date ? \Carbon\Carbon::parse($announcement->date)->format('M d, Y') : '—' }}
        </td>
        <td class="px-6 py-4 hidden md:table-cell">
          @if($announcement->is_published)
            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Published</span>
          @else
            <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">Draft</span>
          @endif
        </td>
        <td class="px-6 py-4 w-52">
          <div class="flex items-center space-x-2">
            <a href="{{ route('maintenance.library-website.view-announcement', ['id' => $announcement->id]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-yellow-500 rounded-lg hover:bg-yellow-600 focus:ring-4 focus:outline-none focus:ring-yellow-300 dark:bg-yellow-400 dark:hover:bg-yellow-500 dark:focus:ring-yellow-800">View</a>
            @can(PermissionsEnum::EDIT_ANNOUNCEMENTS)
            <a href="{{ route('maintenance.library-website.edit-announcement', ['id' => $announcement->id]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">Edit</a>
            @endcan
            @can(PermissionsEnum::DELETE_ANNOUNCEMENTS)
            <button type="button" data-modal-target="delete-announcement-modal" data-modal-toggle="delete-announcement-modal" value="{{ $announcement->id }}" class="deleteAnnouncementBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800">Delete</button>
            @endcan
          </div>
        </td>
      </tr>
      @empty
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
        <td colspan="6" class="px-6 py-4 text-center">No announcements found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="p-4">
    {{ $announcements->withQueryString()->links('pagination::tailwind') }}
  </div>
</div>

{{-- Delete Modal --}}
<div id="delete-announcement-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-announcement-modal">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this announcement?</h3>
        <form action="{{ route('maintenance.library-website.delete-announcement') }}" method="POST">
          @csrf
          @method('DELETE')
          <input type="hidden" name="id" id="delete_announcement_id" value="" />
          <button data-modal-hide="delete-announcement-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="delete-announcement-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const deleteAnnouncementBtns = document.querySelectorAll('.deleteAnnouncementBtn');
    const deleteAnnouncementIDInput = document.getElementById('delete_announcement_id');

    if (deleteAnnouncementBtns.length > 0 && deleteAnnouncementIDInput) {
      deleteAnnouncementBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          deleteAnnouncementIDInput.value = this.value;
        });
      });
    }
  });
</script>
