<?php

namespace App\Usecases\SmartKeu;

use App\Entities\BookEntity;
use App\Entities\DatabaseEntity;
use App\Entities\ResponseEntity;
use App\Http\Presenter\Response;
use App\Usecases\Usecase;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TrxUsecase extends Usecase
{
    public string $className;

    public function __construct()
    {
        $this->className = "TrxUsecase";
    }

    public function getAll(array $filterData = [], bool $withPagination = true): array
    {
        $funcName = $this->className . ".getAll";

        $page                = $filterData['page'] ?? 1;
        $limit               = $filterData['limit'] ?? 10;
        $page                = ($page > 0 ? $page : 1);
        $filterName          = $filterData['filter_name'] ?? "";
        $filterPaymentMethod = $filterData['filter_payment_method'] ?? "";
        $filterTrxType       = $filterData['filter_trx_type'] ?? "";
        $filterSourceFund    = $filterData['filter_source_fund'] ?? "";
        $filterCtg           = $filterData['filter_category_id'] ?? "";
        $filterDesc          = $filterData['filter_description'] ?? "";
        $filterStartDate     = $filterData['filter_start_date'] ?? "";
        $filterEndDate       = $filterData['filter_end_date'] ?? "";
        $filterStatus        = $filterData['filter_status'] ?? "";

        $filterMonth = $filterData['filter_month'] ?? "";
        $filterYear  = $filterData['filter_year'] ?? year_now();

        try {
            $data = DB::connection(DatabaseEntity::SQL_SMARTKEU)
                ->table('transactions', 't')
                ->leftJoin("source_of_funds as sf", "sf.id", "=", "t.source_of_fund_id")
                ->leftJoin("pic_units as pu", "pu.id", "=", "t.pic_type_id")
                ->leftJoin("trx_categories as tc", "tc.id", "=", "t.category_id")
                ->whereNull("t.deleted_at");

            if (!empty($filterName)) {
                $data = $data->where('t.executor_name', 'like', '%' . $filterName . '%');
            }
            if (!empty($filterDesc)) {
                $data = $data->where('t.description', 'like', '%' . $filterDesc . '%');
            }
            if (!empty($filterPaymentMethod)) {
                $data = $data->where('t.payment_method', (int) $filterPaymentMethod);
            }
            if (!empty($filterSourceFund)) {
                $data = $data->where('t.source_of_fund_id', (int) $filterSourceFund);
            }
            if (!empty($filterTrxType)) {
                $data = $data->where('t.trx_type', (int) $filterTrxType);
            }
            if (!empty($filterCtg)) {
                $data = $data->where('t.category_id', (int) $filterCtg);
            }
            if (!empty($filterStatus)) {
                $data = $data->where('t.status', (int) $filterStatus);
            }
            if (!empty($filterMonth)) {
                $data = $data->whereMonth('t.trx_date', "=", $filterMonth);
            }
            if (!empty($filterYear)) {
                $data = $data->whereYear('t.trx_date', '=', $filterYear);
            }
            if (!empty($filterStartDate) && !empty($filterEndDate)) {
                $data = $data->whereBetween('t.trx_date', [$filterStartDate, $filterEndDate]);
            }

            $fields = ['t.*', 'tc.name as category', 'pu.name as picUnits', 'sf.name as scfund', 'sf.slug as sf_slug'];

            $data = $data->orderBy("t.id", "desc");

            if ($withPagination) {
                $data = $data->paginate(20, $fields)->appends(request()->query());
            } else {
                $data = $data->get($fields);
            }

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
}
