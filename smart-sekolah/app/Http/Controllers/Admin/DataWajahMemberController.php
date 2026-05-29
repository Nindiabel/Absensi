<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResponseEntity;
use App\Http\Controllers\Controller;
use App\Usecases\DataWajahMemberUsecase;
use App\Usecases\MemberUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DataWajahMemberController extends Controller
{
    protected DataWajahMemberUsecase $usecase;
    protected MemberUsecase $memberUsecase;

    protected array $page;
    protected string $baseRedirect;

    public function __construct(
        DataWajahMemberUsecase $usecase,
        MemberUsecase $memberUsecase
    ) {
        $this->usecase = $usecase;
        $this->memberUsecase = $memberUsecase;

        $this->page = [
            "route" => "data-wajah-member",
            "title" => "Data Wajah Member",
        ];

        $this->baseRedirect = "admin/" . $this->page['route'];
    }

    /**
     * ============================
     * LIST DATA
     * ============================
     */
    public function index(Request $req): View|Response {
        $data = $this->usecase
            ->getAll($req->input())['data']['list'] ?? [];

        $members = $this->memberUsecase
            ->getAll(["limit" => 1000])['data']['list'] ?? [];

        return render_view("_admin.data_wajah_member.list", [
            'data' => $data,
            'page' => $this->page,
            'members' => $members,
            'filter' => $req->input()
        ]);
    }

    /**
     * ============================
     * FORM TAMBAH
     * ============================
     */
    public function add(): View|Response {
        $members = $this->memberUsecase
            ->getAll(["limit" => 1000])['data']['list'] ?? [];

        return render_view("_admin.data_wajah_member.add", [
            'page' => $this->page,
            'members' => $members,
        ]);
    }

    /**
     * ============================
     * SIMPAN DATA
     * ============================
     */
    public function doCreate(Request $request): JsonResponse {
        $process = $this->usecase->create($request);

        return response()->json([
            "success" => empty($process['error']),
            "message" => empty($process['error'])
                ? ResponseEntity::SUCCESS_MESSAGE_CREATED
                : ResponseEntity::DEFAULT_ERROR_MESSAGE,
            "redirect" => $this->page['route'],
        ]);
    }

    /**
     * ============================
     * HAPUS
     * ============================
     */
    public function doDelete(int $id): JsonResponse {
        $process = $this->usecase->delete($id);

        return response()->json([
            "success" => empty($process['error']),
            "message" => empty($process['error'])
                ? ResponseEntity::SUCCESS_MESSAGE_DELETED
                : ResponseEntity::DEFAULT_ERROR_MESSAGE,
        ]);
    }
}