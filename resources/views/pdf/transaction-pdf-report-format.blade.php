<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <title>Transactions</title>
</head>
<body>
  <h1 class="text-center mb-4 mt-4 font-semibold text-2xl">Report Document for Users</h1>
  <div class="relative overflow-x-auto">
    @foreach($data as $items)
    <table class="w-full text-sm text-left rtl:text-right text-gray-900 dark:text-gray-400">
      <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
        <tr>
          <th scope="col" class="px-6 py-3">Accession</th>
          <th scope="col" class="px-6 py-3">Title</th>
          <th scope="col" class="px-6 py-3">Name</th>
          <th scope="col" class="px-6 py-3">Transaction</th>
          <th scope="col" class="px-6 py-3">Borrowed</th>
          <th scope="col" class="px-6 py-3">Due</th>
          <th scope="col" class="px-6 py-3">Returned</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $item)
        @if($item->users && $item->books)
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
          <td>{{ $item->books->accession }}</td>
          <td class="px-6 py-4">{{ $item->books->title }}</td>
          <td class="px-6 py-4">{{ $item->users->last_name }}, {{ $item->users->first_name }} {{ $item->users->middle_name }}</td>
          <td class="px-6 py-4">{{ $item->transaction_type }}</td>
          <td class="px-6 py-4">{{ $item->date_borrowed }}</td>
          <td class="px-6 py-4">{{ $item->due_date }}</td>
          <td class="px-6 py-4">{{ $item->return_date ? $item->return_date : '-' }}</td>
        </tr>
        @endif
        @empty
          <td colspan="8" class="text-center">No data found.</td>
        @endforelse
      </tbody>
    </table>
    @endforeach
  </div>
</body>

</html>