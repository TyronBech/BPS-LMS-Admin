<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Enum\PermissionsEnum;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AnnouncementMaintenanceController extends Controller
{
    private User $authAdmin;
    
    public function __construct()
    {
        $this->authAdmin = User::findOrFail(Auth::guard('admin')->user()->id);
    }

    /**
     * Display a paginated list of announcements.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $search  = $request->input('search', '');
        $perPage = $request->input('perPage', 10);

        Log::info('Announcement Maintenance: List page accessed', [
            'user_id'    => Auth::guard('admin')->id(),
            'user_name'  => Auth::guard('admin')->user()->full_name,
            'search'     => $search,
            'per_page'   => $perPage,
            'ip_address' => $request->ip(),
            'timestamp'  => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'search'  => 'nullable|string|max:255',
            'perPage' => 'nullable|integer|min:1|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $announcements = Announcement::when($search, function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%");
        })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(['search' => $search, 'perPage' => $perPage]);

        return view('maintenance.library-website.announcements.index', compact('announcements', 'search', 'perPage'));
    }

    /**
     * Show the form for creating a new announcement.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if (!$this->authAdmin->can(PermissionsEnum::ADD_ANNOUNCEMENTS)) {
            return redirect()->route('maintenance.library-website.announcements')
                ->with('toast-error', 'You do not have permission to create announcements.');
        }

        Log::info('Announcement Maintenance: Create form accessed', [
            'user_id'    => Auth::guard('admin')->id(),
            'user_name'  => Auth::guard('admin')->user()->full_name,
            'ip_address' => request()->ip(),
            'timestamp'  => now(),
        ]);

        return view('maintenance.library-website.announcements.create');
    }

    /**
     * Store a newly created announcement in the database.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::ADD_ANNOUNCEMENTS)) {
            return redirect()->route('maintenance.library-website.announcements')
                ->with('toast-error', 'You do not have permission to create announcements.');
        }

        Log::info('Announcement Maintenance: Attempting to create announcement', [
            'user_id'    => Auth::guard('admin')->id(),
            'user_name'  => Auth::guard('admin')->user()->full_name,
            'title'      => $request->input('title'),
            'ip_address' => $request->ip(),
            'timestamp'  => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'title'        => 'required|string|max:255',
            'content'      => 'required|string',
            'category'     => 'required|string|max:100',
            'priority'     => 'required|in:high,normal',
            'date'         => 'nullable|date',
            'is_featured'  => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'quote'        => 'nullable|string|max:500',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'title.required'    => 'The title field is required.',
            'content.required'  => 'The content field is required.',
            'category.required' => 'The category field is required.',
            'priority.required' => 'The priority field is required.',
            'priority.in'       => 'Priority must be either high or normal.',
        ]);

        if ($validator->fails()) {
            Log::warning('Announcement Maintenance: Validation failed on create', [
                'user_id'    => Auth::guard('admin')->id(),
                'errors'     => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp'  => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);

            $slug        = Str::slug($request->input('title'));
            $originalSlug = $slug;
            $count       = 1;
            while (Announcement::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            $imageData = null;
            if ($request->hasFile('image')) {
                $imageData = base64_encode(file_get_contents($request->file('image')->getRealPath()));
            }

            Announcement::create([
                'title'        => $request->input('title'),
                'slug'         => $slug,
                'content'      => $request->input('content'),
                'category'     => $request->input('category'),
                'priority'     => $request->input('priority', 'normal'),
                'date'         => $request->input('date'),
                'is_featured'  => $request->boolean('is_featured'),
                'is_published' => $request->boolean('is_published'),
                'quote'        => $request->input('quote'),
                'image'        => $imageData,
            ]);

            Log::info('Announcement Maintenance: Announcement created successfully', [
                'user_id'    => Auth::guard('admin')->id(),
                'user_name'  => Auth::guard('admin')->user()->full_name,
                'slug'       => $slug,
                'timestamp'  => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Announcement Maintenance: Database error during creation', [
                'user_id'       => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'error_trace'   => $e->getTraceAsString(),
                'timestamp'     => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Error occurred while creating announcement.')->withInput();
        }
        DB::commit();

        return redirect()->route('maintenance.library-website.announcements')
            ->with('toast-success', 'Announcement created successfully.');
    }

    /**
     * Display the specified announcement.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request)
    {
        Log::info('Announcement Maintenance: View page accessed', [
            'user_id'        => Auth::guard('admin')->id(),
            'announcement_id' => $request->input('id'),
            'ip_address'     => $request->ip(),
            'timestamp'      => now(),
        ]);

        try {
            $announcement = Announcement::findOrFail($request->input('id'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('maintenance.library-website.announcements')
                ->with('toast-error', 'Announcement not found.');
        }

        return view('maintenance.library-website.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified announcement.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::EDIT_ANNOUNCEMENTS)) {
            return redirect()->route('maintenance.library-website.announcements')
                ->with('toast-error', 'You do not have permission to edit announcements.');
        }

        Log::info('Announcement Maintenance: Edit form accessed', [
            'user_id'        => Auth::guard('admin')->id(),
            'user_name'      => Auth::guard('admin')->user()->full_name,
            'announcement_id' => $request->input('id'),
            'ip_address'     => $request->ip(),
            'timestamp'      => now(),
        ]);

        try {
            $announcement = Announcement::findOrFail($request->input('id'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('maintenance.library-website.announcements')
                ->with('toast-error', 'Announcement not found.');
        }

        return view('maintenance.library-website.announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified announcement in the database.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::EDIT_ANNOUNCEMENTS)) {
            return redirect()->route('maintenance.library-website.announcements')
                ->with('toast-error', 'You do not have permission to edit announcements.');
        }

        Log::info('Announcement Maintenance: Attempting to update announcement', [
            'user_id'        => Auth::guard('admin')->id(),
            'user_name'      => Auth::guard('admin')->user()->full_name,
            'announcement_id' => $request->input('id'),
            'ip_address'     => $request->ip(),
            'timestamp'      => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'id'           => 'required|exists:announcements,id',
            'title'        => 'required|string|max:255',
            'content'      => 'required|string',
            'category'     => 'required|string|max:100',
            'priority'     => 'required|in:high,normal',
            'date'         => 'nullable|date',
            'is_featured'  => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'quote'        => 'nullable|string|max:500',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'id.required'       => 'Announcement ID is required.',
            'id.exists'         => 'Announcement not found.',
            'title.required'    => 'The title field is required.',
            'content.required'  => 'The content field is required.',
            'category.required' => 'The category field is required.',
            'priority.required' => 'The priority field is required.',
            'priority.in'       => 'Priority must be either high or normal.',
        ]);

        if ($validator->fails()) {
            Log::warning('Announcement Maintenance: Validation failed on update', [
                'user_id'    => Auth::guard('admin')->id(),
                'errors'     => $validator->errors(),
                'ip_address' => $request->ip(),
                'timestamp'  => now(),
            ]);
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $announcement = Announcement::findOrFail($request->input('id'));

            $updateData = [
                'title'        => $request->input('title'),
                'content'      => $request->input('content'),
                'category'     => $request->input('category'),
                'priority'     => $request->input('priority', 'normal'),
                'date'         => $request->input('date'),
                'is_featured'  => $request->boolean('is_featured'),
                'is_published' => $request->boolean('is_published'),
                'quote'        => $request->input('quote'),
            ];

            if ($request->hasFile('image')) {
                $updateData['image'] = base64_encode(file_get_contents($request->file('image')->getRealPath()));
            }

            // Regenerate slug only if the title changed
            if ($announcement->title !== $request->input('title')) {
                $slug         = Str::slug($request->input('title'));
                $originalSlug = $slug;
                $count        = 1;
                while (Announcement::where('slug', $slug)->where('id', '!=', $announcement->id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $updateData['slug'] = $slug;
            }

            $announcement->update($updateData);

            Log::info('Announcement Maintenance: Announcement updated successfully', [
                'user_id'        => Auth::guard('admin')->id(),
                'user_name'      => Auth::guard('admin')->user()->full_name,
                'announcement_id' => $announcement->id,
                'timestamp'      => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Announcement Maintenance: Database error during update', [
                'user_id'       => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'error_trace'   => $e->getTraceAsString(),
                'timestamp'     => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Error occurred while updating announcement.')->withInput();
        }
        DB::commit();

        return redirect()->route('maintenance.library-website.announcements')
            ->with('toast-success', 'Announcement updated successfully.');
    }

    /**
     * Remove the specified announcement from the database (soft delete).
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::DELETE_ANNOUNCEMENTS)) {
            return redirect()->route('maintenance.library-website.announcements')
                ->with('toast-error', 'You do not have permission to delete announcements.');
        }

        Log::warning('Announcement Maintenance: Attempting to delete announcement', [
            'user_id'        => Auth::guard('admin')->id(),
            'user_name'      => Auth::guard('admin')->user()->full_name,
            'announcement_id' => $request->input('id'),
            'ip_address'     => $request->ip(),
            'timestamp'      => now(),
        ]);

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $announcement = Announcement::findOrFail($request->input('id'));
            $announcement->delete();

            Log::info('Announcement Maintenance: Announcement deleted successfully', [
                'user_id'        => Auth::guard('admin')->id(),
                'user_name'      => Auth::guard('admin')->user()->full_name,
                'announcement_id' => $request->input('id'),
                'timestamp'      => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Announcement Maintenance: Database error during deletion', [
                'user_id'       => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'error_trace'   => $e->getTraceAsString(),
                'timestamp'     => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Error occurred while deleting announcement.');
        }
        DB::commit();

        return redirect()->route('maintenance.library-website.announcements')
            ->with('toast-success', 'Announcement deleted successfully.');
    }
}
