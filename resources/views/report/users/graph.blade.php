<div class="container mx-auto">
  <div class="flex flex-col lg:flex-row lg:items-end lg:justify-center gap-3 mb-4">
    {{-- Type Select --}}
    <div class="w-full md:w-auto flex flex-col">
      <label for="type" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Type</label>
      <select id="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
        <option value="" selected disabled>Choose a type</option>
        <option value="hourly">Hourly</option>
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
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
    
    {{-- Date Range Picker --}}
    <div id="date-range-picker-graph" date-rangepicker class="flex flex-col sm:flex-row items-end justify-center gap-2 w-full md:w-auto">
      <div class="flex flex-col w-full sm:w-auto">
        <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Start Date</label>
        <div class="relative w-full">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
            </svg>
          </div>
          <input id="datepicker-range-graph-start" name="graph-start" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 cursor-not-allowed bg-gray-100 dark:bg-gray-800 opacity-60" placeholder="Select date start" disabled>
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
          <input id="datepicker-range-graph-end" name="graph-end" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 cursor-not-allowed bg-gray-100 dark:bg-gray-800 opacity-60" placeholder="Select date end" disabled>
        </div>
      </div>
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
    <h2 class="text-center mb-4 font-semibold text-2xl dark:text-white">Graph Data for Users</h2>
    <div id="validation-warning" class="hidden w-full max-w-2xl mx-auto mb-4"></div>
    <div class="relative h-[300px]">
      <canvas id="logsChart"></canvas>
    </div>
  </div>
</div>

<script type="module">
  let chartInstance = null;

  function parseDate(str) {
    if (!str) return null;
    const parts = str.split('/');
    if (parts.length === 3) {
      return new Date(parts[2], parts[0] - 1, parts[1]);
    }
    return null;
  }

  function validateGraphRange() {
    const type = $('#type').val();
    const startStr = $('#datepicker-range-graph-start').val();
    const endStr = $('#datepicker-range-graph-end').val();

    // If dates are not set, validation warning is cleared, but don't query if one is empty
    if (!startStr && !endStr) {
      $('#validation-warning').addClass('hidden').html('');
      $('#downloadPDF').removeAttr('disabled').removeClass('opacity-50 cursor-not-allowed');
      return true;
    }

    if (!startStr || !endStr) {
      // Wait for both dates to be selected
      return false;
    }

    const start = parseDate(startStr);
    const end = parseDate(endStr);

    if (!start || !end) {
      return false;
    }

    // Check if start date is after end date
    if (start > end) {
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
      return false;
    }

    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    let isValid = true;
    let warningMsg = '';

    if (type === 'hourly') {
      if (diffDays > 0 && startStr !== endStr) {
        isValid = false;
        warningMsg = 'Hourly reports are only available for a single day. Please select the same start and end date.';
      }
    } else if (type === 'daily') {
      if (diffDays > 7) {
        isValid = false;
        warningMsg = 'Daily reports can show a maximum of 7 days. For longer periods, please choose Weekly or Monthly.';
      }
    } else if (type === 'weekly') {
      if (diffDays < 7) {
        isValid = false;
        warningMsg = 'Weekly reports require a date range of at least 7 days.';
      } else if (diffDays > 35) {
        isValid = false;
        warningMsg = 'Weekly reports can show a maximum of 5 weeks (35 days). For longer periods, please choose Monthly.';
      }
    } else if (type === 'monthly') {
      if (diffDays < 30) {
        isValid = false;
        warningMsg = 'Monthly reports require a date range of at least 30 days.';
      } else if (diffDays > 366) {
        isValid = false;
        warningMsg = 'Monthly reports can show a maximum of 1 year (365 days). For longer periods, please choose Yearly.';
      }
    } else if (type === 'yearly') {
      if (diffDays < 365) {
        isValid = false;
        warningMsg = 'Yearly reports require a date range of at least 1 year (365 days).';
      }
    }

    if (!isValid) {
      $('#validation-warning').removeClass('hidden').html(`
        <div class="flex items-center p-4 mb-4 text-sm text-red-800 border border-red-300 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800" role="alert">
          <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 14a1 1 0 0 1-1 1H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 1 1Z"/>
          </svg>
          <span class="sr-only">Warning</span>
          <div>
            <span class="font-medium">Invalid Range:</span> ${warningMsg}
          </div>
        </div>
      `);
      $('#downloadPDF').attr('disabled', 'disabled').addClass('opacity-50 cursor-not-allowed');
      if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
      }
      return false;
    } else {
      $('#validation-warning').addClass('hidden').html('');
      $('#downloadPDF').removeAttr('disabled').removeClass('opacity-50 cursor-not-allowed');
      return true;
    }
  }

  function loadGraph() {
    if (!validateGraphRange()) return;

    let type = $('#type').val();
    let start_date = $('#datepicker-range-graph-start').val();
    let end_date = $('#datepicker-range-graph-end').val();
    let user_type = $('#graph_user_type').val();

    // Fallback to page-level date filters if graph dates are empty
    if (!start_date && !end_date) {
      start_date = $('#datepicker-range-start').val() || $('#summary-datepicker-start').val() || '';
      end_date = $('#datepicker-range-end').val() || $('#summary-datepicker-end').val() || '';
    }

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

  function updateDatePickerState() {
    const isTypeSelected = $('#type').val() !== '';
    const dateStart = $('#datepicker-range-graph-start');
    const dateEnd = $('#datepicker-range-graph-end');

    if (isTypeSelected) {
      dateStart.removeAttr('disabled').removeClass('cursor-not-allowed bg-gray-100 dark:bg-gray-800 opacity-60');
      dateEnd.removeAttr('disabled').removeClass('cursor-not-allowed bg-gray-100 dark:bg-gray-800 opacity-60');
    } else {
      dateStart.attr('disabled', 'disabled').addClass('cursor-not-allowed bg-gray-100 dark:bg-gray-800 opacity-60');
      dateEnd.attr('disabled', 'disabled').addClass('cursor-not-allowed bg-gray-100 dark:bg-gray-800 opacity-60');
    }
  }

  $(document).ready(function() {
    updateDatePickerState(); // Update dates picker state based on initial type (e.g. disabled if empty)
    loadGraph(); // load initial graph

    // Auto reload on type change
    $('#type').on('change', function() {
      $('#datepicker-range-graph-start').val('');
      $('#datepicker-range-graph-end').val('');
      const datepickerEl = document.getElementById('date-range-picker-graph');
      if (datepickerEl && datepickerEl._dateRangePicker) {
        datepickerEl._dateRangePicker.clearSelection();
      }
      updateDatePickerState();
      loadGraph();
    });

    // Auto reload on user type change
    $('#graph_user_type').on('change', function() {
      loadGraph();
    });

    // Auto reload on date changes (when user picks start or end)
    $('#datepicker-range-graph-start, #datepicker-range-graph-end').on('change blur', function() {
      loadGraph();
    });
  });

  // Export chart to PDF
  $('#downloadPDF').click(function() {
    let chartImage = document.getElementById('logsChart').toDataURL("image/png");

    // Fallback to page-level date filters if graph dates are empty
    let start_date = $('#datepicker-range-graph-start').val();
    let end_date = $('#datepicker-range-graph-end').val();
    
    if (!start_date && !end_date) {
      start_date = $('#datepicker-range-start').val() || $('#summary-datepicker-start').val() || '';
      end_date = $('#datepicker-range-end').val() || $('#summary-datepicker-end').val() || '';
    }

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