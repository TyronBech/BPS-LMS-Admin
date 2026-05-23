<?php

namespace App\Jobs;

use App\Models\Book;
use App\Models\BkLastAccession;
use App\Models\Category;
use App\Models\ImportProgress;
use App\Models\Inventory;
use App\Models\SubjectAccessCode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Milon\Barcode\DNS1D;

class ProcessMaterialImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum wall-clock seconds allowed for this job.
     *
     * @var int
     */
    public int $timeout = 3600;

    /**
     * Do not auto-retry on failure — the user must re-upload.
     *
     * @var int
     */
    public int $tries = 1;

    /**
     * Number of rows to process per DB transaction chunk.
     * Each chunk is committed independently — if a chunk fails,
     * only that chunk is rolled back and the job stops.
     */
    private const CHUNK_SIZE = 100;

    /**
     * @param string                            $filePath     Path to the temp Excel file in storage.
     * @param int                               $progressId   ID of the ImportProgress record.
     * @param int                               $initiatedBy  User ID who started the import.
     * @param array<int, array<string, mixed>>  $edits        Any user edits made on preview pages.
     */
    public function __construct(
        private readonly string $filePath,
        private readonly int $progressId,
        private readonly int $initiatedBy,
        private readonly array $edits = [],
    ) {}

    /**
     * Execute the import job.
     *
     * Strategy:
     *   - Parse the Excel file into a flat array, then immediately free the spreadsheet.
     *   - Process rows in chunks of CHUNK_SIZE inside individual DB::transaction() calls.
     *   - On chunk success → commit, update progress, flush memory.
     *   - On chunk failure → rollback that chunk only, mark the job as failed,
     *     and tell the user which row (by accession) caused the error.
     *   - Duplicate accessions are silently skipped (not treated as fatal errors).
     */
    public function handle(): void
    {
        // Allow 1 GB for large imports — barcode JPEG generation is memory-intensive
        ini_set('memory_limit', '1G');

        // Prevent Laravel from keeping all executed queries in memory during the long job
        DB::disableQueryLog();

        /** @var ImportProgress $progress */
        $progress = ImportProgress::findOrFail($this->progressId);
        $progress->update(['status' => 'processing']);

        $fullPath = \Illuminate\Support\Facades\Storage::path($this->filePath);

        Log::info('ProcessMaterialImport: Job started', [
            'progress_id'  => $this->progressId,
            'file_path'    => $fullPath,
            'initiated_by' => $this->initiatedBy,
        ]);

        try {
            // ─── 1. Parse the Excel file ────────────────────────────────────────
            if (!file_exists($fullPath)) {
                throw new \Exception("Uploaded Excel file not found: " . $this->filePath);
            }

            $data = $this->parseExcel($fullPath);

            if ($progress->total_rows !== count($data)) {
                $progress->update(['total_rows' => count($data)]);
            }

            // ─── 2. Process rows in transactional chunks ────────────────────────
            $newCount      = 0;
            $updatedCount  = 0;
            $skippedCount  = 0;
            $processedRows = 0;
            $chunks        = array_chunk($data, self::CHUNK_SIZE, true);

            // Free the parsed data array reference for chunked processing
            unset($data);

            Log::info('ProcessMaterialImport: Data parsed', [
                'progress_id' => $this->progressId,
                'total_rows'  => $progress->total_rows,
                'chunks'      => count($chunks),
            ]);

            foreach ($chunks as $chunkIndex => $chunk) {
                // Each chunk gets its own transaction — success = commit, failure = rollback + stop
                try {
                    DB::beginTransaction();
                    DB::statement('SET @current_user_id = ?', [$this->initiatedBy]);

                    foreach ($chunk as $globalRowIndex => $item) {
                        $rowNumber = $globalRowIndex + 1; // 1-based row number for user display

                        $status = $this->processRow($item, $rowNumber);
                        if ($status === 'new') {
                            $newCount++;
                        } elseif ($status === 'updated') {
                            $updatedCount++;
                        } else {
                            $skippedCount++;
                        }
                    }

                    DB::commit();

                    // Update progress after each successful chunk
                    $processedRows += count($chunk);
                    $progress->update([
                        'processed_rows' => $processedRows,
                        'new_count'      => $newCount,
                        'updated_count'  => $updatedCount,
                    ]);

                    Log::debug('ProcessMaterialImport: Chunk committed', [
                        'progress_id'    => $this->progressId,
                        'chunk_index'    => $chunkIndex,
                        'processed_rows' => $processedRows,
                        'new_in_chunk'   => $newCount,
                        'skipped_total'  => $skippedCount,
                        'memory_usage'   => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                    ]);

                    // ─── MEMORY CLEANUP after every chunk ────────────────────────
                    // Force PHP to reclaim all barcode JPEG buffers, Eloquent model
                    // instances, and any GD image resources created during this chunk.
                    unset($chunk);
                    gc_collect_cycles();

                } catch (\Throwable $chunkError) {
                    // Rollback ONLY the current chunk
                    DB::rollBack();

                    // Figure out which row caused the error
                    $failedRow = $processedRows + 1; // approximate row within the chunk
                    $errorContext = "Row ~{$failedRow}: " . $chunkError->getMessage();

                    $progress->update([
                        'status'         => 'failed',
                        'processed_rows' => $processedRows,
                        'new_count'      => $newCount,
                        'error_message'  => $errorContext,
                    ]);

                    Log::error('ProcessMaterialImport: Chunk failed — job stopped', [
                        'progress_id'    => $this->progressId,
                        'chunk_index'    => $chunkIndex,
                        'processed_rows' => $processedRows,
                        'error_message'  => $errorContext,
                        'trace'          => $chunkError->getTraceAsString(),
                    ]);

                    // Stop the entire job — previously committed chunks are safe
                    return;
                }
            }

            // ─── 3. All chunks succeeded ────────────────────────────────────────
            $progress->update([
                'status'         => 'completed',
                'processed_rows' => $processedRows,
                'new_count'      => $newCount,
                'updated_count'  => $updatedCount,
            ]);

            Log::info('ProcessMaterialImport: Completed successfully', [
                'progress_id'   => $this->progressId,
                'new_count'     => $newCount,
                'updated_count' => $updatedCount,
                'skipped_count' => $skippedCount,
            ]);

        } catch (\Throwable $e) {
            // Catch-all for parsing errors or unexpected failures before chunks start
            $progress->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('ProcessMaterialImport: Job failed', [
                'progress_id'   => $this->progressId,
                'error_message' => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);
        } finally {
            if (file_exists($fullPath)) {
                @unlink($fullPath);
                Log::info('ProcessMaterialImport: Deleted temp file', ['path' => $fullPath]);
            }
        }
    }

    /**
     * Parse the Excel file into a flat array and immediately free the spreadsheet from memory.
     *
     * @param string $fullPath Absolute filesystem path to the .xlsx file.
     * @return array<int, array<string, mixed>> Indexed array of parsed rows.
     * @throws \Exception If the file cannot be read.
     */
    private function parseExcel(string $fullPath): array
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $data = [];
        $headerRowIndex = 17;
        $baseCol = 0;

        if (isset($rows[$headerRowIndex][1]) && strtolower(trim((string) $rows[$headerRowIndex][1])) === 'accession') {
            $baseCol = 1;
        }

        for ($i = 18; $i < count($rows); $i++) {
            $isEmptyRow = true;
            for ($col = $baseCol; $col <= $baseCol + 23; $col++) {
                if (isset($rows[$i][$col]) && trim((string) $rows[$i][$col]) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }

            if ($isEmptyRow) {
                continue;
            }

            $data[] = [
                'accession'            => $this->cleanString($rows[$i][$baseCol] ?? null),
                'title'                => $this->cleanString($rows[$i][$baseCol + 1] ?? null),
                'authors'              => [
                    'Main author'      => $this->cleanString($rows[$i][$baseCol + 2] ?? null),
                    'Corporate author' => $this->cleanString($rows[$i][$baseCol + 3] ?? null),
                    'Added authors'    => $this->cleanString($rows[$i][$baseCol + 4] ?? null),
                    'Contributors'     => $this->cleanString($rows[$i][$baseCol + 5] ?? null),
                ],
                'edition'              => $this->cleanString($rows[$i][$baseCol + 6] ?? null),
                'call_number'          => $this->cleanString($rows[$i][$baseCol + 7] ?? null),
                'isbn'                 => $this->cleanString($rows[$i][$baseCol + 8] ?? null),
                'description'          => [
                    'Description'   => $this->cleanString($rows[$i][$baseCol + 9] ?? null),
                    'Content notes' => $this->cleanString($rows[$i][$baseCol + 10] ?? null),
                    'Abstract'      => $this->cleanString($rows[$i][$baseCol + 11] ?? null),
                    'Reviews'       => $this->cleanString($rows[$i][$baseCol + 12] ?? null),
                    'Extent'        => $this->cleanString($rows[$i][$baseCol + 13] ?? null),
                    'Acc Material'  => $this->cleanString($rows[$i][$baseCol + 14] ?? null),
                ],
                'place_of_publication' => $this->cleanString($rows[$i][$baseCol + 15] ?? null),
                'publisher'            => $this->cleanString($rows[$i][$baseCol + 16] ?? null),
                'copyrights'           => $this->cleanString($rows[$i][$baseCol + 17] ?? null),
                'location'             => $this->cleanString($rows[$i][$baseCol + 18] ?? null),
                'languages'            => $this->cleanString($rows[$i][$baseCol + 19] ?? null),
                'book_type'            => $this->cleanString($rows[$i][$baseCol + 20] ?? null) ?? 'Print',
                'category'             => $this->cleanString($rows[$i][$baseCol + 21] ?? null),
                'digital_copy_url'     => $this->cleanString($rows[$i][$baseCol + 22] ?? null),
                'subject'              => $this->cleanString($rows[$i][$baseCol + 23] ?? null),
            ];
        }

        // Apply user edits if any
        foreach ($this->edits as $index => $edit) {
            if (isset($data[$index])) {
                if (isset($edit['authors'])) {
                    $data[$index]['authors'] = array_merge($data[$index]['authors'], $edit['authors']);
                }
                if (isset($edit['description'])) {
                    $data[$index]['description'] = array_merge($data[$index]['description'], $edit['description']);
                }
                $data[$index] = array_merge(
                    $data[$index],
                    array_diff_key($edit, ['authors' => 1, 'description' => 1])
                );
            }
        }

        // Free the spreadsheet from memory — it can consume 50–100 MB for large files
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $sheet, $rows);
        gc_collect_cycles();

        return $data;
    }

    /**
     * Process a single material row.
     *
     * @param array<string, mixed> $item      The parsed row data.
     * @param int                  $rowNumber  1-based row number for error reporting.
     * @return string 'new', 'updated', or 'skipped'
     * @throws \Exception If validation or category lookup fails.
     */
    private function processRow(array $item, int $rowNumber): string
    {
        // ── Validation ──────────────────────────────────────────────────────
        $validator = Validator::make($item, [
            'accession' => 'required|string|max:255',
            'title'     => 'required|string|max:255',
            'category'  => 'required|string',
            'book_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new \Exception(
                "Row {$rowNumber} (accession: "
                . ($item['accession'] ?? 'Unknown')
                . '): ' . $validator->errors()->first()
            );
        }

        // ── Category lookup ─────────────────────────────────────────────────
        $category = Category::where(DB::raw('lower(name)'), strtolower($item['category']))->first();
        if (!$category) {
            throw new \Exception(
                "Row {$rowNumber} (accession: {$item['accession']}): Category not found: {$item['category']}"
            );
        }

        // ── Type mapping ────────────────────────────────────────────────────
        $typeMap = [
            'print'     => 'Print',
            'non-print' => 'Non-print',
            'e-books'   => 'E-books',
            'ebooks'    => 'E-books',
            'ebook'     => 'E-books',
            'e-book'    => 'E-books',
            'physical'  => 'Print',
        ];
        $finalType = $typeMap[strtolower($item['book_type'])] ?? 'Print';

        if ($category->category_type !== $finalType) {
            throw new \Exception(
                "Row {$rowNumber} (accession: {$item['accession']}): "
                . "Category '{$category->name}' is '{$category->category_type}', "
                . "but material type is '{$finalType}'."
            );
        }

        $existingBook = Book::where('accession', $item['accession'])->first();
        $isNew = !$existingBook;
        
        $fillData = [
            'title'                => $item['title'],
            'authors'              => $item['authors'],
            'description'          => $item['description'],
            'edition'              => $item['edition'] ?? null,
            'call_number'          => $item['call_number'] ?? null,
            'isbn'                 => $item['isbn'] ?? null,
            'place_of_publication' => $item['place_of_publication'] ?? null,
            'publisher'            => $item['publisher'] ?? null,
            'copyrights'           => $item['copyrights'] ?? null,
            'location'             => $item['location'] ?? null,
            'languages'            => $finalType === 'Print' ? ($item['languages'] ?? null) : null,
            'book_type'            => $finalType,
            'category_id'          => $category->id,
            'digital_copy_url'     => $item['digital_copy_url'] ?? null,
        ];

        $isModelDirty = false;
        $subjectChanged = false;

        if ($isNew) {
            // Include fields only needed during creation
            $barcodeData = (new DNS1D())->getBarcodeJPG($item['accession'], 'C39', 2, 80, [0, 0, 0, 0], false);
            
            $fillData['accession'] = $item['accession'];
            $fillData['barcode'] = $barcodeData;
            $fillData['remarks'] = 'On Shelf';
            $fillData['availability_status'] = 'Available';
            $fillData['condition_status'] = 'New';
            
            $targetBook = Book::create($fillData);
            unset($barcodeData);
        } else {
            $targetBook = $existingBook;

            // Prevent false "dirty" flags on JSON arrays due to MySQL key reordering
            $oldAuthors = is_array($targetBook->authors) ? $targetBook->authors : [];
            $newAuthors = $fillData['authors'];
            ksort($oldAuthors);
            ksort($newAuthors);
            if ($oldAuthors === $newAuthors) {
                unset($fillData['authors']);
            }

            $oldDesc = is_array($targetBook->description) ? $targetBook->description : [];
            $newDesc = $fillData['description'];
            ksort($oldDesc);
            ksort($newDesc);
            if ($oldDesc === $newDesc) {
                unset($fillData['description']);
            }

            $targetBook->fill($fillData);
            
            if ($targetBook->isDirty()) {
                $isModelDirty = true;
                $targetBook->save();
            }
        }

        // ── Subject Access Codes ────────────────────────────────────────────
        $accessCodeIds = [];
        if (!empty($item['subject'])) {
            $rawSubjects = preg_split('/\s*[;,]\s*/', (string) $item['subject'], -1, PREG_SPLIT_NO_EMPTY);
            
            foreach ($rawSubjects as $rawName) {
                $name = trim((string) $rawName);
                $name = preg_replace('/\s+/', ' ', $name);

                if ($name === '') continue;

                /** @var SubjectAccessCode|null $existingCode */
                $existingCode = SubjectAccessCode::withTrashed()
                    ->whereRaw('LOWER(TRIM(access_code)) = ?', [strtolower($name)])
                    ->first();

                if ($existingCode) {
                    if ($existingCode->trashed()) {
                        $existingCode->restore();
                    }
                    $accessCodeIds[] = $existingCode->id;
                } else {
                    $newCode = SubjectAccessCode::create(['access_code' => $name]);
                    $accessCodeIds[] = $newCode->id;
                }
            }
        }

        // Sync subjects
        $syncResult = $targetBook->subjectAccessCodes()->sync($accessCodeIds);
        if (!empty($syncResult['attached']) || !empty($syncResult['detached']) || !empty($syncResult['updated'])) {
            $subjectChanged = true;
        }

        if ($isNew) {
            // ── Update last accession ───────────────────────────────────────────
            BkLastAccession::updateOrCreate(
                ['category_id' => $category->id],
                ['accession_number' => $item['accession']]
            );

            // ── Inventory entry (physical materials only) ───────────────────────
            if ($finalType !== 'E-books') {
                Inventory::create([
                    'book_id'    => $targetBook->id,
                    'is_scanned' => 1,
                    'checked_at' => now(),
                ]);
            }
        }

        // Free the Eloquent model from this scope
        unset($targetBook, $existingBook, $category);

        if ($isNew) {
            return 'new';
        }
        
        if ($isModelDirty || $subjectChanged) {
            return 'updated';
        }
        
        return 'skipped';
    }

    /**
     * Clean a string by trimming it and converting empty strings to null.
     */
    private function cleanString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $val = trim((string) $value);
        return $val === '' ? null : $val;
    }
}
