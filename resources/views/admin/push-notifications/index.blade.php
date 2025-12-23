<x-app-dashboard-layout>
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h3>Push Notifications</h3>
            <a href="{{ route('admin.push-notifications.create') }}" class="btn btn-primary">
                New Notification
            </a>
        </div>
        
        <table class="table table-bordered table-light">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Target</th>
                    <th>Title</th>
                    <th>Sent At</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($notifications as $n)
                    <tr>
                        <td>{{ $n->id }}</td>
                        <td>{{ $n->user_id ? 'User #' . $n->user_id : 'All Users' }}</td>
                        <td>{{ $n->title }}</td>
                        <td>{{ $n->sent_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.push-notifications.show', $n) }}"
                                class="btn btn-sm btn-secondary">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $notifications->links() }}
    </div>
</x-app-dashboard-layout>
