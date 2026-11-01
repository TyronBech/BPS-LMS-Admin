<?php

namespace Tests\Feature;

use App\Models\Book;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Gate;
use Tests\Support\BookMaintenanceTestCase;

class BookAcquisitionViewsTest extends BookMaintenanceTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app['view']->getFinder()->prependLocation(base_path('tests/Fixtures/views'));
        Gate::before(fn () => true);
    }

    private function renderPage(string $page, ?Book $book = null): string
    {
        return view('maintenance.books.'.$page, [
            'errors' => $this->app['session']->get('errors', new \Illuminate\Support\ViewErrorBag),
            'book' => $book, 'categories' => \App\Models\Category::with('lastAccession')->get(),
            'subjects' => collect(), 'condition' => ['Good', 'Fair'], 'availability' => ['Available', 'Unavailable'],
            'remarks' => ['On Shelf', 'Missing'], 'book_types' => ['Print', 'Non-print', 'E-books'],
            'linkedSubjectIds' => [], 'accessionDashActive' => true, 'cover' => null, 'mimeType' => null,
        ])->render();
    }

    private function dom(string $html): DOMXPath
    {
        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);

        return new DOMXPath($document);
    }

    public function test_create_renders_optional_fields_and_restores_submitted_values(): void
    {
        $general = $this->category();
        $this->category('Thesis');
        $this->app['session']->flashInput(['category' => $general->id, 'acquisition_mode' => 'Exchange', 'price' => '0', 'acquisition_date' => '2027-01-01']);
        $html = $this->renderPage('create');
        $dom = $this->dom($html);
        $this->assertSame(3, $dom->query('//*[@id="acquisition-section"]//input | //*[@id="acquisition-section"]//select')->length);
        $this->assertSame(0, $dom->query('//*[@id="acquisition-section"]//*[@required]')->length);
        $this->assertSame('0', $dom->query('//*[@id="price"]')->item(0)->getAttribute('value'));
        $this->assertSame('2027-01-01', $dom->query('//*[@id="acquisition_date"]')->item(0)->getAttribute('value'));
        $this->assertSame('Exchange', $dom->query('//*[@id="acquisition_mode"]/option[@selected]')->item(0)->getAttribute('value'));
        $this->assertStringContainsString('data-acquisition-eligible="false"', $html);
        $this->assertTrue(strpos($html, 'id="publishing-section"') < strpos($html, 'Acquisition Details'));
    }

    public function test_edit_loads_saved_details_but_new_copy_starts_blank(): void
    {
        $category = $this->category();
        $this->saveBook('store', $this->payload($category, ['acquisition_mode' => 'Gifts', 'price' => '1234.50', 'acquisition_date' => '2026-11-01']));
        $dom = $this->dom($this->renderPage('edit', Book::firstOrFail()));
        $this->assertSame('1234.50', $dom->query('//*[@id="price"]')->item(0)->getAttribute('value'));
        $this->assertSame('2026-11-01', $dom->query('//*[@id="acquisition_date"]')->item(0)->getAttribute('value'));
        $this->assertSame('Gifts', $dom->query('//*[@id="acquisition_mode"]/option[@selected]')->item(0)->getAttribute('value'));
        $this->assertSame('', $dom->query('//*[@id="copy_price"]')->item(0)->getAttribute('value'));
        $this->assertSame('', $dom->query('//*[@id="copy_acquisition_date"]')->item(0)->getAttribute('value'));
        $this->assertSame('', $dom->query('//*[@id="copy_acquisition_mode"]/option[@selected]')->item(0)->getAttribute('value'));
    }

    public function test_copy_validation_input_is_restored_without_replacing_original_acquisition_fields(): void
    {
        $category = $this->category();
        $this->saveBook('store', $this->payload($category, ['price' => '90']));
        $book = Book::firstOrFail();
        $this->app['session']->flashInput(['source_book_id' => $book->id, 'acquisition_mode' => 'Donation', 'price' => '7.25', 'acquisition_date' => '2026-10-31']);
        $dom = $this->dom($this->renderPage('edit', $book));
        $this->assertSame('90.00', $dom->query('//*[@id="price"]')->item(0)->getAttribute('value'));
        $this->assertSame('7.25', $dom->query('//*[@id="copy_price"]')->item(0)->getAttribute('value'));
        $this->assertSame('Donation', $dom->query('//*[@id="copy_acquisition_mode"]/option[@selected]')->item(0)->getAttribute('value'));
    }

    public function test_research_fields_are_hidden_and_disabled_on_initial_render(): void
    {
        $category = $this->category('Dissertations');
        $this->saveBook('store', $this->payload($category));
        $book = Book::firstOrFail();
        $this->app['session']->flashInput(['category' => $category->id]);
        foreach (['create', 'edit'] as $page) {
            $dom = $this->dom($this->renderPage($page, $book));
            $this->assertSame(1, $dom->query('//*[@id="acquisition-section"][@disabled][@hidden]')->length);
        }
        $dom = $this->dom($this->renderPage('edit', $book));
        $this->assertSame(1, $dom->query('//*[@id="copy_acquisition-section"][@disabled][@hidden]')->length);
        $this->assertStringNotContainsString('Acquisition Details', $this->renderPage('view', $book));
    }

    public function test_failed_copy_preserves_accessions_and_status_without_contaminating_source_edit_form(): void
    {
        $category = $this->category();
        $this->saveBook('store', $this->payload($category, ['price' => '90']));
        $book = Book::firstOrFail();
        $this->saveBook('copy', $this->payload($category, [
            'source_book_id' => $book->id, 'accession' => 'TST-00002;TST-00003',
            'remarks' => 'Missing', 'availability' => 'Unavailable', 'condition' => 'Fair', 'price' => '1.234',
        ]));
        $html = $this->renderPage('edit', $book);
        $dom = $this->dom($html);
        $this->assertSame('TST-00001', $dom->query('//*[@id="accession"]')->item(0)->getAttribute('value'));
        $this->assertSame('TST-00002;TST-00003', $dom->query('//*[@id="copy_accession"]')->item(0)->getAttribute('value'));
        foreach (['remarks' => 'On Shelf', 'availability' => 'Available', 'condition' => 'Good', 'copy_remarks' => 'Missing', 'copy_availability' => 'Unavailable', 'copy_condition' => 'Fair'] as $id => $value) {
            $this->assertSame($value, $dom->query('//*[@id="'.$id.'"]/option[@selected]')->item(0)->getAttribute('value'));
        }
        $this->assertSame('1.234', $dom->query('//*[@id="copy_price"]')->item(0)->getAttribute('value'));
        $this->assertSame('true', $dom->query('//*[@id="copy-book-modal"]')->item(0)->getAttribute('data-restore-copy'));
        $this->assertFalse($this->app['session']->get('errors')->getBag('default')->has('price'));
        $this->assertTrue($this->app['session']->get('errors')->getBag('copy')->has('price'));
    }

    public function test_details_display_zero_and_missing_values_without_currency(): void
    {
        $category = $this->category();
        $this->saveBook('store', $this->payload($category, ['price' => '0']));
        $html = $this->renderPage('view', Book::firstOrFail());
        $this->assertStringContainsString('Acquisition Details', $html);
        $dom = $this->dom($html);
        $values = $dom->query('//*[@id="acquisition-details"]//dd');
        $this->assertSame('—', trim($values->item(0)->textContent));
        $this->assertSame('0.00', trim($values->item(1)->textContent));
        $this->assertSame('—', trim($values->item(2)->textContent));
    }
}
