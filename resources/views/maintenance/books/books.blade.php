@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<h1 class="font-semibold text-center text-4xl p-5">Maintenance</h1>
<div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
  <div class="flex justify-between">
    <h5 class="mb-1 text-2xl font-bold tracking-tight">Books</h5>
    <div class="flex flex-row">
      <form action="{{ route('maintenance.show-books') }}" method="GET" class="flex items-center mb-2">
        @csrf
        <label for="search" class="sr-only">Search</label>
        <div class="relative w-full">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5v10M3 5a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 10a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm12 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm0 0V6a3 3 0 0 0-3-3H9m1.5-2-2 2 2 2" />
            </svg>
          </div>
          <input type="text" id="search" name="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Search..." value="{{ $search }}" />
        </div>
        <button type="submit" name="searchBtn" value="search" class="p-2.5 ms-2 mr-2 text-sm font-medium text-white bg-blue-700 rounded-lg border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
          <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
          </svg>
          <span class="sr-only">Search</span>
        </button>
        <label for="category" class="block text-sm font-medium text-gray-900 dark:text-white"></label>
        <select id="category" name="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg py-2 mr-2 focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" onchange="this.form.submit()">
          <option selected disabled>Choose a category</option>
          @foreach ($categories as $item)
          <option value="{{ $item->id }}" {{ $item->id == $category ? 'selected' : '' }}>{{ $item->name }}</option>
          @endforeach
        </select>
        <button type="submit" title="Export Barcode" name="barcodeBtn" value="barcode" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-3 py-1.5 me-2 mb-2 mt-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
          <svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M2.9917 4.9834V18.917M9.96265 4.9834V18.917M15.9378 4.9834V18.917m2.9875-13.9336V18.917" />
            <path stroke="currentColor" stroke-linecap="round" d="M5.47925 4.4834V19.417m1.9917-14.9336V19.417M21.4129 4.4834V19.417M13.4461 4.4834V19.417" />
          </svg>
        </button>
      </form>
    </div>
    @can(PermissionsEnum::ADD_BOOKS, 'admin')
    <div class="flex">
      <a href="{{ route('maintenance.create-book') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Add new book</a>
    </div>
    @endcan
  </div>
  <hr class="h-px my-3 bg-gray-200 border-0">
  @include('maintenance.books.table')
</div>
@endsection