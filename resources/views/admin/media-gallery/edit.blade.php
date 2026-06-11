<x-app-dashboard-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 py-3 mb-0 leading-tight">
                {{ __('Edit Media') }}
            </h2>
            <a href="{{ route('admin.media-gallery.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Gallery
            </a>
        </div>
    </x-slot>

    <style>
        .edit-preview-box {
            background: #0f0e1c;
            border-radius: 12px 12px 0 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 220px;
            overflow: hidden;
        }

        .edit-preview-box img,
        .edit-preview-box video {
            max-width: 100%;
            max-height: 320px;
            display: block;
        }

        .media-details-list {
            font-size: 0.875rem;
        }

        .media-details-list dt {
            color: #767a96;
            font-weight: 500;
        }

        .media-details-list dd {
            margin-bottom: 0.6rem;
            word-break: break-all;
        }
    </style>

    <div class="container-fluid py-4">

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            {{-- Preview + details --}}
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="edit-preview-box" id="media-preview-box">
                        @if ($media->isImage())
                            <img id="media-preview" src="{{ $media->url }}" alt="{{ $media->title }}">
                        @else
                            <video id="media-preview" src="{{ $media->url }}" controls
                                @if ($media->thumbnail) poster="{{ $media->thumbnail }}" @endif></video>
                        @endif
                    </div>
                    <div class="card-body">
                        <span class="badge {{ $media->isVideo() ? 'text-bg-success' : 'text-bg-primary' }} mb-3">
                            <i class="bi {{ $media->isVideo() ? 'bi-film' : 'bi-image' }} me-1"></i>{{ ucfirst($media->type) }}
                        </span>

                        <dl class="media-details-list mb-0">
                            <dt>Original file</dt>
                            <dd>{{ $media->original_name }}</dd>

                            <dt>Size</dt>
                            <dd>{{ number_format($media->file_size / (1024 * 1024), 2) }} MB <span class="text-muted">({{ $media->mime_type }})</span></dd>

                            @if ($media->isVideo() && $media->total_seconds)
                                <dt>Duration</dt>
                                <dd>{{ gmdate($media->total_seconds >= 3600 ? 'G:i:s' : 'i:s', $media->total_seconds) }}</dd>
                            @endif

                            <dt>Uploaded</dt>
                            <dd>{{ $media->created_at->format('M j, Y \a\t H:i') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Edit form --}}
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <i class="bi bi-pencil-square me-1"></i> Details
                    </div>
                    <div class="card-body">
                        <form id="edit-media-form" method="POST"
                            action="{{ route('admin.media-gallery.update', $media) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Title</label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title', $media->title) }}" placeholder="Optional title">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Replace File</label>
                                <input type="file" name="file" id="file-input"
                                    class="form-control @error('file') is-invalid @enderror"
                                    accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime">
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Leave empty to keep the current file. Images max 2 MB, videos max 250 MB.
                                    Replacing the file deletes the old one permanently.
                                </small>
                            </div>

                            {{-- Auto-filled for video replacements (client-side capture) --}}
                            <input type="file" name="thumbnail" id="thumbnail-input" class="d-none" accept="image/jpeg">
                            <input type="hidden" name="total_seconds" id="total-seconds-input" value="">

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary" id="save-btn">
                                    <span class="spinner-border spinner-border-sm me-1 d-none" id="save-spinner"></span>
                                    <i class="bi bi-check-lg me-1" id="save-icon"></i>Save Changes
                                </button>
                                <a href="{{ route('admin.media-gallery.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('edit-media-form');
                const saveBtn = document.getElementById('save-btn');
                const saveSpinner = document.getElementById('save-spinner');
                const saveIcon = document.getElementById('save-icon');
                const fileInput = document.getElementById('file-input');
                const thumbInput = document.getElementById('thumbnail-input');
                const totalSecondsInput = document.getElementById('total-seconds-input');
                const previewBox = document.getElementById('media-preview-box');

                // Live preview of the replacement file
                fileInput.addEventListener('change', function() {
                    const file = this.files[0];
                    thumbInput.value = '';
                    totalSecondsInput.value = '';

                    if (!file) return;

                    const url = URL.createObjectURL(file);
                    previewBox.innerHTML = '';

                    if (file.type.startsWith('video/')) {
                        const video = document.createElement('video');
                        video.src = url;
                        video.controls = true;
                        video.muted = true;
                        previewBox.appendChild(video);

                        // Capture duration + a frame for the server-side thumbnail
                        video.addEventListener('loadedmetadata', function() {
                            if (Number.isFinite(video.duration) && video.duration > 0) {
                                totalSecondsInput.value = Math.round(video.duration);
                            }
                            try {
                                video.currentTime = Math.min(1, Math.max(0, video.duration - 0.05));
                            } catch (e) { /* thumbnail capture is best-effort */ }
                        }, { once: true });

                        video.addEventListener('seeked', function() {
                            try {
                                const canvas = document.createElement('canvas');
                                const maxW = 1280;
                                let cw = video.videoWidth, ch = video.videoHeight;
                                if (!cw || !ch) return;
                                if (cw > maxW) { ch = Math.round(ch * (maxW / cw)); cw = maxW; }
                                canvas.width = cw;
                                canvas.height = ch;
                                canvas.getContext('2d').drawImage(video, 0, 0, cw, ch);
                                canvas.toBlob(function(blob) {
                                    if (!blob) return;
                                    const dt = new DataTransfer();
                                    dt.items.add(new File([blob], 'thumbnail.jpg', { type: 'image/jpeg' }));
                                    thumbInput.files = dt.files;
                                }, 'image/jpeg', 0.88);
                            } catch (e) { /* thumbnail capture is best-effort */ }
                        }, { once: true });
                    } else {
                        const img = document.createElement('img');
                        img.src = url;
                        previewBox.appendChild(img);
                    }
                });

                form.addEventListener('submit', function() {
                    saveBtn.disabled = true;
                    saveSpinner.classList.remove('d-none');
                    saveIcon.classList.add('d-none');
                });
            });
        </script>
    @endpush
</x-app-dashboard-layout>
