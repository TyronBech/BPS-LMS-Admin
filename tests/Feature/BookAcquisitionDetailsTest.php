<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\BookMaintenanceTestCase;

class BookAcquisitionDetailsTest extends BookMaintenanceTestCase
{
    public function test_migration_adds_nullable_fields_and_rolls_back_without_losing_books(): void
    {
        $paths = glob(database_path('migrations/*_add_acquisition_details_to_bk_books_table.php'));
        $this->assertCount(1, $paths);
        $this->saveBook('store', $this->payload($this->category()));
        $this->assertTrue(Schema::hasColumns('bk_books', ['acquisition_mode', 'price', 'acquisition_date']));
        $this->assertNull(Book::first()->price);
        (require $paths[0])->down();
        $this->assertFalse(Schema::hasColumn('bk_books', 'price'));
        $this->assertSame(1, Book::count());
        (require $paths[0])->up();
        $this->assertNull(Book::first()->acquisition_mode);
    }

    public static function materialCases(): array
    {
        return [
            ['General', 'Print', 'Purchase', '1250.50'],
            ['Media', 'Non-print', 'Donation', '0'],
            ['Digital', 'E-books', 'Exchange', '10.5'],
            ['Periodicals', 'Print', 'Gifts', '9999999999.99'],
            ['Serials', 'Print', 'Donation', '25'],
        ];
    }

    #[DataProvider('materialCases')]
    public function test_create_edit_and_copy_persist_details_for_each_material(string $name, string $type, string $mode, string $price): void
    {
        $category = $this->category($name, $type);
        $details = ['acquisition_mode' => $mode, 'price' => $price, 'acquisition_date' => '2026-11-01'];
        $this->saveBook('store', $this->payload($category, $details));
        $book = Book::firstOrFail();
        $this->assertSame($mode, $book->getRawOriginal('acquisition_mode'));
        $this->assertSame(number_format((float) $price, 2, '.', ''), $book->price);
        $this->assertSame('2026-11-01', $book->acquisition_date->format('Y-m-d'));

        $this->saveBook('update', $this->payload($category, ['id' => $book->id, 'accession' => $book->accession, 'price' => '15.25']));
        $book->refresh();
        $this->assertSame('15.25', $book->price);
        $this->assertNull($book->acquisition_mode);
        $this->assertNull($book->acquisition_date);

        $this->saveBook('copy', $this->payload($category, $details + ['source_book_id' => $book->id, 'accession' => 'TST-00002;TST-00003']));
        $copies = Book::where('id', '!=', $book->id)->get();
        $this->assertCount(2, $copies);
        foreach ($copies as $copy) {
            $this->assertSame($mode, $copy->getRawOriginal('acquisition_mode'));
            $this->assertSame(number_format((float) $price, 2, '.', ''), $copy->price);
            $this->assertSame('2026-11-01', $copy->acquisition_date->format('Y-m-d'));
        }
    }

    public static function invalidDetails(): array
    {
        return [
            ['acquisition_mode', 'Borrowed'], ['price', '-0.01'], ['price', '1.234'],
            ['price', '10000000000.00'], ['price', 'abc'], ['price', '1e2'],
            ['acquisition_date', '2026-02-30'], ['acquisition_date', '01/11/2026'],
        ];
    }

    #[DataProvider('invalidDetails')]
    public function test_invalid_details_are_rejected_on_all_write_paths(string $field, string $value): void
    {
        $category = $this->category();
        $this->saveBook('store', $this->payload($category));
        $book = Book::firstOrFail();
        foreach (['store', 'update', 'copy'] as $action) {
            $this->app['session']->flush();
            $this->saveBook($action, $this->payload($category, [
                'id' => $book->id, 'source_book_id' => $book->id,
                'accession' => $action === 'update' ? $book->accession : 'TST-00002', $field => $value,
            ]));
            $this->assertNotNull($this->app['session']->get('toast-warning'), $action);
            $this->assertTrue($this->app['session']->get('errors')->getBag($action === 'copy' ? 'copy' : 'default')->has($field), $action);
            $this->assertSame($value, $this->app['session']->getOldInput($field));
            $this->assertSame(1, Book::count());
        }
    }

    public static function researchAliases(): array
    {
        return array_map(fn ($name) => [$name], ['Academic Research', 'academic researches', ' Research Works ', 'RESEARCH', 'Thesis', 'Theses', 'Dissertation', 'Dissertations']);
    }

    #[DataProvider('researchAliases')]
    public function test_research_category_clears_submitted_details_on_all_write_paths(string $name): void
    {
        $category = $this->category($name);
        $bad = ['acquisition_mode' => 'invalid', 'price' => '-1', 'acquisition_date' => 'invalid'];
        $this->saveBook('store', $this->payload($category, $bad));
        $book = Book::firstOrFail();
        $this->assertNull($book->getAttribute('price'));
        $this->saveBook('update', $this->payload($category, $bad + ['id' => $book->id]));
        $this->saveBook('copy', $this->payload($category, $bad + ['source_book_id' => $book->id, 'accession' => 'TST-00002']));
        $this->assertSame(2, Book::count());
        foreach (Book::all() as $item) {
            $this->assertNull($item->acquisition_mode);
            $this->assertNull($item->price);
            $this->assertNull($item->acquisition_date);
        }
    }

    public function test_changing_to_research_clears_saved_details(): void
    {
        $general = $this->category();
        $this->saveBook('store', $this->payload($general, ['acquisition_mode' => 'Purchase', 'price' => '99.99', 'acquisition_date' => '2026-11-01']));
        $book = Book::firstOrFail();
        $this->assertSame('99.99', $book->price);
        $this->saveBook('update', $this->payload($this->category('Academic Research'), ['id' => $book->id]));
        $book->refresh();
        $this->assertNull($book->price);
        $this->assertNull($book->acquisition_mode);
        $this->assertNull($book->acquisition_date);
    }

    public function test_partial_details_multiple_accessions_and_blank_copies(): void
    {
        $category = $this->category();
        $this->saveBook('store', $this->payload($category, ['accession' => 'TST-00001;TST-00002', 'price' => '0', 'institution' => 'Does not change category eligibility']));
        $this->assertSame(2, Book::count());
        foreach (Book::all() as $book) {
            $this->assertSame('0.00', $book->price);
            $this->assertNull($book->acquisition_mode);
            $this->assertNull($book->acquisition_date);
        }
        $this->saveBook('copy', $this->payload($category, ['accession' => 'TST-00003', 'source_book_id' => Book::first()->id]));
        $this->assertNull(Book::where('accession', 'TST-00003')->firstOrFail()->price);
    }
}
