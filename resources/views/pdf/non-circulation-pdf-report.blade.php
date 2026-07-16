<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Non-Circulation Book Report</title>
  <style>
    @page {
      margin: 15mm 15mm 15mm 15mm;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 11px;
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
      margin-bottom: 30px;
    }

    .data-table th {
      background-color: #f3f4f6;
      border: 1px solid #d1d5db;
      padding: 8px 10px;
      font-weight: bold;
      text-align: left;
      text-transform: uppercase;
      font-size: 9px;
      color: #374151;
    }

    .data-table td {
      border: 1px solid #e5e7eb;
      padding: 7px 10px;
      vertical-align: top;
      color: #4b5563;
    }

    .data-table tr:nth-child(even) {
      background-color: #f9fafb;
    }

    .text-center {
      text-align: center;
    }

    .whitespace-nowrap {
      white-space: nowrap;
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
      font-size: 10px;
      color: #4b5563;
    }

    .generated-by {
      font-weight: bold;
      margin-bottom: 5px;
    }

    .timestamp {
      color: #9ca3af;
      font-size: 9px;
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
        <th style="width: 12%;">Date</th>
        <th style="width: 10%;">Time</th>
        <th style="width: 15%;">RFID</th>
        <th style="width: 25%;">User Name</th>
        <th style="width: 18%;">Grade & Section / Role</th>
        <th style="width: 20%;">Subject</th>
      </tr>
    </thead>
    <tbody>
      @forelse($data as $item)
      <tr>
        <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($item->borrowed_at)->format('M j, Y') }}</td>
        <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($item->borrowed_at)->format('g:i A') }}</td>
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
        <td>{{ $item->subject }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="6" class="text-center" style="color: #9ca3af; padding: 20px;">No non-circulation entries found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>

  <table class="metadata-section">
    <tr>
      <td class="metadata-cell">
        <div class="generated-by">Report Generated By: {{ $user }}</div>
      </td>
      <td class="metadata-cell" style="text-align: right; vertical-align: bottom;">
        <div style="color: #9ca3af; font-size: 9px;">Page 1 of 1</div>
      </td>
    </tr>
  </table>

</body>

</html>
