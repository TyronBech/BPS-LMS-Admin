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
      max-width: 500px;
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

    .page-break {
      page-break-before: always;
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
  @php
    $startStr = request('start');
    $endStr = request('end');
    $startDate = null;
    $endDate = null;
    
    if ($startStr && $endStr) {
        try {
            $startDate = \Carbon\Carbon::createFromFormat('m/d/Y', $startStr)->startOfDay();
            $endDate = \Carbon\Carbon::createFromFormat('m/d/Y', $endStr)->endOfDay();
        } catch (\Throwable $e) {}
    }
    
    if (empty($startDate) || empty($endDate)) {
        $sy = $schoolYear ?? null;
        if ($sy && preg_match('/^\d{4}-\d{4}$/', $sy)) {
            list($syStart, $syEnd) = explode('-', $sy);
            $startDate = \Carbon\Carbon::create((int)$syStart, 6, 1)->startOfDay();
            $endDate = \Carbon\Carbon::create((int)$syEnd, 3, 31)->endOfDay();
        } else {
            $now = \Carbon\Carbon::now();
            if ($now->month >= 6) {
                $startDate = \Carbon\Carbon::create($now->year, 6, 1)->startOfDay();
                $endDate = \Carbon\Carbon::create($now->year + 1, 3, 31)->endOfDay();
            } else {
                $startDate = \Carbon\Carbon::create($now->year - 1, 6, 1)->startOfDay();
                $endDate = \Carbon\Carbon::create($now->year, 3, 31)->endOfDay();
            }
        }
    }

    // Generate list of months
    $monthsList = [];
    $current = $startDate->copy()->startOfMonth();
    $endMonth = $endDate->copy()->startOfMonth();
    
    $maxMonths = 60;
    $countMonths = 0;
    while ($current->lte($endMonth) && $countMonths < $maxMonths) {
        $monthsList[] = [
            'label' => $current->format('F Y'),
            'month' => $current->month,
            'year' => $current->year,
            'student_sections' => [],
            'student_total' => 0,
            'employee_total' => 0,
        ];
        $current->addMonth();
        $countMonths++;
    }

    // Group and count from data
    foreach ($data as $item) {
        if ($item->date_borrowed) {
            $borrowedDate = \Carbon\Carbon::parse($item->date_borrowed);
            $mNum = $borrowedDate->month;
            $yNum = $borrowedDate->year;
            
            foreach ($monthsList as &$m) {
                if ($m['month'] == $mNum && $m['year'] == $yNum) {
                    if ($item->user && $item->user->students) {
                        $secName = ($item->user->students->level ?? 'N/A') . ' - ' . ($item->user->students->section ?? 'N/A');
                        if (!isset($m['student_sections'][$secName])) {
                            $m['student_sections'][$secName] = 0;
                        }
                        $m['student_sections'][$secName]++;
                        $m['student_total']++;
                    } elseif ($item->user && $item->user->employees) {
                        $m['employee_total']++;
                    }
                    break;
                }
            }
        }
    }
    unset($m);

    // Compute max sections for students to determine colspan
    $maxSections = 1;
    foreach ($monthsList as $m) {
        if (count($m['student_sections']) > $maxSections) {
            $maxSections = count($m['student_sections']);
        }
    }
  @endphp

  {{-- Page 1: Graphical Summary --}}
  <header>
    <div class="logo">
      <img src="data:image/png;base64,{{ $logo }}" alt="{{ $title }} Logo">
    </div>
    <hr>
  </header>

  <h4 class="title" style="margin-top: 15px;">{{ \App\Helpers\ReportHelper::getFormattedHeaderSuffix('Book Circulation Graphical Summary Report (' . ($userType === 'student' ? 'Students' : 'Employees') . ')', request('start'), request('end'), $data, 'date_borrowed') }}</h4>
  <div style="text-align: center; font-size: 11px; font-weight: bold; margin-bottom: 12px;">
    {{ $date }}
  </div>

  @if(isset($chart) && $chart)
    <div style="text-align: center; margin-top: 20px;">
      <img src="{{ $chart }}" style="max-width: 90%; max-height: 400px; border: 1px solid #ddd; padding: 10px; background-color: #fff;" alt="Circulation Chart">
    </div>
  @else
    <div style="text-align: center; margin-top: 50px; font-size: 14px; color: #777;">
      No graph data available.
    </div>
  @endif

  <div class="user" style="padding-top: 20px;">Generated by: {{ $user }}</div>

  {{-- Page Break to separate Graphical Summary from Summary Table --}}
  <div class="page-break"></div>

  {{-- Page 2: Summary Table --}}
  <header>
    <div class="logo">
      <img src="data:image/png;base64,{{ $logo }}" alt="{{ $title }} Logo">
    </div>
    <hr>
  </header>

  @if($userType === 'student')
    <h4 class="title" style="margin-top: 15px;">{{ \App\Helpers\ReportHelper::getFormattedHeaderSuffix('Book Circulation Summary Report (Students)', request('start'), request('end'), $data, 'date_borrowed') }}</h4>
    <div style="text-align: center; font-size: 11px; font-weight: bold; margin-bottom: 12px;">
      {{ $date }}
    </div>
    
    <main class="table-container" style="margin-top: 10px;">
      <table style="width: 80%; margin: 0 auto; border: 1px solid #ddd;">
        <thead>
          <tr>
            <th style="width: 25%; text-align: center;">Month</th>
            <th colspan="{{ $maxSections }}" style="text-align: center;">Sections & Circulation Counts</th>
            <th style="width: 15%; text-align: center;">Total</th>
          </tr>
        </thead>
        <tbody>
          @php $studentGrandTotal = 0; @endphp
          @foreach($monthsList as $m)
            <tr>
              <td style="text-align: center; font-weight: bold; vertical-align: middle; padding: 6px;">{{ $m['label'] }}</td>
              
              @php 
                $sections = $m['student_sections'];
                $countSections = count($sections);
              @endphp
              
              @if($countSections == 0)
                <td colspan="{{ $maxSections }}" style="text-align: center; color: #777; vertical-align: middle; padding: 6px;">No student borrowings</td>
              @else
                @php $secIndex = 0; @endphp
                @foreach($sections as $secName => $secCount)
                  @php
                    $colspan = ($secIndex === $countSections - 1) ? ($maxSections - $countSections + 1) : 1;
                  @endphp
                  <td colspan="{{ $colspan }}" style="text-align: center; vertical-align: middle; padding: 6px;">
                    <span style="font-weight: bold;">{{ $secName }}</span><br>
                    <span style="font-size: 9px; color: #2d3748;">{{ $secCount }} book{{ $secCount > 1 ? 's' : '' }}</span>
                  </td>
                  @php $secIndex++; @endphp
                @endforeach
              @endif
              
              <td style="text-align: center; font-weight: bold; vertical-align: middle; background-color: #f7fafc; padding: 6px;">
                {{ $m['student_total'] }}
              </td>
            </tr>
            @php $studentGrandTotal += $m['student_total']; @endphp
          @endforeach
          <tr style="font-weight: bold; background-color: #e2e8f0;">
            <td style="text-align: center; padding: 6px;">Total</td>
            <td colspan="{{ $maxSections }}" style="text-align: right; padding-right: 15px; padding: 6px;">Grand Total Borrowed:</td>
            <td style="text-align: center; background-color: #cbd5e1; padding: 6px;">{{ $studentGrandTotal }}</td>
          </tr>
        </tbody>
      </table>
    </main>
  @else
    <h4 class="title" style="margin-top: 15px;">{{ \App\Helpers\ReportHelper::getFormattedHeaderSuffix('Book Circulation Summary Report (Employees)', request('start'), request('end'), $data, 'date_borrowed') }}</h4>
    <div style="text-align: center; font-size: 11px; font-weight: bold; margin-bottom: 12px;">
      {{ $date }}
    </div>
    
    <main class="table-container" style="margin-top: 10px;">
      <table style="width: 50%; margin: 0 auto; border: 1px solid #ddd;">
        <thead>
          <tr>
            <th style="text-align: center; width: 60%;">Month</th>
            <th style="text-align: center; width: 40%;">Total Books Borrowed</th>
          </tr>
        </thead>
        <tbody>
          @php $employeeGrandTotal = 0; @endphp
          @foreach($monthsList as $m)
            <tr>
              <td style="text-align: center; font-weight: bold; padding: 6px;">{{ $m['label'] }}</td>
              <td style="text-align: center; font-weight: bold; color: #2d3748; padding: 6px;">{{ $m['employee_total'] }}</td>
            </tr>
            @php $employeeGrandTotal += $m['employee_total']; @endphp
          @endforeach
          <tr style="font-weight: bold; background-color: #e2e8f0;">
            <td style="text-align: center; padding: 6px;">Total</td>
            <td style="text-align: center; background-color: #cbd5e1; padding: 6px;">{{ $employeeGrandTotal }}</td>
          </tr>
        </tbody>
      </table>
    </main>
  @endif

  <div class="user" style="padding-top: 20px;">Generated by: {{ $user }}</div>

  {{-- Page Break to separate Summary from Detailed Report --}}
  <div class="page-break"></div>

  {{-- Page 3+: Detailed Report --}}
  <header>
    <div class="logo">
      <img src="data:image/png;base64,{{ $logo }}" alt="{{ $title }} Logo">
    </div>
    <hr>
  </header>

  <h4 class="title">{{ \App\Helpers\ReportHelper::getFormattedHeaderSuffix('Book Circulation Detailed Report', request('start'), request('end'), $data, 'date_borrowed') }}</h4>
  <div class="generated-date">{{ $date }}</div>

  <main class="table-container">
    <table>
      <thead>
        <tr>
          <th>Accession Number</th>
          <th>Title</th>
          <th>Name</th>
          @if($userType === 'student')
          <th>Grade & Section</th>
          @else
          <th>Position</th>
          @endif
          @if($type === 'Reserved' || $type === 'All')
          <th>Reserved Date</th>
          <th>Pickup Deadline</th>
          @endif
          @if($type === 'Borrowed' || $type === 'All')
          <th>Borrowed</th>
          <th>Due</th>
          <th>Returned</th>
          @endif
          <th>Transaction Type</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($data as $item)
        @if($item->book && $item->user)
        <tr>
          <td>{{ $item->book->accession }}</td>
          <td>{{ $item->book->title }}</td>
          <td>{{ $item->user->last_name }}, {{ $item->user->first_name }} {{ $item->user->middle_name ?? '' }}</td>
          <td>
            @if($item->user->students)
              {{ $item->user->students->level }} - {{ $item->user->students->section }}
            @elseif($item->user->employees)
              {{ $item->user->employees->employee_role }}
            @else
              N/A
            @endif
          </td>
          @if($type === 'Reserved' || $type === 'All')
          <td>{{ $item->reserved_date ? \Carbon\Carbon::parse($item->reserved_date)->format('M j, Y') : 'Not Reserved' }}</td>
          <td>{{ $item->pickup_deadline ? \Carbon\Carbon::parse($item->pickup_deadline)->format('M j, Y') : 'No Pickup Deadline' }}</td>
          @endif
          @if($type === 'Borrowed' || $type === 'All')
          <td>{{ $item->date_borrowed ? \Carbon\Carbon::parse($item->date_borrowed)->format('M j, Y') : 'Not Borrowed' }}</td>
          <td>{{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('M j, Y') : 'No Due Date' }}</td>
          <td>{{ $item->return_date ? \Carbon\Carbon::parse($item->return_date)->format('M j, Y') : 'Unreturned' }}</td>
          @endif
          <td>{{ $item->transaction_type ?? 'No Type' }}</td>
          <td>{{ $item->status ?? 'No Status' }}</td>
        </tr>
        @endif
        @empty
        <tr>
          <td colspan="12" style="text-align: center;">No data found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
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