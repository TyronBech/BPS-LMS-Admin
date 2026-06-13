<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Calendar Panel -->
  <div class="lg:col-span-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 sm:p-5 shadow-sm">
    <!-- Calendar Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6">
      <h3 id="calendar-month-year" class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white"></h3>
      
      <div class="flex items-center gap-2">
        <button type="button" id="prev-month-btn" class="skip-loader p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors duration-150" title="Previous Month">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </button>
        <button type="button" id="today-btn" class="skip-loader px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors duration-150">
          Today
        </button>
        <button type="button" id="next-month-btn" class="skip-loader p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors duration-150" title="Next Month">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>
      </div>
    </div>

    <!-- Calendar Grid Headers -->
    <div class="grid grid-cols-7 gap-1 sm:gap-2 mb-2">
      <div class="text-center text-xs font-bold uppercase tracking-wider text-red-500 py-1">Sun</div>
      <div class="text-center text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 py-1">Mon</div>
      <div class="text-center text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 py-1">Tue</div>
      <div class="text-center text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 py-1">Wed</div>
      <div class="text-center text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 py-1">Thu</div>
      <div class="text-center text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 py-1">Fri</div>
      <div class="text-center text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 py-1">Sat</div>
    </div>

    <!-- Days Grid -->
    <div id="calendar-days-grid" class="grid grid-cols-7 gap-1.5 sm:gap-2"></div>
  </div>

  <!-- Details Sidebar Panel -->
  <div class="lg:col-span-1 bg-gray-50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-700 rounded-xl p-4 sm:p-5 shadow-sm flex flex-col h-full min-h-[400px]">
    <div class="mb-4">
      <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">Reservations Details</h3>
      <p id="details-date-title" class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-medium mt-1"></p>
    </div>
    
    <hr class="border-gray-200 dark:border-gray-700 mb-4">
    
    <!-- Details List Wrapper -->
    <div id="details-list-container" class="flex-grow overflow-y-auto max-h-[500px] pr-1"></div>
  </div>
</div>

<!-- Render all Modals so they are reachable on click -->
<div id="calendar-modals-container">
  @foreach($calendarReservations as $reservation)
    @include('maintenance.class-reservations.modals', ['reservation' => $reservation])
  @endforeach
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
      const reservations = @json($calendarReservations ?? []);
      const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
      
      const today = new Date();
      let currentYear = today.getFullYear();
      let currentMonth = today.getMonth();
      
      // Default selection to today's date
      let selectedDateStr = formatDateString(today.getFullYear(), today.getMonth(), today.getDate());

      // Helper to pad numbers
      function pad(num) {
          return String(num).padStart(2, '0');
      }

      // Helper to format date object to YYYY-MM-DD
      function formatDateString(year, month, day) {
          return `${year}-${pad(month + 1)}-${pad(day)}`;
      }

      // Helper to format time strings (e.g. "13:00:00" -> "1:00 PM")
      function formatTimeString(timeStr) {
          if (!timeStr) return '';
          const parts = timeStr.split(':');
          if (parts.length < 2) return timeStr;
          let hours = parseInt(parts[0], 10);
          const minutes = parts[1];
          const ampm = hours >= 12 ? 'PM' : 'AM';
          hours = hours % 12;
          hours = hours ? hours : 12;
          return `${hours}:${minutes} ${ampm}`;
      }

      // Main render function
      function renderCalendar() {
          const firstDayIndex = new Date(currentYear, currentMonth, 1).getDay();
          const lastDay = new Date(currentYear, currentMonth + 1, 0).getDate();
          const prevLastDay = new Date(currentYear, currentMonth, 0).getDate();
          
          // Set Month and Year Title
          const headerTitle = document.getElementById('calendar-month-year');
          if (headerTitle) {
              headerTitle.innerText = `${monthNames[currentMonth]} ${currentYear}`;
          }
          
          const gridContainer = document.getElementById('calendar-days-grid');
          if (!gridContainer) return;
          
          gridContainer.innerHTML = '';
          
          // 1. Render previous month padding days
          for (let x = firstDayIndex; x > 0; x--) {
              const prevDay = prevLastDay - x + 1;
              const prevMonthVal = currentMonth === 0 ? 11 : currentMonth - 1;
              const prevYearVal = currentMonth === 0 ? currentYear - 1 : currentYear;
              const dateStr = formatDateString(prevYearVal, prevMonthVal, prevDay);
              
              const cell = createDayCell(prevDay, dateStr, false);
              gridContainer.appendChild(cell);
          }
          
          // 2. Render current month days
          for (let i = 1; i <= lastDay; i++) {
              const dateStr = formatDateString(currentYear, currentMonth, i);
              const cell = createDayCell(i, dateStr, true);
              gridContainer.appendChild(cell);
          }
          
          // 3. Render next month padding days to fill grid of 42 cells (6 rows of 7 days)
          const totalCells = firstDayIndex + lastDay;
          const remainingCells = 42 - totalCells;
          for (let j = 1; j <= remainingCells; j++) {
              const nextMonthVal = currentMonth === 11 ? 0 : currentMonth + 1;
              const nextYearVal = currentMonth === 11 ? currentYear + 1 : currentYear;
              const dateStr = formatDateString(nextYearVal, nextMonthVal, j);
              
              const cell = createDayCell(j, dateStr, false);
              gridContainer.appendChild(cell);
          }
          
          // 4. Update the side panel details list
          renderDetails();
      }

      // Create a day cell element
      function createDayCell(day, dateStr, isCurrentMonth) {
          const cell = document.createElement('div');
          
          // Base classes matching app theme
          cell.className = `min-h-[85px] sm:min-h-[95px] p-2 border border-gray-100 dark:border-gray-700/50 rounded-xl cursor-pointer flex flex-col justify-between transition-all duration-200 relative`;
          
          if (isCurrentMonth) {
              cell.classList.add('bg-white', 'dark:bg-gray-800', 'hover:bg-gray-50', 'dark:hover:bg-gray-700/30', 'text-gray-900', 'dark:text-gray-100');
          } else {
              cell.classList.add('bg-gray-50/50', 'dark:bg-gray-800/20', 'text-gray-400', 'dark:text-gray-500', 'hover:bg-gray-50', 'dark:hover:bg-gray-700/30');
          }
          
          // Highlight selected date
          if (dateStr === selectedDateStr) {
              cell.classList.remove('border-gray-100', 'dark:border-gray-700/50');
              cell.classList.add('ring-2', 'ring-primary-500', 'bg-primary-50/40', 'dark:bg-primary-950/20');
          }
          
          // Highlight today's date border
          const todayStr = formatDateString(today.getFullYear(), today.getMonth(), today.getDate());
          if (dateStr === todayStr) {
              cell.classList.add('border-primary-400', 'dark:border-primary-500');
          }
          
          // Get reservations for this date
          const dayReservations = reservations.filter(res => {
              const resDate = res.reservation_date.split('T')[0];
              return resDate === dateStr;
          });
          
          const approvedCount = dayReservations.filter(res => res.status === 'Approved').length;
          const pendingCount = dayReservations.filter(res => res.status === 'Pending').length;
          
          let badgeContainerHtml = '<div class="space-y-1 w-full mt-1.5">';
          let dotContainerHtml = '<div class="flex gap-1 justify-center mt-1.5 sm:hidden w-full">';
          
          if (approvedCount > 0) {
              badgeContainerHtml += `
                  <div class="hidden sm:flex items-center gap-1 bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400 text-[10px] px-1.5 py-0.5 rounded-lg border border-green-200 dark:border-green-900/30 font-medium">
                      <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                      <span class="truncate">Approved (${approvedCount})</span>
                  </div>
              `;
              dotContainerHtml += `<span class="w-2.5 h-2.5 rounded-full bg-green-500" title="Approved (${approvedCount})"></span>`;
          }
          
          if (pendingCount > 0) {
              badgeContainerHtml += `
                  <div class="hidden sm:flex items-center gap-1 bg-yellow-50 text-yellow-700 dark:bg-yellow-950/30 dark:text-yellow-400 text-[10px] px-1.5 py-0.5 rounded-lg border border-yellow-200 dark:border-yellow-900/30 font-medium">
                      <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>
                      <span class="truncate">Pending (${pendingCount})</span>
                  </div>
              `;
              dotContainerHtml += `<span class="w-2.5 h-2.5 rounded-full bg-yellow-500" title="Pending (${pendingCount})"></span>`;
          }
          
          badgeContainerHtml += '</div>';
          dotContainerHtml += '</div>';
          
          cell.innerHTML = `
              <div class="flex justify-between items-center w-full">
                  <span class="text-xs sm:text-sm font-semibold">${day}</span>
                  ${dateStr === todayStr ? '<span class="text-[8px] font-bold text-primary-500 dark:text-primary-400 uppercase tracking-wider">Today</span>' : ''}
              </div>
              ${dayReservations.length > 0 ? `${badgeContainerHtml}${dotContainerHtml}` : ''}
          `;
          
          // Click handler to select and update calendar month navigation
          cell.addEventListener('click', () => {
              const clickedDate = new Date(dateStr);
              const clickedYear = clickedDate.getFullYear();
              const clickedMonth = clickedDate.getMonth();
              
              selectedDateStr = dateStr;
              
              if (clickedYear !== currentYear || clickedMonth !== currentMonth) {
                  currentYear = clickedYear;
                  currentMonth = clickedMonth;
              }
              
              renderCalendar();
          });
          
          return cell;
      }

      // Render details in the sidebar panel
      function renderDetails() {
          const detailsTitle = document.getElementById('details-date-title');
          const detailsContainer = document.getElementById('details-list-container');
          if (!detailsContainer || !detailsTitle) return;

          const d = new Date(selectedDateStr);
          const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
          detailsTitle.innerText = d.toLocaleDateString('en-US', options);
          
          detailsContainer.innerHTML = '';
          
          const dayReservations = reservations.filter(res => {
              return res.reservation_date.split('T')[0] === selectedDateStr;
          });
          
          if (dayReservations.length === 0) {
              detailsContainer.innerHTML = `
                  <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                      <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                      </svg>
                      <p class="text-sm font-medium text-gray-500 dark:text-gray-400 italic">No class room reservations on this date.</p>
                  </div>
              `;
              return;
          }
          
          dayReservations.forEach(res => {
              const isPending = res.status === 'Pending';
              const isApproved = res.status === 'Approved';
              
              const statusBadge = isApproved 
                  ? `<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold text-green-800 bg-green-100 rounded-full dark:bg-green-900/30 dark:text-green-400">Approved</span>`
                  : `<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full dark:bg-yellow-900/30 dark:text-yellow-400">Pending</span>`;
                  
              const facultyHtml = res.faculty 
                  ? `<div class="text-xs text-gray-500 dark:text-gray-400 mt-2">Faculty Sponsor: <span class="font-medium text-gray-700 dark:text-gray-300">${res.faculty.first_name} ${res.faculty.last_name}</span></div>`
                  : '';
                  
              const actionButtons = isPending 
                  ? `
                  <div class="flex items-center gap-2 mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50">
                      <button
                          type="button"
                          data-modal-target="approve-modal-${res.id}"
                          data-modal-toggle="approve-modal-${res.id}"
                          class="skip-loader flex-1 text-center py-2 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 dark:bg-green-500 dark:hover:bg-green-600 rounded-lg transition-colors duration-150">
                          Approve
                      </button>
                      <button
                          type="button"
                          data-modal-target="reject-modal-${res.id}"
                          data-modal-toggle="reject-modal-${res.id}"
                          class="skip-loader flex-1 text-center py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 rounded-lg transition-colors duration-150">
                          Reject
                      </button>
                  </div>
                  `
                  : '';
                  
              const card = document.createElement('div');
              card.className = `p-4 mb-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow duration-200`;
              
              card.innerHTML = `
                  <div class="flex justify-between items-start gap-2">
                      <div>
                          <h4 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">${res.user.first_name} ${res.user.last_name}</h4>
                          <p class="text-xs text-gray-400 mt-0.5">${res.user.email}</p>
                      </div>
                      ${statusBadge}
                  </div>
                  
                  <div class="mt-3 text-xs font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/20 px-2.5 py-1 rounded-lg inline-flex items-center gap-1.5 w-fit">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      <span>${formatTimeString(res.start_time)} - ${formatTimeString(res.end_time)}</span>
                  </div>
                  
                  ${facultyHtml}
                  
                  <div class="mt-3 text-xs text-gray-650 dark:text-gray-300 leading-relaxed border-l-2 border-primary-500/30 dark:border-primary-500/50 pl-2.5">
                      <span class="font-medium text-gray-400 dark:text-gray-500 block text-[9px] uppercase tracking-wider mb-0.5">Purpose of Reservation</span>
                      ${res.purpose || 'No purpose specified'}
                  </div>
                  ${actionButtons}
              `;
              
              detailsContainer.appendChild(card);
          });
          
          // Reinitialize Flowbite so dynamic elements get the modal toggle triggers wired
          if (typeof initFlowbite === 'function') {
              initFlowbite();
          }
      }

      // Event listeners for month navigation
      const prevBtn = document.getElementById('prev-month-btn');
      if (prevBtn) {
          prevBtn.addEventListener('click', () => {
              if (currentMonth === 0) {
                  currentMonth = 11;
                  currentYear--;
              } else {
                  currentMonth--;
              }
              renderCalendar();
          });
      }

      const nextBtn = document.getElementById('next-month-btn');
      if (nextBtn) {
          nextBtn.addEventListener('click', () => {
              if (currentMonth === 11) {
                  currentMonth = 0;
                  currentYear++;
              } else {
                  currentMonth++;
              }
              renderCalendar();
          });
      }

      const todayBtn = document.getElementById('today-btn');
      if (todayBtn) {
          todayBtn.addEventListener('click', () => {
              currentYear = today.getFullYear();
              currentMonth = today.getMonth();
              selectedDateStr = formatDateString(today.getFullYear(), today.getMonth(), today.getDate());
              renderCalendar();
          });
      }

      // Initialize
      renderCalendar();
  });
</script>
