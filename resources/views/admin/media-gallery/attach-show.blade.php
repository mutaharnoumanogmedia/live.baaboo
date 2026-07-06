<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attach to Live Show') }}
        </h2>
    </x-slot>

    @php
        $showQuestionsByShowId = [];
        foreach ($liveShows as $show) {
            $showQuestionsByShowId[$show->id] = $show->quizzes->values()->map(function ($q, $i) {
                return [
                    'id' => (int) $q->id,
                    'label' => 'Q' . ($i + 1),
                    'text' => $q->question,
                ];
            })->values()->all();
        }
    @endphp

    <div class="container-fluid py-4" id="attach-show-root"
        data-media-id="{{ $media->id }}"
        data-attach-show-url="{{ route('admin.media-gallery.attach-to-live-show') }}"
        data-detach-show-url="{{ route('admin.media-gallery.detach-from-live-show') }}"
        data-attach-question-url="{{ route('admin.media-gallery.attach-to-question') }}"
        data-detach-question-url="{{ route('admin.media-gallery.detach-from-question') }}"
        data-attach-end-url="{{ route('admin.media-gallery.attach-to-end') }}"
        data-detach-end-url="{{ route('admin.media-gallery.detach-from-end') }}"
        data-csrf="{{ csrf_token() }}">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        @if ($media->isImage())
                            <img src="{{ $media->url }}" alt="{{ $media->title }}" class="img-fluid rounded">
                        @else
                            <video src="{{ $media->url }}" class="img-fluid rounded" style="max-height: 200px;"
                                muted></video>
                        @endif
                        <p class="mt-2 mb-0">{{ $media->title ?: $media->original_name }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Attach this media to a show first, then optionally before a specific question</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped table-light align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Title</th>
                                        <th scope="col">Scheduled At</th>
                                        <th scope="col" class="text-center" style="width: 320px;">Attach</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($liveShows as $show)
                                        @php
                                            $isShowAttached = in_array($show->id, $attachedIds);
                                            $isEndAttached = in_array($show->id, $attachedEndShowIds ?? []);
                                            $showQuizIds = collect($showQuestionsByShowId[$show->id] ?? [])->pluck('id')->all();
                                            $attachedInShow = count(array_intersect($showQuizIds, $attachedQuestionIds));
                                        @endphp
                                        <tr>
                                            <td>{{ $show->title }}</td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $show->scheduled_at?->format('M j, Y') }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center">
                                                    <button type="button"
                                                        class="btn btn-sm attach-show-btn {{ $isShowAttached ? 'btn-success' : 'btn-primary' }}"
                                                        data-live-show-id="{{ $show->id }}"
                                                        data-attached="{{ $isShowAttached ? '1' : '0' }}">
                                                        @if ($isShowAttached)
                                                            <i class="fas fa-check me-1"></i> Attached to show
                                                        @else
                                                            <i class="fas fa-tv me-1"></i> Attach to show
                                                        @endif
                                                    </button>

                                                    @if ($show->quizzes->isNotEmpty())
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-dark open-q-modal-btn {{ $isShowAttached ? '' : 'd-none' }}"
                                                            data-live-show-id="{{ $show->id }}"
                                                            data-show-title="{{ $show->title }}"
                                                            data-show-date="{{ $show->scheduled_at?->format('M j, Y') }}">
                                                            <i class="fas fa-list-ol me-1"></i> Attach before question
                                                            <span class="badge bg-info q-show-count {{ $attachedInShow ? '' : 'd-none' }}"
                                                                data-live-show-id="{{ $show->id }}">{{ $attachedInShow }}</span>
                                                        </button>
                                                    @endif
                                                    <button type="button"
                                                        class="btn btn-sm attach-end-btn {{ $isEndAttached ? 'btn-success' : 'btn-outline-success' }} {{ $isShowAttached ? '' : 'd-none' }}"
                                                        data-live-show-id="{{ $show->id }}"
                                                        data-attached="{{ $isEndAttached ? '1' : '0' }}">
                                                        @if ($isEndAttached)
                                                            <i class="fas fa-check me-1"></i> At end of questions
                                                        @else
                                                            <i class="fas fa-flag-checkered me-1"></i> Attach at end
                                                        @endif
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-muted text-center">No live shows found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('admin.media-gallery.index') }}" class="btn btn-secondary">
                                Back to Gallery
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal — pick question(s) to attach media before --}}
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
                        <div><span class="text-muted">Show:</span> <strong id="qam-show-name">—</strong></div>
                        <div class="text-muted" id="qam-show-date"></div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const root = document.getElementById('attach-show-root');
            const mediaId = parseInt(root.dataset.mediaId, 10);
            const csrf = root.dataset.csrf;

            const showQuestionsByShowId = @json($showQuestionsByShowId);
            let attachedQuestionIds = @json($attachedQuestionIds);

            const modalEl = document.getElementById('question-attach-modal');
            const listEl = document.getElementById('qam-question-list');
            const showNameEl = document.getElementById('qam-show-name');
            const showDateEl = document.getElementById('qam-show-date');
            let currentShowId = null;

            function post(url, body) {
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(body),
                }).then(r => r.json());
            }

            function escapeHtml(s) {
                return String(s == null ? '' : s)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            }

            function isQuizAttached(quizId) {
                return attachedQuestionIds.includes(parseInt(quizId, 10));
            }

            function updateShowCountBadge(showId) {
                const questions = showQuestionsByShowId[showId] || [];
                const count = questions.filter(q => isQuizAttached(q.id)).length;
                const badge = root.querySelector('.q-show-count[data-live-show-id="' + showId + '"]');
                if (badge) {
                    badge.textContent = count;
                    badge.classList.toggle('d-none', count === 0);
                }
            }

            function setQuestionBtnVisible(showId, visible) {
                const qBtn = root.querySelector('.open-q-modal-btn[data-live-show-id="' + showId + '"]');
                if (qBtn) {
                    qBtn.classList.toggle('d-none', !visible);
                }
                const endBtn = root.querySelector('.attach-end-btn[data-live-show-id="' + showId + '"]');
                if (endBtn) {
                    endBtn.classList.toggle('d-none', !visible);
                }
            }

            function renderModal(showId, showTitle, showDate) {
                currentShowId = parseInt(showId, 10);
                if (showNameEl) showNameEl.textContent = showTitle || ('Show #' + showId);
                if (showDateEl) showDateEl.textContent = showDate ? ('Scheduled: ' + showDate) : '';

                const questions = showQuestionsByShowId[currentShowId] || [];
                listEl.innerHTML = questions.length === 0
                    ? '<div class="text-muted text-center py-3">No questions in this show.</div>'
                    : questions.map(function(q) {
                        const isAttached = isQuizAttached(q.id);
                        return `
                            <button type="button"
                                class="list-group-item list-group-item-action d-flex align-items-center gap-2 qam-question-btn ${isAttached ? 'active' : ''}"
                                data-quiz-id="${q.id}" data-attached="${isAttached ? '1' : '0'}">
                                <span class="badge ${isAttached ? 'bg-light text-dark' : 'bg-dark'}">${escapeHtml(q.label)}</span>
                                <span class="flex-grow-1 text-start text-truncate" title="${escapeHtml(q.text)}">${escapeHtml(q.text)}</span>
                                <span class="badge ${isAttached ? 'bg-success' : 'bg-secondary'} qam-state">
                                    ${isAttached ? '<i class="fas fa-check me-1"></i>Attached before ' + escapeHtml(q.label) : 'Attach before this question'}
                                </span>
                            </button>`;
                    }).join('');
            }

            function openModal(btn) {
                renderModal(btn.dataset.liveShowId, btn.dataset.showTitle, btn.dataset.showDate);
                if (typeof bootstrap !== 'undefined' && modalEl) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            }

            root.querySelectorAll('.open-q-modal-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (btn.disabled) return;
                    openModal(btn);
                });
            });

            if (listEl) {
                listEl.addEventListener('click', function(e) {
                    const btn = e.target.closest('.qam-question-btn');
                    if (!btn || currentShowId == null) return;

                    const attached = btn.dataset.attached === '1';
                    const quizId = parseInt(btn.dataset.quizId, 10);
                    const url = attached ? root.dataset.detachQuestionUrl : root.dataset.attachQuestionUrl;
                    btn.classList.add('disabled');

                    post(url, {
                        live_show_id: currentShowId,
                        quiz_id: quizId,
                        gallery_media_id: mediaId,
                    }).then(function(data) {
                        if (!data || !data.success) {
                            alert((data && data.message) || 'Action failed.');
                            btn.classList.remove('disabled');
                            return;
                        }

                        const nowAttached = !attached;
                        if (nowAttached) {
                            if (!attachedQuestionIds.includes(quizId)) attachedQuestionIds.push(quizId);
                        } else {
                            attachedQuestionIds = attachedQuestionIds.filter(id => id !== quizId);
                        }

                        const q = (showQuestionsByShowId[currentShowId] || []).find(x => x.id === quizId);
                        const label = q ? q.label : '';

                        btn.dataset.attached = nowAttached ? '1' : '0';
                        btn.classList.toggle('active', nowAttached);
                        const state = btn.querySelector('.qam-state');
                        if (state) {
                            state.className = 'badge qam-state ' + (nowAttached ? 'bg-success' : 'bg-secondary');
                            state.innerHTML = nowAttached
                                ? '<i class="fas fa-check me-1"></i>Attached before ' + escapeHtml(label)
                                : 'Attach before this question';
                        }
                        const lbl = btn.querySelector('.badge');
                        if (lbl && !lbl.classList.contains('qam-state')) {
                            lbl.className = 'badge ' + (nowAttached ? 'bg-light text-dark' : 'bg-dark');
                        }

                        updateShowCountBadge(currentShowId);
                        btn.classList.remove('disabled');
                    }).catch(function() {
                        alert('An error occurred.');
                        btn.classList.remove('disabled');
                    });
                });
            }

            // ── Attach / detach at end of questions ─────────────────
            root.querySelectorAll('.attach-end-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const attached = btn.dataset.attached === '1';
                    const url = attached ? root.dataset.detachEndUrl : root.dataset.attachEndUrl;
                    btn.disabled = true;
                    post(url, {
                        live_show_id: parseInt(btn.dataset.liveShowId, 10),
                        gallery_media_id: mediaId,
                    }).then(function(data) {
                        if (!data || !data.success) {
                            alert((data && data.message) || 'Action failed.');
                            btn.disabled = false;
                            return;
                        }
                        const nowAttached = !attached;
                        btn.dataset.attached = nowAttached ? '1' : '0';
                        btn.classList.toggle('btn-success', nowAttached);
                        btn.classList.toggle('btn-outline-success', !nowAttached);
                        btn.innerHTML = nowAttached
                            ? '<i class="fas fa-check me-1"></i> At end of questions'
                            : '<i class="fas fa-flag-checkered me-1"></i> Attach at end';
                        btn.disabled = false;
                    }).catch(function() {
                        alert('An error occurred.');
                        btn.disabled = false;
                    });
                });
            });

            // ── Attach / detach to full show ───────────────────────
            root.querySelectorAll('.attach-show-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const attached = btn.dataset.attached === '1';
                    const url = attached ? root.dataset.detachShowUrl : root.dataset.attachShowUrl;
                    btn.disabled = true;
                    post(url, {
                        live_show_id: parseInt(btn.dataset.liveShowId, 10),
                        gallery_media_id: mediaId,
                    }).then(function(data) {
                        if (!data || !data.success) {
                            alert((data && data.message) || 'Action failed.');
                            btn.disabled = false;
                            return;
                        }
                        const nowAttached = !attached;
                        btn.dataset.attached = nowAttached ? '1' : '0';
                        btn.classList.toggle('btn-success', nowAttached);
                        btn.classList.toggle('btn-primary', !nowAttached);
                        btn.innerHTML = nowAttached
                            ? '<i class="fas fa-check me-1"></i> Attached to show'
                            : '<i class="fas fa-tv me-1"></i> Attach to show';
                        setQuestionBtnVisible(btn.dataset.liveShowId, nowAttached);
                        if (!nowAttached && modalEl && currentShowId === parseInt(btn.dataset.liveShowId, 10)) {
                            const inst = bootstrap.Modal.getInstance(modalEl);
                            if (inst) inst.hide();
                        }
                        btn.disabled = false;
                    }).catch(function() {
                        alert('An error occurred.');
                        btn.disabled = false;
                    });
                });
            });
        });
    </script>
</x-app-dashboard-layout>
