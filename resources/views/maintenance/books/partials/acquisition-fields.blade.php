@php
  $acquisitionPrefix = $acquisitionPrefix ?? '';
  $acquisitionBook = $acquisitionBook ?? null;
  $acquisitionErrorBag = $acquisitionErrorBag ?? 'default';
  $savedMode = $acquisitionBook?->acquisition_mode?->value;
  $savedPrice = $acquisitionBook?->price;
  $savedDate = $acquisitionBook?->acquisition_date?->format('Y-m-d');
  $modeValue = $restoreAcquisitionInput ? old('acquisition_mode', $savedMode) : $savedMode;
  $priceValue = $restoreAcquisitionInput ? old('price', $savedPrice) : $savedPrice;
  $dateValue = $restoreAcquisitionInput ? old('acquisition_date', $savedDate) : $savedDate;
@endphp
<fieldset id="{{ $acquisitionPrefix }}acquisition-section" data-acquisition-section data-category-input="{{ $acquisitionCategoryInput }}"
  class="min-w-0 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden {{ $acquisitionEligible ? '' : 'hidden' }}"
  @disabled(!$acquisitionEligible) @if(!$acquisitionEligible) hidden @endif aria-labelledby="{{ $acquisitionPrefix }}acquisition-heading">
  <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
    <h6 id="{{ $acquisitionPrefix }}acquisition-heading" class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Acquisition Details</h6>
  </div>
  <div class="p-5">
    <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">All fields are optional. Price is per copy.</p>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="min-w-0">
        <label for="{{ $acquisitionPrefix }}acquisition_mode" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Acquisition Mode:</label>
        <select id="{{ $acquisitionPrefix }}acquisition_mode" name="acquisition_mode" class="min-h-[44px] bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
          <option value="" @selected(!$modeValue)>Select a mode (optional)</option>
          @foreach(\App\Enum\AcquisitionModeEnum::cases() as $mode)
            <option value="{{ $mode->value }}" @selected($modeValue === $mode->value)>{{ $mode->value }}</option>
          @endforeach
        </select>
        @if($restoreAcquisitionInput)
          @error('acquisition_mode', $acquisitionErrorBag) <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
        @endif
      </div>
      <div class="min-w-0">
        <label for="{{ $acquisitionPrefix }}price" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Price:</label>
        <input type="number" id="{{ $acquisitionPrefix }}price" name="price" min="0" max="9999999999.99" step="0.01" inputmode="decimal" placeholder="0.00" value="{{ is_scalar($priceValue) ? $priceValue : '' }}" class="min-h-[44px] bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
        @if($restoreAcquisitionInput)
          @error('price', $acquisitionErrorBag) <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
        @endif
      </div>
      <div class="min-w-0">
        <label for="{{ $acquisitionPrefix }}acquisition_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Acquisition Date:</label>
        <input type="date" id="{{ $acquisitionPrefix }}acquisition_date" name="acquisition_date" value="{{ is_scalar($dateValue) ? $dateValue : '' }}" class="min-h-[44px] bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-400 focus:border-primary-400 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        @if($restoreAcquisitionInput)
          @error('acquisition_date', $acquisitionErrorBag) <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
        @endif
      </div>
    </div>
  </div>
</fieldset>
