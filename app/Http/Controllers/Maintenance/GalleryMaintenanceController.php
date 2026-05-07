<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Enum\PermissionsEnum;
use App\Models\GalleryFolder;
use App\Models\GalleryVideo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GalleryMaintenanceController extends Controller
{
    
    // =========================================================
    // Folders
    // =========================================================

    private User $authAdmin;
    
    public function __construct()
    {
        $this->authAdmin = User::findOrFail(Auth::guard('admin')->user()->id);
    }

    /**
     * Display a paginated list of top-level gallery folders/albums.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $search  = $request->input('search', '');
        $perPage = $request->input('perPage', 10);

        Log::info('Gallery Maintenance: List page accessed', [
            'user_id'    => Auth::guard('admin')->id(),
            'user_name'  => Auth::guard('admin')->user()->full_name,
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

        $folders = GalleryFolder::whereNull('parent_id')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->withCount(['children', 'videos'])
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(['search' => $search, 'perPage' => $perPage]);

        return view('maintenance.library-website.gallery.index', compact('folders', 'search', 'perPage'));
    }

    /**
     * Show the form for creating a new folder.
     *
     * @return \Illuminate\View\View
     */
    public function createFolder()
    {
        if (!$this->authAdmin->can(PermissionsEnum::ADD_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')
                ->with('toast-error', 'You do not have permission to create gallery folders.');
        }

        Log::info('Gallery Maintenance: Create folder form accessed', [
            'user_id'    => Auth::guard('admin')->id(),
            'user_name'  => Auth::guard('admin')->user()->full_name,
            'ip_address' => request()->ip(),
            'timestamp'  => now(),
        ]);

        $parentFolders = GalleryFolder::whereNull('parent_id')->orderBy('name')->get();

        return view('maintenance.library-website.gallery.create-folder', compact('parentFolders'));
    }

    /**
     * Store a newly created folder.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeFolder(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::ADD_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')
                ->with('toast-error', 'You do not have permission to create gallery folders.');
        }

        Log::info('Gallery Maintenance: Attempting to create folder', [
            'user_id'    => Auth::guard('admin')->id(),
            'user_name'  => Auth::guard('admin')->user()->full_name,
            'name'       => $request->input('name'),
            'ip_address' => $request->ip(),
            'timestamp'  => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'title'       => 'nullable|string|max:255',
            'type'        => 'required|in:folder,album',
            'category'    => 'required|in:photo,video',
            'description' => 'nullable|string|max:2000',
            'fb_url'      => 'nullable|url|max:500',
            'album_date'  => 'nullable|date',
            'sort_order'  => 'nullable|integer|min:0|max:99999',
            'parent_id'   => 'nullable|exists:gallery_folders,id',
            'cover'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'name.required'     => 'The folder name is required.',
            'type.required'     => 'The folder type is required.',
            'type.in'           => 'Folder type must be either folder or album.',
            'category.required' => 'The category is required.',
            'category.in'       => 'Category must be either photo or video.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);

            $slug         = Str::slug($request->input('name'));
            $originalSlug = $slug;
            $count        = 1;
            while (GalleryFolder::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            $coverData = null;
            if ($request->hasFile('cover')) {
                $coverData = base64_encode(file_get_contents($request->file('cover')->getRealPath()));
            }

            GalleryFolder::create([
                'parent_id'   => $request->input('parent_id'),
                'name'        => $request->input('name'),
                'title'       => $request->input('title'),
                'slug'        => $slug,
                'type'        => $request->input('type', 'folder'),
                'category'    => $request->input('category', 'video'),
                'description' => $request->input('description'),
                'cover'       => $coverData,
                'fb_url'      => $request->input('fb_url'),
                'album_date'  => $request->input('album_date'),
                'sort_order'  => $request->input('sort_order', 0),
            ]);

            Log::info('Gallery Maintenance: Folder created successfully', [
                'user_id'    => Auth::guard('admin')->id(),
                'user_name'  => Auth::guard('admin')->user()->full_name,
                'slug'       => $slug,
                'timestamp'  => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Gallery Maintenance: Database error during folder creation', [
                'user_id'       => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'error_trace'   => $e->getTraceAsString(),
                'timestamp'     => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Error occurred while creating folder.')->withInput();
        }
        DB::commit();

        return redirect()->route('maintenance.library-website.gallery')
            ->with('toast-success', 'Folder created successfully.');
    }

    /**
     * Display the specified folder with its videos.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showFolder(Request $request)
    {
        try {
            $folder  = GalleryFolder::with(['videos', 'children'])->findOrFail($request->input('id'));
            $perPage = $request->input('perPage', 10);
            $videos  = GalleryVideo::where('folder_id', $folder->id)
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->appends(['id' => $folder->id, 'perPage' => $perPage]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('maintenance.library-website.gallery')
                ->with('toast-error', 'Folder not found.');
        }

        return view('maintenance.library-website.gallery.show-folder', compact('folder', 'videos', 'perPage'));
    }

    /**
     * Show the form for editing a folder.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function editFolder(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::EDIT_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')
                ->with('toast-error', 'You do not have permission to edit gallery folders.');
        }

        try {
            $folder        = GalleryFolder::findOrFail($request->input('id'));
            $parentFolders = GalleryFolder::whereNull('parent_id')
                ->where('id', '!=', $folder->id)
                ->orderBy('name')
                ->get();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('maintenance.library-website.gallery')
                ->with('toast-error', 'Folder not found.');
        }

        return view('maintenance.library-website.gallery.edit-folder', compact('folder', 'parentFolders'));
    }

    /**
     * Update the specified folder.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateFolder(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::EDIT_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')
                ->with('toast-error', 'You do not have permission to edit gallery folders.');
        }

        Log::info('Gallery Maintenance: Attempting to update folder', [
            'user_id'    => Auth::guard('admin')->id(),
            'user_name'  => Auth::guard('admin')->user()->full_name,
            'folder_id'  => $request->input('id'),
            'ip_address' => $request->ip(),
            'timestamp'  => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'id'          => 'required|exists:gallery_folders,id',
            'name'        => 'required|string|max:255',
            'title'       => 'nullable|string|max:255',
            'type'        => 'required|in:folder,album',
            'category'    => 'required|in:photo,video',
            'description' => 'nullable|string|max:2000',
            'fb_url'      => 'nullable|url|max:500',
            'album_date'  => 'nullable|date',
            'sort_order'  => 'nullable|integer|min:0|max:99999',
            'parent_id'   => 'nullable|exists:gallery_folders,id|different:id',
            'cover'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'id.required'       => 'Folder ID is required.',
            'id.exists'         => 'Folder not found.',
            'name.required'     => 'The folder name is required.',
            'type.required'     => 'The folder type is required.',
            'type.in'           => 'Folder type must be either folder or album.',
            'category.required' => 'The category is required.',
            'category.in'       => 'Category must be either photo or video.',
            'parent_id.different' => 'A folder cannot be its own parent.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $folder = GalleryFolder::findOrFail($request->input('id'));

            $updateData = [
                'parent_id'   => $request->input('parent_id'),
                'name'        => $request->input('name'),
                'title'       => $request->input('title'),
                'type'        => $request->input('type', 'folder'),
                'category'    => $request->input('category', 'video'),
                'description' => $request->input('description'),
                'fb_url'      => $request->input('fb_url'),
                'album_date'  => $request->input('album_date'),
                'sort_order'  => $request->input('sort_order', 0),
            ];

            if ($request->hasFile('cover')) {
                $updateData['cover'] = base64_encode(file_get_contents($request->file('cover')->getRealPath()));
            }

            if ($folder->name !== $request->input('name')) {
                $slug         = Str::slug($request->input('name'));
                $originalSlug = $slug;
                $count        = 1;
                while (GalleryFolder::where('slug', $slug)->where('id', '!=', $folder->id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $updateData['slug'] = $slug;
            }

            $folder->update($updateData);

            Log::info('Gallery Maintenance: Folder updated successfully', [
                'user_id'    => Auth::guard('admin')->id(),
                'user_name'  => Auth::guard('admin')->user()->full_name,
                'folder_id'  => $folder->id,
                'timestamp'  => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Gallery Maintenance: Database error during folder update', [
                'user_id'       => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'error_trace'   => $e->getTraceAsString(),
                'timestamp'     => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Error occurred while updating folder.')->withInput();
        }
        DB::commit();

        return redirect()->route('maintenance.library-website.gallery')
            ->with('toast-success', 'Folder updated successfully.');
    }

    /**
     * Remove the specified folder and cascade-delete its videos.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyFolder(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::DELETE_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')
                ->with('toast-error', 'You do not have permission to delete gallery folders.');
        }

        Log::warning('Gallery Maintenance: Attempting to delete folder', [
            'user_id'    => Auth::guard('admin')->id(),
            'user_name'  => Auth::guard('admin')->user()->full_name,
            'folder_id'  => $request->input('id'),
            'ip_address' => $request->ip(),
            'timestamp'  => now(),
        ]);

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $folder = GalleryFolder::findOrFail($request->input('id'));
            $folder->delete();

            Log::info('Gallery Maintenance: Folder deleted successfully', [
                'user_id'    => Auth::guard('admin')->id(),
                'user_name'  => Auth::guard('admin')->user()->full_name,
                'folder_id'  => $request->input('id'),
                'timestamp'  => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Gallery Maintenance: Database error during folder deletion', [
                'user_id'       => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'error_trace'   => $e->getTraceAsString(),
                'timestamp'     => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Error occurred while deleting folder.');
        }
        DB::commit();

        return redirect()->route('maintenance.library-website.gallery')
            ->with('toast-success', 'Folder deleted successfully.');
    }

    // =========================================================
    // Videos
    // =========================================================

    /**
     * Show the form for creating a new video inside a folder.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function createVideo(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::ADD_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')
                ->with('toast-error', 'You do not have permission to add gallery videos.');
        }

        try {
            $folder = GalleryFolder::findOrFail($request->input('folder_id'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('maintenance.library-website.gallery')
                ->with('toast-error', 'Folder not found.');
        }

        return view('maintenance.library-website.gallery.create-video', compact('folder'));
    }

    /**
     * Store a newly created video.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeVideo(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::ADD_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')
                ->with('toast-error', 'You do not have permission to add gallery videos.');
        }

        Log::info('Gallery Maintenance: Attempting to create video', [
            'user_id'    => Auth::guard('admin')->id(),
            'user_name'  => Auth::guard('admin')->user()->full_name,
            'folder_id'  => $request->input('folder_id'),
            'title'      => $request->input('title'),
            'ip_address' => $request->ip(),
            'timestamp'  => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'folder_id'  => 'required|exists:gallery_folders,id',
            'title'      => 'required|string|max:255',
            'url'        => 'required|url|max:500',
            'sort_order' => 'nullable|integer|min:0|max:99999',
        ], [
            'folder_id.required' => 'The folder is required.',
            'folder_id.exists'   => 'The specified folder does not exist.',
            'title.required'     => 'The video title is required.',
            'url.required'       => 'The video URL is required.',
            'url.url'            => 'The video URL must be a valid URL.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            GalleryVideo::create([
                'folder_id'  => $request->input('folder_id'),
                'title'      => $request->input('title'),
                'url'        => $request->input('url'),
                'sort_order' => $request->input('sort_order', 0),
            ]);

            Log::info('Gallery Maintenance: Video created successfully', [
                'user_id'    => Auth::guard('admin')->id(),
                'user_name'  => Auth::guard('admin')->user()->full_name,
                'folder_id'  => $request->input('folder_id'),
                'timestamp'  => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Gallery Maintenance: Database error during video creation', [
                'user_id'       => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'error_trace'   => $e->getTraceAsString(),
                'timestamp'     => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Error occurred while creating video.')->withInput();
        }
        DB::commit();

        return redirect()->route('maintenance.library-website.gallery.show-folder', ['id' => $request->input('folder_id')])
            ->with('toast-success', 'Video added successfully.');
    }

    /**
     * Show the form for editing a video.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function editVideo(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::EDIT_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')
                ->with('toast-error', 'You do not have permission to edit gallery videos.');
        }

        try {
            $video  = GalleryVideo::with('folder')->findOrFail($request->input('id'));
            $folder = $video->folder;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('maintenance.library-website.gallery')
                ->with('toast-error', 'Video not found.');
        }

        return view('maintenance.library-website.gallery.edit-video', compact('video', 'folder'));
    }

    /**
     * Update the specified video.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateVideo(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::EDIT_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')
                ->with('toast-error', 'You do not have permission to edit gallery videos.');
        }

        Log::info('Gallery Maintenance: Attempting to update video', [
            'user_id'    => Auth::guard('admin')->id(),
            'user_name'  => Auth::guard('admin')->user()->full_name,
            'video_id'   => $request->input('id'),
            'ip_address' => $request->ip(),
            'timestamp'  => now(),
        ]);

        $validator = Validator::make($request->all(), [
            'id'         => 'required|exists:gallery_videos,id',
            'title'      => 'required|string|max:255',
            'url'        => 'required|url|max:500',
            'sort_order' => 'nullable|integer|min:0|max:99999',
        ], [
            'id.required'    => 'Video ID is required.',
            'id.exists'      => 'Video not found.',
            'title.required' => 'The video title is required.',
            'url.required'   => 'The video URL is required.',
            'url.url'        => 'The video URL must be a valid URL.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $video = GalleryVideo::findOrFail($request->input('id'));
            $video->update([
                'title'      => $request->input('title'),
                'url'        => $request->input('url'),
                'sort_order' => $request->input('sort_order', 0),
            ]);

            Log::info('Gallery Maintenance: Video updated successfully', [
                'user_id'    => Auth::guard('admin')->id(),
                'user_name'  => Auth::guard('admin')->user()->full_name,
                'video_id'   => $video->id,
                'timestamp'  => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Gallery Maintenance: Database error during video update', [
                'user_id'       => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'error_trace'   => $e->getTraceAsString(),
                'timestamp'     => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Error occurred while updating video.')->withInput();
        }
        DB::commit();

        return redirect()->route('maintenance.library-website.gallery.show-folder', ['id' => $video->folder_id])
            ->with('toast-success', 'Video updated successfully.');
    }

    /**
     * Remove the specified video.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyVideo(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::DELETE_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')
                ->with('toast-error', 'You do not have permission to delete gallery videos.');
        }

        Log::warning('Gallery Maintenance: Attempting to delete video', [
            'user_id'    => Auth::guard('admin')->id(),
            'user_name'  => Auth::guard('admin')->user()->full_name,
            'video_id'   => $request->input('id'),
            'ip_address' => $request->ip(),
            'timestamp'  => now(),
        ]);

        DB::beginTransaction();
        try {
            DB::statement("SET @current_user_id = ?", [Auth::guard('admin')->user()->id]);
            $video     = GalleryVideo::findOrFail($request->input('id'));
            $folderId  = $video->folder_id;
            $video->delete();

            Log::info('Gallery Maintenance: Video deleted successfully', [
                'user_id'    => Auth::guard('admin')->id(),
                'user_name'  => Auth::guard('admin')->user()->full_name,
                'video_id'   => $request->input('id'),
                'timestamp'  => now(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Gallery Maintenance: Database error during video deletion', [
                'user_id'       => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'error_trace'   => $e->getTraceAsString(),
                'timestamp'     => now(),
            ]);
            return redirect()->back()->with('toast-error', 'Error occurred while deleting video.');
        }
        DB::commit();

        return redirect()->route('maintenance.library-website.gallery.show-folder', ['id' => $folderId])
            ->with('toast-success', 'Video deleted successfully.');
    }
}
