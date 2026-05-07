<x-app-dashboard-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Players') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            {{-- Filters --}}
            <div class="card mb-4">
                <div class="card-header bg-dark text-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-funnel-fill me-2"></i>Filters</h5>
                    <button type="button" class="btn btn-sm btn-outline-light" data-bs-toggle="collapse"
                        data-bs-target="#playersFilters" aria-expanded="true" aria-controls="playersFilters">
                        Toggle
                    </button>
                </div>
                <div id="playersFilters" class="collapse show">
                    <div class="card-body bg-dark text-light">
                        <form id="playersFilterForm" class="row g-3">
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small mb-1">Registered From</label>
                                <input type="date" name="filter_registered_from"
                                    class="form-control form-control-sm filter-input">
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small mb-1">Registered To</label>
                                <input type="date" name="filter_registered_to"
                                    class="form-control form-control-sm filter-input">
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small mb-1">Last Show Played From</label>
                                <input type="date" name="filter_last_show_from"
                                    class="form-control form-control-sm filter-input">
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small mb-1">Last Show Played To</label>
                                <input type="date" name="filter_last_show_to"
                                    class="form-control form-control-sm filter-input">
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small mb-1">Has Referrer?</label>
                                <select name="filter_has_referrer" class="form-select form-select-sm filter-input">
                                    <option value="">All</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small mb-1">Min. Games Played</label>
                                <input type="number" min="0" name="filter_min_games"
                                    class="form-control form-control-sm filter-input" placeholder="0">
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small mb-1">Min. Referred Users</label>
                                <input type="number" min="0" name="filter_min_referred"
                                    class="form-control form-control-sm filter-input" placeholder="0">
                            </div>
                            <div class="col-md-3 col-sm-6 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-funnel"></i> Apply
                                </button>
                                <button type="button" id="resetFiltersBtn" class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Players Table --}}
            <div class="card">
                <div class="card-header bg-dark text-light">
                    <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Players</h5>
                </div>
                <div class="card-body bg-dark text-light">
                    <div class="table-responsive">
                        <table id="playersTable" class="table table-striped table-borderless table-dark w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Username</th>
                                    <th>Registered At</th>
                                    <th>Live Games Played</th>
                                    <th>Last Game Played</th>
                                    <th>Referred Users</th>
                                    <th>Referred By</th>
                                    <th>Is Affiliate</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                                <tr class="column-search-row">
                                    <th><input type="text" class="form-control form-control-sm" placeholder="ID"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Name"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Email"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Username"></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th><input type="text" class="form-control form-control-sm"
                                            placeholder="Referrer"></th>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Is Affiliate"></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            #playersTable thead th {
                vertical-align: middle;
                white-space: nowrap;
            }

            .column-search-row th {
                padding: 4px 6px;
            }

            .column-search-row input {
                background-color: #2d2d2d;
                border: 1px solid #444;
                color: #fff;
            }

            .column-search-row input::placeholder {
                color: #aaa;
            }

            #playersTable_wrapper .dataTables_filter input,
            #playersTable_wrapper .dataTables_length select {
                background-color: #2d2d2d;
                border: 1px solid #444;
                color: #fff;
            }

            #playersTable_wrapper .dataTables_info,
            #playersTable_wrapper .dataTables_paginate,
            #playersTable_wrapper .dataTables_length,
            #playersTable_wrapper .dataTables_filter {
                color: #fff;
            }

            #playersTable_wrapper .paginate_button {
                color: #fff !important;
            }

            #playersTable_wrapper .paginate_button.current,
            #playersTable_wrapper .paginate_button.current:hover {
                background: #0d6efd !important;
                border-color: #0d6efd !important;
                color: #fff !important;
            }

            #playersTable_wrapper .paginate_button:hover {
                background: #495057 !important;
                border-color: #495057 !important;
                color: #fff !important;
            }

            #playersTable_wrapper .dataTables_processing {
                background: rgba(0, 0, 0, .65);
                color: #fff;
                border: 0;
            }

            #playersTableLoader .spinner-border {
                width: 4rem;
                height: 4rem;
                position: absolute;
                top: 50%;
                left: 50%;
            }

            
        </style>
    @endpush

    @push('scripts')
        <script>
            $(function() {
                const dataUrl = "{{ route('admin.players.data') }}";

                // Insert loader markup (hidden by default)
                if ($('#playersTableLoader').length === 0) {
                    $('body').append(`
                        <div id="playersTableLoader" style="display:none; position:fixed;bottom:10vh;left:0;width:100vw;height:80vh;z-index:2000;background:rgba(0,0,0,.5);justify-content:center;align-items:center;">
                            <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    `);
                }

                const showLoader = () => {
                    $('#playersTableLoader').fadeIn(150);
                };
                const hideLoader = () => {
                    $('#playersTableLoader').fadeOut(150);
                };

                const debounce = (fn, delay = 500) => {
                    let timer;
                    return function (...args) {
                        clearTimeout(timer);
                        timer = setTimeout(() => fn.apply(this, args), delay);
                    };
                };

                const table = $('#playersTable').DataTable({
                    processing: true,
                    serverSide: true,
                    deferRender: true,
                    searchDelay: 700,
                    pageLength: 100,
                    lengthMenu: [
                        [50, 100, 200, 500],
                        [50, 100, 200, 500]
                    ],
                    order: [
                        [0, 'desc']
                    ],
                    orderCellsTop: true,
                    ajax: {
                        url: dataUrl,
                        data: function (d) {
                            $('#playersFilterForm').serializeArray().forEach(function (item) {
                                d[item.name] = item.value;
                            });
                        },
                        beforeSend: function () {
                            showLoader();
                        },
                        complete: function () {
                            hideLoader();
                        }
                    },
                    columns: [
                        { data: 'id' },
                        { data: 'name' },
                        { data: 'email' },
                        { data: 'user_name' },
                        { data: 'created_at' },
                        { data: 'live_games_played', className: 'text-center' },
                        { data: 'last_game_played_at' },
                        { data: 'referred_users_count', className: 'text-center' },
                        { data: 'referred_by_username' },
                        {data: 'is_affiliate', className: 'text-center'},
                        { data: 'actions', orderable: false, searchable: false, className: 'text-end' }
                    ],
                });

                // Also show/hide loader when DataTables triggers AJAX requests (for maximum reliability)
                table.on('preXhr.dt', function () {
                    showLoader();
                });
                table.on('xhr.dt', function () {
                    hideLoader();
                });

                // Per-column search inputs
                $('#playersTable thead .column-search-row input').on(
                    'keyup change',
                    debounce(function () {
                        const colIndex = $(this).closest('th').index();
                        const value = $(this).val();
                        if (table.column(colIndex).search() !== value) {
                            table.column(colIndex).search(value).draw();
                        }
                    }, 500)
                );

                // Stop sort triggering when interacting with the search input row
                $('#playersTable thead .column-search-row input').on('click', function (e) {
                    e.stopPropagation();
                });

                // Apply custom filters
                $('#playersFilterForm').on('submit', function (e) {
                    e.preventDefault();
                    table.draw();
                });

                // Reset filters and column searches
                $('#resetFiltersBtn').on('click', function () {
                    $('#playersFilterForm')[0].reset();
                    $('#playersTable thead .column-search-row input').val('');
                    table.columns().search('');
                    table.search('');
                    table.draw();
                });
            });
        </script>
    @endpush
</x-app-dashboard-layout>
