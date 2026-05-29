<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected array $page = [
        "route" => "pegawai",
        "title" => "Dashboard Pegawai",
        "active" => "dashboard",
    ];

    public function index(): View | Response | RedirectResponse
    {
        $user = Auth::user();

        if (empty($user->member_id)) {
            return redirect("pegawai/auth/login")
                ->with('error', 'Akun ini belum terhubung dengan data pegawai.');
        }

        return render_view("_pegawai.app.list", [
            'page' => $this->page,
        ]);
    }
}
