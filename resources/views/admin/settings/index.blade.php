<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            App Settings
        </h2>
    </x-slot>

    <div class="container">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf

            {{-- General --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-gear me-2"></i>General</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="app_name" class="form-label">App / Site name</label>
                            <input type="text" name="app_name" id="app_name" class="form-control"
                                value="{{ old('app_name', $settings['app_name']['value'] ?? '') }}"
                                placeholder="e.g. Live Quiz Show">
                        </div>
                        <div class="col-md-6">
                            <label for="support_email" class="form-label">Support / Contact email</label>
                            <input type="email" name="support_email" id="support_email" class="form-control"
                                value="{{ old('support_email', $settings['support_email']['value'] ?? '') }}"
                                placeholder="support@example.com">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="maintenance_mode" value="0">
                                <input type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1"
                                    class="form-check-input"
                                    {{ old('maintenance_mode', $settings['maintenance_mode']['value'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="maintenance_mode">Maintenance mode (show maintenance page to players)</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quiz --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-stopwatch me-2"></i>Quiz timer</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="default_quiz_timer" class="form-label">Default quiz timer (seconds)</label>
                            <input type="number" name="default_quiz_timer" id="default_quiz_timer" class="form-control"
                                min="2" max="120"
                                value="{{ old('default_quiz_timer', $settings['default_quiz_timer']['value'] ?? 10) }}">
                            <div class="form-text">Pre-filled when sending a question (2–120)</div>
                        </div>
                        <div class="col-md-4">
                            <label for="min_quiz_timer" class="form-label">Min quiz timer (seconds)</label>
                            <input type="number" name="min_quiz_timer" id="min_quiz_timer" class="form-control"
                                min="1" max="60"
                                value="{{ old('min_quiz_timer', $settings['min_quiz_timer']['value'] ?? 2) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="max_quiz_timer" class="form-label">Max quiz timer (seconds)</label>
                            <input type="number" name="max_quiz_timer" id="max_quiz_timer" class="form-control"
                                min="10" max="300"
                                value="{{ old('max_quiz_timer', $settings['max_quiz_timer']['value'] ?? 120) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Features --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-toggle-on me-2"></i>Features</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="hidden" name="chat_enabled" value="0">
                        <input type="checkbox" name="chat_enabled" id="chat_enabled" value="1"
                            class="form-check-input"
                            {{ old('chat_enabled', $settings['chat_enabled']['value'] ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="chat_enabled">Chat enabled on live show</label>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg me-1"></i> Save settings
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </div>
        </form>
    </div>
</x-app-dashboard-layout>
