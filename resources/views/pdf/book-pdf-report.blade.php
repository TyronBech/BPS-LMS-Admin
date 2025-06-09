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
      padding: 3px;
      font-size: 10px;
      text-align: left;
      border: 1px solid #ddd;
      word-wrap: break-word;
      word-break: break-word;
    }

    th {
      background-color: #f2f2f2;
    }
    h2, p {
      margin: 0;
      padding: 2px;
    }

    h1,
    h4 {
      color: #333;
    }
    h4 {
      text-align: center;
      margin: 0;
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
        <h2>{{ $school }}</h2>
        <p>{{ $address }}</p>
      </div>
    </div>
  </header>
  <h4>{{ $title }}</h4>
  <h4>{{ $date }}</h4>
  <main class="table-container">
    <table>
      <thead>
        <tr>
          <th>Accession Number</th>
          <th>Call Number</th>
          <th>Title</th>
          <th>Availability</th>
          <th>Condition</th>
        </tr>
      </thead>
      <tbody>
        @forelse($data as $item)
        <tr>
          <td>{{ $item->accession }}</td>
          <td>{{ $item->call_number }}</td>
          <td>{{ $item->title }}</td>
          <td>{{ $item->availability_status }}</td>
          <td>{{ $item->condition_status }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="text-center">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </main>
  <!-- Page break if the content overflows -->
  <div class="page-break"></div>
  @if (!app()->runningInConsole())
  @php ob_start(); @endphp
  <script type="text/php">
    if (isset($pdf)) {
      $font = $fontMetrics->getFont("IBM Plex Sans", "normal");
      $size = 10;
      $pageText = "Page {PAGE_NUM} of {PAGE_COUNT}";
      $x = 520; // Adjust horizontal position
      $y = 820; // Adjust vertical position (lower right corner)
      $pdf->page_text($x, $y, $pageText, $font, $size);
    }
  </script>
  @php echo ob_get_clean(); @endphp
  @endif
</body>

</html>