<div class="row"> 
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">

                    <div class="col-md-6">
                        <h4 class="mb-0">Detail <b>{{ $page['title'] }}</b></h4>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="">
                            <a href="{{ base_url($page['route'] . '/') }}" class="btn btn-outline-indigo btn-sm fw-bold" navigate>
                                <b>← Kembali</b>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    
                    <div class="col-md-12">
                        <div class="mb-0">
                            <label for="">Nama Kelas</label>
                            <p class="mb-0 fs-5"><strong>{{ title($data->name) }}</strong></p>
                        </div>
                        <hr class="dotted">
                    </div>

                    <div class="col-md-12">
                        <div class="mb-2">
                            <label for="">Deskripsi</label>
                            <p class="mb-0 fs-5"><strong>{{ $data->description ?? '-' }}</strong></p>
                        </div>
                        <div class="mb-2">
                            <label for="">Tahun Masuk</label>
                            <p class="mb-0 fs-5"><strong>{{ $data->enrollment_year }}</strong></p>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
