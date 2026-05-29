<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-body">
                @include('_admin._layout.components.form-header', ['type' => "Tambah"])

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
                        <label for="name" class="form-label">Nama Kelas</label>
                        <input type="text" class="form-control" name="name" id="name"
                            value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" id="description" rows="3">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="enrollment_year" class="form-label">Tahun Masuk</label>
                        <input type="number" class="form-control" name="enrollment_year" id="enrollment_year"
                            value="{{ old('enrollment_year') }}" required min="2000" max="{{ date('Y') }}">
                    </div>

                    <button type="submit" class="btn btn-primary bg-gradient"><b>Simpan Data</b></button>
                </form>
            </div>
        </div>
    </div>
</div>
