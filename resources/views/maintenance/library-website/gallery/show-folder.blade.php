@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  {{-- Folder Details Card --}}
  <div class="w-full p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md mb-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
        📁 {{ $folder->name }}
      </h5>
      <div class="flex gap-2 mt-4 sm:mt-0">
        @can(PermissionsEnum::ADD_GALLERY)
        <a href="{{ route('maintenance.library-website.gallery.create-video', ['folder_id' => $folder->id]) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-green-800">
          + Add Video
        </a>
        @endcan
        <a href="{{ route('maintenance.library-website.gallery') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
          <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
          </svg>
          Back
        </a>
      </div>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      @if($folder->cover)
      <div>
        <img src="data:image/jpeg;base64,{{ $folder->cover }}" alt="{{ $folder->name }}" class="w-full h-40 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
      </div>
      @endif
      <div class="{{ $folder->cover ? 'md:col-span-2' : 'md:col-span-3' }}">
        <div class="flex flex-wrap gap-2 mb-3">
          <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-purple-900 dark:text-purple-300 capitalize">{{ $folder->type }}</span>
          <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-indigo-900 dark:text-indigo-300 capitalize">{{ $folder->category }}</span>
        </div>
        @if($folder->title)
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-1"><strong>Title:</strong> {{ $folder->title }}</p>
        @endif
        @if($folder->description)
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-1"><strong>Description:</strong> {{ $folder->description }}</p>
        @endif
        @if($folder->fb_url)
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-1"><strong>Facebook URL:</strong> <a href="{{ $folder->fb_url }}" target="_blank" class="text-primary-500 hover:underline">{{ $folder->fb_url }}</a></p>
        @endif
        @if($folder->album_date)
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-1"><strong>Album Date:</strong> {{ \Carbon\Carbon::parse($folder->album_date)->format('F d, Y') }}</p>
        @endif
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-1"><strong>Sort Order:</strong> {{ $folder->sort_order }}</p>
        <p class="text-gray-400 text-xs mt-2">Slug: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{{ $folder->slug }}</code></p>
      </div>
    </div>
  </div>

  {{-- Videos Table --}}
  <div class="w-full p-4 sm:p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <h5 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white mb-4">Videos in this Folder</h5>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    <form method="GET" action="{{ route('maintenance.library-website.gallery.show-folder') }}" class="m-2">
      <input type="hidden" name="id" value="{{ $folder->id }}">
      <label for="perPage" class="mr-2 text-sm font-medium text-gray-500">Show</label>
      <input type="number" name="perPage" id="perPage" min="1" max="500" onchange="this.form.submit()" value="{{ old('perPage', $perPage) }}" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-400 focus:border-primary-400 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
      <span class="ml-2 text-sm text-gray-600">entries per page</span>
    </form>

    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
      <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th scope="col" class="px-6 py-3">Title</th>
            <th scope="col" class="px-6 py-3 hidden md:table-cell">URL</th>
            <th scope="col" class="px-6 py-3 hidden lg:table-cell">Sort</th>
            <th scope="col" class="px-6 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($videos as $video)
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
              {{ $video->title }}
            </th>
            <td class="px-6 py-4 hidden md:table-cell">
              <a href="{{ $video->url }}" target="_blank" class="text-primary-500 hover:underline text-xs">{{ Str::limit($video->url, 50) }}</a>
            </td>
            <td class="px-6 py-4 hidden lg:table-cell text-xs">{{ $video->sort_order }}</td>
            <td class="px-6 py-4 w-48">
              <div class="flex items-center space-x-2">
                @can(PermissionsEnum::EDIT_GALLERY)
                <a href="{{ route('maintenance.library-website.gallery.edit-video', ['id' => $video->id]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">Edit</a>
                @endcan
                @can(PermissionsEnum::DELETE_GALLERY)
                <button type="button" data-modal-target="delete-video-modal" data-modal-toggle="delete-video-modal" value="{{ $video->id }}" class="deleteVideoBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800">Delete</button>
                @endcan
              </div>
            </td>
          </tr>
          @empty
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            <td colspan="4" class="px-6 py-4 text-center">No videos found in this folder.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
      <div class="p-4">
        {{ $videos->withQueryString()->links('pagination::tailwind') }}
      </div>
    </div>
  </div>
</div>

{{-- Delete Video Modal --}}
<div id="delete-video-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-video-modal">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this video?</h3>
        <form action="{{ route('maintenance.library-website.gallery.delete-video') }}" method="POST">
          @csrf
          @method('DELETE')
          <input type="hidden" name="id" id="delete_video_id" value="" />
          <button data-modal-hide="delete-video-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="delete-video-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const deleteVideoBtns = document.querySelectorAll('.deleteVideoBtn');
    const deleteVideoIDInput = document.getElementById('delete_video_id');

    if (deleteVideoBtns.length > 0 && deleteVideoIDInput) {
      deleteVideoBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          deleteVideoIDInput.value = this.value;
        });
      });
    }
  });
</script>
@endsection
