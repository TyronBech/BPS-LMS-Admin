@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  
  <div class="w-full p-4 sm:p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Folders in "{{ $album->name }}"</h5>
      <div class="flex gap-4">
          <a href="{{ route('maintenance.library-website.gallery', ['tab' => 'video']) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-gray-900 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700">
            Back to Albums
          </a>
          @can(PermissionsEnum::ADD_GALLERY)
          <a href="{{ route('maintenance.library-website.gallery.create-video-folder', ['album_id' => $album->id]) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
            Add Folder
          </a>
          @endcan
      </div>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">
    
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Folder Name</th>
                    <th scope="col" class="px-6 py-3">Description</th>
                    <th scope="col" class="px-6 py-3 text-center">Items</th>
                    <th scope="col" class="px-6 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($folders as $folder)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            <a href="{{ route('maintenance.library-website.gallery.show-video-folder', ['id' => $folder->id]) }}" class="text-primary-600 hover:underline">
                                {{ $folder->name }}
                            </a>
                        </th>
                        <td class="px-6 py-4">
                            {{ Str::limit($folder->description ?? '-', 50) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium inline-flex items-center px-2.5 py-0.5 rounded me-2 dark:bg-gray-700 dark:text-gray-400 border border-gray-500">
                                {{ $folder->items_count }} Videos
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @can(PermissionsEnum::EDIT_GALLERY)
                            <a href="{{ route('maintenance.library-website.gallery.edit-video-folder', ['id' => $folder->id]) }}" class="font-medium text-primary-600 dark:text-primary-500 hover:underline mr-3">Edit</a>
                            @endcan
                            
                            @can(PermissionsEnum::DELETE_GALLERY)
                            <button type="button" data-modal-target="deleteModal{{ $folder->id }}" data-modal-toggle="deleteModal{{ $folder->id }}" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
                            @endcan
                        </td>
                    </tr>

                    @can(PermissionsEnum::DELETE_GALLERY)
                    <!-- Delete Modal -->
                    <div id="deleteModal{{ $folder->id }}" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                        <div class="relative p-4 w-full max-w-md max-h-full">
                            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                                <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="deleteModal{{ $folder->id }}">
                                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                                    </svg>
                                    <span class="sr-only">Close modal</span>
                                </button>
                                <div class="p-4 md:p-5 text-center">
                                    <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this folder?</h3>
                                    <form action="{{ route('maintenance.library-website.gallery.delete-video-folder') }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="id" value="{{ $folder->id }}">
                                        <button data-modal-hide="deleteModal{{ $folder->id }}" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center me-2">
                                            Yes, I'm sure
                                        </button>
                                    </form>
                                    <button data-modal-hide="deleteModal{{ $folder->id }}" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">No, cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endcan
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No folders found in this album.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $folders->links() }}
    </div>
  </div>
</div>
@endsection
