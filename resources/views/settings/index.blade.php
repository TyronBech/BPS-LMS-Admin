@extends('layouts.admin-app')

@section('content')
<div class="container mx-auto px-4">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">System Settings</h1>

  <div class="bg-white dark:bg-gray-800 rounded-lg p-6 relative shadow-md">
    <!-- Edit Button -->
    <button data-modal-target="edit-settings-modal" data-modal-toggle="edit-settings-modal" class="absolute top-4 right-4 bg-primary-500 hover:bg-primary-400 text-white font-semibold py-2 px-3 sm:px-4 rounded-lg shadow transition duration-150 ease-in-out flex items-center dark:bg-primary-400 dark:hover:bg-primary-500 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:focus:ring-primary-500">
      <svg class="w-5 h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
      </svg>
      <span class="hidden sm:inline">Edit Settings</span>
    </button>

    <!-- Organization Details Section -->
    <div class="mb-8 mt-16 sm:mt-12">
      <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4 border-b dark:border-gray-700 pb-2">Organization Details</h2>
      <div class="grid grid-cols-1 gap-6">
        <!-- Organization Name -->
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Organization Name</label>
          <p class="text-gray-800 dark:text-white">{{ $settings->org_name ?? 'Not set' }}</p>
        </div>

        <!-- Organization Initial -->
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Organization Initial</label>
          <p class="text-gray-800 dark:text-white">{{ $settings->org_initial ?? 'Not set' }}</p>
        </div>

        <!-- Organization Address -->
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Organization Address</label>
          <p class="text-gray-800 dark:text-white">{{ $settings->org_address ?? 'Not set' }}</p>
        </div>

        <!-- Contact Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Email Address</label>
            <p class="text-gray-800 dark:text-white">{{ $settings->email ?? 'Not set' }}</p>
          </div>

          <!-- Contact Number -->
          <div>
            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Contact Number</label>
            <p class="text-gray-800 dark:text-white">{{ $settings->contact_number ?? 'Not set' }}</p>
          </div>
        </div>

        <!-- Organization Logos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Organization Logo (Icon) -->
          <div>
            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Organization Logo (Icon)</label>
            <div class="flex items-center justify-center w-full h-64 border-2 border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700">
              @if($settings->org_logo)
                <img src="{{ $settings->org_logo_base64 }}" alt="Organization Logo" class="max-h-full max-w-full object-contain p-4">
              @else
                <p class="text-gray-400 dark:text-gray-500">No logo uploaded</p>
              @endif
            </div>
          </div>

          <!-- Organization Logo Full -->
          <div>
            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Organization Logo Full</label>
            <div class="flex items-center justify-center w-full h-64 border-2 border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700">
              @if($settings->org_logo_full)
                <img src="{{ $settings->org_logo_full_base64 }}" alt="Organization Logo Full" class="max-h-full max-w-full object-contain p-4">
              @else
                <p class="text-gray-400 dark:text-gray-500">No logo uploaded</p>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Social Links Section -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4 border-b dark:border-gray-700 pb-2">Social Links</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Facebook -->
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Facebook</label>
          <p class="text-gray-800 dark:text-white break-all">{{ ($settings->social_links ?? [])['facebook'] ?? 'Not set' }}</p>
        </div>

        <!-- Instagram -->
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Instagram</label>
          <p class="text-gray-800 dark:text-white break-all">{{ ($settings->social_links ?? [])['instagram'] ?? 'Not set' }}</p>
        </div>

        <!-- Twitter (X) -->
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Twitter (X)</label>
          <p class="text-gray-800 dark:text-white break-all">{{ ($settings->social_links ?? [])['twitter'] ?? 'Not set' }}</p>
        </div>

        <!-- YouTube -->
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">YouTube</label>
          <p class="text-gray-800 dark:text-white break-all">{{ ($settings->social_links ?? [])['youtube'] ?? 'Not set' }}</p>
        </div>

        <!-- Official Website -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Official Website</label>
          <p class="text-gray-800 dark:text-white break-all">{{ ($settings->social_links ?? [])['website'] ?? 'Not set' }}</p>
        </div>
      </div>
    </div>

    <!-- Theme Colors Section -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4 border-b dark:border-gray-700 pb-2">Theme Colors</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Primary Color -->
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Primary Color</label>
          <div class="flex items-center space-x-3">
            <div class="h-10 w-10 bg-primary-500 rounded border border-gray-300 dark:border-gray-600"></div>
            <span class="text-gray-800 dark:text-white font-mono">{{ ($settings->theme_colors ?? [])['primary'] ?? '#3B82F6' }}</span>
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Used for headers, active states, and primary buttons</p>
        </div>

        <!-- Secondary Color -->
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Secondary Color</label>
          <div class="flex items-center space-x-3">
            <div class="h-10 w-10 bg-secondary-500 rounded border border-gray-300 dark:border-gray-600"></div>
            <span class="text-gray-800 dark:text-white font-mono">{{ ($settings->theme_colors ?? [])['secondary'] ?? '#6366F1' }}</span>
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Used for background accents</p>
        </div>

        <!-- Tertiary Color -->
        <div>
          <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Tertiary Color</label>
          <div class="flex items-center space-x-3">
            <div class="h-10 w-10 bg-tertiary-500 rounded border border-gray-300 dark:border-gray-600"></div>
            <span class="text-gray-800 dark:text-white font-mono">{{ ($settings->theme_colors ?? [])['tertiary'] ?? '#F59E0B' }}</span>
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Used for buttons, hover, and other accents</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div id="edit-settings-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-2 sm:p-4 w-full max-w-full sm:max-w-4xl max-h-full">
      <!-- Modal content -->
      <div class="relative bg-white rounded-lg dark:bg-gray-700 shadow-md">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-3 sm:p-4 md:p-5 border-b rounded-t dark:border-gray-600">
          <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white">
            Edit System Settings
          </h3>
          <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="edit-settings-modal">
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
            </svg>
            <span class="sr-only">Close modal</span>
          </button>
        </div>
        <!-- Modal body -->
        <form action="{{ route('settings.update-ui-settings') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="p-3 sm:p-4 md:p-5 space-y-3 sm:space-y-4 max-h-[65vh] sm:max-h-[60vh] overflow-y-auto">
            <!-- Organization Details Section -->
            <div class="mb-4 sm:mb-6">
              <h4 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-white mb-2 sm:mb-3">Organization Details</h4>
              <div class="grid grid-cols-1 gap-4">
                <!-- Organization Name -->
                <div>
                  <label for="org_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Organization Name</label>
                  <input type="text" name="org_name" id="org_name"
                    value="{{ old('org_name', $settings->org_name) }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-primary-400 focus:ring-primary-400 dark:focus:border-primary-500 dark:focus:ring-primary-500"
                    placeholder="e.g. {{ $settings->org_initial ?? 'BPS' }} Library">
                  @error('org_name')
                  <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                  @enderror
                </div>

                <!-- Organization Initial -->
                <div>
                  <label for="org_initial" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Organization Initial</label>
                  <input type="text" name="org_initial" id="org_initial"
                    value="{{ old('org_initial', $settings->org_initial) }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-primary-400 focus:ring-primary-400 dark:focus:border-primary-500 dark:focus:ring-primary-500"
                    placeholder="e.g. {{ $settings->org_initial ?? 'BPS' }}">
                  @error('org_initial')
                  <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                  @enderror
                </div>

                <!-- Organization Address -->
                <div>
                  <label for="org_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Organization Address</label>
                  <textarea name="org_address" id="org_address" rows="2"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-primary-400 focus:ring-primary-400 dark:focus:border-primary-500 dark:focus:ring-primary-500"
                    placeholder="e.g. 123 Main St, City, Country">{{ old('org_address', $settings->org_address) }}</textarea>
                  @error('org_address')
                  <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                  @enderror
                </div>

                <!-- Contact Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Email -->
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                    <input type="email" name="email" id="email"
                      value="{{ old('email', $settings->email) }}"
                      class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-primary-400 focus:ring-primary-400 dark:focus:border-primary-500 dark:focus:ring-primary-500"
                      placeholder="e.g. info@bps.edu.ph">
                    @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                  </div>

                  <!-- Contact Number -->
                  <div>
                    <label for="contact_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact Number</label>
                    <input type="text" name="contact_number" id="contact_number"
                      value="{{ old('contact_number', $settings->contact_number) }}"
                      class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-primary-400 focus:ring-primary-400 dark:focus:border-primary-500 dark:focus:ring-primary-500"
                      placeholder="e.g. +63 912 345 6789">
                    @error('contact_number')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                  </div>
                </div>

                <!-- Organization Logos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Organization Logo (Icon) -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Organization Logo (Icon)</label>
                    <div class="flex flex-col items-center justify-center w-full">
                      <label id="dropzone_org_logo" for="org_logo" class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-500 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-800 transition duration-300 ease-in-out relative overflow-hidden group">
                        <img id="preview_org_logo" src="{{ $settings->org_logo_base64 ?? '' }}" alt="Current Logo" class="absolute inset-0 w-full h-full object-contain p-4 transition-all duration-300 {{ $settings->org_logo ? 'opacity-100 group-hover:blur-sm group-hover:brightness-50' : 'hidden' }}">
                        <div id="text_org_logo" class="flex flex-col items-center justify-center pt-3 pb-3 z-10 transition-opacity duration-300 {{ $settings->org_logo ? 'opacity-0 group-hover:opacity-100' : 'opacity-100' }}">
                          <svg class="w-6 h-6 mb-2 text-white dark:text-gray-100" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                          </svg>
                          <p class="mb-1 text-xs text-white dark:text-gray-100"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                          <p class="text-xs text-white dark:text-gray-100">PNG only (MAX. 5MB)</p>
                          <p class="text-xs text-white dark:text-gray-100 mt-1 text-center px-2">This image should only contain the organization's logo itself.</p>
                          <p class="text-xs text-yellow-300 dark:text-yellow-200 mt-1 text-center px-2 font-semibold">⚠️ Please use a PNG with transparent background</p>
                          <p class="text-xs text-primary-100 dark:text-primary-100 mt-1 text-center px-2 font-semibold">📐 Recommended: 1:1 aspect ratio (square image)</p>
                          <p class="text-xs text-primary-100 dark:text-primary-100 mt-1 text-center px-2 font-semibold">🖥️ Usage: This image is used for the website header and favicon.</p>
                        </div>
                        <input id="org_logo" name="org_logo" type="file" class="hidden" accept="image/png" onchange="handleFileSelect(this, 'preview_org_logo', 'text_org_logo')" />
                      </label>
                    </div>
                    @error('org_logo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                  </div>

                  <!-- Organization Logo Full -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Organization Logo Full</label>
                    <div class="flex flex-col items-center justify-center w-full">
                      <label id="dropzone_org_logo_full" for="org_logo_full" class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-500 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-800 transition duration-300 ease-in-out relative overflow-hidden group">
                        <img id="preview_org_logo_full" src="{{ $settings->org_logo_full_base64 ?? '' }}" alt="Current Logo Full" class="absolute inset-0 w-full h-full object-contain p-4 transition-all duration-300 {{ $settings->org_logo_full ? 'opacity-100 group-hover:blur-sm group-hover:brightness-50' : 'hidden' }}">
                        <div id="text_org_logo_full" class="flex flex-col items-center justify-center pt-3 pb-3 z-10 transition-opacity duration-300 {{ $settings->org_logo_full ? 'opacity-0 group-hover:opacity-100' : 'opacity-100' }}">
                          <svg class="w-6 h-6 mb-2 text-white dark:text-gray-100" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                          </svg>
                          <p class="mb-1 text-xs text-white dark:text-gray-100"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                          <p class="text-xs text-white dark:text-gray-100">PNG only (MAX. 5MB)</p>
                          <p class="text-xs text-white dark:text-gray-100 mt-1 text-center px-2">This image should contain the full organization logo including text.</p>
                          <p class="text-xs text-yellow-300 dark:text-yellow-200 mt-1 text-center px-2 font-semibold">⚠️ Please use a PNG with transparent background</p>
                          <p class="text-xs text-primary-100 dark:text-primary-100 mt-1 text-center px-2 font-semibold">📐 Recommended: Banner-style (wide, horizontal image)</p>
                          <p class="text-xs text-primary-100 dark:text-primary-100 mt-1 text-center px-2 font-semibold">📃 Usage: This image is used for reports' headers and other official documents.</p>
                        </div>
                        <input id="org_logo_full" name="org_logo_full" type="file" class="hidden" accept="image/png" onchange="handleFileSelect(this, 'preview_org_logo_full', 'text_org_logo_full')" />
                      </label>
                    </div>
                    @error('org_logo_full')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                  </div>
                </div>
              </div>
            </div>

            <!-- Social Links Section -->
            <div class="mb-4 sm:mb-6">
              <h4 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-white mb-2 sm:mb-3">Social Links</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Facebook -->
                <div>
                  <label for="facebook" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Facebook</label>
                  <input type="url" name="facebook" id="facebook"
                    value="{{ old('facebook', ($settings->social_links ?? [])['facebook'] ?? '') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-primary-400 focus:ring-primary-400 dark:focus:border-primary-500 dark:focus:ring-primary-500"
                    placeholder="https://facebook.com/yourpage">
                  @error('facebook')
                  <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                  @enderror
                </div>

                <!-- Instagram -->
                <div>
                  <label for="instagram" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instagram</label>
                  <input type="url" name="instagram" id="instagram"
                    value="{{ old('instagram', ($settings->social_links ?? [])['instagram'] ?? '') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                    placeholder="https://instagram.com/yourpage">
                  @error('instagram')
                  <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                  @enderror
                </div>

                <!-- Twitter (X) -->
                <div>
                  <label for="twitter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Twitter (X)</label>
                  <input type="url" name="twitter" id="twitter"
                    value="{{ old('twitter', ($settings->social_links ?? [])['twitter'] ?? '') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-black focus:ring-black"
                    placeholder="https://twitter.com/yourhandle">
                  @error('twitter')
                  <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                  @enderror
                </div>

                <!-- YouTube -->
                <div>
                  <label for="youtube" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">YouTube</label>
                  <input type="url" name="youtube" id="youtube"
                    value="{{ old('youtube', ($settings->social_links ?? [])['youtube'] ?? '') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-red-500 focus:ring-red-500"
                    placeholder="https://youtube.com/yourchannel">
                  @error('youtube')
                  <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                  @enderror
                </div>

                <!-- Official Website -->
                <div class="md:col-span-2">
                  <label for="website" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Official Website</label>
                  <input type="url" name="website" id="website"
                    value="{{ old('website', ($settings->social_links ?? [])['website'] ?? '') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="https://yourwebsite.com">
                  @error('website')
                  <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                  @enderror
                </div>
              </div>
            </div>

            <!-- Theme Colors Section -->
            <div class="mb-4 sm:mb-6">
              <h4 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-white mb-2 sm:mb-3">Theme Colors</h4>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Primary Color -->
                <div>
                  <label for="primary" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Primary Color</label>
                  <div class="flex items-center space-x-2">
                    <input type="color" name="primary" id="primary"
                      value="{{ old('primary', ($settings->theme_colors ?? [])['primary'] ?? '#3B82F6') }}"
                      class="h-10 w-20 p-1 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 cursor-pointer">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Headers & buttons</span>
                  </div>
                  @error('primary')
                  <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                  @enderror
                </div>

                <!-- Secondary Color -->
                <div>
                  <label for="secondary" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Secondary Color</label>
                  <div class="flex items-center space-x-2">
                    <input type="color" name="secondary" id="secondary"
                      value="{{ old('secondary', ($settings->theme_colors ?? [])['secondary'] ?? '#6366F1') }}"
                      class="h-10 w-20 p-1 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 cursor-pointer">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Background accents</span>
                  </div>
                  @error('secondary')
                  <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                  @enderror
                </div>

                <!-- Tertiary Color -->
                <div>
                  <label for="tertiary" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tertiary Color</label>
                  <div class="flex items-center space-x-2">
                    <input type="color" name="tertiary" id="tertiary"
                      value="{{ old('tertiary', ($settings->theme_colors ?? [])['tertiary'] ?? '#F59E0B') }}"
                      class="h-10 w-20 p-1 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 cursor-pointer">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Hover & accents</span>
                  </div>
                  @error('tertiary')
                  <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                  @enderror
                </div>
              </div>
            </div>

            <!-- Modal Actions -->
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-3 sm:pt-4 border-t dark:border-gray-700">
              <button data-modal-hide="edit-settings-modal" type="button" class="skip-loader w-full sm:w-auto py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-500 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-50 dark:border-gray-600 dark:hover:text-gray-50 dark:hover:bg-gray-700 shadow-md">Cancel</button>
              <button type="submit" class="w-full sm:w-auto text-white bg-primary-500 hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">Save Settings</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  // File upload functionality
  function handleFileSelect(input, previewId, textContainerId) {
    const preview = document.getElementById(previewId);
    const textContainer = document.getElementById(textContainerId);
    const file = input.files[0];

    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        preview.classList.add('opacity-100', 'group-hover:blur-sm', 'group-hover:brightness-50');

        textContainer.classList.remove('hidden');
        textContainer.classList.remove('opacity-100');
        textContainer.classList.add('opacity-0', 'group-hover:opacity-100');
      }
      reader.readAsDataURL(file);
    }
  }

  function setupDragAndDrop(dropzoneId, inputId, previewId, textId) {
    const dropzone = document.getElementById(dropzoneId);
    const input = document.getElementById(inputId);

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      dropzone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
      dropzone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropzone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
      dropzone.classList.add('border-primary-500', 'bg-gray-100', 'dark:bg-gray-600');
    }

    function unhighlight(e) {
      dropzone.classList.remove('border-primary-500', 'bg-gray-100', 'dark:bg-gray-600');
    }

    dropzone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
      const dt = e.dataTransfer;
      const files = dt.files;

      if (files.length > 0) {
        input.files = files;
        handleFileSelect(input, previewId, textId);
      }
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    setupDragAndDrop('dropzone_org_logo', 'org_logo', 'preview_org_logo', 'text_org_logo');
    setupDragAndDrop('dropzone_org_logo_full', 'org_logo_full', 'preview_org_logo_full', 'text_org_logo_full');
  });
</script>
@endsection
