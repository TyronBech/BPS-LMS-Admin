@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="w-full p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Video Folder</h5>
      <a href="{{ route('maintenance.library-website.gallery.show-video-album', ['id' => $folder->album_id]) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500 mt-4 sm:mt-0">
        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
        </svg>
        Back
      </a>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    <form action="{{ route('maintenance.library-website.gallery.update-video-folder') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl mx-auto">
      @csrf
      @method('PUT')
      <input type="hidden" name="id" value="{{ $folder->id }}">
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Name (required) --}}
        <div>
          <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Folder Name <span class="text-red-500">*</span>
          </label>
          <input type="text" id="name" name="name" value="{{ old('name', $folder->name) }}" required
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          @error('name')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Sort Order --}}
        <div>
          <label for="sort_order" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Sort Order</label>
          <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $folder->sort_order) }}" min="0" max="99999"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          @error('sort_order')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Thumbnail --}}
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="thumbnail">Thumbnail Image</label>
          <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" aria-describedby="thumbnail_help" id="thumbnail" name="thumbnail" type="file" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
          <div class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="thumbnail_help">Optional. Upload a new image to replace the current thumbnail.</div>
          @error('thumbnail')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Active Status --}}
        <div class="flex items-center mt-6">
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $folder->is_active ?? true) ? 'checked' : '' }}>
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Active</span>
          </label>
        </div>

        {{-- Description --}}
        <div class="md:col-span-2">
          <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
          <textarea id="description" name="description" rows="4"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">{{ old('description', $folder->description) }}</textarea>
          @error('description')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
      </div>

      {{-- Existing Videos Section --}}
      @if(isset($folder->items) && $folder->items->count() > 0)
      <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
          <div class="mb-4">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">Existing Videos</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Update properties of existing videos in this folder.</p>
          </div>
          <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
              @foreach($folder->items as $video)
                  <details class="group bg-gray-50 border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 shadow-sm video-item">
                      <summary class="flex justify-between items-center cursor-pointer font-medium text-gray-900 dark:text-white p-4 list-none [&::-webkit-details-marker]:hidden">
                          <span class="flex items-center gap-2">
                              <svg class="w-5 h-5 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                              {{ $video->title }}
                          </span>
                      </summary>
                      <div class="p-4 border-t border-gray-200 dark:border-gray-600">
                          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                              <div>
                                  <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Title <span class="text-red-500">*</span></label>
                                  <input type="text" name="existing_videos[{{ $video->id }}][title]" value="{{ old('existing_videos.'.$video->id.'.title', $video->title) }}" required class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                              </div>
                              <div>
                                  <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">URL <span class="text-red-500">*</span></label>
                                  <input type="url" name="existing_videos[{{ $video->id }}][url]" value="{{ old('existing_videos.'.$video->id.'.url', $video->url) }}" required class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                              </div>
                              <div class="md:col-span-2">
                                  <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
                                  <textarea name="existing_videos[{{ $video->id }}][description]" rows="2" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">{{ old('existing_videos.'.$video->id.'.description', $video->description) }}</textarea>
                              </div>
                              <div>
                                  <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Video Provider</label>
                                  <input type="text" name="existing_videos[{{ $video->id }}][video_provider]" value="{{ old('existing_videos.'.$video->id.'.video_provider', $video->video_provider) }}" placeholder="e.g. YouTube, Vimeo" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                              </div>
                              <div>
                                  <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Thumbnail URL</label>
                                  <input type="url" name="existing_videos[{{ $video->id }}][thumbnail_url]" value="{{ old('existing_videos.'.$video->id.'.thumbnail_url', $video->thumbnail_url) }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                              </div>
                              <div>
                                  <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Duration (seconds)</label>
                                  <input type="number" name="existing_videos[{{ $video->id }}][duration]" value="{{ old('existing_videos.'.$video->id.'.duration', $video->duration) }}" min="0" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                              </div>
                              <div>
                                  <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Sort Order</label>
                                  <input type="number" name="existing_videos[{{ $video->id }}][sort_order]" value="{{ old('existing_videos.'.$video->id.'.sort_order', $video->sort_order) }}" min="0" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                              </div>
                              <div class="md:col-span-2 flex items-center">
                                  <label class="relative inline-flex items-center cursor-pointer">
                                      <input type="checkbox" name="existing_videos[{{ $video->id }}][is_featured]" value="1" class="sr-only peer" {{ old('existing_videos.'.$video->id.'.is_featured', $video->is_featured) ? 'checked' : '' }}>
                                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-500 peer-checked:bg-primary-600"></div>
                                      <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Is Featured</span>
                                  </label>
                              </div>
                          </div>
                      </div>
                  </details>
              @endforeach
          </div>
      </div>
      @endif

      {{-- Add Videos Section --}}
      <div class="mt-8 mb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
              <div>
                  <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add New Videos</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">You can optionally add videos directly to this folder.</p>
              </div>
              <button type="button" id="add-video-btn" class="mt-4 sm:mt-0 text-white bg-green-500 hover:bg-green-600 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-green-600 dark:hover:bg-green-700 focus:outline-none dark:focus:ring-green-800 flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                  Add Video
              </button>
          </div>
          <div id="videos-container" class="space-y-4">
              <!-- Dynamic video items will be appended here -->
          </div>
      </div>

      <div class="flex justify-end mt-6 gap-4">
        <a href="{{ route('maintenance.library-website.gallery.show-video-album', ['id' => $folder->album_id]) }}" class="skip-loader py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">Cancel</a>
        <button type="submit" class="text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-400 dark:hover:bg-primary-500 focus:outline-none dark:focus:ring-primary-500">Update Folder</button>
      </div>
    </form>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let videoCount = 0;
        const container = document.getElementById('videos-container');
        const addBtn = document.getElementById('add-video-btn');

        addBtn.addEventListener('click', function() {
            const index = videoCount++;
            const template = `
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 relative video-item shadow-sm">
                    <button type="button" class="remove-video-btn absolute top-3 right-3 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 bg-white dark:bg-gray-800 rounded-full p-1 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pr-8">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="new_videos[${index}][title]" required class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">URL <span class="text-red-500">*</span></label>
                            <input type="url" name="new_videos[${index}][url]" required class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
                            <textarea name="new_videos[${index}][description]" rows="2" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white"></textarea>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Video Provider</label>
                            <input type="text" name="new_videos[${index}][video_provider]" placeholder="e.g. YouTube, Vimeo" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Thumbnail URL</label>
                            <input type="url" name="new_videos[${index}][thumbnail_url]" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Duration (seconds)</label>
                            <input type="number" name="new_videos[${index}][duration]" min="0" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Sort Order</label>
                            <input type="number" name="new_videos[${index}][sort_order]" min="0" value="0" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="md:col-span-2 flex items-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="new_videos[${index}][is_featured]" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-500 peer-checked:bg-primary-600"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Is Featured</span>
                            </label>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
        });

        container.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.remove-video-btn');
            if (removeBtn) {
                removeBtn.closest('.video-item').remove();
            }
        });
    });
</script>
@endsection
