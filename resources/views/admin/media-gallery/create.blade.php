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
                    Images: max 2 MB (JPEG, PNG, GIF, WebP). Videos: max 10 MB (MP4, WebM).
                </p>
                <form action="{{ route('admin.media-gallery.upload') }}" class="dropzone border rounded p-4" id="gallery-dropzone">
                    @csrf
                </form>
                <div class="mt-3">
                    <a href="{{ route('admin.media-gallery.index') }}" class="btn btn-secondary">Back to Gallery</a>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Dropzone.autoDiscover = false;
                const dz = new Dropzone('#gallery-dropzone', {
                    url: '{{ route("admin.media-gallery.upload") }}',
                    paramName: 'file',
                    maxFilesize: 250,
                    maxFiles: 50,
                    acceptedFiles: 'image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    addRemoveLinks: true,
                    dictDefaultMessage: 'Drop images or videos here (Images 2MB max, Videos 250MB max)',
                    dictFileTooBig: 'File is too big. Images max 2MB, videos max 250MB.',
                    dictInvalidFileType: 'Only images and videos allowed.',
                    init: function() {
                        this.on('success', function(file, res) {
                            if (res.media) {
                                file.previewElement.dataset.mediaId = res.media.id;
                            }
                        });
                        this.on('error', function(file, msg) {
                            if (typeof msg === 'string') {
                                file.previewElement.querySelector('[data-dz-errormessage]').textContent = msg;
                            } else if (msg.file && msg.file.length) {
                                file.previewElement.querySelector('[data-dz-errormessage]').textContent = msg.file[0];
                            }
                        });
                    }
                });
            });
        </script>
    @endpush
</x-app-dashboard-layout>
