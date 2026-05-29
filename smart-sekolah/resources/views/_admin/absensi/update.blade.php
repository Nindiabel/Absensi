<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-0">Edit Status Absensi</h4>
                    <small class="text-muted">Ubah status kehadiran guru/tendik</small>
                </div>
                <a href="{{ base_url($page['route']) }}" class="btn btn-outline-secondary btn-sm" navigate>
                    ← Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form id="formEdit">
                    @csrf

                    {{-- Info tidak bisa diubah --}}
                    <div class="mb-3">
                        <label class="form-label text-muted">Nama</label>
                        <input type="text" class="form-control bg-light"
                               value="{{ $data->member_name ?? '-' }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Kategori</label>
                        <input type="text" class="form-control bg-light"
                               value="{{ $data->category_name ?? '-' }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Tanggal</label>
                        <input type="text" class="form-control bg-light"
                               value="{{ !empty($data->tanggal_absensi) ? date('d F Y', strtotime($data->tanggal_absensi)) : '-' }}"
                               disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Status Saat Ini</label>
                        <div class="mt-1">
                            @if (($data->status_kehadiran ?? '') == 'alpha')
                                <span class="badge bg-success">Alpha</span>
                            @elseif (($data->status_kehadiran ?? '') == 'izin')
                                <span class="badge bg-primary">Izin</span>
                            @elseif (($data->status_kehadiran ?? '') == 'sakit')
                                <span class="badge bg-warning">Sakit</span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    {{-- Hanya bisa ubah ke izin, sakit, alpha --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Ubah Status <span class="text-danger">*</span>
                        </label>
                        <select name="status_kehadiran" class="form-select" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="izin"  {{ ($data->status_kehadiran ?? '') == 'izin'  ? 'selected' : '' }}>Izin</option>
                            <option value="sakit" {{ ($data->status_kehadiran ?? '') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="alpha" {{ ($data->status_kehadiran ?? '') == 'alpha' ? 'selected' : '' }}>Alpha</option>
                        </select>
                        <small class="text-muted">Status Hadir/Terlambat tidak dapat diubah manual.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3"
                                  placeholder="Contoh: Izin keperluan keluarga, Sakit demam, dll">{{ $data->catatan ?? '' }}</textarea>
                        <small class="text-muted">Opsional, isi jika ada keterangan tambahan.</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">Simpan</button>
                        <a href="{{ base_url($page['route']) }}" class="btn btn-outline-secondary" navigate>Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#formEdit').on('submit', function (e) {
        e.preventDefault();

        const btn = $(this).find('button[type=submit]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: '/admin/absensi/edit/{{ $data->id }}',
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (res) {
                if (res.success) {
                    window.location.href = '/admin/absensi';
                } else {
                    alert(res.message ?? 'Terjadi kesalahan.');
                    btn.prop('disabled', false).html('Simpan');
                }
            },
            error: function () {
                alert('Terjadi kesalahan server.');
                btn.prop('disabled', false).html('Simpan');
            }
        });
    });
});
</script>