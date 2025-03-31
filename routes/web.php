<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\AdminAuthentication;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\Auth\RegisterAdminController;
use App\Http\Controllers\Report\UserLogsController;
use App\Http\Controllers\Report\VisitorLogsController;
use App\Http\Controllers\Report\TransactionController;
use App\Http\Controllers\Report\BookCirculationController;
use App\Http\Controllers\Import\StudentImportController;
use App\Http\Controllers\Import\BookImportController;
use App\Http\Controllers\Maintenance\AdminMaintenanceController;
use App\Http\Controllers\Maintenance\BookMaintenanceController;
use App\Http\Controllers\Maintenance\UsersMaintenanceController;
use App\Http\Controllers\Roles_Permissions\RolesController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Report\CategoriesController;
use App\Http\Controllers\Report\InventoriesController;
use App\Http\Middleware\BookAuthentication;
use App\Http\Middleware\SuperAdminAuthentication;
use App\Http\Middleware\UserAuthentication;

Route::get('/', function () {
    return view('main-welcome');
});
Route::get('/transactions', [TransactionController::class, 'test'])->name('transactions');

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

// Route::prefix('roles_and_permissions')->group(function(){
//     Route::get('permissions', [PermissionsController::class, 'index'])->name('permissions.index');
// });
Route::middleware('guest', RedirectIfAuthenticated::class)->group(function () {
    Route::get('/', function () {
        return view('main-welcome');
    })->name('main-welcome');
    Route::get('login',     [AdminLoginController::class, 'index'])     ->name('login');
    Route::post('login',    [AdminLoginController::class, 'store'])     ->name('login');
    Route::get('register',  [RegisterAdminController::class, 'create']) ->name('register');
    Route::post('register', [RegisterAdminController::class, 'store']);
});
Route::prefix('admin')->middleware('auth:admin', AdminAuthentication::class)->group(function () {
    Route::get('dashboard', function(){
        return view('dashboard.dashboard');
    })->name('dashboard');
    Route::group(['prefix' => 'report'], function () {
        Route::get('user-report',       [UserLogsController::class, 'index'])           ->name('report.user');
        Route::post('user-report',      [UserLogsController::class, 'search'])          ->name('report.user-search');
        Route::get('visitor-report',    [VisitorLogsController::class, 'index'])        ->name('report.visitor');
        Route::post('visitor-report',   [VisitorLogsController::class, 'retrieve'])     ->name('report.visitor-retrieve');
        Route::get('transaction',       [TransactionController::class, 'index'])        ->name('report.transaction');
        Route::post('transaction',      [TransactionController::class, 'search'])       ->name('report.transaction-search');
        Route::get('book-circulation',  [BookCirculationController::class, 'index'])    ->name('report.book-circulation');
        Route::post('book-circulation', [BookCirculationController::class, 'search'])   ->name('report.book-circulation-search');
        Route::get('summary',           [CategoriesController::class, 'index'])         ->name('report.summary');
        Route::get('inventory-report',  [InventoriesController::class, 'index'])        ->name('report.inventory');
        Route::post('inventory-report', [InventoriesController::class, 'search'])       ->name('report.inventory-search');
    });
    
    Route::group(['prefix' => 'import'], function () {
        Route::get('students',          [StudentImportController::class, 'index'])  ->name('import.import-students');
        Route::post('students-data',    [StudentImportController::class, 'upload']) ->name('import.upload-students');
        Route::post('insert-data',      [StudentImportController::class, 'store'])  ->name('import.store-students');
        Route::get('books',             [BookImportController::class, 'index'])     ->name('import.import-books');
        Route::post('books-data',       [BookImportController::class, 'upload'])    ->name('import.upload-books');
        Route::put('insert-data',       [BookImportController::class, 'store'])     ->name('import.store-books');
    });
    Route::group(['prefix' => 'inventory'], function () {
        Route::get('inventory', [InventoryController::class, 'index'])  ->name('inventory.inventory');
        Route::post('search',   [InventoryController::class, 'search']) ->name('inventory.search');
        Route::patch('update',  [InventoryController::class, 'update']) ->name('inventory.update');
    });
    Route::group(['prefix' => 'maintenance'], function () {
        Route::prefix('books')->middleware(BookAuthentication::class)->group(function () {
            Route::get('books',             [BookMaintenanceController::class, 'index'])    ->name('maintenance.books');
            Route::get('add-book',          [BookMaintenanceController::class, 'create'])   ->name('maintenance.create-book');
            Route::post('add-book',         [BookMaintenanceController::class, 'store'])    ->name('maintenance.store-book');
            Route::get('edit-book',         [BookMaintenanceController::class, 'edit'])     ->name('maintenance.edit-book');
            Route::put('edit-book',         [BookMaintenanceController::class, 'update'])   ->name('maintenance.update-book');
            Route::post('show-books',       [BookMaintenanceController::class, 'show'])     ->name('maintenance.show-books');
            Route::delete('delete-book',    [BookMaintenanceController::class, 'destroy'])  ->name('maintenance.delete-book');
        });
        Route::prefix('users')->middleware(UserAuthentication::class)->group(function () {
            Route::get('users',             [UsersMaintenanceController::class, 'index'])           ->name('maintenance.users');
            Route::get('add-student',       [UsersMaintenanceController::class, 'create_student'])  ->name('maintenance.create-student');
            Route::post('add-student',      [UsersMaintenanceController::class, 'store_student'])   ->name('maintenance.store-student');
            Route::get('add-employee',      [UsersMaintenanceController::class, 'create_employee']) ->name('maintenance.create-employee');
            Route::post('add-employee',     [UsersMaintenanceController::class, 'store_employee'])  ->name('maintenance.store-employee');
            Route::get('edit-student',      [UsersMaintenanceController::class, 'edit_student'])    ->name('maintenance.edit-student');
            Route::put('update-student',    [UsersMaintenanceController::class, 'update_student'])  ->name('maintenance.update-student');
            Route::get('edit-employee',     [UsersMaintenanceController::class, 'edit_employee'])   ->name('maintenance.edit-employee');
            Route::put('update-employee',   [UsersMaintenanceController::class, 'update_employee']) ->name('maintenance.update-employee');
            Route::get('show-users',        [UsersMaintenanceController::class, 'show'])            ->name('maintenance.show-users');
            Route::delete('delete-user',    [UsersMaintenanceController::class, 'destroy'])         ->name('maintenance.delete-user');
        });
        Route::prefix('admin-management')->middleware(SuperAdminAuthentication::class)->group(function () {
            Route::get('admins',            [AdminMaintenanceController::class, 'index'])   ->name('maintenance.admins');
            Route::get('add-admin',         [AdminMaintenanceController::class, 'create'])  ->name('maintenance.create-admin');
            Route::post('add-admin',        [AdminMaintenanceController::class, 'store'])   ->name('maintenance.store-admin');
            Route::post('search-admin',     [AdminMaintenanceController::class, 'search_user'])  ->name('maintenance.search-user');
            Route::post('search',           [AdminMaintenanceController::class, 'search_admin'])  ->name('maintenance.search-admin');
            Route::get('edit-admin',        [AdminMaintenanceController::class, 'edit'])    ->name('maintenance.edit-admin');
            Route::put('edit-admin',        [AdminMaintenanceController::class, 'update'])  ->name('maintenance.update-admin');
            Route::get('show-admins',       [AdminMaintenanceController::class, 'show'])    ->name('maintenance.show-admins');
            Route::delete('delete-admin',   [AdminMaintenanceController::class, 'destroy']) ->name('maintenance.delete-admin');
            Route::prefix('roles-and-permissions')->group(function () {
                Route::get('management',        [RolesController::class, 'index'])      ->name('maintenance.roles-and-permissions.management');
                Route::get('add-role',          [RolesController::class, 'create'])     ->name('maintenance.roles-and-permissions.create-role');
                Route::post('add-role',         [RolesController::class, 'store'])      ->name('maintenance.roles-and-permissions.store-role');
                Route::get('edit-role',         [RolesController::class, 'edit'])       ->name('maintenance.roles-and-permissions.edit-role');
                Route::put('edit-role',         [RolesController::class, 'update'])     ->name('maintenance.roles-and-permissions.update-role');
                Route::delete('delete-role',    [RolesController::class, 'destroy'])    ->name('maintenance.roles-and-permissions.delete-role');
            });
        });
    });
    Route::post('logout', [AdminLoginController::class, 'destroy'])->name('admin.logout');
});
require __DIR__.'/auth.php';
