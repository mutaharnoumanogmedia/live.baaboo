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

</x-app-dashboard-layout>
