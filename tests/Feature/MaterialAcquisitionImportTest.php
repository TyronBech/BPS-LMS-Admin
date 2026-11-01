<?php

namespace Tests\Feature;

use App\Http\Controllers\Import\MaterialImportController;
use App\Jobs\ProcessMaterialImport;
use App\Models\Book;
use App\Models\ImportProgress;
use App\Services\Imports\MaterialImportReader;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\BookMaintenanceTestCase;

class MaterialAcquisitionImportTest extends BookMaintenanceTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['filesystems.default' => 'local']);
        Storage::fake('local');
        Schema::create('import_progress', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('initiated_by')->nullable();
            foreach (['total_rows', 'processed_rows', 'new_count', 'updated_count'] as $name) {
                $table->integer($name)->default(0);
            }
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    private function workbook(array $details = [], bool $legacy = false, int $base = 0, int $calendar = 1900): string
    {
        $book = new Spreadsheet;
        $book->setExcelCalendar($calendar);
        $sheet = $book->getActiveSheet();
        $row = array_fill(0, 39, null);
        foreach ([0 => 'TST-00001', 1 => 'Imported Material', 20 => 'Print', 21 => 'General', 37 => 'Institution stays intact'] as $column => $value) {
            $row[$column] = $value;
        }
        $headers = array_fill(0, 39, null);
        foreach ([0 => 'Accession', 1 => 'Title', 20 => 'Material Type', 21 => 'Category'] as $column => $value) {
            $headers[$column] = $value;
        }
        if (! $legacy) {
            $headers = array_merge($headers, ['Acquisition Mode', 'Price', 'Acquisition Date']);
            $row = array_merge($row, [$details['acquisition_mode'] ?? null, $details['price'] ?? null, $details['acquisition_date'] ?? null]);
        }
        $sheet->fromArray($headers, null, ($base ? 'B' : 'A').'18', true);
        $sheet->fromArray($row, null, ($base ? 'B' : 'A').'20', true);
        foreach (['acquisition_mode', 'price', 'acquisition_date'] as $offset => $field) {
            if (! $legacy && is_string($details[$field] ?? null)) {
                $cell = Coordinate::stringFromColumnIndex($base + 40 + $offset).'20';
                $sheet->setCellValueExplicit($cell, $details[$field], DataType::TYPE_STRING);
            }
        }
        Storage::makeDirectory('temp_imports');
        $path = 'temp_imports/materials.xlsx';
        (new Xlsx($book))->save(Storage::path($path));
        $book->disconnectWorksheets();

        return $path;
    }

    private function runImport(string $path, array $edits = []): ImportProgress
    {
        $progress = ImportProgress::create(['type' => 'materials', 'total_rows' => 1]);
        (new ProcessMaterialImport($path, $progress->id, 1, $edits))->handle();

        return $progress->refresh();
    }

    public function test_reader_handles_both_layouts_and_real_excel_dates_without_losing_category_fields(): void
    {
        $this->assertTrue(class_exists(MaterialImportReader::class));
        foreach ([0, 1] as $base) {
            foreach ([1900, 1904] as $calendar) {
                Date::setExcelCalendar($calendar);
                $serial = Date::PHPToExcel(new \DateTimeImmutable('2026-11-01'));
                $path = $this->workbook(['acquisition_mode' => 'Purchase', 'price' => 1250.50, 'acquisition_date' => $serial], false, $base, $calendar);
                $row = (new MaterialImportReader)->read(Storage::path($path))[0];
                $this->assertSame('2026-11-01', $row['acquisition_date']);
                $this->assertSame('1250.5', $row['price']);
                $this->assertSame('Institution stays intact', $row['institution']);
                $this->assertSame(20, $row['_excel_row']);
            }
        }
        Date::setExcelCalendar(1900);
    }

    public function test_worker_creates_and_updates_acquisition_details(): void
    {
        $this->category();
        $path = $this->workbook(['acquisition_mode' => 'Gifts', 'price' => 0, 'acquisition_date' => '2026-11-01']);
        $this->assertSame('completed', $this->runImport($path)->status);
        $book = Book::firstOrFail();
        $this->assertSame('Gifts', $book->getRawOriginal('acquisition_mode'));
        $this->assertSame('0.00', $book->price);
        $this->assertSame('2026-11-01', $book->acquisition_date->format('Y-m-d'));
        $path = $this->workbook(['acquisition_mode' => 'Exchange', 'price' => '9999999999.99']);
        $this->assertSame('completed', $this->runImport($path)->status);
        $this->assertSame('9999999999.99', $book->refresh()->price);
        $this->assertNull($book->acquisition_date);
        $this->assertSame(1, Book::count());
    }

    public static function invalidAcquisitions(): array
    {
        return [
            ['acquisition_mode', 'Borrowed'], ['price', '-1'], ['price', '1.234'], ['price', '10000000000'],
            ['price', '1e2'], ['price', '1,250.50'], ['acquisition_date', '2026-02-30'],
            ['acquisition_date', '11/01/2026'], ['acquisition_date', 46327.5],
            ['price', false], ['acquisition_date', false], ['acquisition_mode', false],
            ['acquisition_date', 0], ['acquisition_date', 60],
        ];
    }

    #[DataProvider('invalidAcquisitions')]
    public function test_invalid_acquisitions_are_rejected_before_dispatch_and_revalidated_by_worker(string $field, mixed $value): void
    {
        $this->category();
        $path = $this->workbook([$field => $value]);
        Bus::fake([ProcessMaterialImport::class]);
        $session = $this->app['session']->driver();
        $session->put('material_import_file', $path);
        $request = Request::create('/import/materials', 'POST');
        $request->setLaravelSession($session);
        $response = (new MaterialImportController)->store($request);
        $this->assertSame(422, $response->getStatusCode());
        $body = $response->getData(true);
        $this->assertArrayHasKey('materials.0.'.$field, $body['errors']);
        $this->assertStringContainsString('20', $body['message']);
        $this->assertStringContainsString('TST-00001', $body['message']);
        Bus::assertNotDispatched(ProcessMaterialImport::class);
        $this->assertSame(0, ImportProgress::count());
        $this->assertSame($path, $session->get('material_import_file'));

        $progress = $this->runImport($path);
        $this->assertSame('failed', $progress->status);
        $this->assertStringContainsString('20', $progress->error_message);
        $this->assertSame(0, Book::count());
    }

    public function test_legacy_template_preserves_acquisition_values_and_present_blank_columns_clear_them(): void
    {
        $category = $this->category();
        $this->saveBook('store', $this->payload($category, ['price' => '99.99', 'acquisition_mode' => 'Purchase', 'acquisition_date' => '2026-11-01']));
        $this->assertSame('completed', $this->runImport($this->workbook([], true))->status);
        $book = Book::firstOrFail();
        $this->assertSame('99.99', $book->price);
        $this->assertSame('Purchase', $book->getRawOriginal('acquisition_mode'));
        $this->assertSame('completed', $this->runImport($this->workbook())->status);
        $this->assertNull($book->refresh()->price);
        $this->assertNull($book->acquisition_mode);
        $this->assertNull($book->acquisition_date);
    }

    public function test_worker_ignores_acquisition_values_for_research_category(): void
    {
        $this->category('General');
        $this->category('Academic Research');
        $path = $this->workbook(['acquisition_mode' => 'Invalid', 'price' => '-2', 'acquisition_date' => 'bad']);
        $progress = $this->runImport($path, [0 => ['category' => 'Academic Research']]);
        $this->assertSame('completed', $progress->status);
        $book = Book::firstOrFail();
        $this->assertNull($book->price);
        $this->assertNull($book->acquisition_mode);
        $this->assertNull($book->acquisition_date);
    }

    public function test_submitted_edits_are_validated_and_retained_for_correction(): void
    {
        $this->category();
        $path = $this->workbook(['price' => '10']);
        $session = $this->app['session']->driver();
        $session->put('material_import_file', $path);
        $request = Request::create('/import/materials', 'POST', ['materials' => [0 => ['price' => '1.234']]]);
        $request->setLaravelSession($session);
        $response = (new MaterialImportController)->store($request);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('1.234', $session->get('material_import_edits.0.price'));
    }

    public function test_preview_renders_acquisitions_and_errors_but_legacy_files_do_not_submit_missing_columns(): void
    {
        $details = [
            'acquisition_mode' => 'Wrong', 'price' => '1.234', 'acquisition_date' => 'bad', 'category' => 'General',
        ];
        $html = view('import.materials.acquisition-fields', [
            'index' => 0, 'item' => $details, 'researchCategoryNames' => ['academic research'],
            'acquisitionErrors' => ['materials.0.price' => ['Excel row 20: invalid price']],
        ])->render();
        $this->assertStringContainsString('materials[0][price]', $html);
        $this->assertStringContainsString('1.234', $html);
        $this->assertStringContainsString('Excel row 20: invalid price', $html);
        $this->assertStringContainsString('Wrong (invalid)', $html);
        $legacy = view('import.materials.acquisition-fields', ['index' => 0, 'item' => ['category' => 'General']])->render();
        $this->assertStringNotContainsString('name=', $legacy);
        $this->assertStringContainsString('Saved acquisition details will be kept', $legacy);
    }

    public function test_template_contains_new_headers_native_values_and_optional_validation(): void
    {
        $book = IOFactory::load(public_path('excel/Material-template.xlsx'));
        $sheet = $book->getActiveSheet();
        $this->assertSame(1, $book->getSheetCount());
        $this->assertSame('Acquisition Mode', $sheet->getCell('AN18')->getValue());
        $this->assertSame('Price', $sheet->getCell('AO18')->getValue());
        $this->assertSame('Acquisition Date', $sheet->getCell('AP18')->getValue());
        $this->assertIsNumeric($sheet->getCell('AO19')->getValue());
        $this->assertIsNumeric($sheet->getCell('AP19')->getValue());
        $this->assertSame('yyyy-mm-dd', $sheet->getStyle('AP19')->getNumberFormat()->getFormatCode());
        $this->assertSame('list', $sheet->getCell('AN19')->getDataValidation()->getType());
        foreach (['AN19', 'AO19', 'AP19'] as $cell) {
            $validation = $sheet->getCell($cell)->getDataValidation();
            $this->assertTrue($validation->getAllowBlank());
            $this->assertTrue($validation->getShowErrorMessage());
        }
        $book->disconnectWorksheets();
    }

    public function test_updated_downloadable_template_parses_its_native_date_and_decimal_example(): void
    {
        $row = (new MaterialImportReader)->read(public_path('excel/Material-template.xlsx'))[0];
        $this->assertSame('Purchase', $row['acquisition_mode']);
        $this->assertSame('1250.5', $row['price']);
        $this->assertSame('2026-11-01', $row['acquisition_date']);
        $this->assertSame(19, $row['_excel_row']);
    }

    public function test_header_error_redirect_preserves_uploaded_file_and_preview_edits(): void
    {
        $path = $this->workbook();
        $book = IOFactory::load(Storage::path($path));
        $book->getActiveSheet()->setCellValue('AN18', 'Acquisition Method');
        (new Xlsx($book))->save(Storage::path($path));
        $book->disconnectWorksheets();
        $session = $this->app['session']->driver();
        $session->put('material_import_file', $path);
        $session->put('material_import_edits', [0 => ['price' => '10']]);
        $request = Request::create('/import/materials/upload', 'GET');
        $request->setLaravelSession($session);
        $response = (new MaterialImportController)->upload($request);
        $landingRequest = Request::create($response->getTargetUrl(), 'GET');
        $landingRequest->setLaravelSession($session);
        (new MaterialImportController)->index($landingRequest);
        $this->assertSame($path, $session->get('material_import_file'));
        $this->assertSame('10', $session->get('material_import_edits.0.price'));
        $this->assertTrue(Storage::exists($path));
    }

    public function test_corrected_preview_values_are_dispatched_and_the_worker_saves_them(): void
    {
        $this->category();
        $path = $this->workbook(['price' => '1.234', 'acquisition_date' => 'bad']);
        $session = $this->app['session']->driver();
        $session->put('material_import_file', $path);
        $session->put('material_import_edits', [0 => ['acquisition_mode' => 'Donation']]);
        Bus::fake([ProcessMaterialImport::class]);
        $request = Request::create('/import/materials', 'POST', ['materials' => [0 => ['price' => '15.25', 'acquisition_date' => '2027-01-01']]]);
        $request->setLaravelSession($session);
        $response = (new MaterialImportController)->store($request);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertNull($session->get('material_import_file'));
        $this->assertNull($session->get('material_import_edits'));
        $jobs = Bus::dispatched(ProcessMaterialImport::class);
        $this->assertCount(1, $jobs);
        $jobs->first()->handle();
        $book = Book::firstOrFail();
        $this->assertSame('15.25', $book->price);
        $this->assertSame('Donation', $book->getRawOriginal('acquisition_mode'));
        $this->assertSame('2027-01-01', $book->acquisition_date->format('Y-m-d'));
    }

    public static function eligibleMaterials(): array
    {
        return [['General', 'Print'], ['Media', 'Non-print'], ['Digital', 'E-books'], ['Periodicals', 'Print'], ['Serials', 'Print']];
    }

    #[DataProvider('eligibleMaterials')]
    public function test_acquisitions_apply_to_every_eligible_material_type(string $name, string $type): void
    {
        $this->category($name, $type);
        $path = $this->workbook(['acquisition_mode' => 'Donation']);
        $progress = $this->runImport($path, [0 => ['category' => $name, 'book_type' => $type]]);
        $this->assertSame('completed', $progress->status);
        $book = Book::firstOrFail();
        $this->assertSame('Donation', $book->getRawOriginal('acquisition_mode'));
        $this->assertNull($book->price);
        $this->assertNull($book->acquisition_date);
    }

    public function test_xls_files_use_the_same_reader_and_acquisition_validation(): void
    {
        $this->category();
        $source = $this->workbook(['acquisition_mode' => 'Exchange', 'price' => '15.25', 'acquisition_date' => '2026-11-01']);
        $book = IOFactory::load(Storage::path($source));
        $target = 'temp_imports/materials.xls';
        (new Xls($book))->save(Storage::path($target));
        $book->disconnectWorksheets();
        $this->assertSame('completed', $this->runImport($target)->status);
        $this->assertSame('15.25', Book::firstOrFail()->price);
    }
}
