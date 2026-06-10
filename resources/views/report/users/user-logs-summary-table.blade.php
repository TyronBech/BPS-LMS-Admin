<div id="summary-card" class="container mx-auto mt-4 mb-4">
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700 max-w-5xl mx-auto">
    
    <!-- Title / Header (Compact) -->
    <div class="flex items-center space-x-2 border-b border-gray-150 dark:border-gray-700 pb-2 mb-3.5">
      <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Hourly Summary</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-stretch">
      
      <!-- Left Area (2 sub-columns of hours + Centered Total below them) -->
      <div class="md:col-span-3 flex flex-col justify-between">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-0.5">
          <!-- Column 1: Morning Hours -->
          <div class="space-y-0.5">
            @php
              $morningHours = [
                6 => '6:00am',
                7 => '7:00am',
                8 => '8:00am',
                9 => '9:00am',
                10 => '10:00am',
                11 => '11:00am',
                12 => '12:00nn',
                13 => '1:00pm'
              ];
            @endphp
            @foreach($morningHours as $hour => $label)
              <div class="flex items-center justify-between py-1 px-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $label }}</span>
                <span class="inline-flex items-center justify-center px-2.5 py-0.5 text-xs font-bold rounded {{ ($hourlySummary[$hour] ?? 0) > 0 ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500' }}">
                  {{ $hourlySummary[$hour] ?? 0 }}
                </span>
              </div>
            @endforeach
          </div>

          <!-- Column 2: Afternoon/Evening Hours -->
          <div class="space-y-0.5">
            @php
              $eveningHours = [
                14 => '2:00pm',
                15 => '3:00pm',
                16 => '4:00pm',
                17 => '5:00pm',
                18 => '6:00pm',
                19 => '7:00pm',
                20 => '8:00pm',
                21 => '9:00pm'
              ];
            @endphp
            @foreach($eveningHours as $hour => $label)
              <div class="flex items-center justify-between py-1 px-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $label }}</span>
                <span class="inline-flex items-center justify-center px-2.5 py-0.5 text-xs font-bold rounded {{ ($hourlySummary[$hour] ?? 0) > 0 ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500' }}">
                  {{ $hourlySummary[$hour] ?? 0 }}
                </span>
              </div>
            @endforeach
          </div>
        </div>

        <!-- Total Row (Centered under columns 1 & 2) -->
        <div class="flex justify-center items-center mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
          <div class="flex items-center space-x-3 py-1 px-4 rounded bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700">
            <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total</span>
            <span class="text-sm font-extrabold text-gray-800 dark:text-gray-200">{{ array_sum($hourlySummary) }}</span>
            <input type="hidden" id="summary_total" value="{{ array_sum($hourlySummary) }}">
            <input type="hidden" id="summary_num_days" value="{{ $numDays }}">
          </div>
        </div>

      </div>

      <!-- Right Area (Stats - Column 3, Compact) -->
      <div class="flex flex-col items-center justify-center space-y-4 md:border-l md:border-gray-200 md:dark:border-gray-700 md:pl-4">
        
        <div class="w-full max-w-[120px] text-center">
          <label for="hours_day" class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Hours/Day</label>
          <select id="hours_day" class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-955 dark:text-white text-xs rounded-lg focus:ring-gray-400 focus:border-gray-400 block p-1.5 font-semibold text-center shadow-sm cursor-pointer hover:border-gray-400 transition-colors">
            <option value="16" selected>16</option>
            <option value="12">12</option>
            <option value="8">8</option>
          </select>
        </div>

        <div class="w-full max-w-[120px] text-center">
          <span class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Average</span>
          <div class="bg-white dark:bg-gray-700 rounded-xl p-2.5 border border-gray-200 dark:border-gray-600 shadow-sm flex flex-col items-center justify-center">
            <span id="average_display_metric" class="text-xl font-black text-gray-800 dark:text-white tracking-tight">0.00</span>
            <span class="text-[9px] text-gray-400 dark:text-gray-500 uppercase font-medium">per hour</span>
          </div>
          <input type="hidden" id="average_display">
        </div>

      </div>

    </div>

    <!-- Date range form inline filter (Compact) -->
    <div class="mt-4 border-t border-gray-100 dark:border-gray-700 pt-3 flex justify-center">
      <form action="{{ route('report.user-search') }}" method="POST" id="summary-filter-form" class="flex flex-wrap items-center justify-center gap-4">
        @csrf
        <input type="hidden" name="search" value="{{ $search }}">
        <input type="hidden" name="user_type" value="{{ $userType }}">

        <!-- From and To Inputs with datepicker -->
        <div id="date-range-picker-summary" date-rangepicker class="flex flex-wrap items-center justify-center gap-3">
          <div class="flex items-center space-x-1.5">
            <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wide">From</span>
            <div class="relative w-36">
              <div class="absolute inset-y-0 start-0 flex items-center ps-2.5 pointer-events-none">
                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                </svg>
              </div>
              <input id="summary-datepicker-start" name="start" type="text" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-xs rounded-lg focus:ring-gray-400 focus:border-gray-400 block w-full ps-8 p-1.5 shadow-sm" placeholder="Start date" value="{{ $fromInputDate }}">
            </div>
          </div>
          
          <div class="flex items-center space-x-1.5">
            <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wide">To</span>
            <div class="relative w-36">
              <div class="absolute inset-y-0 start-0 flex items-center ps-2.5 pointer-events-none">
                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                </svg>
              </div>
              <input id="summary-datepicker-end" name="end" type="text" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-xs rounded-lg focus:ring-gray-400 focus:border-gray-400 block w-full ps-8 p-1.5 shadow-sm" placeholder="End date" value="{{ $toInputDate }}">
            </div>
          </div>
        </div>

      </form>
    </div>

  </div>
</div>

<script type="module">
  $(document).ready(function() {
    function calculateAverage() {
      const total = parseInt($('#summary_total').val()) || 0;
      const numDays = parseInt($('#summary_num_days').val()) || 1;
      const hoursPerDay = parseInt($('#hours_day').val()) || 16;
      const divisor = numDays * hoursPerDay;
      const average = divisor > 0 ? (total / divisor) : 0;
      const formattedAverage = average.toFixed(2);
      $('#average_display').val(formattedAverage);
      $('#average_display_metric').text(formattedAverage);
    }

    // Calculate initial average
    calculateAverage();

    // Re-calculate on dropdown change
    $(document).on('change', '#hours_day', function() {
      calculateAverage();
    });

    // Auto-submit form on date change
    $(document).on('changeDate change', '#summary-datepicker-start, #summary-datepicker-end', function() {
      const startStr = $('#summary-datepicker-start').val();
      const endStr = $('#summary-datepicker-end').val();
      if (startStr && endStr) {
        const startParts = startStr.split('/');
        const endParts = endStr.split('/');
        if (startParts.length === 3 && endParts.length === 3) {
          const startDate = new Date(startParts[2], startParts[0] - 1, startParts[1]);
          const endDate = new Date(endParts[2], endParts[0] - 1, endParts[1]);
          if (startDate <= endDate) {
            $('#summary-filter-form').submit();
          }
        }
      }
    });

    // Handle AJAX form submission for the summary card filter
    $(document).on('submit', '#summary-filter-form', function(e) {
      e.preventDefault();
      
      const form = this;
      const formData = new FormData(form);
      
      // Inject search and user_type from main inputs to keep page-level state in sync
      const mainSearch = $('#search').val() || '';
      const mainUserType = $('#user_type').val() || 'all';
      formData.set('search', mainSearch);
      formData.set('user_type', mainUserType);
      
      const url = form.action || window.location.href;
      
      $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
          'X-Skip-Loader': 'true'
        },
        success: function(html) {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          
          // 1. Sync the main search datepickers with the new values
          const newStartVal = doc.querySelector('#summary-datepicker-start').value;
          const newEndVal = doc.querySelector('#summary-datepicker-end').value;
          $('#datepicker-range-start').val(newStartVal);
          $('#datepicker-range-end').val(newEndVal);
          
          // 2. Update the summary card HTML content
          const oldSummaryContent = document.getElementById('summary-card');
          const newSummaryContent = doc.getElementById('summary-card');
          if (oldSummaryContent && newSummaryContent) {
            oldSummaryContent.innerHTML = newSummaryContent.innerHTML;
          }
          
          // 3. Update the detailed table container HTML content
          const oldTableContainer = document.getElementById('table-container');
          const newTableContainer = doc.getElementById('table-container');
          if (oldTableContainer && newTableContainer) {
            oldTableContainer.innerHTML = newTableContainer.innerHTML;
          }
          
          // 4. Re-calculate average using new values
          calculateAverage();
          
          // 5. Re-initialize Flowbite datepickers and elements
          if (typeof initFlowbite === 'function') {
            initFlowbite();
          }
          
          // 6. Reload chart/graph with the new dates
          if (typeof loadGraph === 'function') {
            loadGraph();
          }
          
          // 7. Update browser URL history state to match
          const params = new URLSearchParams(formData);
          window.history.pushState({}, '', window.location.pathname + '?' + params.toString());
        },
        error: function(xhr) {
          console.error('AJAX summary filter submit failed:', xhr);
        }
      });
    });
  });
</script>
