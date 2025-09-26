@use('App\Enum\PermissionsEnum')
@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Home</h1>
@if(auth()->user()->can(PermissionsEnum::VIEW_DASHBOARD))
<div class="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
  <div class="flex flex-col min-h-96 justify-between max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <div>
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Current Users</h5>
      <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Total number of users currently timed-in in the library.</p>
    </div>
    <div class="mb-2">
      <h1 id="timed-in-count" class="text-8xl text-center font-extrabold dark:text-gray-300"></h1>
    </div>
    <button type="button" id="timeout-all-users" class="text-white bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">Timeout All Users</button>
  </div>
  <div class="flex flex-col min-h-96 col-span-3 justify-between max-h-96 p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Monthly Logs</h5>
    <div>
      <canvas id="monthly-logs" width="" height="84"></canvas>
    </div>
  </div>
  <div class="flex flex-col min-h-96 justify-between max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <div>
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Total Books</h5>
      <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Total number of books in the database.</p>
    </div>
    <div class="mb-20">
      <h1 id="book-count" class="text-8xl text-center font-extrabold dark:text-gray-300"></h1>
    </div>
  </div>
  <div class="flex flex-col min-h-96 col-span-3 justify-between max-h-96 p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Transaction History</h5>
    <div>
      <canvas id="transaction-history" width="" height="84"></canvas>
    </div>
  </div>
  <div class="flex flex-col min-h-96 col-span-2 justify-between p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Yearly Book Acquisition</h5>
    <div class="mb-5">
      <canvas id="yearly-books" width="" height="84"></canvas>
    </div>
  </div>
  <div class="flex flex-col min-h-96 col-span-2 justify-between p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Registered Users</h5>
    <div class="mb-5">
      <canvas id="registered-users" width="250" height="250"></canvas>
    </div>
  </div>
  <div class="flex flex-col col-span-4 justify-between p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Top 10 Most Visited Students</h5>
    <div class="mb-5">
      <table class="table-fixed bg-white dark:bg-gray-800 w-full">
        <thead class="bg-blue-400 text-left font-bold text-slate-200 border-2 border-slate-300 dark:border-slate-700">
          <tr>
            <th class="pl-2 border-r w-16 border-slate-300 dark:border-slate-700">Top</th>
            <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Name</th>
            <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Total Visits</th>
            <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Grade Level</th>
            <th class="pl-2 border-r border-slate-300 dark:border-slate-700">Section</th>
          </tr>
        </thead>
        <tbody id="top-students-body">
          <tr>
            <td colspan="5" class="text-center">Loading...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="sticky z-index-100 bottom-10 left-20">
    <button type="button" id="refresh" class="flex text-white bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">
      <span class="">Refresh</span>
      <svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4" />
      </svg>
    </button>
  </div>
</div>
<script>
  // Fetch the current count of active users
  async function fetchActiveCount() {
    try {
      const response = await fetch("{{ route('fetch-current-count') }}");
      const data = await response.json();
      document.getElementById('timed-in-count').textContent = data.active_count;
    } catch (error) {
      document.getElementById('timed-in-count').textContent = '...';
    }
  }
  async function topVisitedStudents() {
    try {
      const response = await fetch("{{ route('fetch-most-visited-students') }}");
      const top = await response.json();

      const tbody = document.getElementById('top-students-body');
      tbody.innerHTML = ''; // clear old rows

      if (top.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center">No data found.</td></tr>`;
        return;
      }
      top.forEach((item, index) => {
        const row = `
        <tr class="text-left border-2 border-slate-300 dark:border-slate-700">
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">${index + 1}</td>
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">
            ${item.last_name}, ${item.first_name} ${item.middle_name ?? ''}
          </td>
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">${item.logs_count}</td>
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">${item.students.level}</td>
          <td class="pb-1 pl-2 border-r border-slate-300 dark:border-slate-700">${item.students.section}</td>
        </tr>
      `;
        tbody.insertAdjacentHTML('beforeend', row);
      });
    } catch (error) {
      console.error('Error fetching top visited students:', error);
      document.getElementById('top-students-body').innerHTML =
        `<tr><td colspan="5" class="text-center">Error loading data.</td></tr>`;
    }
  }
  // Timeout all users
  document.getElementById('timeout-all-users').addEventListener('click', async () => {
    try {
      const response = await fetch("{{ route('timeout-all-users') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
      });
      const data = await response.json();
      location.reload();
    } catch (error) {
      console.error('Error timing out users:', error);
    }
  });
  // Fetch the monthly count of logs
  async function fetchMonthlyCount() {
    try {
      const response = await fetch("{{ route('fetch-monthly-count') }}");
      const data = await response.json();
      const labels = data.map(item => item.month);
      const counts = data.map(item => item.count);
      monthlyLogsLineGraph(labels, counts);
    } catch (error) {
      console.error('Error fetching monthly count:', error);
    }
  }
  // Fetch the total count of books
  async function fetchBookCount() {
    try {
      const response = await fetch("{{ route('fetch-book-count') }}");
      const data = await response.json();
      document.getElementById('book-count').textContent = data.total_books;
    } catch (error) {
      console.error('Error fetching book count:', error);
      document.getElementById('book-count').textContent = '...';
    }
  }
  // Fetch the transaction history
  async function fetchTransactionHistory() {
    try {
      const response = await fetch("{{ route('fetch-transaction-history') }}");
      const data = await response.json();
      const labels = data.transaction_history.map(item => item.month);
      const counts = data.transaction_history.map(item => item.count);
      const borrowed = data.borrowed.map(item => item.count);
      const reserved = data.reserved.map(item => item.count);
      const returned = data.returned.map(item => item.count);
      transactionHistoryBarGraph(labels, counts, borrowed, reserved, returned);
    } catch (error) {
      console.error('Error fetching transaction history:', error);
    }
  }
  // Fetch the yearly acquired books
  async function fetchYearlyAquiredBooks() {
    try {
      const response = await fetch("{{ route('fetch-yearly-aquired-books') }}");
      const data = await response.json();
      const labels = data.map(item => item.year);
      const counts = data.map(item => item.count);
      YearlyBooksDoughnutGraph(labels, counts);
    } catch (error) {
      console.error('Error fetching yearly acquired books:', error);
    }
  }
  // Fetch the registered users
  async function fetchRegisteredUsers() {
    try {
      const response = await fetch("{{ route('fetch-registered-users') }}");
      const data = await response.json();
      const student = data.students || 0;
      const employees = data.employees || 0;
      const visitors = data.visitors || 0;
      const labels = ['Students', 'Faculty & Staff', 'Visitors'];
      const counts = [student, employees, visitors];
      RegisteredUsersPieGraph(labels, counts);
    } catch (error) {
      console.error('Error fetching registered users:', error);
    }
  }
  // Initialize the chart variable
  let transactionHistoryChart = null;
  let monthlyLogsChart = null;
  let yearlyBooksChart = null;
  let registeredUsersChart = null;
  // Create a line graph for monthly logs
  function monthlyLogsLineGraph(labels, counts) {
    const ctx = document.getElementById('monthly-logs').getContext('2d');
    // Check if the chart already exists
    if (monthlyLogsChart) {
      monthlyLogsChart.destroy(); // 👈 Destroy old chart if exists
    }

    monthlyLogsChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Number of Logs',
          data: counts,
          backgroundColor: 'rgba(54, 162, 235, 0.2)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 2,
          pointBackgroundColor: 'white',
          tension: 0.3,
          fill: true,
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true
          }
        },
        plugins: {
          datalabels: false,
        },
      }
    });
  }
  // Check if dark mode is enabled
  const isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
  // Set chart colors based on dark mode
  const chartColors = isDarkMode ? {
    totalBg: 'rgba(54, 162, 235, 0.2)',
    totalBorder: 'rgba(54, 162, 235, 1)',
    borrowedBg: 'rgba(255, 114, 118, 0.2)',
    borrowedBorder: 'rgba(255, 114, 118, 1)',
    reservedBg: 'rgba(254, 221, 0, 0.2)',
    reservedBorder: 'rgba(254, 221, 0, 1)',
    returnedBg: 'rgba(75, 192, 192, 0.2)',
    returnedBorder: 'rgba(75, 192, 192, 1)',
    fontColor: '#fff',
  } : {
    totalBg: 'rgba(54, 162, 235, 0.6)',
    totalBorder: 'rgba(54, 162, 235, 1)',
    borrowedBg: 'rgba(255, 114, 118, 0.6)',
    borrowedBorder: 'rgba(255, 114, 118, 1)',
    reservedBg: 'rgba(254, 221, 0, 0.6)',
    reservedBorder: 'rgba(254, 221, 0, 1)',
    returnedBg: 'rgba(75, 192, 192, 0.6)',
    returnedBorder: 'rgba(75, 192, 192, 1)',
    fontColor: '#111',
  };

  // Create a bar graph for transaction history
  function transactionHistoryBarGraph(labels, counts, borrowed, reserved, returned) {
    const ctx = document.getElementById('transaction-history').getContext('2d');
    // Check if the chart already exists
    if (transactionHistoryChart) {
      transactionHistoryChart.destroy(); // 👈 Destroy old chart if exists
    }

    transactionHistoryChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Total Transactions',
          data: counts,
          backgroundColor: chartColors.totalBg,
          borderColor: chartColors.totalBorder,
          borderWidth: 2,
          pointBackgroundColor: 'white',
          tension: 0.3,
          fill: true,
        }, {
          label: 'Total Borrowed',
          data: borrowed,
          backgroundColor: chartColors.borrowedBg,
          borderColor: chartColors.borrowedBorder,
          borderWidth: 2,
          pointBackgroundColor: 'white',
          tension: 0.3,
          fill: true,
        }, {
          label: 'Total Reserved',
          data: reserved,
          backgroundColor: chartColors.reservedBg,
          borderColor: chartColors.reservedBorder,
          borderWidth: 2,
          pointBackgroundColor: 'white',
          tension: 0.3,
          fill: true,
        }, {
          label: 'Total Returned',
          data: returned,
          backgroundColor: chartColors.returnedBg,
          borderColor: chartColors.returnedBorder,
          borderWidth: 2,
          pointBackgroundColor: 'white',
          tension: 0.3,
          fill: true,
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            labels: {
              color: chartColors.fontColor,
            },
          },
          datalabels: false
        },
        scales: {
          x: {
            ticks: {
              color: chartColors.fontColor,
            },
          },
          y: {
            beginAtZero: true,
            ticks: {
              color: chartColors.fontColor,
            },
          }
        }
      }
    });
  }
  // Create a doughnut graph for yearly acquired books
  function YearlyBooksDoughnutGraph(labels, counts) {
    const ctx = document.getElementById('yearly-books').getContext('2d');
    // Check if the chart already exists
    if (yearlyBooksChart) {
      yearlyBooksChart.destroy(); // 👈 Destroy old chart if exists
    }
    const colors = generateRandomColors(labels.length);
    yearlyBooksChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Yearly Acquired Books',
          data: counts,
          backgroundColor: 'rgba(54, 162, 235, 0.2)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 2,
          pointBackgroundColor: 'white',
          tension: 0.3,
          fill: true,
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true
          }
        },
        plugins: {
          datalabels: false,
        },
      }
    });
  }
  // Create a pie graph for registered users
  function RegisteredUsersPieGraph(labels, counts) {
    const ctx = document.getElementById('registered-users').getContext('2d');
    // Check if the chart already exists
    if (registeredUsersChart) {
      registeredUsersChart.destroy(); // 👈 Destroy old chart if exists
    }
    const colors = generateRandomColors(labels.length);
    registeredUsersChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          label: 'Registered Users',
          data: counts,
          backgroundColor: [
            "rgba(75, 192, 192, 1)",
            "rgba(54, 162, 235, 1)",
            "rgba(255, 182, 115, 1)",
          ],
          hoverOffset: 25,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            labels: {
              color: chartColors.fontColor,
            },
          },
          tooltip: {
            enabled: false,
          },
          datalabels: {
            color: '#fff',
            font: {
              weight: 'bold',
              size: 13,
            },
            align: 'center',
            formatter: (value) => value,
            textAlign: 'center',
            textShadowBlur: 10,
            textShadowColor: 'rgba(0,0,0,0.8)',
          }
        }
      },
    });
  }
  // Generate random colors for the chart
  function generateRandomColors(count) {
    const colors = [];
    for (let i = 0; i < count; i++) {
      const r = Math.floor(Math.random() * 255);
      const g = Math.floor(Math.random() * 255);
      const b = Math.floor(Math.random() * 255);
      colors.push(`rgb(${r}, ${g}, ${b})`);
    }
    return colors;
  }
  const refreshButton = document.getElementById('refresh');
  // Add event listener to the refresh button
  refreshButton.addEventListener('click', () => {
    fetchActiveCount();
    fetchMonthlyCount();
    fetchBookCount();
    fetchTransactionHistory();
    fetchYearlyAquiredBooks();
    fetchRegisteredUsers();
    sessionStorage.setItem('toast-success', 'Data refreshed successfully.');
    location.reload();
  });

  setInterval(fetchActiveCount, 5000);
  setInterval(fetchBookCount, 60000);
  setInterval(fetchTransactionHistory, 60000);
  setInterval(fetchYearlyAquiredBooks, 60000);
  setInterval(fetchRegisteredUsers, 60000);
  setInterval(topVisitedStudents, 60000);

  document.addEventListener('DOMContentLoaded', fetchActiveCount);
  document.addEventListener('DOMContentLoaded', fetchMonthlyCount);
  document.addEventListener('DOMContentLoaded', fetchBookCount);
  document.addEventListener('DOMContentLoaded', fetchTransactionHistory);
  document.addEventListener('DOMContentLoaded', fetchYearlyAquiredBooks);
  document.addEventListener('DOMContentLoaded', fetchRegisteredUsers);
  document.addEventListener('DOMContentLoaded', topVisitedStudents);
</script>
@else

<div class="flex flex-col items-center max-w-sm p-6 my-28 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
  <svg class="w-16 h-16 opacity-70 mb-3 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v3m-3-6V7a3 3 0 1 1 6 0v4m-8 0h10a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1Z" />
  </svg>
  <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Access Restricted</h5>
  <p class="font-normal text-center text-gray-700 dark:text-gray-400">Sorry, you don't have access to view the dashboard page. Please contact your administrator.</p>
</div>

@endif
@endsection