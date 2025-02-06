    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    @include('_admin._layout.components.form-header', ['type' => "Tambah"])

                    <div>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <b>Terjadi kesalahan pada proses input data</b> <br>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ base_url($page['route'] . '/add') }}" navigate-form>
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama</label>
                                <input type="text" class="form-control" name="name" id="name"
                                    value="{{ old('name') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Kategori Anggota</label>
                                <select name="category_id" id="category_id" class="form-select" required>
                                    <option value="">- Pilih Kategori Anggota -</option>
                                    @foreach ($memberCategories as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="identity_no" class="form-label">No. Identitas</label>
                                <input type="text" class="form-control" name="identity_no" id="identity_no"
                                    value="{{ old('identity_no') }}" required>
                                <small>Perhatian! Jika Siswa pakai NISN, Jika Guru / Pegawai pakai NIP</small>
                            </div>
                            <div class="mb-3">
                                <label for="join_year" class="form-label">Tahun Masuk</label>
                                <input type="text" class="form-control" name="join_year" id="join_year"
                                    value="{{ old('join_year') }}">
                                <small>Tahun masuk anggota di sekolah</small>
                            </div>
                            <button type="submit" class="btn btn-primary bg-gradient"><b>Simpan Data</b></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
