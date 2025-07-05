<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequestRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 勤怠一覧画面
    public function index(Request $request)
    {
        $date = $request->input('date');
        $name = $request->input('name');
        $sort = $request->input('sort', 'attendances.clock_in');

        $query = Attendance::select('attendances.*')
            ->with(['user', 'breakTimes'])
            ->join('users', 'attendances.user_id', '=', 'users.id');

        if ($date) {
            $query->whereDate('attendances.work_date', $date);
        }

        if ($name) {
            $query->where('users.name', 'like', '%' . $name . '%');
        }

        if (!in_array($sort, ['attendances.clock_in', 'attendances.clock_out', 'users.name'])) {
            $sort = 'attendances.clock_in';
        }

        $attendances = $query->orderBy($sort)->paginate(20);

        return view('admin.attendance.index', compact('attendances', 'date', 'name', 'sort'));
    }

    // 勤怠詳細画面
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes', 'attendanceRequests'])->findOrFail($id);

        $hasPendingRequest = $attendance->attendanceRequests()
            ->where('status', '承認待ち')
            ->exists();

        return view('admin.attendance.show', compact('attendance', 'hasPendingRequest'));
    }

    // 勤怠更新処理
    public function update(AttendanceRequestRequest $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $attendance = Attendance::findOrFail($id);

            $attendance->clock_in = $request->input('clock_in');
            $attendance->clock_out = $request->input('clock_out');
            $attendance->note = $request->input('note');
            $attendance->save();

            // 既存の休憩時間を削除
            $attendance->breakTimes()->delete();

            $breaks = $request->input('break_times', []);

            foreach ($breaks as $break) {
                if (!empty($break['start']) && !empty($break['end'])) {
                    $attendance->breakTimes()->create([
                        'break_start' => $break['start'],
                        'break_end' => $break['end'],
                    ]);
                }
            }
        });

        return redirect()->route('admin.attendance.index')->with('success', '勤怠情報を更新しました。');
    }

    // スタッフ別月次勤怠一覧
    public function staffMonthlyList(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $yearMonth = $request->input('month', Carbon::now()->format('Y-m'));

        $attendances = Attendance::with(['breakTimes', 'attendanceRequests'])
            ->where('user_id', $userId)
            ->where('work_date', 'like', $yearMonth . '%')
            ->orderBy('work_date')
            ->get();

        return view('admin.attendance.staff', compact('user', 'attendances', 'yearMonth'));
    }

    // 月次勤怠CSV出力
    public function exportMonthlyCsv(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $yearMonth = $request->input('month', Carbon::now()->format('Y-m'));

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $userId)
            ->where('work_date', 'like', $yearMonth . '%')
            ->orderBy('work_date')
            ->get();

        $csvData = [];
        $totalMinutes = 0;

        $csvData[] = ['日付', '出勤時間', '退勤時間', '休憩時間（分）', '労働時間（分）', '備考'];

        foreach ($attendances as $att) {
            $breakMinutes = $att->breakTimes->reduce(function ($carry, $break) {
                $start = Carbon::parse($break->break_start);
                $end = Carbon::parse($break->break_end);
                return $carry + max(0, $start->diffInMinutes($end));
            }, 0);

            $workMinutes = 0;
            if ($att->clock_in && $att->clock_out) {
                $workMinutes = max(0, Carbon::parse($att->clock_in)->diffInMinutes(Carbon::parse($att->clock_out)) - $breakMinutes);
            }

            $totalMinutes += $workMinutes;

            $csvData[] = [
                $att->work_date,
                optional($att->clock_in)->format('H:i'),
                optional($att->clock_out)->format('H:i'),
                $breakMinutes,
                $workMinutes,
                $att->note ?? '',
            ];
        }

        $csvData[] = [];
        $csvData[] = ['合計勤務日数', count($attendances)];
        $csvData[] = ['合計労働時間（分）', $totalMinutes];

        $fileName = "勤怠記録_{$user->name}_{$yearMonth}.csv";

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $line) {
                fputcsv($file, $line);
            }
            fclose($file);
        };

        return Response::streamDownload($callback, $fileName, ['Content-Type' => 'text/csv']);
    }
}
