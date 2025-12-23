@extends('layouts.admin-app')

@section('content')
<div class="container mx-auto px-4 py-8">
  <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">System Settings</h1>

  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <form action="{{ route('settings.update-ui-settings') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <!-- Organization Details Section -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4 border-b dark:border-gray-700 pb-2">Organization Details</h2>
        <div class="grid grid-cols-1 gap-6">
          <!-- Organization Name -->
          <div>
            <label for="org_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Organization Name</label>
            <input type="text" name="org_name" id="org_name"
              value="{{ old('org_name', $settings->org_name) }}"
              class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="e.g. BPS Library">
            @error('org_name')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Organization Address -->
          <div>
            <label for="org_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Organization Address</label>
            <textarea name="org_address" id="org_address" rows="3"
              class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="e.g. 123 Main St, City, Country">{{ old('org_address', $settings->org_address) }}</textarea>
            @error('org_address')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Organization Logos -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Organization Logo (Icon) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Organization Logo (Icon)</label>
              <div class="flex flex-col items-center justify-center w-full">
                <label for="org_logo" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600 transition duration-300 ease-in-out relative overflow-hidden group">
                  @if($settings->org_logo)
                  <img src="{{ asset('storage/' . $settings->org_logo) }}" alt="Current Logo" class="absolute inset-0 w-full h-full object-contain p-4 opacity-50 group-hover:opacity-30 transition-opacity">
                  @endif
                  <div class="flex flex-col items-center justify-center pt-5 pb-6 z-10">
                    <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                    </svg>
                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG or GIF (MAX. 2MB)</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center px-4">This image should only contain the organization's logo itself.</p>
                  </div>
                  <input id="org_logo" name="org_logo" type="file" class="hidden" accept="image/*" />
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
                <label for="org_logo_full" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600 transition duration-300 ease-in-out relative overflow-hidden group">
                  @if($settings->org_logo_full)
                  <img src="{{ asset('storage/' . $settings->org_logo_full) }}" alt="Current Logo Full" class="absolute inset-0 w-full h-full object-contain p-4 opacity-50 group-hover:opacity-30 transition-opacity">
                  @endif
                  <div class="flex flex-col items-center justify-center pt-5 pb-6 z-10">
                    <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                    </svg>
                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG or GIF (MAX. 2MB)</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center px-4">This image should contain the full organization logo including text.</p>
                  </div>
                  <input id="org_logo_full" name="org_logo_full" type="file" class="hidden" accept="image/*" />
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
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4 border-b dark:border-gray-700 pb-2">Social Links</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Facebook -->
          <div>
            <label for="facebook" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Facebook</label>
            <input type="url" name="social_links[facebook]" id="facebook"
              value="{{ old('social_links.facebook', $settings->social_links['facebook'] ?? '') }}"
              class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="https://facebook.com/yourpage">
            @error('social_links.facebook')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Instagram -->
          <div>
            <label for="instagram" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instagram</label>
            <input type="url" name="social_links[instagram]" id="instagram"
              value="{{ old('social_links.instagram', $settings->social_links['instagram'] ?? '') }}"
              class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-pink-500 focus:ring-pink-500"
              placeholder="https://instagram.com/yourpage">
            @error('social_links.instagram')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Twitter (X) -->
          <div>
            <label for="twitter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Twitter (X)</label>
            <input type="url" name="social_links[twitter]" id="twitter"
              value="{{ old('social_links.twitter', $settings->social_links['twitter'] ?? '') }}"
              class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-black focus:ring-black"
              placeholder="https://twitter.com/yourhandle">
            @error('social_links.twitter')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- YouTube -->
          <div>
            <label for="youtube" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">YouTube</label>
            <input type="url" name="social_links[youtube]" id="youtube"
              value="{{ old('social_links.youtube', $settings->social_links['youtube'] ?? '') }}"
              class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-red-500 focus:ring-red-500"
              placeholder="https://youtube.com/yourchannel">
            @error('social_links.youtube')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Official Website -->
          <div class="md:col-span-2">
            <label for="website" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Official Website</label>
            <input type="url" name="social_links[website]" id="website"
              value="{{ old('social_links.website', $settings->social_links['website'] ?? '') }}"
              class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              placeholder="https://yourwebsite.com">
            @error('social_links.website')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>
      </div>

      <!-- Theme Colors Section -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4 border-b dark:border-gray-700 pb-2">Theme Colors</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Primary Color -->
          <div>
            <label for="primary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Primary Color</label>
            <div class="flex items-center space-x-2">
              <input type="color" name="theme_colors[primary]" id="primary_color"
                value="{{ old('theme_colors.primary', $settings->theme_colors['primary'] ?? '#3B82F6') }}"
                class="h-10 w-20 p-1 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 cursor-pointer">
              <span class="text-sm text-gray-500 dark:text-gray-400">Mostly used for headers, active states, and primary buttons.</span>
            </div>
            @error('theme_colors.primary')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Secondary Color -->
          <div>
            <label for="secondary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Secondary Color</label>
            <div class="flex items-center space-x-2">
              <input type="color" name="theme_colors[secondary]" id="secondary_color"
                value="{{ old('theme_colors.secondary', $settings->theme_colors['secondary'] ?? '#10B981') }}"
                class="h-10 w-20 p-1 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 cursor-pointer">
              <span class="text-sm text-gray-500 dark:text-gray-400">Used for secondary buttons, accents, and important highlights.</span>
            </div>
            @error('theme_colors.secondary')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Tertiary Color -->
          <div>
            <label for="tertiary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tertiary Color</label>
            <div class="flex items-center space-x-2">
              <input type="color" name="theme_colors[tertiary]" id="tertiary_color"
                value="{{ old('theme_colors.tertiary', $settings->theme_colors['tertiary'] ?? '#F59E0B') }}"
                class="h-10 w-20 p-1 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 cursor-pointer">
              <span class="text-sm text-gray-500 dark:text-gray-400">Used for subtle details, borders, or background accents.</span>
            </div>
            @error('theme_colors.tertiary')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow transition duration-150 ease-in-out dark:bg-blue-600 dark:hover:bg-blue-700">
          Save Settings
        </button>
      </div>
    </form>
  </div>
</div>
@endsection