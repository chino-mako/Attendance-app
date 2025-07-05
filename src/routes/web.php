<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\AttendanceRequestController as AdminAttendanceRequestController;


Route::get('/', function () {
    return view('welcome');
});


// --------------------------------------------
// 会員認証（登録・ログイン・ログアウト）
// --------------------------------------------

// 会員登録
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'store']);

// ログイン
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// ログアウト
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// --------------------------------------------
// 一般ユーザー機能（認証必須）
// --------------------------------------------
Route::middleware(['auth'])->group(function () {

    // 勤怠打刻・一覧・詳細
    Route::get('/attendance', [AttendanceController::class, 'show']);
    Route::post('/attendance', [AttendanceController::class, 'store']);
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{id}', [AttendanceController::class, 'showDetail'])->name('attendance.detail');
    Route::put('/attendance/{id}', [AttendanceController::class, 'update']);

    // 打刻修正申請一覧
    Route::get('/stamp_correction_request/list', [AttendanceRequestController::class, 'index'])->name('stamp_correction_request.index');
});


// --------------------------------------------
// 管理者ログイン・ログアウト
// --------------------------------------------
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');


// --------------------------------------------
// 管理者機能（auth + adminミドルウェア）
// --------------------------------------------
Route::middleware(['auth', 'admin'])->group(function () {

    // 勤怠管理
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
    Route::put('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');

    // スタッフ情報一覧・月別勤怠
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.index');
    Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'staffMonthlyList'])->name('admin.attendance.staff');
    Route::get('/admin/attendance/staff/{id}/export', [AdminAttendanceController::class, 'exportMonthlyCsv'])->name('admin.attendance.staff.export');

    // 打刻修正申請（一覧・承認）
    Route::get('/admin/stamp_correction_request/list', [AdminAttendanceRequestController::class, 'index'])->name('admin.stamp_correction_request.index');
    Route::get('/admin/stamp_correction_request/approve/{id}', [AdminAttendanceRequestController::class, 'show'])->name('admin.stamp_correction_request.show');
    Route::post('/admin/stamp_correction_request/approve/{id}', [AdminAttendanceRequestController::class, 'approve'])->name('admin.stamp_correction_request.approve');
    Route::post('/admin/stamp_correction_request/bulk_approve', [AdminAttendanceRequestController::class, 'bulkApprove'])->name('admin.stamp_correction_request.bulk_approve');
});


// --------------------------------------------
// メール認証
// --------------------------------------------
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    return redirect('/attendance')->with('status', 'メール認証が完了しました。');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('resent', true);
})->middleware(['auth'])->name('verification.send');
