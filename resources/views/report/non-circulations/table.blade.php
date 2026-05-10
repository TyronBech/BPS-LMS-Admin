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
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Subject</th>
        <th scope="col" class="px-6 py-3 whitespace-nowrap">Teacher</th>
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
      </tr>
      @empty
      <tr>
        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
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
