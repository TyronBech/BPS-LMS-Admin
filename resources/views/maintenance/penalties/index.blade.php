@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<h1 class="font-semibold text-center text-4xl p-5">Maintenance</h1>
<div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
  <div class="flex justify-between">
    <h5 class="mb-1 text-2xl font-bold tracking-tight">Penalty Rules</h5>
    <div>
      @if(auth()->user()->can(PermissionsEnum::ADD_PENALTY_RULES))
      <button data-modal-target="add-penalty-rule-modal" data-modal-toggle="add-penalty-rule-modal" class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">Add new rule</button>
      @endif
    </div>
  </div>
  <hr class="h-px my-3 bg-gray-200 border-0">
  @include('maintenance.penalties.table')
</div>
<div id="add-penalty-rule-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-2xl max-h-full">
    <!-- Modal content -->
    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          Add new rule
        </h3>
        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="add-penalty-rule-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <!-- Modal body -->
      <div class="p-4 md:p-5 space-y-4">
        <form action="{{ route('maintenance.store-penalty-rule') }}" method="POST">
          @csrf
          <h6 class="mb-1 text-xl font-semibold tracking-tight">Rule Information</h6>
          <div class="mb-5">
            <label for="type" class="block mb-2 text-sm font-medium">Type:</label>
            <input type="text" id="type" name="type" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Late" required>
            @error('type')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <div class="mb-5">
            <label for="description" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description:</label>
            <textarea id="description" name="description" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Description"></textarea>
            @error('description')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <div class="mb-5">
            <label for="rate" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Rate:</label>
            <input type="number" id="rate" name="rate" aria-describedby="helper-text-explanation" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="10" min="0" max="1000" required />
            @error('rate')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <div class="mb-5">
            <label for="per_day" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Accrues Daily: <span class="text-xs italic font-normal">(Amount of penalty increments daily?)</span></label>
            <div class="flex items-center mb-4">
              <input id="per_day-1" type="radio" value="1" name="per_day" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
              <label for="per_day-1" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Yes</label>
            </div>
            <div class="flex items-center">
              <input checked id="per_day-2" type="radio" value="0" name="per_day" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
              <label for="per_day-2" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">No</label>
            </div>
            @error('per_day')
            <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
              <span class="font-medium">{{ $message }}</span>
            </div>
            @enderror
          </div>
          <button data-modal-hide="add-penalty-rule-modal" type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Add</button>
          <button data-modal-hide="add-penalty-rule-modal" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancel</button>
        </form>
      </div>
      <!-- Modal footer -->
    </div>
  </div>
</div>
@endsection