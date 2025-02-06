<?php

namespace App\Usecases;

use App\Entities\BookEntity;
use App\Entities\DatabaseEntity;
use App\Entities\ResponseEntity;
use App\Http\Presenter\Response;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SmartKeuUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "SmartKeuUsecase";
    }

    public function getUserAccess(): object
    {
        return DB::table('user_access')
            ->where("app_id", 1)
            ->where('user_id', Auth::user()->id)
            ->first();
    }

    public function getSourceOfFunds(array $filterData = []): array
    {
        $funcName = $this->className . ".getSourceOfFunds";

        $page  = $filterData['page'] ?? 1;
        $limit = $filterData['limit'] ?? 30;
        $page  = ($page > 0 ? $page : 1);
        $filterUser = $filterData['filter_user_id'] ?? "";
        $filterTeam = $filterData['filter_team_id'] ?? "";

        try {
            $data = DB::connection(DatabaseEntity::SQL_SMARTKEU)
                ->table("source_of_funds as sf")
                ->leftJoin("users as u", "u.id", "=", "sf.user_id")
                ->leftJoin("teams as t", "t.id", "=", "sf.team_id")
                ->whereNull("sf.deleted_at")
                ->select(["sf.id", "sf.name as sf_name", "sf.saldo", "u.name as user", "t.name as team", 't.id as team_id', 'sf.updated_at']);

            if (!empty($filterUser)) {
                $data = $data->where('sf.user_id', (int) $filterUser);
            }
            if (!empty($filterTeam)) {
                $data = $data->where('sf.team_id', (int) $filterTeam);
            }

            $data = $data->orderBy("sf.name", "asc")->paginate($limit)->appends(request()->query());

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

    public function getAllUnits(array $filterData = []): array
    {
        $funcName = $this->className . ".getAllUnits";

        $userAccess = $this->getUserAccess();

        $filterTeamIds = $filterData['filter_team_ids'] ?? $userAccess->tenant_ids;

        try {
            $data = DB::connection(DatabaseEntity::SQL_SMARTKEU)
                ->table("teams")
                ->orderBy("id", "asc");

            if (!empty($filterTeamIds)) {
                $data = $data->whereIn('id',  json_decode($filterTeamIds));
            }
            return Response::buildSuccess(
                [
                    'list' =>  $data->get(),
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
}
