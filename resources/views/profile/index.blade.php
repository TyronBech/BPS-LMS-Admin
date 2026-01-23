@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Account Settings</h1>

  {{-- Profile Information Card --}}
  <div class="max-w-5xl mx-auto p-4 sm:p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 mb-8">
    <form id="profileForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PATCH')
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Left Column: Profile Image and Basic Info --}}
        <div class="lg:col-span-1 flex flex-col items-center text-center lg:border-r lg:border-gray-200 dark:lg:border-gray-700 lg:pr-8">

          <div class="relative mb-6 group">
            {{-- Image Preview --}}
            @if($user->profile_image === null)
            <img id="preview-image-dark" class="hidden rounded-full w-40 h-40 md:w-48 md:h-48 object-cover shadow-md dark:block" src="{{ asset('img/User-dark.png') }}" alt="Profile Image">
            <img id="preview-image-light" class="rounded-full w-40 h-40 md:w-48 md:h-48 object-cover shadow-md dark:hidden" src="{{ asset('img/User-light.png') }}" alt="Profile Image">
            @else
            <img id="preview-image-custom" class="rounded-full w-40 h-40 md:w-48 md:h-48 object-cover shadow-md" src="data:image/jpeg;base64, {{ $user->profile_image }}" alt="Profile Image">
            @endif

            {{-- Upload Label (Hidden by default, shown in Edit Mode) --}}
            <label id="image_upload_label" for="profile_image" class="hidden absolute bottom-2 right-2 bg-primary-500 hover:bg-primary-400 text-white p-2.5 rounded-full cursor-pointer shadow-lg border-4 border-white dark:border-gray-800 dark:bg-primary-400 dark:hover:bg-background-500 transition-transform hover:scale-110" title="Upload new photo">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              <input class="hidden" id="profile_image" name="profile_image" type="file" accept="image/jpeg, image/png, image/jpg, image/gif" onchange="previewFile()">
            </label>
          </div>

          @error('profile_image')
          <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
          @enderror

          <h5 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $user->first_name }} {{ $user->middle_name ?? '' }} {{ $user->last_name }}</h5>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            @if($user->privileges->user_type === 'student')
            Student
            @elseif($user->privileges->user_type === 'employee')
            {{ $user->employees->employee_role }}
            @else
            Visitor
            @endif
          </p>

          <div class="w-full max-w-xs space-y-3">
            @if($user->privileges->user_type === 'student')
            <div class="text-center">
              <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $user->students->id_number }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">ID Number</p>
            </div>
            @elseif($user->privileges->user_type === 'employee')
            <div class="text-center">
              <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $user->employees->employee_id }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Employee ID</p>
            </div>
            @endif
          </div>
        </div>

        {{-- Right Column: Form --}}
        <div class="lg:col-span-2">
            <div class="flex justify-between items-center mb-6">
                <h6 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">Personal Information</h6>
                
                {{-- Edit Button (Visible initially) --}}
                <button type="button" id="btn-edit-profile" onclick="toggleEditMode(true)" class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Edit Profile
                </button>
            </div>

          @if($user->privileges->user_type === 'student')
          <input type="hidden" name="user_id" value="{{ $user->students->student_id }}">
          @elseif($user->privileges->user_type === 'employee')
          <input type="hidden" name="user_id" value="{{ $user->employees->employee_id }}">
          @endif

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            
            {{-- First Name --}}
            <div class="relative z-0 w-full group">
                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">First Name</label>
                {{-- View Mode --}}
                <p class="view-field text-base font-medium text-gray-900 dark:text-white py-2 border-b border-transparent">{{ $user->first_name }}</p>
                {{-- Edit Mode --}}
                <input type="text" name="first_name" id="first_name" class="edit-field hidden py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-primary-500 focus:outline-none focus:ring-0 focus:border-primary-400" placeholder=" " value="{{ old('first_name', $user->first_name) }}" required />
                @error('first_name') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Middle Name --}}
            <div class="relative z-0 w-full group">
                 <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Middle Name</label>
                 {{-- View Mode --}}
                 <p class="view-field text-base font-medium text-gray-900 dark:text-white py-2 border-b border-transparent">{{ $user->middle_name ?? '-' }}</p>
                 {{-- Edit Mode --}}
                 <input type="text" name="middle_name" id="middle_name" class="edit-field hidden py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-primary-500 focus:outline-none focus:ring-0 focus:border-primary-400" placeholder=" " value="{{ old('middle_name', $user->middle_name) }}" />
                 @error('middle_name') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Last Name --}}
            <div class="relative z-0 w-full group">
                 <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Last Name</label>
                 {{-- View Mode --}}
                 <p class="view-field text-base font-medium text-gray-900 dark:text-white py-2 border-b border-transparent">{{ $user->last_name }}</p>
                 {{-- Edit Mode --}}
                 <input type="text" name="last_name" id="last_name" class="edit-field hidden py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-primary-500 focus:outline-none focus:ring-0 focus:border-primary-400" placeholder=" " value="{{ old('last_name', $user->last_name) }}" required />
                 @error('last_name') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Suffix --}}
            <div class="relative z-0 w-full group">
                 <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Suffix</label>
                 {{-- View Mode --}}
                 <p class="view-field text-base font-medium text-gray-900 dark:text-white py-2 border-b border-transparent">{{ $user->suffix ?? '-' }}</p>
                 {{-- Edit Mode --}}
                 <input type="text" name="suffix" id="suffix" class="edit-field hidden py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-primary-500 focus:outline-none focus:ring-0 focus:border-primary-400" placeholder=" " value="{{ old('suffix', $user->suffix) }}" />
                 @error('suffix') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div class="relative z-0 w-full sm:col-span-2 group">
                 <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Email Address</label>
                 {{-- View Mode --}}
                 <p class="view-field text-base font-medium text-gray-900 dark:text-white py-2 border-b border-transparent">{{ $user->email }}</p>
                 {{-- Edit Mode --}}
                 <input type="email" name="email" id="email" class="edit-field hidden py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-primary-500 focus:outline-none focus:ring-0 focus:border-primary-400" placeholder=" " value="{{ old('email', $user->email) }}" required />
                 @error('email') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Password SECTION --}}
            <div class="relative z-0 w-full sm:col-span-2 group pt-4 border-t border-gray-100 dark:border-gray-700">
                <h6 class="mb-4 text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Security</h6>

                {{-- View Mode: Hidden Password --}}
                <div id="password-view" class="view-field">
                    <div class="flex justify-between items-center">
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Password</label>
                            <p class="text-xl font-bold text-gray-900 dark:text-white tracking-widest">••••••••</p>
                        </div>
                    </div>
                </div>

                {{-- Edit Mode: Inputs --}}
                <div id="password-edit" class="hidden space-y-6">
                    {{-- Current Password --}}
                    <div class="relative z-0 w-full group">
                        <input type="password" name="current_password" id="current_password" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-primary-500 focus:outline-none focus:ring-0 focus:border-primary-400 peer pr-10" placeholder=" " autocomplete="current-password" />
                        <label for="current_password" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-primary-600 peer-focus:dark:text-primary-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Current Password</label>
                        <button type="button" id="toggleCurrentPassword" class="absolute right-0 top-2.5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none">
                            <span id="currentOpenEye" class="hidden"><svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" /><path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></span>
                            <span id="currentClosedEye"><svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></span>
                        </button>
                        @error('current_password') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- New Password --}}
                    <div class="relative z-0 w-full group">
                        <input type="password" name="new_password" id="new_password" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-primary-500 focus:outline-none focus:ring-0 focus:border-primary-400 peer pr-10" placeholder=" " autocomplete="new-password" />
                        <label for="new_password" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-primary-600 peer-focus:dark:text-primary-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">New Password</label>
                        <button type="button" id="toggleNewPassword" class="absolute right-0 top-2.5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none">
                            <span id="newOpenEye" class="hidden"><svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" /><path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></span>
                            <span id="newClosedEye"><svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></span>
                        </button>
                        @error('new_password') <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Confirm New Password --}}
                    <div class="relative z-0 w-full group">
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-primary-500 focus:outline-none focus:ring-0 focus:border-primary-400 peer pr-10" placeholder=" " autocomplete="new-password" />
                        <label for="new_password_confirmation" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-primary-600 peer-focus:dark:text-primary-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Confirm password</label>
                        <button type="button" id="toggleConfirmPassword" class="absolute right-0 top-2.5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none">
                            <span id="confirmOpenEye" class="hidden"><svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" /><path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></span>
                            <span id="confirmClosedEye"><svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></span>
                        </button>
                    </div>
                </div>
            </div>

          </div>

          {{-- Action Buttons --}}
          <div class="flex justify-end gap-3 mt-8">
            <button type="button" id="btn-cancel" onclick="toggleEditMode(false)" class="hidden text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">
                Cancel
            </button>
            <button type="submit" id="btn-save" class="hidden text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
                Save Changes
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>

  {{-- Two-Factor Authentication Card --}}
  <div class="max-w-5xl mx-auto p-6 sm:p-8 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
      <div class="flex-1">
        <div class="flex items-center gap-3 mb-3">
          <div class="p-3 bg-primary-100 dark:bg-primary-900 rounded-lg">
            <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>
          <h6 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Two-Factor Authentication</h6>
        </div>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 leading-relaxed">
          Strengthen your account security by adding an additional verification step. When enabled, you'll need to enter a code from your authentication app along with your password.
        </p>

        <div class="flex flex-wrap items-center gap-4">
          @if($user->two_factor_enabled ?? false)
          <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gradient-to-r from-green-100 to-green-200 text-green-800 dark:from-green-900 dark:to-green-800 dark:text-green-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Active & Protected</span>
          </div>
          <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
            </svg>
            <span>Activated on {{ $user->updated_at->format('M d, Y') }}</span>
          </div>
          @else
          <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gradient-to-r from-amber-100 to-amber-200 text-amber-800 dark:from-amber-900 dark:to-amber-800 dark:text-amber-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>Not Configured</span>
          </div>
          <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <span>Recommended for enhanced security</span>
          </div>
          @endif
        </div>
      </div>

      <div class="flex-shrink-0">
        @if($user->two_factor_enabled ?? false)
        <button type="button" onclick="openTwoFactorModal('disable')" class="group relative inline-flex items-center justify-center px-6 py-3 text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-semibold rounded-lg text-sm transition-all duration-300 shadow-md hover:shadow-lg dark:from-red-600 dark:to-red-700 dark:hover:from-red-700 dark:hover:to-red-800 dark:focus:ring-red-800">
          <svg class="w-5 h-5 mr-2 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          <span>Disable 2FA</span>
        </button>
        @else
        <button type="button" onclick="openTwoFactorModal('enable')" class="group relative inline-flex items-center justify-center px-6 py-3 text-white bg-gradient-to-r from-primary-500 to-primary-400 hover:from-primary-400 hover:to-primary-500 focus:ring-4 focus:outline-none focus:ring-primary-400 font-semibold rounded-lg text-sm transition-all duration-300 shadow-md hover:shadow-lg dark:from-primary-400 dark:to-primary-500 dark:hover:from-primary-500 dark:hover:to-primary-400 dark:focus:ring-primary-500">
          <svg class="w-5 h-5 mr-2 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
          <span>Enable 2FA</span>
        </button>
        @endif
      </div>
    </div>

    @if(!($user->two_factor_enabled ?? false))
    <div class="mt-6 p-4 bg-secondary-500 dark:bg-primary-900/20 border-l-4 border-primary-500 rounded-r-lg">
      <div class="flex items-start">
        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
        </svg>
        <div class="ml-3">
          <p class="text-sm font-medium text-primary-800 dark:text-primary-300">Security Tip</p>
          <p class="text-xs text-primary-700 dark:text-primary-400 mt-1">Enable 2FA to add an extra layer of protection to your account. You'll need an authenticator app like Google Authenticator or Microsoft Authenticator.</p>
        </div>
      </div>
    </div>
    @endif
  </div>
</div>

{{-- Two-Factor Authentication Modal --}}
<div id="twoFactorModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-900 bg-opacity-50">
  <div class="relative p-4 w-full max-w-md max-h-full mx-auto mt-20">
    {{-- Modal content --}}
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
      {{-- Modal header --}}
      <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white" id="modalTitle">
          Confirm Password
        </h3>
        <button type="button" onclick="closeTwoFactorModal()" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
          <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span class="sr-only">Close modal</span>
        </button>
      </div>
      {{-- Modal body --}}
      <form id="twoFactorForm" action="" method="POST">
        @csrf
        @method('PATCH')
        <div class="p-4 md:p-5 space-y-4">
          <p class="text-sm text-gray-500 dark:text-gray-400" id="modalDescription">
            Please enter your password to continue.
          </p>
          <div class="relative z-0 w-full group">
            <input type="password" name="password" id="modal_password" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-primary-500 focus:outline-none focus:ring-0 focus:border-primary-400 peer pr-10" placeholder=" " required autocomplete="current-password" />
            <label for="modal_password" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-primary-600 peer-focus:dark:text-primary-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Password</label>
            
            <button type="button" id="toggleModalPassword" class="absolute right-0 top-2.5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none" aria-label="Toggle password visibility">
                <span id="modalOpenEye" class="hidden">
                  <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-width="2" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" />
                    <path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  </svg>
                </span>
                <span id="modalClosedEye">
                  <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  </svg>
                </span>
            </button>
          </div>
          <div id="modalError" class="hidden p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            <span class="font-medium">Error!</span> <span id="errorMessage"></span>
          </div>
        </div>
        {{-- Modal footer --}}
        <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
          <button type="submit" id="modalSubmitBtn" class="text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
            Confirm
          </button>
          <button type="button" onclick="closeTwoFactorModal()" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-50 dark:border-gray-600 dark:hover:text-gray-50 dark:hover:bg-gray-700">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Toggle View/Edit Mode
  function toggleEditMode(enable) {
      const viewFields = document.querySelectorAll('.view-field');
      const editFields = document.querySelectorAll('.edit-field');
      const passwordView = document.getElementById('password-view');
      const passwordEdit = document.getElementById('password-edit');
      const btnEdit = document.getElementById('btn-edit-profile');
      const btnSave = document.getElementById('btn-save');
      const btnCancel = document.getElementById('btn-cancel');
      const imageUploadLabel = document.getElementById('image_upload_label');

      if (enable) {
          // Switch to Edit Mode
          viewFields.forEach(el => el.classList.add('hidden'));
          editFields.forEach(el => el.classList.remove('hidden'));
          
          passwordView.classList.add('hidden');
          passwordEdit.classList.remove('hidden');

          btnEdit.classList.add('hidden');
          btnSave.classList.remove('hidden');
          btnCancel.classList.remove('hidden');
          imageUploadLabel.classList.remove('hidden');
      } else {
          // Switch to View Mode
          viewFields.forEach(el => el.classList.remove('hidden'));
          editFields.forEach(el => el.classList.add('hidden'));

          passwordView.classList.remove('hidden');
          passwordEdit.classList.add('hidden');

          btnEdit.classList.remove('hidden');
          btnSave.classList.add('hidden');
          btnCancel.classList.add('hidden');
          imageUploadLabel.classList.add('hidden');

          // Optional: Reset form values on Cancel
          document.getElementById('profileForm').reset();
      }
  }

  function previewFile() {
    const previewDark = document.getElementById('preview-image-dark');
    const previewLight = document.getElementById('preview-image-light');
    const previewCustom = document.getElementById('preview-image-custom');
    const file = document.querySelector('input[type=file]').files[0];
    const reader = new FileReader();

    reader.addEventListener("load", function() {
      if (previewDark) previewDark.src = reader.result;
      if (previewLight) previewLight.src = reader.result;
      if (previewCustom) previewCustom.src = reader.result;
    }, false);

    if (file) {
      reader.readAsDataURL(file);
    }
  }

  function openTwoFactorModal(action) {
    const modal = document.getElementById('twoFactorModal');
    const form = document.getElementById('twoFactorForm');
    const title = document.getElementById('modalTitle');
    const description = document.getElementById('modalDescription');
    const submitBtn = document.getElementById('modalSubmitBtn');
    const passwordInput = document.getElementById('modal_password');
    const errorDiv = document.getElementById('modalError');

    // Reset form and hide errors
    form.reset();
    errorDiv.classList.add('hidden');

    // Reset password visibility
    passwordInput.type = 'password';
    document.getElementById('modalOpenEye').classList.add('hidden');
    document.getElementById('modalClosedEye').classList.remove('hidden');

    if (action === 'enable') {
      title.textContent = 'Enable Two-Factor Authentication';
      description.textContent = 'Please enter your password to enable two-factor authentication.';
      submitBtn.textContent = 'Enable 2FA';
      submitBtn.classList.remove('bg-red-600', 'hover:bg-red-700', 'focus:ring-red-300', 'dark:bg-red-600', 'dark:hover:bg-red-700', 'dark:focus:ring-red-800');
      submitBtn.classList.add('bg-primary-500', 'hover:bg-primary-400', 'focus:ring-primary-400', 'dark:bg-primary-400', 'dark:hover:bg-primary-500', 'dark:focus:ring-primary-500');
      form.action = "{{ route('profile.2fa.enable') }}";
    } else {
      title.textContent = 'Disable Two-Factor Authentication';
      description.textContent = 'Please enter your password to disable two-factor authentication.';
      submitBtn.textContent = 'Disable 2FA';
      submitBtn.classList.remove('bg-primary-500', 'hover:bg-primary-400', 'focus:ring-primary-400', 'dark:bg-primary-400', 'dark:hover:bg-primary-500', 'dark:focus:ring-primary-500');
      submitBtn.classList.add('bg-red-600', 'hover:bg-red-700', 'focus:ring-red-300', 'dark:bg-red-600', 'dark:hover:bg-red-700', 'dark:focus:ring-red-800');
      form.action = "{{ route('profile.2fa.disable') }}";
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    passwordInput.focus();
  }

  function closeTwoFactorModal() {
    const modal = document.getElementById('twoFactorModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  // Close modal on outside click
  document.getElementById('twoFactorModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
      closeTwoFactorModal();
    }
  });

  // Close modal on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeTwoFactorModal();
    }
  });

  // Toggle Password Visibility for Modal
  const toggleModalPassword = document.getElementById('toggleModalPassword');
  const modalPasswordInput = document.getElementById('modal_password');
  const modalOpenEye = document.getElementById('modalOpenEye');
  const modalClosedEye = document.getElementById('modalClosedEye');

  if (toggleModalPassword) {
      toggleModalPassword.addEventListener('click', function() {
        const isPassword = modalPasswordInput.type === 'password';
        modalPasswordInput.type = isPassword ? 'text' : 'password';

        // Toggle icons
        modalOpenEye.classList.toggle('hidden', !isPassword);
        modalClosedEye.classList.toggle('hidden', isPassword);
      });
  }

  // Helper function for password toggles
  function setupPasswordToggle(toggleId, inputId, openEyeId, closedEyeId) {
    const toggleBtn = document.getElementById(toggleId);
    const input = document.getElementById(inputId);
    const openEye = document.getElementById(openEyeId);
    const closedEye = document.getElementById(closedEyeId);

    if (toggleBtn && input && openEye && closedEye) {
      toggleBtn.addEventListener('click', function() {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        openEye.classList.toggle('hidden', !isPassword);
        closedEye.classList.toggle('hidden', isPassword);
      });
    }
  }

  // Initialize toggles for profile form
  setupPasswordToggle('toggleCurrentPassword', 'current_password', 'currentOpenEye', 'currentClosedEye');
  setupPasswordToggle('toggleNewPassword', 'new_password', 'newOpenEye', 'newClosedEye');
  setupPasswordToggle('toggleConfirmPassword', 'new_password_confirmation', 'confirmOpenEye', 'confirmClosedEye');
</script>
@endsection