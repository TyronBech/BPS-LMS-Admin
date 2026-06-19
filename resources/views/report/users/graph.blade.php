<div class="container mx-auto">
  <div class="flex flex-col lg:flex-row lg:items-end lg:justify-center gap-3 mb-4">
    {{-- Type Select --}}
    <div class="w-full md:w-auto flex flex-col">
      <label for="type" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Type</label>
      <select id="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
        <option value="hourly">Hourly</option>
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly" selected>Monthly</option>
        <option value="yearly">Yearly</option>
      </select>
    </div>

    {{-- User Type Select for Graph --}}
    <div class="w-full md:w-auto flex flex-col">
      <label for="graph_user_type" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">User Type</label>
      <select id="graph_user_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
        <option value="all" selected>All</option>
        <option value="student">Students</option>
        <option value="employee">Faculties & Staff</option>
        <option value="visitor">Visitors</option>
      </select>
    </div>
    
    {{-- Date Range Picker (always enabled, optional custom override) --}}
    <div id="date-range-picker-graph" date-rangepicker class="flex flex-col sm:flex-row items-end justify-center gap-2 w-full md:w-auto">
      <div class="flex flex-col w-full sm:w-auto">
        <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Start Date</label>
        <div class="relative w-full">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
            </svg>
          </div>
          <input id="datepicker-range-graph-start" name="graph-start" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Select date start">
        </div>
      </div>
      <span class="mx-2 text-gray-500 hidden sm:block mb-3">to</span>
      <div class="flex flex-col w-full sm:w-auto">
        <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">End Date</label>
        <div class="relative w-full">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
            </svg>
          </div>
          <input id="datepicker-range-graph-end" name="graph-end" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Select date end">
        </div>
      </div>
    </div>

    {{-- Clear Dates Button --}}
    <div class="w-full md:w-auto flex items-end">
      <button type="button" id="clearGraphDates" class="bg-white hover:bg-gray-100 text-gray-900 border border-gray-300 font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-600 text-sm" title="Clear custom dates and use type defaults">
        Clear Dates
      </button>
    </div>

    {{-- PDF Button --}}
    <div class="w-full md:w-auto">
      <button type="button" id="downloadPDF" class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white font-bold py-2.5 px-4 rounded w-full text-sm">
        PDF
      </button>
    </div>
  </div>
</div>

<div class="container mx-auto w-full md:w-[90%] p-4">
  <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-md">
    <h2 class="text-center mb-1 font-semibold text-2xl dark:text-white">Graph Data for Users</h2>
    <p id="graph-reporting-period" class="text-center text-sm text-gray-500 dark:text-gray-400 mb-4"></p>
    <div id="validation-warning" class="hidden w-full max-w-2xl mx-auto mb-4"></div>
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
    let user_type = $('#graph_user_type').val();

    // If only one date is filled, wait for both
    if ((start_date && !end_date) || (!start_date && end_date)) {
      return;
    }

    // Validate: start must be before or equal to end
    if (start_date && end_date) {
      const startParts = start_date.split('/');
      const endParts = end_date.split('/');
      if (startParts.length === 3 && endParts.length === 3) {
        const s = new Date(startParts[2], startParts[0] - 1, startParts[1]);
        const e = new Date(endParts[2], endParts[0] - 1, endParts[1]);
        if (s > e) {
          $('#validation-warning').removeClass('hidden').html(`
            <div class="flex items-center p-4 mb-4 text-sm text-red-800 border border-red-300 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800" role="alert">
              <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 14a1 1 0 0 1-1 1H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 1 1Z"/>
              </svg>
              <span class="sr-only">Warning</span>
              <div>
                <span class="font-medium">Invalid Range:</span> Start date must be before or equal to the end date.
              </div>
            </div>
          `);
          $('#downloadPDF').attr('disabled', 'disabled').addClass('opacity-50 cursor-not-allowed');
          if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
          }
          return;
        }
      }
    }

    // Clear previous warnings
    $('#validation-warning').addClass('hidden').html('');
    $('#downloadPDF').removeAttr('disabled').removeClass('opacity-50 cursor-not-allowed');

    $.ajax({
      url: "{{ route('report.user-graph') }}",
      type: "GET",
      data: {
        type: type,
        start_date: start_date,
        end_date: end_date,
        user_type: user_type
      },
      success: function(response) {
        let ctx = document.getElementById('logsChart').getContext('2d');
        if (chartInstance) {
          chartInstance.destroy();
          chartInstance = null;
        }

        // Update the reporting period header
        if (response.reporting_period) {
          $('#graph-reporting-period').text(response.reporting_period).show();
        } else {
          $('#graph-reporting-period').text('').hide();
        }

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
    // Load initial graph with default type (monthly = school year)
    loadGraph();

    // Type change: reload graph, keep custom dates if set
    $('#type').on('change', function() {
      loadGraph();
    });

    // User type change: reload graph
    $('#graph_user_type').on('change', function() {
      loadGraph();
    });

    // Date change: if a type is already selected, reload with that type
    // If no type somehow, default to monthly
    $('#datepicker-range-graph-start, #datepicker-range-graph-end').on('changeDate change blur', function() {
      const currentType = $('#type').val();
      if (!currentType) {
        $('#type').val('monthly');
      }
      loadGraph();
    });

    // Clear dates button: remove custom dates and reload with type defaults
    $('#clearGraphDates').on('click', function() {
      $('#datepicker-range-graph-start').val('');
      $('#datepicker-range-graph-end').val('');
      const datepickerEl = document.getElementById('date-range-picker-graph');
      if (datepickerEl && datepickerEl._dateRangePicker) {
        datepickerEl._dateRangePicker.clearSelection();
      }
      loadGraph();
    });
  });

  // Export chart to PDF
  $('#downloadPDF').click(function() {
    let chartImage = document.getElementById('logsChart').toDataURL("image/png");
    let start_date = $('#datepicker-range-graph-start').val();
    let end_date = $('#datepicker-range-graph-end').val();

    $.ajax({
      url: "{{ route('report.graph-export-pdf') }}",
      type: "POST",
      data: {
        _token: "{{ csrf_token() }}",
        chart: chartImage,
        type: $('#type').val(),
        start_date: start_date,
        end_date: end_date,
        user_type: $('#graph_user_type').val()
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