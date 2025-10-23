<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use App\Models\Book;
use DateTime;
use Exception;

class TransactionMaintenanceController extends Controller
{
    /**
     * Show the list of transactions in the database.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transactions = Transaction::with('user', 'book')
            ->orderBy('created_at', 'desc')
            ->get();
        $transaction            = new Transaction();
        $books                  = new Book();
        $transactionTypes       = $this->extract_enums($transaction->getTable(), 'transaction_type');
        $transactionStatuses    = $this->extract_enums($transaction->getTable(), 'status');
        $conditions             = $this->extract_enums($books->getTable(), 'condition_status');
        $penaltyStatuses        = $this->extract_enums($transaction->getTable(), 'penalty_status');
        return view('maintenance.transactions.index', compact('transactions', 'transactionTypes', 'transactionStatuses', 'conditions', 'penaltyStatuses'));
    }
    /**
     * Show a single transaction from the database based on the given id.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
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
        $validator = Validator::make($request->all(), [
            'due_date'          => 'required|date',
            'pickup_date'       => 'nullable|date',
            'transaction_type'  => 'required|in:' . implode(',', $this->extract_enums((new Transaction())->getTable(), 'transaction_type')),
            'status'            => 'required|in:' . implode(',', $this->extract_enums((new Transaction())->getTable(), 'status')),
            'book_condition'    => 'nullable|in:' . implode(',', $this->extract_enums((new Book())->getTable(), 'condition_status')),
            'penalty_total'     => 'nullable|numeric|min:0',
            'remarks'           => 'nullable|string|max:2048',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first());
        }
        DB::beginTransaction();
        try{
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $transaction = Transaction::find($request->input('edit_transaction_id'));
            if (!$transaction) {
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
                    return redirect()->back()->with('toast-error', 'Invalid pickup date format');
                }
            }

            $transaction->update([
                'due_date'          => $dueDate->format('Y-m-d'),
                'pickup_date'       => $pickupDate ? $pickupDate->format('Y-m-d') : null,
                'transaction_type'  => $request->input('transcaction_type'),
                'status'            => $request->input('status'),
                'book_condition'    => $request->input('book_condition') ?? null,
                'penalty_total'     => $request->input('penalty_total') ?? 0,
                'remarks'           => $request->input('remarks') ?? null,
            ]);
        } catch(\Illuminate\Database\QueryException $e){
            DB::rollBack();
            return redirect()->back()->with('toast-error', $e->getMessage());
        }
        DB::commit();
        return redirect()->back()->with('toast-success', 'Transaction updated successfully');
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
        $apiKey = env('GOOGLE_BOOKS_API_KEY');
        $url = null;

        if ($title || $author || $isbn) {
            $queryParts = [];

            if ($title) {
                $queryParts[] = "intitle:" . urlencode($title);
            }
            if ($author) {
                $queryParts[] = "inauthor:" . urlencode($author);
            }
            if ($isbn) {
                $queryParts[] = "isbn:" . urlencode($isbn);
            }

            $queryURL = implode("+", $queryParts);
            $url = "https://www.googleapis.com/books/v1/volumes?q={$queryURL}&key={$apiKey}&maxResults=1";
        }

        if (!$url) {
            return null;
        }

        // Path to local CA bundle
        $caPath = storage_path('certs/cacert.pem');

        try {
            // Try secure request with CA verification
            $options = [];
            if (file_exists($caPath)) {
                $options['verify'] = $caPath;
            }

            $response = Http::withOptions($options)->get($url);

            if ($response->successful()) {
                $data = $response->json();

                if (!empty($data['items'][0]['volumeInfo']['imageLinks']['thumbnail'])) {
                    return str_replace(
                        'http://',
                        'https://',
                        $data['items'][0]['volumeInfo']['imageLinks']['thumbnail']
                    );
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
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
