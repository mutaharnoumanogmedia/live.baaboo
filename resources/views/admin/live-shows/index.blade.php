<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 py-3 mb-1">
            {{ __('Live Shows') }}

            <a href="{{ route('admin.live-shows.create') }}" class="btn btn-success btn-sm mx-4">
                New Live Show
            </a>
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body bg-dark text-light">
                    <table id="liveShowsTable" class="table table-striped table-borderless table-dark data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Is Test Show</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($liveShows as $show)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $show->title }}</td>
                                    <td>{{ $show->scheduled_at }}</td>
                                    <td>
                                        @if ($show->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif ($show->status === 'scheduled')
                                            <span class="badge bg-secondary">Scheduled</span>
                                        @elseif ($show->status === 'live')
                                            <span class="badge bg-danger">Live</span>
                                        @else
                                            <span class="badge bg-light text-dark">{{ ucfirst($show->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($show->is_test_show)
                                            <span class="badge bg-danger">Yes</span>
                                        @else
                                            <span class="badge bg-success">No</span>
                                        @endif
                                    </td>
                                    <td class="d-flex gap-2">
                                        <a class="btn btn-sm btn-outline-primary"
                                            href="{{ route('admin.live-shows.edit', $show->id) }}">Edit</a>
                                        <a class="btn btn-sm btn-outline-info"
                                            href="{{ route('admin.live-shows.show', $show->id) }}">View</a>
                                        <a class="btn btn-sm btn-outline-warning"
                                            href="{{ route('admin.live-show-quizzes.index', ['live_show_id' => $show->id]) }}">Quiz
                                            Questions</a>
                                        <a class="btn btn-sm btn-outline-secondary"
                                            href="{{ route('admin.live-shows.gallery-attach', $show) }}">Gallery
                                            Media</a>
                                        <a class="btn btn-sm btn-outline-secondary"
                                            href="{{ route('admin.live-shows.copy', $show->id) }}">Copy</a>
                                        <form action="{{ route('admin.live-shows.destroy', $show->id) }}"
                                            method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure you want to delete this show?')">
                                                Delete
                                            </button>
                                        </form>

                                        <a href="{{ route('admin.live-shows.stream-management', $show->id) }}"
                                            class="btn btn-sm btn-primary ">Stream Management</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('/styles/datatable.css') }}">
    @endpush

    @push('scripts')
    @endpush
</x-app-dashboard-layout>
