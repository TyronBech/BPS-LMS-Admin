<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Category;
use Dompdf\Dompdf;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Milon\Barcode\DNS1D;

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
        $perPage = $request->input('perPage', 10);
        $search = $request->input('search', '');
        $category = $request->input('category', '');
        $categories = Category::select('id', 'name')->get();
        $books = Book::with('category')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search' => $search,
            ]);
        return view('maintenance.books.books', compact('books', 'perPage', 'search', 'categories', 'category'));
    }
    /**
     * Create a new book
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $books = new Book();
        $categories     = $categories = Category::select('id', 'name', 'legend')
            ->with(['books' => function ($query) {
                $query->select('category_id', 'accession') // must include category_id for relation
                    ->orderByDesc('accession')
                    ->limit(1);
            }])
            ->get();
        $condition      = $this->extract_enums($books->getTable(), 'condition_status');
        $availability   = $this->extract_enums($books->getTable(), 'availability_status');
        $remarks        = $this->extract_enums($books->getTable(), 'remarks');
        $book_types     = $this->extract_enums($books->getTable(), 'book_type');
        return view('maintenance.books.create', compact('categories', 'condition', 'availability', 'remarks', 'book_types'));
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
        $validator = Validator::make($request->all(), [
            'accession'         => 'required|string',
            'call_number'       => 'nullable|string|max:50',
            'title'             => 'required|string|max:150',
            'authors'           => 'nullable|string|max:1024',
            'description'       => 'nullable|string',
            'edition'           => 'nullable|string|max:50',
            'publication'       => 'nullable|string|max:50',
            'publisher'         => 'nullable|string|max:100',
            'copyright'         => 'nullable|string|max:50',
            'cover_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'digital_copy_url'  => 'nullable|string',
            'remarks'           => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'remarks')),
            'category'          => 'required|in:' . implode(',', Category::all()->pluck('id')->toArray()),
            'book_type'         => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'book_type')),
            'condition'         => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'condition_status')),
            'availability'      => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'availability_status')),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }
        if (Book::where('accession', $request->input('accession'))->exists()) {
            return redirect()->back()->with('toast-error', 'Book with this accession number already exists!')->withInput();
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
            $accessions = array_map('trim', explode(',', $request->input('accession')));
            foreach ($accessions as $accession) {
                $barcode = new DNS1D();
                Book::create([
                    'accession'             => $accession,
                    'call_number'           => $request->input('call_number') ?? null,
                    'barcode'               => $barcode->getBarcodeJPG($accession, 'C39', 2, 80, array(0, 0, 0, 0), false),
                    'title'                 => $request->input('title'),
                    'author'                => $request->input('authors') ?? null,
                    'description'           => $request->input('description') ?? null,
                    'edition'               => $request->input('edition') ?? null,
                    'place_of_publication'  => $request->input('publication') ?? null,
                    'publisher'             => $request->input('publisher') ?? null,
                    'copyrights'            => $request->input('copyright') ?? null,
                    'cover_image'           => $request->input('cover_image') ?? null,
                    'digital_copy_url'      => $request->input('digital_copy_url') ?? null,
                    'remarks'               => $request->input('remarks'),
                    'category_id'           => $request->input('category'),
                    'book_type'             => $request->input('book_type'),
                    'condition_status'      => $request->input('condition'),
                    'availability_status'   => $request->input('availability'),
                ]);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            if ($e->getCode() == 23000) {
                return redirect()->back()->with('toast-error', 'Book with this accession number already exists!')->withInput();
            } else {
                return redirect()->back()->with('toast-error', $e->getMessage())->withInput();
            }
        }
        DB::commit();
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
            $book = Book::findOrFail($id);
            $books = new Book();
            $categories     = Category::pluck('name', 'id');
            $condition      = $this->extract_enums($books->getTable(), 'condition_status');
            $availability   = $this->extract_enums($books->getTable(), 'availability_status');
            $remarks        = $this->extract_enums($books->getTable(), 'remarks');
            $book_types     = $this->extract_enums($books->getTable(), 'book_type');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        return view('maintenance.books.edit', compact('book', 'categories', 'condition', 'availability', 'remarks', 'book_types'));
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
        $perPage = $request->input('perPage', 10);
        if ($request->input('barcodeBtn') === 'barcode') {
            $this->export_barcode($request);
        }
        // Fetch categories for dropdown
        $categories = Category::select('id', 'name')->get();

        // Validate category only if present
        if ($category) {
            $validator = Validator::make($request->all(), [
                'category' => 'sometimes|integer|in:' . implode(',', $categories->pluck('id')->toArray()),
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('toast-error', $validator->errors()->first());
            }
        }
        // Check if the search if multiple accession
        $is_multiple_accessions = preg_match('/,/', $search);
        $trimmed_accessions = [];
        if ($is_multiple_accessions) {
            $accessions = explode(',', $search);
            $trimmed_accessions = array_map('trim', $accessions);
        }
        // Start query
        $books = Book::query();

        // Apply category filter if provided
        if ($category) {
            $books->whereHas('category', function ($q) use ($category) {
                $q->where('id', $category);
            });
        }

        // Apply search filter if provided
        if ($is_multiple_accessions) {
            $books->whereIn('accession', $trimmed_accessions);
        } elseif ($search) {
            $books->where(function ($q) use ($search) {
                $q->where('accession', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%')
                    ->orWhere('author', 'like', '%' . $search . '%')
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

        // Finalize query
        $books = $books->orderBy('accession', 'asc')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'search' => $search,
                'category' => $category,
            ])
            ->withQueryString();

        return view('maintenance.books.books', compact('books', 'perPage', 'search', 'category', 'categories'));
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
        $book = Book::with('category')->where('accession', $accession)->first();
        try {
            $cover = $this->getBookImage($book->title, $book->author, $book->isbn ?? null);
            if (!$cover) {
                $cover = null;
            }
        } catch (Exception $e) {
            $cover = null;
        }
        if (!$book) {
            return redirect()->back()->with('toast-error', 'Book not found!');
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
        $validator = Validator::make($request->all(), [
            'accession'         => 'required|string|max:50',
            'call_number'       => 'nullable|string|max:50',
            'title'             => 'required|string|max:150',
            'authors'           => 'nullable|string|max:1024',
            'description'       => 'nullable|string',
            'edition'           => 'nullable|string|max:50',
            'publication'       => 'nullable|string|max:50',
            'publisher'         => 'nullable|string|max:100',
            'copyright'         => 'nullable|string|max:50',
            'cover_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'digital_copy_url'  => 'nullable|string|url',
            'remarks'           => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'remarks')),
            'category'          => 'required|in:' . implode(',', Category::all()->pluck('id')->toArray()),
            'book_type'         => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'book_type')),
            'condition'         => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'condition_status')),
            'availability'      => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'availability_status')),
        ]);
        if ($validator->fails()) {
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
            $book->update([
                'accession'             => $request->input('accession'),
                'call_number'           => $request->input('call_number'),
                'barcode'               => $barcode->getBarcodeJPG($request->input('accession'), 'C39', 2, 80, array(0, 0, 0, 0), false),
                'title'                 => $request->input('title'),
                'author'                => $request->input('authors'),
                'description'           => $request->input('description'),
                'edition'               => $request->input('edition'),
                'place_of_publication'  => $request->input('publication'),
                'publisher'             => $request->input('publisher'),
                'copyrights'            => $request->input('copyright'),
                'cover_image'           => $request->input('cover_image'),
                'digital_copy_url'      => $request->input('digital_copy_url'),
                'remarks'               => $request->input('remarks'),
                'category_id'           => $request->input('category'),
                'book_type'             => $request->input('book_type'),
                'condition_status'      => $request->input('condition'),
                'availability_status'   => $request->input('availability'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', $e->getMessage())->withInput();
        }
        DB::commit();
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
        $validator = Validator::make($request->all(), [
            'accession'         => 'required|string',
            'call_number'       => 'nullable|string|max:50',
            'title'             => 'required|string|max:150',
            'authors'           => 'nullable|string|max:1024',
            'description'       => 'nullable|string',
            'edition'           => 'nullable|string|max:50',
            'publication'       => 'nullable|string|max:50',
            'publisher'         => 'nullable|string|max:100',
            'copyright'         => 'nullable|string|max:50',
            'cover_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'digital_copy_url'  => 'nullable|string|url',
            'remarks'           => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'remarks')),
            'category'          => 'required|in:' . implode(',', Category::all()->pluck('id')->toArray()),
            'book_type'         => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'book_type')),
            'condition'         => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'condition_status')),
            'availability'      => 'required|in:' . implode(',', $this->extract_enums($books->getTable(), 'availability_status')),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
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
            $accessions = array_map('trim', explode(',', $request->input('accession')));
            foreach ($accessions as $accession) {
                $barcode = new DNS1D();
                Book::create([
                    'accession'             => $accession,
                    'call_number'           => $request->input('call_number') ?? null,
                    'barcode'               => $barcode->getBarcodeJPG($request->input('accession'), 'C39', 2, 80, array(0, 0, 0, 0), false),
                    'title'                 => $request->input('title'),
                    'author'                => $request->input('authors') ?? null,
                    'description'           => $request->input('description') ?? null,
                    'edition'               => $request->input('edition') ?? null,
                    'place_of_publication'  => $request->input('publication') ?? null,
                    'publisher'             => $request->input('publisher') ?? null,
                    'copyrights'            => $request->input('copyright') ?? null,
                    'cover_image'           => $request->input('cover_image') ?? null,
                    'digital_copy_url'      => $request->input('digital_copy_url') ?? null,
                    'remarks'               => $request->input('remarks'),
                    'category_id'           => $request->input('category'),
                    'book_type'             => $request->input('book_type'),
                    'condition_status'      => $request->input('condition'),
                    'availability_status'   => $request->input('availability'),
                ]);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', $e->getMessage())->withInput();
        }
        DB::commit();
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

        $search   = $request->input('search', '');
        $category = $request->input('category', '');
        $ids      = array_filter(explode(',', $request->input('ids')), function ($id) {
            return is_numeric($id) && $id > 0;
        });

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
                    return redirect()->back()->with('toast-error', $validator->errors()->first());
                }

                $booksQuery->where('category_id', $category);
            }

            // Apply search filter if provided
            if ($search) {
                $booksQuery->where(function ($q) use ($search) {
                    $q->where('accession', 'like', '%' . $search . '%')
                        ->orWhere('title', 'like', '%' . $search . '%')
                        ->orWhere('author', 'like', '%' . $search . '%')
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
            return redirect()->back()->with('toast-warning', 'No books found for barcode export!');
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
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $id = $request->input('id');
            Book::find($id)->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->route('maintenance.books')->with('toast-error', 'Something went wrong!');
        }
        DB::commit();
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
        $ids = array_filter(explode(',', $request->input('ids')), function ($id) {
            return is_numeric($id) && $id > 0;
        });
        if (empty($ids)) {
            return redirect()->back()->with('toast-warning', 'No books selected for deletion!');
        }
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            Book::whereIn('id', $ids)->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('toast-error', 'Something went wrong!');
        }
        DB::commit();
        return redirect()->route('maintenance.books')->with('toast-success', 'Books deleted successfully');
    }
    private function generateCallNumber(Book $book)
    {
        // 1. Get Classification Code from Category (e.g., "Science" -> "SCI")
        $categoryCode = 'GEN'; // Default to "General"
        if ($book->category && !empty($book->category->name)) {
            $categoryCode = strtoupper(substr($book->category->name, 0, 3));
        }

        // 2. Get Author Cutter from the author's last name (e.g., "John Doe" -> "DOE")
        $authorCode = 'UNK'; // Default to "Unknown"
        if (!empty($book->author)) {
            $nameParts = explode(' ', $book->author);
            $firstName = $nameParts[0];
            $authorCode = strtoupper(substr($firstName, 0, 3));
        }

        // 3. Get Copyright Year
        $copyrightYear = !empty($book->copyrights) ? $book->copyrights : date('Y');

        // 4. Combine the parts into the final call number
        $callNumber = "{$categoryCode} {$authorCode} {$copyrightYear}";

        return $callNumber;
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
        $apiKey = env('GOOGLE_BOOKS_API_KEY');
        
        // Check if API key exists
        if (empty($apiKey)) {
            Log::warning('Google Books API key is not set in .env file');
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
                    'url' => $url
                ]);
                return null;
            }

            $data = $response->json();

            // Check if we have results
            if (empty($data['items']) || !is_array($data['items'])) {
                Log::info('No books found in Google Books API', [
                    'title' => $title,
                    'author' => $author,
                    'isbn' => $isbn
                ]);
                return null;
            }

            // Extract thumbnail URL
            $thumbnail = $data['items'][0]['volumeInfo']['imageLinks']['thumbnail'] ?? null;
            
            if (empty($thumbnail)) {
                Log::info('Book found but no thumbnail available', [
                    'title' => $title,
                    'author' => $author,
                    'isbn' => $isbn
                ]);
                return null;
            }

            // Force HTTPS and return
            return str_replace('http://', 'https://', $thumbnail);

        } catch (ConnectionException $e) {
            Log::error('Connection error while fetching book image', [
                'message' => $e->getMessage(),
                'url' => $url
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
}
