<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Printing & Photocopy Report</title>
  <style>
    @page {
      margin: 15mm 15mm 15mm 15mm;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 10px;
      color: #333;
      margin: 0;
      padding: 0;
    }

    /* Header styling matching standard reports */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    .header-logo-cell {
      width: 80px;
      vertical-align: middle;
      padding: 0;
    }

    .header-logo {
      height: 70px;
      width: auto;
    }

    .header-text-cell {
      vertical-align: middle;
      padding-left: 15px;
    }

    .school-name {
      font-size: 15px;
      font-weight: bold;
      color: #1e3a8a;
      margin: 0 0 3px 0;
      text-transform: uppercase;
    }

    .school-address {
      font-size: 10px;
      color: #4b5563;
      margin: 0 0 3px 0;
    }

    .school-contact {
      font-size: 9px;
      color: #6b7280;
      margin: 0;
    }

    .report-title {
      font-size: 13px;
      font-weight: bold;
      text-align: center;
      margin: 15px 0 5px 0;
      color: #111827;
    }

    .report-period {
      font-size: 10px;
      text-align: center;
      margin: 0 0 20px 0;
      color: #4b5563;
      font-style: italic;
    }

    /* Table styling */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    .data-table th {
      background-color: #f3f4f6;
      border: 1px solid #d1d5db;
      padding: 6px 8px;
      font-weight: bold;
      text-align: left;
      text-transform: uppercase;
      font-size: 8px;
      color: #374151;
    }

    .data-table td {
      border: 1px solid #e5e7eb;
      padding: 6px 8px;
      vertical-align: top;
      color: #4b5563;
    }

    .data-table tr:nth-child(even) {
      background-color: #f9fafb;
    }

    .text-center {
      text-align: center;
    }

    .text-right {
      text-align: right;
    }

    .whitespace-nowrap {
      white-space: nowrap;
    }

    .total-row {
      background-color: #f3f4f6 !important;
      font-weight: bold;
    }

    .total-row td {
      border-top: 2px solid #d1d5db;
      border-bottom: 2px solid #d1d5db;
      color: #111827;
    }

    /* Footer / Metadata styling */
    .metadata-section {
      width: 100%;
      margin-top: 30px;
      border-collapse: collapse;
    }

    .metadata-cell {
      width: 50%;
      vertical-align: top;
      font-size: 9px;
      color: #4b5563;
    }

    .generated-by {
      font-weight: bold;
      margin-bottom: 5px;
    }

    .timestamp {
      color: #9ca3af;
      font-size: 8px;
    }
  </style>
</head>

<body>

  <table class="header-table">
    <tr>
      <td class="header-logo-cell">
        <img class="header-logo" src="data:image/png;base64,{{ $logo }}" alt="School Logo">
      </td>
      <td class="header-text-cell">
        <h1 class="school-name">{{ $school }}</h1>
        <p class="school-address">{{ $address }}</p>
        <p class="school-contact">School Year: {{ $schoolYear }}</p>
      </td>
    </tr>
  </table>

  <h2 class="report-title">{{ $title }}</h2>
  <p class="report-period">{{ $date }}</p>

  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 11%;">Date</th>
        <th style="width: 8%;">Time</th>
        <th style="width: 12%;">RFID</th>
        <th style="width: 22%;">User Name</th>
        <th style="width: 15%;">Grade/Section/Role</th>
        <th style="width: 8%;">Service</th>
        <th style="width: 12%;">Topic</th>
        <th style="width: 15%;">Title of Material</th>
        <th style="width: 6%; text-align: right;">Pages</th>
        <th style="width: 10%; text-align: right;">Amount</th>
      </tr>
    </thead>
    <tbody>
      @php
        $totalSum = 0;
      @endphp
      @forelse($data as $item)
      <tr>
        <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($item->printed_at)->format('M j, Y') }}</td>
        <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($item->printed_at)->format('g:i A') }}</td>
        <td>
          @if($item->student && $item->student->users)
            {{ $item->student->users->rfid ?? 'N/A' }}
          @elseif($item->faculty && $item->faculty->users && !$item->student)
            {{ $item->faculty->users->rfid ?? 'N/A' }}
          @else
            N/A
          @endif
        </td>
        <td>
          @if($item->student && $item->student->users)
            {{ $item->student->users->last_name }}, {{ $item->student->users->first_name }} {{ $item->student->users->middle_name }}
          @elseif($item->faculty && $item->faculty->users && !$item->student)
            {{ $item->faculty->users->last_name }}, {{ $item->faculty->users->first_name }} {{ $item->faculty->users->middle_name }}
          @else
            N/A
          @endif
        </td>
        <td>
          @if($item->student)
            {{ $item->student->level }} - {{ $item->student->section }}
          @elseif($item->faculty && !$item->student)
            {{ $item->faculty->employee_role }}
          @else
            N/A
          @endif
        </td>
        <td>{{ ucfirst($item->type) }}</td>
        <td>{{ $item->topic }}</td>
        <td>{{ $item->title_of_material ?? '-' }}</td>
        <td class="text-right">{{ number_format($item->pages) }}</td>
        <td class="text-right whitespace-nowrap">
          @if(isset($item->amount))
            ₱{{ number_format($item->amount, 2) }}
            @php
              $totalSum += $item->amount;
            @endphp
          @else
            -
          @endif
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="10" class="text-center" style="color: #9ca3af; padding: 20px;">No printing or photocopy records found.</td>
      </tr>
      @endforelse
      @if($data->count() > 0)
      <tr class="total-row">
        <td colspan="9" class="text-right">Total Amount:</td>
        <td class="text-right whitespace-nowrap">₱{{ number_format($totalSum, 2) }}</td>
      </tr>
      @endif
    </tbody>
  </table>

  <table class="metadata-section">
    <tr>
      <td class="metadata-cell">
        <div class="generated-by">Report Generated By: {{ $user }}</div>
      </td>
      <td class="metadata-cell" style="text-align: right; vertical-align: bottom;">
        <div style="color: #9ca3af; font-size: 8px;">Page 1 of 1</div>
      </td>
    </tr>
  </table>

</body>

</html>
