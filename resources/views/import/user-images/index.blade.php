@extends('layouts.admin-app')

@section('content')
<div class="container mx-auto px-4 py-6">
  <div class="mb-6 text-center">
    <h4 class="text-2xl font-bold text-slate-800 dark:text-white">User Images Import</h4>
    <p class="text-sm text-slate-500 mt-1">Bulk import profile images for students and employees using a local directory path.</p>
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
      <form action="{{ route('import.upload-user-images') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf

        {{-- Info banner --}}
        <div class="flex items-start gap-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
          <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div>
            <p class="text-sm font-semibold text-blue-700 dark:text-blue-300">How it works</p>
            <ul class="text-xs text-blue-600 dark:text-blue-400 mt-1 space-y-1 list-disc list-inside">
              <li>Select one or more <strong>user images</strong> from your device (you can press Ctrl+A to select all files in a folder).</li>
              <li>Only image files (<strong>.jpg</strong>, <strong>.jpeg</strong>, <strong>.png</strong>) will be processed.</li>
              <li>Each image filename must be the <strong>Student ID</strong> or <strong>Employee ID</strong> of the user (e.g. <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">2024-00001.jpg</code>).</li>
              <li>Maximum file size per image: <strong>5 MB</strong>.</li>
            </ul>
          </div>
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
            Select Images
          </label>
          <div id="folder-drop-zone" class="flex flex-col items-center justify-center border-2 border-dashed border-secondary-200 dark:border-slate-600 rounded-lg p-8 bg-slate-50 dark:bg-slate-900 transition-colors hover:bg-slate-100 cursor-pointer relative group">
            <input
              type="file"
              name="images[]"
              id="folder_input"
              multiple
              accept=".jpg,.jpeg,.png"
              class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
              required
            >
            <svg class="w-10 h-10 text-primary-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p id="folder-label" class="text-sm font-medium text-slate-700 dark:text-slate-200">Click or drag images to select</p>
            <p id="folder-info" class="mt-2 text-xs text-primary-600 font-semibold truncate max-w-full hidden"></p>
            <p class="mt-1 text-[10px] text-slate-400">Only .jpg, .jpeg, and .png files will be imported</p>
          </div>
        </div>

        <div class="flex flex-col items-center gap-3 pt-2">
          <button type="submit" id="btn-scan" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-6 rounded-md shadow-sm transition-all focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
            Upload & Preview
          </button>
        </div>
      </form>
    </div>
  </div>
  @else
  <div class="space-y-4">
    {{-- Uploaded files info --}}
    <div class="max-w-4xl mx-auto flex items-center gap-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2">
      <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
      </svg>
      <span class="text-xs font-mono text-slate-600 dark:text-slate-400">{{ $folderName ?? 'Uploaded Images' }}</span>
    </div>

    @include('import.user-images.table')

    <div class="flex justify-center gap-4 py-4">
      <a href="{{ route('import.import-user-images') }}" class="px-6 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition-all">
        Cancel
      </a>
      @if($hasMatched)
      <button
        id="btn-import-user-images"
        type="button"
        class="px-8 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-md shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed"
        @if(isset($activeImport) && $activeImport) disabled @endif
      >
        Import to Database
      </button>
      @endif
    </div>
  </div>
  @endif
</div>

{{-- Progress overlay --}}
@if(isset($activeImport) && $activeImport && $activeImport->isActive())
  <x-import-progress-overlay
    :status-url="route('import.status-user-images', $activeImport->id)"
    :index-route="route('import.import-user-images')"
    import-label="User Images"
  />
@else
  <x-import-progress-overlay
    status-url=""
    :index-route="route('import.import-user-images')"
    import-label="User Images"
  />
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
  // --- Folder picker logic ---
  const folderInput = document.getElementById('folder_input');
  const folderLabel = document.getElementById('folder-label');
  const folderInfo  = document.getElementById('folder-info');
  const btnScan     = document.getElementById('btn-scan');
  const allowedExts = ['jpg', 'jpeg', 'png'];

  if (folderInput) {
    folderInput.addEventListener('change', function () {
      if (!this.files || this.files.length === 0) {
        folderLabel.textContent = 'Click or drag images to select';
        folderInfo.classList.add('hidden');
        if (btnScan) btnScan.disabled = true;
        return;
      }

      // Count only image files with allowed extensions
      let imageCount = 0;
      for (let i = 0; i < this.files.length; i++) {
        const ext = this.files[i].name.split('.').pop().toLowerCase();
        if (allowedExts.includes(ext)) {
          imageCount++;
        }
      }

      folderLabel.textContent = imageCount + ' image file' + (imageCount !== 1 ? 's' : '') + ' selected';
      folderInfo.textContent  = 'Ready to upload';
      folderInfo.classList.remove('hidden');

      if (btnScan) {
        btnScan.disabled = imageCount === 0;
      }
    });
  }

  // --- Import button logic ---
  const btn = document.getElementById('btn-import-user-images');
  if (!btn) return;

  btn.addEventListener('click', async function () {
    btn.disabled    = true;
    btn.textContent = 'Submitting…';

    try {
      const response = await fetch('{{ route("import.store-user-images") }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          'Accept': 'application/json',
        },
      });

      const data = await response.json();

      if (data.error) {
        window.ImportOverlay.showBlocked(data.message);
        btn.disabled    = false;
        btn.textContent = 'Import to Database';
        return;
      }

      if (window.ImportOverlay._setStatusUrl) {
        window.ImportOverlay._setStatusUrl('{{ url("/admin/import/user-images/import-status") }}/' + data.progress_id);
      }
      window.ImportOverlay.startPolling(data.progress_id);

    } catch (e) {
      btn.disabled    = false;
      btn.textContent = 'Import to Database';
      alert('An unexpected error occurred. Please try again.');
    }
  });
});
</script>
@endsection
