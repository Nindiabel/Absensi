    @php
        $page = Request::segment(2);
    @endphp

    <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
        <ul id="sidebarnav">

            <h5 class="mb-0 fw-bold text-primary-emphasis fw-bolder">{{ tenantName() }}</h5>

            <span class="sidebar-divider lg mb-3"></span>

            <li class="sidebar-item">
                <a class="sidebar-link {{ $page == 'app' ? 'active' : '' }}" href="{{ base_url('app') }}" aria-expanded="false"
                    navigate>
                    @include('_admin._layout.icons.dashboard')
                    <span class="hide-menu">Beranda</span>
                </a>
            </li>
            
            <span class="sidebar-divider lg mt-4"></span>

            <li class="nav-small-cap mb-0 mt-0" style="color: #adadad">
                <span class="hide-menu ms-1">APLIKASI</span>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link {{ $page == 'smart-keuangan' ? 'active' : '' }}" href="{{ base_url('smart-keuangan') }}" aria-expanded="false"
                    navigate>
                    @include('_admin._layout.icons.dashboard')
                    <span class="hide-menu">SmartKeuangan</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link {{ $page == 'member' ? 'active' : '' }}" href="{{ base_url('member') }}" aria-expanded="false"
                    navigate>
                    @include('_admin._layout.icons.dashboard')
                    <span class="hide-menu">Member</span>
                </a>
            </li>
             <li class="sidebar-item">
                <a class="sidebar-link {{ $page == 'member-category' ? 'active' : '' }}" href="{{ base_url('member-category') }}" aria-expanded="false"
                    navigate>
                    @include('_admin._layout.icons.dashboard')
                    <span class="hide-menu">Member Kategori</span>
                </a>
            </li>
            <span class="sidebar-divider lg my-4"></span>
            <li class="sidebar-item">
                <a class="sidebar-link {{ $page == 'setting' ? 'active' : '' }}"
                    href="{{ base_url('setting/general') }}" aria-expanded="false" navigate>
                    @include('_admin._layout.icons.setting')
                    <span class="hide-menu">Pengaturan</span>
                </a>
            </li>

            <span class="sidebar-divider lg my-4"></span>
            <li class="sidebar-item">
                <div class="d-grid gap-2">
                    <a class="btn btn-outline-danger text-start rounded-3" href="{{ base_url('auth/logout') }}"
                        aria-expanded="false" onclick="return confirm('Apakah kamu yakin?')">
                        <span class="hide-menu"><b>Keluar Aplikasi</b></span>
                    </a>
                </div>
            </li>
        </ul>
    </nav>
