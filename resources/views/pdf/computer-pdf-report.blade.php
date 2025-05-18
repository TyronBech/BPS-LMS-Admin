<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      padding: 0;
      box-sizing: border-box;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    th,
    td {
      padding: 8px 12px;
      text-align: left;
      border: 1px solid #ddd;
      word-wrap: break-word;
      word-break: break-word;
    }

    th {
      background-color: #f2f2f2;
    }

    h1,
    h4 {
      color: #333;
    }

    .total {
      text-align: right;
      font-weight: bold;
    }

    /* Add page-break to handle large tables */
    .page-break {
      page-break-before: always;
    }

    /* Ensure the content fits within a page */
    body {
      max-width: 100%;
      overflow-x: hidden;
    }

    table {
      table-layout: fixed;
      width: 100%;
      max-width: 100%;
    }

    /* Prevent table from overflowing */
    .table-container {
      overflow-x: auto;
      margin-bottom: 30px;
    }

    /* Style for the page to break gracefully */
    @media print {
      body {
        width: 100%;
        margin: 0;
        padding: 0;
      }

      table {
        width: 100%;
      }

      .page-break {
        page-break-before: always;
      }
    }
  </style>
  <title>Report Document</title>
</head>

<body>
  <h1>Report Document</h1>
  <h3>Report Title: {{ $title }}</h3>
  <h3>Report Date: {{ $date }}</h3>
  <div class="table-container">
    <h4>Report Details</h4>
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Level</th>
          <th>Section</th>
          <th>Date</th>
          <th>Time</th>
        </tr>
      </thead>
      <tbody>
        @forelse($data as $item)
        <tr>
          <td>{{ $item->user->last_name }}, {{ $item->user->first_name }} {{ $item->user->middle_name ?? '' }}</td>
          <td>{{ $item->user->students->level }}</td>
          <td>{{ $item->user->students->section }}</td>
          <td>{{ \Carbon\Carbon::parse($item->timestamp)->format('Y-m-d') }}</td>
          <td>{{ \Carbon\Carbon::parse($item->timestamp)->format('H:i:s') }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="text-center">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <!-- Page break if the content overflows -->
  <div class="page-break"></div>
</body>

</html>