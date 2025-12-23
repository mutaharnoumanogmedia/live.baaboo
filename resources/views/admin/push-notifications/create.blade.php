<x-app-dashboard-layout>
    <div class="container">
        <h3>Create Push Notification</h3>

        <form method="POST" action="{{ route('admin.push-notifications.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Send To</label>
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Title</label>
                <input name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" rows="4" required></textarea>
            </div>

            <button class="btn btn-success">Send Notification</button>
            <a href="{{ route('admin.push-notifications.index') }}" class="btn btn-secondary">
                Cancel
            </a>
        </form>
    </div>
</x-app-dashboard-layout>
