<?php

namespace App\Usecases;

use App\Entities\DatabaseEntity;
use App\Entities\ResponseEntity;
use App\Http\Presenter\Response;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ClassUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "ClassUsecase";
    }

    public function getAll(array $filterData = []): array
    {
        $funcName = $this->className . ".getAll";

        $page         = $filterData['page'] ?? 1;
        $limit        = $filterData['limit'] ?? 10;
        $page         = ($page > 0 ? $page : 1);
        $filterName   = $filterData['filter_name'] ?? "";
        $filterYear   = $filterData['filter_enrollment_year'] ?? "";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::CLASSE, 'c')
                ->whereNull('c.deleted_at');

            if (!empty($filterName)) {
                $data = $data->where('c.name', 'like', '%' . $filterName . '%');
            }

            if (!empty($filterYear)) {
                $data = $data->where('c.enrollment_year', $filterYear);
            }

            $fields = ['c.*'];

            $data = $data->orderBy('c.created_at', 'desc')
                         ->paginate($limit, $fields)
                         ->appends(request()->query());

            return Response::buildSuccess(
                [
                    'list' => $data,
                    'pagination' => [
                        'current_page' => (int) $page,
                        'limit'        => (int) $limit,
                        'payload'      => $filterData
                    ]
                ],
                ResponseEntity::HTTP_SUCCESS
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getByKeywordName(array $filterData = []): array
    {
        $funcName = $this->className . ".getByKeywordName";

        $term = $filterData['term'] ?? '';

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::CLASSE, 'c')
                ->whereNull("c.deleted_at")
                ->where(function ($query) use ($term) {
                    $query->where('c.name', 'like', '%' . $term . '%')
                        ->orWhere('c.enrollment_year', 'like', '%' . $term . '%');
                })
                ->orderBy("c.created_at", "desc")
                ->limit(30)
                ->get(['c.*']);

            return Response::buildSuccess(['list' => $data]);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);

            return Response::buildErrorService($e->getMessage());
        }
    }

    public function getByID(int $id): array
    {
        $funcName = $this->className . ".getByID";

        try {
            $data = DB::connection(DatabaseEntity::SQL_READ)
                ->table(DatabaseEntity::CLASSE)
                ->whereNull("deleted_at")
                ->where("id", $id)
                ->first();

            return Response::buildSuccess(data: collect($data)->toArray());
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function create(Request $data): array
    {
        $funcName = $this->className . ".create";

        $validator = Validator::make($data->all(), [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'enrollment_year' => 'required|digits:4|integer',
        ]);

        $customAttributes = [
            'name' => 'Nama Kelas',
            'description' => 'Deskripsi',
            'enrollment_year' => 'Tahun Masuk',
        ];
        $validator->setAttributeNames($customAttributes);
        $validator->validate();

        DB::beginTransaction();
        try {
            $id = DB::table(DatabaseEntity::CLASSE)
                ->insertGetId([
                    'name'            => $data['name'],
                    'description'     => $data['description'] ?? null,
                    'enrollment_year' => $data['enrollment_year'],
                    'created_by'      => Auth::user()->id,
                    'created_at'      => datetime_now(),
                ]);

            DB::commit();
            return Response::buildSuccessCreated();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function update(Request $data, int $id): array
    {
        $funcName = $this->className . ".update";

        $validator = Validator::make($data->all(), [
            'name' => 'required|string',
            'description' => 'nullable|string',
            'enrollment_year' => 'required|digits:4|integer',
        ]);

        $customAttributes = [
            'name' => 'Nama Kelas',
            'description' => 'Deskripsi',
            'enrollment_year' => 'Tahun Masuk',
        ];
        $validator->setAttributeNames($customAttributes);
        $validator->validate();

        DB::beginTransaction();
        try {
            $update = [
                'name'            => $data['name'],
                'description'     => $data['description'] ?? null,
                'enrollment_year' => $data['enrollment_year'],
                'updated_by'      => Auth::user()->id,
                'updated_at'      => datetime_now(),
            ];

            DB::table(DatabaseEntity::CLASSE)
                ->where("id", $id)
                ->update($update);

            DB::commit();
            return Response::buildSuccess(message: ResponseEntity::SUCCESS_MESSAGE_UPDATED);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);
            return Response::buildErrorService($e->getMessage());
        }
    }

    public function delete(int $id): array
    {
        $funcName = $this->className . ".delete";

        DB::beginTransaction();
        try {
            $delete = DB::table(DatabaseEntity::CLASSE)
                ->where("id", $id)
                ->update([
                    'deleted_by' => Auth::user()->id,
                    'deleted_at' => datetime_now(),
                ]);

            if (!$delete) {
                DB::rollback();
                throw new Exception("FAILED DELETE DATA");
            }

            DB::commit();
            return Response::buildSuccess(message: ResponseEntity::SUCCESS_MESSAGE_DELETED);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage(), [
                "func_name" => $funcName,
                'user' => Auth::user()
            ]);
            return Response::buildErrorService($e->getMessage());
        }
    }
}
