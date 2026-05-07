@use('App\Enum\PermissionsEnum')
<form method="GET" action="{{ route('maintenance.library-website.gallery') }}" class="justify-end m-2 w-full sm:w-auto">
  <input type="hidden" name="search" value="{{ request('search', '') }}">
  <label for="perPage" class="mr-2 text-sm font-medium text-gray-500">Show</label>
  <input type="number" name="perPage" id="perPage" min="1" max="500" onchange="this.form.submit()" value="{{ old('perPage', $perPage) }}" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-400 focus:border-primary-400 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
  <span class="ml-2 text-sm text-gray-600">entries per page</span>
</form>
<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
      <tr>
        <th scope="col" class="px-6 py-3">Name</th>
        <th scope="col" class="px-6 py-3 hidden md:table-cell">Type</th>
        <th scope="col" class="px-6 py-3 hidden md:table-cell">Category</th>
        <th scope="col" class="px-6 py-3 hidden lg:table-cell">Items</th>
        <th scope="col" class="px-6 py-3 hidden lg:table-cell">Sort</th>
        <th scope="col" class="px-6 py-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($folders as $folder)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
          <div class="flex items-center gap-2">
            @if($folder->cover)
            <img src="data:image/jpeg;base64,{{ $folder->cover }}" alt="{{ $folder->name }}" class="w-10 h-10 rounded-lg object-cover border border-gray-200 flex-shrink-0">
            @else
            <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                <path d="M18 0H2a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2Zm-5.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm4.376 10.481A1 1 0 0 1 16 15H4a1 1 0 0 1-.895-1.447l3.5-7A1 1 0 0 1 7.468 6a.965.965 0 0 1 .9.5l2.775 4.757 1.546-1.887a1 1 0 0 1 1.618.1l2.541 4a1 1 0 0 1 .028 1.011Z"/>
              </svg>
            </div>
            @endif
            <div>
              <div class="text-base font-semibold">{{ $folder->name }}</div>
              @if($folder->title)
              <div class="font-normal text-gray-500 text-xs">{{ $folder->title }}</div>
              @endif
              @if($folder->description)
              <div class="font-normal text-gray-400 text-xs mt-0.5 md:hidden">{{ Str::limit($folder->description, 50) }}</div>
              @endif
            </div>
          </div>
        </th>
        <td class="px-6 py-4 hidden md:table-cell">
          <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-purple-900 dark:text-purple-300 capitalize">
            {{ $folder->type }}
          </span>
        </td>
        <td class="px-6 py-4 hidden md:table-cell">
          <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-indigo-900 dark:text-indigo-300 capitalize">
            {{ $folder->category }}
          </span>
        </td>
        <td class="px-6 py-4 hidden lg:table-cell text-xs">
          <div>{{ $folder->videos_count }} video(s)</div>
          <div class="text-gray-400">{{ $folder->children_count }} sub-folder(s)</div>
        </td>
        <td class="px-6 py-4 hidden lg:table-cell text-xs">{{ $folder->sort_order }}</td>
        <td class="px-6 py-4 w-52">
          <div class="flex items-center space-x-2">
            <a href="{{ route('maintenance.library-website.gallery.show-folder', ['id' => $folder->id]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-yellow-500 rounded-lg hover:bg-yellow-600 focus:ring-4 focus:outline-none focus:ring-yellow-300 dark:bg-yellow-400 dark:hover:bg-yellow-500 dark:focus:ring-yellow-800">View</a>
            @can(PermissionsEnum::EDIT_GALLERY)
            <a href="{{ route('maintenance.library-website.gallery.edit-folder', ['id' => $folder->id]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">Edit</a>
            @endcan
            @can(PermissionsEnum::DELETE_GALLERY)
            <button type="button" data-modal-target="delete-folder-modal" data-modal-toggle="delete-folder-modal" value="{{ $folder->id }}" class="deleteFolderBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800">Delete</button>
            @endcan
          </div>
        </td>
      </tr>
      @empty
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
        <td colspan="6" class="px-6 py-4 text-center">No folders found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="p-4">
    {{ $folders->withQueryString()->links('pagination::tailwind') }}
  </div>
</div>

{{-- Delete Folder Modal --}}
<div id="delete-folder-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-folder-modal">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-2 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this folder?</h3>
        <p class="mb-5 text-sm text-red-500">This will also delete all sub-folders and videos inside.</p>
        <form action="{{ route('maintenance.library-website.gallery.delete-folder') }}" method="POST">
          @csrf
          @method('DELETE')
          <input type="hidden" name="id" id="delete_folder_id" value="" />
          <button data-modal-hide="delete-folder-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="delete-folder-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const deleteFolderBtns = document.querySelectorAll('.deleteFolderBtn');
    const deleteFolderIDInput = document.getElementById('delete_folder_id');

    if (deleteFolderBtns.length > 0 && deleteFolderIDInput) {
      deleteFolderBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          deleteFolderIDInput.value = this.value;
        });
      });
    }
  });
</script>
