<div id="tabular" class="container mx-auto mt-2 mb-4">
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="p-4">
      <h2 class="text-center mb-4 font-semibold text-2xl dark:text-white">Report Table for Computer Use</h2>
      <form method="GET" class="flex items-center">
        <label for="perPage" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="user_type" value="{{ request('user_type') }}">
        <input type="hidden" name="start" value="{{ request('start') }}">
        <input type="hidden" name="end" value="{{ request('end') }}">
        <select name="perPage" id="perPage" onchange="this.form.submit()" class="border border-gray-300 text-xs rounded-lg focus:ring-primary-400 focus:border-primary-400 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
          <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
        </select>
        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">entries per page</span>
      </form>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th scope="col" class="px-6 py-3">Name</th>
            @if($userType == 'students')
            <th scope="col" class="px-6 py-3">Level</th>
            <th scope="col" class="px-6 py-3">Section</th>
            @elseif($userType == 'employees')
            <th scope="col" class="px-6 py-3">Role</th>
            @endif
            <th scope="col" class="px-6 py-3">Date</th>
            <th scope="col" class="px-6 py-3">Time</th>
          </tr>
        </thead>
        <tbody>
          @forelse($data as $item)
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
              {{ $item->user->last_name }}, {{ $item->user->first_name }} {{ $item->user->middle_name ?? '' }}
            </th>
            @if($userType == 'students')
            <td class="px-6 py-4">{{ $item->user->students->level }}</td>
            <td class="px-6 py-4">{{ $item->user->students->section }}</td>
            @elseif($userType == 'employees')
            <td class="px-6 py-4">{{ ucwords($item->user->employees->employee_role) }}</td>
            @endif
            @php $start = \Carbon\Carbon::parse($item->start); @endphp
            <td class="px-6 py-4">{{ $start->format('Y-m-d') }}</td>
            <td class="px-6 py-4">{{ $start->format('g:i A') }}</td>
          </tr>
          @empty
          <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            <td colspan="5" class="px-6 py-4 text-center">No data found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-4">
      {{ $data->withQueryString()->fragment('tabular')->links() }}
    </div>
  </div>
</div>