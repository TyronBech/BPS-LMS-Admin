<script>
  document.addEventListener('DOMContentLoaded', function () {
    const sections = document.querySelectorAll('[data-acquisition-section]');
    function syncAcquisitionSections() {
      sections.forEach(function (section) {
        const categoryInput = document.getElementById(section.dataset.categoryInput);
        const category = categoryInput?.selectedOptions?.[0] || categoryInput;
        const eligible = category?.dataset.acquisitionEligible !== 'false';
        section.hidden = !eligible;
        section.disabled = !eligible;
        section.classList.toggle('hidden', !eligible);
      });
    }
    // Existing handlers can select a category programmatically when the material type changes.
    ['category', 'book_type'].forEach(function (id) {
      document.getElementById(id)?.addEventListener('change', function () {
        queueMicrotask(syncAcquisitionSections);
      });
    });
    syncAcquisitionSections();

    if (document.getElementById('copy-book-modal')?.dataset.restoreCopy === 'true') {
      window.addEventListener('load', function () {
        document.querySelector('[data-modal-target="copy-book-modal"]')?.click();
      });
    }
  });
</script>
