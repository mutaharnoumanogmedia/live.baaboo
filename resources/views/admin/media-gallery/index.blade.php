<x-app-dashboard-layout>
    <style>
        .mg-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .mg-filter-pills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .mg-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            color: #4b5563;
            background: #fff;
            border: 1px solid #e5e7eb;
            text-decoration: none;
            transition: all 0.15s;
        }

        .mg-pill:hover {
            border-color: #8e3be0;
            color: #8e3be0;
        }

        .mg-pill.active {
            background: #2c2769;
            border-color: #2c2769;
            color: #fff;
        }

        .mg-pill .badge {
            font-size: 0.7rem;
            font-weight: 700;
        }

        .media-gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 20px;
        }

        .media-item-card {
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 12px 0 rgba(44, 39, 105, 0.06);
            background: #fff;
            transition: transform 0.15s, box-shadow 0.15s;
            position: relative;
        }

        .media-item-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px 0 rgba(44, 39, 105, 0.16);
        }

        .media-thumb-box {
            position: relative;
            width: 100%;
            aspect-ratio: 4/3;
            background: #f1f1f2;
            overflow: hidden;
            cursor: pointer;
        }

        .media-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.25s ease;
        }

        .media-item-card:hover .media-thumb-img {
            transform: scale(1.04);
        }

        .media-thumb-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #16a34a;
            opacity: 0.6;
        }

        .media-tag {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 3px 11px;
            font-size: 0.72rem;
            border-radius: 25px;
            font-weight: 600;
            color: #fff;
            z-index: 2;
        }

        .media-type-image { background: rgba(142, 59, 224, 0.92); }
        .media-type-video { background: rgba(22, 163, 74, 0.92); }

        .media-duration-tag {
            position: absolute;
            bottom: 10px;
            right: 10px;
            padding: 2px 9px;
            font-size: 0.72rem;
            border-radius: 6px;
            font-weight: 600;
            color: #fff;
            background: rgba(0, 0, 0, 0.65);
            z-index: 2;
        }

        /* Hover overlay with quick actions */
        .media-hover-overlay {
            position: absolute;
            inset: 0;
            background: rgba(17, 14, 50, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            opacity: 0;
            transition: opacity 0.18s ease;
            z-index: 3;
        }

        .media-item-card:hover .media-hover-overlay {
            opacity: 1;
        }

        .media-action-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: #2c2769;
            font-size: 1rem;
            text-decoration: none;
            transition: transform 0.12s, background 0.12s, color 0.12s;
        }

        .media-action-btn:hover {
            transform: scale(1.12);
            background: #2c2769;
            color: #fff;
        }

        .media-action-btn.danger:hover {
            background: #dc2626;
            color: #fff;
        }

        .media-item-info {
            padding: 12px 14px;
        }

        .media-item-title {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #111827;
        }

        .media-item-meta {
            font-size: 0.8rem;
            color: #767a96;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Preview modal */
        #mediaPreviewModal .modal-body {
            background: #0f0e1c;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 320px;
            padding: 0;
        }

        #mediaPreviewModal img,
        #mediaPreviewModal video {
            max-width: 100%;
            max-height: 78vh;
            display: block;
        }

        .mg-empty {
            text-align: center;
            padding: 72px 24px;
            color: #767a96;
        }

        .mg-empty i {
            font-size: 3.5rem;
            opacity: 0.4;
        }
    </style>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 py-3 mb-0">
                {{ __('Media Gallery') }}
                <span class="badge bg-secondary align-middle ms-1">{{ $counts['all'] }}</span>
            </h2>
            <a href="{{ route('admin.media-gallery.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-upload me-1"></i> Upload Media
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="container">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Toolbar: filters + search --}}
            <div class="mg-toolbar">
                <div class="mg-filter-pills">
                    <a href="{{ route('admin.media-gallery.index', array_filter(['search' => request('search')])) }}"
                        class="mg-pill {{ !request('type') ? 'active' : '' }}">
                        All <span class="badge bg-light text-dark">{{ $counts['all'] }}</span>
                    </a>
                    <a href="{{ route('admin.media-gallery.index', array_filter(['type' => 'image', 'search' => request('search')])) }}"
                        class="mg-pill {{ request('type') === 'image' ? 'active' : '' }}">
                        <i class="bi bi-image"></i> Images <span class="badge bg-light text-dark">{{ $counts['image'] }}</span>
                    </a>
                    <a href="{{ route('admin.media-gallery.index', array_filter(['type' => 'video', 'search' => request('search')])) }}"
                        class="mg-pill {{ request('type') === 'video' ? 'active' : '' }}">
                        <i class="bi bi-film"></i> Videos <span class="badge bg-light text-dark">{{ $counts['video'] }}</span>
                    </a>
                </div>

                <form method="GET" action="{{ route('admin.media-gallery.index') }}" class="d-flex">
                    @if (request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif
                    <div class="input-group" style="max-width: 340px;">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0" name="search"
                            value="{{ request('search') }}" placeholder="Search by title or file name...">
                        @if (request('search'))
                            <a href="{{ route('admin.media-gallery.index', array_filter(['type' => request('type')])) }}"
                                class="btn btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>

            @if ($media->isEmpty())
                <div class="card my-4">
                    <div class="card-body mg-empty">
                        <i class="bi bi-images d-block mb-3"></i>
                        @if (request('search') || request('type'))
                            <p class="mb-2">No media matches your filters.</p>
                            <a href="{{ route('admin.media-gallery.index') }}" class="btn btn-outline-secondary btn-sm">Reset filters</a>
                        @else
                            <p class="mb-2">No media in gallery yet.</p>
                            <a href="{{ route('admin.media-gallery.create') }}" class="btn btn-success btn-sm">
                                <i class="bi bi-upload me-1"></i> Upload your first files
                            </a>
                        @endif
                    </div>
                </div>
            @else
                <div class="media-gallery-grid mb-5">

                    @foreach ($media as $item)
                        <div class="media-item-card" id="media-card-{{ $item->id }}">

                            <div class="media-thumb-box">
                                @if ($item->isImage())
                                    <div class="media-tag media-type-image"><i class="bi bi-image me-1"></i>Image</div>
                                @else
                                    <div class="media-tag media-type-video"><i class="bi bi-film me-1"></i>Video</div>
                                    @if ($item->total_seconds)
                                        <div class="media-duration-tag">
                                            <i class="bi bi-clock me-1"></i>{{ gmdate($item->total_seconds >= 3600 ? 'G:i:s' : 'i:s', $item->total_seconds) }}
                                        </div>
                                    @endif
                                @endif

                                @if ($item->isImage())
                                    <img src="{{ $item->path }}" class="media-thumb-img" loading="lazy"
                                        alt="{{ $item->title ?: $item->original_name }}">
                                @elseif ($item->thumbnail)
                                    <img src="{{ $item->thumbnail }}" class="media-thumb-img" loading="lazy"
                                        alt="Video thumbnail">
                                @else
                                    <div class="media-thumb-placeholder"><i class="bi bi-film"></i></div>
                                @endif

                                {{-- Hover quick actions --}}
                                <div class="media-hover-overlay">
                                    <button type="button" class="media-action-btn" title="Preview"
                                        onclick="previewMedia('{{ $item->type }}', '{{ $item->url }}', @js($item->title ?: $item->original_name))">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.media-gallery.edit', $item) }}" class="media-action-btn" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="{{ route('admin.media-gallery.attach-show', $item) }}" class="media-action-btn" title="Attach to Show">
                                        <i class="bi bi-link-45deg"></i>
                                    </a>
                                    <form action="{{ route('admin.media-gallery.destroy', $item) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Delete “{{ addslashes($item->title ?: $item->original_name) }}”? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="media-action-btn danger" title="Delete">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="media-item-info">
                                <div class="media-item-title" title="{{ $item->title ?: $item->original_name }}">
                                    {{ $item->title ?: $item->original_name }}
                                </div>
                                <div class="media-item-meta">
                                    <span>{{ number_format($item->file_size / (1024 * 1024), 2) }} MB</span>
                                    <span>·</span>
                                    <span>{{ $item->created_at->format('M j, Y') }}</span>
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

    {{-- Preview lightbox --}}
    <div class="modal fade" id="mediaPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title text-truncate" id="mediaPreviewTitle"></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="mediaPreviewBody"></div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function previewMedia(type, url, title) {
                const body = document.getElementById('mediaPreviewBody');
                document.getElementById('mediaPreviewTitle').textContent = title || '';
                body.innerHTML = '';

                if (type === 'video') {
                    const video = document.createElement('video');
                    video.src = url;
                    video.controls = true;
                    video.autoplay = true;
                    body.appendChild(video);
                } else {
                    const img = document.createElement('img');
                    img.src = url;
                    img.alt = title || '';
                    body.appendChild(img);
                }

                const modalEl = document.getElementById('mediaPreviewModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();

                // Stop playback when the modal is dismissed
                modalEl.addEventListener('hidden.bs.modal', () => {
                    body.innerHTML = '';
                }, { once: true });
            }
        </script>
    @endpush
</x-app-dashboard-layout>
