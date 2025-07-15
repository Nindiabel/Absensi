<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResponseEntity;
use App\Http\Controllers\Controller;
use App\Usecases\ClassUsecase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ClassController extends Controller
{
    protected $usecase;
    protected $page = [
        "route" => "classe",
        "title" => "Kelas",
    ];
    protected $baseRedirect;

    public function __construct(ClassUsecase $usecase)
    {
        $this->usecase = $usecase;
        $this->baseRedirect = "admin/" . $this->page['route'];
    }

     public function index(Request $req): View|Response
    {
        $filterName = $req->input('q', '');
        $filterYear = $req->input('enrollment_year', '');

        $data = $this->usecase->getAll([
            'filter_name' => $filterName,
            'filter_enrollment_year' => $filterYear,
            'page' => $req->input('page', 1),
            'limit' => $req->input('limit', 10),
        ]);

        $enrollmentYears = [2022, 2023, 2024, 2025];

        return render_view("_admin.classe.list", [
            'data' => $data['data']['list'] ?? [],
            'page' => $this->page,
            'enrollmentYears' => $enrollmentYears,
            'filterYear' => $filterYear,
        ]);
    }

    public function add(): View | Response
    {
        return render_view("_admin.classe.add", [
            'page' => $this->page,
        ]);
    }

    public function doCreate(Request $request): JsonResponse
    {
        $request->merge([
            'enrollment_year' => $request->input('enrollment_year')
        ]);

        $createProcess = $this->usecase->create(
            data: $request,
        );

        if (empty($createProcess['error'])) {
            return response()->json([
                "success" => true, 
                "message" => ResponseEntity::SUCCESS_MESSAGE_CREATED,
                "redirect" => "classe"
            ]);
        } else {
            return response()->json([
                "success" => false, 
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => $this->baseRedirect
            ]);
        }
    }

    public function update(int $id): View | Response | RedirectResponse
    {
        $data = $this->usecase->getByID($id);

        if (empty($data['data'])) {
            return redirect()
                ->intended($this->baseRedirect)
                ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE);
        }
        $data = $data['data'] ?? [];

        return render_view("_admin.classe.update", [
            'data' => (object) $data,
            'page' => $this->page,
        ]);
    }

    public function doUpdate(int $id, Request $request): JsonResponse
    {
        $request->merge([
            'enrollment_year' => $request->input('enrollment_year')
        ]);

        $process = $this->usecase->update(
            data: $request,
            id: $id,
        );

        if (empty($process['error'])) {
            return response()->json([
                "success" => true, 
                "message" => ResponseEntity::SUCCESS_MESSAGE_UPDATED,
                "redirect" => "classe"
            ]);
        } else {
            return response()->json([
                "success" => false, 
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => "classe"
            ]);
        }
    }

    public function doDelete(int $id, Request $request): JsonResponse
    {
        $process = $this->usecase->delete(
            id: $id,
        );

        if (empty($process['error'])) {
            return response()->json([
                "success" => true, 
                "message" => ResponseEntity::SUCCESS_MESSAGE_DELETED,
                "redirect" => "classe"
            ]);
        } else {
            return response()->json([
                "success" => false, 
                "message" => ResponseEntity::DEFAULT_ERROR_MESSAGE,
                "redirect" => "classe"
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

        return render_view("_admin.classe.detail", [
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
                'id'    => $row->id,
                'name'  => $row->name,
            ];
        }

        return response()->json($result);
    }
}
