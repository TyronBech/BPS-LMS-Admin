@use('App\Enum\PermissionsEnum')

<div class="flex items-center justify-end w-full mb-3">
    <form method="GET" class="flex items-center">
        <label for="perPage" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
        <input type="hidden" name="search" value="{{ request('search', '') }}">
        <input type="hidden" name="sort_by" value="{{ request('sort_by', '') }}">
        <input type="hidden" name="sort_order" value="{{ request('sort_order', '') }}">
        <input type="number" name="perPage" id="perPage" min="1" max="500" onchange="this.form.submit()" value="{{ old('perPage', $perPage) }}" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-400 focus:border-primary-400 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" />
        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">entries per page</span>
    </form>
</div>

<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">DDC</th>
                <th scope="col" class="px-6 py-3">Subject</th>
                <th scope="col" class="px-6 py-3 hidden lg:table-cell">Access Codes</th>
                <th scope="col" class="px-6 py-3 hidden xl:table-cell">Book</th>
                <th scope="col" class="px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subjects as $item)
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">{{ $item->ddc ?? 'N/A' }}</td>
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                    <div class="text-base font-semibold break-words">{{ $item->name }}</div>
                    <div class="mt-1 flex flex-wrap gap-1 lg:hidden">
                        @foreach($item->accessCodes->take(2) as $code)
                        <span class="inline-flex items-center rounded-md bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">{{ $code->access_code }}</span>
                        @endforeach
                        @if($item->accessCodes->count() > 2)
                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">+{{ $item->accessCodes->count() - 2 }} more</span>
                        @endif
                    </div>
                </th>
                <td class="px-6 py-4 hidden lg:table-cell">
                    <div class="flex flex-wrap gap-1 max-w-[28rem]">
                        @foreach($item->accessCodes->take(4) as $code)
                        <span class="inline-flex items-center rounded-md bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">{{ $code->access_code }}</span>
                        @endforeach
                        @if($item->accessCodes->count() > 4)
                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">+{{ $item->accessCodes->count() - 4 }} more</span>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 hidden xl:table-cell">{{ $item->book->title ?? 'N/A' }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center space-x-2">
                        @can(PermissionsEnum::EDIT_SUBJECTS)
                        @php
                        $subjectPayload = [
                        'id' => $item->id,
                        'book_id' => $item->book_id,
                        'ddc' => $item->ddc,
                        'name' => $item->name,
                        'access_codes' => $item->accessCodes->pluck('access_code')->values()->all(),
                        ];
                        @endphp
                        <button
                            type="button"
                            data-modal-target="edit-subject-modal"
                            data-modal-toggle="edit-subject-modal"
                            data-subject='{{ json_encode($subjectPayload, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG) }}'
                            class="editSubjectBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">Edit</button>
                        @endcan
                        @can(PermissionsEnum::DELETE_SUBJECTS)
                        <button type="button" data-modal-target="delete-subject-modal" data-modal-toggle="delete-subject-modal" value="{{ $item->id }}" class="deleteSubjectBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800">Delete</button>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td colspan="5" class="px-6 py-4 text-center">No subjects found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-4">
        {{ $subjects->withQueryString()->links() }}
    </div>
</div>

<div id="add-subject-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-3xl max-h-full">
        <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Add New Subject</h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="add-subject-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <form action="{{ route('maintenance.store-subject') }}" method="POST">
                @csrf
                <div class="p-4 md:p-5 space-y-4">
                    <h6 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Subject Information</h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="add_ddc" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">DDC:</label>
                            <input type="text" id="add_ddc" name="ddc" value="{{ old('ddc') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 500">
                        </div>
                        <div>
                            <label for="add_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Subject:</label>
                            <input type="text" id="add_name" name="name" value="{{ old('name') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., Science" required>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gradient-to-r from-slate-50 to-blue-50 p-4 dark:border-gray-600 dark:from-gray-800 dark:to-gray-750">
                        <label for="add_access_code_input" class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Access Codes</label>
                        <p class="mb-3 text-xs text-gray-600 dark:text-gray-300">Type a code and press Enter. Existing codes will appear as suggestions.</p>
                        <div id="add_access_code_chips" class="flex flex-wrap gap-2 mb-2"></div>
                        <div class="relative">
                            <input type="text" id="add_access_code_input" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Type access code and press Enter">
                            <div id="add_access_code_suggestions_wrapper" class="hidden absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-md dark:bg-gray-800 dark:border-gray-600">
                                <ul id="add_access_code_suggestions" class="max-h-44 overflow-y-auto"></ul>
                            </div>
                        </div>
                        <input type="hidden" id="add_access_codes" name="access_codes" value="{{ old('access_codes', '[]') }}">
                    </div>

                    <div>
                        <label for="add_book_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Related Book:</label>
                        <select id="add_book_id" name="book_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
                            <option value="" disabled {{ old('book_id') ? '' : 'selected' }}>Choose a book</option>
                            @foreach($books as $book)
                            <option value="{{ $book->id }}" {{ old('book_id') == $book->id ? 'selected' : '' }}>{{ $book->title }} ({{ $book->accession }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                    <button type="submit" class="text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">Add</button>
                    <button data-modal-hide="add-subject-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-50 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="edit-subject-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-3xl max-h-full">
        <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Edit Subject</h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="edit-subject-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <form action="{{ route('maintenance.update-subject') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="edit_subject_id" id="edit_subject_id" value="">
                <div class="p-4 md:p-5 space-y-4">
                    <h6 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Subject Information</h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="edit_ddc" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">DDC:</label>
                            <input type="text" id="edit_ddc" name="ddc" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., 500">
                        </div>
                        <div>
                            <label for="edit_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Subject:</label>
                            <input type="text" id="edit_name" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gradient-to-r from-slate-50 to-blue-50 p-4 dark:border-gray-600 dark:from-gray-800 dark:to-gray-750">
                        <label for="edit_access_code_input" class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Access Codes</label>
                        <p class="mb-3 text-xs text-gray-600 dark:text-gray-300">Type a code and press Enter. Existing codes will appear as suggestions.</p>
                        <div id="edit_access_code_chips" class="flex flex-wrap gap-2 mb-2"></div>
                        <div class="relative">
                            <input type="text" id="edit_access_code_input" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Type access code and press Enter">
                            <div id="edit_access_code_suggestions_wrapper" class="hidden absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-md dark:bg-gray-800 dark:border-gray-600">
                                <ul id="edit_access_code_suggestions" class="max-h-44 overflow-y-auto"></ul>
                            </div>
                        </div>
                        <input type="hidden" id="edit_access_codes" name="access_codes" value="[]">
                    </div>

                    <div>
                        <label for="edit_book_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Related Book:</label>
                        <select id="edit_book_id" name="book_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
                            <option value="" disabled>Choose a book</option>
                            @foreach($books as $book)
                            <option value="{{ $book->id }}">{{ $book->title }} ({{ $book->accession }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                    <button type="submit" class="text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">Update</button>
                    <button data-modal-hide="edit-subject-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-50 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="delete-subject-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
            <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-subject-modal">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="p-4 md:p-5 text-center">
                <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this subject?</h3>
                <form action="{{ route('maintenance.delete-subject') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="delete_subject_id" id="delete_subject_id" value="">
                    <button data-modal-hide="delete-subject-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
                        Yes, I'm sure
                    </button>
                    <button data-modal-hide="delete-subject-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-50 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">No, cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const suggestRoute = "{{ route('maintenance.subject-access-code-suggestions') }}";

        function parseCodes(rawValue) {
            if (!rawValue) {
                return [];
            }

            try {
                const parsed = JSON.parse(rawValue);
                if (Array.isArray(parsed)) {
                    return parsed.map(item => String(item).trim()).filter(Boolean);
                }
            } catch (e) {
                // Ignore parse error and fallback to comma parsing.
            }

            return String(rawValue).split(',').map(item => item.trim()).filter(Boolean);
        }

        function debounce(callback, delay) {
            let timer = null;
            return function(...args) {
                clearTimeout(timer);
                timer = setTimeout(() => callback.apply(this, args), delay);
            };
        }

        function createAccessCodeManager(prefix, initialValues = []) {
            const chipsContainer = document.getElementById(`${prefix}_access_code_chips`);
            const input = document.getElementById(`${prefix}_access_code_input`);
            const suggestionsWrapper = document.getElementById(`${prefix}_access_code_suggestions_wrapper`);
            const suggestionsList = document.getElementById(`${prefix}_access_code_suggestions`);
            const hiddenInput = document.getElementById(`${prefix}_access_codes`);
            let selectedCodes = [];

            function normalized(code) {
                return String(code || '').trim();
            }

            function syncHiddenInput() {
                hiddenInput.value = JSON.stringify(selectedCodes);
            }

            function renderCodes() {
                chipsContainer.innerHTML = '';

                selectedCodes.forEach((code) => {
                    const chip = document.createElement('span');
                    chip.className = 'inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/50 dark:text-blue-200';
                    chip.innerHTML = `<span>${code}</span>`;

                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'font-bold text-blue-700 hover:text-blue-900 dark:text-blue-200 dark:hover:text-white';
                    removeButton.textContent = '×';
                    removeButton.addEventListener('click', () => removeCode(code));

                    chip.appendChild(removeButton);
                    chipsContainer.appendChild(chip);
                });

                syncHiddenInput();
            }

            function addCode(code) {
                const value = normalized(code);
                if (!value) {
                    return;
                }

                const exists = selectedCodes.some(item => item.toLowerCase() === value.toLowerCase());
                if (exists) {
                    input.value = '';
                    return;
                }

                selectedCodes.push(value);
                renderCodes();
                input.value = '';
            }

            function removeCode(code) {
                selectedCodes = selectedCodes.filter(item => item.toLowerCase() !== String(code).toLowerCase());
                renderCodes();
            }

            function hideSuggestions() {
                suggestionsWrapper.classList.add('hidden');
                suggestionsList.innerHTML = '';
            }

            function showSuggestions(codes) {
                suggestionsList.innerHTML = '';

                if (!codes.length) {
                    hideSuggestions();
                    return;
                }

                codes.forEach((code) => {
                    const item = document.createElement('li');
                    item.className = 'cursor-pointer px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700';
                    item.textContent = code;
                    item.addEventListener('click', () => {
                        addCode(code);
                        hideSuggestions();
                    });
                    suggestionsList.appendChild(item);
                });

                suggestionsWrapper.classList.remove('hidden');
            }

            const requestSuggestions = debounce(async function(query) {
                const cleanQuery = normalized(query);

                if (cleanQuery.length < 1) {
                    hideSuggestions();
                    return;
                }

                try {
                    const response = await fetch(`${suggestRoute}?q=${encodeURIComponent(cleanQuery)}`);
                    if (!response.ok) {
                        hideSuggestions();
                        return;
                    }

                    const data = await response.json();
                    const filtered = (Array.isArray(data) ? data : [])
                        .filter(item => !selectedCodes.some(code => code.toLowerCase() === String(item).toLowerCase()));

                    showSuggestions(filtered);
                } catch (error) {
                    hideSuggestions();
                }
            }, 300);

            input.addEventListener('input', function() {
                requestSuggestions(this.value);
            });

            input.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ',') {
                    event.preventDefault();
                    addCode(this.value);
                    hideSuggestions();
                }

                if (event.key === 'Backspace' && !this.value && selectedCodes.length > 0) {
                    removeCode(selectedCodes[selectedCodes.length - 1]);
                }
            });

            input.addEventListener('blur', function() {
                setTimeout(() => hideSuggestions(), 150);
            });

            document.addEventListener('click', function(event) {
                if (!suggestionsWrapper.contains(event.target) && event.target !== input) {
                    hideSuggestions();
                }
            });

            function setCodes(codes) {
                selectedCodes = parseCodes(JSON.stringify(codes));
                renderCodes();
            }

            selectedCodes = parseCodes(JSON.stringify(initialValues));
            renderCodes();

            return {
                addCode,
                setCodes,
            };
        }

        const addInitialCodes = parseCodes(document.getElementById('add_access_codes').value);
        const addManager = createAccessCodeManager('add', addInitialCodes);
        const editManager = createAccessCodeManager('edit', []);

        const editSubjectButtons = document.querySelectorAll('.editSubjectBtn');
        const editSubjectId = document.getElementById('edit_subject_id');
        const editDdc = document.getElementById('edit_ddc');
        const editName = document.getElementById('edit_name');
        const editBookId = document.getElementById('edit_book_id');

        editSubjectButtons.forEach((button) => {
            button.addEventListener('click', function() {
                const subject = JSON.parse(this.dataset.subject);

                editSubjectId.value = subject.id;
                editDdc.value = subject.ddc || '';
                editName.value = subject.name || '';
                editBookId.value = subject.book_id || '';
                editManager.setCodes(subject.access_codes || []);
            });
        });

        const deleteSubjectButtons = document.querySelectorAll('.deleteSubjectBtn');
        const deleteSubjectId = document.getElementById('delete_subject_id');
        deleteSubjectButtons.forEach((button) => {
            button.addEventListener('click', function() {
                deleteSubjectId.value = this.value;
            });
        });
    });
</script>