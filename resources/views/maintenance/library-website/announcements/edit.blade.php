@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="w-full p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Announcement</h5>
      <a href="{{ request('return_to', route('maintenance.library-website.announcements')) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500 mt-4 sm:mt-0">
        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
        </svg>
        Back
      </a>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    <form action="{{ route('maintenance.library-website.update-announcement') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl mx-auto">
      @csrf
      @method('PUT')
      <input type="hidden" name="id" value="{{ $announcement->id }}">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Title (required) --}}
        <div class="md:col-span-2">
          <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Title <span class="text-red-500">*</span>
          </label>
          <input type="text" id="title" name="title" value="{{ old('title', $announcement->title) }}" required
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            placeholder="Enter announcement title">
          @error('title')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Category (required) --}}
        <div>
          <label for="category" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Category <span class="text-red-500">*</span>
          </label>
          <input type="text" id="category" name="category" value="{{ old('category', $announcement->category) }}" required
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            placeholder="e.g. Notice, News, Event">
          @error('category')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Priority (required) --}}
        <div>
          <label for="priority" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Priority <span class="text-red-500">*</span>
          </label>
          <select id="priority" name="priority" required
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
            <option value="normal" {{ old('priority', $announcement->priority) === 'normal' ? 'selected' : '' }}>Normal</option>
            <option value="high" {{ old('priority', $announcement->priority) === 'high' ? 'selected' : '' }}>High</option>
          </select>
          @error('priority')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Date --}}
        <div>
          <label for="date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date</label>
          <input type="date" id="date" name="date" value="{{ old('date', $announcement->date) }}"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          @error('date')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Image --}}
        <div>
          <label for="image" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Image <span class="text-xs text-gray-500">(Leave blank to keep current)</span>
          </label>
          @if($announcement->image)
          <div class="mb-2">
            <img src="data:image/jpeg;base64,{{ $announcement->image }}" alt="Current image" class="w-32 h-20 object-cover rounded-lg border border-gray-200">
            <p class="text-xs text-gray-500 mt-1">Current image</p>
          </div>
          @endif
          <input type="file" id="image" name="image" accept="image/*"
            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
          @error('image')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Content (required) --}}
        <div class="md:col-span-2">
          <label for="content" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            Content <span class="text-red-500">*</span>
          </label>
          <textarea id="content" name="content" rows="8" required
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            placeholder="Enter announcement content...">{{ old('content', $announcement->content) }}</textarea>
          @error('content')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Quote --}}
        <div class="md:col-span-2">
          <label for="quote" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Quote</label>
          <input type="text" id="quote" name="quote" value="{{ old('quote', $announcement->quote) }}"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
            placeholder="Optional inspirational quote or tagline">
          @error('quote')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Toggles --}}
        <div class="flex items-center space-x-6">
          <div class="flex items-center">
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $announcement->is_featured) ? 'checked' : '' }}
              class="w-4 h-4 text-primary-500 bg-gray-100 border-gray-300 rounded focus:ring-primary-400 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
            <label for="is_featured" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Featured</label>
          </div>
          <div class="flex items-center">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published', $announcement->is_published) ? 'checked' : '' }}
              class="w-4 h-4 text-primary-500 bg-gray-100 border-gray-300 rounded focus:ring-primary-400 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
            <label for="is_published" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Published</label>
          </div>
        </div>
      </div>

      <div class="flex justify-end mt-6 gap-4">
        <a href="{{ route('maintenance.library-website.announcements') }}" class="skip-loader py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">Cancel</a>
        <button type="submit" class="text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-primary-400 dark:hover:bg-primary-500 focus:outline-none dark:focus:ring-primary-500">Update Announcement</button>
      </div>
    </form>
  </div>
</div>
@endsection
