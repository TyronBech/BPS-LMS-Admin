<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    @page {
      margin: 30px 25px 40px 25px;
    }

    body {
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 10px;
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }

    header {
      text-align: center;
      margin-bottom: 10px;
    }

    .logo {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 10px;
    }

    .logo img {
      max-width: 420px;
      margin-right: 10px;
    }

    .school-info {
      text-align: center;
    }

    h2,
    p {
      margin: 0;
      padding: 0;
    }

    .title {
      text-align: center;
      font-size: 14px;
      font-weight: bold;
      margin-top: 10px;
      margin-bottom: 2px;
    }

    h4 {
      text-align: center;
      margin-top: 5px;
      margin-bottom: 3px;
    }

    .generated-date {
      text-align: center;
      margin-bottom: 10px;
    }

    .user {
      text-align: right;
      padding-top: 40px;
      margin-top: 10px;
      margin-right: 5px;
      font-size: 10px;
    }

    .strikethrough {
      text-decoration: line-through;
      color: #6b7280;
    }

    .discounted {
      color: #15803d;
      font-weight: bold;
    }

    .discount-note {
      color: #15803d;
      font-size: 9px;
      margin-left: 4px;
    }

    .summary {
      width: 100%;
      margin-top: 12px;
      font-size: 9.5px;
      border-collapse: collapse;
    }

    .summary td {
      border: 1px solid #d1d5db;
      padding: 4px 6px;
      vertical-align: top;
    }

    .summary-heading {
      font-weight: bold;
      font-size: 10.5px;
      background-color: #f3f4f6;
    }

    .summary-label {
      color: #374151;
    }

    .summary-value {
      text-align: right;
      font-weight: bold;
    }

    .summary-total-row td {
      font-weight: bold;
      background-color: #f9fafb;
    }

    .summary-green {
      color: #15803d;
      font-weight: bold;
    }

    .summary-orange {
      color: #c2410c;
      font-weight: bold;
    }

    .table-container {
      width: 100%;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      table-layout: auto;
    }

    th,
    td {
      border: 1px solid #ddd;
      padding: 4px;
      font-size: 10px;
      word-break: break-word;
      text-align: left;
    }

    th {
      background-color: #cccccc;
      font-weight: bold;
      text-align: center;
    }

    @media print {
      table {
        page-break-inside: auto;
      }

      tr {
        page-break-inside: avoid;
        page-break-after: auto;
      }
    }
  </style>
  <title>{{ $title }}</title>
</head>

<body>
  <header>
    <div class="logo">
      <img src="data:image/png;base64,{{ $logo }}" alt="{{ $title }} Logo">
    </div>
    <hr>
  </header>

  <h4 class="title">{{ $title }}</h4>
  <div class="generated-date">{{ $date }}</div>

  <main class="table-container">
    <div>{{ $reporting_period }}</div>
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Accession</th>
          <th>Book</th>
          <th>Borrowed</th>
          <th>Due</th>
          <th>Returned</th>
          <th>Violation</th>
          <th>Amount</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($data as $item)
        <tr>
          <td>{{ $item->user->first_name }} {{ $item->user->last_name }}</td>
          <td>{{ $item->book->accession }}</td>
          <td>{{ $item->book->title }}</td>
          <td>{{ $item->borrowed }}</td>
          <td>{{ $item->due }}</td>
          <td>{{ $item->returned }}</td>
          <td>{{ $item->violation ?? 'No Violation' }}</td>
          <td>
            @if($item->has_discount)
            <div class="strikethrough">PHP {{ number_format($item->actual_total ?? 0, 2) }}</div>
            <div><span class="discounted">PHP {{ number_format($item->total ?? 0, 2) }}</span><span class="discount-note">{{ $item->discount_percent_label }} discount</span></div>
            @else
            PHP {{ number_format($item->total ?? 0, 2) }}
            @endif
          </td>
          <td>{{ $item->status }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="9" style="text-align: center;">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
    <table class="summary" style="width:48%; float:left;">
      <tr>
        <td class="summary-heading" colspan="2">Payment Summary</td>
      </tr>
      <tr>
        <td class="summary-label">Penalty Amount</td>
        <td class="summary-value">{{ '₱ ' . number_format($summary['penalty_amount'] ?? 0, 2) }}</td>
      </tr>
      <tr>
        <td class="summary-label">Amount Discounted</td>
        <td class="summary-value">{{ '₱ ' . number_format($summary['discounted_amount'] ?? 0, 2) }}</td>
      </tr>
      <tr>
        <td class="summary-label">Amount Waived</td>
        <td class="summary-value">{{ '₱ ' . number_format($summary['waived_amount'] ?? 0, 2) }}</td>
      </tr>
      <tr>
        <td class="summary-label">Not Paid Amount</td>
        <td class="summary-value">{{ '₱ ' . number_format($summary['unpaid_amount'] ?? 0, 2) }}</td>
      </tr>
      <tr>
        <td class="summary-label">Other Amount</td>
        <td class="summary-value">{{ '₱ ' . number_format($summary['other_amount'] ?? 0, 2) }}</td>
      </tr>
      <tr class="summary-total-row">
        <td class="summary-label">Total Collectible</td>
        <td class="summary-value">{{ '₱ ' . number_format($summary['total_collectible'] ?? 0, 2) }}</td>
      </tr>
    </table>

    <div style="width:48%; float:right;">
      <div style="border:1px solid #e5e7eb; padding:12px; border-radius:6px; text-align:center; margin-bottom:8px;">
        <div style="font-size:16px; font-weight:700; color:#15803d;">{{ '₱ ' . number_format($summary['paid_collectible'] ?? 0, 2) }}</div>
        <div style="font-size:11px; color:#6b7280; margin-top:4px;">Paid Collectible</div>
      </div>
      <div style="border:1px solid #e5e7eb; padding:12px; border-radius:6px; text-align:center;">
        <div style="font-size:16px; font-weight:700; color:#c2410c;">{{ '₱ ' . number_format($summary['unpaid_collectible'] ?? ($summary['current_balance'] ?? 0), 2) }}</div>
        <div style="font-size:11px; color:#6b7280; margin-top:4px;">Payment Pending</div>
      </div>
    </div>
    <div style="clear:both;"></div>
  </main>

  <div class="user">Generated by: {{ $user }}</div>

  @if (!app()->runningInConsole())
  @php ob_start(); @endphp
  <script type="text/php">
    if (isset($pdf)) {
      $font = $fontMetrics->getFont("DejaVu Sans", "normal");
      $size = 9;
      $pageText = "Page {PAGE_NUM} of {PAGE_COUNT}";
      $x = $pdf->get_width() - 80;
      $y = $pdf->get_height() - 20;
      $pdf->page_text($x, $y, $pageText, $font, $size);
    }
  </script>
  @php echo ob_get_clean(); @endphp
  @endif
</body>

</html>
