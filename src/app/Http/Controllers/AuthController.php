<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    //会員登録ページの表示
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    //会員登録処理
    public function store(RegisterRequest $request)
    {
        // バリデーション済みデータの取得
        $validated = $request->validated();

        // ユーザー作成
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'is_admin' => false,
        ]);

        // 自動ログイン
        Auth::login($user);

        // メール認証イベント発行（Laravel標準のVerify機能を使う場合）
        event(new Registered($user));

        // 認証メール案内画面へ
        return redirect()->route('verification.notice');
    }

    //ログインフォームの表示
    public function showLoginForm()
    {
        return view('auth.login');
    }

    //ログイン処理
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/attendance'); // ログイン後の遷移先
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません。',
        ])->withInput($request->only('email'));
    }

    //ログアウト処理
    public function logout(Request $request)
    {
        $user = Auth::user();
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user && $user->is_admin) {
            return redirect('/admin/login')->with('status', 'ログアウトしました');
        }

        return redirect('/login')->with('status', 'ログアウトしました');
    }
}
