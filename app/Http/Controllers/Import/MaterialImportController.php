<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\SubjectAccessCode;
use Milon\Barcode\DNS1D;
use App\Models\BkLastAccession;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MaterialImportController extends Controller
{
    /**
     * Index page of Material Import
     */
    public function index(Request $request)
    {
        Log::info('Material Import: Index page accessed', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->full_name,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        if (!$request->has('page') && !$request->has('perPage')) {
            $request->session()->forget('material_import_data');
        }

        $showTable = false;
        return view('import.materials.materials', compact('showTable'));
    }

    /**
     * Handles the upload of material data Excel file.
     */
    public function upload(Request $request)
    {
        try {
            $sessionData = $request->session()->get('material_import_data', []);

            if ($request->isMethod('post') && !$request->hasFile('file')) {
                // Merge submitted edits into the session data
                $submittedMaterials = $request->input('materials', []);
                foreach ($submittedMaterials as $index => $material) {
                    if (isset($sessionData[$index])) {
                        $sessionData[$index] = array_merge($sessionData[$index], $material);
                    }
                }
                $request->session()->put('material_import_data', $sessionData);
                $data = $sessionData;
            } else if ($request->has('page') || $request->has('perPage')) {
                $data = $sessionData;
            } else {
                if (!$request->hasFile('file')) {
                    return redirect()->route('import.import-materials')->with('toast-warning', "Please select a file.");
                }

                $file = $request->file('file');
                $reader = new ReaderXlsx();
                $spreadsheet = $reader->load($file);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                if (empty($rows) || !isset($rows[0][0])) {
                    // Check if there's data after row 18
                    if (count($rows) <= 18) {
                        return redirect()->route('import.import-materials')->with('toast-error', "Excel file is empty or template is incorrect.");
                    }
                }

                $data = [];
                // Data starts from Row 19 (Index 18)
                // Header is Row 18 (Index 17)
                $headerRowIndex = 17;
                $baseCol = 0;
                
                // Leading empty column detection
                if (isset($rows[$headerRowIndex][1]) && strtolower(trim((string)$rows[$headerRowIndex][1])) === 'accession') {
                    $baseCol = 1;
                }

                for ($i = 18; $i < count($rows); $i++) {
                    $isEmptyRow = true;
                    for ($col = $baseCol; $col <= $baseCol + 23; $col++) {
                        if (isset($rows[$i][$col]) && trim((string)$rows[$i][$col]) !== '') {
                            $isEmptyRow = false;
                            break;
                        }
                    }

                    if ($isEmptyRow) continue;

                    $materialData = [
                        'accession'             => isset($rows[$i][$baseCol]) ? trim((string)$rows[$i][$baseCol]) : null,
                        'title'                 => isset($rows[$i][$baseCol + 1]) ? trim((string)$rows[$i][$baseCol + 1]) : null,
                        'authors' => [
                            'Main author'      => isset($rows[$i][$baseCol + 2]) ? trim((string)$rows[$i][$baseCol + 2]) : null,
                            'Corporate author' => isset($rows[$i][$baseCol + 3]) ? trim((string)$rows[$i][$baseCol + 3]) : null,
                            'Added authors'    => isset($rows[$i][$baseCol + 4]) ? trim((string)$rows[$i][$baseCol + 4]) : null,
                            'Contributors'     => isset($rows[$i][$baseCol + 5]) ? trim((string)$rows[$i][$baseCol + 5]) : null,
                        ],
                        'edition'               => isset($rows[$i][$baseCol + 6]) ? trim((string)$rows[$i][$baseCol + 6]) : null,
                        'call_number'           => isset($rows[$i][$baseCol + 7]) ? trim((string)$rows[$i][$baseCol + 7]) : null,
                        'isbn'                  => isset($rows[$i][$baseCol + 8]) ? trim((string)$rows[$i][$baseCol + 8]) : null,
                        'description' => [
                            'Description'   => isset($rows[$i][$baseCol + 9]) ? trim((string)$rows[$i][$baseCol + 9]) : null,
                            'Content notes' => isset($rows[$i][$baseCol + 10]) ? trim((string)$rows[$i][$baseCol + 10]) : null,
                            'Abstract'      => isset($rows[$i][$baseCol + 11]) ? trim((string)$rows[$i][$baseCol + 11]) : null,
                            'Reviews'       => isset($rows[$i][$baseCol + 12]) ? trim((string)$rows[$i][$baseCol + 12]) : null,
                            'Extent'        => isset($rows[$i][$baseCol + 13]) ? trim((string)$rows[$i][$baseCol + 13]) : null,
                            'Acc Material'  => isset($rows[$i][$baseCol + 14]) ? trim((string)$rows[$i][$baseCol + 14]) : null,
                        ],
                        'place_of_publication'  => isset($rows[$i][$baseCol + 15]) ? trim((string)$rows[$i][$baseCol + 15]) : null,
                        'publisher'             => isset($rows[$i][$baseCol + 16]) ? trim((string)$rows[$i][$baseCol + 16]) : null,
                        'copyrights'            => isset($rows[$i][$baseCol + 17]) ? trim((string)$rows[$i][$baseCol + 17]) : null,
                        'location'              => isset($rows[$i][$baseCol + 18]) ? trim((string)$rows[$i][$baseCol + 18]) : null,
                        'languages'             => isset($rows[$i][$baseCol + 19]) ? trim((string)$rows[$i][$baseCol + 19]) : null,
                        'book_type'             => isset($rows[$i][$baseCol + 20]) ? trim((string)$rows[$i][$baseCol + 20]) : 'Print',
                        'category'              => isset($rows[$i][$baseCol + 21]) ? trim((string)$rows[$i][$baseCol + 21]) : null,
                        'digital_copy_url'      => isset($rows[$i][$baseCol + 22]) ? trim((string)$rows[$i][$baseCol + 22]) : null,
                        'subject'               => isset($rows[$i][$baseCol + 23]) ? trim((string)$rows[$i][$baseCol + 23]) : null,
                    ];

                    $data[] = $materialData;
                }

                $request->session()->put('material_import_data', $data);
            }

            $showTable = true;
            $perPage = $request->input('perPage', 10);
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentItems = array_slice($data, ($currentPage - 1) * $perPage, $perPage);
            $paginatedData = new LengthAwarePaginator($currentItems, count($data), $perPage, $currentPage, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            return view('import.materials.materials', ['showTable' => $showTable, 'data' => $paginatedData, 'perPage' => $perPage]);

        } catch (\Exception $e) {
            Log::error('Material Import Error: ' . $e->getMessage());
            return redirect()->route('import.import-materials')->with('toast-error', "An error occurred: " . $e->getMessage());
        }
    }

    /**
     * Stores the materials in the database.
     */
    public function store(Request $request)
    {
        $submittedMaterials = $request->input('materials', []);
        $allMaterials = $request->session()->get('material_import_data', []);

        // Update session data with edits
        foreach ($submittedMaterials as $index => $material) {
            if (isset($allMaterials[$index])) {
                // Merge nested
                if (isset($material['authors'])) {
                    $allMaterials[$index]['authors'] = array_merge($allMaterials[$index]['authors'], $material['authors']);
                }
                if (isset($material['description'])) {
                    $allMaterials[$index]['description'] = array_merge($allMaterials[$index]['description'], $material['description']);
                }
                $allMaterials[$index] = array_merge($allMaterials[$index], array_diff_key($material, ['authors' => 1, 'description' => 1]));
            }
        }

        $data = $allMaterials;
        $newCount = 0;
        
        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::id()]);
            
            foreach ($data as $index => $item) {
                $validator = Validator::make($item, [
                    'accession' => 'required|string|max:255',
                    'title' => 'required|string|max:255',
                    'category' => 'required|string',
                    'book_type' => 'required|string',
                ]);

                if ($validator->fails()) {
                    throw new \Exception("Validation failed for accession: " . ($item['accession'] ?? 'Unknown') . ". " . $validator->errors()->first());
                }

                $category = Category::where(DB::raw('lower(name)'), strtolower($item['category']))->first();
                if (!$category) {
                    throw new \Exception("Category not found: " . $item['category']);
                }

                if (Book::where('accession', $item['accession'])->exists()) {
                    throw new \Exception("Duplicate accession number: " . $item['accession']);
                }

                // Map book_type to standardized values
                $typeMap = [
                    'print'     => 'Print',
                    'non-print' => 'Non-print',
                    'e-books'   => 'E-books',
                    'ebooks'    => 'E-books',
                    'ebook'     => 'E-books',
                    'e-book'    => 'E-books',
                    'physical'  => 'Print', // Support legacy values
                ];
                $finalType = $typeMap[strtolower($item['book_type'])] ?? 'Print';

                // Check if the category's type matches the material's type
                if ($category->category_type !== $finalType) {
                    throw new \Exception("Category type mismatch for accession: " . $item['accession'] . ". Category '{$category->name}' is classified as '{$category->category_type}', but the material type is set to '{$finalType}'.");
                }

                $barcode = new DNS1D();
                $newMaterial = Book::create([
                    'accession'             => $item['accession'],
                    'title'                 => $item['title'],
                    'authors'               => $item['authors'],
                    'description'           => $item['description'],
                    'edition'               => $item['edition'] ?? null,
                    'call_number'           => $item['call_number'] ?? null,
                    'isbn'                  => $item['isbn'] ?? null,
                    'place_of_publication'  => $item['place_of_publication'] ?? null,
                    'publisher'             => $item['publisher'] ?? null,
                    'copyrights'            => $item['copyrights'] ?? null,
                    'location'              => $item['location'] ?? null,
                    'languages'             => $item['languages'] ?? null,
                    'book_type'             => $finalType,
                    'category_id'           => $category->id,
                    'barcode'               => $barcode->getBarcodeJPG($item['accession'], 'C39', 2, 80, [0,0,0,0], false),
                    'digital_copy_url'      => $item['digital_copy_url'] ?? null,
                    'remarks'               => 'On Shelf',
                    'availability_status'   => 'Available',
                    'condition_status'      => 'New',
                ]);

                // Sync Subjects
                if (!empty($item['subject'])) {
                    // Split by: "; ", ", ", ";", or ","
                    $subjects = preg_split('/(; |, |;|,)/', $item['subject']);
                    $accessCodeIds = [];
                    foreach ($subjects as $name) {
                        $name = trim($name);
                        if (empty($name)) continue;
                        $accessCode = SubjectAccessCode::firstOrCreate(['access_code' => $name]);
                        $accessCodeIds[] = $accessCode->id;
                    }
                    $newMaterial->subjectAccessCodes()->sync($accessCodeIds);
                }
                
                // Update last accession
                BkLastAccession::updateOrCreate(
                    ['category_id' => $category->id],
                    ['accession_number' => $item['accession']]
                );

                // Inventory Entry
                Inventory::create([
                    'book_id' => $newMaterial->id,
                    'is_scanned' => 1,
                    'checked_at' => now(),
                ]);

                $newCount++;
            }

            DB::commit();
            $request->session()->forget('material_import_data');
            return redirect()->route('import.import-materials')->with('toast-success', "Imported {$newCount} materials successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Material Import Store Error: ' . $e->getMessage());
            return redirect()->route('import.import-materials')->with('toast-error', $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $filePath = public_path('excel/Material-template.xlsx');
        if (File::exists($filePath)) {
            return Response::download($filePath, 'Material-template.xlsx');
        }
        $oldPath = public_path('excel/Book-template.xlsx');
        if (File::exists($oldPath)) {
            return Response::download($oldPath, 'Material-template.xlsx');
        }
        abort(404, 'Template not found.');
    }
}
