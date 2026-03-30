<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload to Media Gallery') }}
        </h2>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-cloud-upload me-2"></i> Drop files here or click to upload
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Images: max 2 MB (JPEG, PNG, GIF, WebP). Videos: max 250 MB (MP4, WebM).
                </p>
                <form action="{{ route('admin.media-gallery.upload') }}" class="dropzone border rounded p-4"
                    id="gallery-dropzone">
                    @csrf
                </form>
                <div class="mt-3 d-flex  gap-5">
                    <div id="confirm-upload-container">

                    </div>
                    <div>
                        <a href="{{ route('admin.media-gallery.index') }}" class="btn btn-secondary">Back to Gallery</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
        <style>
            .dz-filename-input {
                margin-top: 0.5rem;
                width: 100%;
                display: block;
                font-size: 0.95rem;
                padding: 0.3rem;
                border: 1px solid #d1d5db;
                border-radius: 4px;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
        <script>
            Dropzone.autoDiscover = false;

            document.addEventListener('DOMContentLoaded', function() {
                const dz = new Dropzone('#gallery-dropzone', {
                    url: '{{ route('admin.media-gallery.upload') }}',
                    paramName: 'file',
                    maxFilesize: 250,
                    maxFiles: 50,
                    acceptedFiles: 'image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    addRemoveLinks: true,
                    dictDefaultMessage: 'Drop images or videos here (Images 2MB max, Videos 250MB max)',
                    dictFileTooBig: 'File is too big. Images max 2MB, videos max 250MB.',
                    dictInvalidFileType: 'Only images and videos allowed.',
                    autoProcessQueue: false, // We will process manually after getting filename
                    init: function() {
                        this.on('addedfile', function(file) {
                            // Prevent multiple inputs
                            if (file.filenameInput) return;

                            // Create custom input for filename
                            const input = document.createElement('input');
                            input.type = 'text';
                            input.className = 'dz-filename-input';
                            input.placeholder = 'Enter file name (optional)';
                            // set initial value to original file name (without extension)
                            input.value = file.name.replace(/\.[^/.]+$/, "");
                            file.previewElement.appendChild(input);

                            file.filenameInput = input;

                            input.addEventListener('click', function(e) {
                                e.stopPropagation();
                            });
                            input.addEventListener('keydown', function(e) {
                                // Prevent dropzone (space or esc key) interfering
                                e.stopPropagation();
                            });
                        });

                        this.on('sending', function(file, xhr, formData) {
                            // Append filename from the input if present
                            const filename = file.filenameInput ? file.filenameInput.value : '';
                            formData.append('custom_name', filename);
                        });

                        this.on('success', function(file, res) {
                            if (res.media) {
                                file.previewElement.dataset.mediaId = res.media.id;
                            }
                        });
                        this.on('error', function(file, msg) {
                            if (typeof msg === 'string') {
                                file.previewElement.querySelector('[data-dz-errormessage]')
                                    .textContent = msg;
                            } else if (msg.file && msg.file.length) {
                                file.previewElement.querySelector('[data-dz-errormessage]')
                                    .textContent = msg.file[0];
                            }
                        });

                        // Add a button to confirm upload with filename(s)
                        const dropzoneForm = document.getElementById('gallery-dropzone');
                        let confirmBtn = document.createElement('button');
                        confirmBtn.type = 'button';
                        confirmBtn.className = 'btn btn-primary';
                        confirmBtn.innerHTML = 'Upload Selected Files';
                        confirmBtn.onclick = () => {
                            this.processQueue();
                        };
                        document.getElementById('confirm-upload-container').appendChild(confirmBtn);
                    }
                });
            });
        </script>
    @endpush
</x-app-dashboard-layout>
