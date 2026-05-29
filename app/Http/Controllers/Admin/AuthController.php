<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Usecases\UserUsecase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(): View
    {
        return view("_admin.auth.login");
    }

    public function loginPegawai(): View
    {
        return view("_pegawai.auth.login");
    }

    public function doLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = DB::table('users as u')
            ->leftJoin('members as m', 'm.id', '=', 'u.member_id')
            ->leftJoin('member_categories as mc', 'mc.id', '=', 'm.category_id')
            ->leftJoin('tenants as t', 't.id', '=', 'u.tenant_id')
            ->where('u.email', $request->email)
            ->where('u.is_active', 1)
            ->whereNull('u.deleted_at')
            ->select(
                'u.*',
                'm.name as member_name',
                'm.category_id',
                'mc.name as member_category',
                't.name as tenant_name'
            )
            ->first();

        if (!$user) {
            return redirect()
                ->back()
                ->withInput($request->only('email'))
                ->with('error', 'Email/Password salah, periksa kembali dan coba lagi!');
        }

        if (!Hash::check($request->password, $user->password)) {
            return redirect()
                ->back()
                ->withInput($request->only('email'))
                ->with('error', 'Email/Password salah, periksa kembali dan coba lagi!');
        }

        /**
         * Cek role sebelum login.
         * access_type:
         * 1 = Super Admin
         * 2 = Kepala Sekolah/Admin
         * 3 = Karyawan/Guru/Tendik
         */
        $accessType = (int) $user->access_type;

        if (!in_array($accessType, [1, 2, 3])) {
            return redirect()
                ->back()
                ->withInput($request->only('email'))
                ->with('error', 'Role akun tidak dikenali.');
        }

        /**
         * Pegawai wajib punya member_id dan kategori Guru/Tendik.
         */
        if ($accessType === 3) {
            if (empty($user->member_id)) {
                return redirect()
                    ->back()
                    ->withInput($request->only('email'))
                    ->with('error', 'Akun pegawai belum terhubung dengan data member.');
            }

            if (!in_array(strtolower($user->member_category ?? ''), ['guru', 'tendik'])) {
                return redirect()
                    ->back()
                    ->withInput($request->only('email'))
                    ->with('error', 'Akun ini bukan akun Guru atau Tendik.');
            }
        }

        Auth::loginUsingId($user->id);

        $request->session()->regenerate();

        session([
            'access_type'     => $user->access_type,
            'tenant_name'     => $user->tenant_name ?? null,
            'member_id'       => $user->member_id ?? null,
            'member_name'     => $user->member_name ?? null,
            'member_category' => $user->member_category ?? null,
        ]);

        /**
         * Cek password default.
         */
        $defaultPasswordHash = '$2y$12$2i3/Ln/Nf99VAwEYDxzsWueot4AFIEOyrBytjHTVfbk3gTUqwHOFG';

        if ($user->password === $defaultPasswordHash) {
            if ($accessType === 3) {
                return redirect('pegawai/setting/change-password');
            }

            return redirect('admin/auth/reset-default-password');
        }

        /**
         * Redirect sesuai role.
         */
        if (in_array($accessType, [1, 2])) {
            return redirect('admin/app')
                ->with('success', 'Selamat Datang kembali!');
        }

        if ($accessType === 3) {
            return redirect('pegawai/app')
                ->with('success', 'Selamat Datang kembali!');
        }

        Auth::logout();

        return redirect('admin/auth/login')
            ->with('error', 'Role akun tidak dikenali.');
    }

    public function doLogout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect("admin/auth/login");
    }

    public function doLogoutPegawai(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect("pegawai/auth/login");
    }

    public function changePassword(): View
    {
        return view("_admin.auth.change-password");
    }

    public function resetDefaultPassword(): View
    {
        return view("_admin.reset-default-password");
    }

    public function doResetDefaultPassword(Request $req): RedirectResponse
    {
        $usecase = new UserUsecase();

        $update = $usecase->changeDefaultPassword(
            data: $req->input(),
        );

        if (empty($update['error'])) {
            $user = Auth::user();

            if (!empty($user) && (int) $user->access_type === 3) {
                return redirect()
                    ->intended('pegawai')
                    ->with('success', 'Password baru berhasil disimpan!');
            }

            return redirect()
                ->intended('admin/app')
                ->with('success', 'Password baru berhasil disimpan!');
        }

        return redirect()
            ->intended('admin/auth/reset-default-password')
            ->with('error', 'Maaf, terjadi kesalahan! Hubungi pengembang Aplikasi');
    }
}