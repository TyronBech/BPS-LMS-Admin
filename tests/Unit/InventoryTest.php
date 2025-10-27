<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\Book;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;

class InventoryTest extends TestCase
{
    use DatabaseTransactions;

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
            'checked_at' => null,
        ]);

        $response = $this->get(route('inventory.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.inventory');
        $response->assertViewHas(['books', 'conditions', 'remarks']);
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

        $response->assertRedirect(route('inventory.dashboard'));
        $this->assertDatabaseHas('bk_inventories', [
            'book_id' => $book->id,
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
            'checked_at' => null,
        ]);

        $response = $this->post(route('inventory.search'), [
            'barcode' => 'ABC123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('toast-warning', 'Book already in inventory!');
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
            'checked_at' => null,
        ]);

        $response = $this->post(route('inventory.update'), [
            'condition' => ['ABC123' => 'Good'],
            'remarks' => ['ABC123' => 'On Shelf'],
        ]);

        $response->assertRedirect(route('inventory.dashboard'));
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
            'checked_at' => null,
        ]);
        dump($inventory->id);
        $response = $this->delete(route('inventory.delete'), [
            'accession' => 'ABC123',
        ]);

        $response->assertRedirect(route('inventory.dashboard'));
        $this->assertDatabaseMissing('bk_inventories', [
            'id' => $inventory->id,
        ]);
    }
}
