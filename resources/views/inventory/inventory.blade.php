@extends('layouts.admin-app')
@section('content')
<h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Inventory</h1>
<div class="container mx-auto px-4">
  <div class="rounded-2xl bg-white shadow-sm dark:bg-gray-800">
    <div class="flex flex-col overflow-hidden lg:flex-row">
      <img class="h-64 w-full object-cover lg:h-auto lg:w-80" src="{{ asset('gif/Book.gif') }}" id="book-img" alt="Book Image">
      <div class="flex-1 p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Book Inventory</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
              @if($inventoryActive)
              Inventory is in progress. Scan books below, then click save to timestamp the scanned books.
              @else
              Start a new inventory cycle to archive the previous inventory and prepare every book for scanning.
              @endif
            </p>
          </div>

          @if($inventoryActive)
          <button type="button" data-modal-target="finish-modal" data-modal-toggle="finish-modal" class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-amber-600 focus:outline-none focus:ring-4 focus:ring-amber-300 dark:focus:ring-amber-800">
            Finish Inventory
          </button>
          @else
          <form action="{{ route('inventory.start') }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-primary-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-800">
              Start Inventory
            </button>
          </form>
          @endif
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
          <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Books</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_books']) }}</p>
          </div>
          <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Scanned</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['scanned']) }}</p>
          </div>
          <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Saved</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['saved']) }}</p>
          </div>
          <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pending Save</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['pending_save']) }}</p>
          </div>
        </div>

        <form action="{{ route('inventory.search') }}" id="search-form" method="POST" class="mt-6 flex flex-col gap-3 md:flex-row md:items-center">
          @csrf
          <input type="hidden" name="perPage" value="{{ $perPage }}">
          <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}" class="w-full rounded-lg border border-primary-700 p-2 shadow-sm focus:border-primary-500 dark:border-primary-400 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-primary-400" placeholder="Scan or enter accession number" autofocus required @disabled(!$inventoryActive)>
          <button type="submit" class="rounded-lg bg-primary-500 px-5 py-2 text-white hover:bg-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-300 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-800" @disabled(!$inventoryActive)>
            Scan
          </button>
        </form>

        <p class="mt-3 text-sm {{ $inventoryActive ? 'text-amber-600 dark:text-amber-300' : 'text-gray-500 dark:text-gray-400' }}">
          @if($inventoryActive)
          Finish inventory only after saving your latest scans. Unsaved scans will not be counted during finishing.
          @else
          Scanning is disabled until you start a new inventory cycle.
          @endif
        </p>
      </div>
    </div>
  </div>
</div>

@include('inventory.table')

<div id="reset-modal" tabindex="-1" class="hidden fixed left-0 right-0 top-0 z-50 h-[calc(100%-1rem)] w-full items-center justify-center overflow-y-auto overflow-x-hidden md:inset-0">
  <div class="relative max-h-full w-full max-w-md p-4">
    <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
      <button type="button" class="absolute end-2.5 top-3 inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="reset-modal">
        <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 text-center md:p-5">
        <svg class="mx-auto mb-4 h-12 w-12 text-gray-400 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <h3 class="mb-3 text-lg font-normal text-gray-500 dark:text-gray-400">Reset this scanned book from the current inventory?</h3>
        <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">The book will stay in the inventory table, but it will be marked as not scanned again.</p>
        <form action="{{ route('inventory.delete') }}" id="reset-form" method="POST">
          @csrf
          @method('DELETE')
          <input type="hidden" name="accession" id="reset-accession" value="">
          <input type="hidden" name="perPage" value="{{ $perPage }}">
          <button data-modal-hide="reset-modal" type="submit" class="inline-flex items-center rounded-lg bg-red-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 dark:focus:ring-red-800">
            Yes, reset scan
          </button>
          <button data-modal-hide="reset-modal" type="button" class="skip-loader ms-3 rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-primary-700 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700">
            No, keep it
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<div id="finish-modal" tabindex="-1" class="hidden fixed left-0 right-0 top-0 z-50 h-[calc(100%-1rem)] w-full items-center justify-center overflow-y-auto overflow-x-hidden md:inset-0">
  <div class="relative max-h-full w-full max-w-md p-4">
    <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
      <button type="button" class="absolute end-2.5 top-3 inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="finish-modal">
        <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
        <span class="sr-only">Close modal</span>
      </button>
      <div class="p-4 text-center md:p-5">
        <svg class="mx-auto mb-4 h-12 w-12 text-amber-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3 2 17h16L10 3Zm0 5v3m0 3h.01" />
        </svg>
        <h3 class="mb-3 text-lg font-normal text-gray-500 dark:text-gray-400">Finish this inventory cycle?</h3>
        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">Saved books will be marked as <span class="font-semibold">On Shelf</span>. Books without a saved timestamp and currently marked <span class="font-semibold">On Shelf</span> will be marked as <span class="font-semibold">Missing</span>.</p>
        @if($stats['pending_save'] > 0)
        <p class="mb-5 text-sm font-medium text-amber-600 dark:text-amber-300">{{ number_format($stats['pending_save']) }} scanned books are still pending save and will not be counted if you finish now.</p>
        @else
        <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">All scanned books have already been saved.</p>
        @endif
        <button data-modal-hide="finish-modal" type="submit" form="inventory-form" formaction="{{ route('inventory.finish') }}" formmethod="POST" class="inline-flex items-center rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-amber-600 focus:outline-none focus:ring-4 focus:ring-amber-300 dark:focus:ring-amber-800">
          Yes, finish inventory
        </button>
        <button data-modal-hide="finish-modal" type="button" class="skip-loader ms-3 rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-primary-700 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700">
          No, continue scanning
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const resetInput = document.getElementById('reset-accession');
    document.querySelectorAll('.deleteBtn').forEach(button => {
      button.addEventListener('click', function() {
        resetInput.value = this.value;
      });
    });
  });
</script>
@endsection
