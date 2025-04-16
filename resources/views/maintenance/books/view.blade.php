@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Maintenance</h1>
<h5 class="mb-1 text-xl font-bold tracking-tight">Book Information</h5>
<div class="flex flex-col items-center bg-white border border-gray-200 rounded-lg shadow-sm md:flex-row md:max-w-xl hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
  <img class="object-cover w-full rounded-t-lg h-96 md:h-auto md:w-48 md:rounded-none md:rounded-s-lg" src="data:image/jpeg;base64, {{ $book->cover_image }}" alt="Book Image">
  <div class="flex flex-col justify-between p-4 leading-normal">
    <h5 class="mb-2 text-xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $book->title }}</h5>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><span class="font-bold">Author:</span> {{ $book->author }}</p>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><span class="font-bold">Accession:</span> {{ $book->accession }}</p>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><span class="font-bold">Call Number:</span> {{ $book->call_number }}</p>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><span class="font-bold">Edition:</span> {{ $book->edition ?? '-' }}</p>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><span class="font-bold">Place of Publication:</span> {{ $book->place_of_publication ?? '-' }}</p>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><span class="font-bold">Publisher:</span> {{ $book->publisher ?? '-' }}</p>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><span class="font-bold">Copyright:</span> {{ $book->copyright ?? '-' }}</p>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><span class="font-bold">Category:</span> {{ $book->category->name }}</p>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><span class="font-bold">Digital Copy:</span> {{ $book->digital_copy_url ?? '-' }}</p>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><span class="font-bold">Remarks:</span> {{ $book->remarks }}</p>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><span class="font-bold">Availbability:</span> {{ $book->availability_status }}</p>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400"><span class="font-bold">Condition:</span> {{ $book->condition_status }}</p>
  </div>
</div>
<a href="{{ route('maintenance.books') }}" class="inline-flex items-center px-3 py-3 mt-3 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
  Back
  <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
  </svg>
</a>
@endsection