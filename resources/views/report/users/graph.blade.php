<div class="container mx-auto">
  <div class="flex flex-col lg:flex-row lg:items-end lg:justify-center gap-3 mb-4">
    {{-- Date Range Picker --}}
    <div id="date-range-picker-graph" date-rangepicker class="flex flex-col sm:flex-row items-center justify-center gap-2">
      <div class="relative w-full sm:w-auto">
        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
          <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
          </svg>
        </div>
        <input id="datepicker-range-graph-start" name="graph-start" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date start">
      </div>
      <span class="mx-2 text-gray-500 hidden sm:block">to</span>
      <div class="relative w-full sm:w-auto">
        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
          <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
          </svg>
        </div>
        <input id="datepicker-range-graph-end" name="graph-end" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date end">
      </div>
    </div>

    {{-- Type Select --}}
    <div class="w-full md:w-auto">
      <select id="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
        <option value="" selected disabled>Choose a type</option>
        <option value="hourly">Hourly</option>
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
        <option value="yearly">Yearly</option>
      </select>
    </div>

    {{-- PDF Button --}}
    <div class="w-full md:w-auto">
      <button type="button" id="downloadPDF" class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white font-bold py-2 px-4 rounded w-full">
        PDF
      </button>
    </div>
  </div>
</div>

<div class="container mx-auto w-full md:w-[90%] p-4">
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
    <h2 class="text-center mb-4 font-semibold text-2xl dark:text-white">Graph Data for Users</h2>
    <div class="relative h-[300px]">
      <canvas id="logsChart"></canvas>
    </div>
  </div>
</div>

<script type="module">
  let chartInstance = null;

  function loadGraph() {
    let type = $('#type').val();
    let start_date = $('#datepicker-range-graph-start').val();
    let end_date = $('#datepicker-range-graph-end').val();

    $.ajax({
      url: "{{ route('report.user-graph') }}",
      type: "GET",
      data: {
        type: type,
        start_date: start_date,
        end_date: end_date
      },
      success: function(response) {
        let ctx = document.getElementById('logsChart').getContext('2d');
        let chartTitle = 'User Logs';
        if (type === 'daily') {
          chartTitle = 'User Logs (7AM - 5PM)';
        } else if (type === 'weekly') {
          chartTitle = 'User Logs (Mon - Fri)';
        } else if (type === 'monthly') {
          chartTitle = 'User Logs (Monthly Totals)';
        } else if (type === 'yearly') {
          chartTitle = 'User Logs (Yearly Totals)';
        }
        if (chartInstance) {
          chartInstance.destroy();
          chartInstance = null;
        }
        console.log(response);
        chartInstance = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: response.labels,
            datasets: [{
              label: 'Number of Logs',
              data: response.counts,
              fill: false,
              backgroundColor: 'rgba(54, 162, 235, 0.7)',
              borderColor: 'rgba(54, 162, 235, 1)',
              tension: 0.3,
              pointBackgroundColor: 'rgba(54, 162, 235, 1)',
              pointRadius: 4
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              datalabels: false,
              legend: {
                display: false,

              },
              title: {
                display: true,
                text: response.chart_title,
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  precision: 1
                }
              }
            }
          }
        });
      }
    });
  }

  $(document).ready(function() {
    loadGraph(); // load initial graph

    // Auto reload on type change
    $('#type').on('change', function() {
      $('#datepicker-range-graph-start').val('');
      $('#datepicker-range-graph-end').val('');
      const datepickerEl = document.getElementById('date-range-picker-graph');
      if (datepickerEl && datepickerEl._dateRangePicker) {
        datepickerEl._dateRangePicker.clearSelection();
      }
      loadGraph();
    });

    // Auto reload on date changes (when user picks start or end)
    $('#datepicker-range-graph-start, #datepicker-range-graph-end').on('change blur', function() {
      document.getElementById('type').value = '';
      loadGraph();
    });
  });

  // Export chart to PDF
  $('#downloadPDF').click(function() {
    let chartImage = document.getElementById('logsChart').toDataURL("image/png");

    $.ajax({
      url: "{{ route('report.graph-export-pdf') }}",
      type: "POST",
      data: {
        _token: "{{ csrf_token() }}",
        chart: chartImage,
        type: $('#type').val(),
        start_date: $('#datepicker-range-graph-start').val(),
        end_date: $('#datepicker-range-graph-end').val()
      },
      xhrFields: {
        responseType: 'blob'
      },
      success: function(blob) {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = "user-logs-graph {{ date('Y-m-d') }}.pdf";
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
      },
      error: function(e) {
        console.error("Error generating PDF:", e);
      }
    });
  });
</script>