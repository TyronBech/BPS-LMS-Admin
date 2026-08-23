@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<div class="container mx-auto px-4">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Report Document</h1>
  <form action="{{ route('report.penalties-search') }}" method="POST" class="auto-search-form">
    @csrf
    <div class="flex flex-col md:flex-row md:flex-wrap md:items-end md:justify-center gap-4 mb-4">

      {{-- Date Range Picker --}}
      <div id="date-range-picker" date-rangepicker class="flex flex-col sm:flex-row items-end justify-center gap-2 w-full md:w-auto">
        <div class="flex flex-col w-full sm:w-auto">
          <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Start Date</label>
          <div class="relative w-full">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
              </svg>
            </div>
            <input id="datepicker-range-start" name="start" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Select date start" value="{{ old('start', $fromInputDate ?? '') }}">
          </div>
        </div>
        <span class="mx-2 text-gray-500 hidden sm:block mb-3">to</span>
        <div class="flex flex-col w-full sm:w-auto">
          <label class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">End Date</label>
          <div class="relative w-full">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
              </svg>
            </div>
            <input id="datepicker-range-end" name="end" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Select date end" value="{{ old('end', $toInputDate ?? '') }}">
          </div>
        </div>
      </div>

      {{-- Search Input --}}
      <div class="flex flex-col w-full md:w-auto md:flex-1 md:max-w-[200px]">
        <label for="search" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Name</label>
        <input type="text" name="search" id="search" placeholder="Juan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" value="{{ old('search', $search ?? '') }}">
      </div>

      {{-- Penalty Status Select --}}
      <div class="flex flex-col w-full md:w-auto md:flex-1 md:max-w-[180px]">
        <label for="penalty_status" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Penalty Status</label>
        <select name="penalty_status" id="penalty_status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          @foreach($penaltyStatuses as $status)
          <option value="{{ $status }}" {{ ($penaltyStatus ?? '') === $status ? 'selected' : '' }}>{{ $status }}</option>
          @endforeach
        </select>
      </div>

      {{-- Subject Filter --}}
      <div class="flex flex-col w-full md:w-auto md:flex-1 md:max-w-[200px]">
        <label for="subject_id" class="block text-xs font-medium mb-1 text-gray-500 dark:text-gray-400">Subject</label>
        <select name="subject_id" id="subject_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
          <option value="All" {{ ($subjectId ?? 'All') == 'All' ? 'selected' : '' }}>All Subjects</option>
          @foreach($subjects as $subject)
          <option value="{{ $subject->id }}" {{ ($subjectId ?? '') == $subject->id ? 'selected' : '' }}>
            {{ $subject->access_code }}{{ $subject->description ? ' - ' . $subject->description : '' }}
          </option>
          @endforeach
        </select>
      </div>

      {{-- Action Buttons --}}
      <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
        <button type="button" data-clear-url="{{ route('report.penalties') }}" class="btn-clear-filters bg-white hover:bg-gray-100 text-gray-900 border border-gray-300 font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-600 text-sm w-full sm:w-auto" title="Clear Filters">Clear</button>
        @can(PermissionsEnum::CREATE_REPORTS)
        <button type="submit" name="submit" value="pdf" class="bg-red-500 hover:bg-red-700 active:bg-red-900 text-white font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors text-sm w-full sm:w-auto">PDF</button>
        <button type="submit" name="submit" value="excel" class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white font-bold py-2.5 px-4 rounded whitespace-nowrap transition-colors text-sm w-full sm:w-auto">Excel</button>
        @endcan
      </div>
    </div>
  </form>
  <div id="table-container">
    @include('report.penalties.table')
  </div>
</div>

{{-- Edit Penalty Modal --}}
<div id="editPenaltyModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-900/50 dark:bg-gray-900/80">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Edit Penalty
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="editPenaltyModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form id="editPenaltyForm" action="{{ route('report.penalties-update-status') }}" method="POST" class="p-4 md:p-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="transaction_id" id="edit_transaction_id">
                
                <div class="grid gap-4 mb-4 grid-cols-2">
                    <div class="col-span-2">
                        <label for="edit_penalty_status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Penalty Status</label>
                        <select id="edit_penalty_status" name="penalty_status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @foreach($penaltyStatuses as $status)
                                @if($status !== 'All')
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label for="edit_penalty_total" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Penalty Amount (Override)</label>
                        <input type="number" step="0.01" min="0" name="penalty_total" id="edit_penalty_total" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="0.00">
                    </div>
                    <div class="col-span-2 hidden" id="discount_container">
                        <label for="edit_discount" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Discount Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="discount" id="edit_discount" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="e.g. 20 for 20%">
                    </div>
                </div>
                <button type="submit" class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    Update Penalty
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-penalty-btn');
    const modalEl = document.getElementById('editPenaltyModal');
    const modal = new Modal(modalEl);
    const closeButtons = modalEl.querySelectorAll('[data-modal-toggle="editPenaltyModal"]');
    
    const statusSelect = document.getElementById('edit_penalty_status');
    const discountContainer = document.getElementById('discount_container');
    
    // Show/hide discount field based on status
    statusSelect.addEventListener('change', function() {
        if (this.value === 'Discounted') {
            discountContainer.classList.remove('hidden');
            document.getElementById('edit_discount').required = true;
        } else {
            discountContainer.classList.add('hidden');
            document.getElementById('edit_discount').required = false;
        }
    });

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const status = this.getAttribute('data-status') || 'No Penalty';
            const amount = this.getAttribute('data-amount');
            const discount = this.getAttribute('data-discount');
            
            document.getElementById('edit_transaction_id').value = id;
            document.getElementById('edit_penalty_status').value = status;
            document.getElementById('edit_penalty_total').value = amount;
            
            if (discount) {
                document.getElementById('edit_discount').value = discount;
            } else {
                document.getElementById('edit_discount').value = '';
            }
            
            // Trigger change event to show/hide discount container
            statusSelect.dispatchEvent(new Event('change'));
            
            modal.show();
        });
    });

    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            modal.hide();
        });
    });
});
</script>
@endsection
