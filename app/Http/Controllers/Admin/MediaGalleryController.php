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

    private const VIDEO_MAX_BYTES = 250 * 1024 * 1024;  // 100 MB

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
        // 1. Validation for both Images and Videos
        $request->validate([
            'file' => 'required|file|mimetypes:video/mp4,video/mpeg,video/quicktime,image/jpeg,image/png,image/webp|max:204800', // 200 MB limit
            'custom_name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $mimeType = $file->getMimeType();
        $isVideo = Str::startsWith($mimeType, 'video/');

        $originalName = $file->getClientOriginalName();
        $title = $request->input('custom_name') ?? pathinfo($originalName, PATHINFO_FILENAME);
        $fileSize = $file->getSize();

        $thumbnailFullUrl = null;
        $tempThumbPath = null;

        // 2. Process Video specifically (FFmpeg)
        if ($isVideo) {
            try {
                $ffmpeg = FFMpeg::create([
                    'ffmpeg.binaries' => '/usr/bin/ffmpeg',
                    'ffprobe.binaries' => '/usr/bin/ffprobe',
                ]);

                $video = $ffmpeg->open($file->getRealPath());
                $thumbnailName = 'thumb_'.time().'_'.Str::random(5).'.jpg';
                $tempThumbPath = storage_path('app/public/'.$thumbnailName);

                // Extract frame at 1 second
                $video->frame(TimeCode::fromSeconds(1))->save($tempThumbPath);

                // Upload Thumbnail to S3
                $thumbS3Path = 'thumbnails/'.$thumbnailName;
                Storage::disk('s3')->put($thumbS3Path, file_get_contents($tempThumbPath), 'public');
                $thumbnailFullUrl = Storage::disk('s3')->url($thumbS3Path);
            } catch (\Exception $e) {
                \Log::error('Error extracting thumbnail: '.$e->getMessage());
            }
        }

        // 3. Upload Main File (Image or Video) to S3
        $folder = $isVideo ? 'videos' : 'images';
        $filePathS3 = Storage::disk('s3')->putFile($folder, $file, 'public');

        // 3. Check if the upload actually worked before getting the URL
        if (! $filePathS3) {
            return response()->json([
                'success' => false,
                'message' => 'S3 Upload failed. Check your AWS credentials and Bucket permissions. '.$filePathS3,
                'error' => $filePathS3,
            ], 500);
        }

        $fullFileUrl = Storage::disk('s3')->url($filePathS3);

        // 4. Save to Database
        $media = GalleryMedia::create([
            'path' => $fullFileUrl,
            'type' => $isVideo ? 'video' : 'image',
            'original_name' => $originalName,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'title' => $title,
            'thumbnail' => $thumbnailFullUrl, // Will be null for images
        ]);

        // 5. Cleanup temp thumbnail if it exists
        if ($tempThumbPath && file_exists($tempThumbPath)) {
            unlink($tempThumbPath);
        }

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

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'live_show_id' => 'required|exists:live_shows,id',
            'order'        => 'required|array',
            'order.*'      => 'integer|exists:gallery_media,id',
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
            $state->video_duration_seconds
        );
    }

    public function items( $live_show): JsonResponse
    {
        $live_show = LiveShow::findOrFail($live_show);
        
        $media = $live_show->galleryMediaItems;

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
