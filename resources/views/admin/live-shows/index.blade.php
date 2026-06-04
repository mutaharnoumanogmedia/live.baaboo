<x-app-dashboard-layout>
    <style>
        .card-body,
        .dataTables_wrapper,
        .table-responsive {
            overflow: visible !important;
        }

        table tr:has(.dropdown-menu.show) {
            position: relative;
            z-index: 1055;
        }

        .dropdown-menu {
            z-index: 1055;
        }
    </style>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 py-3 mb-1">
            {{ __('Manage Live Shows') }}

            <a href="{{ route('admin.live-shows.create') }}" class="btn btn-success btn-sm mx-4 float-end">
                New Live Show
            </a>
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6">
                    {{-- Non-Test Shows Table --}}
                    <div class="card mb-5">
                        <div class="card-header bg-dark text-light">
                            <h5 class="mb-0">Badabing Shows</h5>
                        </div>
                        <div class="card-body bg-dark text-light ">

                            <table id="liveShowsTable"
                                class="table table-striped table-borderless table-dark data-table table-responsive mb-0 h-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Total Players</th>
                                        <th>Status</th>
                                        <th>Is Test Show</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $mainShowIndex = 1; @endphp
                                    @foreach ($liveShows->where('is_test_show', false) as $show)
                                        <tr>
                                            <td>{{ $mainShowIndex++ }}</td>
                                            <td>{{ $show->title }}</td>
                                            <td>{{ $show->scheduled_at }}</td>
                                            <td>{{ $show->users->count() }}</td>
                                            <td>
                                                @if ($show->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif ($show->status === 'scheduled')
                                                    <span class="badge bg-secondary">Scheduled</span>
                                                @elseif ($show->status === 'live')
                                                    <span class="badge bg-danger">Live</span>
                                                @else
                                                    <span
                                                        class="badge bg-light text-dark">{{ ucfirst($show->status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-success">No</span>
                                            </td>
                                            <td class="d-flex gap-2">
                                                <a href="{{ route('admin.live-shows.stream-management', $show->id) }}"
                                                    class="btn btn-sm btn-primary ">Stream Management</a>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-dark dropdown-toggle" type="button"
                                                        id="dropdownMenuButton{{ $show->id }}"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu"
                                                        aria-labelledby="dropdownMenuButton{{ $show->id }}">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.live-shows.edit', $show->id) }}">Edit</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.live-shows.players', $show->id) }}">All
                                                                Players</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.live-shows.view-details', $show->id) }}">Details</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.live-show-quizzes.index', ['live_show_id' => $show->id]) }}">Quiz
                                                                Questions</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.live-shows.gallery-attach', $show) }}">Gallery
                                                                Media</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.live-shows.copy', $show->id) }}">Copy</a>
                                                        </li>
                                                        <li>
                                                            <form
                                                                action="{{ route('admin.live-shows.destroy', $show->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('Are you sure you want to delete this show?');"
                                                                style="display:inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="dropdown-item text-danger">
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>


                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    {{-- Test Shows Table --}}
                    <div class="card">
                        <div class="card-header bg-danger text-light">
                            <h5 class="mb-0">Test Shows</h5>
                        </div>
                        <div class="card-body bg-dark text-light table-responsive">
                            <table id="testShowsTable"
                                class="table table-striped table-borderless table-dark data-table mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Total Players</th>
                                        <th>Status</th>
                                        <th>Is Test Show</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $testShowIndex = 1; @endphp
                                    @foreach ($liveShows->where('is_test_show', true) as $show)
                                        <tr>
                                            <td>{{ $testShowIndex++ }}</td>
                                            <td>{{ $show->title }}</td>
                                            <td>{{ $show->scheduled_at }}</td>
                                            <td>{{ $show->users->count() }}</td>
                                            <td>
                                                @if ($show->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif ($show->status === 'scheduled')
                                                    <span class="badge bg-secondary">Scheduled</span>
                                                @elseif ($show->status === 'live')
                                                    <span class="badge bg-danger">Live</span>
                                                @else
                                                    <span
                                                        class="badge bg-light text-dark">{{ ucfirst($show->status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">Yes</span>
                                            </td>
                                            <td class="d-flex gap-2">
                                                <a href="{{ route('admin.live-shows.stream-management', $show->id) }}"
                                                    class="btn btn-sm btn-primary ">Stream Management</a>

                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-dark dropdown-toggle" type="button"
                                                        id="dropdownMenuButton{{ $show->id }}"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu"
                                                        aria-labelledby="dropdownMenuButton{{ $show->id }}">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.live-shows.edit', $show->id) }}">Edit</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.live-shows.players', $show->id) }}">All
                                                                Players</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.live-shows.view-details', $show->id) }}">Details</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.live-show-quizzes.index', ['live_show_id' => $show->id]) }}">Quiz
                                                                Questions</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.live-shows.gallery-attach', $show) }}">Gallery
                                                                Media</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.live-shows.copy', $show->id) }}">Copy</a>
                                                        </li>
                                                        <li>
                                                            <form
                                                                action="{{ route('admin.live-shows.destroy', $show->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('Are you sure you want to delete this show?');"
                                                                style="display:inline;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="dropdown-item text-danger">
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
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
