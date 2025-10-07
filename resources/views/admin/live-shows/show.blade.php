<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('View Live Show') }}
        </h2>
    </x-slot>

    <div class="container py-4">

        <!-- Event Details Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-dark text-white">
                <h3 class="mb-0"><i class="fas fa-broadcast-tower me-2"></i>{{ $liveShow->title }}</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">{{ $liveShow->description }}</p>

                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Host:</strong> {{ $liveShow->host_name ?? 'TBA' }}</p>
                        <p><strong>Scheduled At:</strong>
                            {{ \Carbon\Carbon::parse($liveShow->scheduled_at)->format('d M Y, H:i') }}</p>
                        <p><strong>Status:</strong>
                            <span
                                class="badge bg-{{ $liveShow->status == 'live' ? 'success' : ($liveShow->status == 'completed' ? 'secondary' : 'warning') }}">
                                {{ ucfirst($liveShow->status) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Prize:</strong> 💰 {{ number_format($liveShow->prize_amount, 2) }}
                            {{ $liveShow->currency }}</p>
                        <p><strong>Stream Link:</strong>
                            @if ($liveShow->stream_link)
                                <a href="{{ $liveShow->stream_link }}" target="_blank"
                                    class="btn btn-sm btn-outline-primary">
                                    Watch Stream <i class="fas fa-external-link-alt ms-1"></i>
                                </a>
                            @else
                                <span class="text-muted">No link yet</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="d-flex gap-3 mt-3">
                    @if ($liveShow->thumbnail)
                        <div>
                            <p class="fw-bold">Thumbnail</p>
                            <img src="{{ asset('storage/' . $liveShow->thumbnail) }}"
                                class="img-fluid rounded shadow-sm" style="max-width:200px;">
                        </div>
                    @endif
                    @if ($liveShow->banner)
                        <div>
                            <p class="fw-bold">Banner</p>
                            <img src="{{ asset('storage/' . $liveShow->banner) }}" class="img-fluid rounded shadow-sm"
                                style="max-width:400px;">
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Users List -->
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Participants ({{ $liveShow->users->count() }})
                </h5>
            </div>
            <div class="card-body p-0">
                @if ($liveShow->users->count())
                    <div class="table-responsive">
                        <table class="table table-hover table-dark table-borderless mb-0">
                            <thead class="">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Joined At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($liveShow->users as $index => $user)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $user->pivot->status === 'registered' ? 'primary ' : ($user->pivot->status === 'attended' ? 'success' : 'secondary') }}">
                                                {{ ucfirst($user->pivot->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $user->pivot->score }}</td>
                                        <td>{{ $user->pivot->created_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="p-3 text-muted">No participants have joined this event yet.</p>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ route('admin.live-shows.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <div>
                <a href="{{ route('admin.live-shows.edit', $liveShow->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <form action="{{ route('admin.live-shows.destroy', $liveShow->id) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Are you sure you want to delete this event?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-dashboard-layout>
