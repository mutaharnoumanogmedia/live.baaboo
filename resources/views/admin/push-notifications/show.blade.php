<x-app-dashboard-layout>
    <div class="container">
        <h3>Push Notification #{{ $notification->id }}</h3>

        <ul class="list-group">
            <li class="list-group-item">
                <strong>Target:</strong>
                @if ($notification->push_subscription_id)
                    Subscription #{{ $notification->push_subscription_id }}
                    @if ($notification->pushSubscription?->user)
                        — {{ $notification->pushSubscription->user->name }}
                        ({{ $notification->pushSubscription->user->email }})
                    @else
                        — Guest device
                    @endif
                @else
                    All devices (every push subscription)
                @endif
            </li>
            <li class="list-group-item">
                <strong>Title:</strong> {{ $notification->title }}
            </li>
            <li class="list-group-item">
                <strong>Message:</strong><br>
                {{ $notification->message }}
            </li>
            <li class="list-group-item">
                <strong>URL:</strong>
                <a href="{{ $notification->url ?: ($notification->data['url'] ?? '/') }}" target="_blank"
                    rel="noopener">
                    {{ $notification->url ?: ($notification->data['url'] ?? '/') }}
                </a>
            </li>
            <li class="list-group-item">
                <strong>Sent At:</strong>
                {{ $notification->sent_at?->format('Y-m-d H:i:s') ?? '—' }}
            </li>
        </ul>

        <a href="{{ route('admin.push-notifications.index') }}" class="btn btn-secondary mt-3">
            Back
        </a>
    </div>
</x-app-dashboard-layout>
