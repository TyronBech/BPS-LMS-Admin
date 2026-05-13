@extends('layouts.admin-app')

@section('content')
<div class="container mx-auto px-4 py-6">
  <div class="mb-6 text-center">
    <h4 class="text-2xl font-bold text-slate-800 dark:text-white">Student Import</h4>
    <p class="text-sm text-slate-500 mt-1">Bulk import student accounts using Excel format.</p>
  </div>

  @if(!$showTable)
  <div class="max-w-xl mx-auto">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-md p-6">
      <form action="{{ route('import.upload-students') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
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
            Upload & Preview
          </button>

          <a href="{{ route('import.download-students-template') }}" class="skip-loader text-xs text-secondary-600 hover:text-secondary-700 underline font-medium">
            Download Student Excel Template
          </a>
        </div>
      </form>
    </div>
  </div>
  @else
  <div class="space-y-4">
    @include('import.students.table')

    <div class="flex justify-center gap-4 py-4">
      <a href="{{ route('import.import-students') }}" class="px-6 py-2 border border-slate-300 dark:border-slate-600 rounded-md text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition-all">
        Cancel
      </a>
      <button type="submit" form="import-form" formaction="{{ route('import.store-students') }}" class="px-8 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-md shadow-md transition-all">
        Insert to Database
      </button>
    </div>
  </div>
  @endif
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('file_input');
    const fileName = document.getElementById('file-name');
    if (fileInput) {
      fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
          fileName.textContent = this.files[0].name;
          fileName.classList.remove('hidden');
        }
      });
    }
  });
</script>
@endsection