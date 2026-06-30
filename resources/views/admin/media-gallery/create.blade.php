<x-app-dashboard-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 py-3 mb-0 leading-tight">
                {{ __('Upload to Media Gallery') }}
            </h2>
            <a href="{{ route('admin.media-gallery.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Gallery
            </a>
        </div>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-body">

                <div class="d-flex flex-wrap gap-3 mb-3">
                    <span class="badge rounded-pill text-bg-light border">
                        <i class="bi bi-image me-1 text-primary"></i> Images: max 2 MB (JPEG, PNG, GIF, WebP)
                    </span>
                    <span class="badge rounded-pill text-bg-light border">
                        <i class="bi bi-film me-1 text-success"></i> Videos: max 250 MB (MP4, WebM, MOV)
                    </span>
                    <span class="badge rounded-pill text-bg-light border">
                        <i class="bi bi-lightning-charge me-1 text-warning"></i> Up to 5 files upload in parallel
                    </span>
                </div>

                <form action="{{ route('admin.media-gallery.upload') }}" class="dropzone" id="gallery-dropzone">
                    @csrf
                    <div class="dz-message" data-dz-message>
                        <i class="bi bi-cloud-arrow-up dz-hero-icon"></i>
                        <h5 class="mb-1">Drag &amp; drop files here</h5>
                        <p class="text-muted mb-0">or click to browse your computer</p>
                    </div>
                </form>

                {{-- Status / progress bar --}}
                <div id="upload-status" class="d-none mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted" id="upload-status-text"></small>
                        <small class="text-muted" id="upload-status-count"></small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div id="upload-progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>

                <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
                    <button type="button" class="btn btn-primary" id="upload-all-btn" disabled>
                        <i class="bi bi-cloud-upload me-1"></i> Upload All
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="clear-queue-btn" disabled>
                        <i class="bi bi-x-circle me-1"></i> Clear Queue
                    </button>
                    <a href="{{ route('admin.media-gallery.index') }}" class="btn btn-success d-none ms-auto" id="done-btn">
                        <i class="bi bi-check2-circle me-1"></i> Done — Go to Gallery
                    </a>
                </div>

            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
        <style>
            #gallery-dropzone {
                border: 2px dashed #c7c9e2;
                border-radius: 14px;
                background: #fafaff;
                min-height: 220px;
                transition: border-color 0.15s, background 0.15s;
            }

            #gallery-dropzone:hover,
            #gallery-dropzone.dz-drag-hover {
                border-color: #8e3be0;
                background: #f4eeff;
            }

            #gallery-dropzone .dz-message {
                margin: 2.5rem 0;
            }

            .dz-hero-icon {
                font-size: 3rem;
                color: #8e3be0;
                display: block;
                margin-bottom: 0.5rem;
            }

            #gallery-dropzone .dz-preview {
                border-radius: 12px;
                overflow: visible;
            }

            #gallery-dropzone .dz-preview .dz-image {
                border-radius: 10px;
            }

            .dz-filename-input {
                margin-top: 0.5rem;
                width: 100%;
                display: block;
                font-size: 0.85rem;
                padding: 0.3rem 0.45rem;
                border: 1px solid #d1d5db;
                border-radius: 6px;
            }

            .dz-filename-input:focus {
                outline: none;
                border-color: #8e3be0;
                box-shadow: 0 0 0 2px rgba(142, 59, 224, 0.15);
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
        <script>
            Dropzone.autoDiscover = false;

            /**
             * Capture one frame from a video File as a JPEG Blob (for upload as `thumbnail`).
             * Returns null if the browser cannot decode the file.
             */
            function captureVideoFrameAsJpegBlob(file, timeSeconds) {
                return new Promise((resolve) => {
                    const video = document.createElement('video');
                    video.muted = true;
                    video.playsInline = true;
                    video.setAttribute('playsinline', '');
                    video.preload = 'metadata';
                    const url = URL.createObjectURL(file);
                    let settled = false;

                    const finish = (blob) => {
                        if (settled) return;
                        settled = true;
                        URL.revokeObjectURL(url);
                        video.remove();
                        resolve(blob);
                    };

                    video.onerror = () => finish(null);

                    video.onloadedmetadata = () => {
                        const dur = video.duration;
                        let t = timeSeconds;
                        if (Number.isFinite(dur) && dur > 0) {
                            t = Math.min(Math.max(timeSeconds, 0), Math.max(0, dur - 0.05));
                        }
                        try {
                            video.currentTime = t;
                        } catch (e) {
                            finish(null);
                        }
                    };

                    video.onseeked = () => {
                        if (settled) return;
                        requestAnimationFrame(() => {
                            if (settled) return;
                            try {
                                const w = video.videoWidth;
                                const h = video.videoHeight;
                                if (!w || !h) {
                                    finish(null);
                                    return;
                                }
                                const maxW = 1280;
                                let cw = w;
                                let ch = h;
                                if (w > maxW) {
                                    cw = maxW;
                                    ch = Math.round(h * (maxW / w));
                                }
                                const canvas = document.createElement('canvas');
                                canvas.width = cw;
                                canvas.height = ch;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(video, 0, 0, cw, ch);
                                canvas.toBlob((blob) => finish(blob || null), 'image/jpeg', 0.88);
                            } catch (e) {
                                finish(null);
                            }
                        });
                    };

                    video.src = url;
                });
            }

            /**
             * Read the duration (in seconds) from a video File via metadata.
             * Resolves to an integer number of seconds, or null if unavailable.
             */
            function getVideoDurationSeconds(file) {
                return new Promise((resolve) => {
                    const video = document.createElement('video');
                    video.preload = 'metadata';
                    video.muted = true;
                    video.playsInline = true;
                    video.setAttribute('playsinline', '');
                    const url = URL.createObjectURL(file);

                    const cleanup = (val) => {
                        URL.revokeObjectURL(url);
                        video.remove();
                        resolve(val);
                    };

                    video.onloadedmetadata = () => {
                        const d = video.duration;
                        cleanup(Number.isFinite(d) && d > 0 ? Math.round(d) : null);
                    };
                    video.onerror = () => cleanup(null);

                    video.src = url;
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                const uploadAllBtn = document.getElementById('upload-all-btn');
                const clearQueueBtn = document.getElementById('clear-queue-btn');
                const doneBtn = document.getElementById('done-btn');
                const statusWrap = document.getElementById('upload-status');
                const statusText = document.getElementById('upload-status-text');
                const statusCount = document.getElementById('upload-status-count');
                const progressBar = document.getElementById('upload-progress-bar');

                const dz = new Dropzone('#gallery-dropzone', {
                    url: '{{ route('admin.media-gallery.upload') }}',
                    paramName: 'file',
                    maxFilesize: 250,
                    maxFiles: 50,
                    parallelUploads: 5, // upload up to 5 media assets at the same time
                    acceptedFiles: 'image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    addRemoveLinks: true,
                    timeout: 0, // no timeout for big video uploads
                    dictFileTooBig: 'File is too big. Images max 2MB, videos max 250MB.',
                    dictInvalidFileType: 'Only images and videos allowed.',
                    autoProcessQueue: false, // queue is processed manually via "Upload All"
                    autoQueue: true,
                    init: function() {
                        const dropzoneInstance = this;

                        function getPendingUploadFiles() {
                            return dropzoneInstance.files.filter((file) =>
                                file.accepted !== false &&
                                (file.status === Dropzone.ADDED || file.status === Dropzone.QUEUED)
                            );
                        }

                        function enqueuePendingFiles() {
                            dropzoneInstance.getAddedFiles().forEach((file) => {
                                if (file.accepted === false) {
                                    return;
                                }

                                try {
                                    dropzoneInstance.enqueueFile(file);
                                } catch (error) {
                                    // File was already queued or processed.
                                }
                            });
                        }

                        function refreshButtons() {
                            const pending = getPendingUploadFiles().length;
                            const uploading = dropzoneInstance.getUploadingFiles().length;
                            uploadAllBtn.disabled = pending === 0 || uploading > 0;
                            clearQueueBtn.disabled = dropzoneInstance.files.length === 0 || uploading > 0;
                        }

                        function refreshProgress() {
                            const total = dropzoneInstance.files.length;
                            const done = dropzoneInstance.getFilesWithStatus(Dropzone.SUCCESS).length;
                            const failed = dropzoneInstance.getFilesWithStatus(Dropzone.ERROR).length;
                            if (total === 0) {
                                statusWrap.classList.add('d-none');
                                return;
                            }
                            statusWrap.classList.remove('d-none');
                            statusCount.textContent = `${done + failed} / ${total}`;
                            const pct = total ? Math.round(((done + failed) / total) * 100) : 0;
                            progressBar.style.width = pct + '%';
                            progressBar.classList.toggle('bg-danger', failed > 0 && done === 0);
                            progressBar.classList.toggle('bg-warning', failed > 0 && done > 0);
                        }

                        this.on('addedfile', function(file) {
                            doneBtn.classList.add('d-none');

                            if (!file.filenameInput) {
                                const input = document.createElement('input');
                                input.type = 'text';
                                input.className = 'dz-filename-input';
                                input.placeholder = 'Enter file name (optional)';
                                input.value = file.name.replace(/\.[^/.]+$/, '');
                                file.previewElement.appendChild(input);
                                file.filenameInput = input;

                                input.addEventListener('click', (e) => e.stopPropagation());
                                input.addEventListener('keydown', (e) => e.stopPropagation());
                            }

                            if (file.type && file.type.startsWith('video/')) {
                                file._thumbPromise = captureVideoFrameAsJpegBlob(file, 1).then((blob) => {
                                    file.clientThumbnailBlob = blob;
                                    return blob;
                                }).catch(() => {
                                    file.clientThumbnailBlob = null;
                                });

                                file._durationPromise = getVideoDurationSeconds(file).then((secs) => {
                                    file.clientDurationSeconds = secs;
                                    return secs;
                                }).catch(() => {
                                    file.clientDurationSeconds = null;
                                });
                            } else {
                                file._thumbPromise = Promise.resolve();
                                file._durationPromise = Promise.resolve();
                            }

                            refreshButtons();
                            setTimeout(refreshButtons, 0);
                        });

                        this.on('error', function(file, msg) {
                            refreshButtons();

                            const el = file.previewElement?.querySelector('[data-dz-errormessage]');
                            if (!el) return;
                            if (typeof msg === 'string') {
                                el.textContent = msg;
                            } else if (msg.errors) {
                                el.textContent = Object.values(msg.errors).flat().join(' ');
                            } else if (msg.file && msg.file.length) {
                                el.textContent = msg.file[0];
                            } else if (msg.message) {
                                el.textContent = msg.message;
                            }
                        });

                        this.on('removedfile', function() {
                            refreshButtons();
                            refreshProgress();
                        });

                        this.on('sending', function(file, xhr, formData) {
                            const filename = file.filenameInput ? file.filenameInput.value : '';
                            formData.append('custom_name', filename);

                            if (file.clientThumbnailBlob instanceof Blob) {
                                formData.append('thumbnail', file.clientThumbnailBlob, 'thumbnail.jpg');
                            }

                            if (Number.isFinite(file.clientDurationSeconds) && file.clientDurationSeconds > 0) {
                                formData.append('total_seconds', file.clientDurationSeconds);
                            }
                        });

                        this.on('success', function(file, res) {
                            if (res.media) {
                                file.previewElement.dataset.mediaId = res.media.id;
                            }
                            if (file.filenameInput) {
                                file.filenameInput.disabled = true;
                            }
                        });

                        // Keep feeding the queue so 5 uploads stay in flight until it's drained
                        this.on('complete', function() {
                            if (dropzoneInstance.getQueuedFiles().length > 0) {
                                dropzoneInstance.processQueue();
                            }
                            refreshButtons();
                            refreshProgress();
                        });

                        this.on('queuecomplete', function() {
                            const failed = dropzoneInstance.getFilesWithStatus(Dropzone.ERROR).length;
                            const done = dropzoneInstance.getFilesWithStatus(Dropzone.SUCCESS).length;
                            if (done > 0 || failed > 0) {
                                statusText.textContent = failed > 0
                                    ? `Finished — ${done} uploaded, ${failed} failed.`
                                    : `All ${done} file(s) uploaded successfully.`;
                                if (done > 0) {
                                    doneBtn.classList.remove('d-none');
                                }
                            }
                            refreshButtons();
                        });

                        uploadAllBtn.addEventListener('click', async () => {
                            enqueuePendingFiles();

                            const pending = getPendingUploadFiles();
                            if (pending.length === 0) {
                                return;
                            }

                            uploadAllBtn.disabled = true;
                            clearQueueBtn.disabled = true;
                            statusWrap.classList.remove('d-none');
                            statusText.textContent = 'Preparing files (extracting video thumbnails)...';

                            const prepTimeout = (promise) => Promise.race([
                                promise,
                                new Promise((resolve) => setTimeout(resolve, 15000)),
                            ]);

                            // Wait for client-side thumbnail/duration extraction before sending
                            await Promise.all(pending.flatMap((f) => [
                                prepTimeout(f._thumbPromise || Promise.resolve()),
                                prepTimeout(f._durationPromise || Promise.resolve()),
                            ]));

                            enqueuePendingFiles();

                            if (dropzoneInstance.getQueuedFiles().length === 0) {
                                statusText.textContent = 'No files ready to upload.';
                                refreshButtons();
                                return;
                            }

                            statusText.textContent = 'Uploading (up to 5 in parallel)...';
                            refreshProgress();
                            dropzoneInstance.processQueue();
                        });

                        clearQueueBtn.addEventListener('click', () => {
                            dropzoneInstance.removeAllFiles(true);
                            statusWrap.classList.add('d-none');
                            progressBar.style.width = '0%';
                            refreshButtons();
                        });
                    }
                });

                // Warn before leaving while uploads are in flight
                window.addEventListener('beforeunload', function(e) {
                    if (dz.getUploadingFiles().length > 0 || dz.getQueuedFiles().length > 0) {
                        e.preventDefault();
                        e.returnValue = '';
                    }
                });
            });
        </script>
    @endpush
</x-app-dashboard-layout>
