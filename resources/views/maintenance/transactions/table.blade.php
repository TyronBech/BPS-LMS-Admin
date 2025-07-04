@use('App\Enum\PermissionsEnum')
<div class="mx-auto px-2 font-sans flex-col">
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right whitespace-nowrap table-auto">
      <thead class="text-xs py-2 text-gray-700 uppercase bg-gray-300 text-center dark:bg-gray-500 dark:text-white">
        <tr>
          <th scope="col" class="p-2 text-center">User</th>
          <th scope="col" class="p-2 text-center">Book</th>
          <th scope="col" class="p-2 text-center">Borrowed Date</th>
          <th scope="col" class="p-2 text-center">Due Date</th>
          <th scope="col" class="p-2 text-center">Returned Date</th>
          <th scope="col" class="p-2 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($transactions as $item)
        <tr class="bg-white border-b text-center dark:bg-gray-800 dark:border-gray-600">
          <td class="max-w-40 h-14">{{ $item->user->first_name }} {{ $item->user->last_name }}</td>
          <td class="max-w-72 overflow-hidden text-ellipsis">{{ $item->book->title }}</td>
          <td class="max-w-60">{{ $item->date_borrowed }}</td>
          <td class="max-w-36">{{ $item->due_date ?? '-' }}</td>
          <td class="max-w-36">{{ $item->returned_date ?? 'Not Returned' }}</td>
          <td class="pb-1 flex justify-center">
            <form action="{{ route('maintenance.show-transactions') }}" method="GET" class="inline-block">
              <button type="submit" id="viewBtn" name="viewBtn" value="{{ $item->id }}" class="focus:outline-none text-white bg-yellow-400 hover:bg-yellow-500 focus:ring-4 focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2 dark:focus:ring-yellow-900">View</button>
            </form>
            @if(auth()->user()->can(PermissionsEnum::EDIT_TRANSACTIONS))
            <button type="button" data-modal-target="edit-privilege-modal" data-modal-toggle="edit-privilege-modal" value="{{ $item->id }}" class="editBtn focus:outline-none text-white bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2">Edit</button>
            @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="3" class="text-center py-1.5">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>