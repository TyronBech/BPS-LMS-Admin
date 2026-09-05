<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
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
      'sort_by' => 'nullable|in:access_code,updated_at',
      'sort_order' => 'nullable|in:asc,desc',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
    }

    $subjectsQuery = SubjectAccessCode::with([
      'books:id,accession,title',
    ]);

    if ($search !== '') {
      $subjectsQuery->where(function ($query) use ($search) {
        $query->where('access_code', 'like', "%{$search}%")
          ->orWhereHas('books', function ($bookQuery) use ($search) {
            $bookQuery->where('title', 'like', "%{$search}%")
              ->orWhere('accession', 'like', "%{$search}%");
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
    Log::info('Subject Maintenance: Attempting to create subject access code', [
      'user_id' => Auth::guard('admin')->id(),
      'user_name' => Auth::guard('admin')->user()->full_name,
      'access_code' => $request->input('access_code'),
      'ip_address' => $request->ip(),
      'timestamp' => now(),
    ]);

    $validator = Validator::make($request->all(), [
      'access_code' => 'required|string|max:255|unique:bk_subject_access_codes,access_code',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
    }

    DB::beginTransaction();

    try {
      DB::statement('SET @current_user_id = ?', [Auth::guard('admin')->user()->id]);

      SubjectAccessCode::create([
        'access_code' => trim((string) $request->input('access_code')),
      ]);

    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error('Subject Maintenance: Database error during creation', [
        'user_id' => Auth::guard('admin')->id(),
        'error_message' => $e->getMessage(),
        'error_trace' => $e->getTraceAsString(),
        'timestamp' => now(),
      ]);

      return redirect()->back()->with('toast-error', 'Error occurred while creating subject access code.')->withInput();
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
      'edit_subject_id' => 'required|integer|exists:bk_subject_access_codes,id',
      'access_code' => 'required|string|max:255|unique:bk_subject_access_codes,access_code,' . $request->input('edit_subject_id'),
    ]);

    if ($validator->fails()) {
      return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
    }

    DB::beginTransaction();

    try {
      DB::statement('SET @current_user_id = ?', [Auth::guard('admin')->user()->id]);

      $subject = SubjectAccessCode::findOrFail($request->input('edit_subject_id'));
      $subject->update([
        'access_code' => trim((string) $request->input('access_code')),
      ]);

    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error('Subject Maintenance: Database error during update', [
        'user_id' => Auth::guard('admin')->id(),
        'subject_id' => $request->input('edit_subject_id'),
        'error_message' => $e->getMessage(),
        'error_trace' => $e->getTraceAsString(),
        'timestamp' => now(),
      ]);

      return redirect()->back()->with('toast-error', 'Error occurred while updating subject access code.')->withInput();
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
      'delete_subject_id' => 'required|integer|exists:bk_subject_access_codes,id',
    ]);

    if ($validator->fails()) {
      return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
    }

    DB::beginTransaction();

    try {
      DB::statement('SET @current_user_id = ?', [Auth::guard('admin')->user()->id]);

      $subject = SubjectAccessCode::with('books')->findOrFail($request->input('delete_subject_id'));
      $subject->books()->detach();
      $subject->delete();

    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error('Subject Maintenance: Database error during deletion', [
        'user_id' => Auth::guard('admin')->id(),
        'subject_id' => $request->input('delete_subject_id'),
        'error_message' => $e->getMessage(),
        'error_trace' => $e->getTraceAsString(),
        'timestamp' => now(),
      ]);

      return redirect()->back()->with('toast-error', 'Error occurred while deleting subject access code.')->withInput();
    }

    DB::commit();

    return redirect()->route('maintenance.subjects')->with('toast-success', 'Subject deleted successfully.');
  }

}
