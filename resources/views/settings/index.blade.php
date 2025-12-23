@extends('layouts.admin-app')

@section('content')
<div class="container mx-auto px-4 py-8">
  <h1 class="text-3xl font-bold text-gray-800 mb-6">System Settings</h1>

  <div class="bg-white rounded-lg shadow-md p-6">
    <form action="{{ route('settings.update-ui-settings') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <!-- Organization Details Section -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Organization Details</h2>
        <div class="grid grid-cols-1 gap-6">
          <!-- Organization Name -->
          <div>
            <label for="org_name" class="block text-sm font-medium text-gray-700 mb-1">Organization Name</label>
            <input type="text" name="org_name" id="org_name"
              value="{{ old('org_name', $settings->org_name) }}"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="e.g. BPS Library">
            @error('org_name')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Organization Address -->
          <div>
            <label for="org_address" class="block text-sm font-medium text-gray-700 mb-1">Organization Address</label>
            <textarea name="org_address" id="org_address" rows="3"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="e.g. 123 Main St, City, Country">{{ old('org_address', $settings->org_address) }}</textarea>
            @error('org_address')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Organization Logo -->
          <div>
            <label for="org_logo" class="block text-sm font-medium text-gray-700 mb-1">Organization Logo</label>
            <div class="flex items-center space-x-4">
              @if($settings->org_logo)
              <div class="shrink-0">
                <img src="{{ asset('storage/' . $settings->org_logo) }}" alt="Current Logo" class="h-16 w-16 object-contain border rounded p-1">
              </div>
              @endif
              <div class="flex-1">
                <input type="file" name="org_logo" id="org_logo" accept="image/*"
                  class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100">
                <p class="text-xs text-gray-500 mt-1">Recommended size: 200x200px. Max size: 2MB.</p>
              </div>
            </div>
            @error('org_logo')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>
      </div>

      <!-- Social Links Section -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Social Links</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Facebook -->
          <div>
            <label for="facebook" class="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
            <input type="url" name="social_links[facebook]" id="facebook"
              value="{{ old('social_links.facebook', $settings->social_links['facebook'] ?? '') }}"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="https://facebook.com/yourpage">
            @error('social_links.facebook')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Instagram -->
          <div>
            <label for="instagram" class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
            <input type="url" name="social_links[instagram]" id="instagram"
              value="{{ old('social_links.instagram', $settings->social_links['instagram'] ?? '') }}"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
              placeholder="https://instagram.com/yourpage">
            @error('social_links.instagram')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Twitter (X) -->
          <div>
            <label for="twitter" class="block text-sm font-medium text-gray-700 mb-1">Twitter (X)</label>
            <input type="url" name="social_links[twitter]" id="twitter"
              value="{{ old('social_links.twitter', $settings->social_links['twitter'] ?? '') }}"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black"
              placeholder="https://twitter.com/yourhandle">
            @error('social_links.twitter')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- YouTube -->
          <div>
            <label for="youtube" class="block text-sm font-medium text-gray-700 mb-1">YouTube</label>
            <input type="url" name="social_links[youtube]" id="youtube"
              value="{{ old('social_links.youtube', $settings->social_links['youtube'] ?? '') }}"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
              placeholder="https://youtube.com/yourchannel">
            @error('social_links.youtube')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Official Website -->
          <div class="md:col-span-2">
            <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Official Website</label>
            <input type="url" name="social_links[website]" id="website"
              value="{{ old('social_links.website', $settings->social_links['website'] ?? '') }}"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              placeholder="https://yourwebsite.com">
            @error('social_links.website')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>
      </div>

      <!-- Theme Colors Section -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Theme Colors</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Primary Color -->
          <div>
            <label for="primary_color" class="block text-sm font-medium text-gray-700 mb-1">Primary Color</label>
            <div class="flex items-center space-x-2">
              <input type="color" name="theme_colors[primary]" id="primary_color"
                value="{{ old('theme_colors.primary', $settings->theme_colors['primary'] ?? '#3B82F6') }}"
                class="h-10 w-20 p-1 rounded border border-gray-300 cursor-pointer">
              <span class="text-sm text-gray-500">Main brand color</span>
            </div>
            @error('theme_colors.primary')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Secondary Color -->
          <div>
            <label for="secondary_color" class="block text-sm font-medium text-gray-700 mb-1">Secondary Color</label>
            <div class="flex items-center space-x-2">
              <input type="color" name="theme_colors[secondary]" id="secondary_color"
                value="{{ old('theme_colors.secondary', $settings->theme_colors['secondary'] ?? '#10B981') }}"
                class="h-10 w-20 p-1 rounded border border-gray-300 cursor-pointer">
              <span class="text-sm text-gray-500">Accent color</span>
            </div>
            @error('theme_colors.secondary')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Tertiary Color -->
          <div>
            <label for="tertiary_color" class="block text-sm font-medium text-gray-700 mb-1">Tertiary Color</label>
            <div class="flex items-center space-x-2">
              <input type="color" name="theme_colors[tertiary]" id="tertiary_color"
                value="{{ old('theme_colors.tertiary', $settings->theme_colors['tertiary'] ?? '#F59E0B') }}"
                class="h-10 w-20 p-1 rounded border border-gray-300 cursor-pointer">
              <span class="text-sm text-gray-500">Highlight color</span>
            </div>
            @error('theme_colors.tertiary')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow transition duration-150 ease-in-out">
          Save Settings
        </button>
      </div>
    </form>
  </div>
</div>
@endsection