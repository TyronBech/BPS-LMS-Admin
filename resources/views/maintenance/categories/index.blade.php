@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="w-full p-4 sm:p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0 mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Categories</h5>
      <div class="flex flex-col sm:flex-row sm:items-center gap-4">
        <form action="{{ route('maintenance.categories') }}" method="GET" class="flex items-center w-full sm:w-auto relative" id="category-search-form">
          <input type="hidden" name="tab" id="categories-tab-input" value="{{ request('tab', 'print') }}" />
          <label for="search-categories" class="sr-only">Search</label>
          <div class="relative w-full">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5v10M3 5a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 10a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm12 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm0 0V6a3 3 0 0 0-3-3H9m1.5-2-2 2 2 2" />
              </svg>
            </div>
            <input type="text" id="search-categories" name="search-categories" autocomplete="off" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Search..." value="{{ $search }}" />
            <!-- Autocomplete Suggestions Dropdown -->
            <div id="autocomplete-results" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg dark:bg-gray-700 dark:border-gray-600 hidden">
              <ul class="py-1 text-sm text-gray-700 dark:text-gray-200" id="autocomplete-list">
              </ul>
            </div>
          </div>
          <button type="submit" class="p-2.5 ms-2 text-sm font-medium text-white bg-primary-500 rounded-lg border border-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
            </svg>
            <span class="sr-only">Search</span>
          </button>
        </form>
        @can(PermissionsEnum::ADD_CATEGORIES)
        <button data-modal-target="add-categories-modal" data-modal-toggle="add-categories-modal" class="w-full sm:w-auto inline-flex items-center px-4 py-2.5 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">Add New Category</button>
        @endcan
      </div>
    </div>

    <hr class="h-px my-4 bg-gray-200 border-0 dark:bg-gray-700">

    <!-- Toggle buttons -->
    <div class="mb-4" role="tablist" aria-label="Choose category type">
      <div class="inline-flex rounded-md shadow-sm border border-gray-200 dark:border-gray-700" role="group">
        <button type="button" data-table="print" class="js-category-toggle skip-loader px-4 py-2 text-sm font-medium rounded-l-md focus:outline-none bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 border-r border-gray-200 dark:border-gray-700" aria-selected="true" role="tab">
          Print
        </button>
        <button type="button" data-table="non-print" class="js-category-toggle skip-loader px-4 py-2 text-sm font-medium focus:outline-none bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 border-r border-gray-200 dark:border-gray-700" aria-selected="false" role="tab">
          Non-print
        </button>
        <button type="button" data-table="ebooks" class="js-category-toggle skip-loader px-4 py-2 text-sm font-medium rounded-r-md focus:outline-none bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600" aria-selected="false" role="tab">
          E-books
        </button>
      </div>
    </div>

    <!-- Toggle content -->
    <div class="space-y-0">
      <div data-content="print" id="print-section">
        <div class="overflow-x-auto">
          @include('maintenance.categories.table', ['categories' => $printCategories, 'perPage' => $perPrintPage, 'type' => 'Print', 'pageParam' => 'print_page'])
        </div>
      </div>
      <div data-content="non-print" id="non-print-section" class="hidden">
        <div class="overflow-x-auto">
          @include('maintenance.categories.table', ['categories' => $nonPrintCategories, 'perPage' => $perNonPrintPage, 'type' => 'Non-print', 'pageParam' => 'non_print_page'])
        </div>
      </div>
      <div data-content="ebooks" id="ebooks-section" class="hidden">
        <div class="overflow-x-auto">
          @include('maintenance.categories.table', ['categories' => $ebooksCategories, 'perPage' => $perEbooksPage, 'type' => 'E-books', 'pageParam' => 'ebooks_page'])
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Tab switching logic
    const buttons = document.querySelectorAll('.js-category-toggle');
    const sections = document.querySelectorAll('[data-content]');
    const hiddenTabInput = document.getElementById('categories-tab-input');
    const activeBtnClasses = ['bg-primary-400', 'text-white'];
    const inactiveBtnClasses = ['bg-white', 'text-gray-700', 'hover:bg-gray-50', 'dark:bg-gray-700', 'dark:text-gray-200', 'dark:hover:bg-gray-600'];

    function setActive(tab) {
      sections.forEach(sec => {
        sec.classList.toggle('hidden', sec.dataset.content !== tab);
      });

      buttons.forEach(btn => {
        const isActive = btn.dataset.table === tab;
        btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        activeBtnClasses.forEach(c => btn.classList.toggle(c, isActive));
        inactiveBtnClasses.forEach(c => btn.classList.toggle(c, !isActive));
      });

      const url = new URL(window.location.href);
      url.searchParams.set('tab', tab);
      window.history.replaceState({}, '', url);

      if (hiddenTabInput) hiddenTabInput.value = tab;
    }

    const params = new URLSearchParams(window.location.search);
    const initial = params.get('tab') || 'print';
    setActive(initial);

    buttons.forEach(btn => {
      btn.addEventListener('click', () => setActive(btn.dataset.table));
    });

    // AJAX Table Update Logic
    async function updateTable(url, skipLoader = false) {
      try {
        const headers = {};
        if (skipLoader) {
          headers['X-Skip-Loader'] = 'true';
        }

        const response = await fetch(url, { headers });
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        ['print', 'non-print', 'ebooks'].forEach(tab => {
          const sectionId = `${tab}-section`;
          const oldSection = document.getElementById(sectionId);
          const newSection = doc.getElementById(sectionId);
          if (oldSection && newSection) {
            oldSection.innerHTML = newSection.innerHTML;
          }
        });

        // Re-initialize Flowbite (important for modals and dropdowns in the new HTML)
        if (typeof initFlowbite === 'function') {
          initFlowbite();
        }

        // Update the browser URL without full refresh
        window.history.pushState({}, '', url);
      } catch (error) {
        console.error('AJAX update failed:', error);
      }
    }

    // Intercept search form submission (Manual Search)
    const searchForm = document.getElementById('category-search-form');
    if (searchForm) {
      searchForm.addEventListener('submit', e => {
        e.preventDefault();
        const formData = new FormData(searchForm);
        const params = new URLSearchParams(formData);
        // Manual search: show loader (skipLoader = false)
        updateTable(`${searchForm.action}?${params.toString()}`, false);
      });
    }

    // Intercept pagination and per-page changes using delegation (Auto Filter)
    document.addEventListener('click', e => {
      const link = e.target.closest('nav a, .pagination a');
      if (link && (link.closest('#print-section') || link.closest('#non-print-section') || link.closest('#ebooks-section'))) {
        e.preventDefault();
        // Pagination: skip loader
        updateTable(link.href, true);
      }
    });

    document.addEventListener('change', e => {
      const input = e.target.closest('input[name^="per"]');
      if (input && input.form && input.form.classList.contains('skip-loader')) {
        e.preventDefault();
        const formData = new FormData(input.form);
        const params = new URLSearchParams(formData);
        // Ensure the current search and tab are preserved
        const searchVal = document.getElementById('search-categories')?.value;
        const tabVal = document.getElementById('categories-tab-input')?.value;
        if (searchVal) params.set('search-categories', searchVal);
        if (tabVal) params.set('tab', tabVal);
        
        // Per-page change: skip loader
        updateTable(`${window.location.pathname}?${params.toString()}`, true);
      }
    });

    // Autocomplete logic
    const searchInput = document.getElementById('search-categories');
    const resultsDropdown = document.getElementById('autocomplete-results');
    const resultsList = document.getElementById('autocomplete-list');
    let debounceTimer;

    searchInput.addEventListener('input', function() {
      const query = this.value;
      clearTimeout(debounceTimer);

      if (query.length < 2) {
        resultsDropdown.classList.add('hidden');
        return;
      }

      debounceTimer = setTimeout(() => {
        fetch(`{{ route('maintenance.categories-autocomplete') }}?q=${encodeURIComponent(query)}`, {
          headers: { 'X-Skip-Loader': 'true' }
        })
          .then(response => response.json())
          .then(data => {
            resultsList.innerHTML = '';
            if (data.length > 0) {
              data.forEach(item => {
                const li = document.createElement('li');
                li.className = 'px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer';
                li.innerHTML = `
                  <div class="font-medium text-gray-900 dark:text-white">${item.name}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">${item.legend}</div>
                `;
                li.addEventListener('click', () => {
                  searchInput.value = item.name;
                  resultsDropdown.classList.add('hidden');
                  
                  // For selection from autocomplete: skip loader
                  const formData = new FormData(searchForm);
                  const params = new URLSearchParams(formData);
                  updateTable(`${searchForm.action}?${params.toString()}`, true);
                });
                resultsList.appendChild(li);
              });
              resultsDropdown.classList.remove('hidden');
            } else {
              resultsDropdown.classList.add('hidden');
            }
          });
      }, 300);
    });

    document.addEventListener('click', function(e) {
      if (!searchInput.contains(e.target) && !resultsDropdown.contains(e.target)) {
        resultsDropdown.classList.add('hidden');
      }
    });
  });
</script>
<div id="add-categories-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-lg max-h-full">
    <!-- Modal content -->
    <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          Add New Category
        </h3>
        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="add-categories-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <!-- Modal body -->
      <form action="{{ route('maintenance.store-category') }}" method="POST">
        @csrf
        <div class="p-4 md:p-5 space-y-4">
          <h6 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Category Information</h6>
          <div>
            <label for="legend" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Legend:</label>
            <input type="text" id="legend" name="legend" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., FIL" value="{{ old('legend') }}" required>
            @error('legend')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name:</label>
            <input type="text" id="name" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., Filipino" value="{{ old('name') }}" required>
            @error('name')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label for="category_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category Type:</label>
            <select id="category_type" name="category_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
              <option value="">-- Select Category Type --</option>
              <option value="Print" {{ old('category_type') === 'Print' ? 'selected' : '' }}>Print</option>
              <option value="Non-print" {{ old('category_type') === 'Non-print' ? 'selected' : '' }}>Non-print</option>
              <option value="E-books" {{ old('category_type') === 'E-books' ? 'selected' : '' }}>E-books</option>
            </select>
            @error('category_type')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
          </div>
          <div class="flex w-full items-center space-x-4">
            <div class="flex items-center space-x-2">
              <input type="hidden" name="can_borrow" id="can_borrow_add_input" value="{{ old('can_borrow', '1') }}">
              <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" id="can_borrow_add_switch" class="sr-only" />
                <div id="can_borrow_add_track" class="w-11 h-6 bg-gray-200 rounded-full relative transition-colors">
                  <span id="can_borrow_add_knob" class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform"></span>
                </div>
              </label>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Can Be Borrowed</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Toggle to mark this category borrowable or not.</p>
            </div>
          </div>
          <div id="borrow_duration_days_add_wrapper">
            <label for="borrow_duration_days_add" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Duration of Borrow (Days):</label>
            <input type="number" id="borrow_duration_days_add" name="borrow_duration_days_add" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 5" min="1" max="999" value="{{ old('borrow_duration_days_add', 1) }}" required>
            @error('borrow_duration_days_add')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
          </div>
        </div>
        <!-- Modal footer -->
        <div class="flex items-center justify-end p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
          <button type="submit" class="text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">Add</button>
          <button data-modal-hide="add-categories-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">Cancel</button>
        </div>
      </form>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const switchEl = document.getElementById('can_borrow_add_switch');
      const hiddenInput = document.getElementById('can_borrow_add_input');
      const track = document.getElementById('can_borrow_add_track');
      const knob = document.getElementById('can_borrow_add_knob');
      const wrapper = document.getElementById('borrow_duration_days_add_wrapper');
      const input = document.getElementById('borrow_duration_days_add');

      if (!switchEl || !hiddenInput || !track || !knob || !wrapper || !input) return;

      const updateSwitchUI = () => {
        const borrowable = hiddenInput.value === '1';
        switchEl.checked = borrowable;
        wrapper.classList.toggle('hidden', !borrowable);
        input.required = borrowable;
        if (!borrowable) input.value = 0;
        else if (!input.value || Number(input.value) < 1) input.value = 1;

        if (borrowable) {
          track.classList.remove('bg-gray-200');
          track.classList.add('bg-primary-500');
          knob.style.transform = 'translateX(20px)';
        } else {
          track.classList.remove('bg-primary-500');
          track.classList.add('bg-gray-200');
          knob.style.transform = 'translateX(0)';
        }
      };

      switchEl.addEventListener('change', function() {
        hiddenInput.value = switchEl.checked ? '1' : '0';
        updateSwitchUI();
      });

      updateSwitchUI();
    });
  </script>
</div>
<!-- Edit modal -->
<div id="edit-category-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-lg max-h-full">
    <!-- Modal content -->
    <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          Edit Category
        </h3>
        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="edit-category-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <!-- Modal body -->
      <form action="{{ route('maintenance.update-category') }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="edit_category_id" id="edit_category_id" value="" />
        <div class="p-4 md:p-5 space-y-4">
          <div>
            <label for="edit_legend" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Legend:</label>
            <input type="text" name="legend" id="edit_legend" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., FIL" />
          </div>
          <div>
            <label for="edit_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name:</label>
            <input type="text" name="name" id="edit_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., Filipino" required />
          </div>
          <div>
            <label for="edit_category_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category Type:</label>
            <select name="category_type" id="edit_category_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
              <option value="">-- Select Category Type --</option>
              <option value="Print">Print</option>
              <option value="Non-print">Non-print</option>
              <option value="E-books">E-books</option>
            </select>
          </div>
          <div class="flex w-full items-center space-x-4">
            <div class="flex items-center space-x-2">
              <input type="hidden" name="can_borrow_edit" id="can_borrow_edit_input" value="1">
              <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" id="can_borrow_edit_switch" class="sr-only" />
                <div id="can_borrow_edit_track" class="w-11 h-6 bg-gray-200 rounded-full relative transition-colors">
                  <span id="can_borrow_edit_knob" class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform"></span>
                </div>
              </label>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Can Be Borrowed</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Uncheck to make this category not borrowable.</p>
            </div>
          </div>

          <div id="borrow_duration_days_edit_wrapper">
            <label for="borrow_duration_days_edit" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Duration of Borrow (Days):</label>
            <input type="number" id="borrow_duration_days_edit" name="borrow_duration_days_edit" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 5" min="1" max="999" required />
          </div>
        </div>
        <!-- Modal footer -->
        <div class="flex items-center justify-end p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
          <button type="submit" class="text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">Update</button>
          <button data-modal-hide="edit-category-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete modal -->
<div id="delete-category-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-category-modal">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this Category?</h3>
        <form action="{{ route('maintenance.delete-category') }}" method="POST" id="delete-category-form">
          @csrf
          @method('DELETE')
          <input type="hidden" name="delete_category_id" id="delete_category_id" value="" />
          <button data-modal-hide="delete-category-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="delete-category-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Shared modal logic for Edit and Delete
    const editModal = {
      id: document.getElementById('edit_category_id'),
      legend: document.getElementById('edit_legend'),
      name: document.getElementById('edit_name'),
      categoryType: document.getElementById('edit_category_type'),
      duration: document.getElementById('borrow_duration_days_edit'),
      hiddenInput: document.getElementById('can_borrow_edit_input'),
      switchEl: document.getElementById('can_borrow_edit_switch'),
      track: document.getElementById('can_borrow_edit_track'),
      knob: document.getElementById('can_borrow_edit_knob'),
      durationWrapper: document.getElementById('borrow_duration_days_edit_wrapper')
    };

    const updateEditSwitchUI = () => {
      if (!editModal.hiddenInput || !editModal.switchEl || !editModal.track || !editModal.knob) return;
      const borrowable = editModal.hiddenInput.value === '1';
      editModal.switchEl.checked = borrowable;
      if (editModal.durationWrapper) editModal.durationWrapper.classList.toggle('hidden', !borrowable);
      if (editModal.duration) editModal.duration.required = borrowable;
      if (!borrowable && editModal.duration) editModal.duration.value = 0;
      else if (borrowable && editModal.duration && (!editModal.duration.value || Number(editModal.duration.value) < 1)) editModal.duration.value = 1;

      if (borrowable) {
        editModal.track.classList.remove('bg-gray-200');
        editModal.track.classList.add('bg-primary-500');
        editModal.knob.style.transform = 'translateX(20px)';
      } else {
        editModal.track.classList.remove('bg-primary-500');
        editModal.track.classList.add('bg-gray-200');
        editModal.knob.style.transform = 'translateX(0)';
      }
    };

    if (editModal.switchEl) {
      editModal.switchEl.addEventListener('change', function() {
        if (!editModal.hiddenInput) return;
        editModal.hiddenInput.value = editModal.switchEl.checked ? '1' : '0';
        updateEditSwitchUI();
      });
    }

    // Delegation for edit and delete buttons (since they might be re-rendered or in different tabs)
    document.addEventListener('click', function(e) {
      const editBtn = e.target.closest('.editBtn');
      const deleteBtn = e.target.closest('.deleteBtn');

      if (editBtn) {
        const category = JSON.parse(editBtn.dataset.category);
        const categoryDuration = Number(category.borrow_duration_days ?? 0);
        const isBorrowable = categoryDuration > 0;

        editModal.id.value = category.id;
        editModal.legend.value = category.legend;
        editModal.name.value = category.name;
        if (editModal.categoryType) editModal.categoryType.value = category.category_type ?? 'Print';
        if (editModal.hiddenInput) editModal.hiddenInput.value = isBorrowable ? '1' : '0';
        if (editModal.duration) editModal.duration.value = isBorrowable ? categoryDuration : 0;
        updateEditSwitchUI();
      }

      if (deleteBtn) {
        const deleteInputID = document.getElementById('delete_category_id');
        if (deleteInputID) deleteInputID.value = deleteBtn.value;
      }
    });
  });
</script>
@endsection