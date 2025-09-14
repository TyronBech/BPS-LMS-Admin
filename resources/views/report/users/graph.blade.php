<div class="container flex flex-row justify-center">
  <div id="date-range-picker-graph" date-rangepicker class="flex items-center">
    <div class="relative">
      <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
          <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
        </svg>
      </div>
      <input id="datepicker-range-graph-start" name="graph-start" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date start">
    </div>
    <span class="mx-4 text-gray-500">to</span>
    <div class="relative">
      <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
          <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
        </svg>
      </div>
      <input id="datepicker-range-graph-end" name="graph-end" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date end">
    </div>
  </div>
  <div class="sm:col-span-2 sm:col-start-1 flex items-center ml-3">
    <select id="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
      <option value="daily" selected>Daily</option>
      <option value="weekly">Weekly</option>
      <option value="monthly">Monthly</option>
    </select>
  </div>
  <button type="button" id="filterGraph" class="bg-blue-500 hover:bg-blue-700 active:bg-blue-900 text-white text-sm font-bold py-1 px-4 rounded h-12 mt-2 mb-2 ml-4 mr-4 w-20">Filter</button>
  <button type="button" id="downloadPDF" class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white text-sm font-bold py-1 px-4 rounded h-12 mt-2 mb-2 ml-4 mr-4 w-20">Export PDF</button>
</div>
<div class="container w-[90%] flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Graph Data for Users</h2>
  <canvas id="logsChart" style="max-height:300px;"></canvas>
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

        if (chartInstance) {
          chartInstance.destroy(); // destroy old chart before re-rendering
          chartInstance = null;
        }

        chartInstance = new Chart(ctx, {
          type: 'line',
          data: {
            labels: response.labels,
            datasets: [{
              label: 'Number of Logs',
              data: response.counts,
              fill: false,
              borderColor: 'rgba(54, 162, 235, 1)',
              tension: 0.3,
              pointBackgroundColor: 'rgba(54, 162, 235, 1)',
              pointRadius: 4
            }]
          },
          options: {
            responsive: true,
            plugins: {
              datalabels: false,
              legend: {
                display: false
              },
              title: {
                display: true,
                text: 'User Logs (7AM - 5PM)'
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
  });

  // Apply filter when button clicked
  $('#filterGraph').click(function() {
    loadGraph();
    document.getElementById('logsChart').style.height = '300px'; // Ensure height is set
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
        window.location.reload(true);
      },
      error: function(e) {
        console.error("Error generating PDF:", e);
      }
    });
  });
</script>