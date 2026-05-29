<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResponseEntity;
use App\Http\Controllers\Controller;
use App\Usecases\AbsensiUsecase;
use App\Usecases\MemberUsecase;
use App\Usecases\MemberCategoryUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AbsensiController extends Controller
{
    protected AbsensiUsecase $usecase;
    protected MemberUsecase $memberUsecase;
    protected MemberCategoryUsecase $memberCategoryUsecase;

    protected array $page;
    protected string $baseRedirect;

    public function __construct(
        AbsensiUsecase $usecase,
        MemberUsecase $memberUsecase,
        MemberCategoryUsecase $memberCategoryUsecase
    ) {
        $this->usecase = $usecase;
        $this->memberUsecase = $memberUsecase;
        $this->memberCategoryUsecase = $memberCategoryUsecase;

        $this->page = [
            "route" => "absensi",
            "title" => "Absensi",
        ];

        $this->baseRedirect = "admin/" . $this->page['route'];
    }

    public function index(Request $req): View|Response
    {
        $members = $this->memberUsecase
            ->getAll(["limit" => 1000])['data']['list'] ?? [];

        $categories = $this->memberCategoryUsecase
            ->getAll()['data']['list'] ?? [];

        $absensi = $this->usecase
            ->getAllAbsensi($req->input())['data']['list'] ?? [];

        return render_view("_admin.absensi.list", [
            'data'       => $absensi,
            'page'       => $this->page,
            'filter'     => $req->input(),
            'members'    => $members,
            'categories' => $categories,
        ]);
    }

    public function add(): View|Response
    {
        $members = $this->memberUsecase
            ->getAll(["limit" => 1000])['data']['list'] ?? [];

        return render_view("_admin.absensi.add", [
            'page'    => $this->page,
            'members' => $members,
        ]);
    }

    public function doCreate(Request $request): JsonResponse
    {
        $process = $this->usecase->createAbsensi($request);

        return response()->json([
            "success"  => empty($process['error']),
            "message"  => $process['message'] ?? ResponseEntity::DEFAULT_ERROR_MESSAGE,
            "redirect" => $this->baseRedirect,
        ]);
    }

    public function detail(int $id): View|RedirectResponse|Response
    {
        $result = $this->usecase->getAllAbsensi(['id' => $id]);
        $list   = $result['data']['list'] ?? null;
        $data   = $list ? collect($list->items())->first() : null;

        if (!$data) {
            return redirect($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }

        return render_view("_admin.absensi.detail", [
            'data' => (object) $data,
            'page' => $this->page,
        ]);
    }

    public function edit(int $id): View|RedirectResponse|Response
    {
        $result = $this->usecase->getAllAbsensi(['id' => $id]);
        $list   = $result['data']['list'] ?? null;
        $data   = $list ? collect($list->items())->first() : null;

        if (!$data) {
            return redirect($this->baseRedirect)
                ->with('error', 'Data tidak ditemukan.');
        }

        return render_view("_admin.absensi.update", [
            'data' => (object) $data,
            'page' => $this->page,
        ]);
    }

    public function doUpdate(int $id, Request $request): JsonResponse
    {
        $process = $this->usecase->updateAbsensi($request, $id);

        if (empty($process['error'])) {
            return response()->json([
                "success"  => true,
                "message"  => ResponseEntity::SUCCESS_MESSAGE_UPDATED,
                "redirect" => $this->page['route'],
            ]);
        }

        return response()->json([
            "success" => false,
            "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
        ]);
    }

    /**
     * API untuk scan masuk (dipanggil dari mesin absensi)
     */
    public function doScanMasuk(Request $request): JsonResponse
    {
        $process = $this->usecase->scanMasuk($request);

        return response()->json([
            "success" => empty($process['error']),
            "message" => $process['message'] ?? ResponseEntity::DEFAULT_ERROR_MESSAGE,
        ]);
    }

    /**
     * API untuk scan pulang (dipanggil dari mesin absensi)
     * Hanya bisa dilakukan jika sudah scan masuk hari ini
     */
    public function doScanPulang(Request $request): JsonResponse
    {
        $process = $this->usecase->scanPulang($request);

        return response()->json([
            "success" => empty($process['error']),
            "message" => $process['message'] ?? ResponseEntity::DEFAULT_ERROR_MESSAGE,
        ]);
    }

    public function registrasiWajah(Request $req): View|Response
    {
        $members = $this->memberUsecase
            ->getAll(["limit" => 1000])['data']['list'] ?? [];

        $registeredMemberIds = \Illuminate\Support\Facades\DB::table('data_wajah_member')
            ->pluck('member_id')
            ->toArray();

        return render_view("_admin.absensi.registrasi_wajah", [
            'page'    => $this->page,
            'members' => $members,
            'registeredMemberIds' => $registeredMemberIds,
        ]);
    }

    public function doCreateWajah(Request $request): JsonResponse
    {
        $process = $this->usecase->registerFace($request);

        return response()->json([
            "success"  => empty($process['error']),
            "message"  => $process['message'] ?? ResponseEntity::DEFAULT_ERROR_MESSAGE,
            "redirect" => $this->baseRedirect . "/registrasi-wajah",
        ]);
    }

    public function scanWajah(Request $req): View|Response
    {
        return render_view("_admin.absensi.scan_wajah", [
            'page'    => $this->page,
        ]);
    }

    public function doScanWajah(Request $request): JsonResponse
    {
        $process = $this->usecase->scanFace($request);

        return response()->json([
            "success"  => empty($process['error']),
            "message"  => $process['message'] ?? ResponseEntity::DEFAULT_ERROR_MESSAGE,
            "data"     => $process['data'] ?? null
        ]);
    }

    public function doDelete(int $id, Request $request): JsonResponse
    {
        $process = $this->usecase->deleteAbsensi($id);

        if (empty($process['error'])) {
            return response()->json([
                "success"  => true,
                "message"  => "Data absensi berhasil dihapus",
                "redirect" => $this->page['route'],
            ]);
        }

        return response()->json([
            "success"  => false,
            "message"  => $process['message'] ?? ResponseEntity::DEFAULT_ERROR_MESSAGE,
            "redirect" => $this->page['route'],
        ]);
    }

    public function exportToExcel(Request $request)
    {
        return $this->usecase->exportToExcel($request, $this->baseRedirect);
    }
}