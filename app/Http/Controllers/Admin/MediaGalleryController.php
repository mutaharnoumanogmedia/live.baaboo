<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryMedia;
use App\Models\LiveShow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MediaGalleryController extends Controller
{
    private const IMAGE_MAX_BYTES = 2 * 1024 * 1024;  // 2 MB
    private const VIDEO_MAX_BYTES = 100 * 1024 * 1024;  // 100 MB

    private const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const VIDEO_MIMES = ['video/mp4', 'video/webm', 'video/quicktime'];

    public function index()
    {
        $media = GalleryMedia::orderBy('created_at', 'desc')->paginate(24);

        return view('admin.media-gallery.index', compact('media'));
    }

    public function create()
    {
        return view('admin.media-gallery.create');
    }

    /**
     * Store single file from DropZone (AJAX). Validates size: images 2MB, videos 5MB.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $file = $request->file('file');
        $mime = $file->getMimeType();
        $size = $file->getSize();

        $type = null;
        if (in_array($mime, self::IMAGE_MIMES)) {
            $type = 'image';
            if ($size > self::IMAGE_MAX_BYTES) {
                throw ValidationException::withMessages([
                    'file' => ['Image must not exceed 2 MB.'],
                ]);
            }
        } elseif (in_array($mime, self::VIDEO_MIMES)) {
            $type = 'video';
            if ($size > self::VIDEO_MAX_BYTES) {
                throw ValidationException::withMessages([
                    'file' => ['Video must not exceed 100 MB.'],
                ]);
            }
        } else {
            throw ValidationException::withMessages([
                'file' => ['File must be an image (JPEG, PNG, GIF, WebP) or video (MP4, WebM).'],
            ]);
        }

        $path = $file->store('gallery-media/' . date('Y/m'), 'public');

        $media = GalleryMedia::create([
            'path' => $path,
            'type' => $type,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $size,
            'mime_type' => $mime,
            'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
        ]);

        return response()->json([
            'success' => true,
            'media' => [
                'id' => $media->id,
                'url' => $media->url,
                'type' => $media->type,
                'title' => $media->title,
            ],
        ]);
    }

    public function edit(GalleryMedia $media_gallery)
    {
        return view('admin.media-gallery.edit', ['media' => $media_gallery]);
    }

    public function update(Request $request, GalleryMedia $media_gallery)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
        ]);

        $media_gallery->update($validated);

        return redirect()
            ->route('admin.media-gallery.index')
            ->with('success', 'Media updated.');
    }

    public function destroy(GalleryMedia $media_gallery)
    {
        Storage::disk('public')->delete($media_gallery->path);
        $media_gallery->liveShows()->detach();
        $media_gallery->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('admin.media-gallery.index')
            ->with('success', 'Media deleted.');
    }

    public function attachToLiveShow(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'live_show_id' => 'required|exists:live_shows,id',
            'gallery_media_id' => 'required|exists:gallery_media,id',
        ]);

        $liveShow = LiveShow::findOrFail($validated['live_show_id']);
        $media = GalleryMedia::findOrFail($validated['gallery_media_id']);

        if ($liveShow->galleryMedia()->where('gallery_media.id', $media->id)->exists()) {
            return response()->json(['success' => true, 'message' => 'Already attached.']);
        }

        $maxOrder = $liveShow->galleryMedia()->max('live_show_gallery_media.sort_order') ?? 0;
        $liveShow->galleryMedia()->attach($media->id, ['sort_order' => $maxOrder + 1]);

        return response()->json(['success' => true, 'message' => 'Attached to live show.']);
    }

    public function detachFromLiveShow(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'live_show_id' => 'required|exists:live_shows,id',
            'gallery_media_id' => 'required|exists:gallery_media,id',
        ]);

        LiveShow::findOrFail($validated['live_show_id'])
            ->galleryMedia()
            ->detach($validated['gallery_media_id']);

        return response()->json(['success' => true, 'message' => 'Detached from live show.']);
    }

    /** Page to pick a live show to attach a gallery item to */
    public function attachShow(GalleryMedia $media_gallery)
    {
        $liveShows = LiveShow::orderBy('scheduled_at', 'desc')->get();

        return view('admin.media-gallery.attach-show', [
            'media' => $media_gallery,
            'liveShows' => $liveShows,
        ]);
    }

    /** Page on a live show to attach/detach gallery media */
    public function liveShowsAttachPage(LiveShow $live_show)
    {
        $liveShow = $live_show->load('galleryMedia');
        $allMedia = GalleryMedia::orderBy('created_at', 'desc')->get();

        return view('admin.media-gallery.attach-to-live-show', [
            'liveShow' => $liveShow,
            'allMedia' => $allMedia,
        ]);
    }
}
