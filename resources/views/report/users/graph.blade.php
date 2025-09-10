<button type="button" id="downloadPDF" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">Export PDF</button>
<div class="container flex flex-col border-collapse border-2 overflow-x-auto border-slate-900 mt-2 mb-4 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600">
  <h2 class="text-center mb-4 mt-4 font-semibold text-2xl">Graph Data for Users</h2>
  <canvas id="logsChart" height="300"></canvas>
</div>
<script type="module">
  $(document).ready(function() {
    $.ajax({
      url: "{{ route('report.user-graph') }}",
      type: "GET",
      success: function(response) {
        let ctx = document.getElementById('logsChart').getContext('2d');

        new Chart(ctx, {
          type: 'line', // <-- changed from 'bar' to 'line'
          data: {
            labels: response.labels,
            datasets: [{
              label: 'Number of Logs',
              data: response.counts,
              fill: true,
              backgroundColor: 'rgba(54, 162, 235, 0.2)',
              borderColor: 'rgba(54, 162, 235, 1)',
              borderWidth: 2,
              tension: 0.3, // smooth curve
              pointBackgroundColor: 'rgba(54, 162, 235, 1)',
              pointRadius: 5
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                display: false
              },
              title: {
                display: true,
                text: 'Daily User Logs'
              },
              datalabels: {
                display: false
              },
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  precision: 0
                }
              }
            }
          }
        });
      }
    });
  });
  $('#downloadPDF').click(function() {
    let chartImage = document.getElementById('logsChart').toDataURL("image/png");

    $.ajax({
      url: "{{ route('report.graph-export-pdf') }}",
      type: "POST",
      data: {
        _token: "{{ csrf_token() }}",
        chart: chartImage
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