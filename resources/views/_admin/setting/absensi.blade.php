<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="row mb-0">
                    <div class="col-md-6">
                        <h4 class="fw-bolder mb-4">{{ $page['title'] }}</h4>
                    </div>
                </div>

                <ul class="nav nav-pills">
                    <li class="nav-item me-2">
                        <a class="nav-link rounded-5 px-4 shadow-sm"
                           href="{{ base_url('setting/general') }}" navigate>
                            Umum
                        </a>
                    </li>
                    <li class="nav-item me-2">
                        <a class="nav-link rounded-5 px-4 active shadow-sm"
                           href="{{ base_url('setting/absensi') }}" navigate>
                            Aturan Absensi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-5 px-4 shadow-sm"
                           href="{{ base_url('setting/change-password') }}" navigate>
                            Ubah Password
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="fw-bold mb-1">Aturan Absensi</h5>
                <small class="text-muted d-block mb-4">
                    Pengaturan ini digunakan sebagai acuan sistem absensi secara otomatis.
                    Cukup diatur sekali dan ubah hanya jika ada perubahan kebijakan.
                </small>

                @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $jamMasuk       = $setting->jam_masuk ?? '07:00:00';
                    $toleransi      = $setting->toleransi_terlambat ?? 30;
                    $batasTerlambat = date('H:i', strtotime($jamMasuk) + ($toleransi * 60));
                @endphp

                <form action="{{ url('admin/setting/absensi') }}" method="POST">
                    @csrf

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label">
                            Jam Masuk Kerja
                            <small class="text-muted d-block fw-normal">
                                Jam clock-in resmi pegawai
                            </small>
                        </label>
                        <div class="col-sm-4">
                            <input type="time"
                                   name="jam_masuk"
                                   class="form-control"
                                   value="{{ date('H:i', strtotime($jamMasuk)) }}"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label">
                            Toleransi Keterlambatan
                            <small class="text-muted d-block fw-normal">
                                Pegawai dianggap terlambat jika clock-in melebihi batas ini
                            </small>
                        </label>
                        <div class="col-sm-4">
                            <div class="input-group">
                                <input type="number"
                                       name="toleransi_terlambat"
                                       class="form-control"
                                       value="{{ $toleransi }}"
                                       min="0"
                                       required>
                                <span class="input-group-text">menit</span>
                            </div>
                            <small class="text-muted">
                                Dengan pengaturan saat ini, pegawai terlambat jika
                                clock-in setelah <strong>{{ $batasTerlambat }}</strong>
                            </small>
                        </div>
                    </div>

                    <div class="mb-4 row">
                        <label class="col-sm-4 col-form-label">
                            Jam Pulang Kerja
                            <small class="text-muted d-block fw-normal">
                                Jam clock-out resmi pegawai
                            </small>
                        </label>
                        <div class="col-sm-4">
                            <input type="time"
                                   name="jam_pulang"
                                   class="form-control"
                                   value="{{ date('H:i', strtotime($setting->jam_pulang ?? '16:00:00')) }}"
                                   required>
                        </div>
                    </div>

                    <div class="mb-0 row">
                        <label class="col-sm-4 col-form-label"></label>
                        <div class="col-sm-4">
                            <button class="btn btn-primary bg-gradient" type="submit">
                                <b>Simpan Pengaturan</b>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>