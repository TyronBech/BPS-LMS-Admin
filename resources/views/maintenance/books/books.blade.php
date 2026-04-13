@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="w-full p-4 sm:p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Books</h5>
      @can(PermissionsEnum::ADD_BOOKS, 'admin')
      <a href="{{ route('maintenance.create-book', ['return_to',  request()->fullUrl()]) }}" class="w-full sm:w-auto mt-4 sm:mt-0 inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
        Add New Book
      </a>
      @endcan
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    {{-- Search and Filter Form --}}
    <form action="{{ route('maintenance.show-books') }}" method="GET" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
      @csrf
      <div class="flex-grow w-full md:w-auto">
        <label for="search" class="sr-only">Search</label>
        <div class="relative">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
            </svg>
          </div>
          <input type="text" id="search" name="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Search by title, author, etc." value="{{ old('search', $search) }}" autocomplete="off">
          <div id="suggestions-container" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 hidden shadow-md">
            <ul id="suggestions-list" class="max-h-60 overflow-y-auto text-gray-900 dark:text-white">
              {{-- Suggestions will be populated by JavaScript --}}
            </ul>
          </div>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
        <select id="category" name="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" onchange="resetSortAndSubmit(this.form)">
          <option value="" {{ !$category ? 'selected' : '' }}>All Categories</option>
          @foreach ($categories as $item)
          <option value="{{ $item->id }}" {{ $item->id == $category ? 'selected' : '' }}>{{ $item->name }}</option>
          @endforeach
        </select>

        <select id="sort_dropdown" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" onchange="updateSortAndSubmit(this.form)">
          <option value="">Default Sorting</option>
          <option value="title-asc" {{ ($sortBy == 'title' && $sortOrder == 'asc') ? 'selected' : '' }}>Title (Ascending)</option>
          <option value="title-desc" {{ ($sortBy == 'title' && $sortOrder == 'desc') ? 'selected' : '' }}>Title (Descending)</option>
          <option value="accession-asc" {{ ($sortBy == 'accession' && $sortOrder == 'asc') ? 'selected' : '' }}>Accession (Ascending)</option>
          <option value="accession-desc" {{ ($sortBy == 'accession' && $sortOrder == 'desc') ? 'selected' : '' }}>Accession (Descending)</option>
        </select>

        <input type="hidden" name="sort_by" id="sort_by" value="{{ $sortBy }}">
        <input type="hidden" name="sort_order" id="sort_order" value="{{ $sortOrder }}">

        <div class="flex gap-2">
          <button type="submit" name="searchBtn" value="search" class="flex-grow justify-center p-2.5 text-sm font-medium text-white bg-primary-500 rounded-lg border border-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
            </svg>
            <span class="sr-only">Search</span>
          </button>

          <button type="submit" title="Export Barcode" name="barcodeBtn" id="exportBarcode" value="barcode" class="p-2.5 text-sm font-medium text-white bg-gray-700 rounded-lg border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M2.9917 4.9834V18.917M9.96265 4.9834V18.917M15.9378 4.9834V18.917m2.9875-13.9336V18.917" />
              <path stroke="currentColor" stroke-linecap="round" d="M5.47925 4.4834V19.417m1.9917-14.9336V19.417M21.4129 4.4834V19.417M13.4461 4.4834V19.417" />
            </svg>
            <span class="sr-only">Export Barcode</span>
          </button>
          <button type="submit" title="Export Call Number" name="callNumberBtn" id="exportCallNumber" value="callNumber" class="p-2.5 text-sm font-medium text-white bg-gray-700 rounded-lg border border-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
            <svg class="w-4 h-4 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
              <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h10" />
            </svg>
            <span class="sr-only">Export Call Number</span>
          </button>
        </div>
      </div>
    </form>

    @include('maintenance.books.table')
  </div>
</div>
@endsection

@section('scripts')
<script>
  function resetSortAndSubmit(form) {
    document.getElementById('sort_by').value = '';
    document.getElementById('sort_order').value = '';
    form.submit();
  }

  function updateSortAndSubmit(form) {
    const sortDropdown = document.getElementById('sort_dropdown').value;
    if (sortDropdown) {
      const parts = sortDropdown.split('-');
      document.getElementById('sort_by').value = parts[0];
      document.getElementById('sort_order').value = parts[1];
    } else {
      document.getElementById('sort_by').value = '';
      document.getElementById('sort_order').value = '';
    }
    form.submit();
  }

  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const suggestionsContainer = document.getElementById('suggestions-container');
    const suggestionsList = document.getElementById('suggestions-list');
    const books = <?php echo json_encode($books->map(function ($book) {
                    return [
                      'title' => $book->title,
                      'author' => $book->author,
                      'isbn' => $book->isbn,
                    ];
                  })); ?>;

    searchInput.addEventListener('input', function() {
      const query = this.value.toLowerCase();
      suggestionsList.innerHTML = '';

      if (query.length === 0) {
        suggestionsContainer.classList.add('hidden');
        return;
      }

      const filteredBooks = books.filter(book =>
        book.title.toLowerCase().includes(query) ||
        book.author.toLowerCase().includes(query) ||
        (book.isbn && book.isbn.toLowerCase().includes(query))
      );

      if (filteredBooks.length > 0) {
        filteredBooks.forEach(book => {
          const li = document.createElement('li');
          li.textContent = `${book.title} by ${book.author}`;
          li.className = 'px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600';
          li.addEventListener('click', function() {
            searchInput.value = book.title;
            suggestionsContainer.classList.add('hidden');
            searchInput.form.submit();
          });
          suggestionsList.appendChild(li);
        });
        suggestionsContainer.classList.remove('hidden');
      } else {
        suggestionsContainer.classList.add('hidden');
      }
    });

    document.addEventListener('click', function(e) {
      if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
        suggestionsContainer.classList.add('hidden');
      }
    });
  });
</script>
@endsection
