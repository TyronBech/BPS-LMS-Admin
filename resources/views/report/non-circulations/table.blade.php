@use('App\Enum\PermissionsEnum')
<div class="relative overflow-x-auto shadow-md sm:rounded-lg mb-6">
  <table class="w-full text-xs text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
      <tr>
        <th scope="col" class="px-4 py-2 whitespace-nowrap">Date</th>
        <th scope="col" class="px-4 py-2 whitespace-nowrap">Time</th>
        <th scope="col" class="px-4 py-2 whitespace-nowrap">RFID</th>
        <th scope="col" class="px-4 py-2 whitespace-nowrap">User Name</th>
        <th scope="col" class="px-4 py-2 whitespace-nowrap">
          @if ($userType == 'students')
            Grade & Section
          @else
            Role
          @endif
        </th>
        <th scope="col" class="px-4 py-2 whitespace-nowrap">Subject</th>
        <th scope="col" class="px-4 py-2 whitespace-nowrap">Teacher</th>
        <th scope="col" class="px-4 py-2 whitespace-nowrap">Status</th>
        @can(PermissionsEnum::CREATE_NON_CIRCULATION_ENTRY->value, 'admin')
        <th scope="col" class="px-4 py-2 whitespace-nowrap">Actions</th>
        @endcan
      </tr>
    </thead>
    <tbody>
      @forelse($data as $item)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
        <td class="px-4 py-2 whitespace-nowrap font-medium text-gray-900 dark:text-white">
          {{ \Carbon\Carbon::parse($item->borrowed_at)->format('M j, Y') }}
        </td>
        <td class="px-4 py-2 whitespace-nowrap">
          {{ \Carbon\Carbon::parse($item->borrowed_at)->format('g:i A') }}
        </td>
        <td class="px-4 py-2 whitespace-nowrap">
          @if($item->student && $item->student->users)
            {{ $item->student->users->rfid ?? 'N/A' }}
          @elseif($item->faculty && $item->faculty->users && !$item->student)
            {{ $item->faculty->users->rfid ?? 'N/A' }}
          @else
            N/A
          @endif
        </td>
        <td class="px-4 py-2 whitespace-nowrap">
          @if($item->student && $item->student->users)
            {{ $item->student->users->last_name }}, {{ $item->student->users->first_name }} {{ $item->student->users->middle_name }}
          @elseif($item->faculty && $item->faculty->users && !$item->student)
            {{ $item->faculty->users->last_name }}, {{ $item->faculty->users->first_name }} {{ $item->faculty->users->middle_name }}
          @else
            N/A
          @endif
        </td>
        <td class="px-4 py-2 whitespace-nowrap">
          @if($item->student)
            {{ $item->student->level }} - {{ $item->student->section }}
          @elseif($item->faculty && !$item->student)
            {{ $item->faculty->employee_role }}
          @else
            N/A
          @endif
        </td>
        <td class="px-4 py-2">
          {{ $item->subject }}
        </td>
        <td class="px-4 py-2 whitespace-nowrap">
          @if($item->student && $item->faculty && $item->faculty->users)
            {{ $item->faculty->users->last_name }}, {{ $item->faculty->users->first_name }}
          @else
            N/A
          @endif
        </td>
        <td class="px-4 py-2 whitespace-nowrap">
          @if($item->returned_at)
            <div>
              <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Returned</span>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              {{ \Carbon\Carbon::parse($item->returned_at)->format('M j, Y g:i A') }}
            </div>
          @else
            <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">Not Returned</span>
          @endif
        </td>
        @can(PermissionsEnum::CREATE_NON_CIRCULATION_ENTRY->value, 'admin')
        <td class="px-4 py-2 whitespace-nowrap">
          <div class="flex items-center gap-2">
            @if(!$item->returned_at)
            <form action="{{ route('report.non-circulation-return') }}" method="POST" class="m-0 p-0">
              @csrf
              <input type="hidden" name="id" value="{{ $item->id }}">
              <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">
                Return
              </button>
            </form>
            @endif
            <button data-modal-target="delete-non-circulation-modal" data-modal-toggle="delete-non-circulation-modal" class="deleteNonCirculationBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800" type="button" value="{{ $item->id }}">
              Delete
            </button>
          </div>
        </td>
        @endcan
      </tr>
      @empty
      <tr>
        <td colspan="@can(PermissionsEnum::CREATE_NON_CIRCULATION_ENTRY->value, 'admin') 9 @else 8 @endcan" class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">
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
