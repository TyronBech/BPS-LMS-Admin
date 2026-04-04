<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\AdminAuthentication;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
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
use App\Http\Controllers\Maintenance\ReservationExtensionController;
use App\Http\Controllers\Maintenance\ReservationStatus;
use App\Http\Controllers\Maintenance\TransactionMaintenanceController;
use App\Http\Controllers\Settings\UISettingController;
use App\Http\Middleware\AuditReportAuthentication;
use App\Http\Middleware\BackupAuthentication;
use App\Http\Middleware\BookAuthentication;
use App\Http\Middleware\SuperAdminAuthentication;
use App\Http\Middleware\UserAuthentication;
use App\Http\Middleware\InventoryAuthentication;
use App\Http\Middleware\ReportAuthentication;
use App\Http\Middleware\PrivilegeAuthentication;
use App\Http\Middleware\BookCategoriesAuthentication;
use App\Http\Middleware\BookImportAuthentication;
use App\Http\Middleware\EmployeeImportAuthentication;
use App\Http\Middleware\ImportAuthentication;
use App\Http\Middleware\MaintenanceAuthentication;
use App\Http\Middleware\PenaltyRuleMiddleware;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\ReservationAuthentication;
use App\Http\Middleware\StudentImportAuthentication;
use App\Http\Middleware\UISettingAuthentication;

Route::middleware(['guest', RedirectIfAuthenticated::class, PreventBackHistory::class])->group(function () {
    Route::get('/', function () {
        return view('main-welcome');
    })->name('main-welcome');

    Route::controller(AdminLoginController::class)->group(function () {
        Route::get('login', 'index')->name('login');
        Route::post('login', 'store')->name('login.store');
        Route::get('/login/2fa', 'show2FA')->name('login.2fa');
        Route::post('/login/2fa/verify', 'verify2FA')->name('login.2fa.verify');
        Route::post('/login/2fa/resend', 'resend2FA')->name('login.2fa.resend');
        Route::post('/login/2fa/cancel', 'cancel2FA')->name('login.2fa.cancel');
    });

    Route::controller(PasswordResetLinkController::class)->group(function () {
        Route::get('forgot-password', 'create')->name('password.request');
        Route::post('forgot-password', 'store')->name('password.email');
    });

    // Guest routes (no auth guard)
    Route::controller(NewPasswordController::class)->group(function () {
        Route::get('reset-password/{token}', 'create')->name('password.reset');
        Route::post('reset-password', 'store')->name('password.store');
    });
});

Route::prefix('admin')->middleware(['auth:admin', AdminAuthentication::class])->group(function () {
    Route::get('dashboard', function () {
        return view('dashboard.dashboard');
    })->name('dashboard');

    Route::controller(FetchDataController::class)->group(function () {
        Route::get('function-test', 'mostBorrowedStudents')->name('function-test');
        Route::post('timeout-all-users', 'timeoutAllUsers')->name('timeout-all-users');
    });

    Route::controller(ProfileController::class)->group(function () {
        Route::get('profile', 'index')->name('profile');
        Route::patch('profile', 'update')->name('profile.update');
        Route::patch('/profile/2fa/enable', 'enableTwoFactor')->name('profile.2fa.enable');
        Route::patch('/profile/2fa/disable', 'disableTwoFactor')->name('profile.2fa.disable');
    });

    Route::prefix('analytics')->controller(FetchDataController::class)->group(function () {
        Route::get('current-users', 'fetchCurrentTimeInUsers')->name('fetch-current-count');
        Route::get('monthly-users', 'fetchMonthlyUsers')->name('fetch-monthly-count');
        Route::get('total-books', 'totalBooks')->name('fetch-book-count');
        Route::get('transaction-history', 'fetchTransactionHistory')->name('fetch-transaction-history');
        Route::get('yearly-aquired-books', 'fetchYearlyAquiredBooks')->name('fetch-yearly-aquired-books');
        Route::get('registered-users', 'fetchRegisteredUsers')->name('fetch-registered-users');
        Route::get('most-visited-students', 'mostVisitedStudents')->name('fetch-most-visited-students');
        Route::get('most-borrowed-students', 'mostBorrowedStudents')->name('fetch-most-borrowed-students');
        Route::get('top-books-borrowed', 'topBooksBorrowed')->name('fetch-top-books-borrowed');
        Route::get('top-categories-borrowed', 'topCategoriesBorrowed')->name('fetch-top-categories-borrowed');
    });

    Route::prefix('report')->middleware(ReportAuthentication::class)->group(function () {
        Route::controller(UserLogsController::class)->group(function () {
            Route::get('user-report', 'index')->name('report.user');
            Route::post('user-report', 'search')->name('report.user-search');
            Route::get('user-graph', 'graph')->name('report.user-graph');
            Route::post('export-graph', 'exportGraph')->name('report.graph-export-pdf');
        });

        Route::controller(ComputerUseController::class)->group(function () {
            Route::get('computer-use', 'index')->name('report.computer-use');
            Route::post('computer-use', 'search')->name('report.computer-use-search');
        });

        Route::controller(VisitorLogsController::class)->group(function () {
            Route::get('visitor-report', 'index')->name('report.visitor');
            Route::post('visitor-report', 'retrieve')->name('report.visitor-retrieve');
        });

        Route::controller(TransactionController::class)->group(function () {
            Route::get('book-circulation', 'index')->name('report.circulation');
            Route::post('book-circulation', 'search')->name('report.circulation-search');
        });

        Route::controller(BookCirculationController::class)->group(function () {
            Route::get('accession-list', 'index')->name('report.accession-list');
            Route::post('accession-list', 'search')->name('report.accession-list-search');
        });

        Route::controller(CategoriesController::class)->group(function () {
            Route::get('summary', 'index')->name('report.summary');
            Route::post('summary', 'export')->name('report.summary-export');
            Route::post('update-summary', 'update')->name('report.summary-update');
        });

        Route::controller(InventoriesController::class)->group(function () {
            Route::get('inventory-report', 'index')->name('report.inventory');
            Route::post('inventory-report', 'search')->name('report.inventory-search');
        });

        Route::controller(PenaltiesController::class)->group(function () {
            Route::get('penalties', 'index')->name('report.penalties');
            Route::post('penalties', 'search')->name('report.penalties-search');
        });

        Route::middleware(AuditReportAuthentication::class)->controller(AuditTrailController::class)->group(function () {
            Route::get('audit-trail', 'index')->name('report.audit-trail');
            Route::post('audit-trail', 'search')->name('report.audit-trail-search');
        });
    });

    Route::prefix('import')->middleware(ImportAuthentication::class)->group(function () {
        Route::controller(StudentImportController::class)->middleware(StudentImportAuthentication::class)->group(function () {
            Route::get('students', 'index')->name('import.import-students');
            Route::match(['get', 'post'], 'upload-students', 'upload')->name('import.upload-students');
            Route::post('store-students', 'store')->name('import.store-students');
            Route::get('students/download-template', 'downloadTemplate')->name('import.download-students-template');
        });

        Route::controller(FacultyStaffImportController::class)->middleware(EmployeeImportAuthentication::class)->group(function () {
            Route::get('faculties-staffs', 'index')->name('import.import-faculties-staffs');
            Route::match(['get', 'post'], 'upload-faculties-staffs', 'upload')->name('import.upload-faculties-staffs');
            Route::post('store-faculties-staffs', 'store')->name('import.store-faculties-staffs');
            Route::get('employees/download-template', 'downloadTemplate')->name('import.download-employee-template');
        });

        Route::controller(BookImportController::class)->middleware(BookImportAuthentication::class)->group(function () {
            Route::get('books', 'index')->name('import.import-books');
            Route::match(['get', 'post'], 'upload-books', 'upload')->name('import.upload-books');
            Route::post('store-books', 'store')->name('import.store-books');
            Route::get('books/download-template', 'downloadTemplate')->name('import.download-book-template');
        });
    });

    Route::prefix('inventory')->middleware(InventoryAuthentication::class)->controller(InventoryController::class)->group(function () {
        Route::get('dashboard', 'index')->name('inventory.dashboard');
        Route::post('start', 'start')->name('inventory.start');
        Route::post('cancel', 'cancel')->name('inventory.cancel');
        Route::post('search', 'search')->name('inventory.search');
        Route::post('update', 'update')->name('inventory.update');
        Route::post('finish', 'finish')->name('inventory.finish');
        Route::delete('delete', 'destroy')->name('inventory.delete');
    });

    Route::prefix('maintenance')->middleware(MaintenanceAuthentication::class)->group(function () {
        Route::prefix('books')->middleware(BookAuthentication::class)->group(function () {
            Route::controller(BookMaintenanceController::class)->group(function () {
                Route::get('books', 'index')->name('maintenance.books');
                Route::get('add-book', 'create')->name('maintenance.create-book');
                Route::post('add-book', 'store')->name('maintenance.store-book');
                Route::get('edit-book', 'edit')->name('maintenance.edit-book');
                Route::put('edit-book', 'update')->name('maintenance.update-book');
                Route::post('copy-book', 'copy')->name('maintenance.copy-book');
                Route::get('show-books', 'show')->name('maintenance.show-books');
                Route::get('show-book', 'view')->name('maintenance.view-book');
                Route::get('category', 'search_category')->name('maintenance.search-category');
                Route::get('export-barcode', 'export_barcode')->name('maintenance.export-barcode');
                Route::get('export-call-number', 'export_call_numbers')->name('maintenance.export-call-number');
                Route::delete('delete-book', 'destroy')->name('maintenance.delete-book');
                Route::delete('bulk-delete-book', 'bulkDelete')->name('maintenance.bulk-delete-book');
            });

            Route::prefix('categories')->middleware(BookCategoriesAuthentication::class)->controller(CategoryMaintenanceController::class)->group(function () {
                Route::get('categories', 'index')->name('maintenance.categories');
                Route::post('add-category', 'store')->name('maintenance.store-category');
                Route::put('edit-category', 'update')->name('maintenance.update-category');
                Route::delete('delete-category', 'destroy')->name('maintenance.delete-category');
            });
        });

        Route::prefix('users')->middleware(UserAuthentication::class)->controller(UsersMaintenanceController::class)->group(function () {
            Route::get('users', 'index')->name('maintenance.users');
            Route::get('view-student', 'view_student')->name('maintenance.view-student');
            Route::get('view-employee', 'view_employee')->name('maintenance.view-employee');
            Route::get('add-student', 'create_student')->name('maintenance.create-student');
            Route::post('add-student', 'store_student')->name('maintenance.store-student');
            Route::get('add-employee', 'create_employee')->name('maintenance.create-employee');
            Route::post('add-employee', 'store_employee')->name('maintenance.store-employee');
            Route::get('edit-student', 'edit_student')->name('maintenance.edit-student');
            Route::put('update-student', 'update_student')->name('maintenance.update-student');
            Route::get('edit-employee', 'edit_employee')->name('maintenance.edit-employee');
            Route::put('update-employee', 'update_employee')->name('maintenance.update-employee');
            Route::get('edit-visitor', 'edit_visitor')->name('maintenance.edit-visitor');
            Route::put('update-visitor', 'update_visitor')->name('maintenance.update-visitor');
            Route::get('show-users', 'show')->name('maintenance.show-users');
            Route::delete('delete-user', 'destroy')->name('maintenance.delete-user');
            Route::delete('bulk-delete-student', 'bulk_delete_student')->name('maintenance.bulk-delete-student');
            Route::delete('bulk-delete-employee', 'bulk_delete_employee')->name('maintenance.bulk-delete-employee');
            Route::delete('bulk-delete-visitor', 'bulk_delete_visitor')->name('maintenance.bulk-delete-visitor');
        });

        Route::prefix('privileges')->middleware(PrivilegeAuthentication::class)->controller(PrivilegeMaintenanceController::class)->group(function () {
            Route::get('privileges', 'index')->name('maintenance.privileges');
            Route::post('add-privilege', 'store')->name('maintenance.store-privilege');
            Route::put('edit-privilege', 'update')->name('maintenance.update-privilege');
            Route::delete('delete-privilege', 'destroy')->name('maintenance.delete-privilege');
            Route::get('show-privileges', 'show')->name('maintenance.show-privileges');
        });

        Route::prefix('penalty-rules')->middleware(PenaltyRuleMiddleware::class)->controller(PenaltyRuleController::class)->group(function () {
            Route::get('penalty-rules', 'index')->name('maintenance.penalty-rules');
            Route::post('add-penalty-rule', 'store')->name('maintenance.store-penalty-rule');
            Route::put('edit-penalty-rule', 'update')->name('maintenance.update-penalty-rule');
            Route::delete('delete-penalty-rule', 'destroy')->name('maintenance.delete-penalty-rule');
        });

        Route::prefix('circulations')->middleware(SuperAdminAuthentication::class)->controller(TransactionMaintenanceController::class)->group(function () {
            Route::get('circulations', 'index')->name('maintenance.circulations');
            Route::get('show-circulations', 'show')->name('maintenance.show-circulations');
            Route::get('retrieve-circulation', 'retrieve')->name('maintenance.retrieve-circulation');
            Route::put('edit-circulation', 'update')->name('maintenance.update-circulation');
            Route::delete('delete-circulation', 'destroy')->name('maintenance.delete-circulation');
        });

        Route::prefix('reservations')->middleware(ReservationAuthentication::class)->group(function () {
            Route::controller(ReservationExtensionController::class)->group(function () {
                Route::get('reservations', 'index')->name('maintenance.reservations');
                Route::get('show-reservations', 'pendingExtensionCount')->name('maintenance.pending-extensions');
                Route::post('approve-extension/{id}', 'approve')->name('maintenance.approve-extension');
                Route::post('reject-extension/{id}', 'reject')->name('maintenance.reject-extension');
                Route::get('search', 'search')->name('maintenance.search-extension');
            });

            Route::controller(ReservationStatus::class)->group(function () {
                Route::get('toggle', 'index')->name('maintenance-toggle');
                Route::get('status', 'getReservationStatus')->name('maintenance.status');
                Route::post('toggle', 'toggleReservationSystem')->name('maintenance.toggle');
                Route::get('stats', 'getReservationStats')->name('maintenance.stats');
            });
        });

        Route::prefix('admin-management')->middleware(SuperAdminAuthentication::class)->group(function () {
            Route::controller(AdminMaintenanceController::class)->group(function () {
                Route::get('admins', 'index')->name('maintenance.admins');
                Route::get('add-admin', 'create')->name('maintenance.create-admin');
                Route::post('add-admin', 'store')->name('maintenance.store-admin');
                Route::post('search-admin', 'search_user')->name('maintenance.search-user');
                Route::post('search', 'search_admin')->name('maintenance.search-admin');
                Route::get('edit-admin', 'edit')->name('maintenance.edit-admin');
                Route::put('edit-admin', 'update')->name('maintenance.update-admin');
                Route::get('show-admins', 'show')->name('maintenance.show-admins');
                Route::delete('delete-admin', 'destroy')->name('maintenance.delete-admin');
            });

            Route::prefix('roles-and-permissions')->controller(RolesController::class)->group(function () {
                Route::get('management', 'index')->name('maintenance.roles-and-permissions.management');
                Route::get('add-role', 'create')->name('maintenance.roles-and-permissions.create-role');
                Route::post('add-role', 'store')->name('maintenance.roles-and-permissions.store-role');
                Route::get('edit-role', 'edit')->name('maintenance.roles-and-permissions.edit-role');
                Route::put('edit-role', 'update')->name('maintenance.roles-and-permissions.update-role');
                Route::delete('delete-role', 'destroy')->name('maintenance.roles-and-permissions.delete-role');
            });
        });
    });

    Route::prefix('backup')->middleware(BackupAuthentication::class)->controller(BackupController::class)->group(function () {
        Route::get('backup', 'index')->name('backup.index');
        Route::post('create', 'create')->name('backup.create');
        Route::post('download', 'download')->name('backup.download');
        Route::delete('delete', 'destroy')->name('backup.destroy');
    });

    Route::prefix('settings')->middleware(UISettingAuthentication::class)->controller(UISettingController::class)->group(function () {
        Route::get('ui-settings', 'index')->name('settings.ui-settings');
        Route::post('ui-settings', 'update')->name('settings.update-ui-settings');
    });

    Route::post('logout', [AdminLoginController::class, 'destroy'])->name('admin.logout');
});
Route::fallback(function () {
    return view('errors.404');
});
