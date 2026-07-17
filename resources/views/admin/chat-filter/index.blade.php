<x-app-dashboard-layout>
    {{-- chat_filter_module: tiers overview + per-tier policy settings --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Chat Filter
        </h2>
    </x-slot>

    <div class="container-fluid py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <p class="text-muted mb-0">Manage moderation tiers, the word dictionary and the watchlist.</p>
            <div>
                <a href="{{ route('admin.chat-filter.words') }}" class="btn btn-outline-primary">
                    <i class="bi bi-list-ul me-1"></i> Words
                </a>
                <a href="{{ route('admin.chat-filter.watchlist') }}" class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-eye me-1"></i> Watchlist
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted small">Total words</div>
                    <div class="fs-3 fw-bold">{{ $stats['total_words'] }}</div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted small">Active words</div>
                    <div class="fs-3 fw-bold">{{ $stats['active_words'] }}</div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted small">Pending watchlist</div>
                    <div class="fs-3 fw-bold">{{ $stats['pending_watchlist'] }}</div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted small">Muted users</div>
                    <div class="fs-3 fw-bold">{{ $stats['muted_users'] }}</div>
                </div></div>
            </div>
        </div>

        @foreach ($tiers as $tier)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold">
                        Tier {{ $tier->tier_number }} &middot; {{ $tier->name }}
                        @if ($tier->is_enabled)
                            <span class="badge bg-success ms-2">Enabled</span>
                        @else
                            <span class="badge bg-secondary ms-2">Disabled</span>
                        @endif
                    </h5>
                    <span class="text-muted small">{{ $tier->active_words_count }}/{{ $tier->words_count }} active words</span>
                </div>
                <div class="card-body">
                    @if ($tier->description)
                        <p class="text-muted small mb-3">{{ $tier->description }}</p>
                    @endif
                    <form method="POST" action="{{ route('admin.chat-filter.tiers.update', $tier) }}" class="row g-3 align-items-end">
                        @csrf
                        @method('PUT')
                        <div class="col-md-3">
                            <label class="form-label">Default action</label>
                            <select name="action" class="form-select">
                                @foreach (['ban' => 'Ban (delete + block)', 'timeout' => 'Timeout (delete + mute on repeat)', 'watchlist' => 'Watchlist (highlight only)', 'hard_block' => 'Hard block (delete only)'] as $val => $label)
                                    <option value="{{ $val }}" @selected($tier->action === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Timeout (min)</label>
                            <input type="number" name="timeout_minutes" min="1" max="1440" class="form-control" value="{{ $tier->timeout_minutes }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Timeout after</label>
                            <input type="number" name="timeout_after_offenses" min="1" max="100" class="form-control" value="{{ $tier->timeout_after_offenses }}">
                        </div>
                        <div class="col-md-2">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_enabled" value="0">
                                <input type="checkbox" name="is_enabled" value="1" class="form-check-input" id="enabled-{{ $tier->id }}" @checked($tier->is_enabled)>
                                <label class="form-check-label" for="enabled-{{ $tier->id }}">Enabled</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="delete_message" value="0">
                                <input type="checkbox" name="delete_message" value="1" class="form-check-input" id="delete-{{ $tier->id }}" @checked($tier->delete_message)>
                                <label class="form-check-label" for="delete-{{ $tier->id }}">Delete msg</label>
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <a href="{{ route('admin.chat-filter.words', ['tier' => $tier->id]) }}" class="btn btn-outline-secondary">View words</a>
                            <button type="submit" class="btn btn-primary ms-1"><i class="bi bi-check-lg me-1"></i>Save</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</x-app-dashboard-layout>
