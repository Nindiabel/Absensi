<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row mb-0 align-items-center">
                    <div class="col-md-8">
                        <h4 class="fw-bolder mb-1">{{ $page['title'] ?? 'Pengaturan Pegawai' }}</h4>
                        <small class="text-muted">
                            Pengaturan akun dan tampilan untuk pegawai
                        </small>
                    </div>
                </div>

                <ul class="nav nav-pills mt-4">
                    <li class="nav-item me-2">
                        <a class="nav-link rounded-5 px-4 active shadow-sm"
                           href="{{ base_url('setting/general') }}"
                           navigate>
                            Umum
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link rounded-5 px-4 shadow-sm"
                           href="{{ base_url('setting/change-password') }}"
                           navigate>
                            Ubah Password
                        </a>
                    </li>
                </ul>

            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- DATA PEGAWAI --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Data Pegawai</h5>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Nama</label>
                    <div class="col-sm-9">
                        <input type="text"
                               class="form-control"
                               value="{{ Auth::user()->name ?? '-' }}"
                               readonly>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">Email</label>
                    <div class="col-sm-9">
                        <input type="text"
                               class="form-control no-uppercase"
                               value="{{ Auth::user()->email ?? '-' }}"
                               readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PENGATURAN TAMPILAN --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Pengaturan Tampilan</h5>

                <form action="{{ base_url('setting/general') }}" method="POST">
                    @csrf

                    <div class="mb-3 row">
                        <label for="theme" class="col-sm-3 col-form-label">
                            Warna Tampilan
                        </label>

                        <div class="col-sm-5">
                            <select name="theme" id="theme" class="form-select">
                                <option value="light" @selected(($theme ?? 'light') == 'light')>
                                    Terang (Light Mode)
                                </option>
                                <option value="dark" @selected(($theme ?? 'light') == 'dark')>
                                    Gelap (Dark Mode)
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-0 row">
                        <label class="col-sm-3 col-form-label"></label>

                        <div class="col-sm-5">
                            <button class="btn btn-primary bg-gradient" type="submit">
                                <b>Terapkan Perubahan</b>
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>