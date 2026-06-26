@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Circulation History</h1>

  <form action="{{ route('report.circulation-search') }}" method="POST" class="auto-search-form">
    @csrf
    <input type="hidden" name="chart" id="chart-input">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-center gap-3 mb-4">
      {{-- Date Range Picker --}}
      {{-- Date Range Picker --}}
      <div id="date-range-picker" date-rangepicker class="flex flex-col sm:flex-row items-end gap-2 w-full md:w-auto">
        <div class="flex flex-col w-full sm:w-56">
          <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Start Date</label>
          <div class="relative w-full">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
              </svg>
            </div>
            <input id="datepicker-range-start" name="start" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Select date start" value="{{ old('start', $fromInputDate ?? '') }}">
          </div>
        </div>

        <span class="mx-2 text-gray-500 hidden sm:inline mb-3">to</span>

        <div class="flex flex-col w-full sm:w-56">
          <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">End Date</label>
          <div class="relative w-full">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
              </svg>
            </div>
            <input id="datepicker-range-end" name="end" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Select date end" value="{{ old('end', $toInputDate ?? '') }}">
          </div>
        </div>
      </div>

      {{-- Search Input --}}
      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[200px]">
        <label for="search" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Search</label>
        <input type="text" name="search" id="search" placeholder="Name..." class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ old('search', $search ?? '') }}">
      </div>

      {{-- Type Select --}}
      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[180px]">
        <label for="type" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Type</label>
        <select id="type" name="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          @foreach($availability as $typeOption)
          <option value="{{ $typeOption }}" {{ request('type') == $typeOption ? 'selected' : '' }}>{{ $typeOption }}</option>
          @endforeach
        </select>
      </div>

      {{-- User Type Select --}}
      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[180px]">
        <label for="user_type" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">User Type</label>
        <select id="user_type" name="user_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          <option value="student" {{ request('user_type', 'student') == 'student' ? 'selected' : '' }}>Students</option>
          <option value="employee" {{ request('user_type') == 'employee' ? 'selected' : '' }}>Employees</option>
        </select>
      </div>

      {{-- Subject Filter --}}
      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[200px]">
        <label for="subject_id" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Subject</label>
        <select name="subject_id" id="subject_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          <option value="All" {{ ($subjectId ?? 'All') == 'All' ? 'selected' : '' }}>All Subjects</option>
          @foreach($subjects as $subject)
          <option value="{{ $subject->id }}" {{ ($subjectId ?? '') == $subject->id ? 'selected' : '' }}>
            {{ $subject->access_code }}{{ $subject->description ? ' - ' . $subject->description : '' }}
          </option>
          @endforeach
        </select>
      </div>

      {{-- Action Buttons --}}
      <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
        <button type="button" data-clear-url="{{ route('report.circulation') }}" class="btn-clear-filters bg-white hover:bg-gray-100 text-gray-900 border border-gray-300 font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-600 text-sm" title="Clear Filters">Clear</button>
        @can(PermissionsEnum::CREATE_REPORTS)
        <button type="submit" name="submit" value="pdf" class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors text-sm">PDF</button>
        <button type="submit" name="submit" value="excel" class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors text-sm">Excel</button>
        @endcan
      </div>

    </div>
  </form>

  {{-- Circulation Summary Graph --}}
  <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 text-center">Circulation Summary Graph</h2>
    <div class="relative w-full h-[300px]">
      <canvas id="circulationChart"></canvas>
    </div>
  </div>

  <div id="table-container">

    @include('report.transactions.transaction-table')

  </div>
</div>
@endsection
@section('scripts')
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const canvas = document.getElementById('circulationChart');
    const ctx = canvas.getContext('2d');
    const chartLabels = @json($chartLabels);
    const chartCounts = @json($chartCounts);

    const chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: chartLabels,
        datasets: [{
          label: 'Books Borrowed',
          data: chartCounts,
          backgroundColor: 'rgba(54, 162, 235, 0.7)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          datalabels: {
            anchor: 'end',
            align: 'top',
            formatter: function(value) {
              return value > 0 ? value : '';
            },
            font: {
              weight: 'bold'
            }
          },
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1,
              precision: 0
            }
          }
        }
      }
    });

    const pdfButton = document.querySelector('button[value="pdf"]');
    if (pdfButton) {
      pdfButton.addEventListener('click', function () {
        const chartImage = canvas.toDataURL('image/png');
        document.getElementById('chart-input').value = chartImage;
      });
    }

    const form = document.querySelector('.auto-search-form');
    if (form) {
      form.addEventListener('submit', function (e) {
        const submitter = e.submitter || document.activeElement;
        if (submitter && submitter.value === 'pdf') {
          const chartImage = canvas.toDataURL('image/png');
          document.getElementById('chart-input').value = chartImage;
        }
      });
    }

    // Dynamic Chart Update on AJAX table container updates
    const tableContainer = document.getElementById('table-container');
    if (tableContainer) {
      const observer = new MutationObserver(function () {
        const bridge = document.getElementById('chart-data-bridge');
        if (bridge) {
          try {
            const newLabels = JSON.parse(bridge.getAttribute('data-labels'));
            const newCounts = JSON.parse(bridge.getAttribute('data-counts'));
            chart.data.labels = newLabels;
            chart.data.datasets[0].data = newCounts;
            chart.update();
          } catch (e) {
            console.error("Error updating chart:", e);
          }
        }
      });
      observer.observe(tableContainer, { childList: true, subtree: true });
    }
  });
</script>
@endsection