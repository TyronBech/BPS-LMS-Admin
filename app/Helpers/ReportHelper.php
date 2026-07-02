<?php

namespace App\Helpers;

use Carbon\Carbon;

class ReportHelper
{
    /**
     * Centralized logic to get the School Year (SY).
     * The school year in the Philippines is from June to March.
     * E.g., June 2025 to March 2026 is School Year 2025-2026.
     *
     * @param string|null $startDateStr
     * @param string|null $endDateStr
     * @param mixed $collection
     * @param string $dateField
     * @return string
     */
    public static function getSchoolYear($startDateStr = null, $endDateStr = null, $collection = null, $dateField = 'created_at')
    {
        $date = null;

        // 1. Look at the start date from filter
        if (!empty($startDateStr)) {
            try {
                $date = self::parseDate($startDateStr);
            } catch (\Throwable $e) {}
        }

        // 2. Look at the end date from filter (if start was empty/invalid)
        if (!$date && !empty($endDateStr)) {
            try {
                $date = self::parseDate($endDateStr);
            } catch (\Throwable $e) {}
        }

        // 3. Look at request parameters directly
        if (!$date) {
            $reqStart = request('start') ?: request('start_date');
            if (!empty($reqStart)) {
                try {
                    $date = self::parseDate($reqStart);
                } catch (\Throwable $e) {}
            }
        }
        if (!$date) {
            $reqEnd = request('end') ?: request('end_date');
            if (!empty($reqEnd)) {
                try {
                    $date = self::parseDate($reqEnd);
                } catch (\Throwable $e) {}
            }
        }

        // 4. Look at the collection data
        if (!$date && $collection) {
            $items = (is_object($collection) && method_exists($collection, 'getCollection')) ? $collection->getCollection() : $collection;
            if (!empty($items)) {
                $firstItem = null;
                $firstKey = null;
                foreach ($items as $k => $v) {
                    $firstItem = $v;
                    $firstKey = $k;
                    break;
                }
                if ($firstItem !== null) {
                    if (is_array($firstItem)) {
                        $val = $firstItem[$dateField] ?? $firstItem['timestamp'] ?? $firstItem['created_at'] ?? $firstItem['start'] ?? $firstItem['borrowed_at'] ?? $firstItem['printed_at'] ?? null;
                    } else if (is_object($firstItem)) {
                        $val = $firstItem->{$dateField} ?? $firstItem->timestamp ?? $firstItem->created_at ?? $firstItem->start ?? $firstItem->borrowed_at ?? $firstItem->printed_at ?? null;
                    } else {
                        $val = $firstItem;
                    }
                    if (!$val && is_string($firstKey)) {
                        $val = $firstKey;
                    }
                    if ($val) {
                        try {
                            $date = Carbon::parse($val);
                        } catch (\Throwable $e) {}
                    }
                }
            }
        }

        // 5. Fallback to current date
        if (!$date) {
            $date = Carbon::now();
        }

        $year = $date->year;
        if ($date->month >= 6) {
            return $year . '-' . ($year + 1);
        } else {
            return ($year - 1) . '-' . $year;
        }
    }

    /**
     * central logic to format reporting period and fix min/max date range bugs.
     *
     * @param mixed $data
     * @param string $dateField
     * @param string $format
     * @return string
     */
    public static function buildReportingPeriod($data, $dateField = 'created_at', $format = 'F j, Y')
    {
        // Check if start/end parameters are provided in the request
        $startStr = request('start') ?: request('start_date');
        $endStr = request('end') ?: request('end_date');

        if (!empty($startStr) && !empty($endStr)) {
            try {
                $start = self::parseDate($startStr);
                $end = self::parseDate($endStr);
                if ($start->isSameDay($end)) {
                    return $start->format($format);
                }
                return 'From ' . $start->format($format) . ' to ' . $end->format($format);
            } catch (\Throwable $e) {}
        }

        // Otherwise, fallback to collection dates
        $items = (is_object($data) && method_exists($data, 'getCollection')) ? $data->getCollection() : $data;

        if (empty($items) || (is_object($items) && method_exists($items, 'isEmpty') && $items->isEmpty())) {
            return 'N/A';
        }

        $dates = collect($items)->map(function ($item, $key) use ($dateField) {
            if (is_array($item)) {
                $val = $item[$dateField] ?? $item['timestamp'] ?? $item['created_at'] ?? $item['start'] ?? $item['borrowed_at'] ?? $item['printed_at'] ?? null;
            } else if (is_object($item)) {
                $val = $item->{$dateField} ?? $item->timestamp ?? $item->created_at ?? $item->start ?? $item->borrowed_at ?? $item->printed_at ?? null;
            } else {
                $val = $item;
            }
            if (!$val && is_string($key)) {
                $val = $key;
            }
            if ($val) {
                try {
                    return Carbon::parse($val);
                } catch (\Throwable $e) {}
            }
            return null;
        })->filter();

        if ($dates->isNotEmpty()) {
            $min = $dates->min();
            $max = $dates->max();
            
            if ($min->isSameDay($max)) {
                return $min->format($format);
            }
            return 'From ' . $min->format($format) . ' to ' . $max->format($format);
        }

        return 'N/A';
    }

    /**
     * Helper to parse dates with multiple formats.
     */
    private static function parseDate($value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }
        foreach (['m/d/Y', 'Y-m-d', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable $e) {}
        }
        return Carbon::parse($value);
    }

    /**
     * Helper to format report titles suffix based on date filters or fallback to data.
     * E.g. (Online Research for the month of June to July 2026) or (Online Research for S.Y. 2025-2026)
     */
    public static function getFormattedHeaderSuffix($reportName, $startDateStr = null, $endDateStr = null, $collection = null, $dateField = 'created_at')
    {
        $start = null;
        $end = null;

        if (!empty($startDateStr)) {
            try {
                $start = self::parseDate($startDateStr);
            } catch (\Throwable $e) {}
        }
        if (!empty($endDateStr)) {
            try {
                $end = self::parseDate($endDateStr);
            } catch (\Throwable $e) {}
        }

        if (!$start) {
            $reqStart = request('start') ?: request('start_date');
            if (!empty($reqStart)) {
                try {
                    $start = self::parseDate($reqStart);
                } catch (\Throwable $e) {}
            }
        }
        if (!$end) {
            $reqEnd = request('end') ?: request('end_date');
            if (!empty($reqEnd)) {
                try {
                    $end = self::parseDate($reqEnd);
                } catch (\Throwable $e) {}
            }
        }

        if ((!$start || !$end) && $collection) {
            $items = (is_object($collection) && method_exists($collection, 'getCollection')) ? $collection->getCollection() : $collection;
            $dates = collect($items)->map(function ($item, $key) use ($dateField) {
                if (is_array($item)) {
                    $val = $item[$dateField] ?? $item['timestamp'] ?? $item['created_at'] ?? $item['start'] ?? $item['borrowed_at'] ?? $item['printed_at'] ?? null;
                } else if (is_object($item)) {
                    $val = $item->{$dateField} ?? $item->timestamp ?? $item->created_at ?? $item->start ?? $item->borrowed_at ?? $item->printed_at ?? null;
                } else {
                    $val = $item;
                }
                if (!$val && is_string($key)) {
                    $val = $key;
                }
                if ($val) {
                    try {
                        return Carbon::parse($val);
                    } catch (\Throwable $e) {}
                }
                return null;
            })->filter();

            if ($dates->isNotEmpty()) {
                if (!$start) $start = $dates->min();
                if (!$end) $end = $dates->max();
            }
        }

        if (!$start) {
            $start = Carbon::now();
        }
        if (!$end) {
            $end = Carbon::now();
        }

        // Philippine school year is June to March next year
        if ($start->month === 6 && $end->month === 3 && $start->year === ($end->year - 1)) {
            return "{$reportName} for S.Y. {$start->year}-{$end->year}";
        }

        if ($start->year === $end->year) {
            if ($start->month === $end->month) {
                return "{$reportName} for the month of " . $start->format('F Y');
            } else {
                return "{$reportName} for the month of " . $start->format('F') . ' to ' . $end->format('F Y');
            }
        } else {
            return "{$reportName} for " . $start->format('F Y') . ' to ' . $end->format('F Y');
        }
    }
}
