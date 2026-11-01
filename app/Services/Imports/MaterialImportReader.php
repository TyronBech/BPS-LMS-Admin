<?php

namespace App\Services\Imports;

use DateTimeZone;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class MaterialImportReader
{
    private const COLUMNS = [
        'accession', 'title', 'authors.Main author', 'authors.Corporate author',
        'authors.Added authors', 'authors.Contributors', 'edition', 'call_number', 'isbn',
        'description.Description', 'description.Content notes', 'description.Abstract',
        'description.Reviews', 'description.Extent', 'description.Acc Material',
        'place_of_publication', 'publisher', 'copyrights', 'location', 'languages',
        'book_type', 'category', 'digital_copy_url', 'subject', 'parallel_title',
        'description.Series', 'periodical_kind', 'volume', 'issue_number', 'material_type',
        'issn', 'frequency', 'latest_received', 'notes', 'topical_access_point',
        'corporate_access_point', 'discipline', 'institution', 'program',
    ];

    private const ACQUISITION_COLUMNS = [
        'acquisition_mode' => 'Acquisition Mode', 'price' => 'Price', 'acquisition_date' => 'Acquisition Date',
    ];

    public function read(string $path, array $edits = []): array
    {
        $originalCalendar = Date::getExcelCalendar();
        $reader = IOFactory::createReaderForFile($path, [IOFactory::READER_XLSX, IOFactory::READER_XLS]);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        try {
            Date::setExcelCalendar($spreadsheet->getExcelCalendar());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->rangeToArray('A1:'.$sheet->getHighestDataColumn().$sheet->getHighestDataRow(), null, true, false);
            $header = $rows[17] ?? [];
            $base = strtolower(trim((string) ($header[0] ?? ''))) === 'accession' ? 0 : 1;
            if (strtolower(trim((string) ($header[$base] ?? ''))) !== 'accession') {
                throw new \InvalidArgumentException('The template must have Accession in cell A18 or B18. Data must begin at row 19.');
            }
            $acquisitionColumns = [];
            foreach (self::ACQUISITION_COLUMNS as $field => $label) {
                $column = $base + 39 + count($acquisitionColumns);
                $name = trim((string) ($header[$column] ?? ''));
                if ($name !== '' && strcasecmp($name, $label) !== 0) {
                    throw new \InvalidArgumentException("Header row 18: expected {$label}, found {$name}.");
                }
                $acquisitionColumns[$field] = ['column' => $column, 'present' => $name !== ''];
            }
            $data = [];
            for ($index = 18; $index < count($rows); $index++) {
                $row = $rows[$index];
                if (! array_filter(array_slice($row, $base, 42), fn ($value) => $this->clean($value) !== null)) {
                    continue;
                }
                $item = ['_excel_row' => $index + 1];
                foreach (self::COLUMNS as $column => $field) {
                    Arr::set($item, $field, $this->clean($row[$base + $column] ?? null));
                }
                $item['accession'] = $this->capitalizeAccession($item['accession']);
                $item['book_type'] ??= 'Print';
                foreach ($acquisitionColumns as $field => $column) {
                    $value = $row[$column['column']] ?? null;
                    if ($column['present']) {
                        $item[$field] = $field === 'acquisition_date' ? $this->date($value) : $this->clean($value);
                    } elseif ($this->clean($value) !== null) {
                        throw new \InvalidArgumentException('Excel row '.($index + 1).': missing '.self::ACQUISITION_COLUMNS[$field].' header in row 18.');
                    }
                }
                $data[] = $item;
            }

            return $this->applyEdits($data, $edits);
        } finally {
            $spreadsheet->disconnectWorksheets();
            Date::setExcelCalendar($originalCalendar);
        }
    }

    private function applyEdits(array $data, array $edits): array
    {
        $allowed = array_fill_keys(array_merge(array_map(fn ($name) => explode('.', $name)[0], self::COLUMNS), array_keys(self::ACQUISITION_COLUMNS)), true);
        foreach ($edits as $index => $edit) {
            if (! isset($data[$index]) || ! is_array($edit)) {
                continue;
            }
            $edit = array_intersect_key($edit, $allowed);
            foreach (['authors', 'description'] as $field) {
                if (isset($edit[$field]) && is_array($edit[$field])) {
                    $data[$index][$field] = array_merge($data[$index][$field], $edit[$field]);
                }
                unset($edit[$field]);
            }
            $data[$index] = array_replace($data[$index], $edit);
        }

        return $data;
    }

    private function clean(mixed $value): ?string
    {
        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        return $value === null || trim((string) $value) === '' ? null : trim((string) $value);
    }

    private function capitalizeAccession(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $parts = explode('-', $value, 2);
        $parts[0] = strtoupper($parts[0]);

        return implode('-', $parts);
    }

    private function date(mixed $value): ?string
    {
        $minimum = Date::getExcelCalendar() === 1904 ? 0 : 1;
        $maximum = Date::getExcelCalendar() === 1904 ? 2957003 : 2958465;
        if ((is_int($value) || is_float($value)) && is_finite((float) $value) && floor($value) === (float) $value && $value >= $minimum && $value <= $maximum) {
            if (! (Date::getExcelCalendar() === 1900 && (float) $value === 60.0)) {
                return Date::excelToDateTimeObject($value, new DateTimeZone('UTC'))->format('Y-m-d');
            }
        }

        return $this->clean($value);
    }
}
