@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4">
  <h1 class="font-semibold text-center text-4xl p-5">Book Accession List</h1>
  <form action="{{ route('report.book-circulation-search') }}" method="POST">
    @csrf
    <div class="flex flex-col md:flex-row md:flex-wrap md:items-end md:justify-center gap-4 mb-4">

      {{-- Barcode Input --}}
      <div class="flex items-center w-full md:w-auto">
        <label for="barcode" class="block text-sm/6 font-medium mr-2">Barcode:</label>
        <input type="text" name="barcode" id="barcode" placeholder="123-456-789" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" value="<?php echo $barcode; ?>">
      </div>

      {{-- Title Input --}}
      <div class="flex items-center w-full md:w-auto">
        <label for="title" class="block text-sm/6 font-medium mr-2">Title:</label>
        <input type="text" name="title" id="title" placeholder="Atomic Habits" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" value="<?php echo $title; ?>">
      </div>

      {{-- Availability Select --}}
      <div class="w-full md:w-auto">
        <label for="availability" class="sr-only">Select Availability</label>
        <select id="availability" name="availability" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
          @foreach($availability as $item)
          <option value="{{ $item }}" {{ request('availability') == $item ? 'selected' : '' }}>{{ $item }}</option>
          @endforeach
        </select>
      </div>

      {{-- Action Buttons --}}
      <div class="flex flex-wrap items-center justify-center gap-2">
        <button type="submit" name="submit" value="find" class="bg-blue-500 hover:bg-blue-700 active:bg-blue-900 text-white font-bold py-2 px-4 rounded w-full sm:w-auto">Find</button>
        @can(PermissionsEnum::CREATE_REPORTS)
        <button type="submit" name="submit" value="pdf" class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white font-bold py-2 px-4 rounded w-full sm:w-auto">Export PDF</button>
        <button type="submit" name="submit" value="excel" class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white font-bold py-2 px-4 rounded w-full sm:w-auto">Export Excel</button>
        @endcan
      </div>
    </div>
  </form>
  @include('report.book-circulations.book-circulations-table')
</div>
@endsection
@section('scripts')
@endsection