<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attach to Live Show') }}
        </h2>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        @if ($media->isImage())
                            <img src="{{ $media->url }}" alt="{{ $media->title }}" class="img-fluid rounded">
                        @else
                            <video src="{{ $media->url }}" class="img-fluid rounded" style="max-height: 200px;"
                                muted></video>
                        @endif
                        <p class="mt-2 mb-0">{{ $media->title ?: $media->original_name }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Select a live show to attach this media to</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped table-light align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Title</th>
                                        <th scope="col">Scheduled At</th>
                                        <th scope="col" class="text-center" style="width: 250px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($liveShows as $show)
                                        <tr>
                                            <td>
                                                {{ $show->title }}
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $show->scheduled_at?->format('M j, Y') }}
                                                </small>
                                            </td>
                                            <td class="text-center align-right justify-content-end d-flex">
                                                @if (!in_array($show->id, $attachedIds))
                                                    <form
                                                        action="{{ route('admin.media-gallery.attach-to-live-show') }}"
                                                        method="POST" class="d-inline attach-media-form"
                                                        data-live-show-id="{{ $show->id }}">
                                                        @csrf
                                                        <input type="hidden" name="live_show_id"
                                                            value="{{ $show->id }}">
                                                        <input type="hidden" name="gallery_media_id"
                                                            value="{{ $media->id }}">
                                                        <button type="submit"
                                                            class="btn btn-sm btn-primary">Attach</button>
                                                    </form>
                                                @else
                                                    <div>
                                                        <span class="badge bg-success me-3">Attached</span>
                                                    </div>
                                                    <form
                                                        action="{{ route('admin.media-gallery.detach-from-live-show') }}"
                                                        method="POST" class="d-inline detach-media-form"
                                                        data-live-show-id="{{ $show->id }}">
                                                        @csrf
                                                        <input type="hidden" name="live_show_id"
                                                            value="{{ $show->id }}">
                                                        <input type="hidden" name="gallery_media_id"
                                                            value="{{ $media->id }}">
                                                        <button type="submit"
                                                            class="btn btn-sm btn-danger">Detach</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-muted text-center">No live shows found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('admin.media-gallery.index') }}" class="btn btn-secondary">Back to
                                Gallery</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.attach-media-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const submitButton = form.querySelector('button[type=submit]');
                    if (submitButton) submitButton.disabled = true;

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
                    }).then(async response => {
                        const data = await response.json();
                        if (data.success) {
                            // Optionally, notify user or update UI
                            form.closest('li').querySelector('button[type=submit]')
                                .textContent = 'Attached';
                            form.closest('li').querySelector('button[type=submit]')
                                .classList.remove('btn-primary');
                            form.closest('li').querySelector('button[type=submit]')
                                .classList.add('btn-success');
                        } else {
                            alert(data.message || 'Failed to attach media.');
                            if (submitButton) submitButton.disabled = false;
                        }
                    }).catch(() => {
                        alert('An error occurred.');
                        if (submitButton) submitButton.disabled = false;
                    });
                });
            });

            document.querySelectorAll('.detach-media-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const submitButton = form.querySelector('button[type=submit]');
                    if (submitButton) submitButton.disabled = true;

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
                                    'input[name="live_show_id"]')
                                .value,
                            gallery_media_id: form.querySelector(
                                'input[name="gallery_media_id"]').value,
                        })
                    }).then(async response => {
                        const data = await response.json();
                        if (data.success) {
                            //reload
                            window.location.reload();
                        } else {
                            alert(data.message || 'Failed to detach media.');
                            if (submitButton) submitButton.disabled = false;
                        }
                    }).catch(() => {
                        alert('An error occurred.');
                        if (submitButton) submitButton.disabled = false;
                    });
                });

            });
        });
    </script>
</x-app-dashboard-layout>
