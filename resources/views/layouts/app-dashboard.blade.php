<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>
        {{ env('APP_NAME') }} - Dashboard
    </title>
    {{-- @include('partials.pwa-admin') --}}
    <link href="{{ asset('/styles/dashboard.css') }}" rel="stylesheet" />
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}

    <link rel="stylesheet" href="{{ asset('/styles/dashboard-style.css') }}">



    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />

    @stack('styles')
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    @include('partials.gtm', ['part' => 'head'])

    <style>
        table.data-table {
            overflow: visible;
            /* override the hidden above */
        }

        /* DataTables wrappers */
        .dataTables_wrapper,
        .dataTables_scroll,
        .dataTables_scrollBody,
        .dataTables_scrollHead {
            overflow: visible !important;
        }

        /* lift the active row above the next ones */
        table.data-table tbody tr:has(.dropdown-menu.show) {
            position: relative;
            z-index: 1055;
        }

        .dropdown-menu {
            z-index: 1055;
        }

        input[readonly] {
            border: 1px solid red !important;
        }
    </style>

</head>

<body class="app-topnav-fixed">
    @include('partials.gtm', ['part' => 'body'])

    <nav class="navbar navbar-expand-xl navbar-dark fixed-top app-topnav">
        <div class="container-fluid app-topnav__inner">
            <a class="navbar-brand app-topnav__brand" href="{{ route('admin.dashboard') }}">
                <span class="app-topnav__brand-mark" aria-hidden="true">
                    <i class="fas fa-play"></i>
                </span>
                <span class="app-topnav__brand-text">{{ env('APP_NAME') }}</span>
            </a>

            <button class="navbar-toggler app-topnav__toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#appPrimaryNav" aria-controls="appPrimaryNav" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="appPrimaryNav">
                <ul class="navbar-nav app-topnav__nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                            href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>

                    @can('can-manage-live-shows')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.live-shows.*') ? 'active' : '' }}"
                                href="{{ route('admin.live-shows.index') }}">
                                <i class="bi bi-camera-video-fill me-1"></i> Live Streams
                            </a>
                        </li>
                    @endcan

                    @can('can-manage-quiz-questions')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.live-show-quizzes.*') ? 'active' : '' }}"
                                href="{{ route('admin.live-show-quizzes.index') }}">
                                <i class="bi bi-question-circle-fill me-1"></i> Quiz Questions
                            </a>
                        </li>
                    @endcan

                    @can('can-manage-media-gallery')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.media-gallery.*') ? 'active' : '' }}"
                                href="{{ route('admin.media-gallery.index') }}">
                                <i class="bi bi-images me-1"></i> Media Gallery
                            </a>
                        </li>
                    @endcan

                    @can('can-manage-players')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.players.*') ? 'active' : '' }}"
                                href="{{ route('admin.players.index') }}">
                                <i class="bi bi-people-fill me-1"></i> Players
                            </a>
                        </li>
                    @endcan

                    @can('can-manage-analytics')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}"
                                href="{{ route('admin.analytics.index') }}">
                                <i class="bi bi-graph-up-arrow me-1"></i> Analytics
                            </a>
                        </li>
                    @endcan

                    @canany(['can-manage-settings', 'can-manage-gtm', 'can-manage-push-notifications'])
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs(['admin.settings.*', 'admin.gtm.*', 'admin.push-notifications.*', 'admin.push-subscriptions.*']) ? 'active' : '' }}"
                                href="#" id="settingsDropdown" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-sliders me-1"></i> Settings
                            </a>
                            <ul class="dropdown-menu app-topnav__dropdown" aria-labelledby="settingsDropdown">
                                @can('can-manage-settings')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                                            <i class="bi bi-sliders me-2"></i> App Settings
                                        </a>
                                    </li>
                                @endcan
                                @can('can-manage-gtm')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.gtm.index') }}">
                                            <i class="bi bi-google me-2"></i> Google Tag Manager
                                        </a>
                                    </li>
                                @endcan
                                {{-- @can('can-manage-push-notifications') --}}
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('admin.push-subscriptions.*') ? 'active' : '' }}"
                                            href="{{ route('admin.push-subscriptions.index') }}">
                                            <i class="bi bi-phone-vibrate me-2"></i> Push Subscriptions
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('admin.push-notifications.*') ? 'active' : '' }}"
                                            href="{{ route('admin.push-notifications.index') }}">
                                            <i class="bi bi-bell-fill me-2"></i> Push Notifications
                                        </a>
                                    </li>
                                {{-- @endcan --}}
                            </ul>
                        </li>
                    @endcanany

                    @canany(['can-manage-users', 'can-manage-roles', 'can-manage-permissions'])
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs(['admin.users.*', 'admin.roles.*', 'admin.permissions.*']) ? 'active' : '' }}"
                                href="#" id="accessDropdown" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-shield-lock-fill me-1"></i> Access Control
                            </a>
                            <ul class="dropdown-menu app-topnav__dropdown" aria-labelledby="accessDropdown">
                                @can('can-manage-users')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.users.index') }}">
                                            <i class="bi bi-person-badge-fill me-2"></i> In App Users
                                        </a>
                                    </li>
                                @endcan
                                @can('can-manage-roles')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.roles.index') }}">
                                            <i class="bi bi-shield-lock-fill me-2"></i> Roles
                                        </a>
                                    </li>
                                @endcan
                                @can('can-manage-permissions')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.permissions.index') }}">
                                            <i class="bi bi-key-fill me-2"></i> Permissions
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                </ul>

                <div class="app-topnav__user ms-lg-2">
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle app-topnav__user-btn d-flex align-items-center gap-2"
                                id="navbarUserDropdown" href="#" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <span class="app-topnav__avatar" aria-hidden="true">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                                <span class="app-topnav__user-name text-truncate">{{ Auth::user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end app-topnav__dropdown"
                                aria-labelledby="navbarUserDropdown">
                                @can('can-manage-settings')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                                            <i class="bi bi-sliders me-2"></i> App Settings
                                        </a>
                                    </li>
                                @endcan
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.password.form') }}">
                                        <i class="bi bi-gear-fill me-2"></i> Change Password
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider" />
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="javascript:void(0)"
                                        onclick="document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div id="app-layout-content">
        <div class="container-fluid">
            @if (isset($header))
                <header class="page-header">
                    {{ $header ?? '' }}
                </header>
            @endif
            <main id="main-content">
                {{ $slot }}
            </main>
        </div>
        <footer class="py-4 app-dashboard-footer mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">Copyright &copy; {{ env('APP_NAME') }} {{ date('Y') }}</div>
                    <div>
                        <a href="#">Privacy Policy</a>
                        &middot;
                        <a href="#">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <form action="{{ route('logout') }}" id="logout-form" method="post"></form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


    <script src="{{ asset('/js/scripts.js') }}"></script>
    {{-- <script src="{{ asset('/demo/chart-area-demo.js') }}"></script>
    <script src="{{ asset('/demo/chart-bar-demo.js') }}"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
        crossorigin="anonymous"></script>
    <!-- Bootstrap 5 JS Bundle -->

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.data-table').DataTable({
                lengthChange: true,
                "pageLength": 20,
                // "lengthMenu": [
                //     [10, 20, 50, -1],
                //     [10, 20, 50, "All"]
                // ]
            });
        });
    </script>



    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>



    @stack('scripts')
</body>

</html>
