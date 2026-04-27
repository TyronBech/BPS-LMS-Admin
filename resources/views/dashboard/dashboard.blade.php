@use('App\Enum\PermissionsEnum')
@extends('layouts.admin-app')
@section('content')
<style>
  @keyframes slide-in {
    from {
      transform: translateX(100%);
      opacity: 0;
    }

    to {
      transform: translateX(0);
      opacity: 1;
    }
  }

  .animate-slide-in {
    animation: slide-in 0.3s ease-out;
  }
</style>
<h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Home</h1>
@if(auth()->user()->can(PermissionsEnum::VIEW_DASHBOARD))
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
  <div class="flex flex-col min-h-96 justify-between p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div>
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Current Users</h5>
      <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Total number of users currently timed-in in the library.</p>
    </div>
    <div class="mb-2">
      <h1 id="timed-in-count" class="text-8xl text-center font-extrabold dark:text-gray-300"></h1>
    </div>
    <button type="button" id="timeout-all-users" data-modal-target="timeout-all-users-modal" data-modal-toggle="timeout-all-users-modal" class="skip-loader text-white bg-gradient-to-r from-primary-500 via-primary-500 to-primary-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-primary-300 dark:focus:ring-primary-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">Timeout All Users</button>
  </div>
  <div class="flex flex-col min-h-96 md:col-span-1 lg:col-span-3 justify-between p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Monthly Logs</h5>
    <div class="relative h-full">
      <canvas id="monthly-logs"></canvas>
    </div>
  </div>
  <div class="flex flex-col min-h-96 justify-between p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div>
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Pending Overdues</h5>
      <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Total number of unpaid overdues.</p>
    </div>
    <div class="mb-2">
      <h1 id="pending-overdues-count" class="text-8xl text-center font-extrabold dark:text-gray-300"></h1>
    </div>
    <a href="{{ route('report.penalties', ['penalty_status' => 'Unpaid']) }}" class="skip-loader text-white bg-gradient-to-r from-primary-500 via-primary-500 to-primary-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-primary-300 dark:focus:ring-primary-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">View Unpaid Penalties</a>
  </div>
  <div class="flex flex-col min-h-96 md:col-span-1 lg:col-span-3 justify-between p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Transaction History</h5>
    <div class="relative h-full">
      <canvas id="transaction-history"></canvas>
    </div>
  </div>
  <div class="flex flex-col min-h-96 justify-between p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div>
      <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Total Books</h5>
      <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Total number of books in the database.</p>
    </div>
    <div class="mb-20">
      <h1 id="book-count" class="text-8xl text-center font-extrabold dark:text-gray-300"></h1>
    </div>
  </div>
  <div class="flex flex-col min-h-96 col-span-1 md:col-span-1 lg:col-span-3 justify-between p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Yearly Book Acquisition</h5>
    <div class="relative h-full mb-5">
      <canvas id="yearly-books"></canvas>
    </div>
  </div>
  <div class="flex flex-col col-span-1 md:col-span-2 lg:col-span-4 justify-between p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
        Top 6 Most Visited Students per Grade Level
      </h5>
      <div id="date-range-picker" date-rangepicker class="flex items-center">
        <div class="relative">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
            </svg>
          </div>
          <input id="datepicker-range-start-top-students" name="start_top_students" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-400 dark:focus:border-primary-400" placeholder="Start date">
        </div>
        <span class="mx-4 text-gray-500">to</span>
        <div class="relative">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
            </svg>
          </div>
          <input id="datepicker-range-end-top-students" name="end_top_students" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-400 dark:focus:border-primary-400" placeholder="End date">
        </div>
      </div>
    </div>
    <div id="top-students-container" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <p class="text-center text-gray-500 col-span-full py-10">Loading data...</p>
    </div>
  </div>
  <div class="flex flex-col col-span-1 md:col-span-2 lg:col-span-4 justify-between p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
        Top 3 Students with the Most Borrowed Books per Grade Level
      </h5>
      <div id="date-range-picker-borrowed" date-rangepicker class="flex items-center">
        <div class="relative">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
            </svg>
          </div>
          <input id="datepicker-range-start-top-borrowed" name="start_top_borrowed" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-400 dark:focus:border-primary-400" placeholder="Start date">
        </div>
        <span class="mx-4 text-gray-500">to</span>
        <div class="relative">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
            </svg>
          </div>
          <input id="datepicker-range-end-top-borrowed" name="end_top_borrowed" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-400 dark:focus:border-primary-400" placeholder="End date">
        </div>
      </div>
    </div>
    <div id="top-borrowed-container" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <p class="text-center text-gray-500 col-span-full py-10">Loading data...</p>
    </div>
  </div>
  <div class="flex flex-col min-h-96 col-span-1 md:col-span-1 lg:col-span-2 justify-between max-h-96 p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Top 5 Most Borrowed Books</h5>
    <div class="relative h-full">
      <canvas id="top-borrowed-books"></canvas>
    </div>
  </div>
  <div class="flex flex-col min-h-96 col-span-1 md:col-span-1 lg:col-span-2 justify-between max-h-96 p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Top 5 Most Borrowed Books per Category</h5>
    <div class="relative h-full">
      <canvas id="top-borrowed-categories"></canvas>
    </div>
  </div>

  <div id="timeout-all-users-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
      <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
        <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="timeout-all-users-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
        <div class="p-4 md:p-5 text-center">
          <svg class="mx-auto mb-4 text-red-400 w-12 h-12 dark:text-red-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          <h3 class="mb-3 text-base md:text-lg font-normal text-gray-500 dark:text-gray-400 px-2">
            Timeout all currently logged-in users?
          </h3>
          <p class="mb-5 text-sm text-gray-600 dark:text-gray-400 px-2">
            This will end every active library session immediately.
          </p>
          <button type="button" id="confirm-timeout-all-users" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
            Yes, timeout all users
          </button>
          <button data-modal-hide="timeout-all-users-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 shadow-md">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Fixed Refresh Button (Bottom Right) -->
  <div class="fixed bottom-6 left-6 z-50">
    <button type="button" id="refresh" class="flex items-center gap-2 text-white bg-primary-500 hover:bg-primary-600 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-full text-sm px-6 py-3 text-center shadow-lg transition-transform hover:scale-105 dark:bg-primary-500 dark:hover:bg-primary-400 dark:focus:ring-primary-500">
      <span class="font-semibold">Refresh</span>
      <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4" />
      </svg>
    </button>
  </div>
</div>
<script>
  // Toast notification system for user-friendly error messages
  function showToast(message, type = 'error') {
    const toastId = `toast-${type}-${Date.now()}`;
    const icons = {
      success: `<svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" /></svg>`,
      error: `<svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 11.793a1 1 0 1 1-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 0 1-1.414-1.414L8.586 10 6.293 7.707a1 1 0 0 1 1.414-1.414L10 8.586l2.293-2.293a1 1 0 0 1 1.414 1.414L11.414 10l2.293 2.293Z" /></svg>`,
      warning: `<svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0V6a1 1 0 0 1 2 0v5Z" /></svg>`
    };

    const colors = {
      success: {
        bg: 'bg-green-100 dark:bg-green-800',
        text: 'text-green-500 dark:text-green-200'
      },
      error: {
        bg: 'bg-red-100 dark:bg-red-800',
        text: 'text-red-500 dark:text-red-200'
      },
      warning: {
        bg: 'bg-orange-100 dark:bg-orange-700',
        text: 'text-orange-500 dark:text-orange-200'
      }
    };

    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = 'flex items-center fixed top-4 right-5 w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-lg dark:text-gray-400 dark:bg-gray-800 z-50 animate-slide-in';
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
      <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 ${colors[type].text} ${colors[type].bg} rounded-lg">
        ${icons[type]}
        <span class="sr-only">${type} icon</span>
      </div>
      <div class="ms-3 text-sm font-normal">${message}</div>
      <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700 shadow-md" onclick="this.parentElement.remove()">
        <span class="sr-only">Close</span>
        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
      </button>
    `;

    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
  }

  const pendingDashboardToast = sessionStorage.getItem('dashboard-timeout-toast');
  if (pendingDashboardToast) {
    sessionStorage.removeItem('dashboard-timeout-toast');
    showToast(pendingDashboardToast, 'success');
  }

  // Handle API errors gracefully
  async function handleApiResponse(response, errorContext = '') {
    if (!response.ok) {
      if (response.status === 404) {
        throw new Error('The requested resource was not found.');
      } else if (response.status === 500) {
        throw new Error('Server error. Please try again later.');
      } else if (response.status === 403) {
        throw new Error('You do not have permission to access this resource.');
      } else {
        throw new Error(`Unable to load ${errorContext}. Please try again.`);
      }
    }
    return response.json();
  }

  // Fetch the current count of active users
  async function fetchActiveCount() {
    try {
      const response = await fetch("{{ route('fetch-current-count') }}");
      const data = await handleApiResponse(response, 'current user count');
      document.getElementById('timed-in-count').textContent = data.active_count;
    } catch (error) {
      document.getElementById('timed-in-count').textContent = '...';
      showToast('Unable to load current user count. Please refresh the page.', 'error');
    }
  }
  // Timeout all users
  const timeoutAllUsersConfirmButton = document.getElementById('confirm-timeout-all-users');

  function setTimeoutAllUsersPending(isPending) {
    timeoutAllUsersConfirmButton.disabled = isPending;
    timeoutAllUsersConfirmButton.classList.toggle('opacity-70', isPending);
    timeoutAllUsersConfirmButton.classList.toggle('cursor-not-allowed', isPending);
  }

  timeoutAllUsersConfirmButton.addEventListener('click', async () => {
    setTimeoutAllUsersPending(true);
    try {
      const response = await fetch("{{ route('timeout-all-users') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
      });
      await handleApiResponse(response, 'timeout operation');
      sessionStorage.setItem('dashboard-timeout-toast', 'All users have been timed out successfully.');
      window.location.reload();
    } catch (error) {
      showToast(error.message || 'Failed to timeout users. Please try again.', 'error');
      setTimeoutAllUsersPending(false);
    }
  });
  // Fetch the monthly count of logs
  async function fetchMonthlyCount() {
    try {
      const response = await fetch("{{ route('fetch-monthly-count') }}");
      const data = await handleApiResponse(response, 'monthly logs');
      const labels = data.map(item => item.month);
      const counts = data.map(item => item.count);
      monthlyLogsLineGraph(labels, counts);
    } catch (error) {
      showToast('Unable to load monthly logs chart.', 'warning');
    }
  }
  // Fetch the total count of books
  async function fetchBookCount() {
    try {
      const response = await fetch("{{ route('fetch-book-count') }}");
      const data = await handleApiResponse(response, 'book count');
      document.getElementById('book-count').textContent = data.total_books;
    } catch (error) {
      document.getElementById('book-count').textContent = '...';
      showToast('Unable to load book count.', 'warning');
    }
  }
  // Fetch the total count of pending overdues (unpaid penalties)
  async function fetchPendingOverduesCount() {
    try {
      const response = await fetch("{{ route('fetch-pending-overdues-count') }}");
      const data = await handleApiResponse(response, 'pending overdues count');
      document.getElementById('pending-overdues-count').textContent = data.unpaid_penalties_count;
    } catch (error) {
      document.getElementById('pending-overdues-count').textContent = '...';
      showToast('Unable to load pending overdues count.', 'warning');
    }
  }
  // Fetch the transaction history
  async function fetchTransactionHistory() {
    try {
      const response = await fetch("{{ route('fetch-transaction-history') }}");
      const data = await handleApiResponse(response, 'transaction history');
      const labels = data.labels;
      const counts = data.total;
      const borrowed = data.borrowed;
      const reserved = data.reserved;
      const returned = data.returned;

      transactionHistoryBarGraph(labels, counts, borrowed, reserved, returned);
    } catch (error) {
      showToast('Unable to load transaction history chart.', 'warning');
    }
  }
  // Fetch the yearly acquired books
  async function fetchYearlyAquiredBooks() {
    try {
      const response = await fetch("{{ route('fetch-yearly-aquired-books') }}");
      const data = await handleApiResponse(response, 'yearly acquired books');
      const labels = data.map(item => item.year);
      const counts = data.map(item => item.count);
      YearlyBooksDoughnutGraph(labels, counts);
    } catch (error) {
      showToast('Unable to load yearly acquired books chart.', 'warning');
    }
  }
  async function topVisitedStudents(start, end) {
    try {
      let url = "{{ route('fetch-most-visited-students') }}";
      url = buildUrl(url, start, end);
      const response = await fetch(url);
      const data = await handleApiResponse(response, 'top visited students');
      const container = document.getElementById('top-students-container');
      container.innerHTML = ''; // clear old content

      if (!data || data.length === 0) {
        container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">No data found for the selected range.</div>`;
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
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-800 rounded-xl dark: hover: transition- duration-200 overflow-hidden flex flex-col h-full shadow-md">
          <div class="bg-gradient-to-r from-primary-500 to-primary-500 px-5 py-3 border-b border-primary-500">
            <h6 class="text-white text-lg font-bold flex items-center gap-2">
              <svg class="w-5 h-5 text-primary-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
              Grade ${level}
            </h6>
          </div>
          <div class="overflow-x-auto flex-grow scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
              <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10">
                <tr>
                  <th scope="col" class="px-4 py-3 w-12 text-center">#</th>
                  <th scope="col" class="px-4 py-3">Student</th>
                  <th scope="col" class="px-4 py-3 text-center">Visits</th>
                  <th scope="col" class="px-4 py-3">Section</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
      `;
        let rank = 1;
        filteredStudents.forEach(student => {
          const s = student.students;
          const fullName = `${student.last_name}, ${student.first_name} ${student.middle_name ?? ''}`.trim();

          // Rank styling
          let rankBadge = `<span class="font-medium text-gray-500 dark:text-gray-400">${rank}</span>`;
          if (rank === 1) rankBadge = `<span class="inline-flex items-center justify-center w-6 h-6 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full ring-1 ring-yellow-400 dark:bg-yellow-900 dark:text-yellow-300">1</span>`;
          else if (rank === 2) rankBadge = `<span class="inline-flex items-center justify-center w-6 h-6 bg-gray-100 text-gray-700 text-xs font-bold rounded-full ring-1 ring-gray-400 dark:bg-gray-700 dark:text-gray-300">2</span>`;
          else if (rank === 3) rankBadge = `<span class="inline-flex items-center justify-center w-6 h-6 bg-orange-100 text-orange-700 text-xs font-bold rounded-full ring-1 ring-orange-400 dark:bg-orange-900 dark:text-orange-300">3</span>`;

          tableHTML += `
          <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <td class="px-4 py-3 text-center font-medium text-gray-900 dark:text-white whitespace-nowrap">
                ${rankBadge}
            </td>
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap truncate max-w-[140px]" title="${fullName}">
                ${fullName}
            </td>
            <td class="px-4 py-3 text-center">
                <span class="bg-gray-100 text-gray-800 text-xs font-bold px-2.5 py-0.5 rounded-full dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                    ${student.logs_count}
                </span>
            </td>
            <td class="px-4 py-3 whitespace-nowrap truncate max-w-[100px] text-gray-500 dark:text-gray-400" title="${s?.section ?? ''}">${s?.section ?? '-'}</td>
          </tr>
        `;
          rank++;
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
        container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">No students with visits found for this period.</div>`;
      }
    } catch (error) {
      document.getElementById('top-students-container').innerHTML =
        `<div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">
          <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p>Unable to load data. Please try refreshing.</p>
        </div>`;
      showToast('Unable to load top visited students data.', 'warning');
    }
  }
  async function topBorrowedStudents(start, end) {
    try {
      let url = "{{ route('fetch-most-borrowed-students') }}";
      url = buildUrl(url, start, end);
      const response = await fetch(url);
      const data = await handleApiResponse(response, 'top borrowed students');

      const container = document.getElementById('top-borrowed-container');
      container.innerHTML = ''; // Clear previous content

      if (!data || data.length === 0) {
        container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">No data found for the selected range.</div>`;
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
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-800 rounded-xl dark: hover: transition- duration-200 overflow-hidden flex flex-col h-full shadow-md">
        <div class="bg-gradient-to-r from-primary-500 to-primary-500 px-5 py-3 border-b border-primary-500">
          <h6 class="text-white text-lg font-bold flex items-center gap-2">
             <svg class="w-5 h-5 text-amber-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            Grade ${level}
          </h6>
        </div>
        <div class="overflow-x-auto flex-grow scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
          <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 sticky top-0 z-10">
              <tr>
                <th scope="col" class="px-4 py-3 w-12 text-center">#</th>
                <th scope="col" class="px-4 py-3">Student</th>
                <th scope="col" class="px-4 py-3 text-center">Borrowed</th>
                <th scope="col" class="px-4 py-3">Section</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
      `;
        let rank = 1;
        filteredStudents.forEach(student => {
          const s = student.students;
          const fullName = `${student.last_name}, ${student.first_name} ${student.middle_name ?? ''}`.trim();

          // Rank styling
          let rankBadge = `<span class="font-medium text-gray-500 dark:text-gray-400">${rank}</span>`;
          if (rank === 1) rankBadge = `<span class="inline-flex items-center justify-center w-6 h-6 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full ring-1 ring-yellow-400 dark:bg-yellow-900 dark:text-yellow-300">1</span>`;
          else if (rank === 2) rankBadge = `<span class="inline-flex items-center justify-center w-6 h-6 bg-gray-100 text-gray-700 text-xs font-bold rounded-full ring-1 ring-gray-400 dark:bg-gray-700 dark:text-gray-300">2</span>`;
          else if (rank === 3) rankBadge = `<span class="inline-flex items-center justify-center w-6 h-6 bg-orange-100 text-orange-700 text-xs font-bold rounded-full ring-1 ring-orange-400 dark:bg-orange-900 dark:text-orange-300">3</span>`;

          tableHTML += `
        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
          <td class="px-4 py-3 text-center font-medium text-gray-900 dark:text-white whitespace-nowrap">
            ${rankBadge}
          </td>
          <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap truncate max-w-[140px]" title="${fullName}">
            ${fullName}
          </td>
          <td class="px-4 py-3 text-center">
             <span class="bg-gray-100 text-gray-800 text-xs font-bold px-2.5 py-0.5 rounded-full dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                ${student.borrow_count}
             </span>
          </td>
          <td class="px-4 py-3 whitespace-nowrap truncate max-w-[100px] text-gray-500 dark:text-gray-400" title="${s?.section ?? ''}">${s?.section ?? '-'}</td>
        </tr>
      `;
          rank++;
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
        container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">No borrowed book data found for this period.</div>`;
      }
    } catch (error) {
      document.getElementById('top-borrowed-container').innerHTML =
        `<div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">
          <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p>Unable to load data. Please try refreshing.</p>
        </div>`;
      showToast('Unable to load top borrowed students data.', 'warning');
    }
  }
  async function fetchTopBorrowedBooks() {
    try {
      const response = await fetch("{{ route('fetch-top-books-borrowed') }}");
      const data = await handleApiResponse(response, 'top borrowed books');
      const labels = data.labels;
      const counts = data.counts;
      topBorrowedBooks(labels, counts);
    } catch (error) {
      showToast('Unable to load top borrowed books chart.', 'warning');
    }
  }
  async function fetchTopBorrowedCategories() {
    try {
      const response = await fetch("{{ route('fetch-top-categories-borrowed') }}");
      const data = await handleApiResponse(response, 'top borrowed categories');
      const labels = data.labels;
      const counts = data.counts;
      topBorrowedCategories(labels, counts);
    } catch (error) {
      showToast('Unable to load top borrowed categories chart.', 'warning');
    }
  }
  // Initialize the chart variable
  let transactionHistoryChart = null;
  let monthlyLogsChart = null;
  let yearlyBooksChart = null;
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
  refreshButton.addEventListener('click', async () => {
    showToast('Refreshing dashboard data...', 'success');

    await Promise.allSettled([
      fetchActiveCount(),
      fetchMonthlyCount(),
      fetchBookCount(),
      fetchPendingOverduesCount(),
      fetchTransactionHistory(),
      fetchYearlyAquiredBooks(),
      topVisitedStudents(),
      topBorrowedStudents(),
      fetchTopBorrowedBooks(),
      fetchTopBorrowedCategories()
    ]);

    showToast('Dashboard data refreshed successfully!', 'success');
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
  document.addEventListener('DOMContentLoaded', fetchPendingOverduesCount);
  document.addEventListener('DOMContentLoaded', fetchTransactionHistory);
  document.addEventListener('DOMContentLoaded', fetchYearlyAquiredBooks);
  document.addEventListener('DOMContentLoaded', () => topVisitedStudents());
  document.addEventListener('DOMContentLoaded', () => topBorrowedStudents());
  document.addEventListener('DOMContentLoaded', fetchTopBorrowedBooks);
  document.addEventListener('DOMContentLoaded', fetchTopBorrowedCategories);
</script>
@else

<div class="flex flex-col items-center max-w-sm p-6 my-28 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
  <svg class="w-16 h-16 opacity-70 mb-3 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v3m-3-6V7a3 3 0 1 1 6 0v4m-8 0h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
  </svg>
  <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Access Restricted</h5>
  <p class="font-normal text-center text-gray-700 dark:text-gray-400">Sorry, you don't have access to view the dashboard page. Please contact your administrator.</p>
</div>

@endif
@endsection
