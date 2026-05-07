@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="w-full p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Gallery Folder</h5>
      <a href="{{ route('maintenance.library-website.gallery') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500 mt-4 sm:mt-0">
        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
        </svg>
        Back
      </a>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    <form action="{{ route('maintenance.library-website.gallery.update-folder') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl mx-auto">
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

        {{-- Title --}}
        <div>
          <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Display Title</label>
          <input type="text" id="title" name="title" value="{{ old('title', $folder->title) }}"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          @error('title')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Type (required) --}}
        <div>
          <label for="type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Type <span class="text-red-500">*</span>
          </label>
          <select id="type" name="type" required
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            <option value="folder" {{ old('type', $folder->type) === 'folder' ? 'selected' : '' }}>Folder</option>
            <option value="album" {{ old('type', $folder->type) === 'album' ? 'selected' : '' }}>Album</option>
          </select>
          @error('type')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Category (required) --}}
        <div>
          <label for="category" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Category <span class="text-red-500">*</span>
          </label>
          <select id="category" name="category" required
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            <option value="video" {{ old('category', $folder->category) === 'video' ? 'selected' : '' }}>Video</option>
            <option value="photo" {{ old('category', $folder->category) === 'photo' ? 'selected' : '' }}>Photo</option>
          </select>
          @error('category')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Parent Folder --}}
        <div>
          <label for="parent_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Parent Folder</label>
          <select id="parent_id" name="parent_id"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            <option value="">— None (Top Level) —</option>
            @foreach($parentFolders as $parent)
            <option value="{{ $parent->id }}" {{ old('parent_id', $folder->parent_id) == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
            @endforeach
          </select>
          @error('parent_id')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Album Date --}}
        <div>
          <label for="album_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Album Date</label>
          <input type="date" id="album_date" name="album_date" value="{{ old('album_date', $folder->album_date) }}"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          @error('album_date')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Facebook URL --}}
        <div>
          <label for="fb_url" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Facebook Album URL</label>
          <input type="url" id="fb_url" name="fb_url" value="{{ old('fb_url', $folder->fb_url) }}"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            placeholder="https://facebook.com/media/...">
          @error('fb_url')
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

        {{-- Cover --}}
        <div>
          <label for="cover" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Cover Image <span class="text-xs text-gray-500">(Leave blank to keep current)</span>
          </label>
          @if($folder->cover)
          <div class="mb-2">
            <img src="data:image/jpeg;base64,{{ $folder->cover }}" alt="Current cover" class="w-32 h-20 object-cover rounded-lg border border-gray-200">
            <p class="text-xs text-gray-500 mt-1">Current cover</p>
          </div>
          @endif
          <input type="file" id="cover" name="cover" accept="image/*"
            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
          @error('cover')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
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

      <div class="flex justify-end mt-6 gap-4">
        <a href="{{ route('maintenance.library-website.gallery') }}" class="skip-loader py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">Cancel</a>
        <button type="submit" class="text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-400 dark:hover:bg-primary-500 focus:outline-none dark:focus:ring-primary-500">Update Folder</button>
      </div>
    </form>
  </div>
</div>
@endsection
