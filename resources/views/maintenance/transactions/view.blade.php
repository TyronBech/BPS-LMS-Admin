@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <h1 class="font-semibold text-center text-3xl md:text-4xl mb-8">Maintenance</h1>
  <div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Circulation Details</h5>
      <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 mt-4 sm:mt-0">
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
        @if($cover)
        <img class="object-cover w-full max-w-xs rounded-lg shadow-md" src="{{ str_starts_with($cover, 'data:') ? $cover : 'data:' . $mimeType . ';base64,' . $cover }}" alt="Book Image">
        @else
        <img class="object-cover w-full max-w-xs rounded-lg shadow-md dark:hidden" src="{{ asset('img/Book-light.png') }}" alt="Book Image">
        <img class="hidden object-cover w-full max-w-xs rounded-lg shadow-md dark:block" src="{{ asset('img/Book-dark.png') }}" alt="Book Image">
        @endif
      </div>

      {{-- Circulation Details --}}
      <div class="lg:col-span-2 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">User:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $transaction->user->first_name }} {{ $transaction->user->last_name }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Book:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $transaction->book->title }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Borrowed Date:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($transaction->date_borrowed)->format('F j, Y') }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Due Date:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $transaction->due_date ? \Carbon\Carbon::parse($transaction->due_date)->format('F j, Y') : '-' }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Returned Date:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $transaction->return_date ? \Carbon\Carbon::parse($transaction->return_date)->format('F j, Y') : 'Not returned' }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Transaction Type:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $transaction->transaction_type }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Transaction Status:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $transaction->status }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Book Condition:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $transaction->book_condition }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Penalty:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $transaction->penalty_total }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Penalty Status:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $transaction->penalty_status }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <p class="font-semibold text-gray-900 dark:text-white">Remarks:</p>
          <p class="sm:col-span-2 text-gray-700 dark:text-gray-300">{{ $transaction->remarks ?? 'None' }}</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection