<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">

                {{-- HEADER --}}
                <div class="row mb-4 align-items-center">
                    <div class="col-md-6">
                        <h4 class="fw-bold mb-1">{{ $page['title'] }}</h4>
                        <small class="text-muted">Data absensi Guru dan Karyawan</small>
                    </div>

                    <div class="col-md-6 text-end">
                        <a href="{{ base_url($page['route'] . '/exportToExcel') . (request()->getQueryString() ? '?' . request()->getQueryString() : '') }}"
                            class="btn btn-success fw-bold me-2">
                            Download
                        </a>
                        <a href="{{ base_url($page['route'] . '/scan-wajah') }}" class="btn btn-warning fw-bold text-dark me-2"
                            navigate>
                            @include('_admin._layout.icons.users')
                            Scan Wajah
                        </a>
                        <a href="{{ base_url($page['route'] . '/registrasi-wajah') }}" class="btn btn-primary fw-bold"
                            navigate>
                            @include('_admin._layout.icons.plus')
                            Registrasi
                        </a>
                    </div>
                </div>

                {{-- FILTER --}}
                <form action="{{ base_url($page['route']) }}" method="GET">
                    <input type="hidden" name="filter_on" value="true">

                    <div class="d-flex flex-wrap gap-3 mb-4">

                        {{-- SEARCH --}}
                        <div class="position-relative" style="max-width: 260px; width:100%;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                style="position:absolute; left:12px; top:50%; transform:translateY(-50%); z-index:5;">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                            </svg>

                            <input type="text" name="keyword" value="{{ $filter['keyword'] ?? '' }}"
                                class="form-control no-uppercase"
                                style="padding-left:40px; height:44px; border-radius:8px;" placeholder="Cari nama...">
                        </div>

                        {{-- KATEGORI --}}
                        <div class="position-relative" style="max-width: 200px; width:100%;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                style="position:absolute; left:12px; top:50%; transform:translateY(-50%); z-index:5;">
                                <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"></path>
                            </svg>

                            <select name="category_id" class="form-select"
                                style="padding-left:40px; height:44px; border-radius:8px;">
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $category)
                                    @if (in_array(strtolower($category->name), ['guru', 'tendik']))
                                        <option value="{{ $category->id }}"
                                            {{ ($filter['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        {{-- BULAN --}}
                        <div style="max-width: 180px; width:100%;">
                            <input type="month" name="bulan" value="{{ $filter['bulan'] ?? '' }}" class="form-control"
                                style="height:44px; border-radius:8px;">
                        </div>

                        {{-- STATUS --}}
                        <div class="position-relative" style="max-width: 200px; width:100%;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                style="position:absolute; left:12px; top:50%; transform:translateY(-50%); z-index:5;">
                                <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"></path>
                            </svg>

                            <select name="status_kehadiran" class="form-select"
                                style="padding-left:40px; height:44px; border-radius:8px;">
                                <option value="">Semua Status</option>
                                <option value="hadir" {{ ($filter['status_kehadiran'] ?? '') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="terlambat" {{ ($filter['status_kehadiran'] ?? '') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                                <option value="izin" {{ ($filter['status_kehadiran'] ?? '') == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ ($filter['status_kehadiran'] ?? '') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="alpha" {{ ($filter['status_kehadiran'] ?? '') == 'alpha' ? 'selected' : '' }}>Alpha</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Cari
                        </button>

                        @if (!empty($filter['filter_on']))
                            <a href="{{ base_url($page['route']) }}" class="btn btn-outline-warning">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>

                {{-- TABLE --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>NAMA</th>
                                <th>KATEGORI</th>
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
                                    <td>{{ $d->category_name ?? '-' }}</td>
                                    <td>
                                        {{ !empty($d->tanggal_absensi) ? date('d F Y', strtotime($d->tanggal_absensi)) : '-' }}
                                    </td>
                                    <td class="text-center">
                                        {{ !empty($d->jam_masuk) ? date('H:i', strtotime($d->jam_masuk)) : '-' }}
                                    </td>
                                    <td class="text-center">
                                        @if (!empty($d->jam_pulang))
                                            {{ date('H:i', strtotime($d->jam_pulang)) }}
                                        @elseif (($d->status_final ?? '') == 'hadir' || ($d->status_final ?? '') == 'terlambat')
                                            <span class="badge bg-secondary-subtle text-secondary">Belum scan</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if (($d->status_final ?? '') == 'hadir')
                                            <span class="badge bg-success-subtle text-success">Hadir</span>
                                        @elseif (($d->status_final ?? '') == 'terlambat')
                                            <span class="badge bg-warning-subtle text-warning">Terlambat</span>
                                        @elseif (($d->status_final ?? '') == 'izin')
                                            <span class="badge bg-primary-subtle text-primary">Izin</span>
                                        @elseif (($d->status_final ?? '') == 'sakit')
                                            <span class="badge bg-warning-subtle text-warning">Sakit</span>
                                        @elseif (($d->status_final ?? '') == 'alpha')
                                            <span class="badge bg-danger-subtle text-danger">Alpha</span>
                                        @else
                                            <span class="badge bg-secondary">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                •••
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ base_url($page['route'] . "/detail/{$d->id}") }}"
                                                        navigate>
                                                        Detail
                                                    </a>
                                                </li>

                                                {{-- Tombol edit hanya muncul jika status alpha, izin, atau sakit --}}
                                                @if (in_array($d->status_kehadiran ?? '', ['alpha', 'izin', 'sakit']))
                                                    <li>
                                                        <a class="dropdown-item text-warning"
                                                            href="{{ base_url($page['route'] . "/edit/{$d->id}") }}"
                                                            navigate>
                                                            Edit Status
                                                        </a>
                                                    </li>
                                                @endif

                                                <li><hr class="dropdown-divider"></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (!count($data))
                    @include('_admin._layout.components.empty-data', ['title' => $page['title']])
                @endif

                <div>
                    {{ !empty($data) ? $data->links() : '' }}
                </div>

            </div>
        </div>
    </div>
</div>

<script src="{{ url('admin-ui') }}/assets/js/paginate.js"></script>