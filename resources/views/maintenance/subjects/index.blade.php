@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="w-full p-4 sm:p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-3">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Subjects</h5>
      @can(PermissionsEnum::ADD_SUBJECTS)
      <button data-modal-target="add-subject-modal" data-modal-toggle="add-subject-modal" class="w-full sm:w-auto mt-2 sm:mt-0 inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
        Add New Subject
      </button>
      @endcan
    </div>

    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    <form action="{{ route('maintenance.subjects') }}" method="GET" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
      <div class="flex-grow w-full md:w-auto">
        <label for="search" class="sr-only">Search</label>
        <div class="relative">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
            </svg>
          </div>
          <input type="text" id="search" name="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Search by DDC, subject, or access code" value="{{ old('search', $search) }}" autocomplete="off">
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
        <select id="sort_dropdown" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" onchange="updateSortAndSubmit(this.form)">
          <option value="">Default Sorting</option>
          <option value="ddc-asc" {{ ($sortBy == 'ddc' && $sortOrder == 'asc') ? 'selected' : '' }}>DDC (Ascending)</option>
          <option value="ddc-desc" {{ ($sortBy == 'ddc' && $sortOrder == 'desc') ? 'selected' : '' }}>DDC (Descending)</option>
          <option value="name-asc" {{ ($sortBy == 'name' && $sortOrder == 'asc') ? 'selected' : '' }}>Subject (Ascending)</option>
          <option value="name-desc" {{ ($sortBy == 'name' && $sortOrder == 'desc') ? 'selected' : '' }}>Subject (Descending)</option>
          <option value="updated_at-desc" {{ ($sortBy == 'updated_at' && $sortOrder == 'desc') ? 'selected' : '' }}>Recently Updated</option>
        </select>

        <input type="hidden" name="sort_by" id="sort_by" value="{{ $sortBy }}">
        <input type="hidden" name="sort_order" id="sort_order" value="{{ $sortOrder }}">

        <button type="submit" class="w-full sm:w-auto p-2.5 text-sm font-medium text-white bg-primary-500 rounded-lg border border-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
          Search
        </button>
      </div>
    </form>

    @include('maintenance.subjects.table')
  </div>
</div>
@endsection

@section('scripts')
<script>
  function updateSortAndSubmit(form) {
    const sortDropdown = document.getElementById('sort_dropdown').value;

    if (sortDropdown) {
      const [sortBy, sortOrder] = sortDropdown.split('-');
      document.getElementById('sort_by').value = sortBy;
      document.getElementById('sort_order').value = sortOrder;
    } else {
      document.getElementById('sort_by').value = '';
      document.getElementById('sort_order').value = '';
    }

    form.submit();
  }
</script>
@endsection