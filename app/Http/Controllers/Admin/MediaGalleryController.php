<?php

namespace App\Http\Controllers\Admin;

use App\Events\HideGalleryImageEvent;
use App\Events\ShowGalleryImageEvent;
use App\Http\Controllers\Controller;
use App\Models\GalleryMedia;
use App\Models\LiveShow;
use App\Models\LiveShowEndMedia;
use App\Models\LiveShowGalleryMedia;
use App\Models\LiveShowGalleryState;
use App\Models\LiveShowQuiz;
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
        LiveShowGalleryMedia::where('live_show_id', $validated['live_show_id'])
            ->where('gallery_media_id', $validated['gallery_media_id'])
            ->delete();

        return response()->json(['success' => true, 'message' => 'Detached from live show.']);
    }

    /**
     * Attach a gallery media item so it plays *before* a specific quiz question.
     */
    public function attachToQuestion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'live_show_id' => 'required|exists:live_shows,id',
            'quiz_id' => 'required|exists:live_show_quizzes,id',
            'gallery_media_id' => 'required|exists:gallery_media,id',
        ]);

        $liveShow = LiveShow::findOrFail($validated['live_show_id']);
        $quiz = $liveShow->quizzes()->findOrFail($validated['quiz_id']);
        $media = GalleryMedia::findOrFail($validated['gallery_media_id']);

        if ($quiz->questionMedia()->where('gallery_media.id', $media->id)->exists()) {
            return response()->json(['success' => true, 'message' => 'Already attached before this question.']);
        }

        $maxOrder = $quiz->questionMedia()->max('live_show_gallery_media.sort_order') ?? 0;
        $quiz->questionMedia()->attach($media->id, [
            'live_show_id' => $liveShow->id,
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json(['success' => true, 'message' => 'Attached before question.']);
    }

    /**
     * Detach a gallery media item that was set to play before a quiz question.
     */
    public function detachFromQuestion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quiz_id' => 'required|exists:live_show_quizzes,id',
            'gallery_media_id' => 'required|exists:gallery_media,id',
        ]);

        $quiz = LiveShowQuiz::findOrFail($validated['quiz_id']);
        $quiz->questionMedia()->detach($validated['gallery_media_id']);

        return response()->json(['success' => true, 'message' => 'Detached from question.']);
    }

    /**
     * Attach a gallery media item so it plays after all quiz questions.
     */
    public function attachToEnd(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'live_show_id' => 'required|exists:live_shows,id',
            'gallery_media_id' => 'required|exists:gallery_media,id',
        ]);

        $liveShow = LiveShow::findOrFail($validated['live_show_id']);
        $media = GalleryMedia::findOrFail($validated['gallery_media_id']);

        if ($liveShow->endMedia()->where('gallery_media.id', $media->id)->exists()) {
            return response()->json(['success' => true, 'message' => 'Already attached at end of show.']);
        }

        $maxOrder = LiveShowEndMedia::where('live_show_id', $liveShow->id)->max('sort_order') ?? 0;
        $liveShow->endMedia()->attach($media->id, ['sort_order' => $maxOrder + 1]);

        return response()->json(['success' => true, 'message' => 'Attached at end of questions.']);
    }

    /**
     * Detach a gallery media item from the end-of-show slot.
     */
    public function detachFromEnd(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'live_show_id' => 'required|exists:live_shows,id',
            'gallery_media_id' => 'required|exists:gallery_media,id',
        ]);

        LiveShow::findOrFail($validated['live_show_id'])
            ->endMedia()
            ->detach($validated['gallery_media_id']);

        return response()->json(['success' => true, 'message' => 'Detached from end of show.']);
    }

    public function reorderEnd(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'live_show_id' => 'required|exists:live_shows,id',
            'order' => 'required|array',
            'order.*' => 'integer|exists:gallery_media,id',
        ]);

        $liveShowId = $validated['live_show_id'];

        DB::transaction(function () use ($liveShowId, $validated) {
            foreach ($validated['order'] as $position => $mediaId) {
                LiveShowEndMedia::where('live_show_id', $liveShowId)
                    ->where('gallery_media_id', $mediaId)
                    ->update(['sort_order' => $position]);
            }
        });

        return response()->json(['success' => true, 'message' => 'End media order updated.']);
    }

    public function reorderQuestionMedia(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quiz_id' => 'required|exists:live_show_quizzes,id',
            'order' => 'required|array',
            'order.*' => 'integer|exists:gallery_media,id',
        ]);

        $quizId = (int) $validated['quiz_id'];

        DB::transaction(function () use ($quizId, $validated) {
            foreach ($validated['order'] as $position => $mediaId) {
                LiveShowGalleryMedia::where('before_question', $quizId)
                    ->where('gallery_media_id', $mediaId)
                    ->update(['sort_order' => $position]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Question media order updated.']);
    }

    /**
     * Move media between "before question" and "at end" placements in the show flow.
     */
    public function moveFlowMedia(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'live_show_id' => 'required|exists:live_shows,id',
            'gallery_media_id' => 'required|exists:gallery_media,id',
            'from_type' => 'required|in:question,end',
            'from_quiz_id' => 'nullable|integer|exists:live_show_quizzes,id',
            'to_type' => 'required|in:question,end',
            'to_quiz_id' => 'nullable|integer|exists:live_show_quizzes,id',
        ]);

        if ($validated['from_type'] === 'question' && empty($validated['from_quiz_id'])) {
            return response()->json(['success' => false, 'message' => 'from_quiz_id is required.'], 422);
        }
        if ($validated['to_type'] === 'question' && empty($validated['to_quiz_id'])) {
            return response()->json(['success' => false, 'message' => 'to_quiz_id is required.'], 422);
        }

        $liveShow = LiveShow::findOrFail($validated['live_show_id']);
        $mediaId = (int) $validated['gallery_media_id'];

        if ($validated['from_type'] === $validated['to_type']) {
            if ($validated['from_type'] === 'end') {
                return response()->json(['success' => true, 'message' => 'Already at end.']);
            }
            if ((int) $validated['from_quiz_id'] === (int) $validated['to_quiz_id']) {
                return response()->json(['success' => true, 'message' => 'Already before this question.']);
            }
        }

        DB::transaction(function () use ($validated, $liveShow, $mediaId) {
            if ($validated['from_type'] === 'question') {
                LiveShowQuiz::findOrFail($validated['from_quiz_id'])
                    ->questionMedia()
                    ->detach($mediaId);
            } else {
                $liveShow->endMedia()->detach($mediaId);
            }

            if ($validated['to_type'] === 'question') {
                $toQuiz = $liveShow->quizzes()->findOrFail($validated['to_quiz_id']);
                if (! $toQuiz->questionMedia()->where('gallery_media.id', $mediaId)->exists()) {
                    $maxOrder = $toQuiz->questionMedia()->max('live_show_gallery_media.sort_order') ?? 0;
                    $toQuiz->questionMedia()->attach($mediaId, [
                        'live_show_id' => $liveShow->id,
                        'sort_order' => $maxOrder + 1,
                    ]);
                }
            } elseif (! $liveShow->endMedia()->where('gallery_media.id', $mediaId)->exists()) {
                $maxOrder = LiveShowEndMedia::where('live_show_id', $liveShow->id)->max('sort_order') ?? 0;
                $liveShow->endMedia()->attach($mediaId, ['sort_order' => $maxOrder + 1]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Placement updated.']);
    }

    /** Show flow order: questions + before-question media + end media. */
    public function flowOrder($live_show): JsonResponse
    {
        $liveShow = LiveShow::with([
            'quizzes' => fn ($q) => $q->orderBy('id'),
            'quizzes.questionMedia',
            'endMedia',
        ])->findOrFail($live_show);

        return response()->json([
            'success' => true,
            ...$this->buildShowFlowItems($liveShow),
        ]);
    }

    /**
     * @return array{main: array<int, array<string, mixed>>, end: array<int, array<string, mixed>>, questions: array<int, array<string, mixed>>}
     */
    private function buildShowFlowItems(LiveShow $liveShow): array
    {
        $main = [];
        $row = 0;

        foreach ($liveShow->quizzes as $qIndex => $quiz) {
            foreach ($quiz->questionMedia as $media) {
                $row++;
                $main[] = [
                    'row' => $row,
                    'type' => 'media',
                    'attachment_type' => 'question',
                    'attachment_id' => (int) $media->pivot->id,
                    'gallery_media_id' => (int) $media->id,
                    'quiz_id' => (int) $quiz->id,
                    'quiz_num' => $qIndex + 1,
                    'title' => $media->title ?? $media->original_name,
                    'media_type' => $media->type,
                    'thumbnail' => $media->thumbnail ?? $media->path,
                    'placement_label' => 'Before Question '.($qIndex + 1),
                    'media_played' => (bool) ($media->pivot->media_played ?? false),
                ];
            }
            $row++;
            $main[] = [
                'row' => $row,
                'type' => 'question',
                'quiz_id' => (int) $quiz->id,
                'quiz_num' => $qIndex + 1,
                'question' => $quiz->question,
                'placement_label' => 'Question '.($qIndex + 1),
                'has_shown' => (bool) $quiz->has_shown,
            ];
        }

        $end = [];
        $endRow = 0;
        foreach ($liveShow->endMedia as $media) {
            $endRow++;
            $end[] = [
                'row' => $endRow,
                'type' => 'media',
                'attachment_type' => 'end',
                'attachment_id' => (int) $media->pivot->id,
                'gallery_media_id' => (int) $media->id,
                'title' => $media->title ?? $media->original_name,
                'media_type' => $media->type,
                'thumbnail' => $media->thumbnail ?? $media->path,
                'placement_label' => 'After all questions',
                'media_played' => (bool) ($media->pivot->media_played ?? false),
            ];
        }

        $questions = $liveShow->quizzes->values()->map(fn ($q, $i) => [
            'id' => (int) $q->id,
            'label' => 'Q'.($i + 1),
            'num' => $i + 1,
        ])->all();

        return compact('main', 'end', 'questions');
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
        $liveShows = LiveShow::with(['quizzes' => function ($q) {
            $q->orderBy('id');
        }])->orderBy('scheduled_at', 'desc')->get();

        $attachedIds = $media_gallery->liveShows()->pluck('live_shows.id')->toArray();

        // Quiz ids where THIS media is already attached before the question.
        $attachedQuestionIds = LiveShowGalleryMedia::where('gallery_media_id', $media_gallery->id)
            ->whereNotNull('before_question')
            ->pluck('before_question')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        $attachedEndShowIds = LiveShowEndMedia::where('gallery_media_id', $media_gallery->id)
            ->pluck('live_show_id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        return view('admin.media-gallery.attach-show', [
            'media' => $media_gallery,
            'liveShows' => $liveShows,
            'attachedIds' => $attachedIds,
            'attachedQuestionIds' => $attachedQuestionIds,
            'attachedEndShowIds' => $attachedEndShowIds,
        ]);
    }

    /** Page on a live show to attach/detach gallery media */
    public function liveShowsAttachPage(LiveShow $live_show)
    {
        $liveShow = $live_show->load(['galleryMedia', 'endMedia', 'quizzes' => function ($q) {
            $q->orderBy('id');
        }]);
        $allMedia = GalleryMedia::orderBy('created_at', 'desc')->get();

        // Map of gallery_media_id => [quiz_id, ...] for question-level attachments.
        $questionAttachments = LiveShowGalleryMedia::where('live_show_id', $liveShow->id)
            ->whereNotNull('before_question')
            ->get(['gallery_media_id', 'before_question'])
            ->groupBy('gallery_media_id')
            ->map(fn ($rows) => $rows->pluck('before_question')->map(fn ($id) => (int) $id)->values()->all())
            ->toArray();

        return view('admin.media-gallery.attach-to-live-show', [
            'liveShow' => $liveShow,
            'allMedia' => $allMedia,
            'questionAttachments' => $questionAttachments,
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

        if (! $live_show->isGalleryMediaAttached($media->id)) {
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
                if (! $live_show->isGalleryMediaAttached($media->id)) {
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
        $liveShow = LiveShow::with([
            'galleryMedia',
            'endMedia',
            'quizzes' => fn ($q) => $q->orderBy('id'),
            'quizzes.questionMedia',
        ])->findOrFail($live_show);

        $items = collect();

        foreach ($liveShow->galleryMedia as $media) {
            $items->push([
                'id' => $media->id,
                'attachment_id' => (int) $media->pivot->id,
                'attachment_type' => 'show',
                'attachment_label' => 'Show-wide',
                'media_played' => (bool) ($media->pivot->media_played ?? false),
                'type' => $media->type,
                'title' => $media->title,
                'original_name' => $media->original_name,
                'url' => $media->url,
                'path' => $media->path,
                'thumbnail' => $media->thumbnail,
                'total_seconds' => $media->total_seconds,
                'mime_type' => $media->mime_type,
                'file_size' => $media->file_size,
                'sort_order' => (int) ($media->pivot->sort_order ?? 0),
                'is_image' => $media->isImage(),
                'play_with_live' => (bool) ($media->pivot->play_with_live ?? false),
            ]);
        }

        foreach ($liveShow->quizzes as $qIndex => $quiz) {
            foreach ($quiz->questionMedia as $media) {
                $items->push([
                    'id' => $media->id,
                    'attachment_id' => (int) $media->pivot->id,
                    'attachment_type' => 'question',
                    'attachment_label' => 'Before Q'.($qIndex + 1),
                    'quiz_id' => (int) $quiz->id,
                    'media_played' => (bool) ($media->pivot->media_played ?? false),
                    'type' => $media->type,
                    'title' => $media->title,
                    'original_name' => $media->original_name,
                    'url' => $media->url,
                    'path' => $media->path,
                    'thumbnail' => $media->thumbnail,
                    'total_seconds' => $media->total_seconds,
                    'mime_type' => $media->mime_type,
                    'file_size' => $media->file_size,
                    'sort_order' => 1000 + ($qIndex * 100) + (int) ($media->pivot->sort_order ?? 0),
                    'is_image' => $media->isImage(),
                    'play_with_live' => false,
                ]);
            }
        }

        foreach ($liveShow->endMedia as $media) {
            $items->push([
                'id' => $media->id,
                'attachment_id' => (int) $media->pivot->id,
                'attachment_type' => 'end',
                'attachment_label' => 'After all questions',
                'media_played' => (bool) ($media->pivot->media_played ?? false),
                'type' => $media->type,
                'title' => $media->title,
                'original_name' => $media->original_name,
                'url' => $media->url,
                'path' => $media->path,
                'thumbnail' => $media->thumbnail,
                'total_seconds' => $media->total_seconds,
                'mime_type' => $media->mime_type,
                'file_size' => $media->file_size,
                'sort_order' => 200000 + (int) ($media->pivot->sort_order ?? 0),
                'is_image' => $media->isImage(),
                'play_with_live' => false,
            ]);
        }

        $sorted = $items->sortBy('sort_order')->values();

        return response()->json([
            'success' => true,
            'media' => $sorted,
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
