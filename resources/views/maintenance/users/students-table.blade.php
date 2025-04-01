@use('App\Enum\PermissionsEnum')
@php
$studentID = null;
$increment = 0;
@endphp
<div class="container mx-auto px-2 font-sans flex-col">
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right whitespace-nowrap table-auto">
      <thead class="text-xs py-2 text-gray-700 uppercase bg-gray-300 text-center dark:bg-gray-500 dark:text-white">
        <tr>
          <th scope="col" class="p-2 text-center min-w-32">RFID</th>
          <th scope="col" class="p-2 text-center min-w-36">First Name</th>
          <th scope="col" class="p-2 text-center min-w-36">Middle Name</th>
          <th scope="col" class="p-2 text-center min-w-36">Last Name</th>
          <th scope="col" class="p-2 text-center min-w-30">Suffix</th>
          <th scope="col" class="p-2 text-center min-w-32">LRN</th>
          <th scope="col" class="p-2 text-center min-w-24">Grade</th>
          <th scope="col" class="p-2 text-center min-w-24">Section</th>
          <th scope="col" class="p-2 text-center min-w-60">Email</th>
          <th scope="col" class="p-2 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $item)
        <tr class="bg-white border-b text-center dark:bg-gray-800 dark:border-gray-600">
          @if($item->students)
          @php
          $increment++;
          @endphp
          <td class="pb-1 pl-2">{{ $item->rfid }}</td>
          <td class="pb-1">{{ $item->first_name }}</td>
          <td class="pb-1">{{ $item->middle_name ? $item->middle_name : '-' }}</td>
          <td class="pb-1">{{ $item->last_name }}</td>
          <td class="pb-1">{{ $item->suffix ? $item->suffix : '-' }}</td>
          <td class="pb-1">{{ $item->students->lrn }}</td>
          <td class="pb-1">{{ $item->students->grade_level }}</td>
          <td class="pb-1">{{ $item->students->section }}</td>
          <td class="pb-1 px-5">{{ $item->email }}</td>
          <td class="pb-1 flex justify-center">
            @can(PermissionsEnum::EDIT_USERS, 'admin')
            <a href="{{ route('maintenance.edit-student', $item->id) }}" id="editBtn" name="editBtn" class="text-white bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2">Edit</a>
            @endcan
            @php
            $studentID = ['id' => $item->id];
            @endphp
            @can(PermissionsEnum::DELETE_USERS, 'admin')
            <form action="{{ route('maintenance.delete-user', $studentID) }}" method="POST" class="flex items-center justify-center">
              @csrf
              @method('DELETE')
              <button class="focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2" type="submit">
                Delete
              </button>
            </form>
            @endcan
          </td>
          @endif
        </tr>
        @empty
        <tr>
          <td colspan="10" class="text-center py-1.5">No data found.</td>
        </tr>
        @endforelse
        @if($increment == 0)
        <tr>
          <td colspan="10" class="text-center py-1.5">No data found.</td>
        </tr>
        @endif
      </tbody>
    </table>
  </div>
</div>
<!-- <div id="popup-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="popup-modal">
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 md:p-5 text-center">
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this user?</h3>
        <form action="{{ route('maintenance.delete-user', $studentID) }}" method="POST" class="flex items-center justify-center">
          @csrf
          @method('DELETE')
          <button data-modal-hide="popup-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, I'm sure
          </button>
          <button data-modal-hide="popup-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">No, cancel</button>
        </form>
      </div>
    </div>
  </div>
</div> -->