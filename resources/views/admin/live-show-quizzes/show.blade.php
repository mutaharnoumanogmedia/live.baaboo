<x-app-dashboard-layout>
    <div class="container mt-4">
        <h2>Quiz Details</h2>
        <p><strong>Live Show:</strong> {{ $quiz->liveShow->title }}</p>
        <p><strong>Question:</strong> {{ $quiz->question }}</p>

        <h5>Options:</h5>
        <ul class="list-group">
            @foreach ($quiz->options as $opt)
                <li class="list-group-item d-flex justify-content-between">
                    {{ $opt->option_text }}
                    @if ($opt->is_correct)
                        <span class="badge bg-success">Correct</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</x-app-dashboard-layout>
