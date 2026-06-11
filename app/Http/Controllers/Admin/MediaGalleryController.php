<?php

namespace App\Http\Controllers\Admin;

use App\Events\HideGalleryImageEvent;
use App\Events\ShowGalleryImageEvent;
use App\Http\Controllers\Controller;
use App\Models\GalleryMedia;
use App\Models\LiveShow;
use App\Models\LiveShowGalleryMedia;
use App\Models\LiveShowGalleryState;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaGalleryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:can-manage-media-gallery');
    }

    private const IMAGE_MAX_BYTES = 2 * 1024 * 1024;  // 2 MB

    private const VIDEO_MAX_BYTES = 250 * 1024 * 1024;  // 250 MB

    private const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    private const VIDEO_MIMES = ['video/mp4', 'video/webm', 'video/quicktime'];

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $type = $request->input('type');

        $query = GalleryMedia::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('original_name', 'like', "%{$search}%");
            });
        }

        if (in_array($type, ['image', 'video'], true)) {
            $query->where('type', $type);
        }

        $media = $query->orderByDesc('created_at')->paginate(24)->withQueryString();

        $counts = [
            'all' => GalleryMedia::count(),
            'image' => GalleryMedia::where('type', 'image')->count(),
            'video' => GalleryMedia::where('type', 'video')->count(),
        ];

        return view('admin.media-gallery.index', compact('media', 'counts'));
    }

    public function create()
    {
        return view('admin.media-gallery.create');
    }

    /**
     * Store single file from DropZone (AJAX). Validates size: images 2MB, videos 250MB.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimetypes:'.implode(',', array_merge(self::IMAGE_MIMES, self::VIDEO_MIMES)).'|max:'.(self::VIDEO_MAX_BYTES / 1024),
            'custom_name' => 'nullable|string|max:255',
            'thumbnail' => 'nullable|file|mimes:jpeg,jpg,png|max:2048',
            'total_seconds' => 'nullable|integer|min:0',
        ]);

        $file = $request->file('file');
        $mimeType = $file->getMimeType();
        $isVideo = Str::startsWith($mimeType, 'video/');

        $this->assertFileSizeAllowed($file, $isVideo);

        $originalName = $file->getClientOriginalName();
        $title = $request->filled('custom_name')
            ? $request->input('custom_name')
            : pathinfo($originalName, PATHINFO_FILENAME);

        // Video thumbnail: prefer client-captured JPEG/PNG, else FFmpeg
        $thumbnailFullUrl = $isVideo
            ? $this->storeVideoThumbnail($file, $request->file('thumbnail'))
            : null;

        // Upload main file (image or video) to S3
        $folder = $isVideo ? 'videos' : 'images';
        $filePathS3 = Storage::disk('s3')->putFile($folder, $file, 'public');

        if (! $filePathS3) {
            return response()->json([
                'success' => false,
                'message' => 'S3 upload failed. Check your AWS credentials and bucket permissions.',
            ], 500);
        }

        $media = GalleryMedia::create([
            'path' => Storage::disk('s3')->url($filePathS3),
            'type' => $isVideo ? 'video' : 'image',
            'original_name' => $originalName,
            'file_size' => $file->getSize(),
            'mime_type' => $mimeType,
            'title' => $title,
            'thumbnail' => $thumbnailFullUrl, // Null for images
            'total_seconds' => $isVideo ? $request->input('total_seconds') : null,
        ]);

        return response()->json([
            'success' => true,
            'media' => [
                'id' => $media->id,
                'url' => $media->url,
                'thumbnail_url' => $media->thumbnail,
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
            'file' => 'nullable|file|mimetypes:'.implode(',', array_merge(self::IMAGE_MIMES, self::VIDEO_MIMES)).'|max:'.(self::VIDEO_MAX_BYTES / 1024),
            'thumbnail' => 'nullable|file|mimes:jpeg,jpg,png|max:2048',
            'total_seconds' => 'nullable|integer|min:0',
        ]);

        $file = $request->file('file');

        if ($file) {
            $mimeType = $file->getMimeType();
            $isVideo = Str::startsWith($mimeType, 'video/');

            $this->assertFileSizeAllowed($file, $isVideo);

            $newThumbnailUrl = $isVideo
                ? $this->storeVideoThumbnail($file, $request->file('thumbnail'))
                : null;

            $folder = $isVideo ? 'videos' : 'images';
            $filePathS3 = Storage::disk('s3')->putFile($folder, $file, 'public');

            if (! $filePathS3) {
                return back()->withErrors(['file' => 'S3 upload failed. The previous file was kept.']);
            }

            // Remove the old objects only after the new upload succeeded
            $this->deleteFromS3ByUrl($media_gallery->path);
            $this->deleteFromS3ByUrl($media_gallery->thumbnail);

            $media_gallery->fill([
                'path' => Storage::disk('s3')->url($filePathS3),
                'type' => $isVideo ? 'video' : 'image',
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $mimeType,
                'thumbnail' => $newThumbnailUrl,
                'total_seconds' => $isVideo ? ($validated['total_seconds'] ?? null) : null,
            ]);
        }

        $media_gallery->title = $validated['title'] ?? $media_gallery->title;
        $media_gallery->save();

        return redirect()
            ->route('admin.media-gallery.index')
            ->with('success', 'Media updated.');
    }

    public function destroy(GalleryMedia $media_gallery)
    {
        $this->deleteFromS3ByUrl($media_gallery->path);
        $this->deleteFromS3ByUrl($media_gallery->thumbnail);

        $media_gallery->liveShows()->detach();
        $media_gallery->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('admin.media-gallery.index')
            ->with('success', 'Media deleted.');
    }

    /**
     * Enforce per-type size limits (images 2MB, videos 250MB).
     *
     * @throws ValidationException
     */
    private function assertFileSizeAllowed(\Illuminate\Http\UploadedFile $file, bool $isVideo): void
    {
        $maxBytes = $isVideo ? self::VIDEO_MAX_BYTES : self::IMAGE_MAX_BYTES;

        if ($file->getSize() > $maxBytes) {
            $limitMb = (int) ($maxBytes / (1024 * 1024));
            throw ValidationException::withMessages([
                'file' => [($isVideo ? 'Videos' : 'Images')." may not be larger than {$limitMb} MB."],
            ]);
        }
    }

    /**
     * Store a thumbnail for a video on S3: prefer the client-captured frame, fall back to FFmpeg.
     * Returns the public URL, or null if no thumbnail could be produced.
     */
    private function storeVideoThumbnail(\Illuminate\Http\UploadedFile $video, ?\Illuminate\Http\UploadedFile $clientThumb): ?string
    {
        $thumbS3Path = 'thumbnails/thumb_'.time().'_'.Str::random(5).'.jpg';

        if ($clientThumb && $clientThumb->isValid()) {
            try {
                Storage::disk('s3')->put($thumbS3Path, file_get_contents($clientThumb->getRealPath()), 'public');

                return Storage::disk('s3')->url($thumbS3Path);
            } catch (\Exception $e) {
                \Log::error('Error uploading client video thumbnail: '.$e->getMessage());
            }
        }

        $tempThumbPath = null;

        try {
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries' => '/usr/bin/ffmpeg',
                'ffprobe.binaries' => '/usr/bin/ffprobe',
            ]);

            $tempThumbPath = storage_path('app/'.basename($thumbS3Path));
            $ffmpeg->open($video->getRealPath())
                ->frame(TimeCode::fromSeconds(1))
                ->save($tempThumbPath);

            Storage::disk('s3')->put($thumbS3Path, file_get_contents($tempThumbPath), 'public');

            return Storage::disk('s3')->url($thumbS3Path);
        } catch (\Exception $e) {
            \Log::error('Error extracting video thumbnail: '.$e->getMessage());

            return null;
        } finally {
            if ($tempThumbPath && file_exists($tempThumbPath)) {
                unlink($tempThumbPath);
            }
        }
    }

    /**
     * Delete an S3 object given the public URL stored in the DB.
     */
    private function deleteFromS3ByUrl(?string $url): void
    {
        if (! $url) {
            return;
        }

        $key = ltrim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($key === '') {
            return;
        }

        try {
            Storage::disk('s3')->delete($key);
        } catch (\Exception $e) {
            \Log::error('Error deleting S3 object: '.$e->getMessage());
        }
    }

    public function attachToLiveShow(Request $request): JsonResponse
    {

        $validator = \Validator::make($request->all(), [
            'live_show_id' => 'required|exists:live_shows,id',
            'gallery_media_id' => 'required|exists:gallery_media,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

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

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'live_show_id' => 'required|exists:live_shows,id',
            'order' => 'required|array',
            'order.*' => 'integer|exists:gallery_media,id',
        ]);

        $liveShowId = $validated['live_show_id'];

        DB::transaction(function () use ($liveShowId, $validated) {
            foreach ($validated['order'] as $position => $mediaId) {
                LiveShowGalleryMedia::where('live_show_id', $liveShowId)
                    ->where('gallery_media_id', $mediaId)
                    ->update(['sort_order' => $position]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Order updated.']);
    }

    /** Page to pick a live show to attach a gallery item to */
    public function attachShow(GalleryMedia $media_gallery)
    {
        $liveShows = LiveShow::orderBy('scheduled_at', 'desc')->get();

        $attachedIds = $media_gallery->liveShows()->pluck('live_shows.id')->toArray();

        return view('admin.media-gallery.attach-show', [
            'media' => $media_gallery,
            'liveShows' => $liveShows,
            'attachedIds' => $attachedIds,
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

    /**
     * GET: Whether gallery media is currently shown, plus persisted details (late joiners / admin).
     */
    public function galleryStreamState(LiveShow $live_show): JsonResponse
    {
        $state = $live_show->galleryState;

        if (! $state || ! $state->is_visible || ! $state->url) {
            return response()->json([
                'showing' => false,
                'state' => null,
            ]);
        }

        return response()->json([
            'showing' => true,
            'state' => $this->galleryStateToArray($state),
        ]);
    }

    /**
     * POST: Show gallery media on stream (visible). Sets DB state + broadcasts.
     *
     * Body: gallery_media_id (required), video_duration_seconds (optional, for video).
     */
    public function galleryStreamShow(Request $request, LiveShow $live_show): JsonResponse
    {
        $validated = $request->validate([
            'gallery_media_id' => 'required|integer|exists:gallery_media,id',
            'video_duration_seconds' => 'nullable|integer|min:0|max:86400',
        ]);

        $media = GalleryMedia::findOrFail($validated['gallery_media_id']);

        if (! $live_show->galleryMedia()->where('gallery_media.id', $media->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This media is not attached to this live show.',
            ], 422);
        }

        $state = DB::transaction(function () use ($live_show, $media, $validated) {
            $playbackStartedAt = $media->isVideo() ? now() : null;
            $duration = $media->isVideo()
                ? ($validated['video_duration_seconds'] ?? null)
                : null;

            return LiveShowGalleryState::updateOrCreate(
                ['live_show_id' => $live_show->id],
                [
                    'is_visible' => true,
                    'gallery_media_id' => $media->id,
                    'url' => $media->url,
                    'media_type' => $media->type,
                    'playback_started_at' => $playbackStartedAt,
                    'video_duration_seconds' => $duration,
                ]
            );
        });

        $this->dispatchShowGalleryEvent($live_show, $state);

        return response()->json([
            'success' => true,
            'message' => 'Gallery media is visible on stream.',
            'state' => $this->galleryStateToArray($state->fresh()),
        ]);
    }

    /**
     * PATCH: Update streaming state — change media and/or video duration (seconds), optional playback restart for video.
     *
     * Body (at least one): gallery_media_id, video_duration_seconds, restart_playback (bool).
     */
    public function galleryStreamUpdate(Request $request, LiveShow $live_show): JsonResponse
    {
        $validated = $request->validate([
            'gallery_media_id' => 'nullable|integer|exists:gallery_media,id',
            'video_duration_seconds' => 'nullable|integer|min:0|max:86400',
            'restart_playback' => 'nullable|boolean',
        ]);

        if (
            empty($validated['gallery_media_id'])
            && ! array_key_exists('video_duration_seconds', $validated)
            && ! array_key_exists('restart_playback', $validated)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Provide gallery_media_id, video_duration_seconds, and/or restart_playback.',
            ], 422);
        }

        $state = $live_show->galleryState;

        if (! $state) {
            return response()->json([
                'success' => false,
                'message' => 'No gallery stream state exists for this live show. Use gallery-stream/show first.',
            ], 422);
        }

        $restartPlayback = $validated['restart_playback'] ?? false;

        DB::transaction(function () use ($live_show, $state, $validated, $restartPlayback) {
            if (! empty($validated['gallery_media_id'])) {
                $media = GalleryMedia::findOrFail($validated['gallery_media_id']);
                if (! $live_show->galleryMedia()->where('gallery_media.id', $media->id)->exists()) {
                    throw ValidationException::withMessages([
                        'gallery_media_id' => ['This media is not attached to this live show.'],
                    ]);
                }

                $state->gallery_media_id = $media->id;
                $state->url = $media->url;
                $state->media_type = $media->type;
                if ($media->isVideo()) {
                    $state->playback_started_at = now();
                    if (array_key_exists('video_duration_seconds', $validated)) {
                        $state->video_duration_seconds = $validated['video_duration_seconds'];
                    }
                } else {
                    $state->playback_started_at = null;
                    $state->video_duration_seconds = null;
                }
            } else {
                if (array_key_exists('video_duration_seconds', $validated)) {
                    if ($state->media_type !== 'video') {
                        throw ValidationException::withMessages([
                            'video_duration_seconds' => ['video_duration_seconds applies only when current media is a video.'],
                        ]);
                    }
                    $state->video_duration_seconds = $validated['video_duration_seconds'];
                }
                if ($restartPlayback && $state->media_type === 'video') {
                    $state->playback_started_at = now();
                }
            }

            $state->is_visible = true;
            $state->save();
        });

        $state->refresh();

        $this->dispatchShowGalleryEvent($live_show, $state);

        return response()->json([
            'success' => true,
            'message' => 'Gallery stream state updated.',
            'state' => $this->galleryStateToArray($state),
        ]);
    }

    /**
     * POST: Set whether gallery overlay is visible (hide, or re-show last media without changing timing when possible).
     *
     * Body: is_visible (required boolean).
     */
    public function galleryStreamVisibility(Request $request, LiveShow $live_show): JsonResponse
    {
        $validated = $request->validate([
            'is_visible' => 'required|boolean',
        ]);

        $state = $live_show->galleryState;

        if ($validated['is_visible'] === false) {
            if ($state) {
                $state->is_visible = false;
                $state->save();
            }
            HideGalleryImageEvent::dispatch((string) $live_show->id);

            return response()->json([
                'success' => true,
                'message' => 'Gallery overlay hidden.',
                'state' => $state ? $this->galleryStateToArray($state->fresh()) : null,
            ]);
        }

        if (! $state || ! $state->gallery_media_id || ! $state->url) {
            return response()->json([
                'success' => false,
                'message' => 'No gallery media to show. Use gallery-stream/show first.',
            ], 422);
        }

        $state->is_visible = true;
        $state->save();

        $this->dispatchShowGalleryEvent($live_show, $state);

        return response()->json([
            'success' => true,
            'message' => 'Gallery overlay visible.',
            'state' => $this->galleryStateToArray($state->fresh()),
        ]);
    }

    private function galleryStateToArray(LiveShowGalleryState $state): array
    {
        return [
            'is_visible' => $state->is_visible,
            'gallery_media_id' => $state->gallery_media_id,
            'url' => $state->url,
            'media_type' => $state->media_type,
            'playback_started_at' => $state->playback_started_at?->toIso8601String(),
            'video_duration_seconds' => $state->video_duration_seconds,
            'updated_at' => $state->updated_at?->toIso8601String(),
        ];
    }

    private function dispatchShowGalleryEvent(LiveShow $liveShow, LiveShowGalleryState $state): void
    {
        $playbackIso = $state->playback_started_at?->toIso8601String();
        ShowGalleryImageEvent::dispatch(
            (string) $liveShow->id,
            $state->url,
            $state->media_type,
            $playbackIso,
            $state->video_duration_seconds,
            $state->galleryMedia->thumbnail ?? null
        );
    }

    public function items($live_show): JsonResponse
    {
        $live_show = LiveShow::findOrFail($live_show);

        $media = $live_show->galleryMedia;

        return response()->json([
            'success' => true,
            'media' => $media,
        ]);
    }

    public function allMedia(): JsonResponse
    {
        $media = GalleryMedia::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'media' => $media,
        ]);
    }
}
