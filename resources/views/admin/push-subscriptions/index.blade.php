<x-app-dashboard-layout>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-1">Push Subscriptions ({{ $subscriptions->count() }})</h3>
                <p class="text-muted mb-0">
                    Registered browser devices that can receive push notifications.
                </p>
            </div>
            <a href="{{ route('admin.push-notifications.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-bell-fill me-1"></i> Send Notification
            </a>
        </div>

        <table class="table table-bordered table-light table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Endpoint</th>
                    <th>Registered</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subscriptions as $subscription)
                    <tr>
                        <td>{{ $subscription->id }}</td>
                        <td>
                            @if ($subscription->user)
                                <div class="fw-medium">{{ $subscription->user->name }}</div>
                                <div class="small text-muted">{{ $subscription->user->email }}</div>
                            @else
                                <span class="text-muted">Guest / no user</span>
                            @endif
                        </td>
                        <td class="small text-break" style="max-width: 360px;">
                            {{ $subscription->endpoint }}
                        </td>
                        <td>{{ $subscription->created_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $subscription->updated_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No push subscriptions registered yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $subscriptions->links() }}
    </div>
</x-app-dashboard-layout>
