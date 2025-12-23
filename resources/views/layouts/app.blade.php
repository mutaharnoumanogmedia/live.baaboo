<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{ config('app.name', 'baaboo Live') }}
    </title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('assets/styles/dashboard-style.css') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />
    @stack('styles')

</head>

<body>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar">
            <div class="sidebar-header">
                <h3><i class="bi bi-play-circle-fill"></i>baaboo Live Admin</h3>
            </div>

            <ul class="list-unstyled components">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.live-shows.index') }}">
                        <i class="bi bi-camera-video-fill"></i> Live Streams
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.live-show-quizzes.index') }}">
                        <i class="bi bi-question-circle-fill"></i> Quiz Questions
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.players.index') }}">
                        <i class="bi bi-people-fill"></i> Players
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="bi bi-trophy-fill"></i> Winners
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="bi bi-graph-up-arrow"></i> Analytics
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.password.form') }}">
                        <i class="bi bi-gear-fill"></i> Settings
                    </a>
                </li>
                <li class="mt-4">
                    <a href="#"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Page Content -->
        <div id="content" class="w-100">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <button class="btn btn-youtube me-3" id="sidebarCollapse">
                        <i class="bi bi-list"></i>
                    </button>

                    <div class="d-flex align-items-center">
                        <h4 class="navbar-brand mb-0">Dashboard Overview</h4>
                        <span class="live-badge ms-3">Live</span>
                    </div>

                    <div class="d-flex align-items-center ms-auto">
                        <div class="input-group me-3 d-none d-lg-flex" style="width: 300px;">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search players, questions...">
                        </div>

                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle"
                                id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="https://ui-avatars.com/api/?name=Game+Master&background=FF0000&color=fff"
                                    alt="Admin" width="40" height="40" class="rounded-circle me-2">
                                <div class="d-none d-md-block">
                                    <strong style="color: var(--text-primary);">Game Master</strong>
                                    <div style="color: var(--text-secondary); font-size: 0.8rem;">Administrator</div>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="#"><i
                                            class="bi bi-person-circle me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.password.form') }}"><i
                                            class="bi bi-gear me-2"></i>Settings</a>
                                </li>
                                <li><a class="dropdown-item" href="#"><i
                                            class="bi bi-bell me-2"></i>Notifications</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                            class="bi bi-box-arrow-right me-2"></i>
                                        Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>


            <!-- Main Content -->
            <div class="container-fluid w-100">

                @if (isset($header))
                    {{ $header }}
                @endif

                <div id="slot">
                    {{ $slot }}
                </div>
                <!-- Stats Cards -->

            </div>

        </div>

    </div>

    <form action="{{ route('logout') }}" id="logout-form" method="post"></form>
</body>


<!-- Scripts -->
<!-- jQuery CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('.data-table').DataTable();
    });

    const sidebar = document.getElementById("sidebar");
    const content = document.getElementById("content");
    const sidebarCollapse = document.getElementById("sidebarCollapse");
    const sidebarOverlay = document.getElementById("sidebarOverlay");

    // Toggle sidebar
    sidebarCollapse.addEventListener("click", () => {
        if (window.innerWidth < 1200) {
            // For mobile/tablet
            sidebar.classList.toggle("show");
            sidebarOverlay.classList.toggle("show");
        } else {
            // For desktop
            sidebar.classList.toggle("collapsed");
            content.classList.toggle("expanded");
        }
    });

    // Hide sidebar when clicking overlay (mobile)
    sidebarOverlay.addEventListener("click", () => {
        sidebar.classList.remove("show");
        sidebarOverlay.classList.remove("show");
    });

    // Auto reset sidebar on resize
    window.addEventListener("resize", () => {
        if (window.innerWidth >= 1200) {
            sidebar.classList.remove("show");
            sidebarOverlay.classList.remove("show");
        }
    });


    document.querySelectorAll('#sidebar ul.components li a').forEach(link => {
        if (link.href === window.location.href) {
            link.classList.add('active');
        }
    });
</script>



<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Document loaded');

        $('.question-slider').slick({
            infinite: false,
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: true,
            dots: true,
            adaptiveHeight: true
        });
    });
</script>


@stack('scripts')

</html>
