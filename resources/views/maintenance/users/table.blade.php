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
          <th scope="col" class="p-2 text-center  min-w-32">Employee ID</th>
          <th scope="col" class="p-2 text-center min-w-60">Organization</th>
          <th scope="col" class="p-2 text-center min-w-24">Group</th>
          <th scope="col" class="p-2 text-center min-w-60">Email</th>
          <th scope="col" class="p-2 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $item)
        <tr class="bg-white border-b text-center dark:bg-gray-800 dark:border-gray-600">
          <td class="pb-1 pl-2">{{ $item->rfid }}</td>
          <td class="pb-1">{{ $item->first_name }}</td>
          <td class="pb-1">{{ $item->middle_name ? $item->middle_name : '-' }}</td>
          <td class="pb-1">{{ $item->last_name }}</td>
          <td class="pb-1">{{ $item->suffix ? $item->suffix : '-' }}</td>
          @if($item->students)
          <td class="pb-1">{{ $item->students->lrn }}</td>
          <td class="pb-1">{{ $item->students->grade_level }}</td>
          <td class="pb-1">{{ $item->students->section }}</td>
          @else
          <td class="pb-1">-</td>
          <td class="pb-1">-</td>
          <td class="pb-1">-</td>
          @endif
          @if($item->employees)
          <td class="pb-1 px-5">{{ $item->employees->employee_id }}</td>
          @else
          <td class="pb-1 px-5">-</td>
          @endif
          @if($item->visitors)
          <td class="pb-1 px-5">{{ $item->visitors->school_org }}</td>
          @else
          <td class="pb-1 px-5">-</td>
          @endif
          <td class="pb-1">{{ $item->groups->group_name }}</td>
          <td class="pb-1 px-5">{{ $item->email }}</td>
          <td class="pb-1 flex justify-center">
            <a href="{{ route('maintenance.edit-student', $item->user_id) }}" id="editBtn" name="editBtn" class="text-white bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2">Edit</a>
            <a href="{{ route('maintenance.delete-student', $item->user_id) }}" id="deleteBtn" name="deleteBtn" class="focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2" onclick="return confirm('Are you sure you want to delete this data?')">Delete</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="9" class="text-center py-1.5">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>