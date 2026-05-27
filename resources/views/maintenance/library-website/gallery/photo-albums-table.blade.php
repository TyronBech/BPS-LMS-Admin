@use('App\Enum\PermissionsEnum')
<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">Album Name</th>
                <th scope="col" class="px-6 py-3">Title</th>
                <th scope="col" class="px-6 py-3 text-center">Status</th>
                <th scope="col" class="px-6 py-3 text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($photoAlbums as $album)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        {{ $album->name }}
                    </th>
                    <td class="px-6 py-4">
                        {{ $album->title ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($album->is_active)
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Active</span>
                        @else
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @can(PermissionsEnum::EDIT_GALLERY)
                        <a href="{{ route('maintenance.library-website.gallery.edit-photo-album', ['id' => $album->id]) }}" class="font-medium text-primary-600 dark:text-primary-500 hover:underline mr-3">Edit</a>
                        @endcan
                        
                        @can(PermissionsEnum::DELETE_GALLERY)
                        <button type="button" data-modal-target="deleteModal{{ $album->id }}" data-modal-toggle="deleteModal{{ $album->id }}" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
                        @endcan
                    </td>
                </tr>

                @can(PermissionsEnum::DELETE_GALLERY)
                <!-- Delete Modal -->
                <div id="deleteModal{{ $album->id }}" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                    <div class="relative p-4 w-full max-w-md max-h-full">
                        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                            <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="deleteModal{{ $album->id }}">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                            <div class="p-4 md:p-5 text-center">
                                <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this photo album?</h3>
                                <form action="{{ route('maintenance.library-website.gallery.delete-photo-album') }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="id" value="{{ $album->id }}">
                                    <button data-modal-hide="deleteModal{{ $album->id }}" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center me-2">
                                        Yes, I'm sure
                                    </button>
                                </form>
                                <button data-modal-hide="deleteModal{{ $album->id }}" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No, cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endcan
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                        No photo albums found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $photoAlbums->links() }}
</div>
