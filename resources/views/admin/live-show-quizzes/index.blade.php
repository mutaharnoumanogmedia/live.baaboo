<x-app-dashboard-layout>
    <div class="container mt-4">
        <div class="d-flex justify-content-between mb-3">
            <h2>Quizzes</h2>
            <a href="{{ route('admin.live-show-quizzes.create', ['live_show_id' => request('live_show_id')]) }}"
                class="btn btn-primary">Add Quiz</a>
        </div>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <table class="table table-borderless table-dark data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th style="width: 200px">Live Show</th>
                    <th>Question</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quizzes as $quiz)
                    <tr>
                        <td>{{ $quiz->id }}</td>
                        <td>
                            <a class="text-warning"
                                href="{{ route('admin.live-shows.show', $quiz->liveShow->id) }}">{{ $quiz->liveShow->title ?? 'N/A' }}</a>
                        </td>
                        <td>{{ $quiz->question }}</td>
                        <td>
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
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</x-app-dashboard-layout>
