<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\EmployeeDetail;
use App\Models\Inventory;
use App\Models\Log;
use App\Models\Penalty;
use App\Models\PenaltyRule;
use App\Models\StudentDetail;
use App\Models\Subject;
use App\Models\SubjectAccessCode;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\VisitorDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportantTablesSeeder extends Seeder
{
  public function run(): void
  {
    DB::statement("SET @current_user_id = 'seeder'");

    $this->seedPrivileges();
    $categories = $this->seedCategories();
    $books = $this->seedBooks($categories);
    $this->seedSubjectsAndAccessCodes($books);
    $users = $this->seedUsers();
    $this->seedInventories($books);
    $penaltyRules = $this->seedPenaltyRules();
    $transactions = $this->seedTransactions($users, $books, $penaltyRules);
    $this->seedNotifications($transactions);
    $this->seedLogs();
  }

  private function seedPrivileges(): void
  {
    $privileges = [
      [
        'user_type' => 'student',
        'category' => 'Regular Student',
        'max_book_allowed' => 3,
        'duration_type' => 'standard',
        'renewal_limit' => 2,
      ],
      [
        'user_type' => 'employee',
        'category' => 'Teacher',
        'max_book_allowed' => 5,
        'duration_type' => 'standard',
        'renewal_limit' => 3,
      ],
      [
        'user_type' => 'employee',
        'category' => 'Staff',
        'max_book_allowed' => 4,
        'duration_type' => 'standard',
        'renewal_limit' => 2,
      ],
      [
        'user_type' => 'employee',
        'category' => 'Librarian',
        'max_book_allowed' => 10,
        'duration_type' => 'unlimited',
        'renewal_limit' => 5,
      ],
      [
        'user_type' => 'visitor',
        'category' => 'Guest Visitor',
        'max_book_allowed' => 1,
        'duration_type' => 'none',
        'renewal_limit' => 0,
      ],
    ];

    foreach ($privileges as $privilege) {
      UserGroup::firstOrCreate(
        [
          'user_type' => $privilege['user_type'],
          'category' => $privilege['category'],
        ],
        [
          'max_book_allowed' => $privilege['max_book_allowed'],
          'duration_type' => $privilege['duration_type'],
          'renewal_limit' => $privilege['renewal_limit'],
        ]
      );
    }
  }

  private function seedCategories()
  {
    $categories = [
      ['legend' => 'FIC', 'name' => 'Fiction', 'borrow_duration_days' => 7],
      ['legend' => 'REF', 'name' => 'Reference', 'borrow_duration_days' => 3],
      ['legend' => 'SCI', 'name' => 'Science', 'borrow_duration_days' => 7],
      ['legend' => 'MTH', 'name' => 'Mathematics', 'borrow_duration_days' => 7],
      ['legend' => 'HIS', 'name' => 'History', 'borrow_duration_days' => 7],
      ['legend' => 'ENG', 'name' => 'English', 'borrow_duration_days' => 7],
      ['legend' => 'FIL', 'name' => 'Filipino', 'borrow_duration_days' => 7],
      ['legend' => 'TEC', 'name' => 'Technology', 'borrow_duration_days' => 7],
    ];

    foreach ($categories as $category) {
      Category::updateOrCreate(
        ['name' => $category['name']],
        [
          'legend' => $category['legend'],
          'borrow_duration_days' => $category['borrow_duration_days'],
        ]
      );
    }

    return Category::query()->get();
  }

  private function seedBooks($categories)
  {
    if (Book::count() < 60) {
      Book::factory()->count(60)->create();
    }

    $specificBooks = [
      [
        'accession' => 'REF-900001',
        'title' => 'Introduction to Library Systems',
        'author' => 'Maria Santos',
        'category_legend' => 'REF',
      ],
      [
        'accession' => 'SCI-900002',
        'title' => 'General Biology Concepts',
        'author' => 'Henry Cruz',
        'category_legend' => 'SCI',
      ],
      [
        'accession' => 'MTH-900003',
        'title' => 'Applied Mathematics for Senior High',
        'author' => 'Anna Reyes',
        'category_legend' => 'MTH',
      ],
    ];

    foreach ($specificBooks as $data) {
      $category = $categories->firstWhere('legend', $data['category_legend']) ?? $categories->first();
      Book::updateOrCreate(
        ['accession' => $data['accession']],
        [
          'title' => $data['title'],
          'author' => $data['author'],
          'category_id' => $category->id,
          'remarks' => 'On Shelf',
          'availability_status' => 'Available',
          'condition_status' => 'Good',
          'book_type' => 'physical',
          'cover_image' => 'default.jpg',
          'barcode' => $data['accession'],
        ]
      );
    }

    return Book::query()->with('category')->get();
  }

  private function seedSubjectsAndAccessCodes($books): void
  {
    $subjectSeeds = [
      ['ddc' => '000', 'name' => 'General Knowledge', 'codes' => ['GEN-READ', 'KNW-001']],
      ['ddc' => '100', 'name' => 'Philosophy', 'codes' => ['PHL-101', 'GEN-READ']],
      ['ddc' => '200', 'name' => 'Religion', 'codes' => ['RLG-201']],
      ['ddc' => '300', 'name' => 'Social Sciences', 'codes' => ['SOC-301', 'GEN-READ']],
      ['ddc' => '400', 'name' => 'Language', 'codes' => ['LAN-401']],
      ['ddc' => '500', 'name' => 'Natural Sciences', 'codes' => ['SCI-501', 'LAB-ACCESS']],
      ['ddc' => '600', 'name' => 'Technology', 'codes' => ['TEC-601', 'LAB-ACCESS']],
      ['ddc' => '700', 'name' => 'Arts & Recreation', 'codes' => ['ART-701']],
      ['ddc' => '800', 'name' => 'Literature', 'codes' => ['LIT-801', 'GEN-READ']],
      ['ddc' => '900', 'name' => 'History & Geography', 'codes' => ['HIS-901']],
    ];

    foreach ($subjectSeeds as $index => $seed) {
      $book = $books[$index % max(1, $books->count())];

      $subject = Subject::updateOrCreate(
        [
          'ddc' => $seed['ddc'],
          'name' => $seed['name'],
        ],
        [
          'book_id' => $book->id,
        ]
      );

      foreach ($seed['codes'] as $code) {
        $accessCode = SubjectAccessCode::whereRaw('LOWER(access_code) = ?', [strtolower($code)])->first();

        if (!$accessCode) {
          $accessCode = SubjectAccessCode::create([
            'subject_id' => $subject->id,
            'access_code' => $code,
          ]);
        }

        $subject->accessCodes()->syncWithoutDetaching([$accessCode->id]);
      }
    }
  }

  private function seedUsers()
  {
    $fixedUsers = [
      [
        'email' => 'student1@bpsu.local',
        'rfid' => '7000000000011',
        'privilege_id' => 1,
        'first_name' => 'Liam',
        'middle_name' => 'S',
        'last_name' => 'Student',
        'suffix' => null,
        'gender' => 'Male',
        'profile_image' => 'default.jpg',
        'detail_type' => 'student',
        'detail_payload' => ['id_number' => '2026-00001-BPSU', 'level' => 'Grade 11', 'section' => 'STEM-A'],
      ],
      [
        'email' => 'student2@bpsu.local',
        'rfid' => '7000000000012',
        'privilege_id' => 1,
        'first_name' => 'Mia',
        'middle_name' => 'R',
        'last_name' => 'Student',
        'suffix' => null,
        'gender' => 'Female',
        'profile_image' => 'default.jpg',
        'detail_type' => 'student',
        'detail_payload' => ['id_number' => '2026-00002-BPSU', 'level' => 'Grade 12', 'section' => 'HUMSS-B'],
      ],
      [
        'email' => 'teacher1@bpsu.local',
        'rfid' => '7000000000013',
        'privilege_id' => 2,
        'first_name' => 'Noah',
        'middle_name' => 'T',
        'last_name' => 'Teacher',
        'suffix' => null,
        'gender' => 'Male',
        'profile_image' => 'default.jpg',
        'detail_type' => 'employee',
        'detail_payload' => ['employee_id' => 'EMP-100001', 'employee_role' => 'Teacher'],
      ],
      [
        'email' => 'staff1@bpsu.local',
        'rfid' => '7000000000014',
        'privilege_id' => 3,
        'first_name' => 'Ava',
        'middle_name' => 'P',
        'last_name' => 'Staff',
        'suffix' => null,
        'gender' => 'Female',
        'profile_image' => 'default.jpg',
        'detail_type' => 'employee',
        'detail_payload' => ['employee_id' => 'EMP-100002', 'employee_role' => 'Staff'],
      ],
      [
        'email' => 'librarian1@bpsu.local',
        'rfid' => '7000000000015',
        'privilege_id' => 4,
        'first_name' => 'Ethan',
        'middle_name' => 'L',
        'last_name' => 'Librarian',
        'suffix' => null,
        'gender' => 'Male',
        'profile_image' => 'default.jpg',
        'detail_type' => 'employee',
        'detail_payload' => ['employee_id' => 'EMP-100003', 'employee_role' => 'Librarian'],
        'admin_role' => 'Librarian',
      ],
      [
        'email' => 'visitor1@bpsu.local',
        'rfid' => '7000000000016',
        'privilege_id' => 5,
        'first_name' => 'Sophia',
        'middle_name' => 'V',
        'last_name' => 'Visitor',
        'suffix' => null,
        'gender' => 'Female',
        'profile_image' => 'default.jpg',
        'detail_type' => 'visitor',
        'detail_payload' => ['school_org' => 'Community College', 'purpose' => 'Research'],
      ],
      [
        'email' => 'libraryadmin@bpsu.local',
        'rfid' => '7000000000017',
        'privilege_id' => 4,
        'first_name' => 'Lucas',
        'middle_name' => 'A',
        'last_name' => 'Admin',
        'suffix' => null,
        'gender' => 'Male',
        'profile_image' => 'default.jpg',
        'detail_type' => 'employee',
        'detail_payload' => ['employee_id' => 'EMP-100004', 'employee_role' => 'Librarian'],
        'admin_role' => 'Admin',
      ],
    ];

    foreach ($fixedUsers as $seed) {
      $user = User::updateOrCreate(
        ['email' => $seed['email']],
        [
          'rfid' => $seed['rfid'],
          'privilege_id' => $seed['privilege_id'],
          'first_name' => $seed['first_name'],
          'middle_name' => $seed['middle_name'],
          'last_name' => $seed['last_name'],
          'suffix' => $seed['suffix'],
          'gender' => $seed['gender'],
          'profile_image' => $seed['profile_image'],
          'password' => Hash::make('password'),
        ]
      );

      if ($seed['detail_type'] === 'student') {
        StudentDetail::updateOrCreate(
          ['user_id' => $user->id],
          [
            'id_number' => $seed['detail_payload']['id_number'],
            'level' => $seed['detail_payload']['level'],
            'section' => $seed['detail_payload']['section'],
          ]
        );
      }

      if ($seed['detail_type'] === 'employee') {
        EmployeeDetail::updateOrCreate(
          ['user_id' => $user->id],
          [
            'employee_id' => $seed['detail_payload']['employee_id'],
            'employee_role' => $seed['detail_payload']['employee_role'],
          ]
        );
      }

      if ($seed['detail_type'] === 'visitor') {
        VisitorDetail::updateOrCreate(
          ['user_id' => $user->id],
          [
            'school_org' => $seed['detail_payload']['school_org'],
            'purpose' => $seed['detail_payload']['purpose'],
          ]
        );
      }

      if (isset($seed['admin_role'])) {
        $user->syncRoles([$seed['admin_role']]);
      }
    }

    User::factory()->count(20)->create();

    return User::query()->whereNotNull('privilege_id')->get();
  }

  private function seedInventories($books): void
  {
    $books->take(50)->each(function ($book) {
      Inventory::firstOrCreate(
        ['book_id' => $book->id],
        [
          'is_scanned' => (bool) random_int(0, 1),
          'checked_at' => now()->subDays(random_int(0, 60)),
        ]
      );
    });
  }

  private function seedPenaltyRules()
  {
    $rules = [
      [
        'type' => 'Overdue',
        'description' => 'Overdue fine per day',
        'rate' => 5.00,
        'per_day' => 1,
      ],
      [
        'type' => 'Lost Book',
        'description' => 'Replacement fee for lost book',
        'rate' => 300.00,
        'per_day' => 0,
      ],
      [
        'type' => 'Damaged Book',
        'description' => 'Damage fee',
        'rate' => 150.00,
        'per_day' => 0,
      ],
    ];

    foreach ($rules as $rule) {
      PenaltyRule::updateOrCreate(
        ['type' => $rule['type']],
        [
          'description' => $rule['description'],
          'rate' => $rule['rate'],
          'per_day' => $rule['per_day'],
        ]
      );
    }

    return PenaltyRule::query()->get();
  }

  private function seedTransactions($users, $books, $penaltyRules)
  {
    $transactions = collect();

    $userPool = $users->values();
    $bookPool = $books->values();

    for ($i = 0; $i < 35; $i++) {
      $user = $userPool[$i % max(1, $userPool->count())];
      $book = $bookPool[$i % max(1, $bookPool->count())];
      $today = Carbon::today();

      $payload = [
        'user_id' => $user->id,
        'book_id' => $book->id,
        'book_condition' => 'Good',
        'remarks' => 'Seeded demo transaction',
        'penalty_total' => 0,
        'penalty_status' => 'No Penalty',
      ];

      if ($i % 5 === 0) {
        $payload['transaction_type'] = 'Reserved';
        $payload['status'] = 'Available for pick up';
        $payload['reserved_date'] = $today->copy()->subDays(random_int(0, 3))->toDateString();
        $payload['pickup_deadline'] = $today->copy()->addDays(random_int(1, 2))->toDateString();
      } elseif ($i % 4 === 0) {
        $payload['transaction_type'] = 'Borrowed';
        $payload['status'] = 'Overdue';
        $payload['date_borrowed'] = $today->copy()->subDays(15)->toDateString();
        $payload['due_date'] = $today->copy()->subDays(8)->toDateString();
        $payload['penalty_status'] = 'Unpaid';
      } elseif ($i % 3 === 0) {
        $payload['transaction_type'] = 'Returned';
        $payload['status'] = 'Completed';
        $payload['date_borrowed'] = $today->copy()->subDays(10)->toDateString();
        $payload['due_date'] = $today->copy()->subDays(3)->toDateString();
        $payload['return_date'] = $today->copy()->subDays(2)->toDateString();
      } else {
        $payload['transaction_type'] = 'Borrowed';
        $payload['status'] = 'Pending';
        $payload['date_borrowed'] = $today->copy()->subDays(2)->toDateString();
        $payload['due_date'] = $today->copy()->addDays(5)->toDateString();
      }

      $transaction = Transaction::create($payload);

      if ($transaction->status === 'Overdue') {
        $rule = $penaltyRules->firstWhere('type', 'Overdue') ?? $penaltyRules->first();
        $amount = 40.00;

        Penalty::create([
          'transaction_id' => $transaction->id,
          'penalty_rule_id' => $rule->id,
          'amount' => $amount,
        ]);

        $transaction->update([
          'penalty_total' => $amount,
          'penalty_status' => 'Unpaid',
        ]);
      }

      $transactions->push($transaction);
    }

    return $transactions;
  }

  private function seedNotifications($transactions): void
  {
    $notificationRows = [];

    foreach ($transactions->take(15) as $transaction) {
      $notificationRows[] = [
        'user_id' => $transaction->user_id,
        'transaction_id' => $transaction->id,
        'title' => 'Transaction Update',
        'message' => 'Your transaction status is now ' . $transaction->status . '.',
        'type' => 'transaction',
        'notif_date' => now()->subDays(random_int(0, 5)),
        'status' => random_int(0, 1) ? 'read' : 'unread',
        'created_at' => now(),
        'updated_at' => now(),
      ];
    }

    if (!empty($notificationRows)) {
      DB::table('notifications')->insert($notificationRows);
    }
  }

  private function seedLogs(): void
  {
    Log::factory()->count(40)->create();
  }
}
