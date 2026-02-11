@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="w-full p-4 sm:p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Add New Book</h5>
      <a href="{{ request('return_to', route('maintenance.books')) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500 mt-4 sm:mt-0">
        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
        </svg>
        Back
      </a>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">
    <form action="{{ route('maintenance.store-book') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div class="md:col-span-2">
          <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Title:</label>
          <input type="text" id="title" name="title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Book Title" value="{{ old('title') }}" required>
          @error('title')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="category" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category:</label>
          <select id="category" name="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
            <option value="" selected disabled>Choose a category</option>
            @foreach($categories as $category)
            <option value="{{ $category->id }}" {{ old('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
          </select>
          @error('category')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="accession" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Accession Number:</label>
          <input type="text" id="accession" name="accession" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., FIL0123456789" value="{{ old('accession') }}" required>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Add multiple by separating with a comma.</p>
          @error('accession')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="call_number" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Call Number:</label>
          <input type="text" id="call_number" name="call_number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 192.000" value="{{ old('call_number') }}">
          @error('call_number')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="authors" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Authors:</label>
          <input type="text" id="authors" name="authors" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., Juan Dela Cruz" value="{{ old('authors') }}">
          @error('authors')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div class="md:col-span-2">
          <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Book Description:</label>
          <textarea id="description" name="description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-400 focus:border-primary-400 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Write book description here..." value="{{ old('description') }}"></textarea>
          @error('description')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="edition" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Edition:</label>
          <input type="text" id="edition" name="edition" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 1st Edition" value="{{ old('edition') }}">
          @error('edition')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="publication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Place of Publication:</label>
          <input type="text" id="publication" name="publication" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., Manila, Philippines" value="{{ old('publication') }}">
          @error('publication')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="publisher" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Publisher:</label>
          <input type="text" id="publisher" name="publisher" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., National Library" value="{{ old('publisher') }}">
          @error('publisher')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="copyright" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Copyright Year:</label>
          <input type="text" id="copyright" name="copyright" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 2026" value="{{ old('copyright') }}">
          @error('copyright')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="cover_image">Cover Image:</label>
          <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-700 focus:outline-none dark:border-gray-600 dark:placeholder-gray-400" id="cover_image" name="cover_image" type="file">
          @error('cover_image')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="digital_copy_url" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Digital Copy URL:</label>
          <input type="url" id="digital_copy_url" name="digital_copy_url" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="https://example.com" value="{{ old('digital_copy_url') }}">
          @error('digital_copy_url')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="remarks" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Remarks:</label>
          <p class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 hover:cursor-not-allowed">Missing</p>
          <input type="hidden" id="remarks" name="remarks" value="Missing" readonly>
          @error('remarks')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="book_type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Book Type:</label>
          <select id="book_type" name="book_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
            @foreach($book_types as $value)
            <option value="{{ $value }}" {{ old('book_type') == $value ? 'selected' : '' }}>{{ $value }}</option>
            @endforeach
          </select>
          @error('book_type')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="availability" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Availability:</label>
          <p class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 hover:cursor-not-allowed">Unavailable</p>
          <input type="hidden" id="availability" name="availability" value="Unavailable" readonly>
          @error('availability')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
        <div>
          <label for="condition" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Condition:</label>
          <select id="condition" name="condition" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
            @foreach($condition as $value)
            <option value="{{ $value }}" {{ "New" == $value ? 'selected' : '' }}>{{ $value }}</option>
            @endforeach
          </select>
          @error('condition')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror
        </div>
      </div>
      <div class="flex justify-end mt-6">
        <button type="submit" class="text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">Submit</button>
      </div>
    </form>
  </div>
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
