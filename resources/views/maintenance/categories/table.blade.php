@use('App\Enum\PermissionsEnum')
<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
  <form method="GET" class="flex items-center m-2 skip-loader">
    @if(request('search-categories'))
    <input type="hidden" name="search-categories" value="{{ request('search-categories') }}">
    @endif
    <input type="hidden" name="tab" value="{{ request('tab', 'print') }}">
    <label for="per{{ str_replace([' ', '-'], '', $type) }}Page" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
    <input type="number" name="per{{ str_replace([' ', '-'], '', $type) }}Page" id="per{{ str_replace([' ', '-'], '', $type) }}Page" min="1" max="500" value="{{ $perPage }}" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-400 focus:border-primary-400 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">entries per page</span>
  </form>
  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
      <tr>
        <th scope="col" class="px-6 py-3">Name</th>
        <th scope="col" class="px-6 py-3 hidden sm:table-cell">Legend</th>
        <th scope="col" class="px-6 py-3 hidden md:table-cell">Category Type</th>
        <th scope="col" class="px-6 py-3 hidden lg:table-cell">Educational Level</th>
        <th scope="col" class="px-6 py-3 hidden md:table-cell">Duration of Borrow (Days)</th>
        <th scope="col" class="px-6 py-3">Borrowable</th>
        <th scope="col" class="px-6 py-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($categories as $item)
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
          <div class="text-base font-semibold">{{ $item->name }}</div>
          <div class="font-normal text-gray-500 sm:hidden">{{ $item->legend }}</div>
        </th>
        <td class="px-6 py-4 hidden sm:table-cell">{{ $item->legend }}</td>
        <td class="px-6 py-4 hidden md:table-cell">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
            {{ $item->category_type ?? 'Print' }}
          </span>
        </td>
        <td class="px-6 py-4 hidden lg:table-cell">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 uppercase">
            {{ $item->educational_level ?? 'N/A' }}
          </span>
        </td>
        <td class="px-6 py-4 hidden md:table-cell">{{ (int) $item->borrow_duration_days === 0 ? 'Cannot be borrowed' : $item->borrow_duration_days }}</td>
        <td class="px-6 py-4">
          @if((int) $item->borrow_duration_days === 0)
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Not Borrowable</span>
          @else
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Borrowable</span>
          @endif
        </td>
        <td class="px-6 py-4">
          <div class="flex items-center space-x-2">
            @can(PermissionsEnum::EDIT_CATEGORIES)
            <button type="button" data-modal-target="edit-category-modal" data-modal-toggle="edit-category-modal" data-category='@json($item)' class="editBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">Edit</button>
            @endcan
            @can(PermissionsEnum::DELETE_CATEGORIES)
            <button type="button" data-modal-target="delete-category-modal" data-modal-toggle="delete-category-modal" value="{{ $item->id }}" class="deleteBtn inline-flex items-center px-3 py-1.5 text-xs font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800">Delete</button>
            @endcan
          </div>
        </td>
      </tr>
      @empty
      <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
        <td colspan="7" class="px-6 py-4 text-center">No categories found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="p-4">
    {{ $categories->appends(request()->except($pageParam))->links() }}
  </div>
</div>