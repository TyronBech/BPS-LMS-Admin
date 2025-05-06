@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Inventory</h1>
<div class="flex flex-col items-center bg-white border border-gray-200 rounded-lg shadow-sm mb-12 md:flex-row md:max-w-xl dark:border-gray-700 dark:bg-gray-800">
  <img class="object-cover w-full rounded-t-lg h-96 md:h-auto md:w-48 md:rounded-none md:rounded-s-lg" src="{{ asset('img/books.png') }}" id="book-img" alt="Book Image">
  <div class="flex flex-col justify-between p-4 leading-normal">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Book Inventory</h5>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Scan the book or type it below.</p>
    <form action="{{ route('inventory.search') }}" id="search-form" method="POST" class="flex flex-col items-center">
      @csrf
      <input type="text" name="barcode" id="barcode" class="w-full p-2 mb-2 border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" placeholder="Barcode" required>
      <button type="submit" class="w-full p-2 mb-2 text-white bg-blue-500 border border-blue-500 rounded-lg shadow-sm hover:bg-blue-600 dark:border-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800">Add</button>
    </form>
  </div>
</div>
@include('inventory.table')
@endsection
@section('scripts')
<script type="module">
  window.onload = function() {
    document.getElementById('barcode').focus();
  };
  const form = document.getElementById('search-form');
  $(function() {
    $(form).on('submit', function(e) {
      e.preventDefault();
      $.ajax({
        url: $(this).attr('action'),
        method: $(this).attr('method'),
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData: false,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
        success: function(response) {
          if (response.success) {
            showData(response);
            showToast('success', 'Book added to inventory.');
          } else {
            showToast('warning', response.message || 'Unexpected warning.');
          }
          document.getElementById('barcode').value = '';
          document.getElementById('barcode').focus();
        },
        error: function(xhr) {
          const res = xhr.responseJSON;
          const message = res?.message || "An unexpected error occurred.";
          showToast('error', message);

          document.getElementById('barcode').value = '';
          document.getElementById('barcode').focus();
        }
      })
    })
  })

  function showData(response) {
    var data = response.data;
    var conditions = response.conditions;

    var row = '<tr>';
    row += '<td class="mt-1">' + data.accession + '</td>';
    row += '<td class="mt-1">' + data.call_number + '</td>';
    row += '<td class="mt-1">' + data.title + '</td>';
    row += '<td class="mt-1">' + data.author + '</td>';


    row += '<td class="mt-1 mx-2">';
    row += '<select id="condition" name="condition[' + data.accession + ']" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">';

    for (var i = 0; i < conditions.length; i++) {
      var selected = (conditions[i] === data.condition_status) ? 'selected' : '';
      row += '<option value="' + conditions[i] + '" ' + selected + '>' + conditions[i] + '</option>';
    }
    row += '</select></td>';
    row += '<td class="mt-1 mx-2"><button type="button" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">Delete</button></td>'
    row += '</tr>';

    if ($('#no-data').length > 0) {
      $('#no-data').remove();
    }
    $('#inventory-record').find('tbody').prepend(row);
  }

  function showToast(type, message) {
    const icons = {
      success: `
        <div id="toast-success" class="flex items-center absolute top-4 z-10 right-2 w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow-sm dark:text-gray-400 dark:bg-gray-800" role="alert">
  <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg dark:bg-green-800 dark:text-green-200">
    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
      <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
    </svg>
    <span class="sr-only">Check icon</span>
  </div>
  <div class="ms-3 text-sm font-normal">${message}</div>
  <button id="toast-close" type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" aria-label="Close">
    <span class="sr-only">Close</span>
    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
    </svg>
  </button>
</div>
      `,
      warning: `
<div id="toast-warning" class="flex items-center absolute top-4 z-10 right-2 w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-sm dark:text-gray-400 dark:bg-gray-800" role="alert">
  <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-orange-500 bg-orange-100 rounded-lg dark:bg-orange-700 dark:text-orange-200">
    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
      <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0V6a1 1 0 0 1 2 0v5Z" />
    </svg>
    <span class="sr-only">Warning icon</span>
  </div>
  <div class="ms-3 text-sm font-normal">${message}</div>
  <button id="toast-close" type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" aria-label="Close">
    <span class="sr-only">Close</span>
    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
    </svg>
  </button>
</div>
      `,
      error: `
<div id="toast-danger" class="flex items-center absolute top-4 z-10 right-2 w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-lg shadow-sm dark:text-gray-400 dark:bg-gray-800" role="alert">
  <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg dark:bg-red-800 dark:text-red-200">
    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
      <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z" />
    </svg>
    <span class="sr-only">Error icon</span>
  </div>
  <div class="ms-3 text-sm font-normal">${message}</div>
  <button id="toast-close" type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" aria-label="Close">
    <span class="sr-only">Close</span>
    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
    </svg>
  </button>
</div>
      `
    };
    // Create a temporary wrapper to parse the string into HTML
    const temp = document.createElement('div');
    temp.innerHTML = icons[type]; // insert HTML string
    const toastEl = temp.firstElementChild;

    // Append to your main container
    const main = document.getElementById('main-app');
    main.appendChild(toastEl);

    const button = document.getElementById('toast-close');
    button.addEventListener('click', function() {
      const toast = button.closest('[role="alert"]');
      toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
      setTimeout(() => {
        toast.remove();
      }, 300);
    });
  }
</script>
@endsection