<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Book;
use App\Models\User;
use App\Models\Transaction;
use App\Enum\PermissionsEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CategoriesCountAutomationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /**
     * Test counting automation when books are created, updated, soft-deleted, restored, and category changed.
     */
    public function test_category_counting_automation(): void
    {
        // 1. Create a category
        $category1 = Category::create([
            'legend' => 'CAT1',
            'name' => 'Category One',
            'category_type' => 'Print',
            'previous_inventory' => 5,
            'newly_acquired' => 0,
            'discarded' => 0,
            'present_inventory' => 5,
            'borrow_duration_days' => 7,
            'educational_level' => ['Elementary'],
        ]);

        $category2 = Category::create([
            'legend' => 'CAT2',
            'name' => 'Category Two',
            'category_type' => 'Print',
            'previous_inventory' => 3,
            'newly_acquired' => 0,
            'discarded' => 0,
            'present_inventory' => 3,
            'borrow_duration_days' => 7,
            'educational_level' => ['Elementary'],
        ]);

        // 2. Create a book in category 1 (Acquisition)
        $book = Book::create([
            'accession' => 'CAT1-000001',
            'title' => 'Test Book',
            'category_id' => $category1->id,
            'book_type' => 'Print',
            'remarks' => 'On Shelf',
            'availability_status' => 'Available',
            'condition_status' => 'Good',
        ]);

        $category1->refresh();
        $this->assertEquals(1, $category1->newly_acquired, "newly_acquired should increment on book create");
        $this->assertEquals(6, $category1->present_inventory, "present_inventory should increment on book create");

        // 3. Soft delete the book (Discard)
        $book->delete();

        $category1->refresh();
        $this->assertEquals(1, $category1->discarded, "discarded should increment on book soft delete");
        $this->assertEquals(5, $category1->present_inventory, "present_inventory should decrement on book soft delete");

        // 4. Restore the book (Reverse Discard)
        $book->restore();

        $category1->refresh();
        $this->assertEquals(0, $category1->discarded, "discarded should decrement on book restore");
        $this->assertEquals(6, $category1->present_inventory, "present_inventory should increment on book restore");

        // 5. Change category from category 1 to category 2
        $book->update([
            'category_id' => $category2->id,
        ]);

        $category1->refresh();
        $category2->refresh();

        // Old category present_inventory decrements, newly_acquired doesn't change
        $this->assertEquals(5, $category1->present_inventory, "old category present_inventory should decrement on category change");
        $this->assertEquals(1, $category1->newly_acquired, "old category newly_acquired should remain unchanged");

        // New category present_inventory increments, newly_acquired doesn't change
        $this->assertEquals(4, $category2->present_inventory, "new category present_inventory should increment on category change");
        $this->assertEquals(0, $category2->newly_acquired, "new category newly_acquired should remain unchanged");

        // 6. Hard delete the active book (category 2)
        $book->forceDelete();

        $category2->refresh();
        $this->assertEquals(3, $category2->present_inventory, "present_inventory should decrement on book hard delete");
    }

    /**
     * Test matrix update functionality.
     */
    public function test_matrix_update_and_rollover(): void
    {
        // 1. Create a category with initial state
        $category = Category::create([
            'legend' => 'ROLL',
            'name' => 'Roll Category',
            'category_type' => 'Print',
            'previous_inventory' => 10,
            'newly_acquired' => 4,
            'discarded' => 1,
            'present_inventory' => 13,
            'borrow_duration_days' => 7,
            'educational_level' => ['Elementary'],
        ]);

        $admin = User::factory()->create();
        $admin->givePermissionTo(PermissionsEnum::VIEW_SUMMARY_REPORTS->value);

        // 2. Call the update route
        $response = $this->actingAs($admin, 'admin')
            ->post(route('report.summary-update'));

        $response->assertRedirect();
        $response->assertSessionHas('toast-success', 'Successfully updated');

        // 3. Verify category rolled over correctly
        $category->refresh();
        $this->assertEquals(13, $category->previous_inventory, "previous_inventory should become the old present_inventory");
        $this->assertEquals(0, $category->newly_acquired, "newly_acquired should reset to 0");
        $this->assertEquals(0, $category->discarded, "discarded should reset to 0");
        $this->assertEquals(13, $category->present_inventory, "present_inventory should equal previous_inventory after reset");

        // 4. Verify archive entry was created
        $archive = DB::table('archive_categories')
            ->where('category_id', $category->id)
            ->first();

        $this->assertNotNull($archive);
        $this->assertEquals(10, $archive->previous_inventory);
        $this->assertEquals(4, $archive->newly_acquired);
        $this->assertEquals(1, $archive->discarded);
        $this->assertEquals(13, $archive->present_inventory);

        // Verify token file was stored
        Storage::disk('local')->assertExists('private/categories_rollback_timestamp.txt');
    }

    /**
     * Test remarks-based dynamic counters (lost and paid for, lost and replaced, unreturned, missing).
     */
    public function test_remarks_based_dynamic_counters(): void
    {
        // 1. Create a category
        $category = Category::create([
            'legend' => 'DYN',
            'name' => 'Dynamic Remarks Category',
            'category_type' => 'Print',
            'previous_inventory' => 0,
            'newly_acquired' => 0,
            'discarded' => 0,
            'present_inventory' => 0,
            'borrow_duration_days' => 7,
            'educational_level' => ['Elementary'],
        ]);

        // 2. Create a book in this category
        $book = Book::create([
            'accession' => 'DYN-000001',
            'title' => 'Dynamic Remarks Book',
            'category_id' => $category->id,
            'book_type' => 'Print',
            'remarks' => 'On Shelf',
            'availability_status' => 'Available',
            'condition_status' => 'Good',
        ]);

        $admin = User::factory()->create();
        $admin->givePermissionTo(PermissionsEnum::VIEW_SUMMARY_REPORTS->value);

        // Fetch index and check all remarks-based counters are 0
        $response = $this->actingAs($admin, 'admin')
            ->get(route('report.summary'));
        $response->assertStatus(200);
        $data = $response->viewData('data');
        $catData = $data->where('name', 'Dynamic Remarks Category')->first();
        
        $this->assertNotNull($catData);
        $this->assertEquals(0, $catData->lost_and_paid_for);
        $this->assertEquals(0, $catData->lost_and_replaced);
        $this->assertEquals(0, $catData->unreturned);
        $this->assertEquals(0, $catData->missing);

        // 3. Update remarks to 'lost and replaced'
        $book->update(['remarks' => 'Lost And Replaced']);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('report.summary'));
        $catData = $response->viewData('data')->where('name', 'Dynamic Remarks Category')->first();
        $this->assertEquals(1, $catData->lost_and_replaced);
        $this->assertEquals(0, $catData->lost_and_paid_for);

        // 4. Update remarks to 'lost and paid for'
        $book->update(['remarks' => 'Lost And Paid For']);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('report.summary'));
        $catData = $response->viewData('data')->where('name', 'Dynamic Remarks Category')->first();
        $this->assertEquals(0, $catData->lost_and_replaced);
        $this->assertEquals(1, $catData->lost_and_paid_for);

        // 5. Update availability status to 'Borrowed' and remarks to 'Unreturned'
        $book->update([
            'remarks' => 'Unreturned',
            'availability_status' => 'Borrowed',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('report.summary'));
        $catData = $response->viewData('data')->where('name', 'Dynamic Remarks Category')->first();
        $this->assertEquals(1, $catData->unreturned);

        // 6. Update remarks to 'missing'
        $book->update(['remarks' => 'Missing']);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('report.summary'));
        $catData = $response->viewData('data')->where('name', 'Dynamic Remarks Category')->first();
        $this->assertEquals(1, $catData->missing);
    }

    /**
     * Test the borrowed and reserved helper, relationship, and scope on the Book model.
     */
    public function test_borrowed_and_reserved_methods(): void
    {
        // 1. Create a category and book
        $category = Category::create([
            'legend' => 'TST',
            'name' => 'Test Category',
            'category_type' => 'Print',
            'previous_inventory' => 0,
            'newly_acquired' => 0,
            'discarded' => 0,
            'present_inventory' => 0,
            'borrow_duration_days' => 7,
            'educational_level' => ['Elementary'],
        ]);

        $book = Book::create([
            'accession' => 'TST-000001',
            'title' => 'Test Book for Borrow & Reserve',
            'category_id' => $category->id,
            'book_type' => 'Print',
            'remarks' => 'On Shelf',
            'availability_status' => 'Available',
            'condition_status' => 'Good',
        ]);

        $user = User::factory()->create();

        // Initially book is not borrowed and not reserved
        $this->assertFalse($book->is_borrowed_and_reserved);
        $this->assertNull($book->activeBorrow);
        $this->assertNull($book->activeReservation);

        // 2. Make it borrowed
        $book->update(['availability_status' => 'Borrowed']);
        $borrowTx = Transaction::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'transaction_type' => 'Borrowed',
            'status' => 'Borrowed',
            'date_borrowed' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $book->refresh();
        $this->assertFalse($book->is_borrowed_and_reserved, "Should be false as there is no reservation yet");
        $this->assertNotNull($book->activeBorrow);
        $this->assertEquals($borrowTx->id, $book->activeBorrow->id);
        $this->assertNull($book->activeReservation);

        // 3. Someone reserves it (reservation approved but waiting for return, status = 'Reserved')
        $reserveTx = Transaction::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'transaction_type' => 'Reserved',
            'status' => 'Reserved',
            'reserved_date' => now()->format('Y-m-d'),
        ]);

        $book->refresh();
        $this->assertTrue($book->is_borrowed_and_reserved, "Should be true since it is Borrowed and has an active Reservation");
        $this->assertNotNull($book->activeReservation);
        $this->assertEquals($reserveTx->id, $book->activeReservation->id);

        // 4. Verify the scope filters it correctly
        $scopedBooks = Book::borrowedAndReserved()->get();
        $this->assertTrue($scopedBooks->contains('id', $book->id));

        // 5. Complete borrow, book availability is set to Reserved, reserve transaction becomes Available for pick up
        $borrowTx->update(['status' => 'Completed', 'return_date' => now()->format('Y-m-d')]);
        $reserveTx->update(['status' => 'Available for pick up', 'pickup_deadline' => now()->addDays(3)->format('Y-m-d')]);
        $book->update(['availability_status' => 'Reserved']);

        $book->refresh();
        // Since book availability_status is 'Reserved' (not 'Borrowed'), is_borrowed_and_reserved should be false
        $this->assertFalse($book->is_borrowed_and_reserved);
        // Active borrow is now null
        $this->assertNull($book->activeBorrow);
        // Active reservation is still active (Available for pick up)
        $this->assertNotNull($book->activeReservation);
        $this->assertEquals($reserveTx->id, $book->activeReservation->id);

        // Verify the scope does not include it anymore since availability_status is no longer Borrowed
        $scopedBooks = Book::borrowedAndReserved()->get();
        $this->assertFalse($scopedBooks->contains('id', $book->id));
    }
}
