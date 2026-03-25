<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Create Permission') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="container-fluid">
            <div class="card bg-dark text-light">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.permissions.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Permission Name</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                placeholder="e.g. can-manage-live-shows" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted">Use kebab-case, e.g. <code>can-manage-live-shows</code>,
                                <code>can-manage-users</code></div>
                        </div>

                        <button type="submit" class="btn btn-success">Create Permission</button>
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-dashboard-layout>
