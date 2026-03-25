<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Create Role') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="container-fluid">
            <div class="card bg-dark text-light">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.roles.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Role Name</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="row">
                                @foreach ($permissions as $perm)
                                    <div class="col-md-4 col-lg-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]"
                                                value="{{ $perm->id }}" id="perm_{{ $perm->id }}"
                                                {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label"
                                                for="perm_{{ $perm->id }}">{{ $perm->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if ($permissions->isEmpty())
                                <p class="text-muted">No permissions created yet.
                                    <a href="{{ route('admin.permissions.create') }}">Create one</a>.
                                </p>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-success">Create Role</button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-dashboard-layout>
