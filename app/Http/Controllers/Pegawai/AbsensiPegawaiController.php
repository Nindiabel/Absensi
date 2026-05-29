<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Usecases\AbsensiUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AbsensiPegawaiController extends Controller
{
    protected AbsensiUsecase $usecase;

    protected array $page;
    protected string $baseRedirect;

    public function __construct(AbsensiUsecase $usecase)
    {
        $this->usecase = $usecase;

        $this->page = [
            "route"  => "absensi",
            "title"  => "Absensi Saya",
        ];

        $this->baseRedirect = "pegawai/absensi"; // ← redirect tetap pakai full path
    }

    public function index(Request $req): View|Response|RedirectResponse
    {
        $user = Auth::user();

        if (empty($user->member_id)) {
            return redirect("pegawai/auth/login")
                ->with('error', 'Akun ini belum terhubung dengan data pegawai.');
        }

        // Ambil hanya filter yang relevan
        $filter = $req->only(['bulan', 'status_kehadiran', 'filter_on']);

        // Paksa member_id dari user login
        $filter['member_id'] = $user->member_id;

        $absensi = $this->usecase->getAllAbsensi($filter)['data']['list'] ?? [];

        return render_view("_pegawai.absensi.list", [
            'data'   => $absensi,
            'page'   => $this->page,
            'filter' => $req->only(['bulan', 'status_kehadiran', 'filter_on']),
        ]);
    }

    public function detail(int $id): View|RedirectResponse|Response
    {
        $user = Auth::user();

        if (empty($user->member_id)) {
            return redirect("pegawai/auth/login")
                ->with('error', 'Akun ini belum terhubung dengan data pegawai.');
        }

        $result = $this->usecase->getAllAbsensi([
            'id'        => $id,
            'member_id' => $user->member_id,
        ]);

        $list = $result['data']['list'] ?? null;
        $data = $list && method_exists($list, 'items')
            ? collect($list->items())->first()
            : null;

        if (!$data) {
            return redirect($this->baseRedirect)
                ->with('error', 'Data absensi tidak ditemukan atau bukan milik Anda.');
        }

        return render_view("_pegawai.absensi.detail", [
            'data' => (object) $data,
            'page' => $this->page,
        ]);
    }
    public function exportToExcel(Request $req): mixed
    {
        $user = Auth::user();

        if (empty($user->member_id)) {
            return redirect("pegawai/auth/login")
                ->with('error', 'Akun ini belum terhubung dengan data pegawai.');
        }

        // Ambil filter dari request, lalu paksa member_id dari user login
        // sehingga pegawai tidak bisa download data orang lain
        $filter              = $req->only(['bulan', 'status_kehadiran']);
        $filter['member_id'] = $user->member_id;

        return $this->usecase->exportToExcelPegawai(
            filter: $filter,
            baseRedirect: $this->baseRedirect,
            namaAnggota: $user->name,
        );
    }
}
