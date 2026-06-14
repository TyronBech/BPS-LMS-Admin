<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Category;
use App\Models\User;
use App\Enum\PermissionsEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CategoriesRollbackTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /**
     * Test rollback is not allowed when no token exists.
     */
    public function test_rollback_requires_token(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(PermissionsEnum::VIEW_SUMMARY_REPORTS->value);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('report.summary-rollback'));

        $response->assertRedirect();
        $response->assertSessionHas('toast-warning', 'No rollback data available.');
    }

    /**
     * Test successful rollback restoring data.
     */
    public function test_rollback_success(): void
    {
        // 1. Create a category
        $category = Category::create([
            'legend' => 'TEST',
            'name' => 'Test Category',
            'category_type' => 'Print',
            'previous_inventory' => 10,
            'newly_acquired' => 5,
            'discarded' => 2,
            'present_inventory' => 13,
            'borrow_duration_days' => 7,
            'educational_level' => ['Elementary'],
        ]);

        // 2. Insert into archive_categories mimicking pre-update state
        $archivedAt = now()->toDateTimeString();
        DB::table('archive_categories')->insert([
            'category_id' => $category->id,
            'legend' => $category->legend,
            'name' => $category->name,
            'previous_inventory' => 8, // Pre-update values
            'newly_acquired' => 4,
            'discarded' => 1,
            'present_inventory' => 11,
            'archived_at' => $archivedAt,
        ]);

        // 3. Update the category to simulate that an update happened
        $category->update([
            'previous_inventory' => 11,
            'newly_acquired' => 0,
            'discarded' => 0,
        ]);

        // 4. Create the rollback token file in fake storage
        Storage::disk('local')->put('private/categories_rollback_timestamp.txt', $archivedAt);

        // 5. Authenticate and call rollback route
        $admin = User::factory()->create();
        $admin->givePermissionTo(PermissionsEnum::VIEW_SUMMARY_REPORTS->value);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('report.summary-rollback'));

        // 6. Assertions
        $response->assertRedirect();
        $response->assertSessionHas('toast-success', 'Successfully rolled back to the previous version.');

        // Verify categories are reverted
        $category->refresh();
        $this->assertEquals(8, $category->previous_inventory);
        $this->assertEquals(4, $category->newly_acquired);
        $this->assertEquals(1, $category->discarded);
        $this->assertEquals(11, $category->present_inventory);

        // Verify archive record is deleted
        $this->assertDatabaseMissing('archive_categories', [
            'category_id' => $category->id,
            'archived_at' => $archivedAt,
        ]);

        // Verify token file is deleted
        Storage::disk('local')->assertMissing('private/categories_rollback_timestamp.txt');
    }
}
