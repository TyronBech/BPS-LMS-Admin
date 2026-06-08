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
        $progress->update(['status' => 'processing']);

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

                if (StudentDetail::where('id_number', $temp['id_number'])->exists()) {
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
            $stagedUsers    = [];
            $chunks         = array_chunk($students, self::CHUNK_SIZE);
            $users          = new User();

            // Free the spreadsheet from memory
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $sheet, $rows);
            gc_collect_cycles();

            Log::info('ProcessStudentImport: Data parsed', [
                'progress_id' => $this->progressId,
                'total_rows'  => count($students),
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
                        if (!empty($item['email'])) {
                            $stagedUsers[] = $item['email'];
                        }
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

                Log::debug('ProcessStudentImport: Chunk committed', [
                    'progress_id'    => $this->progressId,
                    'chunk_index'    => $chunkIndex,
                    'processed_rows' => $processedRows,
                ]);
            }

            // Run the stored procedure to distribute staged users to their final tables
            DB::statement('CALL DistributeStagingUsers()');

            // Send account notification emails to newly created students
            foreach ($stagedUsers as $email) {
                $student = User::where('email', $email)->first();
                if (!$student) {
                    Log::warning('ProcessStudentImport: Student not found after distribution', [
                        'email' => $email,
                    ]);
                    continue;
                }
                $this->sendAccountNotification($student);
            }

            $progress->update([
                'status'         => 'completed',
                'processed_rows' => count($students),
                'new_count'      => $newCount,
                'updated_count'  => $updatedCount,
            ]);

            Log::info('ProcessStudentImport: Completed successfully', [
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
     * Process a single student row.
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
            'rfid'        => 'nullable|string|min:10|regex:/^[0-9]+$/u',
            'first_name'  => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'middle_name' => 'nullable|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'last_name'   => 'required|string|max:50|regex:/^[\pL\s\-\'\.]+$/u',
            'suffix'      => 'nullable|string|max:10|regex:/^[\pL\s\-\'\.]+$/u',
            'id_number'   => 'required|string|max:20',
            'grade_level' => 'required|numeric|min:7|max:12',
            'section'     => 'required|string|max:50',
            'gender'      => 'required|string|in:' . implode(',', $this->extractEnums($usersModel->getTable(), 'gender')),
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

        $existingStudent = User::whereHas('students', function ($query) use ($item) {
            $query->where('id_number', $item['id_number']);
        })->with('students')->first();

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
                && $existingStudent->students->level   == $item['grade_level']
                && $existingStudent->students->section == $item['section']
            ) {
                return 'skipped';
            }

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

            return 'updated';
        }

        // New student — check for duplicate email / RFID first
        if (!empty($item['email']) && User::where('email', $item['email'])->exists()) {
            throw new \Exception(
                'Email already exists for student: '
                . $item['first_name'] . ' ' . $item['last_name']
            );
        }

        if (!empty($item['rfid']) && User::where('rfid', $item['rfid'])->exists()) {
            throw new \Exception(
                'RFID already exists for student: '
                . $item['first_name'] . ' ' . $item['last_name']
            );
        }

        $password = Str::password(8, true, true, true, false);

        StagingUser::create([
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
        ]);

        // Store plain password temporarily on the model instance so we can email it later.
        // We attach it to a transient property that won't be persisted.
        // This is safe because the StagingUser row is consumed by the stored procedure.
        cache()->put("import_student_pwd_{$item['email']}", $password, now()->addHour());

        return 'new';
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
}
