<div class="relative overflow-x-auto shadow-md sm:rounded-lg mb-6">
  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
      <tr>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Date</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Time</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">User Name</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">
          @if ($userType == 'students')
            Grade & Section
          @else
            Role
          @endif
        </th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Type</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Topic</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Title of Material</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Pages</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Amount</th>
      </tr>
    </thead>
    <tbody>
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
        <td class="px-6 py-4 whitespace-nowrap capitalize font-semibold">
          @if($item->type == 'photocopy')
            <span class="text-blue-600 dark:text-blue-400">Photocopy</span>
          @else
            <span class="text-purple-600 dark:text-purple-400">Print</span>
          @endif
        </td>
        <td class="px-6 py-4">
          {{ $item->topic }}
        </td>
        <td class="px-6 py-4">
          {{ $item->title_of_material ?? 'N/A' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          {{ $item->pages }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          @if(isset($item->amount))
            ₱{{ number_format($item->amount, 2) }}
          @else
            N/A
          @endif
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="9" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
          No printing/photocopy entries found.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="mb-4">
  {{ $data->links() }}
</div>
