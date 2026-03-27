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
                    <div class="card-body text-center p-0">
                        @if ($media->isImage())
                            <img src="{{ $media->url }}" alt="{{ $media->title }}" class="img-fluid rounded">
                        @else
                            <video src="{{ $media->url }}" class="img-fluid rounded" controls style="max-height: 240px;" poster="{{ $media->url }}"></video>
                        @endif
                        <p class="small text-muted mt-2 mb-0">{{ $media->original_name }} · {{ number_format($media->file_size / 1024, 1) }} KB</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.media-gallery.update', $media) }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $media->title) }}" placeholder="Optional title">
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('admin.media-gallery.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-dashboard-layout>
