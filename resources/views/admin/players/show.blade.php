<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Player Details') }}
        </h2>
        <a href="{{ route('admin.players.index') }}" class="btn btn-secondary btn-sm mx-4">
            Back to Players
        </a>
    </x-slot>
    <div class="py-6 mt-4">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body bg-dark text-light">
                    <h4>Player Information</h4>
                    <table class="table table-borderless table-dark">
                        <tr>
                            <th>ID:</th>
                            <td>{{ $player->id }}</td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>{{ $player->name }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $player->email }}</td>
                        </tr>
                        <tr>
                            <th>Registered At:</th>
                            <td>{{ $player->created_at }}</td>
                        </tr>
                    </table>

                    <!-- show player participation in live shows and quizzes -->
                    <h4 class="mt-4">Participation</h4>
                    @if ($player->liveShows->isEmpty())
                        <p>No participation records found.</p>
                    @else
                        <table class="table table-borderless table-dark">
                            <thead>
                                <tr>
                                    <th>Live Show</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Prize Won</th>
                                    <th>Joined At</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($player->liveShows as $liveShow)
                                    {{-- @dd($liveShow); --}}
                                    <tr>
                                        <td>
                                            <a class="text-warning"
                                                href="{{ route('admin.live-shows.show', $liveShow->id) }}">
                                                {{ $liveShow->title ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>{{ $liveShow->pivot->status ?? 'N/A' }}</td>
                                        <td>{{ $liveShow->pivot->score ?? 'N/A' }}</td>
                                        <td>{{ $liveShow->pivot->prize_won ?? 'N/A' }}</td>
                                        <td>{{ $liveShow->pivot->created_at ?? 'N/A' }}</td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif


                </div>
            </div>
        </div>

</x-app-dashboard-layout>
