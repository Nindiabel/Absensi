<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row mb-0 align-items-center">
                    <div class="col-md-8">
                        <h4 class="fw-bolder mb-1">{{ $page['title'] ?? 'Pengaturan Pegawai' }}</h4>
                        <small class="text-muted">
                            Pengaturan akun dan keamanan pegawai
                        </small>
                    </div>
                </div>

                <ul class="nav nav-pills mt-4">
                    <li class="nav-item me-2">
                        <a class="nav-link rounded-5 px-4 shadow-sm"
                           href="{{ base_url('setting/general') }}"
                           navigate>
                            Umum
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link rounded-5 px-4 active shadow-sm"
                           aria-current="page"
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
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="mb-1 fw-bold">Ubah Password Aplikasi</h4>
                        <small class="text-muted">
                            Gunakan password yang kuat dan mudah Anda ingat.
                        </small>
                    </div>
                </div>

                <div class="mt-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <b>Terjadi kesalahan pada proses input data</b>
                            <br>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST"
                         action="{{ url('pegawai/setting/change-password') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="current_password" class="form-label">
                                Password Saat Ini
                            </label>
                            <input type="password" class="form-control" name="current_password" id="current_password" required>

                            <div class="mt-1">
                                <small class="text-muted">
                                    Pada Email:
                                    <b>{{ Auth::user()->email ?? '-' }}</b>
                                </small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Password Baru
                            </label>
                            <input type="password" class="form-control" name="password" id="password" minlength="6" required>
                        </div>

                        <div class="mb-3">
                            <label for="re_password" class="form-label">
                                Konfirmasi Password
                            </label>
                            <input type="password" class="form-control" name="re_password" id="re_password" minlength="6" required>
                        </div>

                        <button type="submit" class="btn btn-primary bg-gradient">
                            <b>Ubah Password</b>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>