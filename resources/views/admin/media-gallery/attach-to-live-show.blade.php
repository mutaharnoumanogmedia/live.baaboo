<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gallery media for: ') }} {{ $liveShow->title }}
        </h2>
        <a href="{{ route('admin.live-shows.stream-management', $liveShow->id) }}"
            class="btn btn-outline-secondary btn-sm">Back to Stream Management</a>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="card mb-4">
            <div class="card-header">Attached to this show</div>
            <div class="card-body">
                <div class="row g-3" id="attached-media">
                    @forelse ($liveShow->galleryMedia as $item)
                        <div class="col-6 col-md-4 col-lg-2" data-media-id="{{ $item->id }}">
                            <div class="card">
                                @if ($item->isImage())
                                    <img src="{{ $item->path }}" class="card-img-top"
                                        style="height: 100px; object-fit: cover;" alt="">
                                @else
                                    <video src="{{ $item->path }}" class="card-img-top"
                                        style="height: 100px; object-fit: cover;" poster="{{ $item->path }}"
                                        muted></video>
                                @endif
                                <div class="card-body p-2 text-center">
                                    <form action="{{ route('admin.media-gallery.detach-from-live-show') }}"
                                        method="POST" class="detach-form">
                                        @csrf
                                        <input type="hidden" name="live_show_id" value="{{ $liveShow->id }}">
                                        <input type="hidden" name="gallery_media_id" value="{{ $item->id }}">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Detach</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted">No gallery media attached yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">All gallery media – click Attach to add to this show</div>
            <div class="card-body">
                <div class="row g-3">
                    @php $attachedIds = $liveShow->galleryMedia->pluck('id')->toArray(); @endphp
                    @foreach ($allMedia as $item)
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="card">
                                @if ($item->isImage())
                                    <img src="{{ $item->path }}" class="card-img-top"
                                        style="height: 100px; object-fit: cover;" alt="">
                                @else
                                    <video src="{{ $item->path }}" class="card-img-top"
                                        style="height: 100px; object-fit: cover;" poster="{{ $item->path }}"
                                        muted></video>
                                @endif
                                <div class="card-body p-2 text-center">
                                    @if (in_array($item->id, $attachedIds))
                                        <span class="badge bg-success">Attached</span>
                                    @else
                                        <form action="{{ route('admin.media-gallery.attach-to-live-show') }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="live_show_id" value="{{ $liveShow->id }}">
                                            <input type="hidden" name="gallery_media_id" value="{{ $item->id }}">
                                            <button type="submit" class="btn btn-sm btn-primary">Attach</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if ($allMedia->isEmpty())
                    <p class="text-muted mb-0">No media in gallery. <a
                            href="{{ route('admin.media-gallery.create') }}">Upload some</a> first.</p>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Handle AJAX form submits for both Attach and Detach buttons in the media list
            document.addEventListener('DOMContentLoaded', function() {
                // Attach AJAX submit for Attach buttons
                document.querySelectorAll('form[action="{{ route('admin.media-gallery.attach-to-live-show') }}"]')
                    .forEach(function(form) {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            const submitBtn = form.querySelector('button[type=submit]');
                            if (submitBtn) submitBtn.disabled = true;

                            fetch(form.action, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')
                                            .value,
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        live_show_id: form.querySelector(
                                            'input[name="live_show_id"]').value,
                                        gallery_media_id: form.querySelector(
                                            'input[name="gallery_media_id"]').value,
                                    })
                                })
                                .then(async response => {
                                    const data = await response.json();
                                    if (data.success) {
                                        // Optionally, update UI: mark as attached, switch badge/button
                                        // Easiest: reload section or page
                                        window.location.reload();
                                    } else {
                                        alert(data.message || 'Failed to attach.');
                                        if (submitBtn) submitBtn.disabled = false;
                                    }
                                })
                                .catch(() => {
                                    alert('An error occurred.');
                                    if (submitBtn) submitBtn.disabled = false;
                                });
                        });
                    });

                // Attach AJAX submit for Detach buttons
                document.querySelectorAll('form[action="{{ route('admin.media-gallery.detach-from-live-show') }}"]')
                    .forEach(function(form) {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            const submitBtn = form.querySelector('button[type=submit]');
                            if (submitBtn) submitBtn.disabled = true;

                            fetch(form.action, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')
                                            .value,
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        live_show_id: form.querySelector(
                                            'input[name="live_show_id"]').value,
                                        gallery_media_id: form.querySelector(
                                            'input[name="gallery_media_id"]').value,
                                    })
                                })
                                .then(async response => {
                                    const data = await response.json();
                                    if (data.success) {
                                        window.location.reload();
                                    } else {
                                        alert(data.message || 'Failed to detach.');
                                        if (submitBtn) submitBtn.disabled = false;
                                    }
                                })
                                .catch(() => {
                                    alert('An error occurred.');
                                    if (submitBtn) submitBtn.disabled = false;
                                });
                        });
                    });
            });
        </script>
    @endpush
</x-app-dashboard-layout>
