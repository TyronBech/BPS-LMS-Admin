import 'flowbite';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import $ from 'jquery';
import axios from 'axios';
import ApexCharts from 'apexcharts';

window.ApexCharts = ApexCharts;
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.$ = window.jQuery = $;
window.Chart = Chart;
Chart.register(ChartDataLabels);

document.addEventListener('DOMContentLoaded', () => {
  const loader = document.getElementById('form-loader');

  function showLoader() {
    loader.classList.remove('hidden');
  }

  function hideLoader() {
    loader.classList.add('hidden');
  }

  // IDs or classes you want to skip loader for
  const skipButtonIds = [
    'dropdownNavbarLink',   // dropdown
    'doubleDropdownButton', // another dropdown
    'decrement-button',     // number input decrement
    'increment-button',     // number input increment
    'createCopy',           // create book copy button
    'exportBarcodeBtn',     // export barcode button
    'exportBarcode',        // export barcode button (books page)
  ];

  // --- 1️⃣ Handle button clicks ---
  document.querySelectorAll('button, input[type="submit"]').forEach(btn => {
    btn.addEventListener('click', e => {
      const name = btn.getAttribute('name');
      const value = btn.getAttribute('value');
      const id = btn.id;
      const form = btn.closest('form');

      // 🧩 Skip these cases
      if (
        skipButtonIds.includes(id) ||                                     // dropdowns
        (name === 'submit' && (value === 'pdf' || value === 'excel')) ||  // PDF export
        (name === 'barcodeBtn' && value === 'barcode') ||                 // Barcode export
        btn.disabled ||                                                   // disabled
        btn.offsetParent === null ||                                      // hidden
        btn.closest('.dashboard-card') ||
        btn.classList.contains('skip-loader') ||                          // skip-loader class
        (form && !form.checkValidity()) ||                                // form is invalid
        (!form &&                                                         // not inside a form...
          id !== 'refresh' &&                                             // ...and not refresh
          id !== 'timeout-all-users')                                     // ...and not timeout
      ) {
        return;
      }

      showLoader();
    });
  });

  // --- 2️⃣ Handle form submissions ---
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', e => {
      const submitter = e.submitter;
      if (form.classList.contains('skip-loader')) return;
      if (submitter && (submitter.value === 'pdf' || submitter.value === 'excel')) return;
      if (submitter && submitter.name === 'barcodeBtn' && submitter.value === 'barcode') return;
      showLoader();
    });
  });

  // --- 3️⃣ Handle anchor (<a>) clicks ---
  document.querySelectorAll('a[href]').forEach(link => {
    link.addEventListener('click', e => {
      const href = link.getAttribute('href');

      // Skip internal anchors, new tabs, JS voids, or dropdown triggers
      if (
        !href ||
        href.startsWith('#') ||
        href.startsWith('javascript:') ||
        link.id === 'dropdownNavbarLink' ||
        link.closest('#dropdownNavbarLink') || // nested inside dropdown button
        link.target === '_blank' ||
        link.classList.contains('skip-loader') // skip-loader class
      ) {
        return;
      }

      showLoader();
    });
  });

  // --- 4️⃣ Hide loader when page reloads or back navigation happens ---
  window.addEventListener('pageshow', hideLoader);

  // --- 5️⃣ Intercept AJAX (fetch + XHR) ---
  const originalFetch = window.fetch;
  window.fetch = async (...args) => {
    const url = args[0] instanceof Request ? args[0].url : String(args[0]);

    // Skip loader for dashboard analytics fetches and pending extensions
    const isDashboardAnalytics = url.includes('/analytics/most-visited-students') || url.includes('/analytics/most-borrowed-students');
    const isPendingExtensions = url.includes('/maintenance/reservations/show-reservations') || url.includes('maintenance.pending-extensions');

    try {
      if (!isDashboardAnalytics && !isPendingExtensions) {
        showLoader();
      }
      const response = await originalFetch(...args);
      if (!isDashboardAnalytics && !isPendingExtensions) {
        hideLoader();
      }
      return response;
    } catch (error) {
      if (!isDashboardAnalytics && !isPendingExtensions) {
        hideLoader();
      }
      throw error;
    }
  };

  const originalXHR = window.XMLHttpRequest.prototype.open;
  window.XMLHttpRequest.prototype.open = function (...args) {
    const url = String(args[1] || ''); // Get the URL from the 'open' arguments

    // Define URLs to skip the loader for
    const urlsToSkip = [
      '/report/user-graph',
      '/maintenance/reservations/show-reservations',
      'maintenance.pending-extensions'
    ];

    const shouldSkipLoader = urlsToSkip.some(skipUrl => url.includes(skipUrl));

    if (!shouldSkipLoader) {
      showLoader();
      this.addEventListener('loadend', hideLoader, { once: true });
    }

    return originalXHR.apply(this, args);
  };
});