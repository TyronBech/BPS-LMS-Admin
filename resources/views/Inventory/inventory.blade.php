@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Inventory</h1>
<div class="flex flex-col items-center bg-white border border-gray-200 rounded-lg shadow-sm md:flex-row md:max-w-xl dark:border-gray-700 dark:bg-gray-800">
  <img class="object-cover w-full rounded-t-lg h-96 md:h-auto md:w-48 md:rounded-none md:rounded-s-lg" src="{{ asset('img/books.png') }}" id="book-img" alt="Book Image">
  <div class="flex flex-col justify-between p-4 leading-normal">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Book Title</h5>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Scan the book or type it below.</p>
    <form action="#" method="POST" class="flex flex-col items-center">
      @csrf
      <input type="text" name="book" id="book" class="w-full p-2 mb-2 border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" placeholder="Barcode" required>
      <button type="submit" class="w-full p-2 mb-2 text-white bg-blue-500 border border-blue-500 rounded-lg shadow-sm hover:bg-blue-600 dark:border-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800">Check</button>
    </form>
  </div>
</div>
@endsection