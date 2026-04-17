@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

<x-app-dashboard-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
            <div>
                <h2 class="h4 mb-0">All Players</h2>
                <div class="text-muted small">{{ $liveShow->title }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.live-shows.stream-management', $liveShow->id) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Stream Management
                </a>
                <a href="{{ route('admin.live-shows.export-all-users-as-csv', $liveShow->id) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-export me-1"></i> Export Users
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <div class="fw-bold">Participating Players</div>
                            <div class="text-muted small">
                                Showing all {{ $totalPlayers }} participating players with DataTable search,
                                sorting, and pagination.
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-light table-striped align-middle data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Online</th>
                                    <th>Winner</th>
                                    <th>Prize</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($players as $index => $player)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $player->name }}</div>
                                            <div class="small text-muted">{{ $player->user_name }}</div>
                                        </td>
                                        <td>{{ $player->email }}</td>
                                        <td>{{ $player->pivot->score ?? 0 }}</td>
                                        <td>{{ ucfirst($player->pivot->status ?? 'n/a') }}</td>
                                        <td>
                                            <span class="badge {{ $player->pivot->is_online ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $player->pivot->is_online ? 'Online' : 'Offline' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($player->pivot->is_winner)
                                                <span class="badge bg-warning text-dark">Winner</span>
                                            @else
                                                <span class="text-muted">No</span>
                                            @endif
                                        </td>
                                        <td>{{ $player->pivot->prize_won ?? 'N/A' }}</td>
                                        <td>
                                            {{ $player->pivot->created_at ? \Carbon\Carbon::parse($player->pivot->created_at)->format('d M Y, H:i') : 'N/A' }}
                                        </td>
                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                    id="playerActionsDropdown{{ $player->id }}" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end"
                                                    aria-labelledby="playerActionsDropdown{{ $player->id }}">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('admin.players.show', $player->id) }}"
                                                            target="_blank">
                                                            <i class="fas fa-eye me-1"></i>
                                                            View Details
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:void(0)"
                                                            onclick="toggleBlockStatusForPlayer('{{ $player->id }}', '{{ $player->is_blocked_for_live_show ? 'unblock' : 'block' }}')">
                                                            <i class="fas fa-ban me-1"></i>
                                                            {{ $player->is_blocked_for_live_show ? 'Unblock Player' : 'Block Player' }}
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:void(0)"
                                                            onclick="resetScore('{{ $player->id }}')">
                                                            <i class="fas fa-sync me-1"></i>
                                                            Reset Score
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">No players found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-dashboard-layout>

@push('scripts')
    <script>
        function toggleBlockStatusForPlayer(userId, action) {
            if (!confirm('Are you sure you want to ' + action + ' this player?')) {
                return;
            }

            fetch(`{{ url('admin/live-shows/stream-management') }}/{{ $liveShow->id }}/toggle-block-status-for-player/${userId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: action
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message || 'Player action completed.');
                    if (data.success) {
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error updating player block status:', error);
                    alert('Error updating player block status.');
                });
        }

        function resetScore(userId) {
            if (!confirm('Are you sure you want to reset this player score?')) {
                return;
            }

            fetch(`{{ url('admin/live-shows/stream-management') }}/{{ $liveShow->id }}/reset-score/${userId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message || 'Player score updated.');
                    if (data.success) {
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error resetting player score:', error);
                    alert('Error resetting player score.');
                });
        }
    </script>
@endpush
