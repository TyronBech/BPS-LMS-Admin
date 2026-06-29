@use('App\Enum\PermissionsEnum')
<div class="relative overflow-x-auto shadow-md sm:rounded-lg mb-6">
  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
      <tr>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Date</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Time</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">RFID</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">User Name</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">
          @if ($userType == 'students')
            Grade & Section
          @else
            Role
          @endif
        </th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Subject</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Teacher</th>
        @can(PermissionsEnum::CREATE_NON_CIRCULATION_ENTRY->value, 'admin')
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Actions</th>
        @endcan
      </tr>
    </thead>
    <tbody>
      @forelse($data as $item)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">
          {{ \Carbon\Carbon::parse($item->borrowed_at)->format('M j, Y') }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          {{ \Carbon\Carbon::parse($item->borrowed_at)->format('g:i A') }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          @if($item->student && $item->student->users)
            {{ $item->student->users->rfid ?? 'N/A' }}
          @elseif($item->faculty && $item->faculty->users && !$item->student)
            {{ $item->faculty->users->rfid ?? 'N/A' }}
          @else
            N/A
          @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          @if($item->student && $item->student->users)
            {{ $item->student->users->last_name }}, {{ $item->student->users->first_name }} {{ $item->student->users->middle_name }}
          @elseif($item->faculty && $item->faculty->users && !$item->student)
            {{ $item->faculty->users->last_name }}, {{ $item->faculty->users->first_name }} {{ $item->faculty->users->middle_name }}
          @else
            N/A
          @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          @if($item->student)
            {{ $item->student->level }} - {{ $item->student->section }}
          @elseif($item->faculty && !$item->student)
            {{ $item->faculty->employee_role }}
          @else
            N/A
          @endif
        </td>
        <td class="px-6 py-4">
          {{ $item->subject }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          @if($item->student && $item->faculty && $item->faculty->users)
            {{ $item->faculty->users->last_name }}, {{ $item->faculty->users->first_name }}
          @else
            N/A
          @endif
        </td>
        @can(PermissionsEnum::CREATE_NON_CIRCULATION_ENTRY->value, 'admin')
        <td class="px-6 py-4 whitespace-nowrap">
          <button data-modal-target="delete-non-circulation-modal" data-modal-toggle="delete-non-circulation-modal" class="deleteNonCirculationBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800" type="button" value="{{ $item->id }}">
            Delete
          </button>
        </td>
        @endcan
      </tr>
      @empty
      <tr>
        <td colspan="@can(PermissionsEnum::CREATE_NON_CIRCULATION_ENTRY->value, 'admin') 8 @else 7 @endcan" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
          No non-circulation entries found.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mb-4">
  {{ $data->links() }}
</div>
