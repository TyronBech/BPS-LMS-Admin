@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="w-full p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Video Album</h5>
      <a href="{{ route('maintenance.library-website.gallery', ['tab' => 'video']) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500 mt-4 sm:mt-0">
        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
        </svg>
        Back
      </a>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    <form action="{{ route('maintenance.library-website.gallery.update-video-album') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl mx-auto">
      @csrf
      @method('PUT')
      <input type="hidden" name="id" value="{{ $album->id }}">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

        {{-- Name (required) --}}
        <div>
          <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Album Name <span class="text-red-500">*</span>
          </label>
          <input type="text" id="name" name="name" value="{{ old('name', $album->name) }}" required
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            placeholder="e.g. Video Events 2024">
          @error('name')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Title --}}
        <div>
          <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Display Title</label>
          <input type="text" id="title" name="title" value="{{ old('title', $album->title) }}"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            placeholder="Optional display title">
          @error('title')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Album Date --}}
        <div>
          <label for="album_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Album Date</label>
          <input type="date" id="album_date" name="album_date" value="{{ old('album_date', $album->album_date) }}"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          @error('album_date')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Facebook URL --}}
        <div>
          <label for="fb_url" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Facebook Album URL</label>
          <input type="url" id="fb_url" name="fb_url" value="{{ old('fb_url', $album->fb_url) }}"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            placeholder="https://facebook.com/media/...">
          @error('fb_url')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Sort Order --}}
        <div>
          <label for="sort_order" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Sort Order</label>
          <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $album->sort_order) }}" min="0" max="99999"
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
            <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $album->is_active ?? true) ? 'checked' : '' }}>
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Active</span>
          </label>
        </div>

        {{-- Description --}}
        <div class="md:col-span-2">
          <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
          <textarea id="description" name="description" rows="4"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            placeholder="Optional album description...">{{ old('description', $album->description) }}</textarea>
          @error('description')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
      </div>

      <div class="flex justify-end mt-6 gap-4">
        <a href="{{ route('maintenance.library-website.gallery', ['tab' => 'video']) }}" class="skip-loader py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">Cancel</a>
        <button type="submit" class="text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-400 dark:hover:bg-primary-500 focus:outline-none dark:focus:ring-primary-500">Update Video Album</button>
      </div>
    </form>
  </div>
</div>
@endsection
