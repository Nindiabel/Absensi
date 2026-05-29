@php
    $segment = Request::segment(2); // untuk /pegawai/app → 'app', /pegawai/absensi → 'absensi'
@endphp

<nav class="sidebar-nav scroll-sidebar" data-simplebar="">
    <ul id="sidebarnav">

        <h5 class="mb-0 fw-bold text-primary-emphasis fw-bolder">{{ tenantName() }}</h5>

        <span class="sidebar-divider lg mb-3"></span>

        <li class="sidebar-item">
            <a class="sidebar-link {{ $segment == 'app' ? 'active' : '' }}"
               href="/pegawai/app"
               aria-expanded="false" navigate>
                @include('_admin._layout.icons.dashboard')
                <span class="hide-menu">Beranda</span>
            </a>
        </li>

        <span class="sidebar-divider lg mt-4"></span>

        <li class="nav-small-cap mb-0 mt-0" style="color: #adadad">
            <span class="hide-menu ms-1">MENU</span>
        </li>

        <li class="sidebar-item">
            <a class="sidebar-link {{ $segment == 'absensi' ? 'active' : '' }}"
               href="/pegawai/absensi"
               aria-expanded="false" navigate>
                @include('_admin._layout.icons.book')
                <span class="hide-menu">Absensi</span>
            </a>
        </li>

        <span class="sidebar-divider lg my-4"></span>

        <li class="sidebar-item">
            <a class="sidebar-link {{ $segment == 'setting' ? 'active' : '' }}"
               href="/pegawai/setting/general"
               aria-expanded="false" navigate>
                @include('_admin._layout.icons.setting')
                <span class="hide-menu">Pengaturan</span>
            </a>
        </li>

        <span class="sidebar-divider lg my-4"></span>

        <li class="sidebar-item">
            <div class="d-grid gap-2">
                <a class="btn btn-outline-danger text-start rounded-3"
                   href="/pegawai/auth/logout"
                   aria-expanded="false"
                   onclick="return confirm('Apakah kamu yakin?')">
                    <span class="hide-menu"><b>Keluar Aplikasi</b></span>
                </a>
            </div>
        </li>

    </ul>
</nav>