import "flowbite";
import Chart from "chart.js/auto";
import ChartDataLabels from "chartjs-plugin-datalabels";
import $ from "jquery";
import axios from "axios";

window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.$ = window.jQuery = $;
window.Chart = Chart;
Chart.register(ChartDataLabels);

document.addEventListener("DOMContentLoaded", () => {
    const loader = document.getElementById("form-loader");

    function showLoader() {
        loader.classList.remove("hidden");
    }

    function hideLoader() {
        loader.classList.add("hidden");
    }

    // --- 1️⃣ Handle form submissions (standard page changes) ---
    document.querySelectorAll("form").forEach((form) => {
        form.addEventListener("submit", (e) => {
            const submitter = e.submitter;

            // Wait a tick to check if the submission was prevented by client-side or AJAX logic
            setTimeout(() => {
                if (e.defaultPrevented) return;

                if (
                    form.classList.contains("skip-loader") || 
                    form.closest(".skip-loader") ||
                    form.target === "_blank"
                ) {
                    return;
                }
                
                const skipAjaxValues = ['pdf', 'excel', 'barcode', 'callNumber'];
                if (submitter && skipAjaxValues.includes(submitter.value)) {
                    return;
                }

                if (form.classList.contains('auto-search-form')) {
                    return;
                }

                showLoader();
            }, 0);
        });
    });

    // --- 2️⃣ Handle anchor (<a>) clicks (standard navigation) ---
    document.querySelectorAll("a[href]").forEach((link) => {
        link.addEventListener("click", (e) => {
            // Wait a tick to check if navigation was prevented by a custom click handler
            setTimeout(() => {
                if (e.defaultPrevented) return;

                const href = link.getAttribute("href");

                // Skip internal anchors, new tabs, JS voids, or dropdown triggers
                if (
                    !href ||
                    href.startsWith("#") ||
                    href.startsWith("javascript:") ||
                    link.id === "dropdownNavbarLink" ||
                    link.closest("#dropdownNavbarLink") || // nested inside dropdown button
                    link.target === "_blank" ||
                    link.classList.contains("skip-loader") || // skip-loader class
                    link.closest(".skip-loader") // skip-loader parent
                ) {
                    return;
                }

                showLoader();
            }, 0);
        });
    });

    // --- 3️⃣ Handle page unload (direct location change / refresh / reload) ---
    window.addEventListener("beforeunload", () => {
        showLoader();
    });

    // --- 4️⃣ Hide loader when page reloads or back navigation happens ---
    window.addEventListener("pageshow", hideLoader);

    // --- 6️⃣ Maintenance Forms Standard Validation ---
    if (
        window.location.pathname.includes("/maintenance/") ||
        window.location.pathname.includes("/maintenance")
    ) {
        const requiredInputs = document.querySelectorAll(
            "input[required], select[required], textarea[required]",
        );

        requiredInputs.forEach((input) => {
            // 1. Add Asterisk to Label
            if (input.id) {
                const label = document.querySelector(
                    `label[for="${input.id}"]`,
                );
                if (label && !label.innerHTML.includes("*")) {
                    // Check if it already has text-red-500 span to avoid duplicates
                    if (!label.querySelector(".text-red-500")) {
                        const asterisk = document.createElement("span");
                        asterisk.className =
                            "text-red-600 dark:text-red-500 ml-1";
                        asterisk.innerText = "*";
                        label.appendChild(asterisk);
                    }
                }
            }

            // 2. Add Blur Event for Validation
            input.addEventListener("blur", () => {
                validateInput(input);
            });

            // 3. Remove error on input/change
            const clearHandler = () => {
                // We only clear if the "required" condition is met.
                // We use checkValidity() but strictly check if we have a value.
                if (input.value.trim() !== "") {
                    clearValidationError(input);
                }
            };
            input.addEventListener("input", clearHandler);
            input.addEventListener("change", clearHandler);
        });
    }

    const validClasses = [
        "border-gray-300",
        "focus:ring-primary-400",
        "focus:border-primary-400",
        "dark:border-gray-600",
        "dark:focus:ring-primary-500",
        "dark:focus:border-primary-500",
    ];
    const errorClasses = [
        "border-red-500",
        "focus:ring-red-500",
        "focus:border-red-500",
        "dark:border-red-500",
        "dark:focus:ring-red-500",
        "dark:focus:border-red-500",
    ];

    function validateInput(input) {
        if (input.validity.valueMissing) {
            showValidationError(input);
        } else {
            clearValidationError(input);
        }
    }

    function showValidationError(input) {
        if (!input.classList.contains("border-red-500")) {
            validClasses.forEach((cls) => input.classList.remove(cls));
            errorClasses.forEach((cls) => input.classList.add(cls));

            // Add message
            let errorMsg = input.parentNode.querySelector(
                ".client-required-error",
            );
            if (!errorMsg) {
                errorMsg = document.createElement("p");
                errorMsg.className =
                    "mt-2 text-sm text-red-600 dark:text-red-500 client-required-error";
                errorMsg.innerText = "This field is required.";
                input.parentNode.appendChild(errorMsg);
            }
        }
    }

    function clearValidationError(input) {
        if (input.classList.contains("border-red-500")) {
            errorClasses.forEach((cls) => input.classList.remove(cls));
            validClasses.forEach((cls) => input.classList.add(cls));

            const errorMsg = input.parentNode.querySelector(
                ".client-required-error",
            );
            if (errorMsg) {
                errorMsg.remove();
            }
        }
    }

    // --- 7️⃣ Generic Auto-Search Logic ---
    document.querySelectorAll('.auto-search-form').forEach(form => {
        // Prepend a hidden submit button so that pressing Enter or calling requestSubmit()
        // without arguments doesn't implicitly use the PDF/Excel buttons.
        const hiddenSubmit = document.createElement('button');
        hiddenSubmit.type = 'submit';
        hiddenSubmit.name = 'auto_search_trigger';
        hiddenSubmit.style.display = 'none';
        form.prepend(hiddenSubmit);

        const inputs = form.querySelectorAll('input[type="text"], input[type="search"]');
        const selects = form.querySelectorAll('select');
        let debounceTimer;

        inputs.forEach(input => {
            const triggerSearch = () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    form.classList.add('skip-loader');
                    if (input.id) sessionStorage.setItem('autoSearchFocus', input.id);
                    if (form.requestSubmit) {
                        form.requestSubmit(hiddenSubmit);
                    } else {
                        form.submit();
                    }
                }, 500);
            };

            input.addEventListener('input', triggerSearch);
            input.addEventListener('change', triggerSearch);
            input.addEventListener('changeDate', triggerSearch);
        });

        selects.forEach(select => {
            select.addEventListener('change', () => {
                form.classList.add('skip-loader');
                if (form.requestSubmit) {
                    form.requestSubmit(hiddenSubmit);
                } else {
                    form.submit();
                }
            });
        });

        form.addEventListener('submit', async (e) => {
            const submitter = e.submitter;
            const skipAjaxValues = ['pdf', 'excel', 'barcode', 'callNumber'];
            
            // Allow normal submission for exports
            if (submitter && skipAjaxValues.includes(submitter.value)) {
                return;
            }

            e.preventDefault();
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const method = form.method.toUpperCase();
            
            let url = form.action || window.location.href;
            let fetchOptions = {
                headers: { 'X-Skip-Loader': 'true' }
            };

            if (method === 'GET') {
                const urlObj = new URL(url);
                urlObj.search = params.toString();
                url = urlObj.toString();
            } else {
                fetchOptions.method = method;
                fetchOptions.body = formData;
            }

            try {
                const response = await fetch(url, fetchOptions);
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const containerId = 'table-container';
                const oldContainer = document.getElementById(containerId);
                const newContainer = doc.getElementById(containerId);
                
                if (oldContainer && newContainer) {
                    oldContainer.innerHTML = newContainer.innerHTML;
                    if (typeof initFlowbite === 'function') initFlowbite();
                    if (method === 'GET') {
                        window.history.pushState({}, '', url);
                    }
                } else {
                    // Fallback if no container found
                    window.location.href = url;
                }
            } catch (error) {
                console.error('AJAX search failed', error);
            }
        });
        
        // Handle Clear Filters button if any
        const clearBtn = form.querySelector('.btn-clear-filters');
        if (clearBtn) {
            clearBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                
                // Use data-clear-url if provided
                const clearUrl = clearBtn.getAttribute('data-clear-url');
                let url;
                
                if (clearUrl) {
                    url = new URL(clearUrl, window.location.origin);
                } else {
                    url = new URL(form.action || window.location.href);
                    const tabInput = form.querySelector('input[name="tab"]');
                    url.search = '';
                    if (tabInput) {
                        url.searchParams.set('tab', tabInput.value);
                    }
                }
                
                try {
                    const response = await fetch(url.toString(), { headers: { 'X-Skip-Loader': 'true' } });
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const oldContainer = document.getElementById('table-container');
                    const newContainer = doc.getElementById('table-container');
                    
                    if (oldContainer && newContainer) {
                        oldContainer.innerHTML = newContainer.innerHTML;
                        
                        // Clear the form inputs visually and reset datepicker instances
                        form.querySelectorAll('input[type="text"], input[type="search"]').forEach(inp => {
                            inp.value = '';
                            if (inp._datepicker) {
                                inp._datepicker.clearSelection();
                            }
                        });

                        // Clear any date-rangepickers
                        form.querySelectorAll('[date-rangepicker]').forEach(el => {
                            if (el._dateRangePicker) {
                                el._dateRangePicker.clearSelection();
                            }
                        });

                        form.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);

                        if (typeof initFlowbite === 'function') initFlowbite();
                        window.history.pushState({}, '', url.toString());
                    } else {
                        window.location.href = url.toString();
                    }
                } catch (error) {
                    window.location.href = url.toString();
                }
            });
        }
    });

    // Global listener to clear datepicker selection when input is cleared manually.
    // Flowbite Datepicker restores the old date on blur if the instance is not cleared.
    const clearDatepickerIfEmpty = (e) => {
        const input = e.target;
        if (input && (input.hasAttribute('datepicker') || input.closest('[date-rangepicker]'))) {
            if (input.value.trim() === '') {
                if (input._datepicker) {
                    input._datepicker.clearSelection();
                }
            }
        }
    };
    document.addEventListener('input', clearDatepickerIfEmpty);
    document.addEventListener('change', clearDatepickerIfEmpty);
    document.addEventListener('blur', clearDatepickerIfEmpty, true);

    // Restore focus on page load for auto-search
    const focusId = sessionStorage.getItem('autoSearchFocus');
    if (focusId) {
        const el = document.getElementById(focusId);
        if (el) {
            el.focus();
            if (typeof el.selectionStart === 'number') {
                el.selectionStart = el.selectionEnd = el.value.length;
            }
        }
        sessionStorage.removeItem('autoSearchFocus');
    }
});
