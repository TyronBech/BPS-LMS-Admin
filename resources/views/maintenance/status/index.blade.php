@extends('layouts.admin-app')
@section('content')
<div class="w-full max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
  
  <!-- Page Header -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
      <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
        <svg class="w-6 h-6 md:w-8 md:h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
      </div>
      <span>Reservation System Control</span>
    </h2>
  </div>

  <div id="alertContainer" class="mb-6"></div>

  <!-- Main Status Card -->
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-8 overflow-hidden">
    <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
      <h5 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        System Status
      </h5>
      <span class="text-xs font-medium px-2.5 py-0.5 rounded bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
        Auto-updates
      </span>
    </div>
    
    <div class="p-6 md:p-10 text-center">
      <div class="mb-8 flex justify-center">
        <span id="systemStatus" class="inline-flex items-center gap-3 px-8 py-4 rounded-full text-xl font-bold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 shadow-sm transition-all duration-300">
          <svg class="animate-spin w-6 h-6" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          Checking Status...
        </span>
      </div>

      <p class="text-gray-600 dark:text-gray-400 mb-8 text-lg max-w-2xl mx-auto">
        Current State: <strong id="statusText" class="text-gray-900 dark:text-white">Unknown</strong>
      </p>

      <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
        <button id="toggleBtn" name="toggleBtn" value="toggle" type="button" onclick="toggleReservationSystem()" disabled class="skip-loader w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 text-base font-medium text-white bg-gray-600 hover:bg-gray-700 rounded-xl transition-all shadow-md hover:shadow-lg focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-700 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none">
          Loading...
        </button>
      </div>

      <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700/50">
        <small class="text-gray-500 dark:text-gray-400 flex items-center justify-center gap-1.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          Status refreshes automatically every 30 seconds
        </small>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- How it works -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-blue-100 dark:border-blue-900/30 h-full flex flex-col">
      <div class="p-6 flex-grow">
        <h6 class="text-blue-600 dark:text-blue-400 font-bold text-lg mb-4 flex items-center gap-2">
          <div class="p-1.5 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
          How it works
        </h6>
        <div class="prose prose-sm dark:prose-invert max-w-none">
          <p class="text-gray-600 dark:text-gray-300 mb-4">When <strong class="text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-1.5 py-0.5 rounded border border-green-100 dark:border-green-800">ENABLED</strong>, the background processor runs every 10 minutes to:</p>
          <ul class="space-y-2 mb-4">
            <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300">
              <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
              <span>Promote pending reservations to active</span>
            </li>
            <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300">
              <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
              <span>Send email notifications to users</span>
            </li>
            <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300">
              <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
              <span>Expire old pickup deadlines automatically</span>
            </li>
          </ul>
          <p class="text-gray-600 dark:text-gray-300">When <strong class="text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-1.5 py-0.5 rounded border border-red-100 dark:border-red-800">DISABLED</strong>, all queue processing pauses immediately.</p>
        </div>
      </div>
    </div>

    <!-- Queue Snapshot -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 h-full flex flex-col">
      <div class="p-6 flex-grow">
        <h6 class="font-bold text-lg text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <div class="p-1.5 bg-gray-100 dark:bg-gray-700 rounded-lg">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
          </div>
          Queue Snapshot
        </h6>
        
        <div class="space-y-4">
          <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
            <div class="flex items-center gap-3">
              <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
              <span class="text-gray-700 dark:text-gray-300 font-medium">Pending</span>
            </div>
            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg text-sm font-bold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300 min-w-[3rem]" id="statPending">-</span>
          </div>
          <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
            <div class="flex items-center gap-3">
              <span class="w-2 h-2 rounded-full bg-green-500"></span>
              <span class="text-gray-700 dark:text-gray-300 font-medium">For Pickup</span>
            </div>
            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg text-sm font-bold bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300 min-w-[3rem]" id="statPickup">-</span>
          </div>
        </div>
        
        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end items-center gap-2">
          <span class="text-sm text-gray-500 dark:text-gray-400">Total Active: <span id="statTotal" class="font-bold text-gray-900 dark:text-white text-base ml-1">-</span></span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    checkReservationStatus();
    updateStats();

    // Auto-refresh every 30 seconds
    setInterval(checkReservationStatus, 30000);
    setInterval(updateStats, 60000); // Update stats every minute
  });

  let currentSystemState = false;

  // 1. Check Status
  function checkReservationStatus() {
    fetch("{{ route('maintenance.status') }}", {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
      })
      .then(res => res.json())
      .then(data => {
        updateUI(data.status);
      })
      .catch(err => console.error('Error checking status:', err));
  }

  // 2. Toggle System
  function toggleReservationSystem() {
    const toggleBtn = document.getElementById('toggleBtn');
    const originalContent = toggleBtn.innerHTML;

    // Disable button while processing
    toggleBtn.disabled = true;
    toggleBtn.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...`;

    // We want to flip the current state
    const newState = !currentSystemState;

    fetch("{{ route('maintenance.toggle') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
          enabled: newState
        })
      })
      .then(res => {
        if (res.status === 403) throw new Error("Unauthorized Access");
        return res.json();
      })
      .then(data => {
        if (data.status === 'success') {
          updateUI(data.enabled);
          showAlert(data.message, 'success');
        } else {
          showAlert('Failed to toggle system', 'danger');
        }
      })
      .catch(err => {
        showAlert(err.message || 'An error occurred', 'danger');
        toggleBtn.disabled = false;
        toggleBtn.innerHTML = originalContent;
      });
  }

  // 3. Get Stats
  function updateStats() {
    fetch("{{ route('maintenance.stats') }}", {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
      })
      .then(res => res.json())
      .then(data => {
        document.getElementById('statPending').textContent = data.pending;
        document.getElementById('statPickup').textContent = data.available_for_pickup;
        document.getElementById('statTotal').textContent = data.total_reserved;
      })
      .catch(err => console.log('Stats error:', err));
  }

  // UI Helper: Updates Badges and Buttons
  function updateUI(isActive) {
    currentSystemState = isActive;

    const badge = document.getElementById('systemStatus');
    const btn = document.getElementById('toggleBtn');
    const text = document.getElementById('statusText');

    if (isActive) {
      // Set to Active State
      badge.className = 'inline-flex items-center gap-2 px-6 py-3 rounded-full text-lg font-semibold bg-green-100 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800';
      badge.innerHTML = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> ACTIVE`;

      text.textContent = "System is processing reservations automatically.";

      btn.className = 'inline-flex items-center justify-center gap-2 px-8 py-3 text-base font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-all shadow-lg hover:shadow-xl focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900';
      btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Disable System`;
      btn.disabled = false;
    } else {
      // Set to Inactive State
      badge.className = 'inline-flex items-center gap-2 px-6 py-3 rounded-full text-lg font-semibold bg-red-100 text-red-700 border border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800';
      badge.innerHTML = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> PAUSED`;

      text.textContent = "System is OFF. No automatic processing.";

      btn.className = 'inline-flex items-center justify-center gap-2 px-8 py-3 text-base font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-all shadow-lg hover:shadow-xl focus:ring-4 focus:ring-green-300 dark:focus:ring-green-900';
      btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Enable System`;
      btn.disabled = false;
    }
  }

  // UI Helper: Show Alerts
  function showAlert(message, type) {
    const container = document.getElementById('alertContainer');
    
    let colorClass = type === 'success' 
        ? 'text-green-800 bg-green-50 dark:bg-gray-800 dark:text-green-400 border-green-200 dark:border-green-800' 
        : 'text-red-800 bg-red-50 dark:bg-gray-800 dark:text-red-400 border-red-200 dark:border-red-800';

    const alertHtml = `
      <div class="p-4 mb-4 text-sm rounded-lg border ${colorClass} flex items-center justify-between shadow-sm" role="alert">
        <span class="font-medium">${message}</span>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 p-1.5 inline-flex h-8 w-8 hover:bg-gray-200 dark:hover:bg-gray-700" onclick="this.parentElement.remove()">
          <span class="sr-only">Close</span>
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
        </button>
      </div>
    `;
    container.innerHTML = alertHtml;

    // Auto dismiss after 3 seconds
    setTimeout(() => {
      const alert = container.firstElementChild;
      if(alert) alert.remove();
    }, 3000);
  }
</script>
@endsection