<?php

namespace App\Jobs;

use App\Mail\AccountEmailMessage;
use App\Models\ImportProgress;
use App\Models\StagingUser;
use App\Models\StudentDetail;
use App\Models\User;
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

class ProcessStudentImport implements ShouldQueue
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
     * @param array                      $editsNew     User edits on new students.
     * @param array                      $editsExisting User edits on existing students.
     */
    public function __construct(
        private readonly string $filePath,
        private readonly int $progressId,
        private readonly int $initiatedBy,
        private readonly array $editsNew = [],
        private readonly array $editsExisting = [],
    ) {}

    /**
     * Called by the queue worker when the job is permanently failed.
     *
     * This is a safety net: if the try-catch inside handle() does not run
     * (e.g., timeout, OOM kill, or serialization error), this method
     * ensures the ImportProgress record is still marked as failed.
     *
     * @param \Throwable $exception
     */
    public function failed(\Throwable $exception): void
    {
        $progress = ImportProgress::find($this->progressId);
        if ($progress && $progress->isActive()) {
            $progress->update([
                'status'        => 'failed',
                'error_message' => $progress->error_message ?: $exception->getMessage(),
            ]);
        }

        Log::error('ProcessStudentImport: Job permanently failed', [
            'progress_id'   => $this->progressId,
            'error_message' => $exception->getMessage(),
        ]);
    }

    /**
     * Execute the import.
     */
    public function handle(): void
    {
        // Allow 1 GB for large imports
        ini_set('memory_limit', '1G');

        // Prevent Laravel from keeping all executed queries in memory
        DB::disableQueryLog();

        /** @var ImportProgress $progress */
        $progress = ImportProgress::findOrFail($this->progressId);
        if ($progress->status === 'cancelled') {
            return;
        }
        $progress->update(['status' => 'processing']);

        // Empty staging table to prevent orphaned data from previous failed/killed runs
        DB::table('usr_staging_users')->delete();

        $fullPath = \Illuminate\Support\Facades\Storage::path($this->filePath);

        Log::info('ProcessStudentImport: Job started', [
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

            // Extract all id_numbers first to bulk-query existence and avoid N+1 queries
            $idNumbers = [];
            for ($i = 18; $i < count($rows); $i++) {
                if (empty(array_filter(array_slice($rows[$i], 1, 7)))) {
                    continue;
                }
                $idNumber = $rows[$i][6] ?? null;
                if ($idNumber) {
                    $idNumbers[] = $idNumber;
                }
            }

            $existingIdNumbers = [];
            if (!empty($idNumbers)) {
                $dbIdNumbers = StudentDetail::whereIn('id_number', $idNumbers)
                    ->pluck('id_number')
                    ->toArray();
                $existingIdNumbers = array_flip(array_map(function ($val) {
                    return strtolower(trim($val));
                }, $dbIdNumbers));
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
                    'rfid'        => $rows[$i][1],
                    'first_name'  => $fullName['first_name'],
                    'middle_name' => $fullName['middle_name'],
                    'last_name'   => $fullName['last_name'],
                    'suffix'      => $rows[$i][3],
                    'gender'      => $rows[$i][4],
                    'email'       => $rows[$i][5],
                    'id_number'   => $rows[$i][6],
                    'grade_level' => $rows[$i][7],
                    'section'     => $rows[$i][8],
                ];

                $lookupId = strtolower(trim($temp['id_number']));
                if (isset($existingIdNumbers[$lookupId])) {
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

            $students = array_merge($newData, $existingData);

            if ($progress->total_rows !== count($students)) {
                $progress->update(['total_rows' => count($students)]);
            }

            $newCount       = 0;
            $updatedCount   = 0;
            $processedRows  = 0;
            $newEmails      = [];
            $users          = new User();

            // Fetch gender enums once to avoid N+1 SHOW COLUMNS queries
            $genderEnums = $this->extractEnums($users->getTable(), 'gender');

            // Free the spreadsheet from memory
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $sheet, $rows, $existingIdNumbers, $idNumbers);
            gc_collect_cycles();

            Log::info('ProcessStudentImport: Data parsed', [
                'progress_id' => $this->progressId,
                'total_rows'  => count($students),
            ]);

            $chunks = array_chunk($students, self::CHUNK_SIZE);
            unset($students);
            gc_collect_cycles();

            foreach ($chunks as $chunkIndex => $chunk) {
                $progress->refresh();
                if ($progress->status === 'cancelled') {
                    return;
                }

                // Bulk-fetch existing students in this chunk to avoid N+1 queries
                $chunkIdNumbers = array_filter(array_column($chunk, 'id_number'));
                $existingUsers = [];
                if (!empty($chunkIdNumbers)) {
                    $existingUsers = User::whereHas('students', function ($query) use ($chunkIdNumbers) {
                        $query->whereIn('id_number', $chunkIdNumbers);
                    })->with('students')->get()->keyBy(function ($user) {
                        return strtolower(trim($user->students->id_number));
                    })->all();
                }

                // Bulk-fetch duplicate check targets for emails and RFIDs in this chunk to avoid N+1 queries
                $chunkEmails = array_filter(array_column($chunk, 'email'));
                $chunkRfids = array_filter(array_column($chunk, 'rfid'));

                $existingEmails = [];
                if (!empty($chunkEmails)) {
                    $dbEmails = array_merge(
                        User::whereIn('email', $chunkEmails)->pluck('email')->toArray(),
                        StagingUser::whereIn('email', $chunkEmails)->pluck('email')->toArray()
                    );
                    $existingEmails = array_flip(array_map('strtolower', $dbEmails));
                }

                $existingRfids = [];
                if (!empty($chunkRfids)) {
                    $dbRfids = array_merge(
                        User::whereIn('rfid', $chunkRfids)->pluck('rfid')->toArray(),
                        StagingUser::whereIn('rfid', $chunkRfids)->pluck('rfid')->toArray()
                    );
                    $existingRfids = array_flip($dbRfids);
                }

                DB::beginTransaction();

                try {
                    $stagingInserts = [];
                    foreach ($chunk as $item) {
                        if (empty(array_filter($item))) {
                            $processedRows++;
                            continue;
                        }

                        $lookupKey = strtolower(trim($item['id_number']));
                        $existingStudent = $existingUsers[$lookupKey] ?? null;
                        [$result, $stagingData] = $this->processRow($item, $users, $genderEnums, $existingEmails, $existingRfids, $existingStudent);

                        if ($result === 'new') {
                            $newCount++;
                            if (!empty($item['email'])) {
                                $newEmails[] = $item['email'];
                            }
                            if ($stagingData) {
                                $stagingInserts[] = $stagingData;
                            }
                        } elseif ($result === 'updated') {
                            $updatedCount++;
                        }

                        $processedRows++;
                    }

                    if (!empty($stagingInserts)) {
                        StagingUser::insert($stagingInserts);
                    }

                    DB::commit();

                    if (!app()->environment('testing')) {
                        DB::statement('CALL DistributeStagingUsers()');
                    }

                    $progress->update([
                        'processed_rows' => $processedRows,
                        'new_count'      => $newCount,
                        'updated_count'  => $updatedCount,
                    ]);

                    // Release memory explicitly
                    unset($chunk, $existingUsers, $chunkIdNumbers, $chunkEmails, $chunkRfids, $existingEmails, $existingRfids);
                    unset($chunks[$chunkIndex]);
                    gc_collect_cycles();

                    Log::debug('ProcessStudentImport: Chunk committed', [
                        'progress_id'    => $this->progressId,
                        'chunk_index'    => $chunkIndex,
                        'processed_rows' => $processedRows,
                    ]);
                } catch (\Throwable $chunkError) {
                    if (DB::transactionLevel() > 0) {
                        DB::rollBack();
                    }

                    $failedRow = $processedRows + 1;
                    $friendlyMsg = $this->getFriendlyErrorMessage($chunkError);
                    $errorContext = "Row ~{$failedRow}: " . $friendlyMsg;

                    $progress->update([
                        'status'         => 'failed',
                        'processed_rows' => $processedRows,
                        'new_count'      => $newCount,
                        'error_message'  => $errorContext,
                    ]);

                    Log::error('ProcessStudentImport: Chunk failed — job stopped', [
                        'progress_id'    => $this->progressId,
                        'chunk_index'    => $chunkIndex,
                        'processed_rows' => $processedRows,
                        'error_message'  => $errorContext,
                        'trace'          => $chunkError->getTraceAsString(),
                    ]);

                    return;
                }
            }

            // Distribute staging users once at the end
            DB::statement('CALL DistributeStagingUsers()');

            // Send account notification emails for new students
            foreach ($newEmails as $email) {
                $student = User::where('email', $email)->first();
                if ($student) {
                    $this->sendAccountNotification($student);
                }
            }

            $progress->update([
                'status'         => 'completed',
                'processed_rows' => $processedRows,
                'new_count'      => $newCount,
                'updated_count'  => $updatedCount,
            ]);

            Log::info('ProcessStudentImport: Completed successfully', [
                'progress_id'  => $this->progressId,
                'new_count'    => $newCount,
                'updated_count' => $updatedCount,
            ]);
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            $progress->refresh();
            if ($progress->status !== 'cancelled') {
                $progress->update([
                    'status'        => 'failed',
                    'error_message' => $this->getFriendlyErrorMessage($e),
                ]);
            }

            Log::error('ProcessStudentImport: Job failed', [
                'progress_id'   => $this->progressId,
                'error_message' => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            throw $e;
        } finally {
            if (file_exists($fullPath)) {
                @unlink($fullPath);
                Log::info('ProcessStudentImport: Deleted temp file', ['path' => $fullPath]);
            }
        }
    }

    /**
     * Extracts the first name, middle name, last name, and suffix from a full name.
     */
    private function extractNameParts(string $fullName): array
    {
        $parts      = explode(',', $fullName, 2);
        $lastName   = preg_replace('/\s+/', ' ', trim($parts[0] ?? ''));
        $otherParts = preg_replace('/\s+/', ' ', trim($parts[1] ?? ''));

        if ($otherParts === '') {
            return ['first_name' => '', 'middle_name' => '', 'last_name' => $lastName, 'suffix' => ''];
        }

        $namePieces = preg_split('/\s+/', $otherParts, -1, PREG_SPLIT_NO_EMPTY);
        $middleName = count($namePieces) > 1 ? array_pop($namePieces) : '';
        $firstName  = implode(' ', $namePieces);

        return [
            'first_name'  => $firstName,
            'middle_name' => $middleName,
            'last_name'   => $lastName,
            'suffix'      => '',
        ];
    }

    /**
     * Process a single student row.
     *
     * Returns one of: 'new' | 'updated' | 'skipped'
     *
     * @param array<string, mixed> $item
     * @return string
     * @throws \Exception
     */
    private function processRow(
        array $item,
        User $usersModel,
        array $genderEnums,
        array $existingEmails,
        array $existingRfids,
        ?User $existingStudent = null
    ): array {
        if ($existingStudent) {
            // Check if anything actually changed
            if (
                $existingStudent->rfid        == $item['rfid']
                && $existingStudent->first_name   == $item['first_name']
                && $existingStudent->middle_name  == $item['middle_name']
                && $existingStudent->last_name    == $item['last_name']
                && $existingStudent->suffix       == $item['suffix']
                && $existingStudent->gender       == $item['gender']
                && $existingStudent->email        == $item['email']
                && $existingStudent->students
                && $existingStudent->students->level   == $item['grade_level']
                && $existingStudent->students->section == $item['section']
            ) {
                return ['skipped', null];
            }
        }

        $validator = Validator::make($item, [
            'rfid'        => 'nullable|string|min:10|regex:/^[0-9]+$/u',
            'first_name'  => 'required|string|max:50|regex:/^[\pL\s\-\'\.\/\_\(\)\[\]\{\}\&\,]+$/u',
            'middle_name' => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.\/\_\(\)\[\]\{\}\&\,]+$/u',
            'last_name'   => 'required|string|max:50|regex:/^[\pL\s\-\'\.\/\_\(\)\[\]\{\}\&\,]+$/u',
            'suffix'      => 'nullable|string|max:10|regex:/^[\pL\s\-\'\.\/\_\(\)\[\]\{\}\&\,]+$/u',
            'id_number'   => 'required|string|max:20',
            'grade_level' => 'required|string|max:50',
            'section'     => 'required|string|max:255',
            'gender'      => 'required|string|in:' . implode(',', $genderEnums),
            'email'       => 'nullable|string|email',
        ]);

        if ($validator->fails()) {
            throw new \Exception(
                'Validation error: '
                    . $validator->errors()->first()
                    . ' for student: '
                    . ($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? '')
            );
        }

        if ($existingStudent) {
            $existingStudent->update([
                'rfid'        => $item['rfid'],
                'first_name'  => $item['first_name'],
                'middle_name' => $item['middle_name'],
                'last_name'   => $item['last_name'],
                'suffix'      => $item['suffix'],
                'gender'      => $item['gender'],
                'email'       => $item['email'],
            ]);

            $existingStudent->students()->update([
                'level'   => $item['grade_level'],
                'section' => $item['section'],
            ]);

            return ['updated', null];
        }

        // New student — check for duplicate email / RFID first
        if (!empty($item['email']) && isset($existingEmails[strtolower($item['email'])])) {
            throw new \Exception(
                'Email already exists for student: '
                    . $item['first_name'] . ' ' . $item['last_name']
            );
        }

        if (!empty($item['rfid']) && isset($existingRfids[$item['rfid']])) {
            throw new \Exception(
                'RFID already exists for student: '
                    . $item['first_name'] . ' ' . $item['last_name']
            );
        }

        $password = Str::password(8, true, true, true, false);

        if (!empty($item['email'])) {
            cache()->put("import_student_pwd_{$item['email']}", $password, now()->addHour());
        }

        $now = now();
        $stagingData = [
            'rfid'        => $item['rfid'],
            'first_name'  => $item['first_name'],
            'middle_name' => $item['middle_name'],
            'last_name'   => $item['last_name'],
            'suffix'      => $item['suffix'],
            'gender'      => $item['gender'],
            'email'       => $item['email'],
            'password'    => Hash::make($password),
            'id_number'   => $item['id_number'],
            'level'       => $item['grade_level'],
            'section'     => $item['section'],
            'user_type'   => 'student',
            'created_at'  => $now,
            'updated_at'  => $now,
        ];

        return ['new', $stagingData];
    }

    /**
     * Send the account credentials email to the newly created student.
     */
    private function sendAccountNotification(User $student): void
    {
        $password = cache()->pull("import_student_pwd_{$student->email}");
        if (!$password) {
            Log::warning('ProcessStudentImport: No cached password found for email notification', [
                'email' => $student->email,
            ]);
            return;
        }

        // Set short timeout to prevent slow mail server from stalling the import job
        config(['mail.mailers.smtp.timeout' => 3]);

        try {
            Mail::to($student->email)->send(new AccountEmailMessage($student, $password));

            Log::info('ProcessStudentImport: Account notification email sent', [
                'student_id' => $student->id,
                'email'      => $student->email,
            ]);
        } catch (\Exception $e) {
            Log::error('ProcessStudentImport: Failed to send email', [
                'email'   => $student->email,
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

    /**
     * Translate database exceptions or general errors into user-friendly messages.
     */
    private function getFriendlyErrorMessage(\Throwable $e): string
    {
        if ($e instanceof \Illuminate\Database\QueryException) {
            $errorCode = $e->errorInfo[1] ?? null;
            $sqlMessage = $e->getMessage();

            if ($errorCode == 1062) {
                if (preg_match("/Duplicate entry '(.+?)' for key '(.+?)'/", $sqlMessage, $matches)) {
                    $entry = $matches[1];
                    $key = strtolower($matches[2]);

                    if (str_contains($key, 'email')) {
                        return "The email address '{$entry}' already exists.";
                    }
                    if (str_contains($key, 'rfid')) {
                        return "The RFID '{$entry}' already exists.";
                    }
                    if (str_contains($key, 'employee_id')) {
                        return "The employee ID '{$entry}' already exists.";
                    }
                    if (str_contains($key, 'id_number')) {
                        return "The ID number '{$entry}' already exists.";
                    }

                    return "Duplicate entry detected: '{$entry}'.";
                }
                return "A duplicate entry was detected.";
            }

            if ($errorCode == 1451 || $errorCode == 1452) {
                return "This record refers to or is referenced by a value that does not exist in our database.";
            }

            return "A database error occurred: " . ($e->errorInfo[2] ?? $e->getMessage());
        }

        return $e->getMessage();
    }
}
