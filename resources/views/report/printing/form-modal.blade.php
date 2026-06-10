<button type="button" data-modal-target="PrintingModal" data-modal-toggle="PrintingModal"
  class="fixed bottom-20 right-4 md:bottom-24 md:right-6 lg:bottom-28 lg:right-8
            bg-primary-500 hover:bg-primary-600 dark:bg-primary-500 dark:hover:bg-primary-400
            text-white rounded-full p-3 md:p-4 shadow-lg hover:shadow-xl
            transition-all duration-300 hover:scale-110 z-50 group"
  title="Create Printing/Photocopy Entry">
  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 md:h-7 md:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
  </svg>
  <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2
                 bg-gray-800 dark:bg-gray-700 text-white text-sm
                 px-3 py-2 rounded whitespace-nowrap opacity-0
                 group-hover:opacity-100 transition-opacity duration-300
                 pointer-events-none hidden md:block">
    Create Entry
  </span>
</button>

<div id="PrintingModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[100%] max-h-full bg-gray-900 bg-opacity-50">
  <div class="relative p-4 w-full max-w-2xl max-h-full">
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-md">
      <div class="flex items-center justify-between p-4 md:p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-primary-700 to-primary-800 rounded-t-lg">
        <h3 class="text-xl md:text-2xl font-bold text-white">Create Printing/Photocopy Entry</h3>
        <button type="button" class="text-white hover:bg-primary-500 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center transition-colors" data-modal-hide="PrintingModal">
          <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
          </svg>
        </button>
      </div>
      <form action="{{ route('report.printing-store') }}" method="POST" id="printing-entry-form">
        @csrf
        <div class="p-4 md:p-6">
          
          {{-- User Type Selection --}}
          <div class="mb-4">
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">User Type</label>
            <div class="flex gap-4">
              <label class="inline-flex items-center">
                <input type="radio" name="modal_user_type" value="student" checked class="text-primary-600 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600">
                <span class="ms-2 text-sm text-gray-900 dark:text-white">Student</span>
              </label>
              <label class="inline-flex items-center">
                <input type="radio" name="modal_user_type" value="faculty" class="text-primary-600 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600">
                <span class="ms-2 text-sm text-gray-900 dark:text-white">Faculty & Staff</span>
              </label>
            </div>
          </div>

          {{-- Student Selection --}}
          <div class="mb-4" id="student_search_container">
            <label for="student_search" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Student Name</label>
            <div class="relative">
              <input type="text" id="student_search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Search student by name..." autocomplete="off">
              <input type="hidden" name="student_id" id="student_id">
              <div id="student_suggestions" class="absolute z-10 w-full bg-white rounded shadow dark:bg-gray-700 hidden max-h-48 overflow-y-auto mt-1 border border-gray-200 dark:border-gray-600"></div>
            </div>
          </div>

          {{-- Faculty Selection --}}
          <div class="mb-4 hidden" id="faculty_search_container">
            <label for="faculty_search" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Faculty / Staff Name</label>
            <div class="relative">
              <input type="text" id="faculty_search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Search faculty/staff by name..." autocomplete="off">
              <input type="hidden" name="faculty_id" id="faculty_id">
              <div id="faculty_suggestions" class="absolute z-10 w-full bg-white rounded shadow dark:bg-gray-700 hidden max-h-48 overflow-y-auto mt-1 border border-gray-200 dark:border-gray-600"></div>
            </div>
          </div>

          {{-- Activity Type Selection --}}
          <div class="mb-4">
            <label for="type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Activity Type</label>
            <select id="type" name="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
              <option value="print">Print</option>
              <option value="photocopy">Photocopy</option>
            </select>
          </div>

          {{-- Topic --}}
          <div class="mb-4">
            <label for="topic" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Topic</label>
            <input type="text" name="topic" id="topic" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
          </div>

          {{-- Title of Library Material (Photocopy only) --}}
          <div class="mb-4 hidden" id="title_container">
            <label for="title_of_material" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Title of Library Material</label>
            <input type="text" name="title_of_material" id="title_of_material" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          </div>

          {{-- No. of Pages --}}
          <div class="mb-4">
            <label for="pages" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No. of Pages</label>
            <input type="number" name="pages" id="pages" min="1" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
          </div>

          {{-- Amount --}}
          <div class="mb-4" id="amount_container">
            <label for="amount" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Amount (₱)</label>
            <input type="number" step="0.01" name="amount" id="amount" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
          </div>

          {{-- Date and Time --}}
          <div class="mb-4">
            <label for="printed_at" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date and Time</label>
            <input type="datetime-local" name="printed_at" id="printed_at" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ now()->format('Y-m-d\TH:i') }}" required>
          </div>
        </div>
        <div class="flex items-center justify-end p-4 md:p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 rounded-b-lg">
          <button type="submit" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">Save Entry</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const studentSearchInput = document.getElementById('student_search');
    const studentIdInput = document.getElementById('student_id');
    const studentSuggestionsBox = document.getElementById('student_suggestions');

    const facultySearchInput = document.getElementById('faculty_search');
    const facultyIdInput = document.getElementById('faculty_id');
    const facultySuggestionsBox = document.getElementById('faculty_suggestions');

    const studentContainer = document.getElementById('student_search_container');
    const facultyContainer = document.getElementById('faculty_search_container');

    const typeSelect = document.getElementById('type');
    const titleContainer = document.getElementById('title_container');
    const titleInput = document.getElementById('title_of_material');

    // Toggle Photocopy specific fields
    typeSelect.addEventListener('change', function() {
      if (this.value === 'photocopy') {
        titleContainer.classList.remove('hidden');
        titleInput.setAttribute('required', 'required');
      } else {
        titleContainer.classList.add('hidden');
        titleInput.removeAttribute('required');
        titleInput.value = '';
      }
    });

    // Toggle User Type containers
    const userTypeRadios = document.querySelectorAll('input[name="modal_user_type"]');
    userTypeRadios.forEach(radio => {
      radio.addEventListener('change', function() {
        if (this.value === 'student') {
          studentContainer.classList.remove('hidden');
          studentSearchInput.setAttribute('required', 'required');
          facultyContainer.classList.add('hidden');
          facultySearchInput.removeAttribute('required');
          
          // Clear faculty
          facultySearchInput.value = '';
          facultyIdInput.value = '';
        } else {
          facultyContainer.classList.remove('hidden');
          facultySearchInput.setAttribute('required', 'required');
          studentContainer.classList.add('hidden');
          studentSearchInput.removeAttribute('required');
          
          // Clear student
          studentSearchInput.value = '';
          studentIdInput.value = '';
        }
      });
    });

    // Set initial requirements based on selected radio
    const activeRadio = document.querySelector('input[name="modal_user_type"]:checked');
    if (activeRadio) {
      if (activeRadio.value === 'student') {
        studentSearchInput.setAttribute('required', 'required');
        facultySearchInput.removeAttribute('required');
      } else {
        facultySearchInput.setAttribute('required', 'required');
        studentSearchInput.removeAttribute('required');
      }
    }

    function setupAutoSuggest(searchInput, idInput, suggestionsBox, type) {
      let debounceTimer;
      searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value;

        if (query.length < 2) {
          suggestionsBox.classList.add('hidden');
          idInput.value = '';
          return;
        }

        debounceTimer = setTimeout(() => {
          fetch(`{{ route('report.printing-search-user') }}?term=${encodeURIComponent(query)}&type=${type}`, {
            headers: {
              'X-Skip-Loader': 'true'
            }
          })
            .then(response => response.json())
            .then(data => {
              suggestionsBox.innerHTML = '';
              if (data.length > 0) {
                data.forEach(item => {
                  const div = document.createElement('div');
                  div.className = 'px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-900 dark:text-white';
                  div.textContent = item.text;
                  div.addEventListener('click', function() {
                    searchInput.value = item.text;
                    idInput.value = item.id;
                    suggestionsBox.classList.add('hidden');
                  });
                  suggestionsBox.appendChild(div);
                });
                suggestionsBox.classList.remove('hidden');
              } else {
                suggestionsBox.classList.add('hidden');
              }
            });
        }, 300);
      });
    }

    setupAutoSuggest(studentSearchInput, studentIdInput, studentSuggestionsBox, 'student');
    setupAutoSuggest(facultySearchInput, facultyIdInput, facultySuggestionsBox, 'faculty');

    document.addEventListener('click', function(e) {
      if (e.target !== studentSearchInput && e.target !== studentSuggestionsBox) {
        studentSuggestionsBox.classList.add('hidden');
      }
      if (e.target !== facultySearchInput && e.target !== facultySuggestionsBox) {
        facultySuggestionsBox.classList.add('hidden');
      }
    });
  });
</script>
