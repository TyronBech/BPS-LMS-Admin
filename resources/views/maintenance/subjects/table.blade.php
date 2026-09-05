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
                <th scope="col" class="px-6 py-3">Subject Access Code</th>
                <th scope="col" class="px-6 py-3 hidden md:table-cell">Linked Books</th>
                <th scope="col" class="px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subjects as $item)
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                    <div class="text-base font-semibold break-words">{{ $item->access_code }}</div>
                        <div class="mt-1 flex flex-wrap gap-1 md:hidden">
                        @foreach($item->books->take(2) as $book)
                        <span class="inline-flex items-center rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200">{{ $book->accession }}</span>
                        @endforeach
                        @if($item->books->count() > 2)
                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">+{{ $item->books->count() - 2 }} more</span>
                        @endif
                    </div>
                </th>
                <td class="px-6 py-4 hidden md:table-cell">
                    @if($item->books->isNotEmpty())
                    <div class="space-y-1">
                        @foreach($item->books->take(3) as $book)
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $book->accession }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 break-words">{{ $book->title }}</div>
                        </div>
                        @endforeach
                        @if($item->books->count() > 3)
                        <div class="text-xs text-gray-500 dark:text-gray-400">+{{ $item->books->count() - 3 }} more</div>
                        @endif
                    </div>
                    @else
                    <span class="text-xs text-gray-500 dark:text-gray-400">Not linked</span>
                    @endif
                </td>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center space-x-2">
                        @can(PermissionsEnum::EDIT_SUBJECTS)
                        @php
                        $subjectPayload = [
                        'id' => $item->id,
                        'access_code' => $item->access_code,
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
                <td colspan="3" class="px-6 py-4 text-center">No subjects found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-4">
        {{ $subjects->withQueryString()->links() }}
    </div>
</div>

<div id="add-subject-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Add New Subject Access Code</h3>
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
                    <div>
                        <label for="add_access_code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Access Code:</label>
                        <input type="text" id="add_access_code" name="access_code" value="{{ old('access_code') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g., MATH101" required>
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
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Edit Subject Access Code</h3>
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
                    <div>
                        <label for="edit_access_code" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Access Code:</label>
                        <input type="text" id="edit_access_code" name="access_code" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
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
                <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this subject access code?</h3>
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
        const editSubjectButtons = document.querySelectorAll('.editSubjectBtn');
        const editSubjectId = document.getElementById('edit_subject_id');
        const editAccessCode = document.getElementById('edit_access_code');

        editSubjectButtons.forEach((button) => {
            button.addEventListener('click', function() {
                const subject = JSON.parse(this.dataset.subject);
                editSubjectId.value = subject.id;
                editAccessCode.value = subject.access_code || '';
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
