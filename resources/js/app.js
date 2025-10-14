import 'flowbite';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import $ from 'jquery';
import axios from 'axios';

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
    'exportBarcode',     // export barcode button
  ];

  // --- 1️⃣ Handle button clicks ---
  document.querySelectorAll('button, input[type="submit"]').forEach(btn => {
    btn.addEventListener('click', e => {
      const name = btn.getAttribute('name');
      const value = btn.getAttribute('value');
      const id = btn.id;

      // 🧩 Skip these cases
      if (
        skipButtonIds.includes(id) ||                                     // dropdowns
        (name === 'submit' && (value === 'pdf' || value === 'excel')) ||  // PDF export
        btn.disabled ||                                                   // disabled
        btn.offsetParent === null ||                                      // hidden
        btn.closest('.dashboard-card') ||                                 // stat cards or summary boxes
        (!btn.closest('form') &&                                          // not inside a form...
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
        link.target === '_blank'
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
    try {
      showLoader();
      const response = await originalFetch(...args);
      hideLoader();
      return response;
    } catch (error) {
      hideLoader();
      throw error;
    }
  };

  const originalXHR = window.XMLHttpRequest.prototype.open;
  window.XMLHttpRequest.prototype.open = function (...args) {
    this.addEventListener('loadend', hideLoader);
    showLoader();
    return originalXHR.apply(this, args);
  };
});