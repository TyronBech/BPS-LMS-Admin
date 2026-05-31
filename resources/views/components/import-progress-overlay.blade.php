{{--
    Import Progress Overlay Component
    ===================================
    Props (passed via slot or attributes):
      $statusUrl  – The URL to poll for JSON progress updates
      $indexRoute – Named route to redirect to on completion / dismiss
      $importLabel – Human-readable label e.g. "Materials", "Students"

    Usage:
      <x-import-progress-overlay
          :status-url="route('import.status-materials', $progressId)"
          :index-route="route('import.import-materials')"
          import-label="Materials"
      />
--}}
@props([
    'statusUrl',
    'indexRoute',
    'importLabel' => 'Import',
])

<!-- ============================================================
     PROGRESS OVERLAY  (hidden until JS shows it)
     ============================================================ -->
<div
    id="import-progress-overlay"
    class="fixed inset-0 z-[9999] bg-black/60 backdrop-blur-sm hidden"
    aria-modal="true"
    role="dialog"
    aria-labelledby="import-overlay-title"
>
    {{-- Inner centering wrapper — always flex, never toggled --}}
    <div class="flex items-center justify-center w-full h-full">

        <!-- Card -->
        <div class="relative w-full max-w-md mx-4 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">




            <!-- Card body -->
            <div class="p-8">

                <!-- Icon + spinner -->
                <div class="flex items-center justify-center mb-6">
                    {{-- Spinning ring (visible during pending/processing) --}}
                    <div id="overlay-spinner" class="relative flex items-center justify-center w-16 h-16">
                        <svg class="animate-spin w-16 h-16 text-primary-200 dark:text-slate-700" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span class="absolute text-xs font-bold text-primary-600 dark:text-primary-300" id="overlay-percent-inner">0%</span>
                    </div>

                    {{-- Success icon (hidden until completed — JS adds 'flex' when revealing) --}}
                    <div id="overlay-success-icon" class="hidden w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/40 items-center justify-center">
                        <svg class="w-9 h-9 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>

                    {{-- Error icon (hidden until failed — JS adds 'flex' when revealing) --}}
                    <div id="overlay-error-icon" class="hidden w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/40 items-center justify-center">
                        <svg class="w-9 h-9 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </div>

                <!-- Title -->
                <h2 id="import-overlay-title" class="text-center text-lg font-bold text-slate-800 dark:text-white mb-1">
                    <span id="overlay-title-text">Importing {{ $importLabel }}…</span>
                </h2>

                <!-- Subtitle / status badge -->
                <div class="flex justify-center mb-5">
                    <span id="overlay-status-badge"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                        Pending
                    </span>
                </div>

                <!-- Progress bar track -->
                <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-3 mb-3 overflow-hidden">
                    <div
                        id="overlay-progress-bar"
                        class="h-3 rounded-full bg-gradient-to-r from-primary-500 to-primary-400 transition-all duration-700 ease-out"
                        style="width: 0%"
                    ></div>
                </div>

                <!-- Row counter -->
                <p class="text-center text-sm text-slate-500 dark:text-slate-400 mb-1">
                    <span id="overlay-rows-text">0 / 0 rows processed</span>
                </p>

                <!-- Result counts (hidden until done) -->
                <div id="overlay-result-counts" class="hidden mt-4">
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-3 text-center">
                            <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400" id="overlay-new-count">0</p>
                            <p class="text-xs text-slate-500 mt-0.5">New</p>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center">
                            <p class="text-xl font-bold text-blue-600 dark:text-blue-400" id="overlay-updated-count">0</p>
                            <p class="text-xs text-slate-500 mt-0.5">Updated</p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3 text-center">
                            <p class="text-xl font-bold text-slate-600 dark:text-slate-400" id="overlay-skipped-count">0</p>
                            <p class="text-xs text-slate-500 mt-0.5">Skipped</p>
                        </div>
                    </div>
                </div>

                <!-- Error message box -->
                <div id="overlay-error-box" class="hidden mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-sm font-semibold text-red-700 dark:text-red-300 mb-1">Import Failed</p>
                    <p id="overlay-error-msg" class="text-xs text-red-600 dark:text-red-400 break-words"></p>
                </div>

                <!-- Action buttons (hidden until terminal state) -->
                <div id="overlay-actions" class="hidden mt-6">
                    <div class="flex justify-center gap-3">
                        <a id="overlay-btn-ok"
                            href="{{ $indexRoute }}"
                            class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-lg shadow transition-all hidden">
                            Done
                        </a>
                        <button id="overlay-btn-dismiss"
                            class="px-6 py-2 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm font-semibold rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all hidden">
                            Dismiss
                        </button>
                    </div>
                </div>

                <!-- "Another import is running" notice -->
                <div id="overlay-blocked-box" class="hidden mt-4 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg text-center">
                    <p class="text-sm font-semibold text-amber-700 dark:text-amber-300 mb-1">Import Blocked</p>
                    <p id="overlay-blocked-msg" class="text-xs text-amber-600 dark:text-amber-400 break-words"></p>
                </div>

            </div>{{-- /card-body --}}
        </div>{{-- /card --}}

    </div>{{-- /centering-wrapper --}}
</div>{{-- /import-progress-overlay --}}

<script>
/**
 * Import Progress Overlay Controller
 * Polls the status endpoint every 1.5 s and updates the UI accordingly.
 */
(function () {
    const POLL_INTERVAL_MS  = 1500;
    let STATUS_URL          = @json($statusUrl);
    const INDEX_ROUTE       = @json($indexRoute);
    const IMPORT_LABEL      = @json($importLabel);

    const overlay           = document.getElementById('import-progress-overlay');
    const spinner           = document.getElementById('overlay-spinner');
    const successIcon       = document.getElementById('overlay-success-icon');
    const errorIcon         = document.getElementById('overlay-error-icon');
    const titleText         = document.getElementById('overlay-title-text');
    const statusBadge       = document.getElementById('overlay-status-badge');
    const progressBar       = document.getElementById('overlay-progress-bar');

    const rowsText          = document.getElementById('overlay-rows-text');
    const percentInner      = document.getElementById('overlay-percent-inner');
    const resultCounts      = document.getElementById('overlay-result-counts');
    const newCountEl        = document.getElementById('overlay-new-count');
    const updatedCountEl    = document.getElementById('overlay-updated-count');
    const skippedCountEl    = document.getElementById('overlay-skipped-count');
    const errorBox          = document.getElementById('overlay-error-box');
    const errorMsg          = document.getElementById('overlay-error-msg');
    const blockedBox        = document.getElementById('overlay-blocked-box');
    const blockedMsg        = document.getElementById('overlay-blocked-msg');
    const actions           = document.getElementById('overlay-actions');
    const btnOk             = document.getElementById('overlay-btn-ok');
    const btnDismiss        = document.getElementById('overlay-btn-dismiss');

    let pollTimer = null;
    let progressId = null;

    // Dismiss overlay click handler
    if (btnDismiss) {
        btnDismiss.addEventListener('click', function () {
            window.location.href = INDEX_ROUTE;
        });
    }

    function reEnablePage() {
        const buttons = [
            document.getElementById('btn-insert-materials'),
            document.getElementById('btn-insert-students'),
            document.getElementById('btn-insert-employees')
        ];
        buttons.forEach(btn => {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Insert to Database';
            }
        });

        const form = document.getElementById('import-form');
        if (form) {
            form.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = false;
            });
        }
    }

    /** ─── Public API ─── */
    window.ImportOverlay = {

        /**
         * Override the status URL after dispatch (used when page loads without a known progress ID).
         */
        _setStatusUrl(url) {
            STATUS_URL = url;
        },

        /**
         * Show the overlay and start polling a known progress ID.
         * Called after a successful store AJAX response.
         */
        startPolling(id) {
            progressId = id;
            resetToProcessing();
            overlay.classList.remove('hidden');
            poll();
        },

        /**
         * Show the overlay in a "blocked" state (another import is running).
         * No polling; the user just sees the error and can dismiss.
         */
        showBlocked(message) {
            resetToProcessing();
            overlay.classList.remove('hidden');

            spinner.classList.add('hidden');
            errorIcon.classList.remove('hidden');
            errorIcon.classList.add('flex');

            titleText.textContent = 'Import Blocked';
            setBadge('failed');

            blockedBox.classList.remove('hidden');
            blockedMsg.textContent = message;

            actions.classList.remove('hidden');
            btnDismiss.classList.remove('hidden');

            stopPolling();
        },
    };

    /** Reset all transient UI back to the initial processing state */
    function resetToProcessing() {
        spinner.classList.remove('hidden');
        successIcon.classList.add('hidden');
        successIcon.classList.remove('flex');
        errorIcon.classList.add('hidden');
        errorIcon.classList.remove('flex');
        errorBox.classList.add('hidden');
        blockedBox.classList.add('hidden');
        resultCounts.classList.add('hidden');
        actions.classList.add('hidden');
        btnOk.classList.add('hidden');
        btnDismiss.classList.add('hidden');
        progressBar.style.width = '0%';

        rowsText.textContent = '0 / 0 rows processed';
        percentInner.textContent = '0%';
        titleText.textContent = 'Importing ' + IMPORT_LABEL + '…';
        setBadge('pending');
    }

    function poll() {
        fetch(STATUS_URL, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => r.json())
        .then(data => {
            updateUI(data);

            if (data.status === 'completed' || data.status === 'failed') {
                stopPolling();
                return;
            }

            pollTimer = setTimeout(poll, POLL_INTERVAL_MS);
        })
        .catch(() => {
            // Network hiccup — keep polling
            pollTimer = setTimeout(poll, POLL_INTERVAL_MS * 2);
        });
    }

    function stopPolling() {
        if (pollTimer) {
            clearTimeout(pollTimer);
            pollTimer = null;
        }
    }

    function updateUI(data) {
        const pct = data.percent ?? 0;

        // Progress bar + accent
        progressBar.style.width = pct + '%';

        percentInner.textContent = pct + '%';

        // Row counter
        const processed = Number(data.processed_rows ?? 0).toLocaleString();
        const total     = Number(data.total_rows ?? 0).toLocaleString();
        rowsText.textContent = processed + ' / ' + total + ' rows processed';

        setBadge(data.status);

        if (data.status === 'completed') {
            handleCompleted(data);
        } else if (data.status === 'failed') {
            handleFailed(data);
        }
    }

    function handleCompleted(data) {
        spinner.classList.add('hidden');
        successIcon.classList.remove('hidden');
        successIcon.classList.add('flex');

        titleText.textContent = IMPORT_LABEL + ' Import Complete!';

        progressBar.style.width = '100%';

        percentInner.textContent = '100%';

        const processed = Number(data.total_rows ?? 0).toLocaleString();
        rowsText.textContent = processed + ' / ' + processed + ' rows processed';

        // Show result counts
        const newCount     = Number(data.new_count ?? 0);
        const updatedCount = Number(data.updated_count ?? 0);
        const processedCnt = Number(data.processed_rows ?? 0);
        const skippedCount = Math.max(0, processedCnt - newCount - updatedCount);
        
        newCountEl.textContent     = newCount.toLocaleString();
        updatedCountEl.textContent = updatedCount.toLocaleString();
        skippedCountEl.textContent = skippedCount.toLocaleString();
        resultCounts.classList.remove('hidden');

        // Show "Done" button so the user can manually dismiss
        actions.classList.remove('hidden');
        btnOk.classList.remove('hidden');
    }

    function handleFailed(data) {
        spinner.classList.add('hidden');
        errorIcon.classList.remove('hidden');
        errorIcon.classList.add('flex');

        titleText.textContent = IMPORT_LABEL + ' Import Failed';

        errorMsg.textContent = data.error_message ?? 'An unknown error occurred. Please check the logs.';
        errorBox.classList.remove('hidden');

        actions.classList.remove('hidden');
        btnDismiss.classList.remove('hidden');
    }

    const BADGE_CLASSES = {
        pending:    'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        processing: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        completed:  'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        failed:     'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    };
    const BADGE_LABELS = {
        pending:    '⏳ Pending',
        processing: '⚙️ Processing',
        completed:  '✅ Completed',
        failed:     '❌ Failed',
    };

    function setBadge(status) {
        // Remove all badge colour classes
        Object.values(BADGE_CLASSES).forEach(cls => cls.split(' ').forEach(c => statusBadge.classList.remove(c)));
        const cls = BADGE_CLASSES[status] ?? BADGE_CLASSES.pending;
        cls.split(' ').forEach(c => statusBadge.classList.add(c));
        statusBadge.textContent = BADGE_LABELS[status] ?? status;
    }

    // Auto-start polling if statusUrl was passed at render time (e.g. active import exists on page load)
    if (STATUS_URL && STATUS_URL.trim() !== '') {
        overlay.classList.remove('hidden');
        poll();
    }
}());
</script>
