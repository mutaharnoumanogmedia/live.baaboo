<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Live Show') }}
        </h2>
    </x-slot>

    <div class="container form-container">
        <form method="POST" action="{{ route('admin.live-shows.update', $liveShow->id) }}" enctype="multipart/form-data"
            id="liveShowForm">
            @csrf
            @method('PUT')

            <!-- Basic Information Card -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2"></i>Basic Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label required-field">Title</label>
                            <input type="text" name="title" class="form-control"
                                placeholder="Enter live show title" value="{{ old('title', $liveShow->title ?? '') }}"
                                required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Provide a description of your live show">{{ old('description', $liveShow->description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule & Stream Details Card -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-2"></i>Schedule & Stream Details
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Scheduled At</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control"
                                value="{{ old('scheduled_at', isset($liveShow) ? \Carbon\Carbon::parse($liveShow->scheduled_at)->format('Y-m-d\TH:i') : '') }}"
                                required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="scheduled"
                                    {{ old('status', $liveShow->status ?? '') == 'scheduled' ? 'selected' : '' }}>
                                    Scheduled</option>
                                <option value="live"
                                    {{ old('status', $liveShow->status ?? '') == 'live' ? 'selected' : '' }}>Live
                                </option>
                                <option value="completed"
                                    {{ old('status', $liveShow->status ?? '') == 'completed' ? 'selected' : '' }}>
                                    Completed</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Stream Link</label>
                            <input type="text" name="stream_link" class="form-control" placeholder="https://"
                                value="{{ old('stream_link', $liveShow->stream_link ?? '') }}">
                            <div class="form-text">Enter the URL where your live stream will be hosted</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Host & Prize Information Card -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i>Host & Prize Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Host Name</label>
                            <input type="text" name="host_name" class="form-control" placeholder="Enter host's name"
                                value="{{ old('host_name', $liveShow->host_name ?? '') }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label required-field">Prize Amount</label>
                            <input type="number" name="prize_amount" class="form-control" placeholder="0.00"
                                step="0.01" required
                                value="{{ old('prize_amount', $liveShow->prize_amount ?? '') }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Currency</label>
                            <input type="text" name="currency" class="form-control" value="EUR" readonly>
                            <div class="form-text">Currency is fixed to EUR</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media Uploads Card -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-images me-2"></i>Media Uploads
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Thumbnail</label>
                            <div class="dropzone" id="thumbnailDropzone">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p class="small text-muted">Recommended: 500×300px, JPG or PNG</p>
                            </div>
                            <input type="file" name="thumbnail" id="thumbnailInput" class="d-none" accept="image/*">
                            <div class="preview-container" id="thumbnailPreview">
                                @if (isset($liveShow) && $liveShow->thumbnail)
                                    <p>Current Thumbnail:</p>
                                    <img src="{{ $liveShow->thumbnail }}" class="preview-image"
                                        style="max-width:200px;">
                                @endif
                                <p>New Preview:</p>
                                <img src="" class="preview-image" id="thumbnailPreviewImage">
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="form-label">Banner</label>
                            <div class="dropzone" id="bannerDropzone">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p class="small text-muted">Recommended: 1200×300px, JPG or PNG</p>
                            </div>
                            <input type="file" name="banner" id="bannerInput" class="d-none" accept="image/*">
                            <div class="preview-container" id="bannerPreview">
                                @if (isset($liveShow) && $liveShow->banner)
                                    <p>Current Banner:</p>
                                    <img src="{{ $liveShow->banner }}" class="preview-image"
                                        style="max-width:400px;">
                                @endif
                                <p>New Preview:</p>
                                <img src="" class="preview-image" id="bannerPreviewImage">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>
                    {{ __('Update Live Show') }}
                </button>
            </div>
        </form>
    </div>

    @push('styles')
        <link href="{{ asset('assets/styles/dropzone.css') }}" rel="stylesheet">
        <style>
            .form-container {
                max-width: 900px;
                margin: 0 auto;
            }

            .card {
                border: none;
                border-radius: 12px;
                box-shadow: var(--card-shadow);
                margin-bottom: 1.5rem;
                transition: transform 0.3s ease;

            }

            .card:hover {
                transform: translateY(-5px);
            }

            .card-header {

                color: white;
                border-radius: 12px 12px 0 0 !important;
                padding: 1rem 1.5rem;
                font-weight: 600;
            }

            .card-body {
                padding: 1.5rem;
            }

            .form-label {
                font-weight: 500;
                margin-bottom: 0.5rem;
                color: #495057;
            }

            .form-control,
            .form-select {
                border-radius: 8px;
                padding: 0.75rem 1rem;
                border: 1px solid #dee2e6;
                transition: all 0.3s;
            }

            .form-control:focus,
            .form-select:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
            }
        </style>
    @endpush
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>


        <script>
            Dropzone.autoDiscover = false;

            function initDropzone(id, inputId) {
                let dz = new Dropzone(id, {
                    url: "#",
                    autoProcessQueue: false,
                    addRemoveLinks: true,
                    maxFiles: 1,
                    acceptedFiles: 'image/*',
                    previewsContainer: null,
                    dictDefaultMessage: "Drag files here or click to upload"


                });
                dz.on("addedfile", function(file) {
                    document.querySelector(inputId).files = file ? createFileList(file) : null;
                });
            }

            function createFileList(file) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                return dataTransfer.files;
            }

            initDropzone("#thumbnailDropzone", "#thumbnailInput");
            initDropzone("#bannerDropzone", "#bannerInput");
        </script>
    @endpush
</x-app-dashboard-layout>
