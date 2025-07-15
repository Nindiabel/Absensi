<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">

                    <div class="col-md-6">
                        <h4 class="mb-0">Detail <b>{{ $page['title'] }}</b></h4>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="{{ base_url($page['route'] . '/') }}" class="btn btn-outline-indigo btn-sm fw-bold" navigate>
                            <b>← Kembali</b>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">

                <div class="mb-3">
                    <label>Nama</label>
                    <p class="mb-0 fs-5"><strong>{{ title($data->name) }}</strong></p>
                </div>
                <hr class="dotted">

                <div class="mb-3">
                    <label>Jenis Anggota</label>
                    <p class="mb-0 fs-5"><strong>{{ getMemberCtg($data->category_id) }}</strong></p>
                </div>

                @if (!empty(trim($data->class ?? '')) && $data->class !== '-')
                    <div class="mb-3">
                        <label>Kelas</label>
                        <p class="mb-0 fs-5"><strong>{{ $data->class }}</strong></p>
                    </div>
                @endif

                <div class="mb-3">
                    <label>Identitas ID ({{ getMemberIdentityCtg($data->category_id) }})</label>
                    <p class="mb-0 fs-5"><strong>{{ title($data->identity_no) }}</strong></p>
                </div>

                <div class="mb-3">
                    <label>Tahun Masuk</label>
                    <p class="mb-0 fs-5"><strong>{{ $data->join_year }}</strong></p>
                </div>

            </div>
        </div>
    </div>
</div>
