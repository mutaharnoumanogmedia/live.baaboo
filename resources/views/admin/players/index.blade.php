<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Players') }}
        </h2>
    </x-slot>

    @php
        // Build a sortable column header link. Preserves all current filters,
        // overrides sort/direction, resets to page 1 on every sort click.
        $currentSort = request('sort', 'id');
        $currentDir = request('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $sortLink = function (string $column, string $label) use ($currentSort, $currentDir) {
            $newDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
            $params = array_merge(
                request()->except(['sort', 'direction', 'page']),
                ['sort' => $column, 'direction' => $newDir],
            );
            $url = url()->current().'?'.http_build_query($params);

            $arrow = '';
            if ($currentSort === $column) {
                $arrow = $currentDir === 'asc'
                    ? ' <i class="bi bi-caret-up-fill small"></i>'
                    : ' <i class="bi bi-caret-down-fill small"></i>';
            } else {
                $arrow = ' <i class="bi bi-arrow-down-up small text-muted"></i>';
            }

            return '<a href="'.e($url).'" class="text-light text-decoration-none">'
                .e($label).$arrow.'</a>';
        };
    @endphp

    <div class="py-4">
        <div class="container-fluid">

            {{-- One form wraps both the filter card and the in-table column searches.
                 Submitting (Apply / Enter on any input) reloads the page with all
                 filters, the current sort, and resets back to page 1. --}}
            <form id="playersFilterForm" method="GET" action="{{ route('admin.players.index') }}">

                {{-- Preserve current sort across filter submissions --}}
                <input type="hidden" name="sort" value="{{ $currentSort }}">
                <input type="hidden" name="direction" value="{{ $currentDir }}">

                {{-- Filters card --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-dark text-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-funnel-fill me-2"></i>Filters</h5>
                        <button type="button" class="btn btn-sm btn-outline-light" data-bs-toggle="collapse"
                            data-bs-target="#playersFilters" aria-expanded="true" aria-controls="playersFilters">
                            Toggle
                        </button>
                    </div>
                    <div id="playersFilters" class="collapse show">
                        <div class="card-body bg-dark text-light">
                            <div class="row g-3">
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label small mb-1">Global Search</label>
                                    <input type="text" name="q" value="{{ request('q') }}"
                                        class="form-control form-control-sm" placeholder="Name, email or username">
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label small mb-1">Registered From</label>
                                    <input type="date" name="filter_registered_from"
                                        value="{{ request('filter_registered_from') }}"
                                        class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label small mb-1">Registered To</label>
                                    <input type="date" name="filter_registered_to"
                                        value="{{ request('filter_registered_to') }}"
                                        class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label small mb-1">Has Referrer?</label>
                                    <select name="filter_has_referrer" class="form-select form-select-sm">
                                        <option value="" {{ request('filter_has_referrer') === null || request('filter_has_referrer') === '' ? 'selected' : '' }}>All</option>
                                        <option value="1" {{ request('filter_has_referrer') === '1' ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ request('filter_has_referrer') === '0' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label small mb-1">Last Show Played From</label>
                                    <input type="date" name="filter_last_show_from"
                                        value="{{ request('filter_last_show_from') }}"
                                        class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label small mb-1">Last Show Played To</label>
                                    <input type="date" name="filter_last_show_to"
                                        value="{{ request('filter_last_show_to') }}"
                                        class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label small mb-1">Min. Games Played</label>
                                    <input type="number" min="0" name="filter_min_games"
                                        value="{{ request('filter_min_games') }}"
                                        class="form-control form-control-sm" placeholder="0">
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="form-label small mb-1">Min. Referred Users</label>
                                    <input type="number" min="0" name="filter_min_referred"
                                        value="{{ request('filter_min_referred') }}"
                                        class="form-control form-control-sm" placeholder="0">
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-funnel"></i> Apply Filters
                                    </button>
                                    <a href="{{ route('admin.players.index') }}" class="btn btn-outline-light btn-sm">
                                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Players table --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Players</h5>
                        <div class="d-flex align-items-center gap-3">
                            <label class="small text-muted mb-0 d-flex align-items-center gap-2">
                                Per page
                                <select name="per_page" class="form-select form-select-sm" style="width: auto;"
                                    onchange="this.form.submit()">
                                    @foreach ([25, 50, 100] as $size)
                                        <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>
                                            {{ $size }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                            <span class="small text-muted">
                                Showing {{ $players->firstItem() ?? 0 }}–{{ $players->lastItem() ?? 0 }}
                                of {{ number_format($players->total()) }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body bg-dark text-light">
                        <div class="table-responsive">
                            <table class="table table-striped table-borderless table-dark align-middle mb-0 w-100">
                                <thead>
                                    {{-- Sortable column titles --}}
                                    <tr>
                                        <th>{!! $sortLink('id', 'ID') !!}</th>
                                        <th>{!! $sortLink('name', 'Name') !!}</th>
                                        <th>{!! $sortLink('email', 'Email') !!}</th>
                                        <th>{!! $sortLink('user_name', 'Username') !!}</th>
                                        <th>{!! $sortLink('created_at', 'Registered At') !!}</th>
                                        <th class="text-center">{!! $sortLink('live_games_played', 'Games Played') !!}</th>
                                        <th>{!! $sortLink('last_game_played_at', 'Last Game Played') !!}</th>
                                        <th class="text-center">{!! $sortLink('referred_users_count', 'Referred Users') !!}</th>
                                        <th>Referred By</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                    {{-- Per-column search inputs (live inside the form) --}}
                                    <tr class="column-search-row">
                                        <th>
                                            <input type="text" name="id" value="{{ request('id') }}"
                                                class="form-control form-control-sm" placeholder="ID">
                                        </th>
                                        <th>
                                            <input type="text" name="name" value="{{ request('name') }}"
                                                class="form-control form-control-sm" placeholder="Name">
                                        </th>
                                        <th>
                                            <input type="text" name="email" value="{{ request('email') }}"
                                                class="form-control form-control-sm" placeholder="Email">
                                        </th>
                                        <th>
                                            <input type="text" name="user_name" value="{{ request('user_name') }}"
                                                class="form-control form-control-sm" placeholder="Username">
                                        </th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th>
                                            <input type="text" name="referred_by_username"
                                                value="{{ request('referred_by_username') }}"
                                                class="form-control form-control-sm" placeholder="Referrer">
                                        </th>
                                        <th class="text-end">
                                            <button type="submit" class="btn btn-sm btn-primary" title="Search">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($players as $player)
                                        @php
                                            $referredByLabel = '-';
                                            if ($player->referredBy) {
                                                $referredByLabel =
                                                    $player->referredBy->user_name
                                                    ?: ($player->referredBy->name ?: '#'.$player->referredBy->id);
                                            }
                                            $lastPlayed = $player->last_game_played_at
                                                ? \Carbon\Carbon::parse($player->last_game_played_at)->format('Y-m-d H:i')
                                                : 'Never';
                                        @endphp
                                        <tr>
                                            <td>{{ $player->id }}</td>
                                            <td>{{ $player->name ?? '-' }}</td>
                                            <td>{{ $player->email }}</td>
                                            <td>{{ $player->user_name ?? '-' }}</td>
                                            <td>{{ optional($player->created_at)->format('Y-m-d H:i') ?? '-' }}</td>
                                            <td class="text-center">{{ (int) $player->live_games_played }}</td>
                                            <td>{{ $lastPlayed }}</td>
                                            <td class="text-center">{{ (int) $player->referred_users_count }}</td>
                                            <td>{{ $referredByLabel }}</td>
                                            <td class="text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-secondary btn-sm dropdown-toggle"
                                                        type="button" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('admin.players.show', $player->id) }}">
                                                                View Player
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">
                                                <i class="bi bi-info-circle me-1"></i> No players match your filters.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination (Bootstrap 5 styling, query string preserved by the controller) --}}
                        @if ($players->hasPages())
                            <div class="mt-3 d-flex justify-content-center">
                                {{ $players->onEachSide(1)->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </form>

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('playersFilterForm');
                if (!form) {
                    return;
                }

                form.querySelectorAll('input[type="text"], input[type="number"], input[type="date"]').forEach(
                    function(input) {
                        input.addEventListener('keydown', function(event) {
                            if (event.key === 'Enter') {
                                event.preventDefault();
                                form.submit();
                            }
                        });
                    });
            });
        </script>
    @endpush

    @push('styles')
        <style>
            .column-search-row th {
                padding: 4px 6px;
                background-color: #1f1f1f;
            }

            .column-search-row input {
                background-color: #2d2d2d;
                border: 1px solid #444;
                color: #fff;
            }

            .column-search-row input::placeholder {
                color: #aaa;
            }

            /* Dark-friendly Bootstrap 5 paginator */
            .pagination .page-link {
                background-color: #2d2d2d;
                border-color: #444;
                color: #fff;
            }

            .pagination .page-item.active .page-link {
                background-color: #0d6efd;
                border-color: #0d6efd;
                color: #fff;
            }

            .pagination .page-item.disabled .page-link {
                background-color: #1f1f1f;
                border-color: #2a2a2a;
                color: #777;
            }

            .pagination .page-link:hover {
                background-color: #495057;
                border-color: #495057;
                color: #fff;
            }
        </style>
    @endpush
</x-app-dashboard-layout>
