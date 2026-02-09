<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\Category;
use App\Models\Inventory;
use Milon\Barcode\DNS1D;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BookImportController extends Controller
{
    /**
     * Index page of Book Import
     *
     * This function handles the index page of the Book Import
     * feature. It logs the user's access and clears the session
     * data if the user did not come from a pagination link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        Log::info('Book Import: Index page accessed', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'user_email' => Auth::user()->email,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        if (!$request->has('page') && !$request->has('perPage')) {
            Log::debug('Book Import: Clearing session data', [
                'user_id' => Auth::id(),
                'action' => 'session_clear',
                'cleared_keys' => ['book_import_data'],
            ]);

            $request->session()->forget('book_import_data');
        }

        $showTable = false;
        return view('import.books.books', compact('showTable'));
    }

    /**
     * Handles the upload of a book data Excel file.
     *
     * This function logs the user's access, validates the file,
     * processes the Excel file, and stores the extracted data in the session.
     * It also handles pagination and displays the uploaded data in a table.
     *
     * @throws \Exception
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        Log::info('Book Import: Upload process initiated', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'ip_address' => $request->ip(),
            'has_file' => $request->hasFile('file'),
            'timestamp' => now(),
        ]);

        try {
            $sessionData = $request->session()->get('book_import_data', []);

            if ($request->isMethod('post') && !$request->hasFile('file')) {
                Log::debug('Book Import: Processing pagination request', [
                    'user_id' => Auth::id(),
                    'books_in_session' => count($sessionData),
                ]);

                // Merge submitted edits into the session data
                $submittedBooks = $request->input('books', []);
                foreach ($submittedBooks as $index => $book) {
                    if (isset($sessionData[$index])) {
                        $sessionData[$index] = array_merge($sessionData[$index], $book);
                    }
                }
                $request->session()->put('book_import_data', $sessionData);
                $data = $sessionData;

            } else if ($request->has('page') || $request->has('perPage')) {
                Log::debug('Book Import: Loading data from session for pagination', [
                    'user_id' => Auth::id(),
                    'current_page' => $request->input('page', 1),
                    'per_page' => $request->input('perPage', 10),
                ]);

                // Navigating via GET, just use session data
                $data = $sessionData;

            } else {
                // Initial file upload
                if ($request->file('file') == null) {
                    Log::warning('Book Import: No file selected', [
                        'user_id' => Auth::id(),
                        'timestamp' => now(),
                    ]);

                    return redirect()->route('import.import-books')->with('toast-warning', "Please select a file.");
                }

                $file = $request->file('file');

                Log::info('Book Import: Processing uploaded file', [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getClientMimeType(),
                    'user_id' => Auth::id(),
                    'timestamp' => now(),
                ]);

                $reader = new ReaderXlsx();
                $spreadsheet = $reader->load($file);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                Log::debug('Book Import: Excel file loaded', [
                    'total_rows' => count($rows),
                    'total_columns' => count($rows[0] ?? []),
                    'user_id' => Auth::id(),
                ]);

                $data = array();

                if ($rows[0][0] == null) {
                    Log::error('Book Import: Empty Excel file', [
                        'file_name' => $file->getClientOriginalName(),
                        'user_id' => Auth::id(),
                        'timestamp' => now(),
                    ]);

                    return redirect()->route('import.import-books')->with('toast-error', "Excel file is empty.");
                }

                for ($i = 19; $i < count($rows); $i++) {
                    if (
                        $rows[$i][1] == null &&
                        $rows[$i][2] == null &&
                        $rows[$i][3] == null &&
                        $rows[$i][4] == null &&
                        $rows[$i][5] == null &&
                        $rows[$i][6] == null &&
                        $rows[$i][7] == null &&
                        $rows[$i][8] == null &&
                        $rows[$i][9] == null &&
                        $rows[$i][10] == null &&
                        $rows[$i][11] == null &&
                        $rows[$i][12] == null
                    ) {
                        Log::debug('Book Import: Skipping empty row in Excel', [
                            'row_number' => $i + 1,
                            'user_id' => Auth::id(),
                        ]);
                        continue;
                    }

                    $bookData = array(
                        'accession'             => $rows[$i][1],
                        'call_number'           => $rows[$i][2],
                        'title'                 => $rows[$i][3],
                        'authors'               => $rows[$i][4],
                        'book_type'             => $rows[$i][5],
                        'description'           => $rows[$i][6],
                        'edition'               => $rows[$i][7],
                        'place_of_publication'  => $rows[$i][8],
                        'publisher'             => $rows[$i][9],
                        'copyrights'            => $rows[$i][10],
                        'category'              => $rows[$i][11],
                        'digital_copy_url'      => $rows[$i][12],
                    );

                    Log::debug('Book Import: Book data extracted from Excel', [
                        'row_number' => $i + 1,
                        'accession' => $bookData['accession'],
                        'title' => $bookData['title'],
                        'category' => $bookData['category'],
                        'user_id' => Auth::id(),
                    ]);

                    $data[] = $bookData;
                }

                $request->session()->put('book_import_data', $data);

                Log::info('Book Import: File processed and data stored in session', [
                    'books_count' => count($data),
                    'user_id' => Auth::id(),
                    'timestamp' => now(),
                ]);
            }

            $showTable = true;
            // Paginate the data array
            $perPage = $request->input('perPage', 10);
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentItems = array_slice($data, ($currentPage - 1) * $perPage, $perPage);
            $paginatedData = new LengthAwarePaginator($currentItems, count($data), $perPage, $currentPage, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            Log::debug('Book Import: Preparing pagination', [
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_books' => count($data),
                'user_id' => Auth::id(),
            ]);

        } catch (\Exception $e) {
            $errors = "An error occurred while loading the books: " . $e->getMessage();

            Log::error('Book Import: Upload process failed', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return redirect()->route('import.import-books')->with('toast-error', $errors);
        }

        Log::info('Book Import: Upload view rendered successfully', [
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        return view('import.books.books', ['showTable' => $showTable, 'data' => $paginatedData, 'perPage' => $perPage]);
    }

    /**
     * Stores the books in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Log::info('Book Import: Store process initiated', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'user_email' => Auth::user()->email,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        // Get the submitted data
        $submittedBooks = $request->input('books', []);
        $allBooks = $request->session()->get('book_import_data', []);

        // Update the session data with the submitted (potentially edited) data
        foreach ($submittedBooks as $index => $book) {
            if (isset($allBooks[$index])) {
                $allBooks[$index] = $book;
            }
        }

        $data = $allBooks;
        $errors = "";
        $newBooksCount = 0;
        $books = new Book();

        Log::info('Book Import: Total books to process', [
            'total_count' => count($data),
            'user_id' => Auth::id(),
        ]);

        DB::beginTransaction();
        Log::info('Book Import: Database transaction started', [
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        foreach ($data as $index => $item) {
            $item['book_type'] = strtolower($item['book_type']);
            $item['category'] = strtolower($item['category']);

            Log::debug('Book Import: Validating book data', [
                'row_index' => $index,
                'accession' => $item['accession'],
                'title' => $item['title'],
                'category' => $item['category'],
                'book_type' => $item['book_type'],
                'user_id' => Auth::id(),
            ]);

            $validator = Validator::make($item, [
                'accession'             => 'required|string|max:50',
                'call_number'           => 'nullable|string|max:50',
                'title'                 => 'required|string|max:255',
                'authors'               => 'nullable|string|max:255',
                'book_type'             => 'nullable|string|in:' . implode(',', $this->extract_enums($books->getTable(), 'book_type')),
                'description'           => 'nullable|string',
                'edition'               => 'nullable|string|max:50',
                'place_of_publication'  => 'nullable|string|max:100',
                'publisher'             => 'nullable|string|max:100',
                'copyrights'            => 'nullable|string|max:255',
                'category'              => 'required|string|in:' . implode(',', Category::pluck(DB::raw('lower(name)'))->toArray()),
                'digital_copy_url'      => 'nullable|url',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                $errors = 'Validation error: ' . $validator->errors()->first() . ' for accession: ' . $item['accession'];

                Log::error('Book Import: Validation failed', [
                    'row_index' => $index,
                    'accession' => $item['accession'],
                    'title' => $item['title'],
                    'error_message' => $validator->errors()->first(),
                    'failed_fields' => $validator->errors()->keys(),
                    'user_id' => Auth::id(),
                    'timestamp' => now(),
                ]);

                return redirect()->route('import.import-books')->with('toast-error', $errors);
            }

            try {
                $category = Category::select('id')->where(DB::raw('lower(name)'), strtolower($item['category']))->first();

                if ($category == null) {
                    DB::rollBack();

                    Log::error('Book Import: Category not found', [
                        'category_name' => $item['category'],
                        'accession' => $item['accession'],
                        'title' => $item['title'],
                        'user_id' => Auth::id(),
                        'timestamp' => now(),
                    ]);

                    return redirect()->route('import.import-books')->with('toast-warning', 'Category not found: ' . $item['category']);
                }

                if (Book::where('accession', $item['accession'])->exists()) {
                    DB::rollBack();

                    Log::error('Book Import: Duplicate accession number detected', [
                        'accession' => $item['accession'],
                        'title' => $item['title'],
                        'user_id' => Auth::id(),
                        'timestamp' => now(),
                    ]);

                    return redirect()->route('import.import-books')->with('toast-warning', 'Duplicate accession number found: ' . $item['accession']);
                }

                $barcode = new DNS1D();

                Log::info('Book Import: Creating new book', [
                    'accession' => $item['accession'],
                    'title' => $item['title'],
                    'category' => $item['category'],
                    'category_id' => $category->id,
                    'book_type' => $item['book_type'],
                    'publisher' => $item['publisher'],
                    'copyrights' => $item['copyrights'],
                    'created_by' => Auth::id(),
                    'created_by_name' => Auth::user()->full_name,
                    'timestamp' => now(),
                ]);

                $newBook = Book::create([
                    'accession'             => $item['accession'],
                    'call_number'           => $item['call_number'] ?? null,
                    'title'                 => $item['title'],
                    'author'                => $item['authors'] ?? null,
                    'book_type'             => $item['book_type'] ?? null,
                    'description'           => $item['description'] ?? null,
                    'edition'               => $item['edition'] ?? null,
                    'place_of_publication'  => $item['place_of_publication'],
                    'publisher'             => $item['publisher'],
                    'copyrights'            => $item['copyrights'],
                    'category_id'           => $category->id,
                    'barcode'               => $barcode->getBarcodeJPG($item['accession'], 'C39', 2, 80, array(0, 0, 0, 0), false),
                    'digital_copy_url'      => $item['digital_copy_url'] ?? null,
                    'remarks'               => 'Missing',
                    'availability_status'   => 'Unavailable',
                    'condition_status'      => 'New',
                ]);

                // Create inventory entry for the new book
                Inventory::create([
                    'book_id'    => $newBook->id,
                    'checked_at' => now(),
                ]);

                Log::info('Book Import: Inventory entry created for new book', [
                    'book_id' => $newBook->id,
                    'accession' => $newBook->accession,
                    'checked_at' => now(),
                    'user_id' => Auth::id(),
                    'timestamp' => now(),
                ]);

                Log::info('Book Import: Book created successfully', [
                    'accession' => $item['accession'],
                    'title' => $item['title'],
                    'created_by' => Auth::id(),
                    'timestamp' => now(),
                ]);

                $newBooksCount++;

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                if ($e->getCode() == 23000) {
                    $errors = "Duplicate ID found for Book: " . $item['accession'];
                } else if ($e->getCode() == "HY000") {
                    $errors = $e->getMessage();
                } else {
                    $errors = "An error occurred while saving Book: " . $e->getMessage();
                }

                Log::error('Book Import: Database error occurred', [
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage(),
                    'accession' => $item['accession'],
                    'title' => $item['title'],
                    'sql_state' => $e->errorInfo[0] ?? 'N/A',
                    'user_id' => Auth::id(),
                    'timestamp' => now(),
                ]);

                return redirect()->route('import.import-books')->with('toast-error', $errors);
            }
        }

        DB::commit();
        Log::info('Book Import: Database transaction committed', [
            'new_books_count' => $newBooksCount,
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        $request->session()->forget('book_import_data');

        Log::info('Book Import: Process completed successfully', [
            'new_books_count' => $newBooksCount,
            'completed_by' => Auth::id(),
            'completed_by_name' => Auth::user()->full_name,
            'timestamp' => now(),
        ]);

        return redirect()->route('import.import-books')->with('toast-success', 'Books imported successfully: ' . $newBooksCount . ' new books added.');
    }

    /**
     * Downloads the template for books import in Excel format.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadTemplate()
    {
        Log::info('Book Import: Template download initiated', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        $filePath = public_path('excel/Book-template.xlsx');

        if (File::exists($filePath)) {
            Log::info('Book Import: Template downloaded successfully', [
                'file_path' => $filePath,
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ]);

            return Response::download($filePath, 'Book-template.xlsx');
        }

        Log::error('Book Import: Template file not found', [
            'file_path' => $filePath,
            'user_id' => Auth::id(),
            'timestamp' => now(),
        ]);

        abort(404, 'File not found.');
    }

    /**
     * Extracts the enum values from a given table and column name.
     *
     * @param string $table The name of the table to query.
     * @param string $columnName The name of the column to extract the enum values from.
     * @return array An array of enum values. If no enum values are found, returns ['N/A'].
     */
    private function extract_enums($table, $columnName)
    {
        $query = "SHOW COLUMNS FROM {$table} LIKE '{$columnName}'";
        $column = DB::select($query);
        if (empty($column)) {
            return ['N/A'];
        }
        $type = $column[0]->Type;
        // Extract enum values
        preg_match('/enum\((.*)\)$/', $type, $matches);
        $enumValues = [];
        if (isset($matches[1])) {
            $enumValues = str_getcsv($matches[1], ',', "'");
        }
        return $enumValues;
    }
}
