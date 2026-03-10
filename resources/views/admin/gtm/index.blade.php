<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Google Tag Manager (GTM)
        </h2>
    </x-slot>

    <div class="container-fluid py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-google me-2"></i>GTM settings</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    Add your Google Tag Manager container to run it across the site. You can either enter just the container ID (e.g. <code>GTM-XXXXXXX</code>) or paste the full script snippets from GTM.
                </p>

                <form method="POST" action="{{ route('admin.gtm.update') }}">
                    @csrf

                    <div class="form-check form-switch mb-4">
                        <input type="hidden" name="gtm_enabled" value="0">
                        <input type="checkbox" name="gtm_enabled" id="gtm_enabled" value="1" class="form-check-input"
                            {{ $gtmEnabled ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="gtm_enabled">Enable Google Tag Manager on this site</label>
                    </div>

                    <div class="mb-4">
                        <label for="gtm_container_id" class="form-label">Container ID</label>
                        <input type="text" name="gtm_container_id" id="gtm_container_id" class="form-control"
                            value="{{ old('gtm_container_id', $gtmContainerId) }}"
                            placeholder="GTM-XXXXXXX">
                        <div class="form-text">From your GTM container: Admin → Container ID. If you paste the full scripts below, this is optional.</div>
                    </div>

                    <div class="mb-4">
                        <label for="gtm_head_script" class="form-label">Script to paste in <code>&lt;head&gt;</code> (optional)</label>
                        <textarea name="gtm_head_script" id="gtm_head_script" class="form-control font-monospace small" rows="8"
                            placeholder="Paste the full GTM script that goes in the &lt;head&gt; here (e.g. <!-- Google Tag Manager --> ... &lt;script&gt;...&lt;/script&gt; <!-- End Google Tag Manager -->)">{{ old('gtm_head_script', $gtmHeadScript) }}</textarea>
                        <div class="form-text">If left empty and Container ID is set, the standard GTM head snippet will be used.</div>
                    </div>

                    <div class="mb-4">
                        <label for="gtm_body_script" class="form-label">Script to paste right after <code>&lt;body&gt;</code> (optional)</label>
                        <textarea name="gtm_body_script" id="gtm_body_script" class="form-control font-monospace small" rows="6"
                            placeholder="Paste the GTM &lt;noscript&gt; iframe snippet that goes right after &lt;body&gt; here.">{{ old('gtm_body_script', $gtmBodyScript) }}</textarea>
                        <div class="form-text">Usually the &lt;noscript&gt; part. If left empty and Container ID is set, the standard snippet will be used.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save GTM settings
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</x-app-dashboard-layout>
