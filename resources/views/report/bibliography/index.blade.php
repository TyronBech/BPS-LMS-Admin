@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Bibliography of Books</h1>
  <form action="{{ route('report.bibliography-search') }}" method="POST" class="auto-search-form">
    @csrf
    <div class="flex flex-col md:flex-row md:flex-wrap md:items-end md:justify-center gap-4 mb-4">
      <div class="relative w-full sm:w-auto">
        <label for="title" class="block mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">Title</label>
        <input type="text" name="title" id="title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Search title" value="{{ $title }}">
      </div>
      <div class="relative w-full sm:w-auto">
        <label for="author" class="block mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">Author</label>
        <input type="text" name="author" id="author" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Search author" value="{{ $author }}">
      </div>
      <div class="relative w-full sm:w-auto">
        <label for="category" class="block mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">Category</label>
        <select name="category" id="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
          <option value="All" {{ $category == 'All' ? 'selected' : '' }}>All Categories</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ $category == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="relative w-full sm:w-auto">
        <label for="subject_id" class="block mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">Subject</label>
        <select name="subject_id" id="subject_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
          <option value="All" {{ $subjectId == 'All' ? 'selected' : '' }}>All Subjects</option>
          @foreach($subjects as $subject)
            <option value="{{ $subject->id }}" {{ $subjectId == $subject->id ? 'selected' : '' }}>{{ $subject->access_code }}</option>
          @endforeach
        </select>
      </div>
      <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
        <button type="button" data-clear-url="{{ route('report.bibliography') }}" class="btn-clear-filters bg-white hover:bg-gray-100 text-gray-900 border border-gray-300 font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-600 text-sm w-full sm:w-auto" title="Clear Filters">Clear</button>
        @can(PermissionsEnum::CREATE_REPORTS)
        <button type="submit" name="submit" value="pdf" class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors text-sm w-full sm:w-auto">PDF</button>
        <button type="submit" name="submit" value="excel" class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors text-sm w-full sm:w-auto">Excel</button>
        @endcan
      </div>
    </div>
  </form>
  <div id="table-container">
    @include('report.bibliography.table')
  </div>
</div>
@endsection
@section('scripts')
@endsection
