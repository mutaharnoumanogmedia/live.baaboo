<x-app-dashboard-layout>
    <div class="container">
        <h3>Create Push Notification</h3>

        <p class="text-muted">
            Pick one registered device from push subscriptions, or broadcast to all
            ({{ number_format($subscriptionCount) }} device{{ $subscriptionCount === 1 ? '' : 's' }} total).
        </p>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($subscriptions->isEmpty())
            <div class="alert alert-warning">
                No push subscriptions are registered yet. A device must opt in on the site before you can send a
                notification.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.push-notifications.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Send To</label>
                <select name="push_subscription_id" class="form-select" @disabled($subscriptions->isEmpty())>
                    <option value="" @selected(old('push_subscription_id') === null || old('push_subscription_id') === '')>
                        All devices (every push subscription)
                    </option>
                    @foreach ($subscriptions as $subscription)
                        <option value="{{ $subscription->id }}"
                            @selected((string) old('push_subscription_id') === (string) $subscription->id)>
                            #{{ $subscription->id }}
                            —
                            {{ $subscription->user?->name ?? 'Guest device' }}
                            @if ($subscription->user?->email)
                                ({{ $subscription->user->email }})
                            @endif
                            — {{ \Illuminate\Support\Str::limit($subscription->endpoint, 48) }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">
                    Choose a single subscription to notify one device, or leave as &ldquo;All devices&rdquo; to notify
                    every entry in push subscriptions.
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Title</label>
                <input name="title" class="form-control" value="{{ old('title') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" rows="4" required>{{ old('message') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">URL</label>
                <input name="url" type="text" class="form-control" value="{{ old('url', url('/')) }}"
                    placeholder="{{ url('/') }}">
                <div class="form-text">
                    Page opened when the recipient taps the notification.
                </div>
            </div>

            <button class="btn btn-success" @disabled($subscriptions->isEmpty())>Send Notification</button>
            <a href="{{ route('admin.push-notifications.index') }}" class="btn btn-secondary">
                Cancel
            </a>
        </form>
    </div>
</x-app-dashboard-layout>
