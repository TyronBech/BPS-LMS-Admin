@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Maintenance</h1>
<div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
  <div class="flex justify-between">
    <h5 class="mb-1 text-2xl font-bold tracking-tight">Add Book</h5>
    <a href="{{ route('maintenance.books') }}" class="inline-flex items-center px-3 py-1 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
      Back
      <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
      </svg>
    </a>
  </div>
  <hr class="h-px my-3 bg-gray-200 border-0">
  <form action="{{ route('maintenance.store-book') }}" class="max-w-2xl mx-auto" method="POST" enctype="multipart/form-data">
    @csrf
    <h6 class="mb-1 text-xl font-semibold tracking-tight">Book Information</h6>
    <div class="mb-5">
      <label for="category" class="block mb-2 text-sm font-medium">Category:</label>
      <select id="category" name="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
        <option selected disabled>Choose a category</option>
        @foreach($categories as $category)
        <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
      </select>
      @error('category')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="accession" class="block mb-2 text-sm font-medium">Accession Number: <span class="mb-2 text-xs text-gray-500 dark:text-gray-400">Note: You can add multiple accession number for the same book separated by a comma.</span></label>
      <input type="text" id="accession" name="accession" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="FIL0123456789" required>
      @error('accession')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="call_number" class="block mb-2 text-sm font-medium">Call Number:</label>
      <input type="text" id="call_number" name="call_number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="192.000...">
      @error('call_number')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="title" class="block mb-2 text-sm font-medium">Title:</label>
      <input type="text" id="title" name="title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Title..." required>
      @error('title')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="authors" class="block mb-2 text-sm font-medium">Authors:</label>
      <input type="text" id="authors" name="authors" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Juan Dela Cruz">
      @error('authors')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Book Description:</label>
      <textarea id="description" name="description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Write book description here..."></textarea>
      @error('description')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="edition" class="block mb-2 text-sm font-medium">Edition:</label>
      <input type="text" id="edition" name="edition" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="1st Edition">
      @error('edition')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="publication" class="block mb-2 text-sm font-medium">Publication:</label>
      <input type="text" id="publication" name="publication" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Manila, Philippines">
      @error('publication')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="publisher" class="block mb-2 text-sm font-medium">Publisher:</label>
      <input type="text" id="publisher" name="publisher" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="National Library of the Philippines">
      @error('publisher')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="copyright" class="block mb-2 text-sm font-medium">Copyright:</label>
      <input type="text" id="copyright" name="copyright" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="2026">
      @error('copyright')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="cover_image">Cover Image:</label>
      <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" id="cover_image" name="cover_image" type="file">
      @error('cover_image')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="digital_copy_url" class="block mb-2 text-sm font-medium">Digital Copy URL:</label>
      <input type="text" id="digital_copy_url" name="digital_copy_url" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="www.example.com">
      @error('digital_copy_url')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="remarks" class="block mb-2 text-sm font-medium">Select Remarks:</label>
      <select id="remarks" name="remarks" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
        @foreach($remarks as $value)
        <option value="{{ $value }}">{{ $value }}</option>
        @endforeach
      </select>
      @error('remarks')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="book_type" class="block mb-2 text-sm font-medium">Select Book Type:</label>
      <select id="book_type" name="book_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
        @foreach($book_types as $value)
        <option value="{{ $value }}">{{ $value }}</option>
        @endforeach
      </select>
      @error('book_type')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="availability" class="block mb-2 text-sm font-medium">Select Availability:</label>
      <select id="availability" name="availability" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
        @foreach($availability as $value)
        <option value="{{ $value }}">{{ $value }}</option>
        @endforeach
      </select>
      @error('availability')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="condition" class="block mb-2 text-sm font-medium">Select Condition:</label>
      <select id="condition" name="condition" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required>
        @foreach($condition as $value)
        <option value="{{ $value }}">{{ $value }}</option>
        @endforeach
      </select>
      @error('condition')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Submit</button>
  </form>
</div>
@endsection
@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const accessionInput = document.getElementById('accession');

    // from PHP: an array of category objects (id, name, legend, books, ...)
    const categoriesArray = <?php echo json_encode($categories); ?> || [];

    // build a lookup map keyed by category id (string keys)
    const categories = {};
    categoriesArray.forEach(cat => {
      if (cat && typeof cat.id !== 'undefined') {
        categories[String(cat.id)] = cat;
      }
    });

    // returns next accession string given a last accession like "FIL000123"
    function getNextAccessionFromLast(lastAccession) {
      if (!lastAccession || typeof lastAccession !== 'string') return null;

      // capture trailing digits
      const match = lastAccession.match(/(\d+)$/);
      const numberStr = match ? match[1] : null;

      // prefix = everything before the trailing digits (or whole string if none)
      const prefix = numberStr ? lastAccession.slice(0, -numberStr.length) : lastAccession;

      // numeric value and width (keep same digit length as last accession)
      const num = numberStr ? parseInt(numberStr, 10) : 0;
      const width = numberStr ? numberStr.length : 6;

      const nextNumberStr = String(num + 1).padStart(width, '0');
      return prefix + nextNumberStr;
    }

    categorySelect.addEventListener('change', function() {
      const selectedId = String(this.value);
      const cat = categories[selectedId];

      if (!cat) {
        accessionInput.value = '';
        return;
      }

      const books = Array.isArray(cat.books) ? cat.books : [];

      if (books.length > 0 && books[0].accession) {
        // increment last accession, preserving numeric width
        const next = getNextAccessionFromLast(books[0].accession);
        accessionInput.value = next || '';
      } else {
        // no books yet -> build a starting accession using legend (or name fallback)
        let prefix = (cat.legend && String(cat.legend).trim()) || '';
        if (!prefix && cat.name) {
          prefix = String(cat.name).replace(/\s+/g, '').slice(0, 3).toUpperCase();
        }
        if (!prefix) prefix = 'ACC';
        accessionInput.value = prefix + '000001';
      }
    });
  });
</script>
@endsection