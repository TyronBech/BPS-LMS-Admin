@use('App\Enum\PermissionsEnum')
@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Home</h1>
@if(auth()->user()->can(PermissionsEnum::VIEW_DASHBOARD))
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
  <div class="flex flex-col min-h-96 justify-between p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <div>
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Current Users</h5>
      <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Total number of users currently timed-in in the library.</p>
    </div>
    <div class="mb-2">
      <h1 id="timed-in-count" class="text-8xl text-center font-extrabold dark:text-gray-300"></h1>
    </div>
    <button type="button" id="timeout-all-users" class="text-white bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">Timeout All Users</button>
  </div>
  <div class="flex flex-col min-h-96 md:col-span-1 lg:col-span-3 justify-between p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Monthly Logs</h5>
    <div class="relative h-full">
      <canvas id="monthly-logs"></canvas>
    </div>
  </div>
  <div class="flex flex-col min-h-96 justify-between p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <div>
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Total Books</h5>
      <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Total number of books in the database.</p>
    </div>
    <div class="mb-20">
      <h1 id="book-count" class="text-8xl text-center font-extrabold dark:text-gray-300"></h1>
    </div>
  </div>
  <div class="flex flex-col min-h-96 md:col-span-1 lg:col-span-3 justify-between p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Transaction History</h5>
    <div class="relative h-full">
      <canvas id="transaction-history"></canvas>
    </div>
  </div>
  <div class="flex flex-col min-h-96 col-span-1 md:col-span-1 lg:col-span-2 justify-between p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Yearly Book Acquisition</h5>
    <div class="relative h-full mb-5">
      <canvas id="yearly-books"></canvas>
    </div>
  </div>
  <div class="flex flex-col min-h-96 col-span-1 md:col-span-1 lg:col-span-2 justify-between p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Registered Users</h5>
    <div class="relative h-full mb-5">
      <canvas id="registered-users"></canvas>
    </div>
  </div>
  <div class="flex flex-col col-span-1 md:col-span-2 lg:col-span-4 justify-between p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-6 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
      Top 6 Most Visited Students per Grade Level
    </h5>
    <div id="date-range-picker" date-rangepicker class="flex items-center mb-5">
      <div class="relative">
        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
          <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
          </svg>
        </div>
        <input id="datepicker-range-start-top-students" name="start_top_students" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date start">
      </div>
      <span class="mx-4 text-gray-500">to</span>
      <div class="relative">
        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
          <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
          </svg>
        </div>
        <input id="datepicker-range-end-top-students" name="end_top_students" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date end">
      </div>
    </div>
    <div id="top-students-container" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <p class="text-center text-gray-500">Loading...</p>
    </div>
  </div>
  <div class="flex flex-col col-span-1 md:col-span-2 lg:col-span-4 justify-between p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-6 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
      Top 3 Students with the Most Borrowed Books per Grade Level
    </h5>
    <div id="date-range-picker-borrowed" date-rangepicker class="flex items-center mb-5">
      <div class="relative">
        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
          <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
          </svg>
        </div>
        <input id="datepicker-range-start-top-borrowed" name="start_top_borrowed" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date start">
      </div>
      <span class="mx-4 text-gray-500">to</span>
      <div class="relative">
        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
          <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
          </svg>
        </div>
        <input id="datepicker-range-end-top-borrowed" name="end_top_borrowed" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date end">
      </div>
    </div>
    <div id="top-borrowed-container" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <p class="text-center text-gray-500">Loading...</p>
    </div>
  </div>
  <div class="flex flex-col min-h-96 col-span-1 md:col-span-1 lg:col-span-2 justify-between max-h-96 p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Top 5 Most Borrowed Books</h5>
    <div class="relative h-full">
      <canvas id="top-borrowed-books"></canvas>
    </div>
  </div>
  <div class="flex flex-col min-h-96 col-span-1 md:col-span-1 lg:col-span-2 justify-between max-h-96 p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Top 5 Most Borrowed Books per Category</h5>
    <div class="relative h-full">
      <canvas id="top-borrowed-categories"></canvas>
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
      let url = "{{ route('fetch-monthly-count') }}";
      const response = await fetch(url);
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
      const labels = data.labels;
      const counts = data.total;
      const borrowed = data.borrowed;
      const reserved = data.reserved;
      const returned = data.returned;

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
  async function topVisitedStudents(start, end) {
    try {
      let url = "{{ route('fetch-most-visited-students') }}";
      url = buildUrl(url, start, end);
      const response = await fetch(url);
      const data = await response.json();

      const container = document.getElementById('top-students-container');
      container.innerHTML = ''; // clear old content

      if (!data || data.length === 0) {
        container.innerHTML = `<p class="text-center text-gray-500">No data found.</p>`;
        return;
      }

      data.forEach(levelData => {
        const {
          level,
          students
        } = levelData;

        // Filter out students with 0 visits
        const filteredStudents = (students || []).filter(s => s.logs_count > 0);

        // Skip this level if no student has visits
        if (filteredStudents.length === 0) return;

        let tableHTML = `
        <div class="bg-white dark:bg-gray-800 border border-slate-300 dark:border-slate-700 rounded-lg shadow-sm overflow-hidden">
          <h6 class="bg-blue-400 text-white text-lg font-semibold px-3 py-2">
            Grade ${level}
          </h6>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="text-left font-bold text-slate-700 dark:text-slate-200 border-b border-slate-300 dark:border-slate-700">
                <tr>
                  <th class="px-3 py-2 w-1/6">Top</th>
                  <th class="px-3 py-2 w-2/6">Student</th>
                  <th class="px-3 py-2 w-1/6">Visits</th>
                  <th class="px-3 py-2 w-2/6">Section</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-300 dark:divide-slate-700">
      `;
        let rank = 1;
        filteredStudents.forEach(student => {
          const s = student.students;
          const fullName = `${student.last_name}, ${student.first_name} ${student.middle_name ?? ''}`.trim();

          tableHTML += `
          <tr>
            <td class="px-3 py-2">${rank++}</td>
            <td class="px-3 py-2 truncate" title="${fullName}">${fullName}</td>
            <td class="px-3 py-2">${student.logs_count}</td>
            <td class="px-3 py-2 truncate" title="${s?.section ?? ''}">${s?.section ?? ''}</td>
          </tr>
        `;
        });

        tableHTML += `
              </tbody>
            </table>
          </div>
        </div>
      `;

        container.insertAdjacentHTML('beforeend', tableHTML);
      });

      // If no grade level had students with visits
      if (container.innerHTML.trim() === '') {
        container.innerHTML = `<p class="text-center text-gray-500">No students with visits found.</p>`;
      }
    } catch (error) {
      console.error('Error fetching top visited students:', error);
      document.getElementById('top-students-container').innerHTML =
        `<p class="text-center text-red-500">Error loading data.</p>`;
    }
  }
  async function topBorrowedStudents(start, end) {
    try {
      let url = "{{ route('fetch-most-borrowed-students') }}";
      url = buildUrl(url, start, end);
      const response = await fetch(url);
      const data = await response.json();

      const container = document.getElementById('top-borrowed-container');
      container.innerHTML = ''; // Clear previous content

      if (!data || data.length === 0) {
        container.innerHTML = `<p class="text-center text-gray-500">No data found.</p>`;
        return;
      }

      data.forEach(levelData => {
        const {
          level,
          students
        } = levelData;

        // Filter out students with 0 borrowed books
        const filteredStudents = (students || []).filter(s => s.borrow_count > 0);
        if (filteredStudents.length === 0) return;

        let tableHTML = `
      <div class="bg-white dark:bg-gray-800 border border-slate-300 dark:border-slate-700 rounded-lg shadow-sm overflow-hidden">
        <h6 class="bg-yellow-400 text-gray-900 text-lg font-semibold px-3 py-2">
          Grade ${level}
        </h6>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="text-left font-bold text-slate-700 dark:text-slate-200 border-b border-slate-300 dark:border-slate-700">
              <tr>
                <th class="px-3 py-2 w-1/6">Top</th>
                <th class="px-3 py-2 w-2/6">Student</th>
                <th class="px-3 py-2 w-1/6">Borrowed</th>
                <th class="px-3 py-2 w-2/6">Section</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-300 dark:divide-slate-700">
      `;
        let rank = 1;
        filteredStudents.forEach(student => {
          const s = student.students;
          const fullName = `${student.last_name}, ${student.first_name} ${student.middle_name ?? ''}`.trim();

          tableHTML += `
        <tr>
          <td class="px-3 py-2">${rank++}</td>
          <td class="px-3 py-2 truncate" title="${fullName}">${fullName}</td>
          <td class="px-3 py-2">${student.borrow_count}</td>
          <td class="px-3 py-2 truncate" title="${s?.section ?? ''}">${s?.section ?? ''}</td>
        </tr>
      `;
        });

        tableHTML += `
            </tbody>
          </table>
        </div>
      </div>
      `;

        container.insertAdjacentHTML('beforeend', tableHTML);
      });

      if (container.innerHTML.trim() === '') {
        container.innerHTML = `<p class="text-center text-gray-500">No borrowed book data found.</p>`;
      }
    } catch (error) {
      console.error('Error fetching top borrowed students:', error);
      document.getElementById('top-borrowed-container').innerHTML =
        `<p class="text-center text-red-500">Error loading data.</p>`;
    }
  }
  async function fetchTopBorrowedBooks() {
    try {
      const response = await fetch("{{ route('fetch-top-books-borrowed') }}");
      const data = await response.json();
      const labels = data.labels;
      const counts = data.counts;
      topBorrowedBooks(labels, counts);
    } catch (error) {
      console.error('Error fetching top borrowed books:', error);
    }
  }
  async function fetchTopBorrowedCategories() {
    try {
      const response = await fetch("{{ route('fetch-top-categories-borrowed') }}");
      const data = await response.json();
      const labels = data.labels;
      const counts = data.counts;
      topBorrowedCategories(labels, counts);
    } catch (error) {
      console.error('Error fetching top borrowed books:', error);
    }
  }
  // Initialize the chart variable
  let transactionHistoryChart = null;
  let monthlyLogsChart = null;
  let yearlyBooksChart = null;
  let registeredUsersChart = null;
  let topBorrowedBooksChart = null;
  let topBorrowedCategoriesChart = null;
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
        maintainAspectRatio: false,
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
        maintainAspectRatio: false,
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
        maintainAspectRatio: false,
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
  // Fetch top borrowed books
  function topBorrowedBooks(labels, counts) {
    const ctx = document.getElementById('top-borrowed-books').getContext('2d');

    // Check if the chart already exists
    if (topBorrowedBooksChart) {
      topBorrowedBooksChart.destroy(); // 👈 Destroy old chart if exists
    }

    topBorrowedBooksChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Top Borrowed Books',
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
        maintainAspectRatio: false,
        indexAxis: 'y',
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
  // Fetch top borrowed categories
  function topBorrowedCategories(labels, counts) {
    const ctx = document.getElementById('top-borrowed-categories').getContext('2d');

    // Check if the chart already exists
    if (topBorrowedCategoriesChart) {
      topBorrowedCategoriesChart.destroy(); // 👈 Destroy old chart if exists
    }

    topBorrowedCategoriesChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Top Borrowed Categories',
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
        maintainAspectRatio: false,
        indexAxis: 'y',
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

  // helper to build query string
  function buildUrl(url, start, end) {
    if (!start || !end) return url;
    const sep = url.includes('?') ? '&' : '?';
    return `${url}${sep}start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`;
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
    topVisitedStudents();
    topBorrowedStudents();
    fetchTopBorrowedBooks();
    fetchTopBorrowedCategories();
    sessionStorage.setItem('toast-success', 'Data refreshed successfully.');
    location.reload();
  });

  // listen for changes on the top-students date pickers
  const topStudentsStartDatepicker = document.getElementById('datepicker-range-start-top-students');
  const topStudentsEndDatepicker = document.getElementById('datepicker-range-end-top-students');

  const handleTopStudentsDateChange = () => {
    const start = topStudentsStartDatepicker.value;
    const end = topStudentsEndDatepicker.value;
    if (start && end) {
      topVisitedStudents(start, end);
    }
  };

  if (topStudentsStartDatepicker) {
    topStudentsStartDatepicker.addEventListener('changeDate', handleTopStudentsDateChange);
  }
  if (topStudentsEndDatepicker) {
    topStudentsEndDatepicker.addEventListener('changeDate', handleTopStudentsDateChange);
  }


  // listen for changes on the top-borrowed date pickers
  const topBorrowedStartDatepicker = document.getElementById('datepicker-range-start-top-borrowed');
  const topBorrowedEndDatepicker = document.getElementById('datepicker-range-end-top-borrowed');

  const handleTopBorrowedDateChange = () => {
    const start = topBorrowedStartDatepicker.value;
    const end = topBorrowedEndDatepicker.value;
    if (start && end) {
      topBorrowedStudents(start, end);
    }
  };

  if (topBorrowedStartDatepicker) {
    topBorrowedStartDatepicker.addEventListener('changeDate', handleTopBorrowedDateChange);
  }
  if (topBorrowedEndDatepicker) {
    topBorrowedEndDatepicker.addEventListener('changeDate', handleTopBorrowedDateChange);
  }


  // Update page load fetches to pass no range (initial)
  document.addEventListener('DOMContentLoaded', fetchActiveCount);
  document.addEventListener('DOMContentLoaded', fetchMonthlyCount);
  document.addEventListener('DOMContentLoaded', fetchBookCount);
  document.addEventListener('DOMContentLoaded', fetchTransactionHistory);
  document.addEventListener('DOMContentLoaded', fetchYearlyAquiredBooks);
  document.addEventListener('DOMContentLoaded', fetchRegisteredUsers);
  document.addEventListener('DOMContentLoaded', () => topVisitedStudents());
  document.addEventListener('DOMContentLoaded', () => topBorrowedStudents());
  document.addEventListener('DOMContentLoaded', fetchTopBorrowedBooks);
  document.addEventListener('DOMContentLoaded', fetchTopBorrowedCategories);
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