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

    .logo-img {
      display: inline-block;
      vertical-align: middle;
    }

    .logo-img img {
      width: 60px;
      height: auto;
      margin-right: 15px;
    }

    .school-info {
      display: inline-block;
      vertical-align: middle;
      text-align: left;
    }

    .school-info h2 {
      font-size: 16px;
      margin: 0;
      padding: 0;
    }

    .school-info p {
      font-size: 10px;
      margin: 0;
      padding: 0;
      color: #333;
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

    .table-container {
      width: 100%;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    th,
    td {
      border: 1px solid #ddd;
      padding: 5px;
      font-size: 9px;
      word-break: break-word;
      text-align: left;
    }

    th {
      background-color: #cccccc;
      font-weight: bold;
      text-align: center;
    }

    /* Column Widths */
    th:nth-child(1) { width: 9%; } /* Date */
    th:nth-child(2) { width: 12%; } /* Time */
    th:nth-child(3) { width: 13%; } /* Requestor Name */
    th:nth-child(4) { width: 14%; } /* Purpose */
    th:nth-child(5) { width: 8%; } /* Status */
    th:nth-child(6) { width: 9%; } /* Submitted */
    th:nth-child(7) { width: 11%; } /* Action Date */
    th:nth-child(8) { width: 24%; } /* Remarks */

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
  <title>Class Reservations Report</title>
</head>

<body>
  <header>
    <div style="text-align: center; margin-bottom: 10px;">
      @if(isset($logo))
      <div class="logo-img">
        <img src="data:image/png;base64,{{ $logo }}" alt="Organization Logo">
      </div>
      @endif
      <div class="school-info">
        <h2>{{ $school }}</h2>
        <p>{{ $address }}</p>
      </div>
    </div>
  </header>

  <div class="title">{{ $title }}</div>
  <h4>{{ $schoolYear }}</h4>
  <div class="generated-date">Date Extracted: {{ date('Y-m-d') }}</div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Time</th>
          <th>Requestor Name</th>
          <th>Purpose</th>
          <th>Status</th>
          <th>Submitted</th>
          <th>Action Date</th>
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody>
        @forelse($data as $item)
        <tr>
          <td>{{ $item->reservation_date ? $item->reservation_date->format('M d, Y') : 'N/A' }}</td>
          <td>
            {{ \Carbon\Carbon::parse($item->start_time)->format('h:i A') }}
            @if($item->end_time)
              - {{ \Carbon\Carbon::parse($item->end_time)->format('h:i A') }}
            @endif
          </td>
          <td>{{ $item->user ? ($item->user->first_name . ' ' . $item->user->last_name) : 'N/A' }}</td>
          <td>{{ $item->purpose }}</td>
          <td>{{ $item->status }}</td>
          <td>{{ $item->created_at->format('M d, Y') }}</td>
          <td>
            @if($item->status === 'Approved' && $item->approved_at)
                {{ $item->approved_at->format('M d, Y h:i A') }}
            @elseif($item->status === 'Rejected' && $item->rejected_at)
                {{ $item->rejected_at->format('M d, Y h:i A') }}
            @else
                -
            @endif
          </td>
          <td>{{ $item->remarks ?? '-' }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="8" style="text-align: center;">No class reservations found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="user">Prepared by: {{ $user }}</div>
</body>
</html>
