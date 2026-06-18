<x-app-dashboard-layout>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-1">Push Notifications</h3>
                <p class="text-muted mb-0">
                    {{ number_format($subscriptionCount) }} registered device{{ $subscriptionCount === 1 ? '' : 's' }}
                    in push subscriptions.
                </p>
            </div>
            <a href="{{ route('admin.push-notifications.create') }}" class="btn btn-primary">
                New Notification
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-light">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Target</th>
                    <th>Title</th>
                    <th>URL</th>
                    <th>Sent At</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($notifications as $n)
                    <tr>
                        <td>{{ $n->id }}</td>
                        <td>
                            @if ($n->push_subscription_id)
                                Subscription #{{ $n->push_subscription_id }}
                                @if ($n->pushSubscription?->user)
                                    ({{ $n->pushSubscription->user->name }})
                                @endif
                            @else
                                All devices
                            @endif
                        </td>
                        <td>{{ $n->title }}</td>
                        <td class="text-truncate" style="max-width: 220px;">
                            <a href="{{ $n->url ?: ($n->data['url'] ?? '/') }}" target="_blank" rel="noopener">
                                {{ $n->url ?: ($n->data['url'] ?? '/') }}
                            </a>
                        </td>
                        <td>{{ $n->sent_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.push-notifications.show', $n) }}"
                                class="btn btn-sm btn-secondary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No push notifications sent yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $notifications->links() }}
    </div>
</x-app-dashboard-layout>
