<?php

namespace App\Support;

use App\Enum\AcquisitionModeEnum;
use App\Models\Category;
use Illuminate\Validation\Rule;

class BookAcquisitionDetails
{
    public const FIELDS = ['acquisition_mode', 'price', 'acquisition_date'];

    public static function rules(): array
    {
        return [
            'acquisition_mode' => ['nullable', Rule::enum(AcquisitionModeEnum::class)],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'acquisition_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public static function normalize(array $values, ?Category $category, bool $includeMissing = true): array
    {
        $research = $category?->isAcademicResearch() ?? false;
        $details = [];
        foreach (self::FIELDS as $field) {
            if (! $includeMissing && ! $research && ! array_key_exists($field, $values)) {
                continue;
            }
            $value = $research ? null : ($values[$field] ?? null);
            $value = is_string($value) ? trim($value) : $value;
            $details[$field] = $value === '' ? null : $value;
        }

        return $details;
    }
}
