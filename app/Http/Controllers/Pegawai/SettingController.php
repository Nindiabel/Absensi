<?php

namespace App\Http\Controllers\Pegawai;

use App\Entities\ResponseEntity;
use App\Http\Controllers\Controller;
use App\Usecases\UserUsecase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SettingController extends Controller
{
    protected UserUsecase $userUsecase;

    protected array $page = [
        "route" => "setting",
        "title" => "Pengaturan Pegawai",
    ];

    protected string $baseRedirect;

    public function __construct(UserUsecase $userUsecase)
    {
        $this->userUsecase = $userUsecase;
        $this->baseRedirect = $this->page['route'];
    }

    public function general(Request $req): View | Response
    {
        $theme = session("theme") ?? "light";

        return render_view("_pegawai.setting.general", [
            'theme' => $theme,
            'page' => $this->page,
        ]);
    }

    public function doUpdateGeneral(Request $request): RedirectResponse
    {
        $theme = $request->input('theme', 'light');

        session([
            'theme' => $theme,
        ]);

        return redirect('pegawai/setting/general')
            ->with('success', ResponseEntity::SUCCESS_MESSAGE_UPDATED);
    }

    public function changePassword(): View | Response
    {
        return render_view("_pegawai.setting.change-password", [
            'page' => $this->page,
        ]);
    }

    public function doChangePassword(Request $request): RedirectResponse 
    {
        $process = $this->userUsecase->changePassword(
            data: $request->input(),
        );

        if (empty($process['error'])) {
            return redirect('pegawai/setting/change-password')
                ->with('success', 'Password berhasil diubah!');
        }

        return redirect('pegawai/setting/change-password')
            ->with('error', ResponseEntity::DEFAULT_ERROR_MESSAGE)
            ->withInput();
    }
}