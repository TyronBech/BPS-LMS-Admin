@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Book Information</h5>
      <a href="{{ request('return_to', route('maintenance.books')) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500 mt-4 sm:mt-0">
        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
        </svg>
        Back
      </a>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-6">
      {{-- Book Cover --}}
      <div class="lg:col-span-1 flex justify-center">
        @if(!empty($book->cover_image))
        <img class="object-contain w-full max-w-xs rounded-lg shadow-md" src="data:{{ $mimeType }};base64, {{ $book->cover_image }}" alt="Book Image">
        @elseif(!empty($cover))
        <img class="object-contain w-full max-w-xs rounded-lg shadow-md" src="{{ $cover }}" alt="Book Image">
        @else
        <div class="w-full max-w-xs">
          <img class="object-contain w-full h-full rounded-lg shadow-md dark:hidden" src="{{ asset('img/Book-light.png') }}" alt="Book Image">
          <img class="hidden object-contain w-full h-full rounded-lg shadow-md dark:block" src="{{ asset('img/Book-dark.png') }}" alt="Book Image">
        </div>
        @endif
      </div>

      {{-- Book Details --}}
      <div class="lg:col-span-2 space-y-4">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white break-words">{{ $book->title }}</h5>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Author:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $book->author }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Accession:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $book->accession }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Call Number:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $book->call_number ?? '-' }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Edition:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $book->edition ?? '-' }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Place of Publication:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $book->place_of_publication ?? '-' }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Publisher:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $book->publisher ?? '-' }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Copyright:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $book->copyrights ?? '-' }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Category:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $book->category->name }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Digital Copy:</p>
          @if(empty($book->digital_copy_url))
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">-</p>
          @else
          <a href="{{ $book->digital_copy_url }}" target="_blank" class="sm:col-span-2 text-primary-600 dark:text-primary-400 hover:underline">{{ $book->title }}</a>
          @endif
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Remarks:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $book->remarks }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Availability:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $book->availability_status }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Condition:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $book->condition_status }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Barcode:</p>
          <div class="sm:col-span-2">
            <img src="data:image/jpeg;base64, {{ $book->barcode }}" alt="Book Barcode">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection