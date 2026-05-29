<?php

namespace App\Usecases;

use App\Http\Presenter\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DataWajahMemberUsecase extends Usecase
{
    public string $className = "DataWajahMemberUsecase";

    /**
     * ============================
     * GET ALL DATA WAJAH
     * ============================
     */
    public function getAll(array $filter = []): array {
        try {

            $query = DB::table("data_wajah_member as d")
                ->leftJoin("members as m", "m.id", "=", "d.member_id");

            if (!empty($filter['member_id'])) {
                $query->where("d.member_id", $filter['member_id']);
            }

            $data = $query
                ->orderBy("d.created_at", "desc")
                ->paginate(20, [
                    "d.id",
                    "d.member_id",
                    "m.name as member_name",
                    "d.data_embedding_wajah",
                    "d.foto_wajah",
                    "d.status_aktif",
                    "d.created_at"
                ]);

            return Response::buildSuccess([
                "list" => $data
            ]);

        } catch (\Exception $e) {

            Log::error($e->getMessage());

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * ============================
     * CREATE / UPDATE (UPSERT)
     * ============================
     */
    public function create(Request $request): array {
        $validator = Validator::make($request->all(), [
            "member_id" => "required|exists:members,id",
            "data_embedding_wajah" => "required",
            "foto_wajah" => "nullable|string"
        ]);

        $validator->validate();

        DB::beginTransaction();

        try {

            DB::table("data_wajah_member")->updateOrInsert(
                [
                    "member_id" => $request->member_id
                ],
                [
                    "data_embedding_wajah" => $request->data_embedding_wajah,
                    "foto_wajah" => $request->foto_wajah,
                    "status_aktif" => 1,
                    "updated_at" => now(),
                    "created_at" => now()
                ]
            );

            DB::commit();

            return Response::buildSuccessCreated();

        } catch (\Exception $e) {

            DB::rollback();

            Log::error($e->getMessage());

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * ============================
     * GET BY ID
     * ============================
     */
    public function getByID(int $id): array {
        try {

            $data = DB::table("data_wajah_member")
                ->where("id", $id)
                ->first();

            return Response::buildSuccess([
                "data" => (array) $data
            ]);

        } catch (\Exception $e) {

            return Response::buildErrorService($e->getMessage());
        }
    }

    /**
     * ============================
     * DELETE
     * ============================
     */
    public function delete(int $id): array {
        try {

            $deleted = DB::table("data_wajah_member")
                ->where("id", $id)
                ->delete();

            if (!$deleted) {
                return Response::buildErrorService("Data tidak ditemukan");
            }

            return Response::buildSuccess(
                message: "Data berhasil dihapus"
            );

        } catch (\Exception $e) {

            Log::error($e->getMessage());

            return Response::buildErrorService($e->getMessage());
        }
    }
}