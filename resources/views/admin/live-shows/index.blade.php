@php
    $badabingShows = $liveShows->where('is_test_show', false);
    $testShows = $liveShows->where('is_test_show', true);
@endphp

<x-app-dashboard-layout>
    <style>
        .live-shows-page .live-shows-table-scroll {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .live-shows-page .live-shows-table-scroll:has(.dropdown-menu.show) {
            overflow: visible;
        }

        .live-shows-page table.live-shows-table {
            width: 100%;
            min-width: 42rem;
            margin-bottom: 0;
        }

        .live-shows-page .live-shows-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            min-width: 11rem;
        }

        .live-shows-page .live-shows-title {
            max-width: 14rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .live-shows-page .live-shows-pills .nav-link {
            color: rgba(255, 255, 255, 0.75);
            border-radius: 2rem;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .live-shows-page .live-shows-pills .nav-link:hover,
        .live-shows-page .live-shows-pills .nav-link:focus {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .live-shows-page .live-shows-pills .nav-link.active {
            color: #fff;
            background-color: #0d6efd;
        }

        .live-shows-page .live-shows-pills .nav-link.active[data-tab="test"] {
            background-color: #dc3545;
        }

        .live-shows-page table.live-shows-table tbody tr:has(.dropdown-menu.show) {
            position: relative;
            z-index: 1055;
        }

        .live-shows-page .dropdown-menu {
            z-index: 1055;
        }

        @media (max-width: 575.98px) {
            .live-shows-page .live-shows-pills {
                flex-direction: column;
                gap: 0.5rem;
            }

            .live-shows-page .live-shows-pills .nav-link {
                text-align: center;
            }
        }
    </style>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 py-3 mb-1">
            {{ __('Manage Live Shows') }}

            <a href="{{ route('admin.live-shows.create') }}" class="btn btn-success btn-sm mx-4 float-end">
                New Live Show
            </a>
        </h2>
    </x-slot>

    <div class="py-6 live-shows-page">
        <div class="container-fluid">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-light border-0 py-3">
                    <ul class="nav nav-pills live-shows-pills flex-wrap gap-2" id="liveShowsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="badabing-shows-tab-btn" data-bs-toggle="pill"
                                data-bs-target="#badabing-shows-tab" type="button" role="tab"
                                aria-controls="badabing-shows-tab" aria-selected="true" data-tab="badabing">
                                Badabing Shows
                                <span class="badge bg-light text-dark ms-1">{{ $badabingShows->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="test-shows-tab-btn" data-bs-toggle="pill"
                                data-bs-target="#test-shows-tab" type="button" role="tab"
                                aria-controls="test-shows-tab" aria-selected="false" data-tab="test">
                                Test Shows
                                <span class="badge bg-danger ms-1">{{ $testShows->count() }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body bg-dark text-light tab-content p-3 p-md-4" id="liveShowsTabContent">
                    <div class="tab-pane fade show active" id="badabing-shows-tab" role="tabpanel"
                        aria-labelledby="badabing-shows-tab-btn" tabindex="0">
                        <div class="live-shows-table-scroll table-responsive">
                            <table id="liveShowsTable"
                                class="table table-striped table-borderless table-dark live-shows-table mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Total Players</th>
                                        <th>Status</th>
                                        <th>Is Test Show</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($badabingShows as $show)
                                        @include('admin.live-shows.partials.show-table-row', [
                                            'show' => $show,
                                            'isTestShow' => false,
                                        ])
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="test-shows-tab" role="tabpanel"
                        aria-labelledby="test-shows-tab-btn" tabindex="0">
                        <div class="live-shows-table-scroll table-responsive">
                            <table id="testShowsTable"
                                class="table table-striped table-borderless table-dark live-shows-table mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Total Players</th>
                                        <th>Status</th>
                                        <th>Is Test Show</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($testShows as $show)
                                        @include('admin.live-shows.partials.show-table-row', [
                                            'show' => $show,
                                            'isTestShow' => true,
                                        ])
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-dashboard-layout>
