<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // 管理者ログインフォームを表示
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    // 管理者ログイン処理
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $request->validated();

        // ログイン試行（webガード）
        if (Auth::attempt($credentials)) {
            $user = Auth::guard('admin')->user();

            // 管理者かどうかチェック
            if (Auth::guard('admin')->attempt($credentials)) {
                $user = Auth::guard('admin')->user();
                if ($user->is_admin) {
                    return redirect()->route('admin.attendance.index');
                } else {
                    Auth::guard('admin')->logout();
                    return redirect()->route('admin.login')->withErrors([
                        'login' => '管理者権限がありません',
                    ]);
                }
            }
        }

        // ログイン失敗時
        return back()->withErrors(['login' => 'ログイン情報が正しくありません'])->withInput();
    }

    // 管理者ログアウト処理
    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login')->with('status', 'ログアウトしました');
    }
}
