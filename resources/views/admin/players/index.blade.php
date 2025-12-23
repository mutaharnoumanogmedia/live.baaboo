
<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Players') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body bg-dark text-light">
                    <table id="playersTable" class="table table-striped table-borderless table-dark data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Registered At</th>
                                <th>Live Games Played</th>
                                <th>
                                    Last Game Played
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($players as $player)
                                <tr>
                                    <td>{{ $player->id }}</td>
                                    <td>{{ $player->name }}</td>
                                    <td>{{ $player->email }}</td>
                                    <td>{{ $player->created_at }}</td>
                                    <th>
                                        <a href="#" class="btn btn-primary text-center" data-bs-toggle="modal"
                                            data-bs-target="#gamesModal{{ $player->id }}">
                                            {{ $player->liveShows->count() }}
                                        </a>

                                        <!-- Modal -->

                                    </th>
                                    <th>
                                        {{ $player->liveShows->last()->created_at ?? 'N/A' }}
                                    </th>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                                id="actionsDropdown{{ $player->id }}" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu"
                                                aria-labelledby="actionsDropdown{{ $player->id }}">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('admin.players.show', $player->id) }}">View
                                                        Player</a>
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


    @foreach ($players as $player)
        <div class="modal fade modal-lg" id="gamesModal{{ $player->id }}" tabindex="-1"
            aria-labelledby="gamesModalLabel{{ $player->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark text-light">
                    <div class="modal-header">
                        <h5 class="modal-title" id="gamesModalLabel{{ $player->id }}">
                            Live Games Played by {{ $player->name }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if ($player->liveShows->count())
                            <table class="table table-striped table-borderless table-dark">
                                <thead>
                                    <tr>
                                        <th>Game Title</th>
                                        <th>Played At</th>
                                        <th>Prize Won</th>
                                        <th> Score </th>
                                    </tr>
                                </thead>
                                @foreach ($player->liveShows as $show)
                                    <tr>
                                        <td>{{ $show->title }}</td>
                                        <td>{{ $show->pivot->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            {{ $show->pivot->prize_won ?? '--' }}
                                        </td>
                                        <td>
                                            {{ $show->pivot->score ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @else
                            <p>No games played.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach

</x-app-dashboard-layout>
