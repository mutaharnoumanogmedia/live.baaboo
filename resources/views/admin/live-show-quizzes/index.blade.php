<x-app-dashboard-layout>
    <div class="container mt-4">
        @if (request('live_show_id'))
            <x-admin.live-show-tabs :live-show-id="request('live_show_id')" active="quiz" />
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Quizzes</h2>
            <div class="d-flex gap-2">
                @if (request('live_show_id'))
                    <a href="{{ route('admin.live-shows.view-details', request('live_show_id')) }}"
                        class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Show Details
                    </a>
                @endif
                <a href="{{ route('admin.live-show-quizzes.create', ['live_show_id' => request('live_show_id')]) }}"
                    class="btn btn-primary">Add Quiz</a>
            </div>
        </div>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.live-show-quizzes.index') }}" class="row g-2 align-items-end mb-3">
            <div class="col-md-5">
                <label for="live_show_id" class="form-label mb-1">Filter by Live Show</label>
                <select name="live_show_id" id="live_show_id" class="form-select">
                    <option value="">All Live Shows</option>
                    @foreach ($liveShows as $liveShow)
                        <option value="{{ $liveShow->id }}"
                            {{ (string) request('live_show_id') === (string) $liveShow->id ? 'selected' : '' }}>
                            {{ $liveShow->title }} (ID: {{ $liveShow->id }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary">Apply</button>
                @if (request('live_show_id'))
                    <a href="{{ route('admin.live-show-quizzes.index') }}" class="btn btn-outline-secondary">Clear</a>
                @endif
            </div>
        </form>

        @if (request('live_show_id'))
            <p class="text-muted small mb-2">
                <i class="fas fa-grip-vertical me-1"></i> Drag rows to reorder questions for this live show.
            </p>
        @else
            <p class="text-muted small mb-2">Filter by a live show to enable drag-and-drop reordering.</p>
        @endif

        <table class="table table-borderless table-dark data-table">
            <thead>
                <tr>
                    @if (request('live_show_id'))
                        <th style="width: 40px"></th>
                    @endif
                    <th>#</th>
                    @unless (request('live_show_id'))
                        <th style="width: 180px">Live Show</th>
                    @endunless
                    <th>Question</th>
                    <th>Options</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="quiz-sortable-list">
                @foreach ($quizzes as $index => $quiz)
                    <tr data-quiz-id="{{ $quiz->id }}">
                        @if (request('live_show_id'))
                            <td class="quiz-drag-handle text-muted" style="cursor: grab;" title="Drag to reorder">
                                <i class="fas fa-grip-vertical"></i>
                            </td>
                        @endif
                        <td class="quiz-row-index">{{ $index + 1 }}</td>
                        @unless (request('live_show_id'))
                            <td>
                                <a class="text-warning"
                                    href="{{ route('admin.live-shows.show', $quiz->liveShow->id ?? -1) }}">{{ $quiz->liveShow->title ?? 'N/A' }}</a>
                            </td>
                        @endunless
                        <td>
                            @if ($quiz->is_special)
                                <span class="badge bg-warning text-dark me-1">SPECIAL QUIZ</span>
                            @else
                                <span class="badge bg-info text-dark me-1">MAIN</span>
                            @endif
                            {{ $quiz->question }}
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach ($quiz->options as $option)
                                    @if ($option->is_correct)
                                        <span class="badge bg-success py-1 px-2">
                                            <i class="fas fa-check me-1"></i>{{ $option->option_text }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary py-1 px-2">
                                            {{ $option->option_text }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.live-show-quizzes.show', $quiz->id) }}"
                                    class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('admin.live-show-quizzes.edit', $quiz->id) }}"
                                    class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('admin.live-show-quizzes.destroy', $quiz->id) }}" method="POST"
                                    style="display:inline-block;">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Delete this quiz?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>

    @if (request('live_show_id'))
        <style>
            .sortable-ghost {
                opacity: 0.4;
                background: rgba(255, 255, 255, 0.08);
            }

            .sortable-chosen {
                background: rgba(255, 255, 255, 0.05);
            }

            .quiz-drag-handle:active {
                cursor: grabbing;
            }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tbody = document.getElementById('quiz-sortable-list');
                if (!tbody || typeof Sortable === 'undefined') return;

                const liveShowId = {{ (int) request('live_show_id') }};
                const reorderUrl = @json(route('admin.live-show-quizzes.reorder'));
                const csrfToken = @json(csrf_token());

                function updateRowIndices() {
                    tbody.querySelectorAll('tr').forEach((row, i) => {
                        const cell = row.querySelector('.quiz-row-index');
                        if (cell) cell.textContent = i + 1;
                    });
                }

                function persistOrder() {
                    const order = Array.from(tbody.querySelectorAll('tr[data-quiz-id]'))
                        .map(row => parseInt(row.dataset.quizId, 10));

                    if (order.length === 0) return;

                    fetch(reorderUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                live_show_id: liveShowId,
                                order: order
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (!data.success) {
                                console.error('Quiz reorder failed', data);
                            }
                        })
                        .catch(err => console.error('Quiz reorder error:', err));
                }

                new Sortable(tbody, {
                    handle: '.quiz-drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    onEnd: function() {
                        updateRowIndices();
                        persistOrder();
                    }
                });
            });
        </script>
    @endif
</x-app-dashboard-layout>
