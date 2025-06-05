<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&display=swap');
    * {
      font-family: "IBM Plex Sans", sans-serif;
      font-optical-sizing: auto;
      font-style: normal; 
    }
    header {
      text-align: center;
      margin-bottom: 4px;

    }

    body {
      margin-top: 10px;
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
    h4 {
      text-align: center;
      margin: 10px 0;
      padding: 0;
    }
    img {
      max-width: 100px;
      height: auto;
      margin-top: 5px;
    }
    .logo {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 10px;
    }
    .logo img {
      margin-right: 10px;
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
   <header class="header">
    <div class="logo">
      <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/BPSLogo.png'))) }}" alt="BPS Logo">
      <div>
        <h2>Bicutan Parochial School, Inc.</h2>
        <p>Lower Bicutan, Taguig City</p>
      </div>
    </div>
  </header>
  <h4>{{ $title }}</h4>
  <h4>{{ $date }}</h4>
  <main class="table-container">
    <h4>Report Details</h4>
    <table>
      <thead>
        <tr>
          <th>Accession Number</th>
          <th>Title</th>
          <th>Name</th>
          <th>Borrowed</th>
          <th>Due</th>
          <th>Returned</th>
        </tr>
      </thead>
      <tbody>
        @forelse($data as $item)
        @if($item->book && $item->user)
        <tr>
          <td>{{ $item->book->accession }}</td>
          <td>{{ $item->book->title }}</td>
          <td>{{ $item->user->last_name }}, {{ $item->user->first_name }} {{ $item->user->middle_name ?? '' }}</td>
          <td>{{ $item->date_borrowed }}</td>
          <td>{{ $item->due_date }}</td>
          <td>{{ $item->return_date }}</td>
        </tr>
        @endif
        @empty
        <tr>
          <td colspan="6" class="text-center">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </main>
  <!-- Page break if the content overflows -->
  <div class="page-break"></div>
</body>

</html>