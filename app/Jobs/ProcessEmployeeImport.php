<?php

namespace App\Jobs;

use App\Mail\AccountEmailMessage;
use App\Models\EmployeeDetail;
use App\Models\ImportProgress;
use App\Models\StagingUser;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProcessEmployeeImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int Maximum seconds allowed. */
    public int $timeout = 1800;

    /** @var int Do not auto-retry on failure. */
    public int $tries = 1;

    /** Number of rows per DB-transaction chunk. */
    private const CHUNK_SIZE = 100;

    /**
     * @param string                     $filePath     Path to the temp Excel file in storage.
     * @param int                        $progressId   ID of the ImportProgress record.
     * @param int                        $initiatedBy  User ID who triggered the import.
     * @param array                      $editsNew     User edits on new employees.
     * @param array                      $editsExisting User edits on existing employees.
     */
    public function __construct(
        private readonly string $filePath,
        private readonly int $progressId,
        private readonly int $initiatedBy,
        private readonly array $editsNew = [],
        private readonly array $editsExisting = [],
    ) {}

    /**
     * Execute the employee/faculty-staff import.
     */
    public function handle(): void
    {
        // Allow 1 GB for large imports
        ini_set('memory_limit', '1G');

        // Prevent Laravel from keeping all executed queries in memory
        DB::disableQueryLog();

        /** @var ImportProgress $progress */
        $progress = ImportProgress::findOrFail($this->progressId);
        $progress->update(['status' => 'processing']);

        $fullPath = \Illuminate\Support\Facades\Storage::path($this->filePath);

        Log::info('ProcessEmployeeImport: Job started', [
            'progress_id'  => $this->progressId,
            'file_path'    => $fullPath,
            'initiated_by' => $this->initiatedBy,
        ]);

        try {
            if (!file_exists($fullPath)) {
                throw new \Exception("Uploaded Excel file not found: " . $this->filePath);
            }

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $newData = [];
            $existingData = [];

            if (empty($rows) || !isset($rows[0][0])) {
                throw new \Exception('Excel file is empty or template is incorrect.');
            }

            for ($i = 18; $i < count($rows); $i++) {
                if (empty(array_filter(array_slice($rows[$i], 1, 7)))) {
                    continue;
                }

                $fullName = $this->extractNameParts($rows[$i][2] ?? '');
                if (empty($fullName['first_name']) || empty($fullName['last_name'])) {
                    throw new \Exception("Invalid format in row " . ($i + 1) . ". Full Name is incorrect.");
                }

                $temp = [
                    'rfid'          => $rows[$i][1],
                    'first_name'    => $fullName['first_name'],
                    'middle_name'   => $fullName['middle_name'],
                    'last_name'     => $fullName['last_name'],
                    'suffix'        => $rows[$i][3],
                    'gender'        => $rows[$i][4],
                    'email'         => $rows[$i][5],
                    'employee_id'   => $rows[$i][6],
                    'employee_role' => $rows[$i][7],
                ];

                if (EmployeeDetail::where('employee_id', $temp['employee_id'])->exists()) {
                    $existingData[] = $temp;
                } else {
                    $newData[] = $temp;
                }
            }

            // Apply edits
            foreach ($this->editsNew as $index => $edit) {
                if (isset($newData[$index])) {
                    $newData[$index] = array_merge($newData[$index], $edit);
                }
            }
            foreach ($this->editsExisting as $index => $edit) {
                if (isset($existingData[$index])) {
                    $existingData[$index] = array_merge($existingData[$index], $edit);
                }
            }

            $employees = array_merge($newData, $existingData);

            if ($progress->total_rows !== count($employees)) {
                $progress->update(['total_rows' => count($employees)]);
            }

            $newCount      = 0;
            $updatedCount  = 0;
            $processedRows = 0;
            $stagedUsers   = [];
            $chunks        = array_chunk($employees, self::CHUNK_SIZE);
            $users         = new User();

            // Free the spreadsheet from memory
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $sheet, $rows);
            gc_collect_cycles();

            Log::info('ProcessEmployeeImport: Data parsed', [
                'progress_id' => $this->progressId,
                'total_rows'  => count($employees),
                'chunks'      => count($chunks),
            ]);

            foreach ($chunks as $chunkIndex => $chunk) {
                DB::beginTransaction();

                foreach ($chunk as $item) {
                    if (empty(array_filter($item))) {
                        $processedRows++;
                        continue;
                    }

                    $result = $this->processRow($item, $users);

                    if ($result === 'new') {
                        $newCount++;
                        $stagedUsers[] = $item['email'];
                    } elseif ($result === 'updated') {
                        $updatedCount++;
                    }

                    $processedRows++;
                }

                DB::commit();

                $progress->update([
                    'processed_rows' => $processedRows,
                    'new_count'      => $newCount,
                    'updated_count'  => $updatedCount,
                ]);

                // Release Eloquent model memory between chunks
                gc_collect_cycles();

                Log::debug('ProcessEmployeeImport: Chunk committed', [
                    'progress_id'    => $this->progressId,
                    'chunk_index'    => $chunkIndex,
                    'processed_rows' => $processedRows,
                ]);
            }

            // Run stored procedure to move staged users to their permanent tables
            DB::statement('CALL DistributeStagingUsers()');

            // Send account notification emails
            foreach ($stagedUsers as $email) {
                $employee = User::where('email', $email)->first();
                if (!$employee) {
                    Log::warning('ProcessEmployeeImport: Employee not found after distribution', [
                        'email' => $email,
                    ]);
                    continue;
                }
                $this->sendAccountNotification($employee);
            }

            $progress->update([
                'status'         => 'completed',
                'processed_rows' => count($employees),
                'new_count'      => $newCount,
                'updated_count'  => $updatedCount,
            ]);

            Log::info('ProcessEmployeeImport: Completed successfully', [
                'progress_id'  => $this->progressId,
                'new_count'    => $newCount,
                'updated_count' => $updatedCount,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            $progress->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('ProcessEmployeeImport: Job failed', [
                'progress_id'   => $this->progressId,
                'error_message' => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            throw $e;
        } finally {
            if (file_exists($fullPath)) {
                @unlink($fullPath);
                Log::info('ProcessEmployeeImport: Deleted temp file', ['path' => $fullPath]);
            }
        }
    }

    /**
     * Extracts the first name, middle name, last name, and suffix from a full name.
     */
    private function extractNameParts(string $fullName): array
    {
        $suffixes     = ['Jr', 'Jr.', 'Sr', 'Sr.', 'II', 'III', 'IV', 'V', 'PhD', 'MD', 'Esq'];
        $normSuffixes = array_map(fn($s) => strtolower(rtrim($s, '.')), $suffixes);

        $parts      = explode(',', $fullName, 2);
        $lastName   = trim($parts[0] ?? '');
        $otherParts = trim($parts[1] ?? '');

        if ($otherParts === '') {
            return ['first_name' => '', 'middle_name' => '', 'last_name' => $lastName, 'suffix' => ''];
        }

        $namePieces  = preg_split('/\s+/', $otherParts);
        $firstName   = '';
        $middleName  = '';
        $suffix      = '';
        $suffixIndex = null;

        for ($i = 1; $i < count($namePieces); $i++) {
            $normalized = strtolower(rtrim($namePieces[$i], '.'));
            if (in_array($normalized, $normSuffixes, true)) {
                $suffixIndex = $i;
                $suffix      = $namePieces[$i];
                break;
            }
        }

        if ($suffixIndex !== null) {
            $firstName  = implode(' ', array_slice($namePieces, 0, $suffixIndex));
            $middleName = implode(' ', array_slice($namePieces, $suffixIndex + 1));
        } else {
            $firstName  = $namePieces[0] ?? '';
            $middleName = count($namePieces) > 1 ? implode(' ', array_slice($namePieces, 1)) : '';
        }

        return [
            'first_name'  => $firstName,
            'middle_name' => $middleName,
            'last_name'   => $lastName,
            'suffix'      => $suffix,
        ];
    }

    /**
     * Process a single employee row.
     *
     * Returns one of: 'new' | 'updated' | 'skipped'
     *
     * @param array<string, mixed> $item
     * @return string
     * @throws \Exception
     */
    private function processRow(array $item, User $usersModel): string
    {
        $validator = Validator::make($item, [
            'rfid'          => 'nullable|string|min:10|regex:/^[0-9]+$/u',
            'first_name'    => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'middle_name'   => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'last_name'     => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'suffix'        => 'nullable|string|max:10|regex:/^[\pL\s\-\'\.]+$/u',
            'gender'        => 'required|string|in:' . implode(',', $this->extractEnums($usersModel->getTable(), 'gender')),
            'email'         => 'required|string|email|max:255',
            'employee_role' => 'required|string|in:' . implode(',', UserGroup::pluck('category')->toArray()),
            'employee_id'   => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            throw new \Exception(
                'Validation error: '
                . $validator->errors()->first()
                . ' for faculty/staff: '
                . ($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? '')
            );
        }

        $existingEmployee = User::whereHas('employees', function ($query) use ($item) {
            $query->where('employee_id', $item['employee_id']);
        })->with('employees')->first();

        if ($existingEmployee) {
            if (
                $existingEmployee->rfid                       == $item['rfid']
                && $existingEmployee->first_name              == $item['first_name']
                && $existingEmployee->middle_name             == $item['middle_name']
                && $existingEmployee->last_name               == $item['last_name']
                && $existingEmployee->suffix                  == $item['suffix']
                && $existingEmployee->gender                  == $item['gender']
                && $existingEmployee->email                   == $item['email']
                && $existingEmployee->employees->employee_role == $item['employee_role']
                && $existingEmployee->employees->employee_id  == $item['employee_id']
            ) {
                return 'skipped';
            }

            $existingEmployee->update([
                'first_name'  => $item['first_name'],
                'middle_name' => $item['middle_name'],
                'last_name'   => $item['last_name'],
                'suffix'      => $item['suffix'],
                'gender'      => $item['gender'],
                'email'       => $item['email'],
            ]);

            $existingEmployee->employees()->update([
                'employee_role' => $item['employee_role'],
                'employee_id'   => $item['employee_id'],
            ]);

            return 'updated';
        }

        // New employee — guard against duplicates
        if (StagingUser::where('email', $item['email'])->exists()) {
            throw new \Exception(
                'Email already exists for user: '
                . $item['first_name'] . ' ' . $item['last_name']
            );
        }

        if (!empty($item['rfid']) && StagingUser::where('rfid', $item['rfid'])->exists()) {
            throw new \Exception(
                'RFID already exists for user: '
                . $item['first_name'] . ' ' . $item['last_name']
            );
        }

        $password = Str::password(8, true, true, true, false);

        StagingUser::create([
            'rfid'          => $item['rfid'],
            'first_name'    => $item['first_name'],
            'middle_name'   => $item['middle_name'],
            'last_name'     => $item['last_name'],
            'suffix'        => $item['suffix'],
            'gender'        => $item['gender'],
            'email'         => $item['email'],
            'password'      => Hash::make($password),
            'employee_id'   => $item['employee_id'],
            'employee_role' => $item['employee_role'],
            'user_type'     => 'employee',
        ]);

        cache()->put("import_employee_pwd_{$item['email']}", $password, now()->addHour());

        return 'new';
    }

    /**
     * Send the account credentials email to the newly created employee.
     */
    private function sendAccountNotification(User $employee): void
    {
        $password = cache()->pull("import_employee_pwd_{$employee->email}");
        if (!$password) {
            Log::warning('ProcessEmployeeImport: No cached password for email notification', [
                'email' => $employee->email,
            ]);
            return;
        }

        try {
            Mail::to($employee->email)->send(new AccountEmailMessage($employee, $password));

            Log::info('ProcessEmployeeImport: Account notification email sent', [
                'employee_id' => $employee->id,
                'email'       => $employee->email,
            ]);
        } catch (\Exception $e) {
            Log::error('ProcessEmployeeImport: Failed to send email', [
                'email'   => $employee->email,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Extract ENUM values from a MySQL column definition.
     *
     * @return list<string>
     */
    private function extractEnums(string $table, string $columnName): array
    {
        $column = DB::select("SHOW COLUMNS FROM {$table} LIKE '{$columnName}'");
        if (empty($column)) {
            return ['N/A'];
        }
        preg_match('/enum\((.*)\)$/', $column[0]->Type, $matches);
        return isset($matches[1]) ? str_getcsv($matches[1], ',', "'") : ['N/A'];
    }
}
