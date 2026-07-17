<x-app-dashboard-layout>
    {{-- chat_filter_module: violations log + Tier 4 watchlist --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Chat Filter &middot; Watchlist &amp; Log
        </h2>
    </x-slot>

    <div class="container-fluid py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('admin.chat-filter.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to tiers
            </a>
            <form method="GET" class="d-flex gap-2">
                <select name="action" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All actions</option>
                    @foreach (['flagged' => 'Flagged (watchlist)', 'deleted' => 'Deleted', 'timeout' => 'Timeout', 'banned' => 'Banned'] as $val => $label)
                        <option value="{{ $val }}" @selected($filter === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>User</th>
                            <th>Tier</th>
                            <th>Term</th>
                            <th>Action</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($violations as $v)
                            <tr>
                                <td class="small text-muted">{{ $v->created_at?->format('d.m.Y H:i') }}</td>
                                <td>
                                    {{ $v->user?->name ?? 'Unknown' }}
                                    @if ($v->user && $v->user->is_blocked)
                                        <span class="badge bg-danger ms-1">Blocked</span>
                                    @elseif ($v->user && $v->user->chat_muted_until && $v->user->chat_muted_until->isFuture())
                                        <span class="badge bg-warning text-dark ms-1">Muted</span>
                                    @endif
                                </td>
                                <td>{{ $v->tier_number }}</td>
                                <td><code>{{ $v->matched_term }}</code></td>
                                <td>
                                    @php $map = ['banned' => 'bg-danger', 'timeout' => 'bg-warning text-dark', 'deleted' => 'bg-secondary', 'flagged' => 'bg-info text-dark']; @endphp
                                    <span class="badge {{ $map[$v->action_taken] ?? 'bg-secondary' }}">{{ $v->action_taken }}</span>
                                </td>
                                <td class="small" style="max-width:280px;">{{ \Illuminate\Support\Str::limit($v->original_message, 120) }}</td>
                                <td>
                                    @if ($v->is_reviewed)
                                        <span class="text-success small"><i class="bi bi-check-lg"></i> Reviewed</span>
                                    @else
                                        <span class="text-muted small">Pending</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if (! $v->is_reviewed)
                                        <form method="POST" action="{{ route('admin.chat-filter.watchlist.review', $v) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" title="Mark reviewed"><i class="bi bi-check2"></i></button>
                                        </form>
                                    @endif
                                    @if ($v->user && $v->user->chat_muted_until && $v->user->chat_muted_until->isFuture())
                                        <form method="POST" action="{{ route('admin.chat-filter.users.unmute', $v->user) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning" title="Unmute">Unmute</button>
                                        </form>
                                    @endif
                                    @if ($v->user && $v->user->is_blocked)
                                        <form method="POST" action="{{ route('admin.chat-filter.users.unblock', $v->user) }}" class="d-inline"
                                            onsubmit="return confirm('Unblock this user?');">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" title="Unblock">Unblock</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No violations logged.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $violations->links() }}
        </div>
    </div>
</x-app-dashboard-layout>
