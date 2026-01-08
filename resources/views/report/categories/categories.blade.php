@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4">
  <h1 class="font-semibold text-center text-2xl md:text-4xl p-5">Summary of BPS Collections</h1>

  @include('report.categories.table')

  <form action="{{ route('report.summary-update') }}" method="POST">
    @csrf
    <div class="fixed z-50 bottom-10 left-10">
      <button type="submit" class="flex items-center text-white bg-gradient-to-r from-primary-500 via-primary-600 to-primary-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-primary-300 dark:focus:ring-primary-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center shadow-lg">
        <span class="hidden sm:inline">Update Matrix</span>
        <svg class="w-6 h-6 text-white sm:ml-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4" />
        </svg>
      </button>
    </div>
  </form>
</div>
@endsection