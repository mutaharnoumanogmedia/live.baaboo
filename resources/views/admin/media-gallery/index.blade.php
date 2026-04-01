<x-app-dashboard-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 py-3 mb-1 mb-2">
                {{ __('Media Gallery') }}
            </h2>
            <a href="{{ route('admin.media-gallery.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-upload me-1"></i> Upload Media
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Search Bar --}}
            <form method="GET" action="{{ route('admin.media-gallery.index') }}" class="mb-4">
                <div class="input-group" style="max-width: 480px;">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text"
                           class="form-control border-start-0"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search media...">
                    @if(request('search'))
                        <a href="{{ route('admin.media-gallery.index') }}" class="btn btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </form>

            <style>
                .media-gallery-grid {
                    display: grid;
                    grid-template-columns: repeat(6, 1fr);
                    gap: 24px;
                }
                @media (max-width: 1400px) {
                    .media-gallery-grid { grid-template-columns: repeat(4, 1fr);}
                }
                @media (max-width: 992px) {
                    .media-gallery-grid { grid-template-columns: repeat(3, 1fr);}
                }
                @media (max-width: 768px) {
                    .media-gallery-grid { grid-template-columns: repeat(2, 1fr);}
                }
                @media (max-width: 576px) {
                    .media-gallery-grid { grid-template-columns: 1fr;}
                }

                .media-item-card {
                    border-radius: 14px;
                    overflow: hidden;
                    box-shadow: 0 2px 12px 0 rgba(44,39,105,0.06);
                    background: #fff;
                    transition: box-shadow 0.12s;
                    position: relative;
                    min-height: 0;
                }
                .media-item-card:hover {
                    box-shadow: 0 4px 20px 0 rgba(44,39,105,0.15);
                }
                .media-thumb-box {
                    position: relative;
                    width: 100%;
                    aspect-ratio: 1/1;
                    background: #f1f1f2;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                }
                .media-tag {
                    position: absolute;
                    top: 8px;
                    left: 8px;
                    padding: 3px 11px 3px 8px;
                    font-size: 0.75rem;
                    border-radius: 25px;
                    font-weight: 600;
                    color: #fff;
                    background: rgba(44,39,105,0.84);
                    display: flex;
                    align-items: center;
                    z-index: 2;
                }
                .media-type-image { background: #8e3be0; }
                .media-type-video { background: #16a34a; }
                .media-thumb-img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    border-radius: 0;
                    display: block;
                }
                .media-item-actions {
                    position: absolute;
                    top: 8px;
                    right: 8px;
                    z-index: 3;
                }
                .dropdown-dot-btn {
                    padding: 0 6px;
                    background: none;
                    border: none;
                    color: #4B5563;
                    font-size: 19px;
                    border-radius: 50%;
                    transition: background 0.14s;
                }
                .dropdown-dot-btn:hover {
                    background: #e5e7eb;
                }
                .media-item-info {
                    padding: 13px 16px 13px 16px;
                }
                .media-item-title {
                    font-size: 1rem;
                    font-weight: 600;
                    margin-bottom: 2px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    color: #000;
                }
                .media-item-meta {
                    font-size: 0.93rem;
                    color: #767a96;
                    margin-bottom: 2px;
                }
            </style>

            @if($media->isEmpty())
                <div class="card my-5">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="bi bi-images display-4"></i>
                        <p class="mb-0 mt-2">No media in gallery yet. <a href="{{ route('admin.media-gallery.create') }}">Upload some</a>.</p>
                    </div>
                </div>
            @else
                <div class="media-gallery-grid mb-5">

                    @foreach ($media as $item)
                        <div class="media-item-card">

                            <div class="media-thumb-box">
                                {{-- "Tag" for type --}}
                                @if($item->isImage())
                                    <div class="media-tag media-type-image">
                                        <i class="bi bi-image me-1"></i> Image
                                    </div>
                                @else
                                    <div class="media-tag media-type-video">
                                        <i class="bi bi-film me-1"></i> Video
                                    </div>
                                @endif

                                {{-- Action dots dropdown --}}
                                <div class="media-item-actions dropdown">
                                    <button class="dropdown-dot-btn" type="button" id="mediaDropdown{{$item->id}}" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="mediaDropdown{{$item->id}}">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.media-gallery.edit', $item) }}">
                                                <i class="bi bi-pencil-square me-1"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.media-gallery.attach-show', $item) }}">
                                                <i class="bi bi-link-45deg me-1"></i> Attach to Show
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.media-gallery.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this item?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash3 me-1"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>

                                {{-- Actual Media Thumbnail --}}
                                @if($item->isImage())
                                    <img src="{{ $item->path }}" class="media-thumb-img" alt="{{ $item->title ?: $item->original_name }}">
                                @else
                                    @if($item->thumbnail)
                                        <img src="{{ $item->thumbnail }}" class="media-thumb-img" alt="Video thumbnail">
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="font-size: 2.3rem; color: #16a34a; opacity: 0.7">
                                            <i class="bi bi-film"></i>
                                        </div>
                                    @endif
                                @endif

                            </div>

                            <div class="media-item-info">
                                <div class="media-item-title"
                                    title="{{ $item->title ?? $item->original_name }}">
                                    {{ $item->title ?: $item->original_name }}
                                </div>
                                <div class="media-item-meta">
                                    {{ number_format($item->file_size / 1024, 1) }} KB · {{ $item->type }}
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>

                @if ($media->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $media->links() }}
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-dashboard-layout>
