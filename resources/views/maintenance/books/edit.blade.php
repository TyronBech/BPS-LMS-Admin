@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="w-full p-4 sm:p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Material</h5>
      <a href="{{ request('return_to', route('maintenance.books')) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500 mt-4 sm:mt-0">
        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
        </svg>
        Back
      </a>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">
    <form action="{{ route('maintenance.update-book') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <input type="hidden" name="id" value="{{ $book->id }}">
      <div class="space-y-8 mt-6">
        <!-- Section 1: Core Information -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
          <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Basic Information</h6>
          </div>
          <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="md:col-span-2 lg:col-span-3">
              <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Title:</label>
              <input type="text" id="title" name="title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Material Title" value="{{ old('title', $book->title) }}" required>
              @error('title') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
              <label for="book_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Material Type:</label>
              <select id="book_type" name="book_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
                @foreach($book_types as $value)
                <option value="{{ $value }}" {{ old('book_type', $book->book_type) == $value ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
              @error('book_type') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
              <label for="category" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category:</label>
              <select id="category" name="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
                <option value="" disabled>Choose a category</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" data-category-type="{{ $category->category_type }}" {{ old('category', $book->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
              </select>
              @error('category') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
              <label for="accession" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Accession Number:</label>
              <input type="text" id="accession" name="accession" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., FIL0123456789" value="{{ old('accession', $book->accession) }}" required>
              @error('accession') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
              <label for="call_number" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Call Number:</label>
              <input type="text" id="call_number" name="call_number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 192.000" value="{{ old('call_number', $book->call_number) }}">
            </div>
            <div id="isbn-container">
              <label for="isbn" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">ISBN:</label>
              <input type="text" id="isbn" name="isbn" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 9789712345678" value="{{ old('isbn', $book->isbn) }}">
            </div>
            <div id="edition-container">
              <label for="edition" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Edition:</label>
              <input type="text" id="edition" name="edition" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 1st Edition" value="{{ old('edition', $book->edition) }}">
            </div>
            <div id="subject-container" class="md:col-span-2 lg:col-span-3">
              <label for="subject_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Subject:</label>
              <select id="subject_id" name="subject_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                <option value="">No subject linked</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" {{ old('subject_id', $linkedSubjectId) == $subject->id ? 'selected' : '' }}>
                  {{ $subject->name }}{{ $subject->ddc ? ' (DDC: '.$subject->ddc.')' : '' }}{{ $subject->accessCodes->isNotEmpty() ? ' - '.$subject->accessCodes->pluck('access_code')->implode(', ') : '' }}
                </option>
                @endforeach
              </select>
              <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 italic">Configure in Subject Maintenance.</p>
              @error('subject_id') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>

        <!-- Section 2: Authors Information -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
          <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Authors & Contributors</h6>
          </div>
          <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="authors_main" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Main Author:</label>
              <input type="text" id="authors_main" name="authors[Main author]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Primary Author" value="{{ old('authors.Main author', $book->authors['Main author'] ?? '') }}">
            </div>
            <div>
              <label for="authors_corporate" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Corporate Author:</label>
              <input type="text" id="authors_corporate" name="authors[Corporate author]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Organization/Company" value="{{ old('authors.Corporate author', $book->authors['Corporate author'] ?? '') }}">
            </div>
            <div>
              <label for="authors_added" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Added Authors:</label>
              <input type="text" id="authors_added" name="authors[Added authors]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Co-authors" value="{{ old('authors.Added authors', $book->authors['Added authors'] ?? '') }}">
            </div>
            <div>
              <label for="authors_contributors" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Contributors:</label>
              <input type="text" id="authors_contributors" name="authors[Contributors]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Editors, Translators, etc." value="{{ old('authors.Contributors', $book->authors['Contributors'] ?? '') }}">
            </div>
            @error('authors.*') <p class="md:col-span-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
          </div>
        </div>

        <!-- Section 3: Material Description -->
        <div id="description-container" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
          <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Material Description</h6>
          </div>
          <div class="p-5 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label for="desc_description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Physical Description:</label>
                <textarea id="desc_description" name="description[Description]" rows="2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Physical characteristics">{{ old('description.Description', $book->description['Description'] ?? '') }}</textarea>
              </div>
              <div>
                <label for="desc_extent" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Extent (Required):</label>
                <input type="text" id="desc_extent" name="description[Extent]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g., 200 pages" value="{{ old('description.Extent', $book->description['Extent'] ?? '') }}">
              </div>
              <div>
                <label for="desc_acc_material" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Acc Material:</label>
                <input type="text" id="desc_acc_material" name="description[Acc Material]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Accompanying material" value="{{ old('description.Acc Material', $book->description['Acc Material'] ?? '') }}">
              </div>
              <div class="md:col-span-2">
                <label for="desc_content_notes" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Content Notes:</label>
                <textarea id="desc_content_notes" name="description[Content notes]" rows="2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Table of contents, etc.">{{ old('description.Content notes', $book->description['Content notes'] ?? '') }}</textarea>
              </div>
              <div class="md:col-span-2">
                <label for="desc_abstract" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Abstract:</label>
                <textarea id="desc_abstract" name="description[Abstract]" rows="2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Summary or abstract">{{ old('description.Abstract', $book->description['Abstract'] ?? '') }}</textarea>
              </div>
              <div class="md:col-span-2">
                <label for="desc_reviews" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Reviews:</label>
                <textarea id="desc_reviews" name="description[Reviews]" rows="2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Material reviews">{{ old('description.Reviews', $book->description['Reviews'] ?? '') }}</textarea>
              </div>
            </div>
            @error('description.*') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
          </div>
        </div>

        <!-- Section 4: Publishing & Logistics -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
          <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Publishing & Logistics</h6>
          </div>
          <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
              <label for="publisher" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Publisher:</label>
              <input type="text" id="publisher" name="publisher" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., National Library" value="{{ old('publisher', $book->publisher) }}">
              @error('publisher') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
              <label for="copyright" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Copyright Year:</label>
              <input type="text" id="copyright" name="copyright" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 2026" value="{{ old('copyright', $book->copyrights) }}">
              @error('copyright') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>
            <div id="publication-container">
              <label for="publication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Place of Publication:</label>
              <input type="text" id="publication" name="publication" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., Manila, Philippines" value="{{ old('publication', $book->place_of_publication) }}">
            </div>
            <div>
              <label for="location" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Location:</label>
              <input type="text" id="location" name="location" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., Section A, Shelf 1" value="{{ old('location', $book->location) }}">
            </div>
            <div id="digital-copy-container" class="lg:col-span-2">
              <label for="digital_copy_url" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Digital Copy URL:</label>
              <input type="url" id="digital_copy_url" name="digital_copy_url" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="https://example.com" value="{{ old('digital_copy_url', $book->digital_copy_url) }}">
            </div>
          </div>
        </div>

        <!-- Section 5: Status & Assets -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
          <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Status & Media</h6>
          </div>
          <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
              <label for="remarks" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Remarks:</label>
              <select id="remarks" name="remarks" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
                @foreach($remarks as $value)
                <option value="{{ $value }}" {{ old('remarks', $book->remarks) == $value ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label for="availability" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Availability:</label>
              <select id="availability" name="availability" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
                @foreach($availability as $value)
                <option value="{{ $value }}" {{ old('availability', $book->availability_status) == $value ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
              <input type="hidden" id="availability_hidden" name="availability" disabled>
            </div>
            <div>
              <label for="condition" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Condition:</label>
              <select id="condition" name="condition" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
                @foreach($condition as $value)
                <option value="{{ $value }}" {{ old('condition', $book->condition_status) == $value ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="cover_image">Cover Image:</label>
              <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-700 focus:outline-none dark:border-gray-600 dark:placeholder-gray-400" id="cover_image" name="cover_image" type="file">
            </div>
          </div>
        </div>
      </div>
      <div class="flex flex-col sm:flex-row justify-end mt-6 gap-2">
        <button type="submit" class="text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">Update Material</button>
        <button data-modal-target="copy-book-modal" data-modal-toggle="copy-book-modal" type="button" class="skip-loader text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">Create New Copy</button>
      </div>
    </form>
  </div>
</div>

<!-- Create Copy Modal -->
<div id="copy-book-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-2xl max-h-full">
    <!-- Modal content -->
    <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          New Copy of {{ $book->title }}
        </h3>
        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="copy-book-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <!-- Modal body -->
      <form action="{{ route('maintenance.copy-book') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="p-4 md:p-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="copy_book_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Material Type:</label>
              <select id="copy_book_type" name="book_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                @foreach($book_types as $value)
                <option value="{{ $value }}" {{ $value == $book->book_type ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label for="copy_edition" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Edition:</label>
              <input type="text" id="copy_edition" name="edition" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g., 1st Edition" value="{{ $book->edition }}">
            </div>
            <div class="md:col-span-2">
              <label for="copy_accession" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Accession Number:</label>
              <input type="text" id="copy_accession" name="accession" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g., FIL0123456789" required>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Add multiple by separating with a comma.</p>
              @error('accession')
              <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
              @enderror
            </div>
            <div class="md:col-span-2">
              <label for="copy_subject_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Subject:</label>
              <select id="copy_subject_id" name="subject_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                <option value="">No subject linked</option>
                @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" {{ old('subject_id', $linkedSubjectId) == $subject->id ? 'selected' : '' }}>
                  {{ $subject->name }}{{ $subject->ddc ? ' (DDC: '.$subject->ddc.')' : '' }}{{ $subject->accessCodes->isNotEmpty() ? ' - '.$subject->accessCodes->pluck('access_code')->implode(', ') : '' }}
                </option>
                @endforeach
              </select>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Create or edit subjects and access codes in Subject Maintenance.</p>
              @error('subject_id')
              <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
              @enderror
            </div>
            <div>
              <label for="copy_remarks" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Remarks:</label>
              <select id="copy_remarks" name="remarks" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                @foreach($remarks as $value)
                <option value="{{ $value }}" {{ $value == $book->remarks ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label for="copy_availability" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Availability:</label>
              <select id="copy_availability" name="availability" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                @foreach($availability as $value)
                <option value="{{ $value }}" {{ $value == $book->availability_status ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
              <input type="hidden" id="copy_availability_hidden" name="availability" disabled>
            </div>
            <div>
              <label for="copy_condition" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Condition:</label>
              <select id="copy_condition" name="condition" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                @foreach($condition as $value)
                <option value="{{ $value }}" {{ $value == $book->condition_status ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div>
            <input type="hidden" name="title" value="{{ $book->title }}">
            @if(is_array($book->authors))
              @foreach($book->authors as $key => $value)
                <input type="hidden" name="authors[{{ $key }}]" value="{{ $value }}">
              @endforeach
            @endif
            @if(is_array($book->description))
              @foreach($book->description as $key => $value)
                <input type="hidden" name="description[{{ $key }}]" value="{{ $value }}">
              @endforeach
            @endif
            <input type="hidden" name="publication" value="{{ $book->place_of_publication }}">
            <input type="hidden" name="publisher" value="{{ $book->publisher }}">
            <input type="hidden" name="copyright" value="{{ $book->copyrights }}">
            <input type="hidden" name="digital_copy_url" value="{{ $book->digital_copy_url }}">
            <input type="hidden" id="copy_category" name="category" value="{{ $book->category_id }}">
            <input type="hidden" name="call_number" value="{{ $book->call_number }}">
            <input type="hidden" name="isbn" value="{{ $book->isbn }}">
          </div>
        </div>
        <!-- Modal footer -->
        <div class="flex items-center justify-end p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
          <button type="submit" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">Submit</button>
          <button data-modal-hide="copy-book-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-50 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script type="application/json" id="book-categories-data">
  {!! $categories->toJson() !!}
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const bookTypeSelect = document.getElementById('book_type');
    const copyBookTypeSelect = document.getElementById('copy_book_type');
    const copyCategoryInput = document.getElementById('copy_category');
    const remarksSelect = document.getElementById('remarks');
    const availabilitySelect = document.getElementById('availability');
    const availabilityHidden = document.getElementById('availability_hidden');
    const categoriesData = document.getElementById('book-categories-data');
    const categories = categoriesData ? JSON.parse(categoriesData.textContent || '[]') : [];

    const categoriesById = {};
    categories.forEach(function(category) {
      if (category && category.id !== undefined && category.id !== null) {
        categoriesById[String(category.id)] = category;
      }
    });

    function getCategoryById(categoryId) {
      return categoriesById[String(categoryId)] || null;
    }

    function syncCategoryOptionsToBookType() {
      if (!categorySelect || !bookTypeSelect) return;

      const selectedBookType = bookTypeSelect.value;
      let firstMatchingCategory = '';

      Array.from(categorySelect.options).forEach(function(option) {
        if (!option.value) return;

        const category = getCategoryById(option.value);
        const matchesBookType = !selectedBookType || !category || category.category_type === selectedBookType;

        option.disabled = !matchesBookType;
        option.hidden = !matchesBookType;

        if (matchesBookType && !firstMatchingCategory) {
          firstMatchingCategory = option.value;
        }
      });

      if (categorySelect.value) {
        const selectedCategory = getCategoryById(categorySelect.value);
        if (selectedBookType && selectedCategory && selectedCategory.category_type !== selectedBookType) {
          categorySelect.value = firstMatchingCategory || '';
        }
      } else if (selectedBookType && firstMatchingCategory) {
        categorySelect.value = firstMatchingCategory;
      }
    }

    function syncBookTypeFromCategory() {
      if (!categorySelect || !bookTypeSelect) return;

      const selectedCategory = getCategoryById(categorySelect.value);
      if (!selectedCategory) return;

      if (selectedCategory.category_type && bookTypeSelect.value !== selectedCategory.category_type) {
        bookTypeSelect.value = selectedCategory.category_type;
      }

      syncCategoryOptionsToBookType();
      syncCopyCategoryFromBookType();
    }

    function syncCopyCategoryFromBookType() {
      if (!copyBookTypeSelect || !copyCategoryInput) return;

      const selectedBookType = copyBookTypeSelect.value;
      const matchingCategory = categories.find(function(category) {
        return category && category.category_type === selectedBookType;
      });

      if (matchingCategory) {
        copyCategoryInput.value = matchingCategory.id;
      }
    }

    function applyAvailabilityRule() {
      if (!availabilitySelect || !availabilityHidden || !remarksSelect) return;
      const rv = remarksSelect.value;
      if (rv && rv !== 'On Shelf') {
        availabilitySelect.value = 'Unavailable';
        availabilitySelect.setAttribute('disabled', 'disabled');
        availabilityHidden.value = 'Unavailable';
        availabilityHidden.removeAttribute('disabled');
      } else {
        availabilitySelect.value = 'Available';
        availabilitySelect.removeAttribute('disabled');
        availabilityHidden.setAttribute('disabled', 'disabled');
      }
    }

    function restructureFormByBookType() {
      const selectedType = bookTypeSelect.value;
      const isNonPrint = selectedType === 'Non-print';

      const containersToToggle = [
        'isbn-container',
        'edition-container',
        'subject-container',
        'description-container',
        'publication-container',
        'digital-copy-container'
      ];

      containersToToggle.forEach(id => {
        const container = document.getElementById(id);
        if (container) {
          if (isNonPrint) {
            container.classList.add('hidden');
          } else {
            container.classList.remove('hidden');
          }
        }
      });
    }

    if (categorySelect) {
      categorySelect.addEventListener('change', syncBookTypeFromCategory);
    }

    if (bookTypeSelect) {
      bookTypeSelect.addEventListener('change', function() {
        syncCategoryOptionsToBookType();
        restructureFormByBookType();
      });
    }

    if (copyBookTypeSelect) {
      copyBookTypeSelect.addEventListener('change', syncCopyCategoryFromBookType);
    }

    if (remarksSelect) {
      remarksSelect.addEventListener('change', applyAvailabilityRule);
    }

    if (categorySelect && categorySelect.value) {
      syncBookTypeFromCategory();
    } else {
      syncCategoryOptionsToBookType();
    }

    restructureFormByBookType();
    syncCopyCategoryFromBookType();
    applyAvailabilityRule();
  });
</script>
@endsection