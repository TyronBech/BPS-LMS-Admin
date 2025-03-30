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
          console.log(response);
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

  function showData(response) {
    var data = response.data;
    var remarks = response.remarks;
    var conditions = response.conditions;

    var row = '<tr>';
    row += '<td class="pb-1">' + data.accession + '</td>';
    row += '<td class="pb-1">' + data.call_number + '</td>';
    row += '<td class="pb-1">' + data.barcode + '</td>';
    row += '<td class="pb-1">' + data.title + '</td>';
    row += '<td class="pb-1">' + data.author + '</td>';


    row += '<td class="pb-1 mx-2">';
    row += '<select id="condition" name="condition[' + data.accession + ']" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">';

    for (var i = 0; i < conditions.length; i++) {
      var selected = (conditions[i] === data.condition_status) ? 'selected' : '';
      row += '<option value="' + conditions[i] + '" ' + selected + '>' + conditions[i] + '</option>';
    }

    row += '</select></td>';

    row += '</tr>';

    if ($('#no-data').length > 0) {
      $('#no-data').remove();
    }
    $('#inventory-record').find('tbody').prepend(row);
  }
</script>
@endsection