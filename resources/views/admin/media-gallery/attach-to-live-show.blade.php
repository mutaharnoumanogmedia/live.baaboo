<x-app-dashboard-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight m-0">
                {{ __('Gallery for: ') }} {{ $liveShow->title }}
            </h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.media-gallery.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-images me-1"></i> Media Gallery
                </a>
                <a href="{{ route('admin.live-shows.stream-management', $liveShow->id) }}"
                    class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Stream Management
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $attachedIds = $liveShow->galleryMedia->pluck('id')->toArray();
        $attachedEndIds = $liveShow->endMedia->pluck('id')->toArray();
        $totalCount = $allMedia->count();
        $attachedCount = $liveShow->galleryMedia->count();
        $endAttachedCount = $liveShow->endMedia->count();
        // quiz_id => "Q{n}" label, in show order.
        $quizLabelById = $liveShow->quizzes
            ->values()
            ->mapWithKeys(fn($q, $i) => [(int) $q->id => 'Q' . ($i + 1)])
            ->toArray();
        $hasQuizzes = $liveShow->quizzes->isNotEmpty();
        $showQuestionsForJs = $liveShow->quizzes
            ->values()
            ->map(function ($q, $i) {
                return [
                    'id' => (int) $q->id,
                    'label' => 'Q' . ($i + 1),
                    'text' => $q->question,
                ];
            })
            ->values()
            ->all();
    @endphp

    <div class="container py-3">
        <div class="row g-3" id="gallery-attach-root" data-live-show-id="{{ $liveShow->id }}"
            data-attach-url="{{ route('admin.media-gallery.attach-to-live-show') }}"
            data-detach-url="{{ route('admin.media-gallery.detach-from-live-show') }}"
            data-reorder-url="{{ route('admin.media-gallery.reorder') }}"
            data-reorder-end-url="{{ route('admin.media-gallery.reorder-end') }}"
            data-attach-question-url="{{ route('admin.media-gallery.attach-to-question') }}"
            data-detach-question-url="{{ route('admin.media-gallery.detach-from-question') }}"
            data-attach-end-url="{{ route('admin.media-gallery.attach-to-end') }}"
            data-detach-end-url="{{ route('admin.media-gallery.detach-from-end') }}"
            data-csrf="{{ csrf_token() }}">

            {{-- ───────────────────  LEFT PANE — All gallery media  ─────────────────── --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-images text-secondary me-2"></i>All Media
                                <span class="badge bg-secondary ms-1" id="all-media-count">{{ $totalCount }}</span>
                            </h5>
                            <small class="text-muted">Click <strong>Attach</strong> to add to this show</small>
                        </div>
                        <a href="{{ route('admin.media-gallery.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-upload me-1"></i> Upload New
                        </a>
                    </div>

                    <div class="card-body border-bottom py-2">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-7">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" id="media-search" class="form-control"
                                        placeholder="Search by name or title…" />
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="btn-group btn-group-sm w-100" role="group" aria-label="Filter by type">
                                    <input type="radio" class="btn-check" name="type-filter" id="type-all"
                                        value="all" checked />
                                    <label class="btn btn-outline-secondary" for="type-all">All</label>

                                    <input type="radio" class="btn-check" name="type-filter" id="type-video"
                                        value="video" />
                                    <label class="btn btn-outline-secondary" for="type-video"><i
                                            class="fas fa-video me-1"></i>Videos</label>

                                    <input type="radio" class="btn-check" name="type-filter" id="type-image"
                                        value="image" />
                                    <label class="btn btn-outline-secondary" for="type-image"><i
                                            class="fas fa-image me-1"></i>Images</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body" style="max-height: 70vh; overflow-y: auto;">
                        @if ($allMedia->isEmpty())
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-photo-video fa-2x mb-2 d-block opacity-50"></i>
                                <div>No media in your gallery yet.</div>
                                <a href="{{ route('admin.media-gallery.create') }}"
                                    class="btn btn-sm btn-primary mt-2">
                                    <i class="fas fa-upload me-1"></i> Upload Media
                                </a>
                            </div>
                        @else
                            <div class="row g-3" id="all-media-grid">
                                @foreach ($allMedia as $item)
                                    @php
                                        $isAttached = in_array($item->id, $attachedIds);
                                        $isEndAttached = in_array($item->id, $attachedEndIds);
                                    @endphp
                                    <div class="col-6 media-tile" data-media-id="{{ $item->id }}"
                                        data-media-type="{{ $item->type }}"
                                        data-media-search="{{ strtolower(($item->title ?? '') . ' ' . ($item->original_name ?? '')) }}">
                                        <div class="card h-100 media-card {{ $isAttached ? 'is-attached' : '' }}">
                                            <div class="media-thumb-wrapper">
                                                @if ($item->isImage())
                                                    <img src="{{ $item->path }}" class="media-thumb" alt="">
                                                @else
                                                    <img src="{{ $item->thumbnail ?? $item->path }}"
                                                        class="media-thumb" alt="">
                                                    <span class="media-type-icon"><i class="fas fa-play"></i></span>
                                                @endif
                                                <span class="media-type-badge">
                                                    @if ($item->isImage())
                                                        <i class="fas fa-image"></i>
                                                    @else
                                                        <i class="fas fa-video"></i>
                                                        @if ($item->total_seconds)
                                                            {{ gmdate('i:s', (int) $item->total_seconds) }}
                                                        @endif
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="card-body p-2">
                                                <div class="media-title"
                                                    title="{{ $item->title ?? $item->original_name }}">
                                                    {{ $item->title ?? $item->original_name }}
                                                </div>
                                                <button type="button" class="btn btn-sm w-100 mt-2 attach-btn"
                                                    data-media-id="{{ $item->id }}"
                                                    {{ $isAttached ? 'disabled' : '' }}>
                                                    @if ($isAttached)
                                                        <i class="fas fa-check me-1"></i> Attached
                                                    @else
                                                        <i class="fas fa-plus me-1"></i> Attach
                                                    @endif
                                                </button>

                                                @if ($liveShow->quizzes->isNotEmpty())
                                                    @php $qAttached = $questionAttachments[$item->id] ?? []; @endphp
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-dark w-100 mt-1 open-q-modal-btn"
                                                        data-media-id="{{ $item->id }}"
                                                        data-media-title="{{ $item->title ?? $item->original_name }}">
                                                        <i class="fas fa-list-ol me-1"></i> Before question
                                                        <span
                                                            class="badge bg-info q-count-badge {{ count($qAttached) ? '' : 'd-none' }}">{{ count($qAttached) }}</span>
                                                    </button>
                                                @endif
                                                <button type="button"
                                                    class="btn btn-sm w-100 mt-1 attach-end-btn {{ $isEndAttached ? 'btn-success' : 'btn-outline-success' }}"
                                                    data-media-id="{{ $item->id }}"
                                                    data-attached="{{ $isEndAttached ? '1' : '0' }}"
                                                    {{ $isEndAttached ? 'disabled' : '' }}>
                                                    @if ($isEndAttached)
                                                        <i class="fas fa-check me-1"></i> At end
                                                    @else
                                                        <i class="fas fa-flag-checkered me-1"></i> Attach at end
                                                    @endif
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div id="search-empty-state" class="text-center text-muted py-4 d-none">
                                <i class="fas fa-search fa-2x mb-2 d-block opacity-50"></i>
                                No media matches your filters.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ───────────────────  RIGHT PANE — Attached media  ─────────────────── --}}
            <div class="col-lg-6">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list-ol me-2"></i>Show-wide
                            <span class="badge bg-light text-dark ms-1"
                                id="attached-count">{{ $attachedCount }}</span>
                        </h5>
                        <small class="opacity-75">
                            <i class="fas fa-grip-vertical me-1"></i>Drag to reorder
                        </small>
                    </div>

                    <div class="card-body p-0" style="max-height: 38vh; overflow-y: auto;">
                        <ul class="list-group list-group-flush" id="attached-list">
                            @foreach ($liveShow->galleryMedia as $item)
                                <li class="list-group-item attached-row d-flex align-items-center gap-2 px-3 py-2"
                                    data-media-id="{{ $item->id }}">
                                    <span class="drag-handle text-muted" title="Drag to reorder">
                                        <i class="fas fa-grip-vertical"></i>
                                    </span>
                                    <span class="row-index badge bg-secondary">{{ $loop->iteration }}</span>
                                    @if ($item->isImage())
                                        <img src="{{ $item->path }}" class="attached-thumb" alt="">
                                    @else
                                        <div class="attached-thumb-wrap">
                                            <img src="{{ $item->thumbnail ?? $item->path }}" class="attached-thumb"
                                                alt="">
                                            <span class="attached-thumb-badge"><i class="fas fa-play"></i></span>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="attached-title text-truncate"
                                            title="{{ $item->title ?? $item->original_name }}">
                                            {{ $item->title ?? $item->original_name }}
                                        </div>
                                        <div class="attached-sub small text-muted">
                                            {{ ucfirst($item->type) }}
                                            @if ($item->total_seconds)
                                                · {{ gmdate('i:s', (int) $item->total_seconds) }}
                                            @endif
                                        </div>
                                        @php
                                            $rowQuizIds = $questionAttachments[$item->id] ?? [];
                                            $rowLabels = collect($rowQuizIds)
                                                ->map(fn($id) => $quizLabelById[(int) $id] ?? null)
                                                ->filter()
                                                ->values();
                                        @endphp
                                        @if (!$rowLabels->isEmpty())
                                            <div class="q-attach-indicator small mt-1 "
                                                data-media-id="{{ $item->id }}">
                                                <span
                                                    class="badge bg-success text-info-emphasis border border-info-subtle text-white">
                                                    <i class="fas fa-forward me-1"></i>Before
                                                    {{ $rowLabels->implode(', ') }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="q-attach-indicator small mt-1">
                                                <span
                                                    class="badge bg-warning text-info-emphasis border border-info-subtle text-dark">
                                                    No questions attached yet.
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    @if ($hasQuizzes)
                                        <button type="button" class="btn btn-sm btn-outline-primary open-q-modal-btn"
                                            data-media-id="{{ $item->id }}"
                                            data-media-title="{{ $item->title ?? $item->original_name }}"
                                            title="Attach before a question">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-danger detach-btn"
                                        data-media-id="{{ $item->id }}" title="Detach">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                        <div id="attached-empty-state"
                            class="text-center text-muted p-4 {{ $attachedCount ? 'd-none' : '' }}">
                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                            <div>No media attached yet.</div>
                            <small>Click <strong>Attach</strong> on the left to add items.</small>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-flag-checkered me-2"></i>After all questions
                            <span class="badge bg-light text-dark ms-1"
                                id="end-attached-count">{{ $endAttachedCount }}</span>
                        </h5>
                        <small class="opacity-75">
                            <i class="fas fa-grip-vertical me-1"></i>Drag to reorder
                        </small>
                    </div>
                    <div class="card-body p-0" style="max-height: 38vh; overflow-y: auto;">
                        <ul class="list-group list-group-flush" id="end-attached-list">
                            @foreach ($liveShow->endMedia as $item)
                                <li class="list-group-item end-attached-row d-flex align-items-center gap-2 px-3 py-2"
                                    data-media-id="{{ $item->id }}">
                                    <span class="drag-handle text-muted" title="Drag to reorder">
                                        <i class="fas fa-grip-vertical"></i>
                                    </span>
                                    <span class="row-index badge bg-secondary">{{ $loop->iteration }}</span>
                                    @if ($item->isImage())
                                        <img src="{{ $item->path }}" class="attached-thumb" alt="">
                                    @else
                                        <div class="attached-thumb-wrap">
                                            <img src="{{ $item->thumbnail ?? $item->path }}" class="attached-thumb" alt="">
                                            <span class="attached-thumb-badge"><i class="fas fa-play"></i></span>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="attached-title text-truncate"
                                            title="{{ $item->title ?? $item->original_name }}">
                                            {{ $item->title ?? $item->original_name }}
                                        </div>
                                        <div class="attached-sub small text-muted">
                                            {{ ucfirst($item->type) }}
                                            @if ($item->total_seconds)
                                                · {{ gmdate('i:s', (int) $item->total_seconds) }}
                                            @endif
                                        </div>
                                        <span class="badge bg-success mt-1">Plays after last question</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger end-detach-btn"
                                        data-media-id="{{ $item->id }}" title="Detach">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                        <div id="end-attached-empty-state"
                            class="text-center text-muted p-4 {{ $endAttachedCount ? 'd-none' : '' }}">
                            <i class="fas fa-flag-checkered fa-2x mb-2 d-block opacity-50"></i>
                            <div>No end media yet.</div>
                            <small>Use <strong>Attach at end</strong> on the left after all questions.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ───────────────────  Modal — Attach media before a question  ─────────────────── --}}
    <div class="modal fade" id="question-attach-modal" tabindex="-1" aria-labelledby="question-attach-modal-label"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="question-attach-modal-label">
                        <i class="fas fa-list-ol me-2"></i>Attach before a question
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 p-2 rounded bg-light border small">
                        <span class="text-muted">Media:</span>
                        <strong id="qam-media-name">—</strong>
                        <div class="text-muted mt-1">
                            Pick the questions this media should play <strong>before</strong>. Click again to detach.
                        </div>
                    </div>
                    <div class="list-group" id="qam-question-list"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* ── Left pane: media tile cards ───────────────────────────── */
            .media-card {
                transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
                border: 1px solid rgba(0, 0, 0, .08);
                cursor: default;
            }

            .media-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 18px rgba(0, 0, 0, .08);
            }

            .media-card.is-attached {
                border-color: #198754;
                background: #f0fdf4;
            }

            .media-thumb-wrapper {
                position: relative;
                width: 100%;
                height: 180px;
                aspect-ratio: 16 / 10;
                background: #111;
                overflow: hidden;
                border-top-left-radius: .375rem;
                border-top-right-radius: .375rem;
            }

            .media-thumb {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            .media-type-icon {
                position: absolute;
                inset: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-size: 28px;
                text-shadow: 0 2px 8px rgba(0, 0, 0, .6);
                pointer-events: none;
                opacity: .9;
            }

            .media-type-badge {
                position: absolute;
                bottom: 6px;
                right: 6px;
                background: rgba(0, 0, 0, .7);
                color: #fff;
                font-size: 11px;
                padding: 2px 6px;
                border-radius: 3px;
                line-height: 1.4;
            }

            .media-title {
                font-size: 13px;
                font-weight: 500;
                color: #000;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .attach-btn {
                background: #0d6efd;
                color: #fff;
                border: none;
            }

            .attach-btn:hover:not(:disabled) {
                background: #0b5ed7;
                color: #fff;
            }

            .attach-btn:disabled {
                background: #198754;
                color: #fff;
                opacity: 1;
                cursor: default;
            }

            /* ── Right pane: attached rows ─────────────────────────────── */
            .attached-row {
                background: #fff;
                transition: background .12s ease;
            }

            .attached-row:hover {
                background: #f8fafc;
            }

            .drag-handle {
                cursor: grab;
                font-size: 14px;
                padding: 0 4px;
            }

            .drag-handle:active {
                cursor: grabbing;
            }

            .row-index {
                min-width: 26px;
            }

            .attached-thumb {
                width: 56px;
                height: 36px;
                object-fit: cover;
                border-radius: 4px;
                background: #111;
                flex: 0 0 auto;
            }

            .attached-thumb-wrap {
                position: relative;
                flex: 0 0 auto;
            }

            .attached-thumb-badge {
                position: absolute;
                inset: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-size: 12px;
                pointer-events: none;
                text-shadow: 0 1px 3px rgba(0, 0, 0, .6);
            }

            .attached-title {
                font-size: 13px;
                font-weight: 500;
                color: #111827;
            }

            .min-w-0 {
                min-width: 0;
            }

            /* SortableJS visuals */
            .sortable-ghost {
                opacity: .35;
                background: #e0f2fe !important;
            }

            .sortable-chosen {
                background: #f0f9ff !important;
            }

            .sortable-drag {
                background: #fff !important;
                box-shadow: 0 8px 22px rgba(0, 0, 0, .15);
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
        <script>
            (function() {
                const root = document.getElementById('gallery-attach-root');
                const liveShowId = parseInt(root.dataset.liveShowId, 10);
                const attachUrl = root.dataset.attachUrl;
                const detachUrl = root.dataset.detachUrl;
                const reorderUrl = root.dataset.reorderUrl;
                const reorderEndUrl = root.dataset.reorderEndUrl;
                const attachEndUrl = root.dataset.attachEndUrl;
                const detachEndUrl = root.dataset.detachEndUrl;
                const csrfToken = root.dataset.csrf;
                const hasQuizzes = @json($hasQuizzes);

                const allGrid = document.getElementById('all-media-grid');
                const attachedList = document.getElementById('attached-list');
                const endAttachedList = document.getElementById('end-attached-list');
                const attachedCountEl = document.getElementById('attached-count');
                const endAttachedCountEl = document.getElementById('end-attached-count');
                const attachedEmptyEl = document.getElementById('attached-empty-state');
                const endAttachedEmptyEl = document.getElementById('end-attached-empty-state');
                const searchEmptyEl = document.getElementById('search-empty-state');
                const searchInput = document.getElementById('media-search');
                const typeRadios = document.querySelectorAll('input[name="type-filter"]');

                // ── HELPERS ──────────────────────────────────────────────
                function jsonHeaders() {
                    return {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    };
                }

                function refreshIndices() {
                    if (!attachedList) return;
                    const rows = attachedList.querySelectorAll('.attached-row');
                    rows.forEach((row, i) => {
                        const idx = row.querySelector('.row-index');
                        if (idx) idx.textContent = i + 1;
                    });
                    attachedCountEl.textContent = rows.length;
                    attachedEmptyEl.classList.toggle('d-none', rows.length > 0);
                }

                function applyFilters() {
                    if (!allGrid) return;
                    const q = (searchInput.value || '').trim().toLowerCase();
                    const type = document.querySelector('input[name="type-filter"]:checked').value;
                    let visible = 0;

                    allGrid.querySelectorAll('.media-tile').forEach(tile => {
                        const haystack = tile.dataset.mediaSearch || '';
                        const t = tile.dataset.mediaType;
                        const matchesText = !q || haystack.includes(q);
                        const matchesType = type === 'all' || t === type;
                        const show = matchesText && matchesType;
                        tile.classList.toggle('d-none', !show);
                        if (show) visible++;
                    });
                    if (searchEmptyEl) searchEmptyEl.classList.toggle('d-none', visible > 0);
                }

                function refreshEndIndices() {
                    if (!endAttachedList) return;
                    const rows = endAttachedList.querySelectorAll('.end-attached-row');
                    rows.forEach((row, i) => {
                        const idx = row.querySelector('.row-index');
                        if (idx) idx.textContent = i + 1;
                    });
                    if (endAttachedCountEl) endAttachedCountEl.textContent = rows.length;
                    if (endAttachedEmptyEl) endAttachedEmptyEl.classList.toggle('d-none', rows.length > 0);
                }

                function setTileEndAttached(mediaId, isAttached) {
                    const tile = allGrid && allGrid.querySelector('.media-tile[data-media-id="' + mediaId + '"]');
                    if (!tile) return;
                    const btn = tile.querySelector('.attach-end-btn');
                    if (btn) {
                        btn.disabled = isAttached;
                        btn.dataset.attached = isAttached ? '1' : '0';
                        btn.classList.toggle('btn-success', isAttached);
                        btn.classList.toggle('btn-outline-success', !isAttached);
                        btn.innerHTML = isAttached
                            ? '<i class="fas fa-check me-1"></i> At end'
                            : '<i class="fas fa-flag-checkered me-1"></i> Attach at end';
                    }
                }

                function buildEndAttachedRow(media) {
                    const li = document.createElement('li');
                    li.className = 'list-group-item end-attached-row d-flex align-items-center gap-2 px-3 py-2';
                    li.dataset.mediaId = media.id;

                    const isImage = media.type === 'image';
                    const thumb = media.thumbnail || media.url || media.path || '';
                    const title = media.title || media.original_name || ('Media #' + media.id);
                    let dur = '';
                    if (media.total_seconds) {
                        const m = Math.floor(media.total_seconds / 60).toString().padStart(2, '0');
                        const s = Math.floor(media.total_seconds % 60).toString().padStart(2, '0');
                        dur = ' · ' + m + ':' + s;
                    }
                    const sub = (media.type ? media.type.charAt(0).toUpperCase() + media.type.slice(1) : '') + dur;

                    li.innerHTML = `
                        <span class="drag-handle text-muted" title="Drag to reorder">
                            <i class="fas fa-grip-vertical"></i>
                        </span>
                        <span class="row-index badge bg-secondary">0</span>
                        ${isImage
                            ? `<img src="${escapeAttr(thumb)}" class="attached-thumb" alt="">`
                            : `<div class="attached-thumb-wrap">
                                  <img src="${escapeAttr(thumb)}" class="attached-thumb" alt="">
                                  <span class="attached-thumb-badge"><i class="fas fa-play"></i></span>
                               </div>`}
                        <div class="flex-grow-1 min-w-0">
                            <div class="attached-title text-truncate" title="${escapeAttr(title)}">${escapeHtml(title)}</div>
                            <div class="attached-sub small text-muted">${escapeHtml(sub)}</div>
                            <span class="badge bg-success mt-1">Plays after last question</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger end-detach-btn"
                            data-media-id="${media.id}" title="Detach">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    return li;
                }

                function attachEndMedia(mediaId, btn) {
                    if (btn) btn.disabled = true;
                    fetch(attachEndUrl, {
                        method: 'POST',
                        headers: jsonHeaders(),
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            live_show_id: liveShowId,
                            gallery_media_id: parseInt(mediaId, 10),
                        }),
                    }).then(r => r.json()).then(data => {
                        if (!data || !data.success) {
                            window.alert((data && data.message) || 'Failed to attach at end.');
                            if (btn) btn.disabled = false;
                            return;
                        }
                        const tile = allGrid.querySelector('.media-tile[data-media-id="' + mediaId + '"]');
                        const titleEl = tile ? tile.querySelector('.media-title') : null;
                        const type = tile ? tile.dataset.mediaType : 'image';
                        const thumbEl = tile ? tile.querySelector('.media-thumb') : null;
                        const media = {
                            id: parseInt(mediaId, 10),
                            type: type,
                            title: titleEl ? titleEl.textContent.trim() : 'Media #' + mediaId,
                            thumbnail: thumbEl ? thumbEl.getAttribute('src') : '',
                        };
                        if (endAttachedList) {
                            endAttachedList.appendChild(buildEndAttachedRow(media));
                        }
                        setTileEndAttached(mediaId, true);
                        refreshEndIndices();
                        persistEndOrder();
                    }).catch(err => {
                        console.error('Attach end error:', err);
                        window.alert('An error occurred.');
                        if (btn) btn.disabled = false;
                    });
                }

                function detachEndMedia(mediaId, btn) {
                    if (btn) btn.disabled = true;
                    fetch(detachEndUrl, {
                        method: 'POST',
                        headers: jsonHeaders(),
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            live_show_id: liveShowId,
                            gallery_media_id: parseInt(mediaId, 10),
                        }),
                    }).then(r => r.json()).then(data => {
                        if (!data || !data.success) {
                            window.alert((data && data.message) || 'Failed to detach.');
                            if (btn) btn.disabled = false;
                            return;
                        }
                        const row = endAttachedList && endAttachedList.querySelector('.end-attached-row[data-media-id="' + mediaId + '"]');
                        if (row) row.remove();
                        setTileEndAttached(mediaId, false);
                        refreshEndIndices();
                    }).catch(err => {
                        console.error('Detach end error:', err);
                        window.alert('An error occurred.');
                        if (btn) btn.disabled = false;
                    });
                }

                function persistEndOrder() {
                    if (!endAttachedList) return;
                    const rows = endAttachedList.querySelectorAll('.end-attached-row');
                    const order = Array.from(rows).map(r => parseInt(r.dataset.mediaId, 10)).filter(Boolean);
                    if (order.length === 0) return;
                    fetch(reorderEndUrl, {
                        method: 'POST',
                        headers: jsonHeaders(),
                        credentials: 'same-origin',
                        body: JSON.stringify({ live_show_id: liveShowId, order: order }),
                    }).then(r => r.json()).then(data => {
                        if (!data || !data.success) console.warn('End reorder failed:', data);
                    }).catch(err => console.error('End reorder error:', err));
                }

                // ── LEFT TILE STATE ──────────────────────────────────────
                function setTileAttached(mediaId, isAttached) {
                    const tile = allGrid && allGrid.querySelector('.media-tile[data-media-id="' + mediaId + '"]');
                    if (!tile) return;
                    const card = tile.querySelector('.media-card');
                    const btn = tile.querySelector('.attach-btn');
                    if (card) card.classList.toggle('is-attached', isAttached);
                    if (btn) {
                        btn.disabled = isAttached;
                        btn.innerHTML = isAttached ?
                            '<i class="fas fa-check me-1"></i> Attached' :
                            '<i class="fas fa-plus me-1"></i> Attach';
                    }
                }

                // ── BUILD an attached row from server-returned media ────
                function buildAttachedRow(media) {
                    const li = document.createElement('li');
                    li.className = 'list-group-item attached-row d-flex align-items-center gap-2 px-3 py-2';
                    li.dataset.mediaId = media.id;

                    const isImage = media.type === 'image';
                    const thumb = media.thumbnail || media.url || media.path || '';
                    const title = media.title || media.original_name || ('Media #' + media.id);
                    let dur = '';
                    if (media.total_seconds) {
                        const m = Math.floor(media.total_seconds / 60).toString().padStart(2, '0');
                        const s = Math.floor(media.total_seconds % 60).toString().padStart(2, '0');
                        dur = ' · ' + m + ':' + s;
                    }
                    const sub = (media.type ? media.type.charAt(0).toUpperCase() + media.type.slice(1) : '') + dur;

                    li.innerHTML = `
                        <span class="drag-handle text-muted" title="Drag to reorder">
                            <i class="fas fa-grip-vertical"></i>
                        </span>
                        <span class="row-index badge bg-secondary">0</span>
                        ${isImage
                            ? `<img src="${escapeAttr(thumb)}" class="attached-thumb" alt="">`
                            : `<div class="attached-thumb-wrap">
                                          <img src="${escapeAttr(thumb)}" class="attached-thumb" alt="">
                                          <span class="attached-thumb-badge"><i class="fas fa-play"></i></span>
                                       </div>`}
                        <div class="flex-grow-1 min-w-0">
                            <div class="attached-title text-truncate" title="${escapeAttr(title)}">${escapeHtml(title)}</div>
                            <div class="attached-sub small text-muted">${escapeHtml(sub)}</div>
                            <div class="q-attach-indicator small mt-1 d-none" data-media-id="${media.id}">
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle"></span>
                            </div>
                        </div>
                        ${hasQuizzes ? `<button type="button" class="btn btn-sm btn-outline-primary open-q-modal-btn"
                                        data-media-id="${media.id}" data-media-title="${escapeAttr(title)}" title="Attach before a question">
                                    <i class="fas fa-list-ol"></i>
                                </button>` : ''}
                        <button type="button" class="btn btn-sm btn-outline-danger detach-btn"
                                data-media-id="${media.id}" title="Detach">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    return li;
                }

                function escapeHtml(s) {
                    return String(s == null ? '' : s)
                        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                }

                function escapeAttr(s) {
                    return escapeHtml(s);
                }

                // ── ATTACH / DETACH ──────────────────────────────────────
                function attachMedia(mediaId, btn) {
                    if (btn) btn.disabled = true;
                    fetch(attachUrl, {
                        method: 'POST',
                        headers: jsonHeaders(),
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            live_show_id: liveShowId,
                            gallery_media_id: parseInt(mediaId, 10),
                        }),
                    }).then(r => r.json()).then(data => {
                        if (!data || !data.success) {
                            window.alert((data && data.message) || 'Failed to attach.');
                            if (btn) btn.disabled = false;
                            return;
                        }

                        // Build a row using info pulled from the left tile (no extra request).
                        const tile = allGrid.querySelector('.media-tile[data-media-id="' + mediaId + '"]');
                        const titleEl = tile ? tile.querySelector('.media-title') : null;
                        const type = tile ? tile.dataset.mediaType : 'image';
                        const thumbEl = tile ? tile.querySelector('.media-thumb') : null;

                        const media = {
                            id: parseInt(mediaId, 10),
                            type: type,
                            title: titleEl ? titleEl.textContent.trim() : 'Media #' + mediaId,
                            thumbnail: thumbEl ? thumbEl.getAttribute('src') : '',
                        };

                        const row = buildAttachedRow(media);
                        attachedList.appendChild(row);
                        setTileAttached(mediaId, true);
                        if (window.refreshQuestionIndicators) window.refreshQuestionIndicators(mediaId);
                        refreshIndices();
                        persistOrder();
                    }).catch(err => {
                        console.error('Attach error:', err);
                        window.alert('An error occurred.');
                        if (btn) btn.disabled = false;
                    });
                }

                function detachMedia(mediaId, btn) {
                    if (btn) btn.disabled = true;
                    fetch(detachUrl, {
                        method: 'POST',
                        headers: jsonHeaders(),
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            live_show_id: liveShowId,
                            gallery_media_id: parseInt(mediaId, 10),
                        }),
                    }).then(r => r.json()).then(data => {
                        if (!data || !data.success) {
                            window.alert((data && data.message) || 'Failed to detach.');
                            if (btn) btn.disabled = false;
                            return;
                        }
                        const row = attachedList.querySelector('.attached-row[data-media-id="' + mediaId + '"]');
                        if (row) row.remove();
                        setTileAttached(mediaId, false);
                        refreshIndices();
                    }).catch(err => {
                        console.error('Detach error:', err);
                        window.alert('An error occurred.');
                        if (btn) btn.disabled = false;
                    });
                }

                // ── REORDER (POST array of IDs in new order) ─────────────
                function persistOrder() {
                    const rows = attachedList.querySelectorAll('.attached-row');
                    const order = Array.from(rows).map(r => parseInt(r.dataset.mediaId, 10)).filter(Boolean);
                    if (order.length === 0) return;

                    fetch(reorderUrl, {
                        method: 'POST',
                        headers: jsonHeaders(),
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            live_show_id: liveShowId,
                            order: order,
                        }),
                    }).then(r => r.json()).then(data => {
                        if (!data || !data.success) {
                            console.warn('Reorder failed:', data);
                        }
                    }).catch(err => console.error('Reorder error:', err));
                }

                // ── EVENT WIRING ─────────────────────────────────────────
                if (allGrid) {
                    allGrid.addEventListener('click', (e) => {
                        const btn = e.target.closest('.attach-btn');
                        if (btn && !btn.disabled) {
                            attachMedia(btn.dataset.mediaId, btn);
                            return;
                        }
                        const endBtn = e.target.closest('.attach-end-btn');
                        if (endBtn && !endBtn.disabled && endBtn.dataset.attached !== '1') {
                            attachEndMedia(endBtn.dataset.mediaId, endBtn);
                        }
                    });
                }

                if (attachedList) {
                    attachedList.addEventListener('click', (e) => {
                        const btn = e.target.closest('.detach-btn');
                        if (btn) detachMedia(btn.dataset.mediaId, btn);
                    });

                    new Sortable(attachedList, {
                        handle: '.drag-handle',
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        onEnd: () => {
                            refreshIndices();
                            persistOrder();
                        },
                    });
                }

                if (endAttachedList) {
                    endAttachedList.addEventListener('click', (e) => {
                        const btn = e.target.closest('.end-detach-btn');
                        if (btn) detachEndMedia(btn.dataset.mediaId, btn);
                    });

                    new Sortable(endAttachedList, {
                        handle: '.drag-handle',
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        onEnd: () => {
                            refreshEndIndices();
                            persistEndOrder();
                        },
                    });
                }

                if (searchInput) searchInput.addEventListener('input', applyFilters);
                typeRadios.forEach(r => r.addEventListener('change', applyFilters));

                // Initial pass
                refreshIndices();
                refreshEndIndices();
                applyFilters();
            })();

            // ── Attach media BEFORE a specific question (modal) ───────
            (function() {
                const root = document.getElementById('gallery-attach-root');
                if (!root) return;
                const liveShowId = parseInt(root.dataset.liveShowId, 10);
                const attachQUrl = root.dataset.attachQuestionUrl;
                const detachQUrl = root.dataset.detachQuestionUrl;
                const csrfToken = root.dataset.csrf;

                // Questions of this show, in display order: [{id, label, text}]
                const showQuestions = @json($showQuestionsForJs);
                const hasQuizzes = @json($hasQuizzes);
                // mediaId => [quizId, ...]
                const questionAttachments = @json($questionAttachments);

                const modalEl = document.getElementById('question-attach-modal');
                const listEl = document.getElementById('qam-question-list');
                const nameEl = document.getElementById('qam-media-name');
                let currentMediaId = null;

                function headers() {
                    return {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    };
                }

                function attachedListFor(mediaId) {
                    const arr = questionAttachments[mediaId];
                    return Array.isArray(arr) ? arr.map(Number) : [];
                }

                function labelsFor(mediaId) {
                    const ids = attachedListFor(mediaId);
                    return showQuestions.filter(q => ids.includes(q.id)).map(q => q.label);
                }

                // Keep the count badge (left tile) + "Before Q.." indicator (right row) in sync.
                window.refreshQuestionIndicators = function(mediaId) {
                    mediaId = parseInt(mediaId, 10);
                    const count = attachedListFor(mediaId).length;
                    const labels = labelsFor(mediaId);

                    document.querySelectorAll('.media-tile[data-media-id="' + mediaId + '"] .q-count-badge')
                        .forEach(badge => {
                            badge.textContent = count;
                            badge.classList.toggle('d-none', count === 0);
                        });

                    document.querySelectorAll('.q-attach-indicator[data-media-id="' + mediaId + '"]')
                        .forEach(ind => {
                            const badge = ind.querySelector('.badge');
                            if (badge) {
                                badge.innerHTML = '<i class="fas fa-forward me-1"></i>Before ' + labels.join(', ');
                            }
                            ind.classList.toggle('d-none', count === 0);
                        });
                };

                function renderModal(mediaId, mediaTitle) {
                    currentMediaId = parseInt(mediaId, 10);
                    if (nameEl) nameEl.textContent = mediaTitle || ('Media #' + mediaId);
                    const ids = attachedListFor(currentMediaId);
                    listEl.innerHTML = showQuestions.map(function(q) {
                        const isAttached = ids.includes(q.id);
                        return `
                            <button type="button"
                                class="list-group-item list-group-item-action d-flex align-items-center gap-2 qam-question-btn ${isAttached ? 'active' : ''}"
                                data-quiz-id="${q.id}" data-attached="${isAttached ? '1' : '0'}">
                                <span class="badge ${isAttached ? 'bg-light text-dark' : 'bg-dark'}">${escapeHtmlSafe(q.label)}</span>
                                <span class="flex-grow-1 text-start text-truncate" title="${escapeHtmlSafe(q.text)}">${escapeHtmlSafe(q.text)}</span>
                                <span class="badge ${isAttached ? 'bg-success' : 'bg-secondary'} qam-state">
                                    ${isAttached ? '<i class="fas fa-check me-1"></i>Attached' : 'Attach'}
                                </span>
                            </button>`;
                    }).join('');
                }

                function escapeHtmlSafe(s) {
                    return String(s == null ? '' : s)
                        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                }

                function openModal(mediaId, mediaTitle) {
                    renderModal(mediaId, mediaTitle);
                    if (typeof bootstrap !== 'undefined' && modalEl) {
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    }
                }

                // Open the modal from either pane (left tiles or attached rows).
                document.addEventListener('click', function(e) {
                    const opener = e.target.closest('.open-q-modal-btn');
                    if (!opener) return;
                    openModal(opener.dataset.mediaId, opener.dataset.mediaTitle);
                });

                // Toggle attach/detach inside the modal.
                if (listEl) {
                    listEl.addEventListener('click', function(e) {
                        const btn = e.target.closest('.qam-question-btn');
                        if (!btn || currentMediaId == null) return;

                        const attached = btn.dataset.attached === '1';
                        const quizId = parseInt(btn.dataset.quizId, 10);
                        const url = attached ? detachQUrl : attachQUrl;
                        btn.classList.add('disabled');

                        fetch(url, {
                            method: 'POST',
                            headers: headers(),
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                live_show_id: liveShowId,
                                quiz_id: quizId,
                                gallery_media_id: currentMediaId,
                            }),
                        }).then(r => r.json()).then(data => {
                            if (!data || !data.success) {
                                window.alert((data && data.message) || 'Action failed.');
                                btn.classList.remove('disabled');
                                return;
                            }
                            const nowAttached = !attached;
                            // Update in-memory map.
                            let ids = attachedListFor(currentMediaId);
                            if (nowAttached) {
                                if (!ids.includes(quizId)) ids.push(quizId);
                            } else {
                                ids = ids.filter(id => id !== quizId);
                            }
                            questionAttachments[currentMediaId] = ids;

                            // Update the modal button.
                            btn.dataset.attached = nowAttached ? '1' : '0';
                            btn.classList.toggle('active', nowAttached);
                            const state = btn.querySelector('.qam-state');
                            if (state) {
                                state.className = 'badge qam-state ' + (nowAttached ? 'bg-success' :
                                    'bg-secondary');
                                state.innerHTML = nowAttached ?
                                    '<i class="fas fa-check me-1"></i>Attached' : 'Attach';
                            }
                            const lbl = btn.querySelector('.badge');
                            if (lbl) lbl.className = 'badge ' + (nowAttached ? 'bg-light text-dark' :
                                'bg-dark');

                            window.refreshQuestionIndicators(currentMediaId);
                            btn.classList.remove('disabled');
                        }).catch(err => {
                            console.error('Question attach error:', err);
                            window.alert('An error occurred.');
                            btn.classList.remove('disabled');
                        });
                    });
                }
            })();
        </script>
    @endpush
</x-app-dashboard-layout>
