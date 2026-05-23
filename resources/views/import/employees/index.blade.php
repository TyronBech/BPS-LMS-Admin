@extends('layouts.admin-app')

@section('content')
<div class="container mx-auto px-4 py-6">
  <div class="mb-6 text-center">
    <h4 class="text-2xl font-bold text-slate-800 dark:text-white">Faculty &amp; Staff Import</h4>
    <p class="text-sm text-slate-500 mt-1">Bulk import employee accounts using Excel format.</p>
  </div>

  {{-- Active-import notice banner --}}
  @if(isset($activeImport) && $activeImport)
  <div class="max-w-xl mx-auto mb-4 flex items-start gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-4">
    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <div>
      <p class="text-sm font-semibold text-amber-700 dark:text-amber-300">An import is already in progress</p>
      <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">
        A <strong>{{ ucfirst($activeImport->type) }}</strong> import is currently running.
        Please wait for it to complete before starting a new import.
      </p>
    </div>
  </div>
  @endif

  @if(!$showTable)
  <div class="max-w-xl mx-auto">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-md p-6">
      <form action="{{ route('import.upload-faculties-staffs') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div class="flex flex-col items-center justify-center border-2 border-dashed border-secondary-200 dark:border-slate-600 rounded-lg p-8 bg-slate-50 dark:bg-slate-900 transition-colors hover:bg-slate-100 cursor-pointer relative group">
          <input type="file" name="file" id="file_input" accept=".xlsx, .xls" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
          <svg class="w-10 h-10 text-primary-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
          </svg>
          <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Click or drag Excel file here</p>
          <p id="file-name" class="mt-2 text-xs text-primary-600 font-semibold truncate max-w-full hidden"></p>
        </div>

        <div class="flex flex-col items-center gap-3 pt-2">
          <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-6 rounded-md shadow-sm transition-all focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            Upload &amp; Preview
          </button>

          <a href="{{ route('import.download-employee-template') }}" class="skip-loader text-xs text-secondary-600 hover:text-secondary-700 underline font-medium">
            Download Faculty &amp; Staff Excel Template
          </a>
        </div>
      </form>
    </div>
  </div>
  @else
  <div class="space-y-4">
    @include('import.employees.table')

    <div class="flex justify-center gap-4 py-4">
      <a href="{{ route('import.import-faculties-staffs') }}" class="px-6 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition-all">
        Cancel
      </a>
      <button
        id="btn-insert-employees"
        type="button"
        class="px-8 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-md shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed"
        @if(isset($activeImport) && $activeImport) disabled @endif
      >
        Insert to Database
      </button>
    </div>
  </div>
  @endif
</div>

{{-- Progress overlay --}}
@if(isset($activeImport) && $activeImport && $activeImport->isActive())
  <x-import-progress-overlay
    :status-url="route('import.status-employees', $activeImport->id)"
    :index-route="route('import.import-faculties-staffs')"
    import-label="Faculty &amp; Staff"
  />
@else
  <x-import-progress-overlay
    status-url=""
    :index-route="route('import.import-faculties-staffs')"
    import-label="Faculty &amp; Staff"
  />
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
  const fileInput = document.getElementById('file_input');
  const fileName  = document.getElementById('file-name');
  if (fileInput) {
    fileInput.addEventListener('change', function () {
      if (this.files && this.files[0]) {
        fileName.textContent = this.files[0].name;
        fileName.classList.remove('hidden');
      }
    });
  }

  const btn = document.getElementById('btn-insert-employees');
  if (!btn) return;

  btn.addEventListener('click', async function () {
    btn.disabled    = true;
    btn.textContent = 'Submitting…';

    const form     = document.getElementById('import-form');
    const formData = form ? new FormData(form) : new FormData();

    // Block editing of all form fields now
    if (form) {
      form.querySelectorAll('input, select, textarea').forEach(el => {
        el.disabled = true;
      });
    }

    try {
      const response = await fetch('{{ route("import.store-faculties-staffs") }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          'Accept': 'application/json',
        },
        body: formData,
      });

      const data = await response.json();

      if (data.error) {
        window.ImportOverlay.showBlocked(data.message);
        btn.disabled    = false;
        btn.textContent = 'Insert to Database';
        if (form) {
          form.querySelectorAll('input, select, textarea').forEach(el => {
            el.disabled = false;
          });
        }
        return;
      }

      if (window.ImportOverlay._setStatusUrl) {
        window.ImportOverlay._setStatusUrl('{{ url("/admin/import/employees/import-status") }}/' + data.progress_id);
      }
      window.ImportOverlay.startPolling(data.progress_id);

    } catch (e) {
      btn.disabled    = false;
      btn.textContent = 'Insert to Database';
      if (form) {
        form.querySelectorAll('input, select, textarea').forEach(el => {
          el.disabled = false;
        });
      }
      alert('An unexpected error occurred. Please try again.');
    }
  });
});
</script>
@endsection