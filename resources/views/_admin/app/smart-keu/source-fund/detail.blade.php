<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row d-flex justify-content-center align-items-center">

                    <div class="col-md-6">
                        <p class="fs-5 mb-2">Detail Dana</p>
                        <h1 class="mb-0">{{ $sfName }}</h1>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="">
                            <a href="{{ base_url('smart-keuangan') }}" class="btn btn-outline-warning btn-sm fw-bold"
                                navigate>
                                <b>← Kembali</b>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


<div class="row mb-4">
    <div class="col-md-5 col-sm-8">
        <label for="" class="form-label">Pilih Filter Tanggal</label>
        <input type="text" name="daterange" id="daterange" class="form-control bg-white" readonly
            style="cursor: pointer" />
    </div>
</div>

<section>
    <div class="row">
        <div class="col-md-3">
            <div class="card bg-success-subtle rounded-4">
                <div class="card-body text-end">
                    <h6 class="mb-1">Total Pemasukan</h6>
                    <p class="mb-0 fs-6 text-success fw-bolder"><b>{{ rupiah($totalPemasukan) }}</b></p>

                    @php
                        $countIn = $transactions->where('trx_type', 1)->count();
                    @endphp
                    @if ($countIn > 0)
                        <small>Dari <b>{{ $countIn }}</b> Transaksi</small>
                    @else
                        <small>-</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning-subtle rounded-4">
                <div class="card-body text-end">
                    <h6 class="mb-1">Total Pengeluaran</h6>
                    <p class="mb-0 fs-6 text-warning fw-bolder"><b>{{ rupiah($totalPengeluaran) }}</b></p>

                    @php
                        $countOut = $transactions->where('trx_type', 2)->count();
                    @endphp
                    @if ($countOut > 0)
                        <small>Dari <b>{{ $countOut }}</b> Transaksi</small>
                    @else
                        <small>-</small>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>

<section>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Transaksi</h5>
                </div>
                <div class="card-body">
                    @if (count($transactions))
                        <p>Total: <b>{{ count($transactions) }}</b> Transaksi</p>
                    @endif
                    <table class="table table-bordered table-striped mt-3 table-sm" style="font-size: 10px !important">
                        <thead class="table-white">
                            <th class="table-header fs-1 fs-1" style="--width: 5%">ID</th>
                            <th class="table-header fs-1" style="--width: 5%">TANGGAL</th>
                            <th class="table-header fs-1" style="--width: 20%">KATEGORI</th>
                            <th class="table-header fs-1" style="--width: 40%">URAIAN</th>
                            <th class="table-header fs-1" style="--width: 30%">NOMINAL</th>
                            <th class="table-header fs-1" style="--width: 30%">PELAKSANA</th>
                            <th class="table-header fs-1" style="--width: 30%">PENANGGUNG JAWAB</th>
                            <th class="table-header fs-1" style="--width: 30%">METODE BAYAR</th>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $d)
                                <tr>
                                    <td>
                                        <b>{{ $d->trx_year }}/{{ $d->sf_slug }}/{{ $d->trx_no }}</b>
                                    </td>
                                    <td>{{ $d->trx_date }}</td>
                                    <td>{{ $d->category }}</td>
                                    <td>{{ $d->description }}</td>
                                    <td class="text-{{ $d->trx_type == 1 ? 'success' : 'danger' }}">
                                        <b>{{ rupiah($d->amount_actual) }}</b>
                                    </td>
                                    <td>{{ $d->executor_name }}</td>
                                    <td>{{ $d->picUnits }}</td>
                                    <td>{{ $d->payment_method == '2' ? 'TRANSFER' : 'CASH' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if (!count($transactions))
                        <div class="text-center my-5">
                            <img src="https://keuangan.smkn2jember.com/admin-ui/assets/images/empty-data.webp"
                                alt="Empty Data" class="img-fluid mb-3" width="140">
                            <h4 class="mb-1">Data Belum Tersedia</h4>
                            <p>Hubungi Unit untuk melakukan penambahan data</p>
                        </div>
                    @endif

                </div>
            </div>
        </div>

    </div>
</section>

<script src="{{ url('admin-ui') }}/assets/libs/jquery/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    $(document).ready(function() {
        moment.locale('id');

        function getParameterByName(name, url = window.location.href) {
            name = name.replace(/[\[\]]/g, '\\$&');
            let regex = new RegExp(`[?&]${name}(=([^&#]*)|&|#|$)`),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }

        let startDateParam = getParameterByName('start_date');
        let endDateParam = getParameterByName('end_date');

        let startDate = startDateParam ? moment(startDateParam, 'YYYY-MM-DD') : moment().startOf('month');
        let endDate = endDateParam ? moment(endDateParam, 'YYYY-MM-DD') : moment();

        $('#daterange').daterangepicker({
            startDate: startDate,
            endDate: endDate,
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                    'month').endOf('month')]
            },
            locale: {
                format: 'DD MMMM YYYY',
                separator: ' - ',
                applyLabel: 'Terapkan',
                cancelLabel: 'Batal',
                daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                    'September', 'Oktober', 'November', 'Desember'
                ],
                firstDay: 1
            }
        });

        $('#daterange').on('apply.daterangepicker', function(ev, picker) {
            let start_date = picker.startDate.format('YYYY-MM-DD');
            let end_date = picker.endDate.format('YYYY-MM-DD');

            let sof_id = <?= $sofID ?>;
            let sof_name = "<?= $sfName ?>";

            let url = BASE_URL +
                `/smart-keuangan/sf/detail/${sof_id}?start_date=${start_date}&end_date=${end_date}&sf_name=${sof_name}`;
            window.location.href = url;
        });
    });
</script>
