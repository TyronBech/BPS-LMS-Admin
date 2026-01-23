@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <h1 class="font-semibold text-center text-3xl md:text-4xl p-5">Maintenance</h1>
  <div class="w-full p-4 sm:p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">
      <h5 class="mb-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Users</h5>
      <div class="flex flex-col sm:flex-row sm:items-center gap-4">
        <form action="{{ route('maintenance.show-users') }}" method="GET" class="flex items-center w-full sm:w-auto">
          @csrf
          <input type="hidden" name="tab" id="users-tab-input" value="{{ request('tab', 'students') }}" />
          <label for="search-users" class="sr-only">Search</label>
          <div class="relative w-full">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5v10M3 5a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 10a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm12 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm0 0V6a3 3 0 0 0-3-3H9m1.5-2-2 2 2 2" />
              </svg>
            </div>
            <input type="text" id="search-users" name="search-users" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Search..." value="{{ old('search-users', $search) }}" />
          </div>
          <button type="submit" class="p-2.5 ms-2 text-sm font-medium text-white bg-primary-500 rounded-lg border border-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
            </svg>
            <span class="sr-only">Search</span>
          </button>
        </form>
        @can(PermissionsEnum::ADD_USERS, 'admin')
        <div class="flex items-stretch sm:items-center gap-2 flex-col sm:flex-row">
          <a href="{{ route('maintenance.create-employee', ['return_to' => request()->fullUrl()]) }}" class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">Add new employee</a>
          <a href="{{ route('maintenance.create-student', ['return_to' => request()->fullUrl()]) }}" class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">Add new student</a>
        </div>
        @endcan
      </div>
    </div>

    <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">

    <!-- Toggle buttons -->
    <div class="mb-4" role="tablist" aria-label="Choose table">
      <div class="inline-flex rounded-md shadow-sm border border-gray-200 dark:border-gray-700" role="group">
        <button type="button" id="toggle-students" data-table="students" class="js-user-toggle px-4 py-2 text-sm font-medium rounded-l-md focus:outline-none bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 border-r border-gray-200 dark:border-gray-700" aria-selected="true" role="tab">
          Students
        </button>
        <button type="button" id="toggle-employees" data-table="employees" class="js-user-toggle px-4 py-2 text-sm font-medium focus:outline-none bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 border-r border-gray-200 dark:border-gray-700" aria-selected="false" role="tab">
          Faculties & Staffs
        </button>
        <button type="button" id="toggle-visitors" data-table="visitors" class="js-user-toggle px-4 py-2 text-sm font-medium rounded-r-md focus:outline-none bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600" aria-selected="false" role="tab">
          Visitors
        </button>
      </div>
    </div>

    <!-- Toggle content -->
    <div class="space-y-0">
      <div data-content="students" id="students-section">
        <div class="overflow-x-auto">
          @include('maintenance.users.students-table')
        </div>
      </div>
      <div data-content="employees" id="employees-section" class="hidden">
        <div class="overflow-x-auto">
          @include('maintenance.users.employees-table')
        </div>
      </div>
      <div data-content="visitors" id="visitors-section" class="hidden">
        <div class="overflow-x-auto">
          @include('maintenance.users.visitors-table')
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Simple toggle without external deps
  document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.js-user-toggle');
    const sections = document.querySelectorAll('[data-content]');
    const hiddenTabInput = document.getElementById('users-tab-input');
    const activeBtnClasses = ['bg-primary-400', 'text-white'];
    const inactiveBtnClasses = ['bg-white', 'text-gray-700', 'hover:bg-gray-50', 'dark:bg-gray-700', 'dark:text-gray-200', 'dark:hover:bg-gray-600'];

    function setActive(tab) {
      // Show/hide sections
      sections.forEach(sec => {
        sec.classList.toggle('hidden', sec.dataset.content !== tab);
      });

      // Style buttons and aria
      buttons.forEach(btn => {
        const isActive = btn.dataset.table === tab;
        btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        activeBtnClasses.forEach(c => btn.classList.toggle(c, isActive));
        inactiveBtnClasses.forEach(c => btn.classList.toggle(c, !isActive));
      });

      // Update URL: set tab, remove all pagination params so only the active table controls its page
      const url = new URL(window.location.href);
      url.searchParams.set('tab', tab);
      ['page', 'students_page', 'employees_page', 'visitors_page'].forEach(p => url.searchParams.delete(p));
      window.history.replaceState({}, '', url);

      // Keep the search form in sync
      if (hiddenTabInput) hiddenTabInput.value = tab;
    }

    // Initial tab from URL or default to students
    const params = new URLSearchParams(window.location.search);
    const initial = params.get('tab') || 'students';
    setActive(initial);

    // Wire up clicks
    buttons.forEach(btn => {
      btn.addEventListener('click', () => setActive(btn.dataset.table));
    });
  });
</script>
@endsection