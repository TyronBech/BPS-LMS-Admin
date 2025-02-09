<div class="mx-auto px-2 font-sans flex-col">
  <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right">
      <thead class="text-xs py-2 text-gray-700 uppercase bg-gray-300 text-center">
        <tr>
          <th scope="col" class="p-2 text-center">First Name</th>
          <th scope="col" class="p-2 text-center">Middle Name</th>
          <th scope="col" class="p-2 text-center">Last Name</th>
          <th scope="col" class="p-2 text-center">Email</th>
          <th scope="col" class="p-2 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($admins as $admin)
        <tr class="bg-white border-b text-center">
          <td>{{ $admin->first_name }}</td>
          <td>{{ $admin->middle_name }}</td>
          <td>{{ $admin->last_name }}</td>
          <td>{{ $admin->email }}</td>
          <td class="pb-1 flex justify-center">
            <a href="{{ route('maintenance.edit-admin', $admin->id) }}" id="editBtn" name="editBtn" class="text-white bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2">Edit</a>
            <a href="#" id="deleteBtn" name="deleteBtn" class="focus:outline-none text-white bg-red-500 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2 me-2 my-2" onclick="return confirm('Are you sure you want to delete this data?')">Delete</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="4" class="text-center py-1.5">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>