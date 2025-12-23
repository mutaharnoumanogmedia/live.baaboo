<x-app-dashboard-layout>
    <div class="container">
        <h3>Push Notification #{{ $notification->id }}</h3>

        <ul class="list-group">
            <li class="list-group-item">
                <strong>Target:</strong>
                {{ $notification->user_id ? 'User #' . $notification->user_id : 'All Users' }}
            </li>
            <li class="list-group-item">
                <strong>Title:</strong> {{ $notification->title }}
            </li>
            <li class="list-group-item">
                <strong>Message:</strong><br>
                {{ $notification->message }}
            </li>
            <li class="list-group-item">
                <strong>Sent At:</strong>
                {{ $notification->sent_at }}
            </li>
        </ul>

        <a href="{{ route('admin.push-notifications.index') }}" class="btn btn-secondary mt-3">
            Back
        </a>
    </div>
</x-app-dashboard-layout>
