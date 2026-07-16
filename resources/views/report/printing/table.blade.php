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
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Service</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Topic</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Title of Material</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap text-right">Pages</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap text-right">Amount</th>
        @can(PermissionsEnum::CREATE_PRINTING_ENTRY->value, 'admin')
        <th scope="col" class="px-6 py-3 whitespace-nowrap text-center">Actions</th>
        @endcan
      </tr>
    </thead>
    <tbody>
      @php
        $totalSum = 0;
      @endphp
      @forelse($data as $item)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">
          {{ \Carbon\Carbon::parse($item->printed_at)->format('M j, Y') }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          {{ \Carbon\Carbon::parse($item->printed_at)->format('g:i A') }}
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
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="px-2 py-1 text-xs font-semibold rounded {{ $item->type === 'print' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' }}">
            {{ ucfirst($item->type) }}
          </span>
        </td>
        <td class="px-6 py-4 max-w-xs truncate" title="{{ $item->topic }}">
          {{ $item->topic }}
        </td>
        <td class="px-6 py-4 max-w-xs truncate" title="{{ $item->title_of_material }}">
          {{ $item->title_of_material ?? '-' }}
        </td>
        <td class="px-6 py-4 text-right">
          {{ number_format($item->pages) }}
        </td>
        <td class="px-6 py-4 text-right whitespace-nowrap font-medium text-gray-900 dark:text-white">
          @if(isset($item->amount))
            ₱{{ number_format($item->amount, 2) }}
            @php
              $totalSum += $item->amount;
            @endphp
          @else
            -
          @endif
        </td>
        @can(PermissionsEnum::CREATE_PRINTING_ENTRY->value, 'admin')
        <td class="px-6 py-4 whitespace-nowrap text-center">
          <button data-modal-target="delete-printing-modal" data-modal-toggle="delete-printing-modal" class="deletePrintingBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800" type="button" value="{{ $item->id }}">
            Delete
          </button>
        </td>
        @endcan
      </tr>
      @empty
      <tr>
        <td colspan="@can(PermissionsEnum::CREATE_PRINTING_ENTRY->value, 'admin') 11 @else 10 @endcan" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
          No printing or photocopy records found.
        </td>
      </tr>
      @endforelse
      @if($data->count() > 0)
      <tr class="bg-gray-50 dark:bg-gray-700 font-semibold text-gray-900 dark:text-white">
        <td colspan="9" class="px-6 py-4 text-right uppercase">Total Amount:</td>
        <td class="px-6 py-4 text-right whitespace-nowrap">₱{{ number_format($totalSum, 2) }}</td>
        @can(PermissionsEnum::CREATE_PRINTING_ENTRY->value, 'admin')
        <td></td>
        @endcan
      </tr>
      @endif
    </tbody>
  </table>
</div>
<div class="mb-4">
  {{ $data->links() }}
</div>
