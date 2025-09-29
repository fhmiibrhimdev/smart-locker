<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ $title }} - Smart Locker Parcel</title>

    <!-- General CSS Files -->
    <link rel="stylesheet" href="{{ asset('/assets/stisla/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.2.0/css/all.css" />
    <link rel="stylesheet" href="https://static.fontawesome.com/css/fontawesome-app.css" />
    <link rel="stylesheet" href="{{ asset('assets/midragon/css/custom.css') }}">
    <link rel="icon" href="{{ asset('/assets/MIDRAGON.png') }}">

    @stack('general-css')

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('/assets/stisla/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/stisla/css/components.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="layout-3" style="font-family: 'Inter', sans-serif">
    <div id="app">
        <div class="container main-wrapper">
            @if (request()->is('pickup/*'))
                <div class="navbar-bg -tw-mt-10"></div>
                <nav class="navbar navbar-expand-lg main-navbar">
                    <a href="{{ url('/') }}" class="navbar-brand sidebar-gone-hide">SMART LOCKER PARCEL</a>
                </nav>
            @else
                <div class="navbar-bg"></div>
                <nav class="navbar navbar-expand-lg main-navbar">
                    <a href="{{ url('dashboard') }}" class="navbar-brand sidebar-gone-hide">SMART LOCKER PARCEL</a>
                    <div class="navbar-nav">
                        <a href="#" class="nav-link sidebar-gone-show" data-toggle="sidebar">
                            <i class="fas fa-bars"></i>
                        </a>
                    </div>
                    <form class="ml-auto form-inline">
                    </form>
                    <ul class="navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                                <div class="d-sm-none d-lg-inline-block">Hi, {{ Auth::user()->name }}</div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <div class="dropdown-title">Logged in 5 min ago</div>
                                <a href="/profile" class="dropdown-item has-icon">
                                    <i class="far fa-user"></i> Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a href="{{ route('logout') }}" class="dropdown-item text-danger has-icon" onclick="event.preventDefault();
                                    this.closest('form').submit();">
                                        <i class="far fa-sign-out-alt"></i> Logout
                                    </a>
                                </form>
                            </div>
                        </li>
                    </ul>
                </nav>
    
                <nav class="navbar navbar-secondary navbar-expand-lg">
                    <div class="container">
                        <ul class="navbar-nav">
                            @if (Auth::user()->hasRole('admin'))
                            <li class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                                <a href="/dashboard" class="nav-link">
                                    <i class="far fa-home"></i><span>Dashboard</span>
                                </a>
                            </li>
                            <li
                                class="nav-item dropdown {{ request()->is('lokasi') || request()->is('loker') ? 'active' : '' }}">
                                <a href="#" data-toggle="dropdown" class="nav-link has-dropdown">
                                    <i class="fas fa-chalkboard"></i><span>Master Data</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="nav-item {{ request()->is('lokasi') ? 'active' : '' }}">
                                        <a href="/lokasi" class="nav-link">Lokasi</a>
                                    </li>
                                    <li class="nav-item {{ request()->is('loker') ? 'active' : '' }}">
                                        <a href="/loker" class="nav-link">Loker</a>
                                    </li>
                                </ul>
                            </li>
                            @elseif (Auth::user()->hasRole('kurir'))
                            <li class="nav-item {{ request()->is('kurir/dashboard') ? 'active' : '' }}">
                                <a href="/kurir/dashboard" class="nav-link">
                                    <i class="far fa-home"></i><span>Dashboard</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </nav>
            @endif

            <!-- Main Content -->
            <div class="main-content">
                {{ $slot }}
            </div>
            <footer class="main-footer">
                <div class="footer-left">
                    Copyright &copy; 2025 <div class="bullet"></div> Created By <a
                        href="http://fahmiibrahimdev.tech/">Team Crash Dev</a>
                </div>
                <div class="footer-right">
                    1.0.0
                </div>
            </footer>
        </div>
    </div>

    <!-- General JS Scripts -->
    <script src="{{ asset('/assets/midragon/select2/jquery.min.js') }}"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script>

    <!-- JS Libraies -->
    <script src="{{ asset('assets/midragon/js/sweetalert2@11.js') }}"></script>
    @stack('js-libraries')

    <!-- Page Specific JS File -->
    <script src="{{ asset('/assets/stisla/js/stisla.js') }}"></script>
    <script>
        window.addEventListener('swal:modal', event => {
            Swal.fire({
                title: event.detail[0].message,
                text: event.detail[0].text,
                icon: event.detail[0].type,
            })
            $("#formDataModal").modal("hide");
        })
        window.addEventListener('swal:confirm', event => {
            Swal.fire({
                title: event.detail[0].message,
                text: event.detail[0].text,
                icon: event.detail[0].type,
                showCancelButton: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('delete')
                }
            })
        })
        window.onbeforeunload = function () {
            window.scrollTo(5, 75);
        };

    </script>

    <!-- Template JS File -->
    <script src="{{ asset('/assets/stisla/js/scripts.js') }}"></script>
    <script src="{{ asset('/assets/stisla/js/custom.js') }}"></script>
    @stack('scripts')
</body>

</html>
