<x-app-dashboard-layout>
    <div class="container-fluid py-4">
        <div class="row mb-3">
            <div class="col-12">
                <a href="{{ route('admin.live-shows.view-details', $liveShow->id) }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Show Details
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Player Quiz Responses</h5>
                        <span>
                            <i class="bi bi-person me-1"></i><strong>Player ID:</strong> {{ $userId }}
                            <span class="ms-3"><i class="bi bi-terminal"></i> <strong>Show:</strong> {{ $liveShow->title ?? 'N/A' }}</span>
                        </span>
                    </div>
                    <div class="card-body bg-dark text-light p-0">
                        @if ($responses->isEmpty())
                            <p class="text-muted m-4">
                                <i class="bi bi-info-circle me-2"></i>No responses found for this player in the selected Live Show.
                            </p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-borderless table-dark align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Question</th>
                                            <th>Selected Option</th>
                                            <th>Correct?</th>
                                            <th>Score</th>
                                            <th>Time (s)</th>
                                            <th>Answered At</th>
                                            <th>Options</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($responses as $i => $response)
                                            <tr>
                                                <td>{{ $i+1 }}</td>
                                                <td>{{ $response['question'] }}</td>
                                                <td>
                                                    <span @if($response['is_correct']) class="text-success" @else class="text-danger" @endif>
                                                        {{ $response['selected_option'] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($response['is_correct'])
                                                        <span class="badge bg-success">Yes</span>
                                                    @else
                                                        <span class="badge bg-danger">No</span>
                                                    @endif
                                                </td>
                                                <td>{{ $response['response_score'] }}</td>
                                                <td>{{ $response['seconds_to_submit'] ?? '-' }}</td>
                                                <td class="text-nowrap">{{ $response['created_at'] }}</td>
                                                <td>
                                                    @if (!empty($response['options']))
                                                        <ul class="mb-0 small">
                                                            @foreach ($response['options'] as $opt)
                                                                <li>
                                                                    {{ $opt['option_text'] }}
                                                                    @if ($opt['is_correct'])
                                                                        <span class="badge bg-success ms-1">Correct</span>
                                                                    @endif
                                                                    @if ($opt['option_text'] == $response['selected_option'])
                                                                        <span class="badge bg-info ms-1">Chosen</span>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span class="text-muted">–</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('styles')
        <style>
            .table th, .table td {
                vertical-align: middle;
            }
            .table td ul {
                padding-left: 1.1rem;
            }
        </style>
    @endpush
</x-app-dashboard-layout>