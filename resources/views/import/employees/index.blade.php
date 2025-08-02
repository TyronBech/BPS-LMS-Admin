@extends('layouts.admin-app')
@section('content')
<h4 class="text-2xl font-semibold mt-3 text-center">User's Data</h4>
<p class="mt-1 text-md/relaxed text-center">Import employee's data to be used in the application using the CSV or Excel format</p>
<p class="mt-1 text-sm/relaxed text-slate-500 text-center">ex. newly hired employees or staff can be imported.</p>
@if(!$showTable)
<div class="border-2 border-slate-700 rounded-lg flex flex-col text-center items-center mb-4 mt-2 w-full">
  <form action="{{ route('import.import-faculties-staffs') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <label class="block mt-4 text-sm font-medium text-gray-600" for="file_input">Upload a file in CSV or Excel format</label>
    <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" id="file_input" name="file" type="file">
    <button type="submit" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-2 focus:ring-green-300 font-medium rounded-lg text-md px-4 py-2 me-2 mb-2 mt-4">Import</button>
  </form>
</div>
@else
@include('import.employees.table')
<form action="{{ route('import.store-faculties-staffs') }}" method="POST" class="flex justify-center">
  @csrf
  <input type="hidden" name="newData" value="{{ json_encode($newData) }}">
  <input type="hidden" name="existingData" value="{{ json_encode($existingData) }}">
  <button type="submit" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-2 focus:ring-green-300 font-medium rounded-lg text-md px-4 py-2 me-2 mb-2 mt-4">Insert to Database</button>
</form>
@endif
@if(!$showTable)
<a href="{{ route('import.download-employee-template') }}" class="flex text-blue-600 hover:text-blue-800 dark:text-blue-200 dark:hover:text-blue-400 underline w-full ml-2">
  Download template for employees
  <svg class="w-6 h-6 ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
    <path fill-rule="evenodd" d="M13 11.15V4a1 1 0 1 0-2 0v7.15L8.78 8.374a1 1 0 1 0-1.56 1.25l4 5a1 1 0 0 0 1.56 0l4-5a1 1 0 1 0-1.56-1.25L13 11.15Z" clip-rule="evenodd" />
    <path fill-rule="evenodd" d="M9.657 15.874 7.358 13H5a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2h-2.358l-2.3 2.874a3 3 0 0 1-4.685 0ZM17 16a1 1 0 1 0 0 2h.01a1 1 0 1 0 0-2H17Z" clip-rule="evenodd" />
  </svg>
</a>
@endif
@endsection