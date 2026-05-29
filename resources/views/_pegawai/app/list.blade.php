<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="mb-4">
                    <h3 class="mb-2">Aplikasi Smart Yang Digunakan</h3>
                    <p class="text-gray-2">Aplikasi yang tersedia di <b> {{ session('tenant_name') }}</b></p>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card py-1 shadow-sm border-2 mb-2 rounded-3">
                            <div class="card-body text-start">
                                <img src="{{ url('admin-ui/assets/images/logos/smart-keuangan.png') }}"
                                    alt="Logo Smart Keuangan" class="img-fluid mb-2">
                                <p class="mb-4"><b>Aplikasi Pengelola Keuangan Bendahara</b></p>
                                <a href="{{ base_url('smart-keuangan') }}" class="btn btn-primary shadow bg-gradient">Lihat Laporan →</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="mb-4">
                    <h3 class="mb-2">Belum Digunakan</h3>
                    <p class="text-gray-2">Aplikasi yang belum digunakan <b> {{ session('tenant_name') }}</b></p>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card py-1 border-light border-1 mb-2 rounded-3">
                            <div class="card-body text-start">
                                <img src="{{ url('admin-ui/assets/images/logos/smart-perpus.png') }}"
                                    alt="Logo Smart Keuangan" class="img-fluid mb-2">
                                <div class="mb-0">
                                    <p class="mb-0"><b>Aplikasi Perpustakaan Digital</b></p>
                                    <small>Kelola perpustakaan sekolah anda dengan mudah dan cepat!</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card py-1 border-light border-1 mb-2 rounded-3">
                            <div class="card-body text-start">
                                <img src="{{ url('admin-ui/assets/images/logos/smart-surat.png') }}"
                                    alt="Logo Smart Keuangan" class="img-fluid mb-2">
                                <div class="mb-0">
                                    <p class="mb-0"><b>Aplikasi Persuratan</b></p>
                                    <small>Mempermudah pengelolaan Surat Masuk dan Keluar di Sekolah</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card py-1 border-light border-1 mb-2 rounded-3">
                            <div class="card-body text-start">
                                <img src="{{ url('admin-ui/assets/images/logos/smart-inventaris.png') }}"
                                    alt="Logo Smart Keuangan" class="img-fluid mb-2">
                                <div class="mb-0">
                                    <p class="mb-0"><b>Aplikasi Inventaris Barang</b></p>
                                    <small>Pengelolaan Invetarisasi Barang semakin cepat dan mudah</small>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ url('admin-ui') }}/assets/js/paginate.js"></script>
