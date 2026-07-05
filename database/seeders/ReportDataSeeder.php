<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\Category;
use App\Models\Book;
use App\Models\StudentDetail;
use App\Models\EmployeeDetail;
use App\Models\VisitorDetail;
use App\Models\Log;
use App\Models\Printing;
use App\Models\BkNonCirculation;
use App\Models\Transaction;
use App\Models\Penalty;
use App\Models\PenaltyRule;
use App\Models\Inventory;
use App\Models\AuditTrail;
use Carbon\Carbon;
use Faker\Factory as Faker;

class ReportDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // 1. Ensure User Groups (Privileges) exist
        if (UserGroup::count() === 0) {
            $this->call(UserGroupSeeder::class);
        }

        $studentGroup = UserGroup::where('user_type', 'student')->first();
        $teacherGroup = UserGroup::where('user_type', 'employee')->where('category', 'Teacher')->first();
        $staffGroup = UserGroup::where('user_type', 'employee')->where('category', 'Staff')->first();
        $librarianGroup = UserGroup::where('user_type', 'employee')->where('category', 'Librarian')->first();
        $visitorGroup = UserGroup::where('user_type', 'visitor')->first();

        // 2. Ensure Categories exist with various types and educational levels for Summary Report
        $categoriesList = [
            [
                'legend' => 'FIC',
                'name' => 'Fiction',
                'category_type' => 'Print',
                'educational_level' => ['Elementary', 'Junior High School', 'Senior High School'],
                'borrow_duration_days' => 7,
            ],
            [
                'legend' => 'GR',
                'name' => 'General Reference',
                'category_type' => 'Print',
                'educational_level' => ['Junior High School', 'Senior High School'],
                'borrow_duration_days' => 3,
            ],
            [
                'legend' => 'CLE',
                'name' => 'Christian Living Education',
                'category_type' => 'Print',
                'educational_level' => ['Elementary'],
                'borrow_duration_days' => 5,
            ],
            [
                'legend' => 'COM',
                'name' => 'Comics and Magazines',
                'category_type' => 'Non-print',
                'educational_level' => ['Elementary', 'Junior High School'],
                'borrow_duration_days' => 1,
            ],
            [
                'legend' => 'RW',
                'name' => 'Research Works',
                'category_type' => 'Non-print',
                'educational_level' => ['Senior High School'],
                'borrow_duration_days' => 2,
            ],
            [
                'legend' => 'EB',
                'name' => 'Electronic Databases',
                'category_type' => 'E-books',
                'educational_level' => ['Junior High School', 'Senior High School'],
                'borrow_duration_days' => 0,
            ],
            [
                'legend' => 'REF',
                'name' => 'Science Reference',
                'category_type' => 'Print',
                'educational_level' => ['Junior High School', 'Senior High School'],
                'borrow_duration_days' => 7,
            ],
            [
                'legend' => 'TREF',
                'name' => "Teacher's Reference",
                'category_type' => 'Print',
                'educational_level' => ['Junior High School', 'Senior High School'],
                'borrow_duration_days' => 14,
            ]
        ];

        foreach ($categoriesList as $cat) {
            Category::updateOrCreate(
                ['legend' => $cat['legend']],
                [
                    'name' => $cat['name'],
                    'category_type' => $cat['category_type'],
                    'educational_level' => $cat['educational_level'],
                    'previous_inventory' => rand(10, 50),
                    'discarded' => 0,
                    'newly_acquired' => 0,
                    'present_inventory' => 0,
                    'borrow_duration_days' => $cat['borrow_duration_days']
                ]
            );
        }

        $allCategories = Category::all();

        // 3. Ensure Books exist with Main author and varying remarks
        $bookRemarks = ['On Shelf', 'Lost and Paid For', 'Lost and Replaced', 'Unreturned', 'Missing'];
        
        if (Book::count() < 30) {
            for ($i = 0; $i < 50; $i++) {
                $category = $allCategories->random();
                $accession = $category->legend . '-' . $faker->unique()->numberBetween(100000, 999999);
                
                Book::create([
                    'accession' => $accession,
                    'call_number' => strtoupper($faker->lexify('???')) . ' ' . $faker->numberBetween(100, 999) . '.' . $faker->numberBetween(10, 99) . ' ' . strtoupper($faker->lexify('??')),
                    'title' => $faker->sentence(rand(3, 6)),
                    'authors' => [
                        'Main author' => $faker->name(),
                        'Co-author' => $faker->optional(0.4)->name(),
                    ],
                    'edition' => $faker->numberBetween(1, 5),
                    'isbn' => $faker->isbn13(),
                    'place_of_publication' => $faker->city() . ', ' . $faker->stateAbbr(),
                    'publisher' => $faker->company(),
                    'copyrights' => $faker->year(),
                    'remarks' => $faker->randomElement($bookRemarks),
                    'category_id' => $category->id,
                    'barcode' => $accession,
                    'availability_status' => $faker->randomElement(['Available', 'Borrowed', 'In Use', 'Reserved']),
                    'condition_status' => $faker->randomElement(['New', 'Good', 'Fair', 'Poor']),
                ]);
            }
        }

        $books = Book::all();

        // 4. Ensure Users exist (and their profile details get hooked up automatically by UserFactory definition)
        if (User::count() < 15) {
            // Seed Students
            for ($i = 0; $i < 10; $i++) {
                User::factory()->create([
                    'privilege_id' => $studentGroup->id,
                ]);
            }

            // Seed Employees
            for ($i = 0; $i < 5; $i++) {
                User::factory()->create([
                    'privilege_id' => $faker->randomElement([$teacherGroup->id, $staffGroup->id, $librarianGroup->id]),
                ]);
            }

            // Seed Visitors
            for ($i = 0; $i < 5; $i++) {
                User::factory()->create([
                    'privilege_id' => $visitorGroup->id,
                ]);
            }
        }

        $studentUsers = User::whereHas('privileges', function ($q) {
            $q->where('user_type', 'student');
        })->get();

        $employeeUsers = User::whereHas('privileges', function ($q) {
            $q->where('user_type', 'employee');
        })->get();

        $visitorUsers = User::whereHas('privileges', function ($q) {
            $q->where('user_type', 'visitor');
        })->get();

        $allUsers = User::all();

        // 5. Seed User Logs (both General and Computer Use)
        // We will seed logs for the last 90 days.
        $now = Carbon::now();
        for ($i = 0; $i < 60; $i++) {
            $user = $allUsers->random();
            $timeIn = Carbon::now()->subDays(rand(1, 90))->subHours(rand(1, 10))->subMinutes(rand(1, 59));
            $timeOut = $faker->optional(0.9)->dateTimeInInterval($timeIn, '+8 hours');

            Log::create([
                'user_id' => $user->id,
                'computer_use' => $faker->randomElement(['Yes', 'No']),
                'time_in' => $timeIn,
                'time_out' => $timeOut,
                'remarks' => $faker->optional(0.1)->randomElement(['Feverish', 'Consulted Librarian']),
            ]);
        }

        // 6. Seed Printing Logs
        $studentDetails = StudentDetail::all();
        $employeeDetails = EmployeeDetail::all();

        if ($studentDetails->isNotEmpty() || $employeeDetails->isNotEmpty()) {
            for ($i = 0; $i < 30; $i++) {
                $isStudent = $faker->boolean(70);
                $studentId = null;
                $facultyId = null;

                if ($isStudent && $studentDetails->isNotEmpty()) {
                    $studentId = $studentDetails->random()->id;
                } elseif ($employeeDetails->isNotEmpty()) {
                    $facultyId = $employeeDetails->random()->id;
                } else {
                    $studentId = $studentDetails->random()->id;
                }

                $type = $faker->randomElement(['print', 'photocopy']);
                $pages = $faker->numberBetween(1, 50);

                Printing::create([
                    'student_id' => $studentId,
                    'faculty_id' => $facultyId,
                    'type' => $type,
                    'title_of_material' => $type === 'photocopy' ? $faker->sentence(3) : null,
                    'topic' => $faker->randomElement(['Research Paper', 'Lesson Plan', 'Self-Learning Module', 'Form', 'Assignment']),
                    'pages' => $pages,
                    'amount' => $type === 'photocopy' ? ($pages * 2.00) : null,
                    'printed_at' => Carbon::now()->subDays(rand(1, 90)),
                ]);
            }
        }

        // 7. Seed Non-Circulation Logs
        if ($studentDetails->isNotEmpty() || $employeeDetails->isNotEmpty()) {
            for ($i = 0; $i < 25; $i++) {
                $isStudent = $faker->boolean(75);
                $studentId = null;
                $facultyId = null;

                if ($isStudent && $studentDetails->isNotEmpty()) {
                    $studentId = $studentDetails->random()->id;
                } elseif ($employeeDetails->isNotEmpty()) {
                    $facultyId = $employeeDetails->random()->id;
                } else {
                    $studentId = $studentDetails->random()->id;
                }

                BkNonCirculation::create([
                    'student_id' => $studentId,
                    'faculty_id' => $facultyId,
                    'subject' => $faker->randomElement(['General Science', 'Pre-Calculus', 'World Literature', 'Media Literacy', 'Philippine History']),
                    'borrowed_at' => Carbon::now()->subDays(rand(1, 90)),
                ]);
            }
        }

        // 8. Ensure Penalty Rules exist
        if (PenaltyRule::count() === 0) {
            $this->call(PenaltyRuleSeeder::class);
        }
        $penaltyRules = PenaltyRule::all();

        // 9. Seed Transactions (tr_transactions)
        // Filter out visitor users, since they typically don't borrow books outside the library.
        $borrowerUsers = $allUsers->filter(function ($u) {
            return in_array($u->privilege_id, [1, 2, 3, 4]);
        });

        if ($borrowerUsers->isNotEmpty()) {
            for ($i = 0; $i < 40; $i++) {
                $user = $borrowerUsers->random();
                $book = $books->random();
                $transactionType = $faker->randomElement(['Borrowed', 'Returned', 'Reserved']);
                
                $dateBorrowed = null;
                $dueDate = null;
                $returnDate = null;
                $reservedDate = null;
                $status = 'Pending';
                $penaltyTotal = 0;
                $penaltyStatus = 'No Penalty';

                $dateBorrowedSeed = Carbon::now()->subDays(rand(5, 60));
                
                if ($transactionType === 'Borrowed') {
                    $dateBorrowed = $dateBorrowedSeed;
                    $dueDate = $dateBorrowedSeed->copy()->addDays(7);
                    
                    // Some are overdue, some are pending
                    if ($dueDate->isPast()) {
                        $status = $faker->randomElement(['Overdue', 'Lost']);
                        if ($status === 'Overdue') {
                            $penaltyTotal = $dueDate->diffInDays(Carbon::now()) * 5; // e.g. 5 pesos per day
                            $penaltyStatus = 'Unpaid';
                        } else {
                            $penaltyTotal = 300.00;
                            $penaltyStatus = 'Unpaid';
                        }
                    } else {
                        $status = 'Pending';
                    }
                } elseif ($transactionType === 'Returned') {
                    $dateBorrowed = $dateBorrowedSeed;
                    $dueDate = $dateBorrowedSeed->copy()->addDays(7);
                    $returnDate = $faker->randomElement([
                        $dateBorrowedSeed->copy()->addDays(rand(1, 6)), // returned on time
                        $dateBorrowedSeed->copy()->addDays(rand(8, 15))  // returned late
                    ]);
                    $status = 'Completed';
                    
                    if ($returnDate->gt($dueDate)) {
                        $penaltyTotal = $dueDate->diffInDays($returnDate) * 5;
                        $penaltyStatus = $faker->randomElement(['Paid', 'Unpaid']);
                    }
                } elseif ($transactionType === 'Reserved') {
                    $reservedDate = Carbon::now()->subDays(rand(1, 5));
                    $status = $faker->randomElement(['Pending', 'Cancelled']);
                }

                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'date_borrowed' => $dateBorrowed,
                    'due_date' => $dueDate,
                    'return_date' => $returnDate,
                    'reserved_date' => $reservedDate,
                    'transaction_type' => $transactionType,
                    'status' => $status,
                    'book_condition' => $faker->randomElement(['New', 'Good', 'Fair', 'Poor']),
                    'penalty_total' => $penaltyTotal,
                    'discount' => 0.00,
                    'penalty_status' => $penaltyStatus,
                    'remarks' => $faker->optional()->sentence(3),
                ]);

                // 10. Seed Penalties mapping to overdue or late-returned transactions
                if ($penaltyTotal > 0) {
                    $ruleType = $status === 'Lost' ? 'Lost Book' : 'Overdue';
                    $rule = $penaltyRules->where('type', $ruleType)->first() ?? $penaltyRules->first();

                    Penalty::create([
                        'transaction_id' => $transaction->id,
                        'penalty_rule_id' => $rule->id,
                        'amount' => $penaltyTotal,
                        'created_at' => $returnDate ?? $dueDate ?? Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }

        // 11. Seed Inventories (bk_inventories)
        for ($i = 0; $i < 30; $i++) {
            $book = $books->random();
            
            // Check if already in inventory
            if (Inventory::where('book_id', $book->id)->exists()) {
                continue;
            }

            Inventory::create([
                'book_id' => $book->id,
                'is_scanned' => true,
                'checked_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }

        // 12. Seed Audit Trail (audit_trail)
        // Find an admin or librarian user to act as the changer
        $librarianUser = User::whereHas('privileges', function ($q) {
            $q->where('user_type', 'employee')->where('category', 'Librarian');
        })->first() ?? User::where('privilege_id', 4)->first() ?? User::first();

        $changerName = $librarianUser ? $librarianUser->id : 'system';

        $tables = ['usr_users', 'bk_books', 'tr_transactions', 'sessions'];
        $fields = ['first_name', 'title', 'status', 'ip_address'];
        $actionTypes = ['INSERT', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT'];

        for ($i = 0; $i < 30; $i++) {
            $action = $faker->randomElement($actionTypes);
            $sourceTable = $faker->randomElement($tables);
            $recordId = $faker->numberBetween(1, 100);
            
            $fieldChanged = null;
            $oldVal = null;
            $newVal = null;

            if ($action === 'UPDATE') {
                $fieldChanged = $faker->randomElement($fields);
                $oldVal = $faker->word();
                $newVal = $faker->word();
            } elseif ($action === 'LOGIN' || $action === 'LOGOUT') {
                $sourceTable = 'sessions';
            }

            AuditTrail::create([
                'record_id' => $recordId,
                'source_table' => $sourceTable,
                'field_changed' => $fieldChanged,
                'old_value' => $oldVal,
                'new_value' => $newVal,
                'action_type' => $action,
                'changed_by' => $changerName,
                'created_at' => Carbon::now()->subDays(rand(1, 90)),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
