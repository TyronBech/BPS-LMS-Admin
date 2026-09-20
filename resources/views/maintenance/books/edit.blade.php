@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="w-full p-4 sm:p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Material</h5>
      <a href="{{ request('return_to', route('maintenance.books')) }}" class="skip-loader inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500 mt-4 sm:mt-0">
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
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-visible">
          <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Basic Information</h6>
          </div>
          <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-6">
            <div id="title-container" class="md:col-span-2 lg:col-span-12">
              <label for="title" id="title-label" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Title:</label>
              <input type="text" id="title" name="title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Material Title" value="{{ old('title', $book->title) }}" required>
              @error('title') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>
            <div id="parallel-title-container" class="md:col-span-2 lg:col-span-12">
              <label for="parallel_title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Parallel Title:</label>
              <input type="text" id="parallel_title" name="parallel_title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., Title 1; Title 2 (separated by semicolon)" value="{{ old('parallel_title', $book->parallel_title) }}">
              @error('parallel_title') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="lg:col-span-4">
              <label for="book_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Material Type:</label>
              <select id="book_type" name="book_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
                @foreach($book_types as $value)
                <option value="{{ $value }}" {{ old('book_type', $book->book_type) == $value ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
              @error('book_type') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="lg:col-span-4">
              <label for="category" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category:</label>
              <select id="category" name="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
                <option value="" disabled>Choose a category</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" data-category-type="{{ $category->category_type }}" {{ old('category', $book->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
              </select>
              @error('category') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>
            <div id="accession-container" class="md:col-span-2 lg:col-span-4">
              <label for="accession" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Accession Number:</label>
              <input type="text" id="accession" name="accession" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., FIL0123456789" value="{{ old('accession', $book->accession) }}" required>
              @error('accession') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>
            <div id="call-number-container" class="lg:col-span-3">
              <label for="call_number" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Call Number:</label>
              <input type="text" id="call_number" name="call_number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 192.000" value="{{ old('call_number', $book->call_number) }}">
            </div>
            <div id="isbn-container" class="lg:col-span-3">
              <label for="isbn" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">ISBN:</label>
              <input type="text" id="isbn" name="isbn" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 9789712345678" value="{{ old('isbn', $book->isbn) }}">
            </div>
            <div id="edition-container" class="lg:col-span-3">
              <label for="edition" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Edition:</label>
              <input type="text" id="edition" name="edition" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 1st Edition" value="{{ old('edition', $book->edition) }}">
            </div>
            <div id="languages-container" class="lg:col-span-3">
              <label for="languages" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Language:</label>
              <input type="text" id="languages" name="languages" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., English" value="{{ old('languages', $book->languages) }}">
            </div>
            <div id="subject-container" class="md:col-span-2 lg:col-span-12">
              <label for="subject_search" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Subject Access Codes:</label>
              
              <div class="relative" id="multiselect-subject">
                {{-- Visible Search & Tags Container --}}
                <div id="subject-tags-container" class="flex flex-wrap gap-2 p-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus-within:ring-primary-400 focus-within:border-primary-400 dark:bg-gray-700 dark:border-gray-600 dark:text-white min-h-[45px] cursor-text">
                  {{-- Tags will be injected here by JS --}}
                  <input type="text" id="subject_search" class="flex-grow bg-transparent border-none focus:ring-0 p-0 text-sm min-w-[150px] placeholder-gray-400" placeholder="Search and select subjects...">
                </div>

                {{-- Dropdown Results --}}
                <div id="subject-dropdown" class="absolute z-30 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-xl hidden max-h-60 overflow-y-auto">
                  <ul id="subject-options-list" class="py-1 text-sm text-gray-700 dark:text-gray-200">
                    {{-- Options will be injected here by JS --}}
                  </ul>
                  <div id="no-subjects-found" class="px-4 py-2 text-gray-500 dark:text-gray-400 hidden">No subjects found.</div>
                </div>

                {{-- Actual Hidden Select for Form Submission --}}
                <select name="subject_access_codes[]" id="subject_access_codes" class="hidden" multiple>
                  @foreach($subjects as $subject)
                  <option value="{{ $subject->id }}" {{ in_array($subject->id, old('subject_access_codes', $linkedSubjectIds)) ? 'selected' : '' }}>
                    {{ $subject->access_code }}
                  </option>
                  @endforeach
                </select>
              </div>
              
              <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 italic">Click to select subjects. Search to filter. Configure in Subject Maintenance.</p>
              @error('subject_access_codes') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>
          </div>
        </div>

        <!-- Section 2: Authors Information -->
        <div id="authors-section" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
          <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Authors & Contributors</h6>
          </div>
          <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="authors_main" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Main Author:</label>
              <input type="text" id="authors_main" name="authors[Main author]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Primary Author" value="{{ old('authors.Main author', is_array($book->authors['Main author'] ?? '') ? implode(', ', $book->authors['Main author']) : ($book->authors['Main author'] ?? '')) }}">
            </div>
            <div>
              <label for="authors_corporate" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Corporate Author:</label>
              <input type="text" id="authors_corporate" name="authors[Corporate author]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Organization/Company" value="{{ old('authors.Corporate author', is_array($book->authors['Corporate author'] ?? '') ? implode(', ', $book->authors['Corporate author']) : ($book->authors['Corporate author'] ?? '')) }}">
            </div>
            <div>
              <label for="authors_added" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Added Authors:</label>
              <input type="text" id="authors_added" name="authors[Added authors]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Co-authors" value="{{ old('authors.Added authors', is_array($book->authors['Added authors'] ?? '') ? implode(', ', $book->authors['Added authors']) : ($book->authors['Added authors'] ?? '')) }}">
            </div>
            <div>
              <label for="authors_contributors" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Contributors:</label>
              <input type="text" id="authors_contributors" name="authors[Contributors]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Editors, Translators, etc." value="{{ old('authors.Contributors', is_array($book->authors['Contributors'] ?? '') ? implode(', ', $book->authors['Contributors']) : ($book->authors['Contributors'] ?? '')) }}">
            </div>
            @error('authors.*') <p class="md:col-span-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
          </div>
        </div>

        <!-- Section 3: Material Description -->
        <div id="description-section" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
          <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Material Description</h6>
          </div>
          <div class="p-5 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div id="desc-description-container" class="md:col-span-3">
                <label for="desc_description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Physical Description:</label>
                <textarea id="desc_description" name="description[Description]" rows="2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Physical characteristics">{{ old('description.Description', is_array($book->description['Description'] ?? '') ? implode(', ', $book->description['Description']) : ($book->description['Description'] ?? '')) }}</textarea>
              </div>
              <div id="desc-extent-container">
                <label for="desc_extent" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Extent: <span id="extent_asterisk" class="text-red-500">*</span></label>
                <input type="text" id="desc_extent" name="description[Extent]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g., 200 pages" value="{{ old('description.Extent', is_array($book->description['Extent'] ?? '') ? implode(', ', $book->description['Extent']) : ($book->description['Extent'] ?? '')) }}">
              </div>
              <div id="desc-acc-material-container">
                <label for="desc_acc_material" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Acc Material:</label>
                <input type="text" id="desc_acc_material" name="description[Acc Material]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Accompanying material" value="{{ old('description.Acc Material', is_array($book->description['Acc Material'] ?? '') ? implode(', ', $book->description['Acc Material']) : ($book->description['Acc Material'] ?? '')) }}">
              </div>
              <div id="desc-series-container">
                <label for="desc_series" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Series:</label>
                <input type="text" id="desc_series" name="description[Series]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g., Harry Potter Series" value="{{ old('description.Series', is_array($book->description['Series'] ?? '') ? implode(', ', $book->description['Series']) : ($book->description['Series'] ?? '')) }}">
              </div>
              <div id="desc-content-notes-container" class="md:col-span-3">
                <label for="desc_content_notes" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Content Notes:</label>
                <textarea id="desc_content_notes" name="description[Content notes]" rows="2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Table of contents, etc.">{{ old('description.Content notes', is_array($book->description['Content notes'] ?? '') ? implode(', ', $book->description['Content notes']) : ($book->description['Content notes'] ?? '')) }}</textarea>
              </div>
              <div id="desc-abstract-container" class="md:col-span-3">
                <label for="desc_abstract" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Abstract:</label>
                <textarea id="desc_abstract" name="description[Abstract]" rows="2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Summary or abstract">{{ old('description.Abstract', is_array($book->description['Abstract'] ?? '') ? implode(', ', $book->description['Abstract']) : ($book->description['Abstract'] ?? '')) }}</textarea>
              </div>
              <div id="desc-reviews-container" class="md:col-span-3">
                <label for="desc_reviews" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Reviews:</label>
                <textarea id="desc_reviews" name="description[Reviews]" rows="2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Material reviews">{{ old('description.Reviews', is_array($book->description['Reviews'] ?? '') ? implode(', ', $book->description['Reviews']) : ($book->description['Reviews'] ?? '')) }}</textarea>
              </div>
            </div>
            @error('description.*') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
          </div>
        </div>

        <!-- Section 4: Publishing & Logistics -->
        <div id="publishing-section" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
          <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Publishing & Logistics</h6>
          </div>
          <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div id="publisher-container">
              <label for="publisher" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Publisher:</label>
              <input type="text" id="publisher" name="publisher" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., National Library" value="{{ old('publisher', $book->publisher) }}">
              @error('publisher') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>
            <div id="copyright-container">
              <label for="copyright" id="copyright-label" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Copyright Year:</label>
              <input type="text" id="copyright" name="copyright" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 2026" value="{{ old('copyright', $book->copyrights) }}">
              @error('copyright') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>
            <div id="publication-container">
              <label for="publication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Place of Publication:</label>
              <input type="text" id="publication" name="publication" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., Manila, Philippines" value="{{ old('publication', $book->place_of_publication) }}">
            </div>
            <div id="location-container">
              <label for="location" id="location-label" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Location:</label>
              <input type="text" id="location" name="location" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., Section A, Shelf 1" value="{{ old('location', $book->location) }}">
            </div>
            <div id="digital-copy-container" class="lg:col-span-2">
              <label for="digital_copy_url" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Digital Copy URL:</label>
              <input type="url" id="digital_copy_url" name="digital_copy_url" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="https://example.com" value="{{ old('digital_copy_url', $book->digital_copy_url) }}">
            </div>
          </div>
        </div>

        <!-- Section 5: Category-Specific Fields -->
        <div id="category-specific-section" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden hidden">
          <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            <h6 id="category-specific-title" class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Category-Specific Fields</h6>
          </div>
          <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Periodical Fields --}}
            <div class="cat-field cat-periodical">
              <label for="periodical_kind" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Kinds of Periodical:</label>
              <select id="periodical_kind" name="periodical_kind" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Select kind</option>
                @foreach(['Newspaper', 'Magazine', 'Journal', 'Newsletter', 'Bulletin'] as $kind)
                  <option value="{{ $kind }}" {{ old('periodical_kind', $book->periodical_kind) == $kind ? 'selected' : '' }}>{{ $kind }}</option>
                @endforeach
              </select>
            </div>
            <div class="cat-field cat-periodical">
              <label for="volume" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Volume:</label>
              <input type="text" id="volume" name="volume" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g., Vol. 5" value="{{ old('volume', $book->volume) }}">
            </div>
            <div class="cat-field cat-periodical">
              <label for="issue_number" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Issue Number:</label>
              <input type="text" id="issue_number" name="issue_number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g., No. 3" value="{{ old('issue_number', $book->issue_number) }}">
            </div>
            <div class="cat-field cat-periodical">
              <label for="material_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Type:</label>
              <select id="material_type" name="material_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Select type</option>
                @foreach(['Article', 'Book Review', 'Essay', 'Case Study', 'Research Article', 'Editorial', 'Letter', 'Report'] as $mtype)
                  <option value="{{ $mtype }}" {{ old('material_type', $book->material_type) == $mtype ? 'selected' : '' }}>{{ $mtype }}</option>
                @endforeach
              </select>
            </div>
            <div class="cat-field cat-periodical md:col-span-2 lg:col-span-2">
              <label for="desc_pages" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Page(s):</label>
              <input type="text" id="desc_pages" name="description[Pages]" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g., pp. 12-25" value="{{ old('description.Pages', $book->description['Pages'] ?? '') }}">
            </div>

            {{-- Serials Fields --}}
            <div class="cat-field cat-serials">
              <label for="issn" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">ISSN:</label>
              <input type="text" id="issn" name="issn" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g., 1234-5678" value="{{ old('issn', $book->issn) }}">
            </div>
            <div class="cat-field cat-serials">
              <label for="frequency" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Frequency:</label>
              <select id="frequency" name="frequency" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Select frequency</option>
                @foreach(['Daily', 'Weekly', 'Biweekly', 'Monthly', 'Bimonthly', 'Quarterly', 'Semi-annually', 'Annually', 'Irregular'] as $freq)
                  <option value="{{ $freq }}" {{ old('frequency', $book->frequency) == $freq ? 'selected' : '' }}>{{ $freq }}</option>
                @endforeach
              </select>
            </div>
            <div class="cat-field cat-serials">
              <label for="latest_received" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Latest Received:</label>
              <input type="text" id="latest_received" name="latest_received" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g., Vol. 10 No. 3 (2026)" value="{{ old('latest_received', $book->latest_received) }}">
            </div>
            <div class="cat-field cat-serials">
              <label for="discipline" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Discipline:</label>
              <input type="text" id="discipline" name="discipline" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g., Social Sciences" value="{{ old('discipline', $book->discipline) }}">
            </div>
            <div class="cat-field cat-serials md:col-span-2 lg:col-span-2">
              <label for="topical_access_point" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Topical Access Point:</label>
              <input type="text" id="topical_access_point" name="topical_access_point" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g., Education -- Philippines" value="{{ old('topical_access_point', $book->topical_access_point) }}">
            </div>
            <div class="cat-field cat-serials md:col-span-2 lg:col-span-3">
              <label for="corporate_access_point" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Corporate Access Point:</label>
              <input type="text" id="corporate_access_point" name="corporate_access_point" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g., Department of Education" value="{{ old('corporate_access_point', $book->corporate_access_point) }}">
            </div>
            <div class="cat-field cat-serials md:col-span-2 lg:col-span-3">
              <label for="serial_notes" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Notes:</label>
              <textarea id="serial_notes" name="notes" rows="2" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Additional notes">{{ old('notes', $book->notes) }}</textarea>
            </div>

            {{-- Academic Research Fields --}}
            <div class="cat-field cat-academic-research">
              <label for="institution" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Institution:</label>
              <input type="text" id="institution" name="institution" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g., University of the Philippines" value="{{ old('institution', $book->institution) }}">
            </div>
            <div class="cat-field cat-academic-research">
              <label for="program" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Program:</label>
              <input type="text" id="program" name="program" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g., BS Computer Science" value="{{ old('program', $book->program) }}">
            </div>
            <div class="cat-field cat-academic-research">
              <label for="research_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Type of Research:</label>
              <select id="research_type" name="material_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">Select type</option>
                @foreach(['Thesis', 'Dissertation', 'Capstone', 'Feasibility Study', 'Action Research', 'Case Study', 'Experimental Research'] as $rtype)
                  <option value="{{ $rtype }}" {{ old('material_type', $book->material_type) == $rtype ? 'selected' : '' }}>{{ $rtype }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        <!-- Section 6: Status & Assets -->
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
                  @php
                    $isDisabled = false;
                    $currentStatus = $book->availability_status;

                    if ($value === 'Borrowed' || $value === 'Reserved') {
                        if ($currentStatus !== $value) {
                            $isDisabled = true;
                        }
                    } elseif ($value === 'Available') {
                        if ($currentStatus === 'Borrowed' || $currentStatus === 'Reserved') {
                            $isDisabled = true;
                        }
                    }
                  @endphp
                  <option value="{{ $value }}" {{ old('availability', $currentStatus) == $value ? 'selected' : '' }} {{ $isDisabled ? 'disabled' : '' }}>
                    {{ $value }}
                  </option>
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
            <div class="md:col-span-2">
              <label for="copy_accession" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Accession Number:</label>
              <input type="text" id="copy_accession" name="accession" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="e.g., FIL0123456789" required>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Add multiple by separating with a semicolon.</p>
              @error('accession')
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
                  @if($value !== 'Borrowed' && $value !== 'Reserved')
                    <option value="{{ $value }}" {{ ($book->availability_status === 'Borrowed' || $book->availability_status === 'Reserved' ? 'Available' : $book->availability_status) == $value ? 'selected' : '' }}>{{ $value }}</option>
                  @endif
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
            <input type="hidden" name="parallel_title" value="{{ $book->parallel_title }}">
            <input type="hidden" name="book_type" value="{{ $book->book_type }}">
            <input type="hidden" name="edition" value="{{ $book->edition }}">
            @foreach($linkedSubjectIds as $subjectId)
              <input type="hidden" name="subject_access_codes[]" value="{{ $subjectId }}">
            @endforeach
            @if(is_array($book->authors))
              @foreach($book->authors as $key => $value)
                <input type="hidden" name="authors[{{ $key }}]" value="{{ is_array($value) ? json_encode($value) : $value }}">
              @endforeach
            @endif
            @if(is_array($book->description))
              @foreach($book->description as $key => $value)
                <input type="hidden" name="description[{{ $key }}]" value="{{ is_array($value) ? json_encode($value) : $value }}">
              @endforeach
            @endif
            <input type="hidden" name="publication" value="{{ $book->place_of_publication }}">
            <input type="hidden" name="publisher" value="{{ $book->publisher }}">
            <input type="hidden" name="copyright" value="{{ $book->copyrights }}">
            <input type="hidden" name="digital_copy_url" value="{{ $book->digital_copy_url }}">
            <input type="hidden" id="copy_category" name="category" value="{{ $book->category_id }}">
            <input type="hidden" name="call_number" value="{{ $book->call_number }}">
            <input type="hidden" name="isbn" value="{{ $book->isbn }}">
            <input type="hidden" name="periodical_kind" value="{{ $book->periodical_kind }}">
            <input type="hidden" name="volume" value="{{ $book->volume }}">
            <input type="hidden" name="issue_number" value="{{ $book->issue_number }}">
            <input type="hidden" name="material_type" value="{{ $book->material_type }}">
            <input type="hidden" name="issn" value="{{ $book->issn }}">
            <input type="hidden" name="frequency" value="{{ $book->frequency }}">
            <input type="hidden" name="latest_received" value="{{ $book->latest_received }}">
            <input type="hidden" name="notes" value="{{ $book->notes }}">
            <input type="hidden" name="topical_access_point" value="{{ $book->topical_access_point }}">
            <input type="hidden" name="corporate_access_point" value="{{ $book->corporate_access_point }}">
            <input type="hidden" name="discipline" value="{{ $book->discipline }}">
            <input type="hidden" name="institution" value="{{ $book->institution }}">
            <input type="hidden" name="program" value="{{ $book->program }}">
            <input type="hidden" name="location" value="{{ $book->location }}">
            <input type="hidden" name="languages" value="{{ $book->languages }}">
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
@include('layouts.NotationGuide')
@endsection
@section('scripts')
<script type="application/json" id="book-categories-data">
  {!! $categories->toJson() !!}
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const bookTypeSelect = document.getElementById('book_type');
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

    const accessionDashActive = <?php echo $accessionDashActive ? 'true' : 'false'; ?>;

    function getNextAccessionFromLast(lastAccession) {
      if (!lastAccession || typeof lastAccession !== 'string') return null;

      const match = lastAccession.match(/(\d+)$/);
      const numberStr = match ? match[1] : null;
      let prefix = numberStr ? lastAccession.slice(0, -numberStr.length) : lastAccession;
      const num = numberStr ? parseInt(numberStr, 10) : 0;

      // Normalize prefix based on dash setting
      if (accessionDashActive) {
        if (prefix && !prefix.endsWith('-')) {
          prefix += '-';
        }
      } else {
        if (prefix && prefix.endsWith('-')) {
          prefix = prefix.slice(0, -1);
        }
      }

      const nextNumberStr = String(num + 1).padStart(6, '0');
      return prefix + nextNumberStr;
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
    }

    const originalAvailability = '{{ $book->availability_status }}';

    function applyAvailabilityRule(isInitial = false) {
      if (!availabilitySelect || !availabilityHidden || !remarksSelect) return;
      const rv = remarksSelect.value;
      if (rv && rv !== 'On Shelf') {
        availabilitySelect.value = 'Unavailable';
        availabilitySelect.setAttribute('disabled', 'disabled');
        availabilityHidden.value = 'Unavailable';
        availabilityHidden.removeAttribute('disabled');
      } else {
        if (isInitial) {
          availabilitySelect.value = originalAvailability;
        } else {
          if (originalAvailability === 'Borrowed' || originalAvailability === 'Reserved') {
            availabilitySelect.value = originalAvailability;
          } else {
            availabilitySelect.value = 'Available';
          }
        }
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
        'publication-container',
        'digital-copy-container',
        'languages-container'
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

      const extentAsterisk = document.getElementById('extent_asterisk');
      if (extentAsterisk) {
        if (isNonPrint) {
          extentAsterisk.style.display = 'none';
        } else {
          extentAsterisk.style.display = 'inline';
        }
      }
    }

    if (categorySelect) {
      categorySelect.addEventListener('change', function() {
        syncBookTypeFromCategory();
        toggleCategorySpecificFields();
      });
    }

    if (bookTypeSelect) {
      bookTypeSelect.addEventListener('change', function() {
        syncCategoryOptionsToBookType();
        restructureFormByBookType();
      });
    }

    if (remarksSelect) {
      remarksSelect.addEventListener('change', applyAvailabilityRule);
    }

    if (categorySelect && categorySelect.value) {
      syncBookTypeFromCategory();
    } else {
      syncCategoryOptionsToBookType();
    }

    async function prefillCopyAccession() {
      const copyAccessionInput = document.getElementById('copy_accession');
      if (!copyAccessionInput || !copyCategoryInput) return;

      const categoryId = copyCategoryInput.value;
      if (!categoryId) return;

      try {
        const response = await fetch(`/admin/maintenance/books/next-accession/${categoryId}`);
        const data = await response.json();
        copyAccessionInput.value = data.next_accession || '';
        validateCopyAccessionInput();
      } catch (error) {
        console.error('Error fetching next accession:', error);
      }
    }

    function validateAccessionField(inputElement, catId, isCopy = false) {
      if (!inputElement || !catId) return;
      const selectedCategory = getCategoryById(catId);
      if (!selectedCategory) return;
      
      let legend = (selectedCategory.legend && String(selectedCategory.legend).trim()) || '';
      let prefixes = [];
      if (legend !== '') {
        prefixes = legend.split('/').map(p => p.trim());
      } else if (selectedCategory.name) {
        prefixes = [String(selectedCategory.name).replace(/\s+/g, '').slice(0, 3).toUpperCase()];
      }
      if (prefixes.length === 0) prefixes = ['ACC'];

      // Normalize prefixes based on dash setting
      prefixes = prefixes.map(p => {
        let normalized = p;
        if (accessionDashActive) {
          if (!normalized.endsWith('-')) normalized += '-';
        } else {
          if (normalized.endsWith('-')) normalized = normalized.slice(0, -1);
        }
        return normalized;
      });

      // Escaping helper
      const escapedPrefixes = prefixes.map(p => p.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&'));
      const regexPattern = new RegExp('^(' + escapedPrefixes.join('|') + ')\\d{5,}$');

      const accessions = inputElement.value.split(';').map(s => s.trim()).filter(s => s);
      let isValid = true;
      for (const acc of accessions) {
        if (!regexPattern.test(acc)) {
          isValid = false;
          break;
        }
      }

      const errorId = isCopy ? 'copy-accession-error-msg' : 'accession-error-msg';
      let errorP = document.getElementById(errorId);
      if (!errorP) {
        errorP = document.createElement('p');
        errorP.id = errorId;
        errorP.className = 'mt-2 text-sm text-red-600 dark:text-red-500 hidden';
        inputElement.parentNode.appendChild(errorP);
      }
      const prefixListStr = prefixes.join("' or '");
      errorP.innerText = `The accession number format is invalid. It must start with '${prefixListStr}' followed by at least a 5-digit number (e.g., ${prefixes[0]}00001).`;

      const submitBtn = isCopy ? document.querySelector('#copy-book-modal button[type="submit"]') : document.querySelector('form button[type="submit"]');

      if (!isValid && accessions.length > 0) {
        inputElement.classList.add('border-red-500', 'text-red-900', 'focus:ring-red-500', 'focus:border-red-500', 'dark:text-red-500', 'dark:border-red-500');
        inputElement.classList.remove('border-gray-300', 'text-gray-900', 'focus:ring-primary-400', 'focus:border-primary-400', 'dark:border-gray-600', 'dark:text-white');
        errorP.classList.remove('hidden');
        if (submitBtn) submitBtn.disabled = true;
      } else {
        inputElement.classList.remove('border-red-500', 'text-red-900', 'focus:ring-red-500', 'focus:border-red-500', 'dark:text-red-500', 'dark:border-red-500');
        inputElement.classList.add('border-gray-300', 'text-gray-900', 'focus:ring-primary-400', 'focus:border-primary-400', 'dark:border-gray-600', 'dark:text-white');
        errorP.classList.add('hidden');
        if (submitBtn) submitBtn.disabled = false;
      }
    }

    const mainAccessionInput = document.getElementById('accession');
    function validateMainAccessionInput() {
      if (categorySelect) {
        validateAccessionField(mainAccessionInput, categorySelect.value, false);
      }
    }

    function validateCopyAccessionInput() {
      const copyAccessionInput = document.getElementById('copy_accession');
      if (copyCategoryInput) {
        validateAccessionField(copyAccessionInput, copyCategoryInput.value, true);
      }
    }

    if (mainAccessionInput) {
      mainAccessionInput.addEventListener('input', validateMainAccessionInput);
    }
    
    if (categorySelect) {
      categorySelect.addEventListener('change', function() {
        validateMainAccessionInput();
      });
    }
    
    const copyAccessionInputEl = document.getElementById('copy_accession');
    if (copyAccessionInputEl) {
      copyAccessionInputEl.addEventListener('input', validateCopyAccessionInput);
    }

    // Prefill when modal button is clicked
    const copyModalBtn = document.querySelector('[data-modal-target="copy-book-modal"]');
    if (copyModalBtn) {
      copyModalBtn.addEventListener('click', prefillCopyAccession);
    }

    // ── Category-specific fields logic ──────────────────────────────────
    const categorySpecificSection = document.getElementById('category-specific-section');
    const categorySpecificTitle = document.getElementById('category-specific-title');
    const titleLabel = document.getElementById('title-label');
    const copyrightLabel = document.getElementById('copyright-label');
    const locationLabel = document.getElementById('location-label');

    function getCategoryClassFromName(categoryName) {
      if (!categoryName) return null;
      const normalized = categoryName.trim().toLowerCase();
      if (normalized === 'periodical') return 'cat-periodical';
      if (normalized === 'serials') return 'cat-serials';
      if (normalized === 'academic research') return 'cat-academic-research';
      return null;
    }

    /**
     * Defines which generic form element IDs should be visible for each special category.
     * Any element not listed here will be hidden when that category is selected.
     */
    const categoryVisibleFields = {
      'cat-periodical': [
        'title-container',        // Article Title
        'authors-section',        // Creator
        'copyright-container',    // Date
        'location-container',     // Location
        'subject-container',      // Subjects
        'desc-abstract-container', // Abstract
        'description-section',    // needed to show abstract
      ],
      'cat-serials': [
        'title-container',        // Title
        'publication-container',  // Place of Publication
        'publisher-container',    // Publisher
        'desc-extent-container',  // Extent
        'description-section',    // needed to show extent
        'call-number-container',  // Call Number
        'accession-container',    // Accession
        'location-container',     // Library Location
        'publishing-section',     // needed to show publisher/publication/location
      ],
      'cat-academic-research': [
        'title-container',        // Title Proper
        'authors-section',        // Creator
        'copyright-container',    // Date
        'desc-extent-container',  // Extent
        'description-section',    // needed to show extent and abstract
        'call-number-container',  // Call Number
        'accession-container',    // Accession
        'languages-container',    // Language
        'subject-container',      // Subjects
        'desc-abstract-container', // Abstract
      ]
    };

    /** All generic field container IDs that can be toggled */
    const allToggleableFields = [
      'title-container', 'parallel-title-container', 'accession-container',
      'call-number-container', 'isbn-container', 'edition-container',
      'languages-container', 'subject-container',
      'authors-section', 'description-section', 'publishing-section',
      'desc-description-container', 'desc-extent-container',
      'desc-acc-material-container', 'desc-series-container',
      'desc-content-notes-container', 'desc-abstract-container',
      'desc-reviews-container',
      'publisher-container', 'copyright-container',
      'publication-container', 'location-container', 'digital-copy-container'
    ];

    /** Title label mapping per category */
    const titleLabelMap = {
      'cat-periodical': 'Article Title:',
      'cat-serials': 'Title:',
      'cat-academic-research': 'Title Proper:'
    };

    /** Copyright/Date label mapping per category */
    const copyrightLabelMap = {
      'cat-periodical': 'Date:',
      'cat-academic-research': 'Date:'
    };

    /** Location label mapping per category */
    const locationLabelMap = {
      'cat-serials': 'Library Location:'
    };

    function toggleCategorySpecificFields() {
      if (!categorySelect || !categorySpecificSection) return;
      const selectedCategory = getCategoryById(categorySelect.value);
      const catClass = selectedCategory ? getCategoryClassFromName(selectedCategory.name) : null;

      // Hide all category-specific fields first
      document.querySelectorAll('.cat-field').forEach(el => el.classList.add('hidden'));

      if (catClass) {
        // Show the category-specific section and its relevant fields
        categorySpecificSection.classList.remove('hidden');
        document.querySelectorAll('.' + catClass).forEach(el => el.classList.remove('hidden'));

        // Update section title
        const titles = {
          'cat-periodical': 'Periodical Fields',
          'cat-serials': 'Serials Fields',
          'cat-academic-research': 'Academic Research Fields'
        };
        categorySpecificTitle.textContent = titles[catClass] || 'Category-Specific Fields';

        // Update the title field label
        if (titleLabel) {
          titleLabel.textContent = titleLabelMap[catClass] || 'Title:';
        }

        // Update the copyright/date label
        if (copyrightLabel) {
          copyrightLabel.textContent = copyrightLabelMap[catClass] || 'Copyright Year:';
        }

        // Update the location label
        if (locationLabel) {
          locationLabel.textContent = locationLabelMap[catClass] || 'Location:';
        }

        // Get the list of visible fields for this category
        const visibleFields = categoryVisibleFields[catClass] || [];

        // Hide all toggleable fields, then show only the ones for this category
        allToggleableFields.forEach(id => {
          const el = document.getElementById(id);
          if (el) {
            if (visibleFields.includes(id)) {
              el.classList.remove('hidden');
            } else {
              el.classList.add('hidden');
            }
          }
        });

        // For description-section: only show the section container if at least one
        // child description field is visible
        const descSection = document.getElementById('description-section');
        if (descSection) {
          const descChildIds = [
            'desc-description-container', 'desc-extent-container',
            'desc-acc-material-container', 'desc-series-container',
            'desc-content-notes-container', 'desc-abstract-container',
            'desc-reviews-container'
          ];
          const hasVisibleChild = descChildIds.some(id => visibleFields.includes(id));
          if (hasVisibleChild) {
            descSection.classList.remove('hidden');
          } else {
            descSection.classList.add('hidden');
          }
        }

        // For publishing-section: only show if at least one child is visible
        const pubSection = document.getElementById('publishing-section');
        if (pubSection) {
          const pubChildIds = [
            'publisher-container', 'copyright-container',
            'publication-container', 'location-container', 'digital-copy-container'
          ];
          const hasVisibleChild = pubChildIds.some(id => visibleFields.includes(id));
          if (hasVisibleChild) {
            pubSection.classList.remove('hidden');
          } else {
            pubSection.classList.add('hidden');
          }
        }
      } else {
        // No special category — show everything (default/generic behavior)
        categorySpecificSection.classList.add('hidden');
        allToggleableFields.forEach(id => {
          const el = document.getElementById(id);
          if (el) el.classList.remove('hidden');
        });

        // Reset labels to defaults
        if (titleLabel) titleLabel.textContent = 'Title:';
        if (copyrightLabel) copyrightLabel.textContent = 'Copyright Year:';
        if (locationLabel) locationLabel.textContent = 'Location:';
      }
    }

    restructureFormByBookType();
    applyAvailabilityRule(true);
    prefillCopyAccession();
    toggleCategorySpecificFields();

    // --- Subject Multiselect Logic ---
    const subjectSearch = document.getElementById('subject_search');
    const subjectDropdown = document.getElementById('subject-dropdown');
    const subjectOptionsList = document.getElementById('subject-options-list');
    const subjectTagsContainer = document.getElementById('subject-tags-container');
    const subjectHiddenSelect = document.getElementById('subject_access_codes');
    const noSubjectsFound = document.getElementById('no-subjects-found');

    // Get all subjects from the hidden select
    const allSubjects = Array.from(subjectHiddenSelect.options).map(opt => ({
      id: opt.value,
      text: opt.text.trim()
    }));

    function updateTags() {
      // Clear existing tags except the input
      const existingTags = subjectTagsContainer.querySelectorAll('.subject-tag');
      existingTags.forEach(tag => tag.remove());

      // Add new tags for selected options
      Array.from(subjectHiddenSelect.options).forEach(opt => {
        if (opt.selected) {
          const tag = document.createElement('span');
          tag.className = 'subject-tag inline-flex items-center gap-1 px-2 py-1 bg-primary-100 text-primary-800 dark:bg-primary-900/40 dark:text-primary-300 rounded text-xs font-medium border border-primary-200 dark:border-primary-800 transition-all';
          tag.innerHTML = `
            ${opt.text}
            <button type="button" class="remove-tag text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-200 focus:outline-none" data-id="${opt.value}">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
          `;
          subjectTagsContainer.insertBefore(tag, subjectSearch);
          
          tag.querySelector('.remove-tag').addEventListener('click', (e) => {
            e.stopPropagation();
            opt.selected = false;
            updateTags();
          });
        }
      });
    }

    function renderDropdown(filter = '') {
      subjectOptionsList.innerHTML = '';
      const filtered = allSubjects.filter(s => 
        s.text.toLowerCase().includes(filter.toLowerCase()) && 
        !subjectHiddenSelect.querySelector(`option[value="${s.id}"]`).selected
      );

      if (filtered.length > 0) {
        noSubjectsFound.classList.add('hidden');
        filtered.forEach(subject => {
          const li = document.createElement('li');
          li.className = 'px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition-colors';
          li.textContent = subject.text;
          li.addEventListener('click', () => {
            const opt = subjectHiddenSelect.querySelector(`option[value="${subject.id}"]`);
            if (opt) opt.selected = true;
            subjectSearch.value = '';
            subjectDropdown.classList.add('hidden');
            updateTags();
          });
          subjectOptionsList.appendChild(li);
        });
      } else {
        noSubjectsFound.classList.remove('hidden');
      }
    }

    subjectSearch.addEventListener('input', (e) => {
      subjectDropdown.classList.remove('hidden');
      renderDropdown(e.target.value);
    });

    subjectSearch.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        const value = e.target.value.trim();
        if (value !== '') {
          const existing = allSubjects.find(s => s.text.toLowerCase() === value.toLowerCase());
          if (existing) {
            const opt = subjectHiddenSelect.querySelector(`option[value="${existing.id}"]`);
            if (opt) opt.selected = true;
          } else {
            const id = value;
            allSubjects.push({ id: id, text: value });
            const opt = document.createElement('option');
            opt.value = id;
            opt.text = value;
            opt.selected = true;
            subjectHiddenSelect.appendChild(opt);
          }
          subjectSearch.value = '';
          subjectDropdown.classList.add('hidden');
          updateTags();
        }
      }
    });

    subjectSearch.addEventListener('focus', () => {
      subjectDropdown.classList.remove('hidden');
      renderDropdown(subjectSearch.value);
    });

    // Handle clicks outside to close dropdown
    document.addEventListener('click', (e) => {
      if (!subjectSearch.contains(e.target) && !subjectDropdown.contains(e.target)) {
        subjectDropdown.classList.add('hidden');
      }
    });

    // Focus input when clicking the container
    subjectTagsContainer.addEventListener('click', () => {
      subjectSearch.focus();
    });

    // Initialize tags
    updateTags();
  });
</script>
@endsection