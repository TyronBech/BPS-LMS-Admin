<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use App\Models\Transaction;
use App\Models\Book;
use DateTime;
use Exception;

class TransactionMaintenanceController extends Controller
{
    /**
     * Show the list of transactions in the database.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);

        Log::info('Transaction Maintenance: List page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'per_page' => $perPage,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'perPage' => 'nullable|integer|min:1|max:500',
        ]);
        if ($validator->fails()) {
            Log::warning('Transaction Maintenance: Invalid perPage parameter', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', 'Invalid per page parameter')->withInput();
        }

        $transactions = Transaction::with('user', 'book')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
            ]);
        $transaction            = new Transaction();
        $books                  = new Book();
        $transactionTypes       = $this->extract_enums($transaction->getTable(), 'transaction_type');
        $transactionStatuses    = $this->extract_enums($transaction->getTable(), 'status');
        $conditions             = $this->extract_enums($books->getTable(), 'condition_status');
        $penaltyStatuses        = $this->extract_enums($transaction->getTable(), 'penalty_status');
        return view('maintenance.transactions.index', compact('transactions', 'transactionTypes', 'transactionStatuses', 'conditions', 'penaltyStatuses', 'perPage'));
    }
    /**
     * Show a single transaction from the database based on the given id.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        Log::info('Transaction Maintenance: Viewing transaction details', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'transaction_id' => $request->input('viewBtn'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $transaction = Transaction::with('user', 'book')
            ->where('id', $request->input('viewBtn'))
            ->firstOrFail();
        $mimeType = null;
        $accession = $transaction->book->accession;
        $book = $transaction->book;
        try {
            $cover = $this->getBookImage($book->title, $book->author, $book->isbn ?? null);
            if (!$cover) {
                $cover = null;
            }
        } catch (Exception $e) {
            $cover = null;
        }
        return view('maintenance.transactions.view', compact('transaction' , 'cover', 'mimeType'));
    }
    /**
     * Retrieve a single transaction from the database based on the given id.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function retrieve(Request $request)
    {
        Log::debug('Transaction Maintenance: Retrieving transaction JSON', [
            'user_id' => Auth::guard('admin')->id(),
            'transaction_id' => $request->input('viewBtn'),
            'timestamp' => now(),
        ]);

        $transaction = Transaction::with('user', 'book')
            ->where('id', $request->input('viewBtn'))
            ->firstOrFail();
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }
        return response()->json([
            'transaction' => $transaction
        ]);
    }
    /**
     * Update a transaction in the database.
     *
     * This function is used to update a transaction in the database. It validates the request
     * and updates the transaction with the given details. If there is an error during the
     * update process, it will rollback the transaction and redirect back with an error message.
     * If the update is successful, it will redirect back with a success message.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Database\QueryException
     */
    public function update(Request $request)
    {
        Log::info('Transaction Maintenance: Attempting to update transaction', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'transaction_id' => $request->input('edit_transaction_id'),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'due_date'          => 'required|date',
            'pickup_date'       => 'nullable|date',
            'transaction_type'  => 'required|in:' . implode(',', $this->extract_enums((new Transaction())->getTable(), 'transaction_type')),
            'status'            => 'required|in:' . implode(',', $this->extract_enums((new Transaction())->getTable(), 'status')),
            'book_condition'    => 'nullable|in:' . implode(',', $this->extract_enums((new Book())->getTable(), 'condition_status')),
            'penalty_status'    => 'nullable|in:' . implode(',', $this->extract_enums((new Transaction())->getTable(), 'penalty_status')),
            'penalty_total'     => 'nullable|numeric|min:0',
            'remarks'           => 'nullable|string|max:2048',
        ]);
        if ($validator->fails()) {
            Log::warning('Transaction Maintenance: Update validation failed', [
                'user_id' => Auth::guard('admin')->id(),
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try{
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $transaction = Transaction::find($request->input('edit_transaction_id'));
            if (!$transaction) {
                Log::warning('Transaction Maintenance: Update failed - Transaction not found', [
                    'user_id' => Auth::guard('admin')->id(),
                    'transaction_id' => $request->input('edit_transaction_id'),
                    'timestamp' => now(),
                ]);
                return redirect()->back()->with('toast-error', 'Transaction not found');
            }
            $dueDateInput = $request->input('due_date');
            $pickupDateInput = $request->input('pickup_date');

            // Parse due_date
            $dueDate = DateTime::createFromFormat('m/d/Y', $dueDateInput);
            if (!$dueDate) {
                // Fallback: try Y-m-d format
                $dueDate = DateTime::createFromFormat('Y-m-d', $dueDateInput);
            }
            if (!$dueDate) {
                Log::warning('Transaction Maintenance: Update failed - Invalid due date', [
                    'user_id' => Auth::guard('admin')->id(),
                    'input_date' => $dueDateInput,
                    'timestamp' => now(),
                ]);
                return redirect()->back()->with('toast-error', 'Invalid due date format');
            }

            // Parse pickup_date
            $pickupDate = null;
            if ($pickupDateInput) {
                $pickupDate = DateTime::createFromFormat('m/d/Y', $pickupDateInput);
                if (!$pickupDate) {
                    // Fallback: try Y-m-d format
                    $pickupDate = DateTime::createFromFormat('Y-m-d', $pickupDateInput);
                }
                if (!$pickupDate) {
                    Log::warning('Transaction Maintenance: Update failed - Invalid pickup date', [
                        'user_id' => Auth::guard('admin')->id(),
                        'input_date' => $pickupDateInput,
                        'timestamp' => now(),
                    ]);
                    return redirect()->back()->with('toast-error', 'Invalid pickup date format');
                }
            }

            $transaction->update([
                'due_date'          => $dueDate->format('Y-m-d'),
                'pickup_date'       => $pickupDate ? $pickupDate->format('Y-m-d') : null,
                'transaction_type'  => $request->input('transaction_type'),
                'status'            => $request->input('status'),
                'book_condition'    => $request->input('book_condition') ?? null,
                'penalty_total'     => $request->input('penalty_total') ?? 0,
                'penalty_status'    => $request->input('penalty_status') ?? 'No Penalty',
                'remarks'           => $request->input('remarks') ?? null,
            ]);
            if($request->input('transaction_type') == 'Returned' && $request->input('status') == 'Completed') {
                Log::debug('Transaction Maintenance: Updating book availability and return date', [
                    'user_id' => Auth::guard('admin')->id(),
                    'transaction_id' => $request->input('edit_transaction_id'),
                    'book_id' => $transaction->book_id,
                    'timestamp' => now(),
                ]);
                $transaction->book->update([
                    'availability_status' => 'Available',
                ]);
                $transaction->update([
                    'return_date' => now()->format('Y-m-d'),
                ]);
            }
            if(!$transaction->book) {
                Log::warning('Transaction Maintenance: Update failed - Book not found', [
                    'user_id' => Auth::guard('admin')->id(),
                    'transaction_id' => $request->input('edit_transaction_id'),
                    'book_id' => $transaction->book_id,
                    'timestamp' => now(),
                ]);
                DB::rollBack();
                return redirect()->back()->with('toast-error', 'Associated book not found');
            }
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            Log::error('Transaction Maintenance: Database error during update', [
                'user_id' => Auth::guard('admin')->id(),
                'transaction_id' => $request->input('edit_transaction_id'),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'timestamp' => now(),
            ]);
            return redirect()->back()->with('toast-error', $e->getMessage());
        }
        DB::commit();
        Log::info('Transaction Maintenance: Transaction updated successfully', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'transaction_id' => $request->input('edit_transaction_id'),
            'timestamp' => now(),
        ]);
        return redirect()->route('maintenance.circulations')->with('toast-success', 'Transaction updated successfully');
    }
    /**
     * Retrieves the book image from Google Books API.
     *
     * @param string|null $title The book title.
     * @param string|null $author The book author.
     * @param string|null $isbn The book ISBN.
     *
     * @return string|null The book image URL or null if no image is found.
     *
     * @throws \Exception
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
}
