<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // ユーザー情報を取得
        $user = Auth::check() ? Auth::user()->only(['id', 'name', 'email']) : ['user' => 'Guest'];

        // ログ出力
        Log::error('アプリケーションエラーが発生しました', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'user' => $user,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'input' => $request->except(['password', 'password_confirmation']),
            'status' => $this->getStatusCode($exception),
        ]);

        return parent::render($request, $exception);
    }

    // ステータスコードの取得メソッドを追加
    protected function getStatusCode(Throwable $exception): int
    {
        return $exception instanceof HttpException
            ? $exception->getStatusCode()
            : 500;
    }
}
