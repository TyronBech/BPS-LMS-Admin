@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Home</h1>
<div class="grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
  <div class="flex flex-col min-h-96 justify-between max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <div>
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Current Users</h5>
      <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Total number of users currently timed-in in the library.</p>
    </div>
    <div>
      <h1 id="timed-in-count" class="text-8xl text-center font-extrabold dark:text-gray-300"></h1>
    </div>
    <button type="button" onclick="fetchActiveCount()" class="inline-flex justify-center items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
      Refresh
      <svg class="w-6 h-6 ml-1 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4" />
      </svg>
    </button>
  </div>
  <div class="flex flex-col min-h-96 col-span-3 justify-between max-h-96 p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Monthly Logs</h5>
    <div>
      <canvas id="monthly-logs" width="" height="84"></canvas>
    </div>
    <button type="button" onclick="fetchMonthlyCount()" class="inline-flex justify-center items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
      Refresh
      <svg class="w-6 h-6 ml-1 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4" />
      </svg>
    </button>
  </div>
  <div class="flex flex-col min-h-96 justify-between max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <div>
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Recorded Books</h5>
      <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Total number of books in the database.</p>
    </div>
    <div>
      <h1 id="book-count" class="text-8xl text-center font-extrabold dark:text-gray-300"></h1>
    </div>
    <button type="button" onclick="fetchBookCount()" class="inline-flex justify-center items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
      Refresh
      <svg class="w-6 h-6 ml-1 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
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
      console.log('New active count:', data.active_count);
      document.getElementById('timed-in-count').textContent = data.active_count;
    } catch (error) {
      console.error('Error fetching active count:', error);
      document.getElementById('timed-in-count').textContent = 'ERR';
    }
  }
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
      document.getElementById('book-count').textContent = 'ERR';
    }
  }
  let monthlyLogsChart = null;
  // Create a line graph for monthly logs
  function monthlyLogsLineGraph(labels, counts) {
    const ctx = document.getElementById('monthly-logs').getContext('2d');

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
        }
      }
    });
  }

  setInterval(fetchActiveCount, 5000);
  setInterval(fetchBookCount, 60000);

  document.addEventListener('DOMContentLoaded', fetchActiveCount);
  document.addEventListener('DOMContentLoaded', fetchMonthlyCount);
  document.addEventListener('DOMContentLoaded', fetchBookCount);
</script>
@endsection