@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4">
  <h1 class="font-semibold text-center text-4xl p-5">Book Accession List</h1>
  <form action="{{ route('report.accession-list-search') }}" method="POST">
    @csrf
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-center gap-3 mb-4">
      {{-- Barcode Input --}}
      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[200px]">
        <label for="barcode" class="block text-sm font-medium mb-1">Barcode</label>
        <input type="text" name="barcode" id="barcode" placeholder="123-456-789" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="<?php echo $barcode; ?>">
      </div>

      {{-- Title Input --}}
      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[200px]">
        <label for="title" class="block text-sm font-medium mb-1">Title</label>
        <input type="text" name="title" id="title" placeholder="Atomic Habits" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="<?php echo $title; ?>">
      </div>

      {{-- Availability Select --}}
      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[150px]">
        <label for="availability" class="block text-sm font-medium mb-1">Availability</label>
        <select id="availability" name="availability" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">  
          @foreach($availability as $item)
          <option value="{{ $item }}" {{ request('availability') == $item ? 'selected' : '' }}>{{ $item }}</option>
          @endforeach
        </select>
      </div>

      {{-- Category Select --}}
      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[150px]">
        <label for="category" class="block text-sm font-medium mb-1">Category</label>
        <select id="category" name="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          <option value="All" {{ !$category ? 'selected' : '' }}>All</option>
          @foreach ($categories as $item)
          <option value="{{ $item->id }}" {{ $category == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
          @endforeach
        </select>
      </div>

      {{-- Action Buttons --}}
      <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
        <button type="submit" name="submit" value="find" class="bg-primary-500 hover:bg-primary-400 active:bg-primary-400 text-white font-bold py-2 px-4 rounded whitespace-nowrap transition-colors dark:bg-primary-400 dark:hover:bg-primary-500 dark:active:bg-primary-500">Find</button>
        @can(PermissionsEnum::CREATE_REPORTS)
        <button type="submit" name="submit" value="pdf" class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white font-bold py-2 px-4 rounded whitespace-nowrap transition-colors">PDF</button>
        <button type="submit" name="submit" value="excel" class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white font-bold py-2 px-4 rounded whitespace-nowrap transition-colors">Excel</button>
        @endcan
      </div>
    </div>
  </form>
  @include('report.book-circulations.book-circulations-table')
</div>
@endsection
@section('scripts')
@endsection