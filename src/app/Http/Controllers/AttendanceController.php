<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
use App\Models\RequestBreakTime;
use App\Http\Requests\AttendanceRequestRequest;

class AttendanceController extends Controller
{
    //勤怠打刻画面を表示する
    public function show()
    {
        //ログイン中のユーザーを取得
        $user = Auth::user();
        //今日の日付を取得
        $today = Carbon::today()->toDateString();
        //今日の自分の勤怠データを1件取得
        $attendance = Attendance::where('user_id', $user->id)
                                ->where('work_date', $today)
                                ->first();

        return view('attendance.create', compact('attendance'));
    }

    //  勤怠打刻の処理（出勤・休憩・退勤など）
    public function store(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['status' => '勤務外']
        );

        $action = $request->input('action');

        switch ($action) {
            case 'clock_in':
                if ($attendance->status !== '勤務外') {
                    return back()->withErrors('既に出勤済みです。');
                }
                $attendance->status = '出勤中';
                $attendance->clock_in = now();
                $attendance->save();
                return back()->with('success', '出勤しました。');

            case 'break_start':
                if ($attendance->status !== '出勤中') {
                    return back()->withErrors('出勤中でないため休憩できません。');
                }
                $attendance->status = '休憩中';
                $attendance->save();

                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => now(),
                ]);
                return back()->with('success', '休憩開始しました。');

            case 'break_end':
                if ($attendance->status !== '休憩中') {
                    return back()->withErrors('現在休憩中ではありません。');
                }
                $lastBreak = BreakTime::where('attendance_id', $attendance->id)
                                    ->whereNull('break_end')
                                    ->latest('break_start')
                                    ->first();

                if (!$lastBreak) {
                    return back()->withErrors('休憩記録が見つかりません。');
                }

                $lastBreak->break_end = now();
                $lastBreak->save();

                $attendance->status = '出勤中';
                $attendance->save();
                return back()->with('success', '休憩終了しました。');

            case 'clock_out':
                if ($attendance->status !== '出勤中') {
                    return back()->withErrors('退勤できる状態ではありません。');
                }
                $attendance->status = '退勤済';
                $attendance->clock_out = now();
                $attendance->save();
                return back()->with('success', '退勤しました。');

            default:
                return back()->withErrors('不正な打刻操作です。');
            }
    }

    //  勤怠一覧画面
    public function index(Request $request)
    {
        $user = Auth::user();
        $month = $request->query('month', Carbon::now()->format('Y-m'));

        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date')
            ->get();

        return view('attendance.index', compact('attendances', 'month'));
    }

    //勤怠詳細画面
    public function showDetail($id)
    {
        $user = Auth::user();

        $attendance = Attendance::with(['breakTimes', 'attendanceRequests'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // 承認待ちの修正申請があるかを確認
        $hasPendingRequest = $attendance->attendanceRequests()
            ->where('status', '承認待ち')
            ->exists();

        return view('attendance.show', compact('attendance', 'hasPendingRequest'));
    }

    //勤怠修正申請
    public function update(AttendanceRequestRequest $request, $id)
    {
        $user = Auth::user();

        $attendance = Attendance::where('id', $id)
                                ->where('user_id', $user->id)
                                ->firstOrFail();

        $attendance->status = '承認待ち';
        $attendance->save();

        $requestData = $request->only([
            'clock_in', 'clock_out', 'breaks', 'note'
        ]);

        $attendanceRequest = AttendanceRequest::updateOrCreate([
            'attendance_id' => $attendance->id,
            'user_id' => Auth::id(),
            'clock_in' => $request->input('clock_in'),
            'clock_out' => $request->input('clock_out'),
            'note' => $request->input('note'),
            'status' => '承認待ち',
            'request_date' => now()->toDateString(),
        ]);

        // 既存の休憩を削除 → 新たに登録
        if (isset($requestData['breaks']) && is_array($requestData['breaks'])) {
            RequestBreakTime::where('attendance_id', $attendanceRequest->attendance_id)->delete();

            foreach ($requestData['breaks'] as $break) {
                RequestBreakTime::create([
                    'attendance_id' => $attendanceRequest->attendance_id,
                    'break_start' => Carbon::parse($break['start']),
                    'break_end' => Carbon::parse($break['end']),
                ]);
            }
        }

        return redirect()->route('stamp_correction_request.index')->with('success', '修正申請を送信しました。');
    }
}
