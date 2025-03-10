@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Inventory</h1>
<div class="flex flex-col items-center bg-white border border-gray-200 rounded-lg shadow-sm mb-12 md:flex-row md:max-w-xl dark:border-gray-700 dark:bg-gray-800">
  <img class="object-cover w-full rounded-t-lg h-96 md:h-auto md:w-48 md:rounded-none md:rounded-s-lg" src="{{ asset('img/books.png') }}" id="book-img" alt="Book Image">
  <div class="flex flex-col justify-between p-4 leading-normal">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Book Title</h5>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Scan the book or type it below.</p>
    <form action="{{ route('inventory.search') }}" id="search-form" method="POST" class="flex flex-col items-center">
      @csrf
      <input type="text" name="barcode" id="barcode" class="w-full p-2 mb-2 border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" placeholder="Barcode" required>
      <button type="submit" class="w-full p-2 mb-2 text-white bg-blue-500 border border-blue-500 rounded-lg shadow-sm hover:bg-blue-600 dark:border-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800">Check</button>
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
  // $(document).ready(function() {
  //   $('#barcode').on('input', function() {
  //     console.log($(this).val());
  //   });
  // })
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
          showData(response);
          document.getElementById('barcode').value = '';
          document.getElementById('barcode').focus();
        },
        error: function() {
          console.log("Error");
          document.getElementById('barcode').value = '';
          document.getElementById('barcode').focus();
        }
      })
    })
  })

  function showData(data) {
    console.log(data.checked_at);
    var row = '<tr>';
    row += '<td class="pb-1">' + data.checked_at + '</td>';
    row += '<td class="pb-1">' + data.book.accession + '</td>';
    row += '<td class="pb-1">' + data.book.call_number + '</td>';
    row += '<td class="pb-1">' + data.book.barcode + '</td>';
    row += '<td class="pb-1">' + data.book.title + '</td>';
    row += '<td class="pb-1">' + data.book.author + '</td>';
    row += '<td class="pb-1">' + data.book.condition_status + '</td>';
    row += '</tr>';
    $('#inventory-record').find('tbody').prepend(row);
  }
</script>
@endsection