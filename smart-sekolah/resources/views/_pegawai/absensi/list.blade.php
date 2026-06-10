<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <div class="row mb-4 align-items-center">
                    <div class="col-md-6">
                        <h4 class="fw-bold mb-1">{{ $page['title'] ?? 'Absensi Saya' }}</h4>
                        <small class="text-muted">Data absensi</small>
                    </div>

                    <div class="col-md-6 text-end">
                        <a href="/pegawai/absensi/exportToExcel{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                            class="btn btn-success fw-bold me-2">
                            Download
                        </a>
                    </div>
                </div>

                {{-- action pakai /pegawai/absensi langsung, bukan base_url() --}}
                <form action="/pegawai/absensi" method="GET">
                    <input type="hidden" name="filter_on" value="true">

                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <div style="max-width: 180px; width:100%;">
                            <input type="month" name="bulan" value="{{ $filter['bulan'] ?? '' }}" class="form-control"
                                style="height:44px; border-radius:8px;">
                        </div>

                        <div class="position-relative" style="max-width: 200px; width:100%;">
                            <select name="status_kehadiran" class="form-select" style="height:44px; border-radius:8px;">
                                <option value="">Semua Status</option>
                                <option value="hadir" {{ ($filter['status_kehadiran'] ?? '') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="terlambat" {{ ($filter['status_kehadiran'] ?? '') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                                <option value="izin" {{ ($filter['status_kehadiran'] ?? '') == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ ($filter['status_kehadiran'] ?? '') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="alpha" {{ ($filter['status_kehadiran'] ?? '') == 'alpha' ? 'selected' : '' }}>Alpha</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Cari</button>

                        @if (!empty($filter['filter_on']))
                            <a href="/pegawai/absensi" class="btn btn-outline-warning">Reset</a>
                        @endif
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>NAMA</th>
                                <th>TANGGAL</th>
                                <th class="text-center">JAM MASUK</th>
                                <th class="text-center">JAM PULANG</th>
                                <th class="text-center">STATUS KEHADIRAN</th>
                                <th class="text-center">AKSI</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($data as $d)
                                <tr>
                                    <td>{{ $d->member_name ?? '-' }}</td>

                                    <td>
                                        {{ !empty($d->tanggal_absensi) ? date('d F Y', strtotime($d->tanggal_absensi)) : '-' }}
                                    </td>

                                    <td class="text-center">
                                        {{ !empty($d->jam_masuk) ? date('H:i', strtotime($d->jam_masuk)) : '-' }}
                                    </td>

                                    <td class="text-center">
                                        {{ !empty($d->jam_pulang) ? date('H:i', strtotime($d->jam_pulang)) : '-' }}
                                    </td>

                                    <td class="text-center">
                                        @php $status = $d->status_final ?? $d->status_kehadiran ?? ''; @endphp

                                        @if ($status == 'hadir')
                                            <span class="badge bg-success-subtle text-success">Hadir</span>
                                        @elseif ($status == 'terlambat')
                                            <span class="badge bg-warning-subtle text-warning">Terlambat</span>
                                        @elseif ($status == 'izin')
                                            <span class="badge bg-primary-subtle text-primary">Izin</span>
                                        @elseif ($status == 'sakit')
                                            <span class="badge bg-warning-subtle text-warning">Sakit</span>
                                        @elseif ($status == 'alpha')
                                            <span class="badge bg-danger-subtle text-danger">Alpha</span>
                                        @else
                                            <span class="badge bg-secondary">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        {{-- link detail pakai path langsung --}}
                                        <a href="/pegawai/absensi/detail/{{ $d->id }}" class="btn btn-light btn-sm"
                                            navigate>
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (empty($data) || count($data) == 0)
                    @include('_admin._layout.components.empty-data', [
                        'title' => $page['title'] ?? 'Absensi Saya'
                    ])
                @endif

            <div>
                    @if (!empty($data) && method_exists($data, 'links'))
                        {{ $data->links() }}
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

<script src="{{ url('admin-ui') }}/assets/js/paginate.js"></script>