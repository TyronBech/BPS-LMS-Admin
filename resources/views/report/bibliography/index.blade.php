@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Bibliography of Books</h1>
  <form action="{{ route('report.bibliography-search') }}" method="POST">
    @csrf
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-center gap-3 mb-4">
      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[200px]">
        <label for="title" class="block text-sm font-medium mb-1">Title</label>
        <input type="text" name="title" id="title" placeholder="Atomic Habits" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ $title }}">
      </div>

      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[200px]">
        <label for="author" class="block text-sm font-medium mb-1">Author</label>
        <input type="text" name="author" id="author" placeholder="James Clear" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ $author }}">
      </div>

      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[150px]">
        <label for="category" class="block text-sm font-medium mb-1">Category</label>
        <select id="category" name="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          <option value="All" {{ !$category || $category === 'All' ? 'selected' : '' }}>All</option>
          @foreach ($categories as $item)
          <option value="{{ $item->id }}" {{ (string) $category === (string) $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
        <button type="submit" name="submit" value="find" class="bg-primary-500 hover:bg-primary-400 active:bg-primary-400 text-white font-bold py-2 px-4 rounded whitespace-nowrap transition-colors dark:bg-primary-400 dark:hover:bg-primary-500 dark:active:bg-primary-500">Find</button>
        @can(PermissionsEnum::CREATE_REPORTS)
        <button type="submit" name="submit" value="pdf" class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white font-bold py-2 px-4 rounded whitespace-nowrap transition-colors">PDF</button>
        <button type="submit" name="submit" value="excel" class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white font-bold py-2 px-4 rounded whitespace-nowrap transition-colors">Excel</button>
        @endcan
      </div>
    </div>
  </form>
  @include('report.bibliography.table')
</div>
@endsection
@section('scripts')
@endsection
