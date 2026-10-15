@extends('layouts.admin-app')
@section('content')
@php
  $catName = strtolower(trim($book->category->name ?? ''));

  // Robust detection for Periodicals
  $isPeriodical = in_array($catName, ['periodical', 'periodicals'])
      || !empty($book->periodical_kind)
      || (!empty($book->volume) && empty($book->issn))
      || (!empty($book->issue_number) && empty($book->issn));

  // Robust detection for Academic Research
  $isAcademicResearch = in_array($catName, ['academic research', 'academic researches', 'research works', 'research', 'thesis', 'theses', 'dissertation', 'dissertations'])
      || !empty($book->institution)
      || !empty($book->program);

  // Robust detection for Series / Serials
  $isSeries = in_array($catName, ['series', 'serials', 'serial', 'periodical series'])
      || !empty($book->issn)
      || !empty($book->frequency)
      || !empty($book->latest_received)
      || !empty($book->topical_access_point)
      || !empty($book->corporate_access_point);

  // Extracted description helpers
  $descArray = is_array($book->description) ? $book->description : [];
  $descPages = $descArray['Pages'] ?? ($descArray['pages'] ?? null);
  $descExtent = $descArray['Extent'] ?? ($descArray['extent'] ?? null);
  $descAbstract = $descArray['Abstract'] ?? ($descArray['abstract'] ?? null);
  $descSeries = $descArray['Series'] ?? ($descArray['series'] ?? null);
  $descPhysical = $descArray['Description'] ?? ($descArray['description'] ?? null);
  $descContentNotes = $descArray['Content notes'] ?? ($descArray['content_notes'] ?? null);
  $descReviews = $descArray['Reviews'] ?? ($descArray['reviews'] ?? null);
  $descAccMaterial = $descArray['Acc Material'] ?? ($descArray['acc_material'] ?? null);

  // Material Type Label
  $materialTypeLabel = 'General Material';
  if ($isPeriodical) {
      $materialTypeLabel = 'Periodical';
  } elseif ($isAcademicResearch) {
      $materialTypeLabel = 'Academic Research';
  } elseif ($isSeries) {
      $materialTypeLabel = 'Series / Serial';
  } elseif (!empty($book->category->name)) {
      $materialTypeLabel = $book->category->name;
  }
@endphp

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
  <!-- Top Navigation & Action Header -->
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
        <span>Maintenance</span>
        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span>Materials</span>
        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-700 dark:text-gray-200 font-semibold">View</span>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Material Details</h1>
        
        {{-- Material Kind Badges --}}
        @if($isPeriodical)
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300 border border-purple-200 dark:border-purple-800 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            Periodical
          </span>
          @if(!empty($book->periodical_kind))
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
              {{ $book->periodical_kind }}
            </span>
          @endif
        @elseif($isAcademicResearch)
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
            Academic Research
          </span>
          @if(!empty($book->material_type))
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
              {{ $book->material_type }}
            </span>
          @endif
        @elseif($isSeries)
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
            Series / Serial
          </span>
          @if(!empty($book->frequency))
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
              {{ $book->frequency }}
            </span>
          @endif
        @else
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            {{ $book->category->name ?? 'Book' }}
          </span>
        @endif
      </div>
    </div>

    <div class="flex items-center gap-3">
      <a href="{{ route('maintenance.edit-book', ['id' => $book->id, 'return_to' => request()->fullUrl()]) }}" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 shadow-sm transition-all duration-150">
        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"/>
        </svg>
        Edit Material
      </a>
      <a href="{{ request('return_to', route('maintenance.books')) }}" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500 shadow-sm transition-all duration-150">
        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
        </svg>
        Back to List
      </a>
    </div>
  </div>

  <!-- Main Content Layout -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    {{-- ======================================================== --}}
    {{-- LEFT COLUMN: Cover, Barcode, Status & Quick Overview     --}}
    {{-- ======================================================== --}}
    <div class="lg:col-span-1 space-y-6">
      
      <!-- Material Cover Card -->
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <h6 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Material Cover</h6>
          </div>
          <span class="text-xs font-semibold px-2 py-0.5 rounded bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200">{{ $book->book_type }}</span>
        </div>
        <div class="p-6 flex justify-center items-center bg-gray-50/40 dark:bg-gray-800/60 min-h-[280px]">
          @if(!empty($book->cover_image))
            <img class="object-contain w-full max-w-[240px] max-h-[320px] rounded-lg shadow-md border border-gray-200 dark:border-gray-700" src="data:{{ $mimeType ?? 'image/jpeg' }};base64, {{ $book->cover_image }}" alt="Material Image">
          @elseif(!empty($cover))
            <img class="object-contain w-full max-w-[240px] max-h-[320px] rounded-lg shadow-md border border-gray-200 dark:border-gray-700" src="{{ $cover }}" alt="Material Image">
          @else
            <div class="w-full max-w-[220px] aspect-[3/4] bg-white dark:bg-gray-700/60 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center p-6 text-center gap-3">
              @if($isPeriodical)
                <svg class="w-16 h-16 text-purple-400 dark:text-purple-500 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Periodical Cover Not Available</span>
              @elseif($isAcademicResearch)
                <svg class="w-16 h-16 text-amber-400 dark:text-amber-500 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Manuscript / Paper Cover Not Available</span>
              @elseif($isSeries)
                <svg class="w-16 h-16 text-indigo-400 dark:text-indigo-500 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Series Cover Not Available</span>
              @else
                <img class="object-contain w-full h-full dark:hidden opacity-50" src="{{ asset('img/Book-light.png') }}" alt="Material Image">
                <img class="hidden object-contain w-full h-full dark:block opacity-50" src="{{ asset('img/Book-dark.png') }}" alt="Material Image">
                <span class="text-xs font-medium text-gray-400 dark:text-gray-500">No Cover Available</span>
              @endif
            </div>
          @endif
        </div>
      </div>

      <!-- Barcode & Accession Card -->
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            <h6 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Identifier & Barcode</h6>
          </div>
          <span class="text-xs font-mono text-gray-500 dark:text-gray-400">C39</span>
        </div>
        <div class="p-5 flex flex-col items-center gap-3 text-center">
          <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm w-full flex justify-center">
            @if(!empty($book->barcode))
              <img src="data:image/jpeg;base64, {{ $book->barcode }}" alt="Material Barcode" class="max-w-full h-auto">
            @else
              <span class="text-xs text-gray-400 italic">Barcode unavailable</span>
            @endif
          </div>
          <div class="flex flex-col items-center">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Accession Number</span>
            <span class="text-lg font-mono font-bold text-gray-900 dark:text-white tracking-wide">{{ $book->accession }}</span>
          </div>
          @if(!empty($book->call_number))
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-gray-100 dark:bg-gray-700 text-xs font-mono font-semibold text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600">
              <span class="text-gray-500 dark:text-gray-400">Call No:</span> {{ $book->call_number }}
            </div>
          @endif
        </div>
      </div>

      <!-- Status & Availability Card -->
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <h6 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Status & Library Holdings</h6>
        </div>
        <div class="p-5 space-y-3.5 text-sm">
          <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Availability</span>
            @if($book->availability_status === 'Available')
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border border-green-200 dark:border-green-800">
                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                Available
              </span>
            @else
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 border border-red-200 dark:border-red-800">
                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                {{ $book->availability_status ?? 'Unavailable' }}
              </span>
            @endif
          </div>

          <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Remarks / Shelf State</span>
            <span class="font-medium text-gray-900 dark:text-white px-2.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-xs">
              {{ $book->remarks }}
            </span>
          </div>

          <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Physical Condition</span>
            <span class="font-medium text-gray-900 dark:text-white">{{ $book->condition_status ?? '-' }}</span>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Library Location</span>
            <span class="font-medium text-gray-900 dark:text-white text-right">{{ $book->location ?? 'Not specified' }}</span>
          </div>

          @if(!empty($book->digital_copy_url))
            <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
              <a href="{{ $book->digital_copy_url }}" target="_blank" rel="noopener noreferrer" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-xs font-medium text-primary-700 bg-primary-50 rounded-lg border border-primary-200 hover:bg-primary-100 dark:bg-primary-900/30 dark:text-primary-300 dark:border-primary-800 dark:hover:bg-primary-900/50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Access Digital Resource / Copy
              </a>
            </div>
          @endif
        </div>
      </div>

    </div>

    {{-- ======================================================== --}}
    {{-- RIGHT COLUMN: Material Content, Unique Specs & Details   --}}
    {{-- ======================================================== --}}
    <div class="lg:col-span-2 space-y-6">

      {{-- ────────────────────────────────────────────────────────── --}}
      {{-- 1. TITLE & BASIC IDENTIFICATION CARD                        --}}
      {{-- ────────────────────────────────────────────────────────── --}}
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gray-50/70 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 flex items-center gap-2">
            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @if($isPeriodical) Article & Title Information
            @elseif($isAcademicResearch) Research Title & Identification
            @elseif($isSeries) Series / Serial Identification
            @else Basic Material Information
            @endif
          </span>
          <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
            {{ $book->category->name ?? 'Uncategorized' }}
          </span>
        </div>

        <div class="p-6 space-y-6">
          <div>
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-1">
              @if($isPeriodical) Article Title
              @elseif($isAcademicResearch) Title Proper (Research Work)
              @elseif($isSeries) Series / Serial Title
              @else Title
              @endif
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white leading-tight break-words">
              {{ $book->title }}
            </h2>
          </div>

          @if(!empty($book->parallel_title))
            <div>
              <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-1.5">Parallel Title(s)</span>
              <div class="flex flex-wrap gap-2">
                @foreach(explode(';', $book->parallel_title) as $pTitle)
                  @if(trim($pTitle) !== '')
                    <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                      {{ trim($pTitle) }}
                    </span>
                  @endif
                @endforeach
              </div>
            </div>
          @endif

          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 pt-4 border-t border-gray-100 dark:border-gray-700/60">
            <div class="flex flex-col">
              <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Accession</span>
              <span class="text-sm font-bold font-mono text-gray-900 dark:text-white mt-0.5">{{ $book->accession }}</span>
            </div>
            <div class="flex flex-col">
              <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Call Number</span>
              <span class="text-sm font-semibold font-mono text-gray-900 dark:text-white mt-0.5">{{ $book->call_number ?? '-' }}</span>
            </div>
            <div class="flex flex-col">
              <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Material Format</span>
              <span class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $book->book_type }}</span>
            </div>
          </div>

          {{-- Subject Access Codes / Topics --}}
          <div class="pt-4 border-t border-gray-100 dark:border-gray-700/60">
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">Subject Access Codes & Keywords</span>
            <div class="flex flex-wrap gap-2">
              @forelse($book->subjectAccessCodes as $code)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 rounded-md text-xs font-medium border border-emerald-200 dark:border-emerald-800">
                  <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                  {{ $code->access_code }}
                </span>
              @empty
                <span class="text-sm text-gray-500 italic">No subject access codes assigned.</span>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      {{-- ────────────────────────────────────────────────────────── --}}
      {{-- 2. CATEGORY-SPECIFIC UNIQUE COLUMNS HERO SECTION          --}}
      {{-- ────────────────────────────────────────────────────────── --}}

      {{-- A. PERIODICALS SPECIFIC CONTENT --}}
      @if($isPeriodical)
        <div class="bg-white dark:bg-gray-800 border-2 border-purple-200 dark:border-purple-900/60 rounded-xl shadow-md overflow-hidden relative">
          <div class="px-6 py-4 bg-gradient-to-r from-purple-50 via-purple-50/50 to-white dark:from-purple-950/40 dark:via-gray-800 dark:to-gray-800 border-b border-purple-200 dark:border-purple-900/60 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <div class="p-2 bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
              </div>
              <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Periodical Specifications</h3>
                <p class="text-xs text-purple-700 dark:text-purple-400 font-medium">Issue, volume, kind, and article contents</p>
              </div>
            </div>
            @if(!empty($book->periodical_kind))
              <span class="px-3 py-1 bg-purple-600 text-white text-xs font-bold rounded-full shadow-sm">
                {{ $book->periodical_kind }}
              </span>
            @endif
          </div>

          <div class="p-6 space-y-6">
            <!-- 6-Grid for Periodical Unique Columns -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
              
              {{-- Kinds of Periodical --}}
              <div class="flex flex-col p-3.5 rounded-lg bg-purple-50/50 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-900/30">
                <span class="text-xs font-semibold text-purple-800 dark:text-purple-300 uppercase tracking-wider mb-1">Kinds of Periodical</span>
                <span class="text-base font-bold text-gray-900 dark:text-white">{{ $book->periodical_kind ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Magazine, Journal, Newspaper, etc.</span>
              </div>

              {{-- Volume --}}
              <div class="flex flex-col p-3.5 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Volume</span>
                <span class="text-base font-bold font-mono text-gray-900 dark:text-white">{{ $book->volume ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Publication volume</span>
              </div>

              {{-- Issue Number --}}
              <div class="flex flex-col p-3.5 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Issue Number</span>
                <span class="text-base font-bold font-mono text-gray-900 dark:text-white">{{ $book->issue_number ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Issue / number designation</span>
              </div>

              {{-- Type / Article Type --}}
              <div class="flex flex-col p-3.5 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Material / Content Type</span>
                <span class="text-base font-bold text-gray-900 dark:text-white">{{ $book->material_type ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Article, Research, Review, Essay</span>
              </div>

              {{-- Page(s) --}}
              <div class="flex flex-col p-3.5 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Page(s)</span>
                <span class="text-base font-bold text-gray-900 dark:text-white">{{ $descPages ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Inclusive article pages</span>
              </div>

              {{-- Publication / Issue Date --}}
              <div class="flex flex-col p-3.5 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Issue Date</span>
                <span class="text-base font-bold text-gray-900 dark:text-white">{{ $book->copyrights ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Date of issue publication</span>
              </div>

            </div>

            {{-- Periodical Abstract / Summary if present --}}
            @if(!empty($descAbstract))
              <div class="p-4 rounded-xl bg-purple-50/40 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-900/50">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                  <span class="text-xs font-bold text-purple-900 dark:text-purple-300 uppercase tracking-wider">Article Abstract / Summary</span>
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">{{ $descAbstract }}</p>
              </div>
            @endif
          </div>
        </div>
      @endif

      {{-- B. ACADEMIC RESEARCH SPECIFIC CONTENT --}}
      @if($isAcademicResearch)
        <div class="bg-white dark:bg-gray-800 border-2 border-amber-200 dark:border-amber-900/60 rounded-xl shadow-md overflow-hidden relative">
          <div class="px-6 py-4 bg-gradient-to-r from-amber-50 via-amber-50/50 to-white dark:from-amber-950/40 dark:via-gray-800 dark:to-gray-800 border-b border-amber-200 dark:border-amber-900/60 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <div class="p-2 bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
              </div>
              <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Academic Research Specifications</h3>
                <p class="text-xs text-amber-700 dark:text-amber-400 font-medium">Institution, academic degree, research type, and defense details</p>
              </div>
            </div>
            @if(!empty($book->material_type))
              <span class="px-3 py-1 bg-amber-600 text-white text-xs font-bold rounded-full shadow-sm">
                {{ $book->material_type }}
              </span>
            @endif
          </div>

          <div class="p-6 space-y-6">
            <!-- Grid for Academic Research Unique Columns -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
              
              {{-- Institution --}}
              <div class="flex flex-col p-4 rounded-lg bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30 md:col-span-2">
                <div class="flex items-center gap-1.5 text-xs font-semibold text-amber-800 dark:text-amber-300 uppercase tracking-wider mb-1">
                  <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                  Institution / University
                </div>
                <span class="text-base font-bold text-gray-900 dark:text-white">{{ $book->institution ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Academic institution granting the degree</span>
              </div>

              {{-- Type of Research --}}
              <div class="flex flex-col p-4 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                  <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  Type of Research
                </div>
                <span class="text-base font-bold text-gray-900 dark:text-white">{{ $book->material_type ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Thesis, Dissertation, Capstone, etc.</span>
              </div>

              {{-- Program / Degree --}}
              <div class="flex flex-col p-4 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700 md:col-span-2">
                <div class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                  <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                  Academic Program / Degree
                </div>
                <span class="text-base font-bold text-gray-900 dark:text-white">{{ $book->program ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">e.g., BS Computer Science, Master of Arts</span>
              </div>

              {{-- Date of Completion --}}
              <div class="flex flex-col p-4 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                  <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  Date Completed / Defended
                </div>
                <span class="text-base font-bold text-gray-900 dark:text-white">{{ $book->copyrights ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Submission or defense date</span>
              </div>

              {{-- Extent (Leaves/Pages) --}}
              <div class="flex flex-col p-4 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Pagination & Extent</span>
                <span class="text-base font-bold text-gray-900 dark:text-white">{{ $descExtent ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Number of leaves / pagination</span>
              </div>

              {{-- Language --}}
              <div class="flex flex-col p-4 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700 md:col-span-2">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Language of Work</span>
                <span class="text-base font-bold text-gray-900 dark:text-white">{{ $book->languages ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Primary language of the manuscript</span>
              </div>

            </div>

            {{-- Research Abstract Block (Prominent) --}}
            @if(!empty($descAbstract))
              <div class="p-5 rounded-xl bg-amber-50/40 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-sm font-bold text-amber-900 dark:text-amber-300 uppercase tracking-wider">Research Abstract</span>
                  </div>
                  <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/60 px-2 py-0.5 rounded">Executive Summary</span>
                </div>
                <div class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-line font-normal pl-3 border-l-2 border-amber-400 dark:border-amber-600">
                  {{ $descAbstract }}
                </div>
              </div>
            @endif
          </div>
        </div>
      @endif

      {{-- C. SERIES / SERIALS SPECIFIC CONTENT --}}
      @if($isSeries)
        <div class="bg-white dark:bg-gray-800 border-2 border-indigo-200 dark:border-indigo-900/60 rounded-xl shadow-md overflow-hidden relative">
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 via-indigo-50/50 to-white dark:from-indigo-950/40 dark:via-gray-800 dark:to-gray-800 border-b border-indigo-200 dark:border-indigo-900/60 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <div class="p-2 bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
              </div>
              <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Series & Serials Specifications</h3>
                <p class="text-xs text-indigo-700 dark:text-indigo-400 font-medium">ISSN, publication frequency, holdings, and access points</p>
              </div>
            </div>
            @if(!empty($book->frequency))
              <span class="px-3 py-1 bg-indigo-600 text-white text-xs font-bold rounded-full shadow-sm">
                {{ $book->frequency }}
              </span>
            @endif
          </div>

          <div class="p-6 space-y-6">
            <!-- Grid for Series / Serials Unique Columns -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
              
              {{-- ISSN --}}
              <div class="flex flex-col p-4 rounded-lg bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/30">
                <span class="text-xs font-semibold text-indigo-800 dark:text-indigo-300 uppercase tracking-wider mb-1">ISSN</span>
                <span class="text-base font-bold font-mono text-gray-900 dark:text-white">{{ $book->issn ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">International Standard Serial Number</span>
              </div>

              {{-- Frequency --}}
              <div class="flex flex-col p-4 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Frequency</span>
                <span class="text-base font-bold text-gray-900 dark:text-white">{{ $book->frequency ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Daily, Monthly, Quarterly, etc.</span>
              </div>

              {{-- Latest Received --}}
              <div class="flex flex-col p-4 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Latest Received</span>
                <span class="text-base font-bold text-gray-900 dark:text-white">{{ $book->latest_received ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Current holdings issue</span>
              </div>

              {{-- Discipline --}}
              <div class="flex flex-col p-4 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Discipline</span>
                <span class="text-base font-bold text-gray-900 dark:text-white">{{ $book->discipline ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Academic / scientific field</span>
              </div>

              {{-- Topical Access Point --}}
              <div class="flex flex-col p-4 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700 md:col-span-2">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Topical Access Point</span>
                <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->topical_access_point ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Subject cataloging access point</span>
              </div>

              {{-- Corporate Access Point --}}
              <div class="flex flex-col p-4 rounded-lg bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-700 md:col-span-3">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Corporate Access Point</span>
                <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->corporate_access_point ?? '-' }}</span>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Responsible corporate body, institution or agency</span>
              </div>

              {{-- Series Statement if recorded --}}
              @if(!empty($descSeries))
                <div class="flex flex-col p-4 rounded-lg bg-indigo-50/30 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/30 md:col-span-3">
                  <span class="text-xs font-semibold text-indigo-800 dark:text-indigo-300 uppercase tracking-wider mb-1">Series Statement</span>
                  <span class="text-base font-bold text-gray-900 dark:text-white">{{ $descSeries }}</span>
                  <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Parent series / series title statement</span>
                </div>
              @endif

            </div>

            {{-- Serials Notes if present --}}
            @if(!empty($book->notes))
              <div class="p-5 rounded-xl bg-indigo-50/40 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-900/50 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  <span class="text-xs font-bold text-indigo-900 dark:text-indigo-300 uppercase tracking-wider">Series & Holdings Notes</span>
                </div>
                <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-line">{{ $book->notes }}</p>
              </div>
            @endif
          </div>
        </div>
      @endif

      {{-- ────────────────────────────────────────────────────────── --}}
      {{-- 3. AUTHORS, RESEARCHERS & CONTRIBUTORS CARD                 --}}
      {{-- ────────────────────────────────────────────────────────── --}}
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gray-50/70 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          <h6 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
            @if($isAcademicResearch) Researchers, Advisers & Contributors
            @elseif($isPeriodical) Article Authors & Contributors
            @else Authors & Contributors
            @endif
          </h6>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @if(is_array($book->authors))
              @php $hasAuthors = false; @endphp
              @foreach([
                'Main author' => $isAcademicResearch ? 'Principal Researcher / Author' : ($isPeriodical ? 'Article Author' : 'Main Author'),
                'Corporate author' => $isAcademicResearch ? 'Research Organization' : 'Corporate Author',
                'Added authors' => $isAcademicResearch ? 'Co-Researchers' : 'Added Authors',
                'Contributors' => $isAcademicResearch ? 'Adviser(s) / Panelists' : 'Contributors'
              ] as $key => $displayLabel)
                @if(!empty($book->authors[$key]))
                  @php $hasAuthors = true; @endphp
                  <div class="flex flex-col p-3 rounded-lg bg-gray-50/60 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-700/60">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ $displayLabel }}</span>
                    <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->authors[$key] }}</span>
                  </div>
                @endif
              @endforeach
              @if(!$hasAuthors)
                <p class="text-gray-500 italic text-sm md:col-span-2">No author information recorded.</p>
              @endif
            @else
              <div class="flex flex-col md:col-span-2">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                  {{ $isAcademicResearch ? 'Researchers' : 'Authors' }}
                </span>
                <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->authors ?? '-' }}</span>
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- ────────────────────────────────────────────────────────── --}}
      {{-- 4. CLASSIFICATION & IDENTIFIERS CARD                        --}}
      {{-- ────────────────────────────────────────────────────────── --}}
      @if(!$isPeriodical && !$isAcademicResearch)
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 bg-gray-50/70 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            <h6 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
              @if($isSeries) Serial Classification & Identifiers
              @else Standard Classification & Identifiers
              @endif
            </h6>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
              @if($isSeries)
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ISSN</span>
                  <span class="text-base font-bold font-mono text-gray-900 dark:text-white">{{ $book->issn ?? '-' }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Call Number</span>
                  <span class="text-base font-medium font-mono text-gray-900 dark:text-white">{{ $book->call_number ?? '-' }}</span>
                </div>
              @else
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ISBN</span>
                  <span class="text-base font-bold font-mono text-gray-900 dark:text-white">{{ $book->isbn ?? '-' }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Edition</span>
                  <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->edition ?? '-' }}</span>
                </div>
              @endif
            </div>
          </div>
        </div>
      @endif

      {{-- ────────────────────────────────────────────────────────── --}}
      {{-- 5. MATERIAL DESCRIPTION & PHYSICAL SPECIFICATIONS           --}}
      {{-- ────────────────────────────────────────────────────────── --}}
      @if($book->book_type !== 'Non-print' && (!$isPeriodical && !$isAcademicResearch))
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 bg-gray-50/70 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <h6 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Material Description</h6>
          </div>
          <div class="p-6 space-y-6">
            @if(is_array($book->description))
              @php $hasDesc = false; @endphp
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(!empty($descPhysical))
                  @php $hasDesc = true; @endphp
                  <div class="flex flex-col md:col-span-2">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Physical Description</span>
                    <span class="text-base text-gray-900 dark:text-white break-words">{{ $descPhysical }}</span>
                  </div>
                @endif

                @if(!empty($descExtent))
                  @php $hasDesc = true; @endphp
                  <div class="flex flex-col">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Extent</span>
                    <span class="text-base font-medium text-gray-900 dark:text-white">{{ $descExtent }}</span>
                  </div>
                @endif

                @if(!empty($descAccMaterial))
                  @php $hasDesc = true; @endphp
                  <div class="flex flex-col">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Accompanying Material</span>
                    <span class="text-base font-medium text-gray-900 dark:text-white">{{ $descAccMaterial }}</span>
                  </div>
                @endif

                @if(!empty($descSeries) && !$isSeries)
                  @php $hasDesc = true; @endphp
                  <div class="flex flex-col">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Series</span>
                    <span class="text-base font-medium text-gray-900 dark:text-white">{{ $descSeries }}</span>
                  </div>
                @endif

                @foreach(['Content notes' => $descContentNotes, 'Abstract' => $descAbstract, 'Reviews' => $descReviews] as $key => $val)
                  @if(!empty($val))
                    @php $hasDesc = true; @endphp
                    <div class="flex flex-col md:col-span-2 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg border border-gray-100 dark:border-gray-700/50">
                      <span class="text-xs font-bold text-primary-600 dark:text-primary-400 uppercase tracking-widest mb-2">{{ $key }}</span>
                      <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">{{ $val }}</p>
                    </div>
                  @endif
                @endforeach
              </div>
              @if(!$hasDesc)
                <p class="text-gray-500 italic text-sm">No detailed description recorded.</p>
              @endif
            @else
              <div class="flex flex-col">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Description</span>
                <span class="text-base text-gray-900 dark:text-white break-words">{{ $book->description ?? '-' }}</span>
              </div>
            @endif
          </div>
        </div>
      @endif

      {{-- ────────────────────────────────────────────────────────── --}}
      {{-- 6. PUBLISHING & LOGISTICS CARD                              --}}
      {{-- ────────────────────────────────────────────────────────── --}}
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gray-50/70 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
          <h6 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
            @if($isPeriodical) Publication Details & Location
            @elseif($isAcademicResearch) Submission & Archive Location
            @elseif($isSeries) Series Publication & Location
            @else Publishing & Logistics
            @endif
          </h6>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
            
            {{-- Publisher (relevant for Books & Series) --}}
            @if(!$isPeriodical && !$isAcademicResearch)
              <div class="flex flex-col">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Publisher</span>
                <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->publisher ?? '-' }}</span>
              </div>
            @endif

            {{-- Date / Copyright Year --}}
            <div class="flex flex-col">
              <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                @if($isPeriodical) Date of Issue / Publication
                @elseif($isAcademicResearch) Completion / Defense Date
                @else Copyright Year
                @endif
              </span>
              <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->copyrights ?? '-' }}</span>
            </div>

            {{-- Place of Publication --}}
            @if(!$isPeriodical && !$isAcademicResearch && $book->book_type !== 'Non-print')
              <div class="flex flex-col">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Place of Publication</span>
                <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->place_of_publication ?? '-' }}</span>
              </div>
            @endif

            {{-- Location --}}
            <div class="flex flex-col">
              <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                @if($isSeries) Library Location
                @else Physical Location / Shelf
                @endif
              </span>
              <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->location ?? '-' }}</span>
            </div>

          </div>
        </div>
      </div>

    </div>

  </div>
</div>
@endsection