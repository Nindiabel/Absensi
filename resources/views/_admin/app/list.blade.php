<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <h3 class="mb-1">Daftar Aplikasi</h3>
                    <p>Aplikasi yang tersedia di <b> {{ session('tenant_name') }}</b></p>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card shadow mb-2">
                            <div class="card-body text-center">
                                <img src="{{ url("admin-ui/assets/images/logos/smart-keuangan.png") }}" alt="Logo Smart Keuangan" class="img-fluid mb-2">
                                <p>Aplikasi Pengelola Keuangan Bendahara</p>
                                <a href="{{ base_url('smart-keuangan') }}" class="btn btn-primary" navigate>Lihat Laporan</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ url('admin-ui') }}/assets/js/paginate.js"></script>
