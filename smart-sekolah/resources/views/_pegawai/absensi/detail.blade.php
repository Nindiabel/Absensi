<div class="row g-3">

    {{-- Header --}}
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">

                    <div class="col-md-6">
                        <h4 class="mb-0">Detail <b>{{ $page['title'] }}</b></h4>
                    </div>

                    <div class="col-md-6 text-end">
                        <a href="{{ base_url($page['route'] . '/') }}"
                            class="btn btn-outline-indigo btn-sm fw-bold"
                            navigate>
                            <b>← Kembali</b>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- FOTO SCAN MASUK --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <small class="text-muted d-block mb-2">Foto Scan Masuk</small>

                @if (in_array($data->status_kehadiran ?? '', ['alpha', 'izin', 'sakit']))
                    <div class="d-flex flex-column align-items-center justify-content-center"
                        style="height:350px; background:#f8f9fa; border-radius:8px; width:100%;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                            fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                            <circle cx="9" cy="9" r="2"/>
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                        </svg>
                        <p class="text-muted mt-3 mb-0">Foto tidak tersedia</p>
                        <small class="text-muted">
                            Pegawai {{ ucfirst($data->status_kehadiran) }}
                        </small>
                    </div>
                @elseif (!empty($data->foto_absensi))
                    <img src="{{ asset('storage/' . $data->foto_absensi) }}"
                        class="img-fluid rounded"
                        style="width:100%; max-height:350px; object-fit:cover;">
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center"
                        style="height:350px; background:#f8f9fa; border-radius:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                            stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                            <circle cx="9" cy="9" r="2" />
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                        </svg>
                        <p class="text-muted mt-3 mb-0">Belum scan masuk</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- FOTO SCAN PULANG --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 text-center">
                <small class="text-muted d-block mb-2">Foto Scan Pulang</small>

                @if (!empty($data->foto_pulang))
                    <img src="{{ asset('storage/' . $data->foto_pulang) }}" class="img-fluid rounded shadow-sm"
                        style="width:100%; height:350px; object-fit:cover;">
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center"
                        style="height:350px; background:#f8f9fa; border-radius:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                            stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                            <circle cx="9" cy="9" r="2" />
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                        </svg>
                        <p class="text-muted mt-3 mb-0">
                            {{ empty($data->jam_pulang) ? 'Belum scan pulang' : 'Foto tidak tersedia' }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Info --}}
    <div class="col-md-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">

                @if (in_array($data->status_kehadiran ?? '', ['alpha', 'izin', 'sakit']))
                    <div class="alert alert-{{ $data->status_kehadiran === 'alpha' ? 'danger' : ($data->status_kehadiran === 'izin' ? 'primary' : 'warning') }} mb-4 d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span>
                            Anda tercatat <strong>{{ ucfirst($data->status_kehadiran) }}</strong> pada hari ini.
                            Jika terdapat kekeliruan, silakan hubungi admin.
                        </span>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Nama</label>
                        <p class="mb-0 fs-5 fw-bold text-dark">
                            {{ $data->member_name ?? '-' }}
                        </p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Kategori</label>
                        <p class="mb-0 fs-5 fw-bold text-dark">
                            {{ $data->category_name ?? '-' }}
                        </p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Tanggal</label>
                        <p class="mb-0 fs-5 fw-bold text-dark">
                            {{ !empty($data->tanggal_absensi) ? date('d F Y', strtotime($data->tanggal_absensi)) : '-' }}
                        </p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Jam Masuk</label>
                        <p class="mb-0 fs-5 fw-bold text-dark">
                            {{ !empty($data->jam_masuk) ? date('H:i', strtotime($data->jam_masuk)) : '-' }}
                        </p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Jam Pulang</label>
                        <p class="mb-0 fs-5 fw-bold text-dark">
                            {{ !empty($data->jam_pulang) ? date('H:i', strtotime($data->jam_pulang)) : '-' }}
                        </p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="text-muted small">Status Kehadiran</label>
                        <div class="mt-1">
                            @if (($data->status_final ?? '') == 'hadir')
                                <span class="badge bg-success">Hadir</span>
                            @elseif (($data->status_final ?? '') == 'terlambat')
                                <span class="badge bg-warning text-dark">Terlambat</span>
                            @elseif (($data->status_final ?? '') == 'izin')
                                <span class="badge bg-primary">Izin</span>
                            @elseif (($data->status_final ?? '') == 'sakit')
                                <span class="badge bg-warning text-dark">Sakit</span>
                            @elseif (($data->status_final ?? '') == 'alpha')
                                <span class="badge bg-danger">Alpha</span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </div>
                    </div>
                    @if (!empty($data->catatan))
                        <div class="col-md-12 mb-0 mt-2">
                            <label class="text-muted small">Catatan</label>
                            <p class="mb-0 text-dark">{{ $data->catatan }}</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

</div>
