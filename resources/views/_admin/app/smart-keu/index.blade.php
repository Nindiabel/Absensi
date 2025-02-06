<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row mb-0">
                    <div class="col-md-6">
                        <p class="fs-5 mb-2">Laporan Aplikasi</p>
                        <img src="{{ url('admin-ui/assets/images/logos/smart-keuangan.png') }}" alt="Logo Smart Keuangan"
                            class="img-fluid mb-2">
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>

<div class="row mb-2">
    <div class="col-md-12">
        <ul class="nav nav-pills">
            @php
                $filterTeamID = $filter['filter_team_id'] ?? null;
                $selectedUnit = '';
            @endphp

            <li class="nav-item me-md-3 me-1 mb-2">
                <a class="nav-link rounded-5 px-4 {{ empty($filterTeamID) ? 'active' : '' }} shadow-sm"
                    href="{{ base_url('smart-keuangan') }}" navigate>
                    Semua Unit
                </a>
            </li>
            @foreach ($units as $u)
                @php
                    $unitName = explode(' - ', $u->name);

                    if (!empty($unitName[1])) {
                        $unitName = $unitName[1];
                    } else {
                        $unitName = $unitName[0];
                    }
                @endphp

                @php
                    if ($filterTeamID == $u->id) {
                        $selectedUnit = $unitName;
                    }

                    $emptyFund = false;
                    if (in_array($u->id, $unitsWithEmptyFundIds) && empty($filterTeamID)) {
                        $emptyFund = true;
                    }
                @endphp

                <li class="nav-item mb-2 me-md-3 me-1">
                    <a class="nav-link rounded-5 px-4 {{ $emptyFund ? "text-danger" : "" }} shadow-sm {{ $filterTeamID == $u->id ? 'active' : '' }}"
                        href="{{ base_url('smart-keuangan?filter_team_id=' . $u->id) }}"
                        navigate>
                        {{ $unitName }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">

            <div class="card-body">

                <div class="row">
                    @foreach ($funds as $f)
                        <div class="col-md-3">
                            <a href="{{ base_url('smart-keuangan/sf/detail/' . $f->id) }}">
                                <div class="card border-2 shadow-sm rounded-4">
                                    <div class="card-body p-3">
                                        @php
                                            $unitName = explode(' - ', $f->team);

                                            if (!empty($unitName[1])) {
                                                $unitName = $unitName[1];
                                            } else {
                                                $unitName = $unitName[0];
                                            }
                                        @endphp
                                        <span class="badge bg-primary-subtle text-primary fs-2">
                                            <b>Unit {{ $unitName }}</b>
                                        </span>
                                        <h3 class="mt-2 fw-bold mb-0">{{ $f->sf_name }}</h3>
                                        <hr class="dotted">
                                        <div>
                                            <small for="">Bendahara</small>
                                            <p class="mb-0 fs-3"><b>{{ $f->user }}</b></p>
                                        </div>

                                        <div class="card mb-0 bg-light mt-3 mb-2">
                                            <div class="card-body p-2">
                                                <p class="mb-1">Sisa Saldo</p>
                                                <h5 class="fw-bolder">{{ rupiah($f->saldo) }}</h5>
                                            </div>
                                        </div>
                                        <small>Terakhir Update <b>{{ formatedDate($f->updated_at, false) }}</b></small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach

                    @if (!count($funds))
                        <div class="text-center my-5">
                            <img src="https://keuangan.smkn2jember.com/admin-ui/assets/images/empty-data.webp"
                                alt="Empty Data" class="img-fluid mb-3" width="140">
                            <h4 class="mb-1">Data Belum Tersedia</h4>
                            <p>Hubungi <b>Unit {{ $selectedUnit }}</b></p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

</div>

@if (!empty($unitsWithEmptyFund) && empty($filterTeamID))
    <section>
        <div class="card">
            <div class="card-body">
                <div class="mt-0">
                    <div class="mb-3">
                        <h4 class="mb-1">Unit <b>Belum Mencatat Keuangan</b></h4>
                        <p>Unit yang belum menggunakan Aplikasi SmartKeuangan</p>
                    </div>

                    <div class="row">
                        @foreach ($unitsWithEmptyFund as $f)
                            <div class="col-md-4">
                                <div class="card border-1 border-danger">
                                    <div class="card-body">
                                        <h5 class="mb-0">{{ $f->name }}</h5>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
