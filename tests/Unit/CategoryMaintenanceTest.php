<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Category;
use App\Models\User;
use App\Enum\PermissionsEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CategoryMaintenanceTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo(PermissionsEnum::ADD_BOOKS->value);
        $this->admin->givePermissionTo(PermissionsEnum::EDIT_BOOKS->value);
        $this->admin->givePermissionTo(PermissionsEnum::VIEW_BOOK_CATEGORIES_MAINTENANCE->value);
        $this->admin->givePermissionTo(PermissionsEnum::ADD_CATEGORIES->value);
        $this->admin->givePermissionTo(PermissionsEnum::EDIT_CATEGORIES->value);
    }

    /**
     * Test creating a category with educational levels.
     */
    public function test_store_category_with_educational_levels(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('maintenance.store-category'), [
                'name' => 'Valid Category One',
                'legend' => 'VAL1',
                'category_type' => 'Print',
                'educational_level' => ['Elementary', 'Junior High School'],
                'can_borrow' => '1',
                'borrow_duration_days_add' => 5,
            ]);

        $response->assertRedirect(route('maintenance.categories'));
        $response->assertSessionHas('toast-success', 'Category created successfully.');

        $this->assertDatabaseHas('bk_categories', [
            'name' => 'Valid Category One',
            'legend' => 'VAL1',
            'category_type' => 'Print',
            'borrow_duration_days' => 5,
        ]);

        $category = Category::where('name', 'Valid Category One')->first();
        $this->assertEquals(['Elementary', 'Junior High School'], $category->educational_level);
    }

    /**
     * Test creating a category WITHOUT educational levels (optional field).
     */
    public function test_store_category_without_educational_levels(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('maintenance.store-category'), [
                'name' => 'Optional Level Category',
                'legend' => 'OPTL',
                'category_type' => 'Non-print',
                'can_borrow' => '0',
            ]);

        $response->assertRedirect(route('maintenance.categories'));
        $response->assertSessionHas('toast-success', 'Category created successfully.');

        $this->assertDatabaseHas('bk_categories', [
            'name' => 'Optional Level Category',
            'legend' => 'OPTL',
            'category_type' => 'Non-print',
            'borrow_duration_days' => 0,
            'educational_level' => null,
        ]);
    }

    /**
     * Test updating a category with educational levels.
     */
    public function test_update_category_with_educational_levels(): void
    {
        $category = Category::create([
            'name' => 'Old Category Name',
            'legend' => 'OLD1',
            'category_type' => 'Print',
            'borrow_duration_days' => 3,
            'educational_level' => ['Elementary'],
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('maintenance.update-category'), [
                'edit_category_id' => $category->id,
                'name' => 'Updated Category Name',
                'legend' => 'UPD1',
                'category_type' => 'E-books',
                'educational_level' => ['Junior High School', 'Senior High School'],
                'can_borrow_edit' => '1',
                'borrow_duration_days_edit' => 10,
            ]);

        $response->assertRedirect(route('maintenance.categories'));
        $response->assertSessionHas('toast-success', 'Category updated successfully.');

        $category->refresh();
        $this->assertEquals('Updated Category Name', $category->name);
        $this->assertEquals('UPD1', $category->legend);
        $this->assertEquals('E-books', $category->category_type);
        $this->assertEquals(10, $category->borrow_duration_days);
        $this->assertEquals(['Junior High School', 'Senior High School'], $category->educational_level);
    }

    /**
     * Test updating a category to have NO educational levels (optional field).
     */
    public function test_update_category_without_educational_levels(): void
    {
        $category = Category::create([
            'name' => 'Optional Update Category',
            'legend' => 'OPTU',
            'category_type' => 'Print',
            'borrow_duration_days' => 3,
            'educational_level' => ['Elementary'],
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('maintenance.update-category'), [
                'edit_category_id' => $category->id,
                'name' => 'Optional Update Category',
                'legend' => 'OPTU',
                'category_type' => 'Print',
                'educational_level' => null,
                'can_borrow_edit' => '0',
            ]);

        $response->assertRedirect(route('maintenance.categories'));
        $response->assertSessionHas('toast-success', 'Category updated successfully.');

        $category->refresh();
        $this->assertNull($category->educational_level);
        $this->assertEquals(0, $category->borrow_duration_days);
    }
}
