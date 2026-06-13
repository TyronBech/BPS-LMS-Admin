<button type="button" data-modal-target="NonCirculationModal" data-modal-toggle="NonCirculationModal"
  class="fixed bottom-20 right-4 md:bottom-24 md:right-6 lg:bottom-28 lg:right-8
            bg-primary-500 hover:bg-primary-600 dark:bg-primary-500 dark:hover:bg-primary-400
            text-white rounded-full p-3 md:p-4 shadow-lg hover:shadow-xl
            transition-all duration-300 hover:scale-110 z-50 group"
  title="Create Non-Circulation Entry">
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

<div id="NonCirculationModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[100%] max-h-full bg-gray-900 bg-opacity-50">
  <div class="relative p-4 w-full max-w-2xl max-h-full">
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-md">
      <div class="flex items-center justify-between p-4 md:p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-primary-700 to-primary-800 rounded-t-lg">
        <h3 class="text-xl md:text-2xl font-bold text-white">Create Non-Circulation Entry</h3>
        <button type="button" class="text-white hover:bg-primary-500 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center transition-colors" data-modal-hide="NonCirculationModal">
          <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
          </svg>
        </button>
      </div>
      <form action="{{ route('report.non-circulation-store') }}" method="POST">
        @csrf
        <div class="p-4 md:p-6">
          <div class="mb-4">
            <label for="rfid_scan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Scan RFID</label>
            <div class="relative">
              <input type="text" id="rfid_scan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Scan or type RFID number..." autocomplete="off">
              <span id="rfid_loading" class="absolute right-3 top-3 hidden">
                <svg class="animate-spin h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
            </div>
            <p id="rfid_status" class="mt-1 text-xs hidden"></p>
          </div>

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
            <label for="faculty_search" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Faculty / Teacher Name</label>
            <div class="relative">
              <input type="text" id="faculty_search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Search faculty/staff by name..." autocomplete="off">
              <input type="hidden" name="faculty_id" id="faculty_id">
              <div id="faculty_suggestions" class="absolute z-10 w-full bg-white rounded shadow dark:bg-gray-700 hidden max-h-48 overflow-y-auto mt-1 border border-gray-200 dark:border-gray-600"></div>
            </div>
          </div>

          <div class="mb-4">
            <label for="subject" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Subject</label>
            <input type="text" name="subject" id="subject" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
          </div>
          <div class="mb-4">
            <label for="borrowed_at" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date and Time</label>
            <input type="datetime-local" name="borrowed_at" id="borrowed_at" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ now()->format('Y-m-d\TH:i') }}">
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

    const rfidInput = document.getElementById('rfid_scan');
    const rfidLoading = document.getElementById('rfid_loading');
    const rfidStatus = document.getElementById('rfid_status');

    function performRfidLookup(rfidValue) {
      const rfid = rfidValue.trim();
      if (!rfid) return;

      rfidLoading.classList.remove('hidden');
      rfidStatus.classList.add('hidden');

      fetch(`{{ route('report.lookup-rfid') }}?rfid=${encodeURIComponent(rfid)}`, {
        headers: {
          'X-Skip-Loader': 'true'
        }
      })
        .then(response => response.json())
        .then(data => {
          rfidLoading.classList.add('hidden');
          rfidStatus.classList.remove('hidden');

          if (data.success) {
            rfidStatus.textContent = `User found: ${data.name} (${data.type === 'student' ? 'Student' : 'Faculty/Staff'})`;
            rfidStatus.className = 'mt-1 text-xs text-green-600 dark:text-green-400';

            const radioToSelect = document.querySelector(`input[name="modal_user_type"][value="${data.type}"]`);
            if (radioToSelect) {
              radioToSelect.checked = true;
              radioToSelect.dispatchEvent(new Event('change'));
            }

            if (data.type === 'student') {
              studentSearchInput.value = data.name;
              studentIdInput.value = data.id;
              studentSuggestionsBox.classList.add('hidden');
            } else if (data.type === 'faculty') {
              facultySearchInput.value = data.name;
              facultyIdInput.value = data.id;
              facultySuggestionsBox.classList.add('hidden');
            }
          } else {
            rfidStatus.textContent = data.message || 'RFID not found.';
            rfidStatus.className = 'mt-1 text-xs text-red-600 dark:text-red-400';
          }
        })
        .catch(err => {
          rfidLoading.classList.add('hidden');
          rfidStatus.classList.remove('hidden');
          rfidStatus.textContent = 'An error occurred during lookup.';
          rfidStatus.className = 'mt-1 text-xs text-red-600 dark:text-red-400';
          console.error(err);
        });
    }

    if (rfidInput) {
      rfidInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          performRfidLookup(this.value);
        }
      });

      let rfidTimer;
      rfidInput.addEventListener('input', function() {
        clearTimeout(rfidTimer);
        const val = this.value.trim();
        if (val.length >= 8) {
          rfidTimer = setTimeout(() => {
            performRfidLookup(val);
          }, 400);
        }
      });
    }

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

    // Auto-focus RFID input when opening modal
    const openModalBtn = document.querySelector('[data-modal-target="NonCirculationModal"]');
    if (openModalBtn) {
      openModalBtn.addEventListener('click', function() {
        setTimeout(() => {
          if (rfidInput) {
            rfidInput.focus();
            rfidInput.value = '';
            if (rfidStatus) {
              rfidStatus.classList.add('hidden');
              rfidStatus.textContent = '';
            }
          }
        }, 300);
      });
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
          fetch(`{{ route('report.non-circulation-search-user') }}?term=${encodeURIComponent(query)}&type=${type}`, {
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