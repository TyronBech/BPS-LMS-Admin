@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Book Circulation</h1>
  <form action="{{ route('report.book-circulation-search') }}" method="POST">
    @csrf
    <div class="container flex flex-row justify-center">
      <div class="sm:col-span-2 sm:col-start-1 flex items-center">
        <label for="barcode" class="block text-sm/6 font-medium mr-2 ml-4">Barcode:</label>
        <div class="">
          <input type="text" name="barcode" id="barcode" placeholder="123-456-789" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" value="<?php echo $barcode; ?>">
        </div>
      </div>
      <div class="sm:col-span-2 sm:col-start-1 flex items-center">
        <label for="title" class="block text-sm/6 font-medium mr-2 ml-4">Title:</label>
        <div class="">
          <input type="text" name="title" id="title" placeholder="Atomic Habits" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" value="<?php echo $title; ?>">
        </div>
      </div>
      <div class="sm:col-span-2 sm:col-start-1 flex items-center">
        <label for="availability" class="block mb-2 text-sm font-medium mr-2 ml-4">Select:</label>
        <select id="availability" name="availability" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
          <option selected>Choose availability status</option>
          @foreach($availability as $item)
            <option value="{{ $item }}">{{ $item }}</option>
          @endforeach
        </select>
      </div>
      <div class="sm:col-span-2 sm:col-start-1 flex items-center">
        <button type="submit" id="submit" name="submit" class="bg-blue-500 hover:bg-blue-700 active:bg-blue-900 text-white text-sm font-bold py-1 px-4 rounded h-12 mt-2 mb-2 ml-6 mr-4 w-20">Find</button>
        <button type="submit" id="submit" name="submit" value="pdf" class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white text-sm font-bold py-1 px-4 rounded h-12 mt-2 mb-2 ml-4 mr-4 w-20">Export PDF</button>
        <button type="submit" id="submit" name="submit" value="excel" class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white text-sm font-bold py-1 px-4 rounded h-12 mt-2 mb-2 ml-4 mr-4 w-20">Export Excel</button>
      </div>
    </div>
  </form>
  @include('report.book-circulations.book-circulations-table')
@endsection
@section('scripts')
@endsection