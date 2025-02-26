<?php

namespace App\Http\Controllers\Admin;

use App\Entities\Database;
use App\Http\Controllers\Controller;
use App\Http\Entities\CompanyEntity;
use App\Http\Entities\GeneralEntity;
use App\Http\Usecases\UserUsecase;
use App\Usecases\UserUsecase as UsecasesUserUsecase;
use Dflydev\DotAccessData\Data;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(): View
    {
        return view("_admin.auth.login");
    }

    public function doLogin(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required'],
            'password' => ['required'],
        ]);

        $request->session()->invalidate();

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $accessType = $user->access_type;

            $tenant = DB::table("tenants")->where('id', $user->tenant_id)->first();
            
            session(['access_type' => $accessType]);
            session(['tenant_name' => $tenant->name]);

            $request->session()->regenerate();

            if ($user->password == "$2y$12$2i3/Ln/Nf99VAwEYDxzsWueot4AFIEOyrBytjHTVfbk3gTUqwHOFG") {
                return redirect()->intended('admin/auth/reset-default-password');
            }

            return redirect()->intended('admin/app')->with('success', "Selamat Datang kembali!");
        } else {
            return redirect('admin/auth/login')->withError("Email/Password salah, periksa kembali dan coba lagi!");
        }
    }

    public function doLogout(): RedirectResponse
    {
        Auth::logout();

        return redirect("admin/auth/login");
    }

    public function changePassword(): View
    {
        return view("admin.auth.change-password");
    }

    // public function doChangePassword(Request $request): RedirectResponse
    // {
    //     $usecase = new UserUsecase();
    //     $createProcess = $usecase->changePassword(
    //         data: $request->all(),
    //         userID: $request->user()->id,
    //         isAPI: false
    //     );

    //     if (empty($createProcess['error'])) {
    //         return redirect()
    //             ->intended('company/setting')
    //             ->with('success', GeneralEntity::SUCCESS_MESSAGE_UPDATED);
    //     } else {
    //         return redirect()
    //             ->intended('company/auth/change-password')
    //             ->with('error', $createProcess['message']);
    //     }
    // }



    public function resetDefaultPassword(): View
    {
        return view("_admin.reset-default-password");
    }

    public function doResetDefaultPassword(Request $req): RedirectResponse
    {
        $usecase = new UsecasesUserUsecase();
        $update = $usecase->changeDefaultPassword(
            data: $req->input(),
        );

        if (empty($update['error'])) {
            return redirect()->intended('admin/app')->with('success', "Password baru berhasil disimpan!");
        } else {
            return redirect()->intended('admin/auth/reset-default-password')->with('error', "Maaf, terjadi kesalahan! Hubungi pengembang Aplikasi");
        }
    }
}
