<tr>
    <td>{{ $show->id }}</td>
    <td class="live-shows-title" title="{{ $show->title }}">{{ $show->title }}</td>
    <td class="text-nowrap">{{ $show->scheduled_at->format('d M Y, H:i') }}</td>
    <td>{{ $show->users->count() }}</td>
    <td class="text-nowrap">
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
    <td class="text-nowrap">
        @if ($isTestShow)
            <span class="badge bg-danger">Yes</span>
        @else
            <span class="badge bg-success">No</span>
        @endif
    </td>
    <td class="live-shows-actions">
        <a href="{{ route('admin.live-shows.stream-management', $show->id) }}"
            class="btn btn-sm btn-primary">Stream Management</a>
        <div class="dropdown">
            <button class="btn btn-sm btn-dark dropdown-toggle" type="button"
                id="dropdownMenuButton{{ $show->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                Actions
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $show->id }}">
                <li>
                    <a class="dropdown-item" href="{{ route('admin.live-shows.edit', $show->id) }}">Edit</a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.live-shows.players', $show->id) }}">All
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
                        href="{{ route('admin.live-shows.gallery-attach', $show) }}">Gallery Media</a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.live-shows.copy', $show->id) }}">Copy</a>
                </li>
                <li>
                    <form action="{{ route('admin.live-shows.destroy', $show->id) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this show?');"
                        style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger">
                            Delete
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </td>
</tr>
