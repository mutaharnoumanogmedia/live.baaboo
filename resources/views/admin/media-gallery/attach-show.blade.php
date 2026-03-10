<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attach to Live Show') }}
        </h2>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        @if ($media->isImage())
                            <img src="{{ $media->url }}" alt="{{ $media->title }}" class="img-fluid rounded">
                        @else
                            <video src="{{ $media->url }}" class="img-fluid rounded" style="max-height: 200px;" muted></video>
                        @endif
                        <p class="mt-2 mb-0">{{ $media->title ?: $media->original_name }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Select a live show to attach this media to</div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @forelse ($liveShows as $show)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $show->title }} <small class="text-muted">({{ $show->scheduled_at?->format('M j, Y') }})</small></span>
                                    <form action="{{ route('admin.media-gallery.attach-to-live-show') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="live_show_id" value="{{ $show->id }}">
                                        <input type="hidden" name="gallery_media_id" value="{{ $media->id }}">
                                        <button type="submit" class="btn btn-sm btn-primary">Attach</button>
                                    </form>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">No live shows found.</li>
                            @endforelse
                        </ul>
                        <div class="mt-3">
                            <a href="{{ route('admin.media-gallery.index') }}" class="btn btn-secondary">Back to Gallery</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-dashboard-layout>
