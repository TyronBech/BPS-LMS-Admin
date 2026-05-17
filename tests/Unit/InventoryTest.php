<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Book;
use App\Models\Inventory;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;

class InventoryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure inventory cycle is active for the tests
        SystemSetting::updateOrCreate(
            ['key' => 'inventory_cycle_active'],
            ['value' => '1', 'description' => 'Active']
        );
    }

    #[Test]
    public function it_displays_the_inventory_index_page(): void
    {
        // Arrange
        $this->withoutMiddleware(\App\Http\Middleware\InventoryAuthentication::class);
        $user = User::factory()->create();
        $this->actingAs($user, 'admin');

        $book = Book::factory()->create();
        Inventory::factory()->create([
            'book_id' => $book->id,
            'is_scanned' => true,
            'checked_at' => null,
        ]);

        $response = $this->get(route('inventory.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.inventory');
        $response->assertViewHas(['inventory', 'conditions', 'remarks']);
    }

    #[Test]
    public function it_adds_a_book_to_inventory_if_not_existing(): void
    {
        // Arrange
        $this->withoutMiddleware(\App\Http\Middleware\InventoryAuthentication::class);
        $user = User::factory()->create();
        $this->actingAs($user, 'admin');

        $book = Book::factory()->create(['accession' => 'ABC123']);

        $response = $this->post(route('inventory.search'), [
            'barcode' => 'ABC123',
        ]);

        $response->assertRedirect(route('inventory.dashboard', ['perPage' => 10]));
        $this->assertDatabaseHas('bk_inventories', [
            'book_id' => $book->id,
            'is_scanned' => true,
            'checked_at' => null,
        ]);
    }

    #[Test]
    public function it_returns_warning_if_book_already_in_inventory(): void
    {
        // Arrange
        $this->withoutMiddleware(\App\Http\Middleware\InventoryAuthentication::class);
        $user = User::factory()->create();
        $this->actingAs($user, 'admin');

        $book = Book::factory()->create(['accession' => 'ABC123']);
        Inventory::factory()->create([
            'book_id' => $book->id,
            'is_scanned' => true,
            'checked_at' => null,
        ]);

        $response = $this->post(route('inventory.search'), [
            'barcode' => 'ABC123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('toast-warning', 'Book already scanned.');
    }

    #[Test]
    public function it_updates_book_and_inventory(): void
    {
        // Arrange
        $this->withoutMiddleware(\App\Http\Middleware\InventoryAuthentication::class);
        $user = User::factory()->create();
        $this->actingAs($user, 'admin');

        $book = Book::factory()->create(['accession' => 'ABC123']);
        Inventory::factory()->create([
            'book_id' => $book->id,
            'is_scanned' => true,
            'checked_at' => null,
        ]);

        $response = $this->post(route('inventory.update'), [
            'condition' => [$book->id => 'Good'],
            'remarks' => [$book->id => 'On Shelf'],
        ]);

        $response->assertRedirect(route('inventory.dashboard', ['perPage' => 10]));
        $this->assertDatabaseHas('bk_books', [
            'id' => $book->id,
            'condition_status' => 'Good',
            'remarks' => 'On Shelf',
        ]);
    }

    #[Test]
    public function it_deletes_inventory_entry(): void
    {
        // Arrange
        $this->withoutMiddleware(\App\Http\Middleware\InventoryAuthentication::class);
        $user = User::factory()->create();
        $this->actingAs($user, 'admin');
        
        $book = Book::factory()->create(['accession' => 'ABC123']);
        $inventory = Inventory::factory()->create([
            'book_id' => $book->id,
            'is_scanned' => true,
            'checked_at' => null,
        ]);

        $response = $this->delete(route('inventory.delete'), [
            'accession' => 'ABC123',
        ]);

        $response->assertRedirect(route('inventory.dashboard', ['perPage' => 10]));
        $this->assertDatabaseHas('bk_inventories', [
            'id' => $inventory->id,
            'is_scanned' => 0,
        ]);
    }
}
