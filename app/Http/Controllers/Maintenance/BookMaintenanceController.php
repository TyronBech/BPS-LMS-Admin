<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Category;
use App\Models\SubjectAccessCode;
use Dompdf\Dompdf;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Milon\Barcode\DNS1D;
use App\Models\Inventory;
use App\Models\BkLastAccession;
use App\Models\SystemSetting;

class BookMaintenanceController extends Controller
{
    /**
     * Index of books
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $perPage    = $request->input('perPage', 10);
        $search     = $request->input('search', '');
        $category   = $request->input('category', '');
        $sortBy     = $request->input('sort_by', '');
        $sortOrder  = $request->input('sort_order', '');
        $bookType   = $request->input('book_type', '');
        
        Log::info('Book Maintenance: List page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'search_term' => $search,
            'category_filter' => $category,
            'book_type_filter' => $bookType,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'per_page' => $perPage,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'category' => 'nullable|integer|in:' . implode(',', Category::pluck('id')->toArray()),
            'book_type' => 'nullable|string',
            'perPage' => 'nullable|integer|min:1|max:500',
            'search' => 'nullable|string|max:255',
            'sort_by' => 'nullable|in:accession,title',
            'sort_order' => 'nullable|in:asc,desc',
        ]);

        if ($validator->fails()) {
            Log::warning('Book Maintenance: Invalid parameter', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $book_types = $this->extract_enums((new Book())->getTable(), 'book_type');
        $booksQuery = Book::with(['category', 'subjectAccessCodes']);

        if ($search) {
            $booksQuery->where(function ($query) use ($search) {
                $query->where('accession', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('parallel_title', 'like', "%{$search}%")
                    ->orWhere('authors->Main author', 'like', "%{$search}%")
                    ->orWhere('authors->Added authors', 'like', "%{$search}%")
                    ->orWhere('authors->Contributors', 'like', "%{$search}%")
                    ->orWhere('authors->Corporate author', 'like', "%{$search}%")
                    ->orWhereHas('subjectAccessCodes', function ($q) use ($search) {
                        $q->where('access_code', 'like', "%{$search}%");
                    });
            });
        }

        if ($category) {
            $booksQuery->where('category_id', $category);
        }

        if ($bookType) {
            $booksQuery->where('book_type', $bookType);
        }

        if ($sortBy && $sortOrder) {
            $booksQuery->orderBy($sortBy, $sortOrder)->orderBy('id', 'desc');
        } else {
            $booksQuery->orderBy('updated_at', 'desc')->orderBy('id', 'desc');
        }

        $books      = $booksQuery
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search' => $search,
                'category' => $category,
                'book_type' => $bookType,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ]);
        return view('maintenance.books.books', compact('books', 'perPage', 'search', 'categories', 'category', 'sortBy', 'sortOrder', 'book_types', 'bookType'));
    }
    /**
     * Create a new book
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        Log::info('Book Maintenance: Create book form accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        $books = new Book();
        $categories     = Category::select('id', 'name', 'legend', 'category_type')
            ->with(['lastAccession'])
            ->orderBy('name')
            ->get();
        $condition      = $this->extract_enums($books->getTable(), 'condition_status');
        $availability   = $this->extract_enums($books->getTable(), 'availability_status');
        $remarks        = $this->extract_enums($books->getTable(), 'remarks');
        $book_types     = $this->extract_enums($books->getTable(), 'book_type');
        $subjects = SubjectAccessCode::orderBy('access_code')->get();

        $accessionDashActive = SystemSetting::where('key', 'accession_number_dash_active')->first();
        $accessionDashActive = $accessionDashActive ? ($accessionDashActive->value === 'true') : true;

        return view('maintenance.books.create', compact('categories', 'condition', 'availability', 'remarks', 'book_types', 'subjects', 'accessionDashActive'));
    }
    /**
     * Process subject access codes, creating new ones if they are non-numeric strings
     *
     * @param \Illuminate\Http\Request $request
     */
    private function processSubjectAccessCodes(Request $request)
    {
        $subjectCodes = $request->input('subject_access_codes', []);
        $processedSubjectCodes = [];
        if (is_array($subjectCodes)) {
            foreach ($subjectCodes as $code) {
                if (!is_numeric($code) && trim($code) !== '') {
                    $newSubject = SubjectAccessCode::firstOrCreate(['access_code' => trim($code)]);
                    $processedSubjectCodes[] = $newSubject->id;
                } else if (is_numeric($code)) {
                    $processedSubjectCodes[] = $code;
                }
            }
            $request->merge(['subject_access_codes' => $processedSubjectCodes]);
        }
    }

    /**
     * Store a new book
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\QueryException
     */
    public function store(Request $request)
    {
        ini_set('memory_limit', '4096M');
        $books = new Book();
        $coverImageFileName = $request->hasFile('cover_image')
            ? $request->file('cover_image')->getClientOriginalName()
            : null;

        Log::info('Book Maintenance: Attempting to store new book(s)', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'accession_input' => $request->input('accession'),
            'title' => $request->input('title'),
            'cover_image_file' => $coverImageFileName,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $this->processSubjectAccessCodes($request);

        $validator = Validator::make($request->all(), [
            'accession'         => 'required|string',
            'call_number'       => 'nullable|string|max:50',
            'isbn'              => 'nullable|string|max:20',
            'title'             => 'required|string|max:150',
            'parallel_title'    => 'nullable|string',
            'subject_access_codes' => 'nullable|array',
            'subject_access_codes.*' => 'integer|exists:bk_subject_access_codes,id,deleted_at,NULL',
            'authors'           => 'nullable|array',
            'authors.Main author'     => 'nullable|string',
            'authors.Added authors'   => 'nullable|string',
            'authors.Contributors'    => 'nullable|string',
            'authors.Corporate author' => 'nullable|string',
            'description'       => 'nullable|array',
            'description.Description'    => 'nullable|string',
            'description.Series'         => 'nullable|string',
            'description.Content notes' => 'nullable|string',
            'description.Abstract'      => 'nullable|string',
            'description.Reviews'       => 'nullable|string',
            'description.Extent'        => 'required_with:description|string',
            'description.Acc Material'  => 'nullable|string',
            'edition'           => 'nullable|string|max:50',
            'publication'       => 'nullable|string|max:50',
            'publisher'         => 'nullable|string|max:100',
            'copyright'         => 'nullable|string|max:50',
            'location'          => 'nullable|string|max:100',
            'languages'         => 'nullable|string|max:50',
            'cover_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'digital_copy_url'  => 'nullable|string',
            'remarks'           => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'remarks')),
            'category'          => 'required|in:' . implode(',', Category::pluck('id')->toArray()),
            'book_type'         => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'book_type')),
            'condition'         => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'condition_status')),
            'availability'      => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'availability_status')),
        ]);
        $validator->after(function ($validator) use ($request) {
            $this->validateAvailabilityStatus($validator, $request);
            $this->validateCategoryBookTypeRelationship($validator, $request);
            $this->validateAccessionPrefix($validator, $request);
        });
        if ($validator->fails()) {
            Log::warning('Book Maintenance: Creation validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        $accessions = collect(explode(';', (string) $request->input('accession')))
            ->map(fn($item) => trim((string) $item))
            ->filter(fn($item) => $item !== '')
            ->values();

        $existingBooks = Book::whereIn('accession', $accessions)->pluck('accession');
        if ($existingBooks->isNotEmpty()) {
            Log::warning('Book Maintenance: Creation failed - Accession already exists', [
                'user_id' => Auth::guard('admin')->id(),
                'accession' => $request->input('accession'),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Book with accession number(s) ' . $existingBooks->implode(', ') . ' already exists!')->withInput();
        }
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $imageContent = file_get_contents($image->getRealPath());
            $base64Image = base64_encode($imageContent);
            $request->merge(['cover_image' => $base64Image]);
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $subjectAccessCodeIds = $request->input('subject_access_codes', []);
            $accessions = collect(explode(';', (string) $request->input('accession')))
                ->map(fn($item) => trim((string) $item))
                ->filter(fn($item) => $item !== '')
                ->values();

            $createdBookIds = [];
            foreach ($accessions as $accession) {
                $barcode = new DNS1D();
                $createdBook = Book::create([
                    'accession'             => $accession,
                    'call_number'           => $request->input('call_number') ?? null,
                    'isbn'                  => $request->input('isbn') ?? null,
                    'barcode'               => $barcode->getBarcodeJPG($accession, 'C39', 2, 80, array(0, 0, 0, 0), false),
                    'title'                 => $request->input('title'),
                    'parallel_title'        => $request->input('parallel_title') ?? null,
                    'authors'               => $request->input('authors') ?? null,
                    'description'           => $request->input('description') ?? null,
                    'edition'               => $request->input('edition') ?? null,
                    'place_of_publication'  => $request->input('publication') ?? null,
                    'publisher'             => $request->input('publisher') ?? null,
                    'copyrights'            => $request->input('copyright') ?? null,
                    'location'              => $request->input('location') ?? null,
                    'cover_image'           => $request->input('cover_image') ?? null,
                    'languages'             => $request->input('languages') ?? null,
                    'digital_copy_url'      => $request->input('digital_copy_url') ?? null,
                    'remarks'               => $request->input('remarks'),
                    'category_id'           => $request->input('category'),
                    'book_type'             => $request->input('book_type'),
                    'condition_status'      => $request->input('condition'),
                    'availability_status'   => $request->input('availability'),
                ]);

                $createdBook->subjectAccessCodes()->sync($subjectAccessCodeIds);
                $createdBookIds[] = $createdBook->id;
                
                // Update last accession
                BkLastAccession::updateOrCreate(
                    ['category_id' => $request->input('category')],
                    ['accession_number' => $accession]
                );
            }
            // After creating books, update remarks/availability and create inventory entries within the same transaction
            $importedAccessions = array_map('trim', explode(';', $request->input('accession')));
            foreach ($importedAccessions as $acc) {
                $book = Book::where('accession', $acc)->first();
                if ($book && $book->book_type !== 'E-books') {
                    Inventory::create([
                        'book_id'    => $book->id,
                        'is_scanned' => 1,
                        'checked_at' => now(),
                    ]);
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Book Maintenance: Database error during creation', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $this->sanitizeDatabaseErrorMessage($e->getMessage()),
                'sql_state' => $e->errorInfo[0] ?? null,
                'driver_code' => $e->errorInfo[1] ?? null,
                'cover_image_file' => $coverImageFileName,
                'timestamp' => now(),
            ]);
            if ($e->getCode() == 23000) {
                return redirect()->back()->with('toast-error', 'Book with this accession number already exists!')->withInput();
            } else {
                return redirect()->back()->with('toast-error', $this->friendlyErrorMessage($e))->withInput();
            }
        }
        DB::commit();
        Log::info('Book Maintenance: Book(s) created successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'accessions' => $request->input('accession'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.books')->with('toast-success', 'Book added successfully');
    }
    /**
     * Edit a book
     *
     * This function is used to edit a book. It fetches the book object from the database and
     * passes it to the view along with the categories, condition status, availability status,
     * remarks, and book types.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function edit(Request $request)
    {
        $book = null;
        try {
            $id = $request->input('id');
            Log::info('Book Maintenance: Edit book form accessed', [
                'user_id' => Auth::guard('admin')->id(),
                'user_name' => Auth::guard('admin')->user()->full_name,
                'book_id' => $id,
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            $book = Book::with(['subjectAccessCodes'])->findOrFail($id);
            $linkedSubjectIds = $book->subjectAccessCodes->pluck('id')->toArray();
            $books = new Book();
            $categories     = Category::select('id', 'name', 'legend', 'category_type')->with(['lastAccession'])->orderBy('name')->get();
            $condition      = $this->extract_enums($books->getTable(), 'condition_status');
            $availability   = $this->extract_enums($books->getTable(), 'availability_status');
            $remarks        = $this->extract_enums($books->getTable(), 'remarks');
            $book_types     = $this->extract_enums($books->getTable(), 'book_type');
            $subjects       = SubjectAccessCode::orderBy('access_code')->get();
        } catch (\Exception $e) {
            Log::error('Book Maintenance: Error accessing edit form', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', $this->friendlyErrorMessage($e))->withInput();
        }
        $accessionDashActive = SystemSetting::where('key', 'accession_number_dash_active')->first();
        $accessionDashActive = $accessionDashActive ? ($accessionDashActive->value === 'true') : true;

        return view('maintenance.books.edit', compact('book', 'linkedSubjectIds', 'categories', 'condition', 'availability', 'remarks', 'book_types', 'subjects', 'accessionDashActive'));
    }
    /**
     * Show books
     *
     * This function is used to show books. It fetches books from the database based on search
     * filter and category filter. It also applies pagination to the results.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function show(Request $request)
    {
        $search = $request->input('search', '');
        $category = $request->input('category', '');
        $bookType = $request->input('book_type', '');
        $perPage = $request->input('perPage', 10);
        $sortBy = $request->input('sort_by', '');
        $sortOrder = $request->input('sort_order', '');

        if ($category && (!$sortBy || !$sortOrder)) {
            $sortBy = 'accession';
            $sortOrder = 'desc';
        }

        Log::info('Book Maintenance: Searching/Filtering books', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'search_term' => $search,
            'category_filter' => $category,
            'book_type_filter' => $bookType,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'action' => $request->input('barcodeBtn') ? 'barcode_export' : ($request->input('callNumberBtn') ? 'call_number_export' : 'view'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'category' => 'nullable|integer|in:' . implode(',', Category::pluck('id')->toArray()),
            'book_type' => 'nullable|string',
            'perPage' => 'nullable|integer|min:1|max:500',
            'search' => 'nullable|string|max:255',
            'sort_by' => 'nullable|in:accession,title',
            'sort_order' => 'nullable|in:asc,desc',
        ]);

        if ($validator->fails()) {
            Log::warning('Book Maintenance: Invalid parameter', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        if ($request->input('barcodeBtn') === 'barcode') {
            $this->export_barcode($request);
        } elseif ($request->input('callNumberBtn') === 'callNumber') {
            $this->export_call_numbers($request);
        }
        // Fetch categories for dropdown
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $book_types = $this->extract_enums((new Book())->getTable(), 'book_type');

        // Validate category only if present
        if ($category) {
            $validator = Validator::make($request->all(), [
                'category' => 'sometimes|integer|in:' . implode(',', $categories->pluck('id')->toArray()),
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('toast-error', $validator->errors()->first())->withInput();
            }
        }
        // Check if the search if multiple accession
        $is_multiple_accessions = preg_match('/;/', $search);
        $trimmed_accessions = [];
        if ($is_multiple_accessions) {
            $accessions = explode(';', $search);
            $trimmed_accessions = array_map('trim', $accessions);
        }
        // Start query
        $booksQuery = Book::with(['category', 'subjectAccessCodes']);

        // Apply category filter if provided
        if ($category) {
            $booksQuery->where('category_id', $category);
        }

        // Apply book type filter if provided
        if ($bookType) {
            $booksQuery->where('book_type', $bookType);
        }

        // Apply search filter if provided
        if ($is_multiple_accessions) {
            $booksQuery->whereIn('accession', $trimmed_accessions);
        } elseif ($search) {
            $booksQuery->where(function ($q) use ($search) {
                $q->where('accession', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%')
                    ->orWhere('parallel_title', 'like', '%' . $search . '%')
                    ->orWhere('authors->Main author', 'like', '%' . $search . '%')
                    ->orWhere('authors->Added authors', 'like', '%' . $search . '%')
                    ->orWhere('authors->Contributors', 'like', '%' . $search . '%')
                    ->orWhere('authors->Corporate author', 'like', '%' . $search . '%')
                    ->orWhere('isbn', 'like', '%' . $search . '%')
                    ->orWhere('publisher', 'like', '%' . $search . '%')
                    ->orWhere('place_of_publication', 'like', '%' . $search . '%')
                    ->orWhere('edition', 'like', '%' . $search . '%')
                    ->orWhere('call_number', 'like', '%' . $search . '%')
                    ->orWhere('copyrights', 'like', '%' . $search . '%')
                    ->orWhere('digital_copy_url', 'like', '%' . $search . '%')
                    ->orWhere('remarks', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%' . $search . '%')
                            ->orWhere('legend', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('subjectAccessCodes', function ($q) use ($search) {
                        $q->where('access_code', 'like', '%' . $search . '%');
                    });
            });
        }

        // Finalize query
        if ($sortBy && $sortOrder) {
            $booksQuery->orderBy($sortBy, $sortOrder)->orderBy('id', 'desc');
        } else {
            $booksQuery->orderBy('updated_at', 'desc')->orderBy('id', 'desc');
        }

        $books = $booksQuery
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search' => $search,
                'category' => $category,
                'book_type' => $bookType,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ])
            ->withQueryString();

        return view('maintenance.books.books', compact('books', 'perPage', 'search', 'category', 'categories', 'sortBy', 'sortOrder', 'book_types', 'bookType'));
    }
    /**
     * View a book with given accession number
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function view(Request $request)
    {
        $mimeType = null;
        $accession = $request->input('accession');

        Log::info('Book Maintenance: Viewing book details', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'accession' => $accession,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $book = Book::with(['category', 'subjectAccessCodes'])->where('accession', $accession)->first();
        try {
            $cover = $this->getBookImage($book->title, $book->author, $book->isbn ?? null);
            if (!$cover) {
                $cover = null;
            }
        } catch (Exception $e) {
            Log::error('Book Maintenance: Error fetching book cover image', [
                'user_id' => Auth::guard('admin')->id(),
                'accession' => $accession,
                'error_message' => $e->getMessage(),
                'timestamp' => now(),
            ]);
            $cover = null;
        }
        if (!$book) {
            Log::warning('Book Maintenance: Book not found for viewing', [
                'user_id' => Auth::guard('admin')->id(),
                'accession' => $accession,
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Book not found!')->withInput();
        }
        return view('maintenance.books.view', compact('book', 'cover', 'mimeType'));
    }
    /**
     * Update a book
     *
     * Validate the request and update the book with the given accession number
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Database\QueryException
     */
    public function update(Request $request)
    {
        ini_set('memory_limit', '4096M');
        $books = new Book();
        $coverImageFileName = $request->hasFile('cover_image')
            ? $request->file('cover_image')->getClientOriginalName()
            : null;

        Log::info('Book Maintenance: Attempting to update book', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'book_id' => $request->input('id'),
            'accession' => $request->input('accession'),
            'cover_image_file' => $coverImageFileName,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $this->processSubjectAccessCodes($request);

        $validator = Validator::make($request->all(), [
            'accession'         => 'required|string|max:50',
            'call_number'       => 'nullable|string|max:50',
            'isbn'              => 'nullable|string|max:20',
            'title'             => 'required|string|max:150',
            'parallel_title'    => 'nullable|string',
            'subject_access_codes' => 'nullable|array',
            'subject_access_codes.*' => 'integer|exists:bk_subject_access_codes,id,deleted_at,NULL',
            'authors'           => 'nullable|array',
            'authors.Main author'     => 'nullable|string',
            'authors.Added authors'   => 'nullable|string',
            'authors.Contributors'    => 'nullable|string',
            'authors.Corporate author' => 'nullable|string',
            'description'       => 'nullable|array',
            'description.Description'    => 'nullable|string',
            'description.Series'         => 'nullable|string',
            'description.Content notes' => 'nullable|string',
            'description.Abstract'      => 'nullable|string',
            'description.Reviews'       => 'nullable|string',
            'description.Extent'        => 'required_with:description|string',
            'description.Acc Material'  => 'nullable|string',
            'edition'           => 'nullable|string|max:50',
            'publication'       => 'nullable|string|max:50',
            'publisher'         => 'nullable|string|max:100',
            'copyright'         => 'nullable|string|max:50',
            'location'          => 'nullable|string|max:100',
            'languages'         => 'nullable|string|max:50',
            'cover_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'digital_copy_url'  => 'nullable|string|url',
            'remarks'           => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'remarks')),
            'category'          => 'required|in:' . implode(',', Category::pluck('id')->toArray()),
            'book_type'         => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'book_type')),
            'condition'         => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'condition_status')),
            'availability'      => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'availability_status')),
        ]);
        $validator->after(function ($validator) use ($request) {
            $this->validateAvailabilityStatus($validator, $request);
            $this->validateCategoryBookTypeRelationship($validator, $request);
            $this->validateAccessionPrefix($validator, $request);
        });
        if ($validator->fails()) {
            Log::warning('Book Maintenance: Update validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        //dd($request->all());
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $imageContent = file_get_contents($image->getRealPath());
            $base64Image = base64_encode($imageContent);
            $request->merge(['cover_image' => $base64Image]);
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $barcode = new DNS1D();
            $book = Book::findOrFail($request->input('id'));
            $subjectAccessCodeIds = $request->input('subject_access_codes', []);

            $book->update([
                'accession'             => $request->input('accession'),
                'call_number'           => $request->input('call_number'),
                'isbn'                  => $request->input('isbn'),
                'barcode'               => $barcode->getBarcodeJPG($request->input('accession'), 'C39', 2, 80, array(0, 0, 0, 0), false),
                'title'                 => $request->input('title'),
                'parallel_title'        => $request->input('parallel_title'),
                'authors'               => $request->input('authors'),
                'description'           => $request->input('description'),
                'edition'               => $request->input('edition'),
                'place_of_publication'  => $request->input('publication'),
                'publisher'             => $request->input('publisher'),
                'copyrights'            => $request->input('copyright'),
                'location'              => $request->input('location'),
                'languages'             => $request->input('languages'),
                'cover_image'           => $request->input('cover_image'),
                'digital_copy_url'      => $request->input('digital_copy_url'),
                'remarks'               => $request->input('remarks'),
                'category_id'           => $request->input('category'),
                'book_type'             => $request->input('book_type'),
                'condition_status'      => $request->input('condition'),
                'availability_status'   => $request->input('availability'),
            ]);

            $book->subjectAccessCodes()->sync($subjectAccessCodeIds);
            
            // Update last accession
            BkLastAccession::updateOrCreate(
                ['category_id' => $request->input('category')],
                ['accession_number' => $request->input('accession')]
            );
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Book Maintenance: Database error during update', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $this->sanitizeDatabaseErrorMessage($e->getMessage()),
                'sql_state' => $e->errorInfo[0] ?? null,
                'driver_code' => $e->errorInfo[1] ?? null,
                'cover_image_file' => $coverImageFileName,
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', $this->friendlyErrorMessage($e))->withInput();
        }
        DB::commit();
        Log::info('Book Maintenance: Book updated successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'book_id' => $request->input('id'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.books')->with('toast-success', 'Book updated successfully');
    }
    /**
     * Copy a book
     *
     * Create a new book by copying the given accession number
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Database\QueryException
     */
    public function copy(Request $request)
    {
        ini_set('memory_limit', '4096M');
        $books = new Book();
        $coverImageFileName = $request->hasFile('cover_image')
            ? $request->file('cover_image')->getClientOriginalName()
            : null;

        Log::info('Book Maintenance: Attempting to copy book', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'source_accession' => $request->input('accession'),
            'cover_image_file' => $coverImageFileName,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'accession'         => 'required|string',
            'call_number'       => 'nullable|string|max:50',
            'isbn'              => 'nullable|string|max:20',
            'title'             => 'required|string|max:150',
            'parallel_title'    => 'nullable|string',
            'subject_access_codes' => 'nullable|array',
            'subject_access_codes.*' => 'integer|exists:bk_subject_access_codes,id,deleted_at,NULL',
            'authors'           => 'nullable|array',
            'authors.Main author'     => 'nullable|string',
            'authors.Added authors'   => 'nullable|string',
            'authors.Contributors'    => 'nullable|string',
            'authors.Corporate author' => 'nullable|string',
            'description'       => 'nullable|array',
            'description.Description'    => 'nullable|string',
            'description.Series'         => 'nullable|string',
            'description.Content notes' => 'nullable|string',
            'description.Abstract'      => 'nullable|string',
            'description.Reviews'       => 'nullable|string',
            'description.Extent'        => 'required_with:description|string',
            'description.Acc Material'  => 'nullable|string',
            'edition'           => 'nullable|string|max:50',
            'publication'       => 'nullable|string|max:50',
            'publisher'         => 'nullable|string|max:100',
            'copyright'         => 'nullable|string|max:50',
            'cover_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'digital_copy_url'  => 'nullable|string|url',
            'remarks'           => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'remarks')),
            'category'          => 'required|in:' . implode(',', Category::pluck('id')->toArray()),
            'book_type'         => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'book_type')),
            'condition'         => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'condition_status')),
            'availability'      => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'availability_status')),
        ]);
        $validator->after(function ($validator) use ($request) {
            $this->validateAvailabilityStatus($validator, $request);
            $this->validateCategoryBookTypeRelationship($validator, $request);
            $this->validateAccessionPrefix($validator, $request);
        });
        if ($validator->fails()) {
            Log::warning('Book Maintenance: Copy validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $imageContent = file_get_contents($image->getRealPath());
            $base64Image = base64_encode($imageContent);
            $request->merge(['cover_image' => $base64Image]);
        }
        
        $accessions = collect(explode(';', (string) $request->input('accession')))
            ->map(fn($item) => trim((string) $item))
            ->filter(fn($item) => $item !== '')
            ->values();

        $existingBooks = Book::whereIn('accession', $accessions)->pluck('accession');
        if ($existingBooks->isNotEmpty()) {
            Log::warning('Book Maintenance: Copy failed - Accession already exists', [
                'user_id' => Auth::guard('admin')->id(),
                'accession' => $request->input('accession'),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Book with accession number(s) ' . $existingBooks->implode(', ') . ' already exists!')->withInput();
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $subjectAccessCodeIds = $request->input('subject_access_codes', []);
            $accessions = collect(explode(';', (string) $request->input('accession')))
                ->map(fn($item) => trim((string) $item))
                ->filter(fn($item) => $item !== '')
                ->values();

            $copiedBookIds = [];
            foreach ($accessions as $accession) {
                $barcode = new DNS1D();
                $copiedBook = Book::create([
                    'accession'             => $accession,
                    'call_number'           => $request->input('call_number') ?? null,
                    'isbn'                  => $request->input('isbn') ?? null,
                    'barcode'               => $barcode->getBarcodeJPG($accession, 'C39', 2, 80, array(0, 0, 0, 0), false),
                    'title'                 => $request->input('title'),
                    'parallel_title'        => $request->input('parallel_title') ?? null,
                    'authors'               => $request->input('authors') ?? null,
                    'description'           => $request->input('description') ?? null,
                    'edition'               => $request->input('edition') ?? null,
                    'place_of_publication'  => $request->input('publication') ?? null,
                    'publisher'             => $request->input('publisher') ?? null,
                    'copyrights'            => $request->input('copyright') ?? null,
                    'cover_image'           => $request->input('cover_image') ?? null,
                    'languages'             => $request->input('languages') ?? null,
                    'digital_copy_url'      => $request->input('digital_copy_url') ?? null,
                    'remarks'               => $request->input('remarks'),
                    'category_id'           => $request->input('category'),
                    'book_type'             => $request->input('book_type'),
                    'condition_status'      => $request->input('condition'),
                    'availability_status'   => $request->input('availability'),
                ]);

                $copiedBook->subjectAccessCodes()->sync($subjectAccessCodeIds);
                
                // Update last accession
                BkLastAccession::updateOrCreate(
                    ['category_id' => $request->input('category')],
                    ['accession_number' => $accession]
                );
                $copiedBookIds[] = $copiedBook->id;
            }
            // After copying books, update remarks/availability and create inventory entries within the same transaction
            $copiedAccessions = array_map('trim', explode(';', $request->input('accession')));
            foreach ($copiedAccessions as $acc) {
                $book = Book::where('accession', $acc)->first();
                if ($book) {
                    Inventory::create([
                        'book_id'    => $book->id,
                        'is_scanned' => 1,
                        'checked_at' => now(),
                    ]);
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Book Maintenance: Database error during copy', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $this->sanitizeDatabaseErrorMessage($e->getMessage()),
                'sql_state' => $e->errorInfo[0] ?? null,
                'driver_code' => $e->errorInfo[1] ?? null,
                'cover_image_file' => $coverImageFileName,
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', $this->friendlyErrorMessage($e))->withInput();
        }
        DB::commit();
        Log::info('Book Maintenance: Book copy created successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'accessions' => $request->input('accession'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.books')->with('toast-success', 'Book copy created successfully');
    }
    /**
     * Export barcodes for selected books
     *
     * This function is used to export barcodes for selected books. It fetches books
     * from the database based on search filter and category filter. It then
     * generates barcodes for the fetched books.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export_barcode(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $search   = $request->input('search', '');
        $category = $request->input('category', '');
        $ids      = array_filter(explode(',', $request->input('ids')), function ($id) {
            return is_numeric($id) && $id > 0;
        });

        Log::info('Book Maintenance: Exporting barcodes', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'ids_count' => count($ids),
            'search_term' => $search,
            'category_filter' => $category,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        // Start query
        $booksQuery = Book::select('barcode', 'accession');

        // ✅ If $ids provided, prioritize specific books
        if (!empty($ids)) {
            $booksQuery->whereIn('id', $ids);
        } else {
            // Apply category filter if provided
            if ($category && $category !== 'all') {
                $validator = Validator::make($request->all(), [
                    'category' => 'sometimes|integer|in:' . implode(',', Category::pluck('id')->toArray()),
                ]);

                if ($validator->fails()) {
                    return redirect()->back()->with('toast-error', $validator->errors()->first())->withInput();
                }

                $booksQuery->where('category_id', $category);
            }

            // Apply search filter if provided
            if ($search) {
                $booksQuery->where(function ($q) use ($search) {
                    $q->where('accession', 'like', '%' . $search . '%')
                        ->orWhere('title', 'like', '%' . $search . '%')
                        ->orWhere('author', 'like', '%' . $search . '%')
                        ->orWhere('isbn', 'like', '%' . $search . '%')
                        ->orWhere('publisher', 'like', '%' . $search . '%')
                        ->orWhere('place_of_publication', 'like', '%' . $search . '%')
                        ->orWhere('edition', 'like', '%' . $search . '%')
                        ->orWhere('call_number', 'like', '%' . $search . '%')
                        ->orWhere('copyrights', 'like', '%' . $search . '%')
                        ->orWhere('digital_copy_url', 'like', '%' . $search . '%')
                        ->orWhere('remarks', 'like', '%' . $search . '%')
                        ->orWhereHas('category', function ($q2) use ($search) {
                            $q2->where('name', 'like', '%' . $search . '%')
                                ->orWhere('legend', 'like', '%' . $search . '%');
                        });
                });
            }
        }

        // Get books
        $books = $booksQuery->orderBy('accession', 'asc')->get();

        if ($books->isEmpty()) {
            return redirect()->back()->with('toast-warning', 'No books found for barcode export!')->withInput();
        }

        // Generate barcodes
        $barcodeGenerator = new DNS1D();
        $dompdf = new Dompdf();

        $html = view('pdf.barcode-export-template', compact('books', 'barcodeGenerator'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->stream('barcodes.pdf');
    }
    public function export_call_numbers(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $search   = $request->input('search', '');
        $category = $request->input('category', '');
        $ids      = array_filter(explode(',', $request->input('ids')), function ($id) {
            return is_numeric($id) && $id > 0;
        });

        Log::info('Book Maintenance: Exporting call numbers', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'ids_count' => count($ids),
            'search_term' => $search,
            'category_filter' => $category,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        // Start query
        $booksQuery = Book::with('category');

        // ✅ If $ids provided, prioritize specific books
        if (!empty($ids)) {
            $booksQuery->whereIn('id', $ids);
        } else {
            // Apply category filter if provided
            if ($category && $category !== 'all') {
                $validator = Validator::make($request->all(), [
                    'category' => 'sometimes|integer|in:' . implode(',', Category::pluck('id')->toArray()),
                ]);

                if ($validator->fails()) {
                    return redirect()->back()->with('toast-error', $validator->errors()->first())->withInput();
                }

                $booksQuery->where('category_id', $category);
            }

            // Apply search filter if provided
            if ($search) {
                $booksQuery->where(function ($q) use ($search) {
                    $q->where('accession', 'like', '%' . $search . '%')
                        ->orWhere('title', 'like', '%' . $search . '%')
                        ->orWhere('author', 'like', '%' . $search . '%')
                        ->orWhere('isbn', 'like', '%' . $search . '%')
                        ->orWhere('publisher', 'like', '%' . $search . '%')
                        ->orWhere('place_of_publication', 'like', '%' . $search . '%')
                        ->orWhere('edition', 'like', '%' . $search . '%')
                        ->orWhere('call_number', 'like', '%' . $search . '%')
                        ->orWhere('copyrights', 'like', '%' . $search . '%')
                        ->orWhere('digital_copy_url', 'like', '%' . $search . '%')
                        ->orWhere('remarks', 'like', '%' . $search . '%')
                        ->orWhereHas('category', function ($q2) use ($search) {
                            $q2->where('name', 'like', '%' . $search . '%')
                                ->orWhere('legend', 'like', '%' . $search . '%');
                        });
                });
            }
        }

        // Get books
        $books = $booksQuery->select('call_number', 'category_id')->orderBy('accession', 'asc')->get();
        if ($books->isEmpty()) {
            Log::warning('Book Maintenance: No books found for call number export', [
                'user_id' => Auth::guard('admin')->id(),
                'user_name' => Auth::guard('admin')->user()->full_name,
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', 'No books found for call number export!')->withInput();
        }
        if ($books->every(fn($book) => is_null($book->call_number))) {
            Log::warning('Book Maintenance: No call numbers found for selected books', [
                'user_id' => Auth::guard('admin')->id(),
                'user_name' => Auth::guard('admin')->user()->full_name,
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', 'No call numbers found for the selected books!')->withInput();
        }
        $dompdf = new Dompdf();

        $html = view('pdf.call-number-export', compact('books'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->stream('call_numbers.pdf');
    }
    /**
     * Delete a book
     *
     * This function is used to delete a book. It starts a transaction, sets the current user id,
     * deletes the book and commits the transaction. If there is an error during the deletion,
     * it rolls back the transaction and redirects the user to the books page with an error message.
     * If the deletion is successful, it commits the transaction and redirects the user to the books page
     * with a success message.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Log::warning('Book Maintenance: Attempting to delete book', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'book_id' => $request->input('id'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $id = $request->input('id');

            $book = Book::findOrFail($id);
            $book->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Book Maintenance: Book deletion failed', [
                'user_id' => Auth::guard('admin')->id(),
                'book_id' => $request->input('id'),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now(),
            ]);
            return redirect()->route('maintenance.books')->with('toast-error', $this->friendlyErrorMessage($e));
        }
        DB::commit();
        Log::info('Book Maintenance: Book deleted successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'book_id' => $request->input('id'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.books')->with('toast-success', 'Book deleted successfully');
    }
    /**
     * Bulk delete books
     *
     * This function is used to delete multiple books. It fetches IDs from the request,
     * checks if any IDs are provided, starts a transaction, sets the current user id,
     * deletes the books and commits the transaction. If there is an error during the deletion,
     * it rolls back the transaction and redirects the user to the books page with an error message.
     * If the deletion is successful, it commits the transaction and redirects the user to the books page
     * with a success message.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {
        Log::warning('Book Maintenance: Attempting bulk delete', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);
        $ids = array_filter(explode(',', $request->input('ids')), function ($id) {
            return is_numeric($id) && $id > 0;
        });
        if (empty($ids)) {
            return redirect()->back()->with('toast-warning', 'No books selected for deletion!')->withInput();
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            Book::whereIn('id', $ids)->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Book Maintenance: Bulk delete failed', [
                'user_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', $this->friendlyErrorMessage($e))->withInput();
        }
        DB::commit();
        Log::info('Book Maintenance: Bulk delete successful', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'ids_count' => count($ids),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.books')->with('toast-success', 'Books deleted successfully');
    }

    /**
     * Fetches a book's thumbnail image from the Google Books API.
     *
     * This function includes a workaround for "cURL error 60: SSL certificate problem"
     * by bundling a CA certificate file (cacert.pem) with the application.
     *
     * @param string|null $title
     * @param string|null $author
     * @param string|null $isbn
     * @return string|null The URL of the book's thumbnail, or null on failure.
     */
    private function getBookImage($title = null, $author = null, $isbn = null)
    {
        $apiKey = config('services.google_books.api_key');

        // Check if API key exists
        if (empty($apiKey)) {
            Log::warning('Google Books API key is not configured', ['timestamp' => now()]);
            return null;
        }

        // Validate input - at least one parameter must be provided
        if (empty($title) && empty($author) && empty($isbn)) {
            return null;
        }

        // Build query parts
        $queryParts = [];

        if (!empty($isbn)) {
            // ISBN is the most accurate, prioritize it
            $queryParts[] = "isbn:" . urlencode(trim($isbn));
        }

        if (!empty($title)) {
            $queryParts[] = "intitle:" . urlencode(trim($title));
        }

        if (!empty($author)) {
            $queryParts[] = "inauthor:" . urlencode(trim($author));
        }

        $queryURL = implode("+", $queryParts);
        $url = "https://www.googleapis.com/books/v1/volumes?q={$queryURL}&key={$apiKey}&maxResults=1";

        // Path to local CA bundle
        $caPath = storage_path('certs/cacert.pem');

        try {
            // Configure HTTP options
            $options = [
                'timeout' => 10, // 10 second timeout
            ];

            if (file_exists($caPath)) {
                $options['verify'] = $caPath;
            }

            $response = Http::withOptions($options)->get($url);

            // Check if request was successful
            if (!$response->successful()) {
                Log::warning('Google Books API request failed', [
                    'status' => $response->status(),
                    'url' => $url,
                    'timestamp' => now()
                ]);
                return null;
            }

            $data = $response->json();

            // Check if we have results
            if (empty($data['items']) || !is_array($data['items'])) {
                Log::info('No books found in Google Books API', [
                    'title' => $title,
                    'author' => $author,
                    'isbn' => $isbn,
                    'timestamp' => now()
                ]);
                return null;
            }

            // Extract thumbnail URL
            $thumbnail = $data['items'][0]['volumeInfo']['imageLinks']['thumbnail'] ?? null;

            if (empty($thumbnail)) {
                Log::info('Book found but no thumbnail available', [
                    'title' => $title,
                    'author' => $author,
                    'isbn' => $isbn,
                    'timestamp' => now()
                ]);
                return null;
            }

            // Force HTTPS and return
            return str_replace('http://', 'https://', $thumbnail);
        } catch (ConnectionException $e) {
            Log::error('Connection error while fetching book image', [
                'message' => $e->getMessage(),
                'url' => $url,
                'timestamp' => now()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Error fetching book image from Google Books API', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Extracts the enum values from a given table and column name.
     *
     * @param string $table The name of the table to query.
     * @param string $columnName The name of the column to extract the enum values from.
     * @return array An array of enum values.
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

    private function validateCategoryBookTypeRelationship($validator, Request $request): void
    {
        $categoryId = $request->input('category');
        $bookType = $request->input('book_type');

        if (!$categoryId || !$bookType) {
            return;
        }

        $category = Category::find($categoryId);
        if (!$category) {
            return;
        }

        if ($category->category_type !== $bookType) {
            $validator->errors()->add('category', 'Selected category must match the selected book type.');
            $validator->errors()->add('book_type', 'Selected book type must match the selected category type.');
        }
    }

    private function validateAccessionPrefix($validator, Request $request): void
    {
        $categoryId = $request->input('category');
        $accessionInput = $request->input('accession');
        if (!$categoryId || !$accessionInput) return;

        $category = Category::find($categoryId);
        if (!$category) return;

        $accessionDashActive = SystemSetting::where('key', 'accession_number_dash_active')->first();
        $accessionDashActive = $accessionDashActive ? ($accessionDashActive->value === 'true') : true;

        $legend = trim($category->legend);
        $prefixes = [];
        if ($legend !== '') {
            $prefixes = array_map('trim', explode('/', $legend));
        } else {
            $prefixes = [strtoupper(substr(str_replace(' ', '', $category->name), 0, 3))];
        }
        $prefixes = array_filter($prefixes, fn($p) => $p !== '');
        if (empty($prefixes)) $prefixes = ['ACC'];

        $prefixes = array_map('strtoupper', $prefixes);
        
        $escapedPrefixes = array_map(function($p) { return preg_quote($p, '/'); }, $prefixes);
        $pattern = '/^(' . implode('|', $escapedPrefixes) . ')-\d{6}$/';

        $accessions = collect(explode(';', (string) $accessionInput))
            ->map(fn($item) => trim((string) $item))
            ->filter(fn($item) => $item !== '')
            ->values();

        foreach ($accessions as $acc) {
            if (!preg_match($pattern, $acc)) {
                $prefixListStr = implode("-' or '", $prefixes);
                $validator->errors()->add('accession', "The accession number '{$acc}' format is invalid. It must start with exactly '{$prefixListStr}-' followed by a 6-digit number (e.g., {$prefixes[0]}-000001), respecting exact capitalization.");
                break;
            }
        }
    }

    public function getNextAccession($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        
        $accessionDashActive = SystemSetting::where('key', 'accession_number_dash_active')->first();
        $accessionDashActive = $accessionDashActive ? ($accessionDashActive->value === 'true') : true;

        $legend = trim($category->legend);
        if ($legend !== '') {
            $prefix = explode('/', $legend)[0];
            $prefix = trim($prefix);
        } else {
            $prefix = strtoupper(substr(str_replace(' ', '', $category->name), 0, 3));
        }
        if ($prefix === '') $prefix = 'ACC';

        if ($accessionDashActive) {
            if (!str_ends_with($prefix, '-')) {
                $prefix .= '-';
            }
        } else {
            if (str_ends_with($prefix, '-')) {
                $prefix = rtrim($prefix, '-');
            }
        }

        // Try to get max from bk_last_accessions first
        $lastFromTable = BkLastAccession::where('category_id', $categoryId)->value('accession_number');

        // Also check bk_books (including trashed) for this category to ensure accuracy
        $maxFromBooks = Book::withTrashed()
            ->where('category_id', $categoryId)
            ->where('accession', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(accession) DESC, accession DESC')
            ->value('accession');

        $lastAccession = null;
        if ($maxFromBooks && $lastFromTable) {
            // Extract numbers to compare
            preg_match('/(\d+)$/', $maxFromBooks, $m1);
            preg_match('/(\d+)$/', $lastFromTable, $m2);
            $n1 = isset($m1[1]) ? (int)$m1[1] : 0;
            $n2 = isset($m2[1]) ? (int)$m2[1] : 0;
            $lastAccession = ($n1 > $n2) ? $maxFromBooks : $lastFromTable;
        } elseif ($maxFromBooks) {
            $lastAccession = $maxFromBooks;
        } else {
            $lastAccession = $lastFromTable;
        }

        if ($lastAccession) {
            preg_match('/(\d+)$/', $lastAccession, $match);
            $numberStr = $match[1] ?? null;
            if ($numberStr) {
                $num = (int)$numberStr;
                $width = strlen($numberStr);
                $nextNumberStr = str_pad((string)($num + 1), $width, '0', STR_PAD_LEFT);
                $nextAccession = $prefix . $nextNumberStr;
            } else {
                $nextAccession = $prefix . '000001';
            }
        } else {
            $nextAccession = $prefix . '000001';
        }

        return response()->json(['next_accession' => $nextAccession]);
    }

    private function validateAvailabilityStatus($validator, Request $request): void
    {
        $bookId = $request->input('id');
        $remarks = $request->input('remarks');
        $availability = $request->input('availability');

        $currentBook = $bookId ? Book::find($bookId) : null;
        $currentAvailability = $currentBook ? $currentBook->availability_status : null;

        if ($currentAvailability === 'Borrowed') {
            if ($availability === 'Available') {
                $validator->errors()->add('availability', 'This book is currently borrowed. It must first be returned before its status can be set to "Available".');
                return;
            }
        }

        if ($currentAvailability === 'Reserved') {
            if ($availability === 'Available') {
                $validator->errors()->add('availability', 'This book is currently reserved. It must first be returned or cancelled before its status can be set to "Available".');
                return;
            }
        }

        // Standard availability rules based on remarks, but respecting Borrowed/Reserved states
        if ($availability !== 'Borrowed' && $availability !== 'Reserved') {
            if ($remarks === 'On Shelf' && $availability !== 'Available') {
                $validator->errors()->add('availability', 'Availability must be "Available" when the book is On Shelf.');
            }
            if ($remarks !== 'On Shelf' && $availability !== 'Unavailable') {
                $validator->errors()->add('availability', 'Availability must be "Unavailable" when the book is not On Shelf.');
            }
        } else {
            // New book or copy trying to be created as Borrowed or Reserved
            if (!$currentBook) {
                $validator->errors()->add('availability', 'New books cannot be created with a "Borrowed" or "Reserved" status.');
            } elseif ($availability !== $currentAvailability) {
                $validator->errors()->add('availability', 'A book\'s status cannot be manually changed to "Borrowed" or "Reserved". This status is managed automatically by transactions.');
            }
        }
    }

    private function sanitizeDatabaseErrorMessage(string $message): string
    {
        $connectionPos = strpos($message, '(Connection:');

        if ($connectionPos !== false) {
            return trim(substr($message, 0, $connectionPos));
        }

        return $message;
    }
}
