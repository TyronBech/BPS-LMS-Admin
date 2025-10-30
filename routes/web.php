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
use App\Http\Controllers\Import\FacultyStaffImportController;
use App\Http\Controllers\Maintenance\AdminMaintenanceController;
use App\Http\Controllers\Maintenance\BookMaintenanceController;
use App\Http\Controllers\Maintenance\UsersMaintenanceController;
use App\Http\Controllers\Maintenance\CategoryMaintenanceController;
use App\Http\Controllers\Roles_Permissions\RolesController;
use App\Http\Controllers\Maintenance\PrivilegeMaintenanceController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Report\CategoriesController;
use App\Http\Controllers\Report\InventoriesController;
use App\Http\Controllers\Analytics\FetchDataController;
use App\Http\Controllers\Report\ComputerUseController;
use App\Http\Controllers\Report\PenaltiesController;
use App\Http\Controllers\Report\AuditTrailController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Backup\BackupController;
use App\Http\Controllers\Maintenance\PenaltyRuleController;
use App\Http\Controllers\Maintenance\TransactionMaintenanceController;
use App\Http\Middleware\BackupAuthentication;
use App\Http\Middleware\BookAuthentication;
use App\Http\Middleware\SuperAdminAuthentication;
use App\Http\Middleware\UserAuthentication;
use App\Http\Middleware\InventoryAuthentication;
use App\Http\Middleware\ReportAuthentication;
use App\Http\Middleware\PrivilegeAuthentication;
use App\Http\Middleware\BookCategoriesAuthentication;
use App\Http\Middleware\ImportAuthentication;
use App\Http\Middleware\MaintenanceAuthentication;
use App\Http\Middleware\PenaltyRuleMiddleware;
use App\Http\Middleware\PreventBackHistory;

Route::get('/', function () {
    return view('main-welcome');
});
Route::middleware('guest', RedirectIfAuthenticated::class, PreventBackHistory::class)->group(function () {
    Route::get('/', function () {
        return view('main-welcome');
    })->name('main-welcome');
    Route::get('login',     [AdminLoginController::class, 'index'])     ->name('login');
    Route::post('login',    [AdminLoginController::class, 'store'])     ->name('login');
    Route::get('register',  [RegisterAdminController::class, 'create']) ->name('register');
    Route::post('register', [RegisterAdminController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});
Route::prefix('admin')->middleware('auth:admin', AdminAuthentication::class)->group(function () {
    Route::get('dashboard', function(){
        return view('dashboard.dashboard');
    })->name('dashboard');
    Route::get('test', function() {
        return view('dashboard.dashboard');
    })->name('test');
    Route::get('function-test', [FetchDataController::class, 'mostBorrowedStudents'])->name('function-test');
    Route::post('timeout-all-users', [FetchDataController::class, 'timeoutAllUsers'])->name('timeout-all-users');
    Route::get('profile',   [ProfileController::class, 'index']) ->name('profile');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::prefix('analytics')->group(function () {
        Route::get('current-users',             [FetchDataController::class, 'fetchCurrentTimeInUsers'])    ->name('fetch-current-count');
        Route::get('monthly-users',             [FetchDataController::class, 'fetchMonthlyUsers'])          ->name('fetch-monthly-count');
        Route::get('total-books',               [FetchDataController::class, 'totalBooks'])                 ->name('fetch-book-count');
        Route::get('transaction-history',       [FetchDataController::class, 'fetchTransactionHistory'])    ->name('fetch-transaction-history');
        Route::get('yearly-aquired-books',      [FetchDataController::class, 'fetchYearlyAquiredBooks'])    ->name('fetch-yearly-aquired-books');
        Route::get('registered-users',          [FetchDataController::class, 'fetchRegisteredUsers'])       ->name('fetch-registered-users');
        Route::get('most-visited-students',     [FetchDataController::class, 'mostVisitedStudents'])        ->name('fetch-most-visited-students');
        Route::get('most-borrowed-students',    [FetchDataController::class, 'mostBorrowedStudents'])       ->name('fetch-most-borrowed-students');
        Route::get('top-books-borrowed',        [FetchDataController::class, 'topBooksBorrowed'])           ->name('fetch-top-books-borrowed');
        Route::get('top-categories-borrowed',   [FetchDataController::class, 'topCategoriesBorrowed'])      ->name('fetch-top-categories-borrowed');
    });
    Route::prefix('report')->middleware(ReportAuthentication::class)->group(function () {
        Route::get('user-report',       [UserLogsController::class, 'index'])           ->name('report.user');
        Route::post('user-report',      [UserLogsController::class, 'search'])          ->name('report.user-search');
        Route::get('user-graph',        [UserLogsController::class, 'graph'])           ->name('report.user-graph');
        Route::post('export-graph',     [UserLogsController::class, 'exportGraph'])     ->name('report.graph-export-pdf');
        Route::get('computer-use',      [ComputerUseController::class, 'index'])        ->name('report.computer-use');
        Route::post('computer-use',     [ComputerUseController::class, 'search'])       ->name('report.computer-use-search');
        Route::get('visitor-report',    [VisitorLogsController::class, 'index'])        ->name('report.visitor');
        Route::post('visitor-report',   [VisitorLogsController::class, 'retrieve'])     ->name('report.visitor-retrieve');
        Route::get('transaction',       [TransactionController::class, 'index'])        ->name('report.transaction');
        Route::post('transaction',      [TransactionController::class, 'search'])       ->name('report.transaction-search');
        Route::get('book-circulation',  [BookCirculationController::class, 'index'])    ->name('report.book-circulation');
        Route::post('book-circulation', [BookCirculationController::class, 'search'])   ->name('report.book-circulation-search');
        Route::get('summary',           [CategoriesController::class, 'index'])         ->name('report.summary');
        Route::post('summary',          [CategoriesController::class, 'export'])        ->name('report.summary-export');
        Route::post('update-summary',   [CategoriesController::class, 'update'])        ->name('report.summary-update');
        Route::get('inventory-report',  [InventoriesController::class, 'index'])        ->name('report.inventory');
        Route::post('inventory-report', [InventoriesController::class, 'search'])       ->name('report.inventory-search');
        Route::get('penalties',         [PenaltiesController::class, 'index'])          ->name('report.penalties');
        Route::post('penalties',        [PenaltiesController::class, 'search'])         ->name('report.penalties-search');
        Route::get('audit-trail',       [AuditTrailController::class, 'index'])         ->name('report.audit-trail');
        Route::post('audit-trail',       [AuditTrailController::class, 'search'])       ->name('report.audit-trail-search');
    });
    Route::prefix('import')->middleware(ImportAuthentication::class)->group(function () {
        Route::get('students',                      [StudentImportController::class, 'index'])                  ->name('import.import-students');
        Route::post('students',                     [StudentImportController::class, 'upload'])                 ->name('import.upload-students');
        Route::post('store-students',               [StudentImportController::class, 'store'])                  ->name('import.store-students');
        Route::get('students/download-template',    [StudentImportController::class, 'downloadTemplate'])       ->name('import.download-students-template');
        Route::get('faculties-staffs',              [FacultyStaffImportController::class, 'index'])             ->name('import.import-faculties-staffs');
        Route::post('faculties-staffs',             [FacultyStaffImportController::class, 'upload'])            ->name('import.upload-faculties-staffs');
        Route::post('store-faculties-staffs',       [FacultyStaffImportController::class, 'store'])             ->name('import.store-faculties-staffs');
        Route::get('employees/download-template',   [FacultyStaffImportController::class, 'downloadTemplate'])  ->name('import.download-employee-template');
        Route::get('books',                         [BookImportController::class, 'index'])                     ->name('import.import-books');
        Route::post('books',                        [BookImportController::class, 'upload'])                    ->name('import.upload-books');
        Route::post('store-books',                  [BookImportController::class, 'store'])                     ->name('import.store-books');
        Route::get('books/download-template',       [BookImportController::class, 'downloadTemplate'])          ->name('import.download-book-template');
    });
    Route::prefix('inventory')->middleware(InventoryAuthentication::class)->group(function () {
        Route::get('dashboard', [InventoryController::class, 'index'])  ->name('inventory.dashboard');
        Route::post('search',   [InventoryController::class, 'search']) ->name('inventory.search');
        Route::patch('update',  [InventoryController::class, 'update']) ->name('inventory.update');
        Route::delete('delete', [InventoryController::class, 'destroy'])->name('inventory.delete');
    });
    Route::prefix('maintenance')->middleware(MaintenanceAuthentication::class)->group(function () {
        Route::prefix('books')->middleware(BookAuthentication::class)->group(function () {
            Route::get('books',                 [BookMaintenanceController::class, 'index'])            ->name('maintenance.books');
            Route::get('add-book',              [BookMaintenanceController::class, 'create'])           ->name('maintenance.create-book');
            Route::post('add-book',             [BookMaintenanceController::class, 'store'])            ->name('maintenance.store-book');
            Route::get('edit-book',             [BookMaintenanceController::class, 'edit'])             ->name('maintenance.edit-book');
            Route::put('edit-book',             [BookMaintenanceController::class, 'update'])           ->name('maintenance.update-book');
            Route::post('copy-book',            [BookMaintenanceController::class, 'copy'])             ->name('maintenance.copy-book');
            Route::get('show-books',            [BookMaintenanceController::class, 'show'])             ->name('maintenance.show-books');
            Route::get('show-book',             [BookMaintenanceController::class, 'view'])             ->name('maintenance.view-book');
            Route::get('category',              [BookMaintenanceController::class, 'search_category'])  ->name('maintenance.search-category');
            Route::get('export-barcode',        [BookMaintenanceController::class, 'export_barcode'])   ->name('maintenance.export-barcode');
            Route::delete('delete-book',        [BookMaintenanceController::class, 'destroy'])          ->name('maintenance.delete-book');
            Route::delete('bulk-delete-book',   [BookMaintenanceController::class, 'bulkDelete'])       ->name('maintenance.bulk-delete-book');
            Route::prefix('categories')->middleware(BookCategoriesAuthentication::class)->group(function () {
                Route::get('categories',            [CategoryMaintenanceController::class, 'index'])    ->name('maintenance.categories');
                Route::post('add-category',         [CategoryMaintenanceController::class, 'store'])    ->name('maintenance.store-category');
                Route::put('edit-category',         [CategoryMaintenanceController::class, 'update'])   ->name('maintenance.update-category');
                Route::delete('delete-category',    [CategoryMaintenanceController::class, 'destroy'])  ->name('maintenance.delete-category');
            });
        });
        Route::prefix('users')->middleware(UserAuthentication::class)->group(function () {
            Route::get('users',                     [UsersMaintenanceController::class, 'index'])                   ->name('maintenance.users');
            Route::get('view-student',              [UsersMaintenanceController::class, 'view_student'])            ->name('maintenance.view-student');
            Route::get('view-employee',             [UsersMaintenanceController::class, 'view_employee'])           ->name('maintenance.view-employee');
            Route::get('add-student',               [UsersMaintenanceController::class, 'create_student'])          ->name('maintenance.create-student');
            Route::post('add-student',              [UsersMaintenanceController::class, 'store_student'])           ->name('maintenance.store-student');
            Route::get('add-employee',              [UsersMaintenanceController::class, 'create_employee'])         ->name('maintenance.create-employee');
            Route::post('add-employee',             [UsersMaintenanceController::class, 'store_employee'])          ->name('maintenance.store-employee');
            Route::get('edit-student',              [UsersMaintenanceController::class, 'edit_student'])            ->name('maintenance.edit-student');
            Route::put('update-student',            [UsersMaintenanceController::class, 'update_student'])          ->name('maintenance.update-student');
            Route::get('edit-employee',             [UsersMaintenanceController::class, 'edit_employee'])           ->name('maintenance.edit-employee');
            Route::put('update-employee',           [UsersMaintenanceController::class, 'update_employee'])         ->name('maintenance.update-employee');
            Route::get('edit-visitor',              [UsersMaintenanceController::class, 'edit_visitor'])            ->name('maintenance.edit-visitor');
            Route::put('update-visitor',            [UsersMaintenanceController::class, 'update_visitor'])          ->name('maintenance.update-visitor');
            Route::get('show-users',                [UsersMaintenanceController::class, 'show'])                    ->name('maintenance.show-users');
            Route::delete('delete-user',            [UsersMaintenanceController::class, 'destroy'])                 ->name('maintenance.delete-user');
            Route::delete('bulk-delete-student',    [UsersMaintenanceController::class, 'bulk_delete_student'])     ->name('maintenance.bulk-delete-student');
            Route::delete('bulk-delete-employee',   [UsersMaintenanceController::class, 'bulk_delete_employee'])    ->name('maintenance.bulk-delete-employee');
            Route::delete('bulk-delete-visitor',    [UsersMaintenanceController::class, 'bulk_delete_visitor'])     ->name('maintenance.bulk-delete-visitor');
        });
        Route::prefix('privileges')->middleware(PrivilegeAuthentication::class)->group(function () {
            Route::get('privileges',            [PrivilegeMaintenanceController::class, 'index'])               ->name('maintenance.privileges');
            Route::post('add-privilege',        [PrivilegeMaintenanceController::class, 'store'])               ->name('maintenance.store-privilege');
            Route::put('edit-privilege',        [PrivilegeMaintenanceController::class, 'update'])              ->name('maintenance.update-privilege');
            Route::delete('delete-privilege',   [PrivilegeMaintenanceController::class, 'destroy'])             ->name('maintenance.delete-privilege');
            // Route::get('add-privilege',         [PrivilegeMaintenanceController::class, 'create'])              ->name('maintenance.create-privilege');
            // Route::post('search-privilege',     [PrivilegeMaintenanceController::class, 'search_privilege'])    ->name('maintenance.search-privilege');
            // Route::get('edit-privilege',        [PrivilegeMaintenanceController::class, 'edit'])                ->name('maintenance.edit-privilege');
            // Route::get('show-privileges',       [PrivilegeMaintenanceController::class, 'show'])                ->name('maintenance.show-privileges');
        });
        Route::prefix('penalty-rules')->middleware(PenaltyRuleMiddleware::class)->group(function () {
            Route::get('penalty-rules',             [PenaltyRuleController::class, 'index'])    ->name('maintenance.penalty-rules');
            Route::post('add-penalty-rule',         [PenaltyRuleController::class, 'store'])    ->name('maintenance.store-penalty-rule');
            Route::put('edit-penalty-rule',         [PenaltyRuleController::class, 'update'])   ->name('maintenance.update-penalty-rule');
            Route::delete('delete-penalty-rule',    [PenaltyRuleController::class, 'destroy'])  ->name('maintenance.delete-penalty-rule');
        });
        Route::prefix('transactions')->middleware(SuperAdminAuthentication::class)->group(function () {
            Route::get('transactions',          [TransactionMaintenanceController::class, 'index'])     ->name('maintenance.transactions');
            Route::get('show-transactions',     [TransactionMaintenanceController::class, 'show'])      ->name('maintenance.show-transactions');
            Route::get('retrieve-transaction',  [TransactionMaintenanceController::class, 'retrieve'])  ->name('maintenance.retrieve-transaction');
            Route::put('edit-transaction',      [TransactionMaintenanceController::class, 'update'])    ->name('maintenance.update-transaction');
            Route::delete('delete-transaction', [TransactionMaintenanceController::class, 'destroy'])   ->name('maintenance.delete-transaction');
        });
        Route::prefix('admin-management')->middleware(SuperAdminAuthentication::class)->group(function () {
            Route::get('admins',            [AdminMaintenanceController::class, 'index'])           ->name('maintenance.admins');
            Route::get('add-admin',         [AdminMaintenanceController::class, 'create'])          ->name('maintenance.create-admin');
            Route::post('add-admin',        [AdminMaintenanceController::class, 'store'])           ->name('maintenance.store-admin');
            Route::post('search-admin',     [AdminMaintenanceController::class, 'search_user'])     ->name('maintenance.search-user');
            Route::post('search',           [AdminMaintenanceController::class, 'search_admin'])    ->name('maintenance.search-admin');
            Route::get('edit-admin',        [AdminMaintenanceController::class, 'edit'])            ->name('maintenance.edit-admin');
            Route::put('edit-admin',        [AdminMaintenanceController::class, 'update'])          ->name('maintenance.update-admin');
            Route::get('show-admins',       [AdminMaintenanceController::class, 'show'])            ->name('maintenance.show-admins');
            Route::delete('delete-admin',   [AdminMaintenanceController::class, 'destroy'])         ->name('maintenance.delete-admin');
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
    Route::prefix('backup')->middleware(BackupAuthentication::class)->group(function () {
        Route::get('backup',    [BackupController::class, 'index'])     ->name('backup.index');
        Route::post('create',   [BackupController::class, 'create'])    ->name('backup.create');
        //Route::post('restore',  [BackupController::class, 'restore'])   ->name('backup.restore');
        Route::post('download', [BackupController::class, 'download'])  ->name('backup.download');
        Route::delete('delete', [BackupController::class, 'destroy'])   ->name('backup.destroy');
    });
    Route::post('logout', [AdminLoginController::class, 'destroy'])->name('admin.logout');
});
Route::fallback(function () {
    return view('layouts.fallback');
});
