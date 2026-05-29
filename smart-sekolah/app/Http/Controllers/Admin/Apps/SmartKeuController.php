<?php

namespace App\Http\Controllers\Admin\Apps;

use App\Entities\ResponseEntity;
use App\Entities\TrxEntity;
use App\Http\Controllers\Controller;
use App\Usecases\BookCategoryUsecase;
use App\Usecases\BookshelveUsecase;
use App\Usecases\BookUsecase;
use App\Usecases\LoanUsecase;
use App\Usecases\MemberCategoryUsecase;
use App\Usecases\MemberUsecase;
use App\Usecases\SmartKeu\TrxUsecase;
use App\Usecases\SmartKeuUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SmartKeuController extends Controller
{
    protected $usecase;
    protected $trxUsecase;
    protected $page = [
        "route" => "smart-keuangan",
        "title" => "SmartKeuangan",
    ];
    protected $baseRedirect;

    public function __construct(
        SmartKeuUsecase $usecase,
        TrxUsecase $trxUsecase,
    ) {
        $this->usecase = $usecase;
        $this->trxUsecase = $trxUsecase;
        $this->baseRedirect = "admin/" . $this->page['route'];
    }

    public function index(Request $req): View | Response
    {
        $funds = $this->usecase->getSourceOfFunds($req->input());
        $funds = $funds['data']['list'] ?? [];

        $units = $this->usecase->getAllUnits();
        $units = $units['data']['list'] ?? [];

        $fundedTeamIds = $funds->pluck('team_id')->unique();
        $unitsWithoutFunds = $units->reject(fn($unit) => $fundedTeamIds->contains($unit->id));
        $unitsWithEmptyFund = $unitsWithoutFunds->values()->all();
        $unitsWithEmptyFundIds = $unitsWithoutFunds->pluck('id')->toArray();

        return render_view("_admin.app.smart-keu.index", [
            'funds'              => $funds,
            'units'              => $units,
            'unitsWithEmptyFund' => $unitsWithEmptyFund,
            'unitsWithEmptyFundIds' => $unitsWithEmptyFundIds,
            'filter'             => $req->input(),
            'page'               => $this->page,
        ]);
    }

    public function sfDetail(int $sofID, Request $req): View | Response
    {
        $filter = [
            'filter_source_fund' => $sofID,
            'filter_start_date'  => $req->input("start_date") ?? date('Y-m-01'),
            'filter_end_date'    => $req->input("end_date") ?? date('Y-m-d'),
            'filter_status'      => TrxEntity::STATUS_PAID
        ];
        $transactions = $this->trxUsecase->getAll($filter, false);
        $transactions = $transactions['data']['list'] ?? [];
        
        $trxCollect      = collect($transactions);
        $totalPemasukan   = $trxCollect->where('trx_type', 1)->sum('amount_actual');
        $totalPengeluaran = $trxCollect->where('trx_type', 2)->sum('amount_actual');
        
        return render_view("_admin.app.smart-keu.source-fund.detail", [
            'sofID'            => $sofID,
            'transactions'     => $transactions,
            'totalPemasukan'   => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'sfName'           => $req->input('sf_name'),
            'page'             => $this->page,
        ]);
    }

}
