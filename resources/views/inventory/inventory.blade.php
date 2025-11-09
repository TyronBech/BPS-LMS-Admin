@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Inventory</h1>
<div class="flex justify-center">
  <div class="w-full max-w-sm md:max-w-xl lg:max-w-4xl flex flex-col items-center bg-white border border-gray-200 rounded-lg shadow-sm mb-12 md:flex-row dark:border-gray-700 dark:bg-gray-800">
    <img class="object-cover w-full rounded-t-lg h-64 md:h-48 md:rounded-none md:rounded-s-lg" src="{{ asset('gif/book.gif') }}" id="book-img" alt="Book Image">
    <div class="flex flex-col justify-between p-4 leading-normal w-full">
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Book Inventory</h5>
      <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Scan the book or type it below.</p>
      <form action="{{ route('inventory.search') }}" id="search-form" method="POST" class="flex flex-col items-center">
        @csrf
        <input type="text" name="barcode" id="barcode" class="w-full p-2 mb-2 border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" placeholder="Barcode" autofocus required>
        <button type="submit" class="w-full p-2 mb-2 text-white bg-blue-500 border border-blue-500 rounded-lg shadow-sm hover:bg-blue-600 dark:border-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800">Add</button>
      </form>
    </div>
  </div>
</div>
@include('inventory.table')
<div id="popup-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="popup-modal">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this book from inventory?</h3>
        <form action="{{ route('inventory.delete') }}" id="delete-form" method="POST">
          @csrf
          @method('DELETE')
          <input type="hidden" name="accession" id="accession" value="{{ old('barcode') }}">
          <button data-modal-hide="popup-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="popup-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  const deleteButtons = document.querySelectorAll('.deleteBtn');
  const deleteInputID = document.getElementById('accession');
  deleteButtons.forEach(btn => {
    btn.addEventListener('click', function(event) {
      const invetoryId   = event.target.value;
      deleteInputID.value = invetoryId;
    });
  });
</script>
@endsection
