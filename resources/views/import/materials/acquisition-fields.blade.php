@php
    $isResearch = in_array(strtolower(trim((string) ($item['category'] ?? ''))), $researchCategoryNames ?? [], true);
    $hasAcquisitionColumns = count(array_intersect(\App\Support\BookAcquisitionDetails::FIELDS, array_keys($item))) > 0;
@endphp
@if($hasAcquisitionColumns)
    <fieldset data-material-acquisition data-row-index="{{ $index }}" class="space-y-2 {{ $isResearch ? 'hidden' : '' }}" @disabled($isResearch) @if($isResearch) hidden @endif>
        @foreach(['acquisition_mode' => 'Acquisition Mode', 'price' => 'Price per Copy', 'acquisition_date' => 'Acquisition Date'] as $field => $label)
            @if(array_key_exists($field, $item))
                @php
                    $value = is_scalar($item[$field]) ? (string) $item[$field] : '';
                    $errorKey = "materials.{$index}.{$field}";
                    $errorId = "material-{$index}-{$field}-error";
                @endphp
                <div>
                    <label for="material-{{ $index }}-{{ $field }}" class="block text-[10px] font-bold text-slate-500 dark:text-slate-400">{{ $label }}</label>
                    @if($field === 'acquisition_mode')
                        <select id="material-{{ $index }}-{{ $field }}" name="materials[{{ $index }}][{{ $field }}]" aria-describedby="{{ $errorId }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-xs p-2 min-h-[44px]">
                            <option value="" @selected($value === '')>Leave blank</option>
                            @if($value !== '' && !\App\Enum\AcquisitionModeEnum::tryFrom($value))
                                <option value="{{ $value }}" selected>{{ $value }} (invalid)</option>
                            @endif
                            @foreach(\App\Enum\AcquisitionModeEnum::cases() as $mode)
                                <option value="{{ $mode->value }}" @selected($value === $mode->value)>{{ $mode->value }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" id="material-{{ $index }}-{{ $field }}" name="materials[{{ $index }}][{{ $field }}]" value="{{ $value }}" placeholder="{{ $field === 'price' ? '0.00' : 'YYYY-MM-DD' }}" @if($field === 'price') inputmode="decimal" @endif aria-describedby="{{ $errorId }}" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-xs p-2 min-h-[44px]">
                    @endif
                    <p id="{{ $errorId }}" data-material-error="{{ $errorKey }}" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ implode(' ', $acquisitionErrors[$errorKey] ?? []) }}</p>
                </div>
            @endif
        @endforeach
    </fieldset>
    <p data-material-acquisition-research class="text-xs text-slate-500 {{ $isResearch ? '' : 'hidden' }}">Not applicable to Academic Research.</p>
@else
    <p class="text-xs text-slate-500">No acquisition columns in this file. Saved acquisition details will be kept.</p>
@endif
