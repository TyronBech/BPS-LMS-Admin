<?php

namespace Tests\Support;

use App\Http\Controllers\Maintenance\BookMaintenanceController;
use App\Models\Category;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Bootstrap\BootProviders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use PDO;
use Tests\TestCase;

abstract class BookMaintenanceTestCase extends TestCase
{
    public function createApplication()
    {
        $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
        $app->afterBootstrapping(LoadConfiguration::class, function ($app) {
            $app['config']->set([
                'database.default' => 'acquisition_test',
                'database.connections' => ['acquisition_test' => ['driver' => 'acquisition_sqlite', 'database' => ':memory:', 'prefix' => '']],
                'cache.default' => 'array',
                'session.driver' => 'array',
                'logging.default' => 'null',
                'app.debug' => false,
            ]);
        });
        $app->beforeBootstrapping(BootProviders::class, function ($app) {
            $app['db']->extend('acquisition_sqlite', fn ($config) => new BookMaintenanceSqliteConnection(new PDO('sqlite::memory:'), ':memory:', '', $config));
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('bk_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legend');
            $table->string('category_type');
            foreach (['previous_inventory', 'newly_acquired', 'discarded', 'present_inventory'] as $column) {
                $table->integer($column)->default(0);
            }
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('bk_books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            foreach (['accession', 'call_number', 'title', 'parallel_title', 'authors', 'description', 'edition', 'isbn', 'place_of_publication', 'publisher', 'copyrights', 'remarks', 'cover_image', 'digital_copy_url', 'barcode', 'book_type', 'availability_status', 'condition_status', 'languages', 'location', 'periodical_kind', 'volume', 'issue_number', 'material_type', 'issn', 'frequency', 'latest_received', 'notes', 'topical_access_point', 'corporate_access_point', 'discipline', 'institution', 'program'] as $column) {
                $table->text($column)->nullable();
            }
            $table->timestamps();
            $table->softDeletes();
        });
        foreach (['bk_inventories', 'bk_last_accession', 'system_settings', 'bk_subject_access_codes', 'bk_book_subject_access_code'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                if ($name === 'system_settings') {
                    $table->string('key');
                    $table->string('value');
                } elseif ($name === 'bk_last_accession') {
                    $table->unsignedBigInteger('category_id');
                    $table->string('accession_number');
                } elseif ($name === 'bk_subject_access_codes') {
                    $table->string('access_code');
                } else {
                    $table->unsignedBigInteger('book_id');
                    if ($name === 'bk_inventories') {
                        $table->boolean('is_scanned')->default(false);
                        $table->timestamp('checked_at')->nullable();
                    } else {
                        $table->unsignedBigInteger('subject_access_code_id');
                    }
                }
                $table->timestamps();
                $table->softDeletes();
            });
        }
        foreach (glob(database_path('migrations/*_add_acquisition_details_to_bk_books_table.php')) as $path) {
            (require $path)->up();
        }
        Schema::create('tr_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->string('transaction_type');
            $table->string('status');
            $table->softDeletes();
        });
        $this->actingAs((new User)->forceFill(['id' => 1, 'first_name' => 'Test', 'last_name' => 'Librarian']), 'admin');
        $this->app['request']->setLaravelSession($this->app['session']->driver());
    }

    protected function category(string $name = 'General', string $type = 'Print'): Category
    {
        return Category::create(['name' => $name, 'legend' => 'TST', 'category_type' => $type]);
    }

    protected function payload(Category $category, array $overrides = []): array
    {
        return array_replace([
            'accession' => 'TST-00001', 'title' => 'Acquisition Test', 'category' => $category->id,
            'book_type' => $category->category_type, 'remarks' => 'On Shelf', 'condition' => 'Good',
            'availability' => 'Available', 'description' => ['Extent' => '100 pages'],
        ], $overrides);
    }

    protected function saveBook(string $action, array $payload)
    {
        $request = Request::create('/maintenance/books/'.$action, $action === 'update' ? 'PUT' : 'POST', $payload);
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);

        return (new BookMaintenanceController)->{$action}($request);
    }
}

/** Adapt only existing MySQL session statements and enum discovery; all record queries use real SQLite. */
class BookMaintenanceSqliteConnection extends SQLiteConnection
{
    public function statement($query, $bindings = [])
    {
        if ($query === 'SET time_zone = "+08:00"' || $query === 'SET @current_user_id = ?') {
            return true;
        }

        return parent::statement($query, $bindings);
    }

    public function select($query, $bindings = [], $useReadPdo = true)
    {
        if (preg_match("/^SHOW COLUMNS FROM bk_books LIKE '(remarks|book_type|condition_status|availability_status)'$/", $query, $match)) {
            $values = match ($match[1]) {
                'remarks' => "'On Shelf','Missing','Lost','Discarded'",
                'book_type' => "'Print','Non-print','E-books'",
                'condition_status' => "'New','Good','Fair','Poor'",
                'availability_status' => "'Available','Unavailable','Borrowed','Reserved'",
            };

            return [(object) ['Type' => "enum($values)"]];
        }

        return parent::select($query, $bindings, $useReadPdo);
    }
}
