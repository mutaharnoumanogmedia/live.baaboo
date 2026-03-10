<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 py-3 mb-1">
            {{ __('Media Gallery') }}
            <a href="{{ route('admin.media-gallery.create') }}" class="btn btn-success btn-sm mx-4">
                <i class="bi bi-upload me-1"></i> Upload Media
            </a>
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row g-4">
                @forelse ($media as $item)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card h-100">
                            <div class="card-img-top position-relative bg-dark" style="height: 160px; overflow: hidden;">
                                @if ($item->isImage())
                                    <img src="{{ $item->url }}" alt="{{ $item->title }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <video src="{{ $item->url }}" class="w-100 h-100 object-fit-cover" muted></video>
                                    <span class="position-absolute bottom-0 end-0 badge bg-dark m-1"><i class="bi bi-play-fill"></i> Video</span>
                                @endif
                            </div>
                            <div class="card-body p-2">
                                <p class="card-text small text-truncate mb-1" title="{{ $item->title }}">{{ $item->title ?: $item->original_name }}</p>
                                <p class="small text-muted mb-2">{{ number_format($item->file_size / 1024, 1) }} KB · {{ $item->type }}</p>
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="{{ route('admin.media-gallery.edit', $item) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <a href="{{ route('admin.media-gallery.attach-show', $item) }}" class="btn btn-sm btn-outline-primary">Attach to Show</a>
                                    <form action="{{ route('admin.media-gallery.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5 text-muted">
                                <i class="bi bi-images display-4"></i>
                                <p class="mb-0 mt-2">No media in gallery yet. <a href="{{ route('admin.media-gallery.create') }}">Upload some</a>.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($media->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $media->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-dashboard-layout>
