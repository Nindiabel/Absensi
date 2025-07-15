<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResponseEntity;
use App\Http\Controllers\Controller;
use App\Usecases\MemberCategoryUsecase;
use App\Usecases\MemberUsecase;
use App\Usecases\ClassUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MemberController extends Controller
{
    protected $usecase;
    protected $memberCategoryUsecase;

    protected $classUsecase;
    protected $bookshelveUsecase;
    protected $loanUsecase;
    protected $page = [
        "route" => "member",
        "title" => "Anggota",
    ];
    protected $baseRedirect;

    public function __construct(
        MemberUsecase $usecase,
        MemberCategoryUsecase $memberCategoryUsecase,
        ClassUsecase $classUsecase,
    ) {
        $this->usecase = $usecase;
        $this->memberCategoryUsecase = $memberCategoryUsecase;
        $this->classUsecase = $classUsecase;
        $this->baseRedirect = "admin/" . $this->page['route'];
    }

    public function index(Request $req): View|Response
    {
        $data = $this->usecase->getAll($req->input());

        $memberCategories = $this->memberCategoryUsecase->getAll();
        $memberCategories = $memberCategories['data']['list'] ?? [];

        return render_view("_admin.member.list", [
            'data' => $data['data']['list'] ?? [],
            'memberCategories' => $memberCategories,
            'page' => $this->page,
            'filter' => $req->input(),
        ]);
    }

    public function add(): View|Response
    {
        $memberCategories = $this->memberCategoryUsecase->getAll();
        $memberCategories = $memberCategories['data']['list'] ?? [];

        $classes = $this->classUsecase->getAll();
        $classes = $classes['data']['list'] ?? [];

        return render_view("_admin.member.add", [
            'page' => $this->page,
            'memberCategories' => $memberCategories,
            'classes' => $classes,
        ]);
    }

    public function doCreate(Request $request): JsonResponse
    {
        $process = $this->usecase->create($request);

        if (empty($process['error'])) {
            return response()->json([
                "success" => true,
                "message" => ResponseEntity::SUCCESS_MESSAGE_CREATED,
                "redirect" => "member"
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => "member"
            ]);
        }
    }

    public function update(int $id): View|RedirectResponse|Response
    {
        $data = $this->usecase->getByID($id);

        if (empty($data['data'])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }
        $data = $data['data'] ?? [];

        $memberCategories = $this->memberCategoryUsecase->getAll();
        $memberCategories = $memberCategories['data']['list'] ?? [];

         $classes = $this->classUsecase->getAll();
         $classes = $classes['data']['list'] ?? [];

        return render_view("_admin.member.update", [
            'data' => (object) $data,
            'page' => $this->page,
            'memberCategories' => $memberCategories,
            'classes' => $classes,
        ]);
    }

    public function doUpdate(int $id, Request $request): JsonResponse
    {
        $process = $this->usecase->update($request, $id);

        if (empty($process['error'])) {
            return response()->json([
                "success" => true,
                "message" => ResponseEntity::SUCCESS_MESSAGE_UPDATED,
                "redirect" => "member"
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => "member"
            ]);
        }
    }

    public function doDelete(int $id, Request $request): JsonResponse
    {
        $process = $this->usecase->delete($id);

        if (empty($process['error'])) {
            return response()->json([
                "success" => true,
                "message" => ResponseEntity::SUCCESS_MESSAGE_DELETED,
                "redirect" => "member"
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => "member"
            ]);
        }
    }

    public function detail(int $id): View|RedirectResponse|Response
    {
        $data = $this->usecase->getByID($id);

        if (empty($data['data'])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }
        $data = $data['data'] ?? [];

        $className = '-';
        if (!empty($data['class_id'])) {
            $class = $this->classUsecase->getByID($data['class_id']);
            if (!empty($class['data'])) {
                $className = $class['data']['name'];
            }
        }
        $data['class_name'] = $className;

        return render_view("_admin.member.detail", [
            'data' => (object) $data,
            'page' => $this->page,
        ]);
    }

    public function searchAPI(Request $req): JsonResponse
    {
        $data = $this->usecase->getByKeywordName($req->input());
        $data = $data['data']['list'] ?? [];

        if (!count($data)) {
            return response()->json([]);
        }

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'id'            => $row->id,
                'name'          => $row->name,
                'identity_no'   => $row->identity_no,
                'identity_type' => $row->identity_type,
            ];
        }

        return response()->json($result);
    }
}
