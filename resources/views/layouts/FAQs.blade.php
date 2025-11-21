<button type="button" data-modal-target="FAQsModal" data-modal-toggle="FAQsModal"
  class="fixed bottom-4 right-4 md:bottom-6 md:right-6 lg:bottom-8 lg:right-8 
            bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 
            text-white rounded-full p-3 md:p-4 shadow-lg hover:shadow-xl 
            transition-all duration-300 hover:scale-110 z-50 group"
  title="Frequently Asked Questions">
  <svg xmlns="http://www.w3.org/2000/svg"
    class="h-6 w-6 md:h-7 md:w-7"
    fill="none"
    viewBox="0 0 24 24"
    stroke="currentColor">
    <path stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2"
      d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
  </svg>
  <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2 
                 bg-gray-800 dark:bg-gray-700 text-white text-sm 
                 px-3 py-2 rounded whitespace-nowrap opacity-0 
                 group-hover:opacity-100 transition-opacity duration-300 
                 pointer-events-none hidden md:block">
    FAQs
  </span>
</button>

<!-- Main modal -->
<div id="FAQsModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-900 bg-opacity-50">
  <div class="relative p-4 w-full max-w-4xl max-h-full">
    <!-- Modal content -->
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-2xl">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 md:p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg">
        <div class="flex items-center gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="text-xl md:text-2xl font-bold text-white">
            Frequently Asked Questions
          </h3>
        </div>
        <button type="button" class="text-white hover:bg-blue-800 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center transition-colors" data-modal-hide="FAQsModal">
          <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      
      <!-- Modal body -->
      <div class="p-4 md:p-6 max-h-[calc(100vh-200px)] overflow-y-auto">
        <!-- Dashboard FAQs Section -->
        <div class="mb-6">
          <div class="flex items-center gap-3 mb-4">
            <img src="{{ asset('gif/Dashboard.gif') }}" alt="Dashboard" class="w-12 h-12 rounded-lg border-2 border-gray-300 dark:border-gray-600">
            <div>
              <h4 class="text-lg font-bold text-gray-900 dark:text-white">Dashboard FAQs</h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">Frequently asked questions about the dashboard functionalities.</p>
            </div>
          </div>
          
          <div id="accordion-dashboard" data-accordion="collapse">
            @php
              $dashboardFAQs = \App\Helpers\FAQHelper::getDashboardFAQs();
            @endphp
            
            @foreach($dashboardFAQs['questions'] as $index => $question)
            <div class="mb-2">
              <h2 id="accordion-dashboard-heading-{{ $index }}">
                <button type="button" class="flex items-center justify-between w-full p-4 font-medium text-left text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors" data-accordion-target="#accordion-dashboard-body-{{ $index }}" aria-expanded="false" aria-controls="accordion-dashboard-body-{{ $index }}">
                  <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $question }}
                  </span>
                  <svg data-accordion-icon class="w-5 h-5 rotate-180 shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
              </h2>
              <div id="accordion-dashboard-body-{{ $index }}" class="hidden" aria-labelledby="accordion-dashboard-heading-{{ $index }}">
                <div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                  <p class="text-gray-700 dark:text-gray-300">{{ $dashboardFAQs['answers'][$index] }}</p>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>

        <!-- Inventory FAQs Section -->
        <div class="mb-6">
          @php
            $inventoryFAQs = \App\Helpers\FAQHelper::getInventoryFAQs();
          @endphp
          <div class="flex items-center gap-3 mb-4">
            <img src="{{ $inventoryFAQs['gif'] }}" alt="Inventory" class="w-12 h-12 rounded-lg border-2 border-gray-300 dark:border-gray-600">
            <div>
              <h4 class="text-lg font-bold text-gray-900 dark:text-white">Inventory FAQs</h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">Frequently asked questions about inventory management.</p>
            </div>
          </div>
          
          <div id="accordion-inventory" data-accordion="collapse">
            
            @foreach($inventoryFAQs['questions'] as $index => $question)
            <div class="mb-2">
              <h2 id="accordion-inventory-heading-{{ $index }}">
                <button type="button" class="flex items-center justify-between w-full p-4 font-medium text-left text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors" data-accordion-target="#accordion-inventory-body-{{ $index }}" aria-expanded="false" aria-controls="accordion-inventory-body-{{ $index }}">
                  <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $question }}
                  </span>
                  <svg data-accordion-icon class="w-5 h-5 rotate-180 shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
              </h2>
              <div id="accordion-inventory-body-{{ $index }}" class="hidden" aria-labelledby="accordion-inventory-heading-{{ $index }}">
                <div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                  <p class="text-gray-700 dark:text-gray-300">{{ $inventoryFAQs['answers'][$index] }}</p>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>

        <!-- Report FAQs Section -->
        <div class="mb-6">
          @php
            $reportFAQs = \App\Helpers\FAQHelper::getReportFAQs();
          @endphp
          <div class="flex items-center gap-3 mb-4">
            <img src="{{ $reportFAQs['gif'] }}" alt="Report" class="w-12 h-12 rounded-lg border-2 border-gray-300 dark:border-gray-600">
            <div>
              <h4 class="text-lg font-bold text-gray-900 dark:text-white">Report FAQs</h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">Frequently asked questions about report generation and management.</p>
            </div>
          </div>
          
          <div id="accordion-report" data-accordion="collapse">
            
            @foreach($reportFAQs['questions'] as $index => $question)
            <div class="mb-2">
              <h2 id="accordion-report-heading-{{ $index }}">
                <button type="button" class="flex items-center justify-between w-full p-4 font-medium text-left text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors" data-accordion-target="#accordion-report-body-{{ $index }}" aria-expanded="false" aria-controls="accordion-report-body-{{ $index }}">
                  <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $question }}
                  </span>
                  <svg data-accordion-icon class="w-5 h-5 rotate-180 shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
              </h2>
              <div id="accordion-report-body-{{ $index }}" class="hidden" aria-labelledby="accordion-report-heading-{{ $index }}">
                <div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                  <p class="text-gray-700 dark:text-gray-300">{{ $reportFAQs['answers'][$index] }}</p>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>

        <!-- Import FAQs Section -->
        <div class="mb-6">
          <div class="flex items-center gap-3 mb-4">
            <img src="{{ asset('gif/Import.gif') }}" alt="Import" class="w-12 h-12 rounded-lg border-2 border-gray-300 dark:border-gray-600">
            <div>
              <h4 class="text-lg font-bold text-gray-900 dark:text-white">Import FAQs</h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">Frequently asked questions about data import processes.</p>
            </div>
          </div>
          
          <div id="accordion-import" data-accordion="collapse">
            @php
              $importFAQs = \App\Helpers\FAQHelper::getImportFAQs();
            @endphp
            
            @foreach($importFAQs['questions'] as $index => $question)
            <div class="mb-2">
              <h2 id="accordion-import-heading-{{ $index }}">
                <button type="button" class="flex items-center justify-between w-full p-4 font-medium text-left text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors" data-accordion-target="#accordion-import-body-{{ $index }}" aria-expanded="false" aria-controls="accordion-import-body-{{ $index }}">
                  <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $question }}
                  </span>
                  <svg data-accordion-icon class="w-5 h-5 rotate-180 shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
              </h2>
              <div id="accordion-import-body-{{ $index }}" class="hidden" aria-labelledby="accordion-import-heading-{{ $index }}">
                <div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                  <p class="text-gray-700 dark:text-gray-300">{{ $importFAQs['answers'][$index] }}</p>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>

        <!-- Maintenance FAQs Section -->
        <div class="mb-6">
          <div class="flex items-center gap-3 mb-4">
            <img src="{{ asset('gif/Maintenance.gif') }}" alt="Maintenance" class="w-12 h-12 rounded-lg border-2 border-gray-300 dark:border-gray-600">
            <div>
              <h4 class="text-lg font-bold text-gray-900 dark:text-white">Maintenance FAQs</h4>
              <p class="text-sm text-gray-600 dark:text-gray-400">Frequently asked questions about system maintenance and updates.</p>
            </div>
          </div>
          
          <div id="accordion-maintenance" data-accordion="collapse">
            @php
              $maintenanceFAQs = \App\Helpers\FAQHelper::getMaintenanceFAQs();
            @endphp
            
            @foreach($maintenanceFAQs['questions'] as $index => $question)
            <div class="mb-2">
              <h2 id="accordion-maintenance-heading-{{ $index }}">
                <button type="button" class="flex items-center justify-between w-full p-4 font-medium text-left text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors" data-accordion-target="#accordion-maintenance-body-{{ $index }}" aria-expanded="false" aria-controls="accordion-maintenance-body-{{ $index }}">
                  <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $question }}
                  </span>
                  <svg data-accordion-icon class="w-5 h-5 rotate-180 shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
              </h2>
              <div id="accordion-maintenance-body-{{ $index }}" class="hidden" aria-labelledby="accordion-maintenance-heading-{{ $index }}">
                <div class="p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                  <p class="text-gray-700 dark:text-gray-300">{{ $maintenanceFAQs['answers'][$index] }}</p>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>

      <!-- Modal footer -->
      <div class="flex items-center justify-end p-4 md:p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 rounded-b-lg">
        <p class="text-sm text-gray-600 dark:text-gray-400">Still have questions? Contact your system administrator.</p>
      </div>
    </div>
  </div>
</div>