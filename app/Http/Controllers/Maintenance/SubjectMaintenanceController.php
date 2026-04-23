<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Subject;
use App\Models\SubjectAccessCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubjectMaintenanceController extends Controller
{
  public function index(Request $request)
  {
    $perPage = $request->input('perPage', 10);
    $search = trim((string) $request->input('search', ''));
    $sortBy = $request->input('sort_by', '');
    $sortOrder = $request->input('sort_order', '');

    Log::info('Subject Maintenance: List page accessed', [
      'user_id' => Auth::guard('admin')->id(),
      'user_name' => Auth::guard('admin')->user()->full_name,
      'search_term' => $search,
      'sort_by' => $sortBy,
      'sort_order' => $sortOrder,
      'per_page' => $perPage,
      'ip_address' => $request->ip(),
      'timestamp' => now(),
    ]);

    $validator = Validator::make($request->all(), [
      'perPage' => 'nullable|integer|min:1|max:500',
      'search' => 'nullable|string|max:255',
      'sort_by' => 'nullable|in:ddc,name,updated_at',
      'sort_order' => 'nullable|in:asc,desc',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
    }

    $subjectsQuery = Subject::with([
      'accessCodes:id,access_code',
      'books:id,subject_id,accession,title',
    ]);

    if ($search !== '') {
      $subjectsQuery->where(function ($query) use ($search) {
        $query->where('name', 'like', "%{$search}%")
          ->orWhere('ddc', 'like', "%{$search}%")
          ->orWhereHas('books', function ($bookQuery) use ($search) {
            $bookQuery->where('title', 'like', "%{$search}%")
              ->orWhere('accession', 'like', "%{$search}%");
          })
          ->orWhereHas('accessCodes', function ($accessCodeQuery) use ($search) {
            $accessCodeQuery->where('access_code', 'like', "%{$search}%");
          });
      });
    }

    if ($sortBy && $sortOrder) {
      $subjectsQuery->orderBy($sortBy, $sortOrder)->orderBy('id', 'desc');
    } else {
      $subjectsQuery->orderBy('updated_at', 'desc')->orderBy('id', 'desc');
    }

    $subjects = $subjectsQuery->paginate($perPage)->appends([
      'perPage' => $perPage,
      'search' => $search,
      'sort_by' => $sortBy,
      'sort_order' => $sortOrder,
    ]);
    return view('maintenance.subjects.index', compact('subjects', 'perPage', 'search', 'sortBy', 'sortOrder'));
  }

  public function store(Request $request)
  {
    Log::info('Subject Maintenance: Attempting to create subject', [
      'user_id' => Auth::guard('admin')->id(),
      'user_name' => Auth::guard('admin')->user()->full_name,
      'subject_name' => $request->input('name'),
      'ip_address' => $request->ip(),
      'timestamp' => now(),
    ]);

    $validator = Validator::make($request->all(), [
      'ddc' => 'nullable|string|max:50',
      'name' => 'required|string|max:255',
      'access_codes' => 'required|string',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
    }

    $accessCodes = $this->parseAccessCodes($request->input('access_codes'));
    if (count($accessCodes) === 0) {
      return redirect()->back()->with('toast-warning', 'Please add at least one subject access code.')->withInput();
    }

    DB::beginTransaction();

    try {
      DB::statement('SET @current_user_id = ?', [Auth::guard('admin')->user()->id]);

      $subject = Subject::create([
        'ddc' => $request->input('ddc') ?: null,
        'name' => trim((string) $request->input('name')),
      ]);

      $accessCodeIds = $this->resolveAccessCodeIds($accessCodes, $subject->id);
      $subject->accessCodes()->sync($accessCodeIds);
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error('Subject Maintenance: Database error during creation', [
        'user_id' => Auth::guard('admin')->id(),
        'error_message' => $e->getMessage(),
        'error_trace' => $e->getTraceAsString(),
        'timestamp' => now(),
      ]);

      return redirect()->back()->with('toast-error', 'Error occurred while creating subject.')->withInput();
    }

    DB::commit();

    return redirect()->route('maintenance.subjects')->with('toast-success', 'Subject created successfully.');
  }

  public function update(Request $request)
  {
    Log::info('Subject Maintenance: Attempting to update subject', [
      'user_id' => Auth::guard('admin')->id(),
      'user_name' => Auth::guard('admin')->user()->full_name,
      'subject_id' => $request->input('edit_subject_id'),
      'ip_address' => $request->ip(),
      'timestamp' => now(),
    ]);

    $validator = Validator::make($request->all(), [
      'edit_subject_id' => 'required|integer|exists:bk_subjects,id',
      'ddc' => 'nullable|string|max:50',
      'name' => 'required|string|max:255',
      'access_codes' => 'required|string',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
    }

    $accessCodes = $this->parseAccessCodes($request->input('access_codes'));
    if (count($accessCodes) === 0) {
      return redirect()->back()->with('toast-warning', 'Please add at least one subject access code.')->withInput();
    }

    DB::beginTransaction();

    try {
      DB::statement('SET @current_user_id = ?', [Auth::guard('admin')->user()->id]);

      $subject = Subject::findOrFail($request->input('edit_subject_id'));
      $subject->update([
        'ddc' => $request->input('ddc') ?: null,
        'name' => trim((string) $request->input('name')),
      ]);

      $accessCodeIds = $this->resolveAccessCodeIds($accessCodes, $subject->id);
      $subject->accessCodes()->sync($accessCodeIds);
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error('Subject Maintenance: Database error during update', [
        'user_id' => Auth::guard('admin')->id(),
        'subject_id' => $request->input('edit_subject_id'),
        'error_message' => $e->getMessage(),
        'error_trace' => $e->getTraceAsString(),
        'timestamp' => now(),
      ]);

      return redirect()->back()->with('toast-error', 'Error occurred while updating subject.')->withInput();
    }

    DB::commit();

    return redirect()->route('maintenance.subjects')->with('toast-success', 'Subject updated successfully.');
  }

  public function destroy(Request $request)
  {
    Log::warning('Subject Maintenance: Attempting to delete subject', [
      'user_id' => Auth::guard('admin')->id(),
      'user_name' => Auth::guard('admin')->user()->full_name,
      'subject_id' => $request->input('delete_subject_id'),
      'ip_address' => $request->ip(),
      'timestamp' => now(),
    ]);

    $validator = Validator::make($request->all(), [
      'delete_subject_id' => 'required|integer|exists:bk_subjects,id',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
    }

    DB::beginTransaction();

    try {
      DB::statement('SET @current_user_id = ?', [Auth::guard('admin')->user()->id]);

      $subject = Subject::with('accessCodes')->findOrFail($request->input('delete_subject_id'));
      Book::withTrashed()->where('subject_id', $subject->id)->update(['subject_id' => null]);
      $subject->accessCodes()->detach();
      $subject->delete();

      SubjectAccessCode::whereDoesntHave('subjects')->delete();
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error('Subject Maintenance: Database error during deletion', [
        'user_id' => Auth::guard('admin')->id(),
        'subject_id' => $request->input('delete_subject_id'),
        'error_message' => $e->getMessage(),
        'error_trace' => $e->getTraceAsString(),
        'timestamp' => now(),
      ]);

      return redirect()->back()->with('toast-error', 'Error occurred while deleting subject.')->withInput();
    }

    DB::commit();

    return redirect()->route('maintenance.subjects')->with('toast-success', 'Subject deleted successfully.');
  }

  public function suggestAccessCodes(Request $request)
  {
    $query = trim((string) $request->input('q', ''));

    if ($query === '') {
      return response()->json([]);
    }

    $accessCodes = SubjectAccessCode::query()
      ->select('access_code')
      ->where('access_code', 'like', "%{$query}%")
      ->groupBy('access_code')
      ->orderByRaw('LENGTH(access_code) asc')
      ->orderBy('access_code', 'asc')
      ->limit(10)
      ->pluck('access_code')
      ->values();

    return response()->json($accessCodes);
  }

  private function parseAccessCodes(string $rawAccessCodes): array
  {
    $decoded = json_decode($rawAccessCodes, true);

    if (!is_array($decoded)) {
      $decoded = explode(',', $rawAccessCodes);
    }

    $parsed = collect($decoded)
      ->map(function ($item) {
        return trim((string) $item);
      })
      ->filter(function ($item) {
        return $item !== '';
      })
      ->unique(function ($item) {
        return strtolower($item);
      })
      ->values()
      ->all();

    return $parsed;
  }

  private function resolveAccessCodeIds(array $accessCodes, int $subjectId): array
  {
    $ids = [];

    foreach ($accessCodes as $accessCode) {
      $existing = SubjectAccessCode::whereRaw('LOWER(access_code) = ?', [strtolower($accessCode)])->first();

      if ($existing) {
        $ids[] = $existing->id;
        continue;
      }

      $created = SubjectAccessCode::create([
        'subject_id' => $subjectId,
        'access_code' => $accessCode,
      ]);

      $ids[] = $created->id;
    }

    return $ids;
  }
}
