<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Permissions') }}
            <a href="{{ route('admin.permissions.create') }}" class="btn btn-success btn-sm mx-4">+ New Permission</a>
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            <div class="card">
                <div class="card-body bg-dark text-light">
                    <table class="table table-striped table-borderless table-dark data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Guard</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permissions as $perm)
                                <tr>
                                    <td>{{ $perm->id }}</td>
                                    <td><code>{{ $perm->name }}</code></td>
                                    <td>{{ $perm->guard_name }}</td>
                                    <td>
                                        <form action="{{ route('admin.permissions.destroy', $perm) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Delete permission {{ $perm->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-dashboard-layout>
