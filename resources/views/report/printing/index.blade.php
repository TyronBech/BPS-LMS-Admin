@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">{{ \App\Helpers\ReportHelper::getFormattedHeaderSuffix('Printing & Photocopy Report', $fromInputDate, $toInputDate, $data, 'printed_at') }}</h1>
  <form action="{{ route('report.printing-search') }}" method="POST" class="auto-search-form">
    @csrf
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-center gap-3 mb-4">
      {{-- Date Range Picker --}}
      <div id="date-range-picker" date-rangepicker class="flex flex-col sm:flex-row items-end gap-2 w-full md:w-auto">
        <div class="flex flex-col w-full sm:w-56">
          <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Start Date</label>
          <div class="relative w-full">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
              </svg>
            </div>
            <input id="datepicker-range-start" name="start" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Select date start" value="{{ $fromInputDate }}">
          </div>
        </div>

        <span class="mx-2 text-gray-500 hidden sm:inline mb-3">to</span>

        <div class="flex flex-col w-full sm:w-56">
          <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">End Date</label>
          <div class="relative w-full">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
              </svg>
            </div>
            <input id="datepicker-range-end" name="end" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Select date end" value="{{ $toInputDate }}">
          </div>
        </div>
      </div>

      {{-- Search Input --}}
      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[200px]">
        <label for="search" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Search</label>
        <input type="text" name="search" id="search" placeholder="Name or Topic..." class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ $search }}">
      </div>

      {{-- User Type Selector --}}
      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[180px]">
        <label for="user_type" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">User Type</label>
        <select id="user_type" name="user_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          <option value="students" {{ $userType == 'students' ? 'selected' : '' }}>Students</option>
          <option value="employees" {{ $userType == 'employees' ? 'selected' : '' }}>Faculties & Staffs</option>
        </select>
      </div>

      {{-- Print/Photocopy Type Selector --}}
      <div class="flex flex-col w-full lg:w-auto lg:flex-1 lg:max-w-[180px]">
        <label for="printing_type" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Activity Type</label>
        <select id="printing_type" name="printing_type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          <option value="all" {{ $printingType == 'all' ? 'selected' : '' }}>All</option>
          <option value="print" {{ $printingType == 'print' ? 'selected' : '' }}>Print</option>
          <option value="photocopy" {{ $printingType == 'photocopy' ? 'selected' : '' }}>Photocopy</option>
        </select>
      </div>

      {{-- Action Buttons --}}
      <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
        <button type="button" data-clear-url="{{ route('report.printing') }}" class="btn-clear-filters bg-white hover:bg-gray-100 text-gray-900 border border-gray-300 font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-600 text-sm w-full sm:w-auto" title="Clear Filters">Clear</button>
        @can(PermissionsEnum::CREATE_REPORTS)
        <button type="submit" name="submit" value="pdf" class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors text-sm w-full sm:w-auto">PDF</button>
        <button type="submit" name="submit" value="excel" class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors text-sm w-full sm:w-auto">Excel</button>
        @endcan
      </div>
    </div>
  </form>

  <div id="table-container">
    @include('report.printing.table')
  </div>

  @can(PermissionsEnum::CREATE_PRINTING_ENTRY)
  @include('report.printing.form-modal')
  @endcan

  @can(PermissionsEnum::CREATE_PRINTING_ENTRY->value, 'admin')
  <div id="delete-printing-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-900 bg-opacity-50">
    <div class="relative p-4 w-full max-w-md max-h-full">
      <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
        <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="delete-printing-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
        <div class="p-4 md:p-5 text-center">
          <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
          <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this printing/photocopy entry?</h3>
          <form action="{{ route('report.printing-delete') }}" method="POST" class="flex items-center justify-center">
            @csrf
            @method('DELETE')
            <input type="hidden" name="id" id="delete_printing_id" value="" />
            <button data-modal-hide="delete-printing-modal" type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
              Yes, I'm sure
            </button>
            <button data-modal-hide="delete-printing-modal" type="button" class="skip-loader py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-50 dark:border-gray-600 dark:hover:text-primary-50 dark:hover:bg-gray-700 shadow-md">No, cancel</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  @endcan
</div>
@endsection
@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const deletePrintingBtn = document.querySelectorAll('.deletePrintingBtn');
    const deletePrintingID = document.getElementById('delete_printing_id');
    deletePrintingBtn.forEach(btn => {
      btn.addEventListener('click', function(event) {
        const printingId = this.value || event.currentTarget.value;
        if (deletePrintingID) {
          deletePrintingID.value = printingId;
        }
      });
    });
  });
</script>
@endsection
