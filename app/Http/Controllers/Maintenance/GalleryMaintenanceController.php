<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Enum\PermissionsEnum;
use App\Models\PhotoAlbum;
use App\Models\VideoAlbum;
use App\Models\VideoFolder;
use App\Models\VideoItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GalleryMaintenanceController extends Controller
{
    private User $authAdmin;

    public function __construct()
    {
        $this->authAdmin = User::findOrFail(Auth::guard('admin')->user()->id);
    }

    /**
     * Display a paginated list of Photo Albums and Video Albums.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 10);
        $tab = $request->input('tab', 'photo'); // Default tab

        Log::info('Gallery Maintenance: List page accessed', [
            'user_id' => Auth::guard('admin')->id(),
            'user_name' => Auth::guard('admin')->user()->full_name,
            'ip_address' => $request->ip(),
            'timestamp' => now(),
        ]);

        $photoAlbums = PhotoAlbum::when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'photo_page')
            ->appends(['search' => $search, 'perPage' => $perPage, 'tab' => 'photo']);

        $videoAlbums = VideoAlbum::when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            })
            ->withCount('folders')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'video_page')
            ->appends(['search' => $search, 'perPage' => $perPage, 'tab' => 'video']);

        return view('maintenance.library-website.gallery.index', compact('photoAlbums', 'videoAlbums', 'search', 'perPage', 'tab'));
    }

    // =========================================================
    // Photo Albums
    // =========================================================

    public function createPhotoAlbum()
    {
        if (!$this->authAdmin->can(PermissionsEnum::ADD_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }
        return view('maintenance.library-website.gallery.create-photo-album');
    }

    public function storePhotoAlbum(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::ADD_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'fb_url' => 'nullable|url|max:500',
            'album_date' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (PhotoAlbum::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        PhotoAlbum::create([
            'name' => $request->name,
            'title' => $request->title,
            'slug' => $slug,
            'description' => $request->description,
            'fb_url' => $request->fb_url,
            'album_date' => $request->album_date,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('maintenance.library-website.gallery', ['tab' => 'photo'])
            ->with('toast-success', 'Photo Album created successfully.');
    }

    public function editPhotoAlbum(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::EDIT_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }

        $album = PhotoAlbum::findOrFail($request->id);
        return view('maintenance.library-website.gallery.edit-photo-album', compact('album'));
    }

    public function updatePhotoAlbum(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::EDIT_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }

        $album = PhotoAlbum::findOrFail($request->id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'fb_url' => 'nullable|url|max:500',
            'album_date' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (PhotoAlbum::where('slug', $slug)->where('id', '!=', $album->id)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $album->update([
            'name' => $request->name,
            'title' => $request->title,
            'slug' => $slug,
            'description' => $request->description,
            'fb_url' => $request->fb_url,
            'album_date' => $request->album_date,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('maintenance.library-website.gallery', ['tab' => 'photo'])
            ->with('toast-success', 'Photo Album updated successfully.');
    }

    public function destroyPhotoAlbum(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::DELETE_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }

        $album = PhotoAlbum::findOrFail($request->id);
        $album->delete();

        return redirect()->route('maintenance.library-website.gallery', ['tab' => 'photo'])
            ->with('toast-success', 'Photo Album deleted successfully.');
    }

    // =========================================================
    // Video Albums
    // =========================================================

    public function createVideoAlbum()
    {
        if (!$this->authAdmin->can(PermissionsEnum::ADD_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }
        return view('maintenance.library-website.gallery.create-video-album');
    }

    public function storeVideoAlbum(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::ADD_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'fb_url' => 'nullable|url|max:500',
            'album_date' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (VideoAlbum::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        VideoAlbum::create([
            'name' => $request->name,
            'title' => $request->title,
            'slug' => $slug,
            'description' => $request->description,
            'fb_url' => $request->fb_url,
            'album_date' => $request->album_date,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('maintenance.library-website.gallery', ['tab' => 'video'])
            ->with('toast-success', 'Video Album created successfully.');
    }

    public function showVideoAlbum(Request $request)
    {
        $album = VideoAlbum::findOrFail($request->id);
        $folders = VideoFolder::where('album_id', $album->id)
            ->withCount('items')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('maintenance.library-website.gallery.show-video-album', compact('album', 'folders'));
    }

    public function editVideoAlbum(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::EDIT_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }

        $album = VideoAlbum::findOrFail($request->id);
        return view('maintenance.library-website.gallery.edit-video-album', compact('album'));
    }

    public function updateVideoAlbum(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::EDIT_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }

        $album = VideoAlbum::findOrFail($request->id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'fb_url' => 'nullable|url|max:500',
            'album_date' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (VideoAlbum::where('slug', $slug)->where('id', '!=', $album->id)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $album->update([
            'name' => $request->name,
            'title' => $request->title,
            'slug' => $slug,
            'description' => $request->description,
            'fb_url' => $request->fb_url,
            'album_date' => $request->album_date,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('maintenance.library-website.gallery', ['tab' => 'video'])
            ->with('toast-success', 'Video Album updated successfully.');
    }

    public function destroyVideoAlbum(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::DELETE_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }

        $album = VideoAlbum::findOrFail($request->id);
        $album->delete(); // Cascades delete if configured in DB, else need to delete relationships

        return redirect()->route('maintenance.library-website.gallery', ['tab' => 'video'])
            ->with('toast-success', 'Video Album deleted successfully.');
    }

    // =========================================================
    // Video Folders
    // =========================================================

    public function createVideoFolder(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::ADD_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }
        $album = VideoAlbum::findOrFail($request->album_id);
        return view('maintenance.library-website.gallery.create-video-folder', compact('album'));
    }

    public function storeVideoFolder(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::ADD_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }

        $validator = Validator::make($request->all(), [
            'album_id' => 'required|exists:video_albums,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (VideoFolder::where('album_id', $request->album_id)->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        VideoFolder::create([
            'album_id' => $request->album_id,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('maintenance.library-website.gallery.show-video-album', ['id' => $request->album_id])
            ->with('toast-success', 'Video Folder created successfully.');
    }

    public function showVideoFolder(Request $request)
    {
        $folder = VideoFolder::with('album')->findOrFail($request->id);
        $items = VideoItem::where('folder_id', $folder->id)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('maintenance.library-website.gallery.show-video-folder', compact('folder', 'items'));
    }

    public function editVideoFolder(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::EDIT_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }
        $folder = VideoFolder::with('album')->findOrFail($request->id);
        return view('maintenance.library-website.gallery.edit-video-folder', compact('folder'));
    }

    public function updateVideoFolder(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::EDIT_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }

        $folder = VideoFolder::findOrFail($request->id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (VideoFolder::where('album_id', $folder->album_id)->where('slug', $slug)->where('id', '!=', $folder->id)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $folder->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('maintenance.library-website.gallery.show-video-album', ['id' => $folder->album_id])
            ->with('toast-success', 'Video Folder updated successfully.');
    }

    public function destroyVideoFolder(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::DELETE_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }

        $folder = VideoFolder::findOrFail($request->id);
        $albumId = $folder->album_id;
        $folder->delete();

        return redirect()->route('maintenance.library-website.gallery.show-video-album', ['id' => $albumId])
            ->with('toast-success', 'Video Folder deleted successfully.');
    }

    // =========================================================
    // Video Items
    // =========================================================

    public function createVideoItem(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::ADD_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }
        $folder = VideoFolder::findOrFail($request->folder_id);
        return view('maintenance.library-website.gallery.create-video-item', compact('folder'));
    }

    public function storeVideoItem(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::ADD_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }

        $validator = Validator::make($request->all(), [
            'folder_id' => 'required|exists:video_folders,id',
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        VideoItem::create([
            'folder_id' => $request->folder_id,
            'title' => $request->title,
            'url' => $request->url,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('maintenance.library-website.gallery.show-video-folder', ['id' => $request->folder_id])
            ->with('toast-success', 'Video Item created successfully.');
    }

    public function editVideoItem(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::EDIT_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }
        $item = VideoItem::with('folder')->findOrFail($request->id);
        return view('maintenance.library-website.gallery.edit-video-item', compact('item'));
    }

    public function updateVideoItem(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::EDIT_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }

        $item = VideoItem::findOrFail($request->id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-warning', $validator->errors()->first())->withInput();
        }

        $item->update([
            'title' => $request->title,
            'url' => $request->url,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('maintenance.library-website.gallery.show-video-folder', ['id' => $item->folder_id])
            ->with('toast-success', 'Video Item updated successfully.');
    }

    public function destroyVideoItem(Request $request)
    {
        if (!$this->authAdmin->can(PermissionsEnum::DELETE_GALLERY)) {
            return redirect()->route('maintenance.library-website.gallery')->with('toast-error', 'Permission denied.');
        }

        $item = VideoItem::findOrFail($request->id);
        $folderId = $item->folder_id;
        $item->delete();

        return redirect()->route('maintenance.library-website.gallery.show-video-folder', ['id' => $folderId])
            ->with('toast-success', 'Video Item deleted successfully.');
    }
}
