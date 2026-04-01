<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Media') }}
        </h2>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center p-0" id="media-preview-div">
                        @if ($media->isImage())
                            <img id="media-preview" src="{{ $media->url }}" alt="{{ $media->title }}" class="img-fluid rounded">
                        @else
                            <video id="media-preview" src="{{ $media->url }}" class="img-fluid rounded" controls style="max-height: 240px;" poster="{{ $media->url }}"></video>
                        @endif
                        <div id="media-loader" class="d-none py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <p class="small text-muted mt-2 mb-0">{{ $media->original_name }} · {{ number_format($media->file_size / 1024, 1) }} KB</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form 
                            id="edit-media-form" 
                            method="POST" 
                            action="{{ route('admin.media-gallery.update', $media) }}"
                            enctype="multipart/form-data"
                        >
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $media->title) }}" placeholder="Optional title">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Change File</label>
                                <input type="file" name="file" class="form-control" accept="image/*,video/*">
                                <small class="form-text text-muted">Leave empty to keep the current file. Max size: 150MB</small>
                            </div>
                            <button type="submit" class="btn btn-primary" id="save-btn">Save</button>
                            <a href="{{ route('admin.media-gallery.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('edit-media-form');
        const saveBtn = document.getElementById('save-btn');
        const previewDiv = document.getElementById('media-preview-div');
        const loader = document.getElementById('media-loader');

        form.addEventListener('submit', function() {
          // Disable all form fields and Save button
          Array.from(form.elements).forEach(el => el.disabled = true);
          saveBtn.disabled = true;

          // Show loader
          loader.classList.remove('d-none');
          
          // Hide media preview (img or video)
          let preview = previewDiv.querySelector('#media-preview');
          if (preview) preview.style.display = 'none';
        });
      });
    </script>
</x-app-dashboard-layout>
