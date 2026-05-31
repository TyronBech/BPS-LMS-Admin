@extends('layouts.admin-app')
@section('content')
@use(Spatie\Permission\Models\Role)
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="w-full p-6 bg-white border border-gray-200 rounded-2xl dark:bg-gray-800 dark:border-gray-700 shadow-md transition-all duration-300">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Add Admin</h5>
      <a href="{{ request('return_to', route('maintenance.admins')) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500 mt-4 sm:mt-0 shadow-sm transition-all duration-200">
        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
        </svg>
        Back
      </a>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    {{-- Search Form --}}
    <form action="{{ route('maintenance.search-user') }}" class="max-w-2xl mx-auto my-6" method="POST">
      @csrf
      <div class="mb-5">
        <label for="user-info" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Search for a user to make an admin</label>
        <div class="flex items-center">
          <div class="relative w-full">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
              </svg>
            </div>
            <input type="text" id="user-info" name="user-info" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Enter Name, Email, or RFID" required value="{{ old('user-info', request('user-info')) }}">
          </div>
          <button type="submit" class="text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 ms-2 dark:bg-primary-400 dark:hover:bg-primary-500 focus:outline-none dark:focus:ring-primary-500 transition-all duration-200">Search</button>
        </div>
        @error('user-info')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
      </div>
    </form>

    {{-- Results Grid --}}
    @if(isset($searched))
      <hr class="h-px my-6 bg-gray-200 border-0 dark:bg-gray-700">
      @if($searched->isNotEmpty())
        <h6 class="mb-4 text-xl font-semibold tracking-tight text-gray-900 dark:text-white flex items-center">
          <svg class="w-5 h-5 me-2 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.333 6.764a3 3 0 1 1 3.141-5.023 3.96 3.96 0 0 0 2.22-.262m-5.36 5.285a3 3 0 0 1 .288-3.197m0 0a3.96 3.96 0 0 0-2.22-.262M1.077 8.91a8.961 8.961 0 0 0 3.715 2.906m0 0a9 9 0 0 0 4.416.03m-8.131-2.94a8.959 8.959 0 0 1 3.715-2.906m0 0a9 9 0 0 1 4.416.03M19 8.91a8.963 8.963 0 0 1-3.715 2.906m0 0a9 9 0 0 1-4.416.03m8.131-2.94a8.958 8.958 0 0 0-3.715-2.906m0 0a9 9 0 0 0-4.416.03m5.393 7.962a3 3 0 1 1-3.141 5.023 3.96 3.96 0 0 0-2.22.262m5.36-5.285a3 3 0 0 1-.288 3.197m0 0a3.96 3.96 0 0 0 2.22.262M18.923 11.09a8.961 8.961 0 0 1-3.715-2.906m0 0a9 9 0 0 1-4.416-.03m8.131 2.94a8.959 8.959 0 0 0-3.715 2.906m0 0a9 9 0 0 0-4.416-.03"/>
          </svg>
          Search Results ({{ $searched->count() }})
        </h6>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
          @foreach($searched as $user)
            @php
              $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
              $isStudent = ($user->user_type === 'student');
              $themeClass = $isStudent 
                  ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-800/30' 
                  : 'bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 border-purple-100 dark:border-purple-800/30';
              $badgeClass = $isStudent 
                  ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' 
                  : 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300';
            @endphp
            <div class="flex flex-col justify-between p-5 bg-white border border-gray-200 rounded-2xl shadow-sm dark:bg-gray-800 dark:border-gray-700 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-full border font-bold text-lg {{ $themeClass }}">
                  {{ $initials }}
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="text-base font-semibold text-gray-900 dark:text-white truncate" title="{{ $user->last_name }}, {{ $user->first_name }} {{ $user->middle_name }}">
                    {{ $user->last_name }}, {{ $user->first_name }}
                  </h4>
                  <p class="text-xs text-gray-500 dark:text-gray-400 truncate mb-2">
                    {{ $user->email }}
                  </p>
                  <div class="flex flex-col space-y-1 text-xs">
                    <span class="text-gray-500 dark:text-gray-400">RFID: <span class="font-mono text-gray-700 dark:text-gray-300">{{ $user->rfid }}</span></span>
                    <span class="inline-flex items-center w-fit text-[10px] font-semibold px-2.5 py-0.5 rounded-full {{ $badgeClass }}">
                      {{ $user->category }}
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="mt-5">
                <button type="button" 
                        class="assign-role-btn w-full text-center inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 focus:ring-4 focus:outline-none focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 transition-all duration-200 cursor-pointer shadow-sm"
                        data-modal-target="assign-role-modal"
                        data-modal-toggle="assign-role-modal"
                        data-rfid="{{ $user->rfid }}"
                        data-name="{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}"
                        data-email="{{ $user->email }}"
                        data-category="{{ $user->category }}"
                        data-user-type="{{ $user->user_type }}"
                        data-initials="{{ $initials }}"
                        data-theme-class="{{ $themeClass }}"
                        data-badge-class="{{ $badgeClass }}">
                  Assign Role
                </button>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="text-center py-12 px-4 max-w-md mx-auto">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">No users found</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">We couldn't find any matching users who aren't already admins. Make sure they are registered and do not currently have administrative privileges.</p>
        </div>
      @endif
    @endif
  </div>
</div>

{{-- Assign Role Modal --}}
<div id="assign-role-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
  <div class="relative p-4 w-full max-w-md max-h-full">
    <div class="relative bg-white rounded-2xl shadow-xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden">
      <!-- Modal header -->
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-700 border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          Assign Administrator Role
        </h3>
        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-700 dark:hover:text-white" data-modal-hide="assign-role-modal">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      <!-- Modal body -->
      <form action="{{ route('maintenance.store-admin') }}" method="POST" class="p-4 md:p-5">
        @csrf
        <input type="hidden" name="adminID" id="modal-admin-rfid" value="">
        
        <div class="space-y-4">
          <!-- User Details Block -->
          <div class="flex items-center p-3 space-x-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-700">
            <div id="modal-user-avatar" class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full border font-bold text-sm">
              <!-- JS Filled -->
            </div>
            <div class="flex-grow min-w-0">
              <p id="modal-user-name" class="text-sm font-semibold text-gray-900 dark:text-white truncate"></p>
              <p id="modal-user-email" class="text-xs text-gray-500 dark:text-gray-400 truncate"></p>
              <span id="modal-user-badge" class="mt-1 inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-full"></span>
            </div>
          </div>

          <!-- Role Dropdown -->
          <div>
            <label for="modal-role" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Select Admin Role</label>
            <select name="role" id="modal-role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-750 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
              <option value="" disabled selected>Select a Role</option>
              @foreach($roles as $role)
                <option value="{{ $role->name }}">{{ $role->name }}</option>
              @endforeach
            </select>
          </div>

          <!-- Warning (hidden by default) -->
          <div id="student-warning" class="hidden p-3 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800/80 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-900/50" role="alert">
            <div class="flex">
              <svg class="flex-shrink-0 inline w-4 h-4 me-2 mt-0.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
              </svg>
              <div>
                <span class="font-semibold">Notice:</span> Students cannot be assigned the <strong class="underline">Super Admin</strong> role. Selecting it will cause validation failure on submission.
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end space-x-2 mt-5 border-t border-gray-200 dark:border-gray-700 pt-4">
          <button type="button" class="py-2 px-3 text-sm font-medium text-gray-500 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-gray-900 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700 dark:hover:text-white dark:hover:bg-gray-700 shadow-sm" data-modal-hide="assign-role-modal">
            Cancel
          </button>
          <button type="submit" class="text-white bg-primary-500 hover:bg-primary-600 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 transition-all duration-200 shadow-sm">
            Confirm Promotion
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const assignButtons = document.querySelectorAll('.assign-role-btn');
    const modalRfid = document.getElementById('modal-admin-rfid');
    const modalName = document.getElementById('modal-user-name');
    const modalEmail = document.getElementById('modal-user-email');
    const modalAvatar = document.getElementById('modal-user-avatar');
    const modalBadge = document.getElementById('modal-user-badge');
    const studentWarning = document.getElementById('student-warning');
    const roleSelect = document.getElementById('modal-role');

    let currentSelectedUserType = '';

    assignButtons.forEach(button => {
      button.addEventListener('click', function() {
        const rfid = this.dataset.rfid;
        const name = this.dataset.name;
        const email = this.dataset.email;
        const category = this.dataset.category;
        const userType = this.dataset.userType;
        const initials = this.dataset.initials;
        const themeClass = this.dataset.themeClass;
        const badgeClass = this.dataset.badgeClass;

        currentSelectedUserType = userType;

        // Set inputs/values
        modalRfid.value = rfid;
        modalName.textContent = name;
        modalEmail.textContent = email;
        modalBadge.textContent = category;

        // Reset badge classes
        modalBadge.className = 'mt-1 inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-full ' + badgeClass;

        // Reset avatar
        modalAvatar.textContent = initials;
        modalAvatar.className = 'flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-full border font-bold text-sm ' + themeClass;

        // Reset dropdown choice & warning visibility
        roleSelect.value = '';
        checkSuperAdminWarning();
      });
    });

    roleSelect.addEventListener('change', checkSuperAdminWarning);

    function checkSuperAdminWarning() {
      if (currentSelectedUserType === 'student' && roleSelect.value === 'Super Admin') {
        studentWarning.classList.remove('hidden');
      } else {
        studentWarning.classList.add('hidden');
      }
    }
  });
</script>
@endsection