<x-app-dashboard-layout>
    <div class="container mt-4">
        @if(request('live_show_id'))
            <x-admin.live-show-tabs :live-show-id="request('live_show_id')" active="quiz" />
       
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Quizzes</h2>
            <div class="d-flex gap-2">
                @if(request('live_show_id'))
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

        <table class="table table-borderless table-dark data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th style="width: 180px">Live Show</th>
                    <th>Question</th>
                    <th>Options</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quizzes as $index => $quiz)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <a class="text-warning"
                                href="{{ route('admin.live-shows.show', $quiz->liveShow->id ?? -1) }}">{{ $quiz->liveShow->title ?? 'N/A' }}</a>
                        </td>
                        <td>{{ $quiz->question }}</td>
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
</x-app-dashboard-layout>
