<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use App\Http\Requests\AttendanceRequestRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceRequestController extends Controller
{
    //申請一覧表示
    public function index(Request $request)
    {
        $user = Auth::user();

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        // 承認待ち申請
        $pendingRequests = AttendanceRequest::with(['user', 'attendance'])
        ->where('status', '承認待ち')
        ->orderBy($sort, $direction)
        ->get();

        // 承認済み申請
        $approvedRequests = AttendanceRequest::with(['user', 'attendance'])
        ->where('status', '承認済み')
        ->orderBy($sort, $direction)
        ->get();

        return view('admin.attendance_request.index', [
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
        ]);
    }

    //修正申請の詳細表示
    public function show($id)
    {
        $request = AttendanceRequest::with(['user', 'attendance'])->findOrFail($id);

        return view('admin.attendance_request.approve', compact('request'));
    }

    //修正申請の承認処理
    public function approve($id)
    {
        DB::beginTransaction();

        try {
            $request = AttendanceRequest::with('attendance')->findOrFail($id);
            $attendance = $request->attendance;

            // 勤怠情報を修正内容で更新
            $attendance->update([
                'clock_in' => $request->clock_in,
                'clock_out' => $request->clock_out,
                'note' => $request->note,
            ]);

            // 修正申請ステータス更新
            $request->update([
                'status' => '承認済み',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.stamp_correction_request.index')->with('success', '修正申請を承認しました。');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', '承認に失敗しました。もう一度お試しください。');
        }
    }

    //修正申請の一括承認
    public function bulkApprove(Request $request)
    {
        $ids = $request->input('ids');

        if (empty($ids)) {
            return back()->with('error', '申請が選択されていません。');
        }

        DB::beginTransaction();

        try {
            foreach ($ids as $id) {
                $requestItem = AttendanceRequest::with('attendance')->findOrFail($id);
                $attendance = $requestItem->attendance;

                $attendance->update([
                    'start_time' => $requestItem->requested_start_time,
                    'end_time' => $requestItem->requested_end_time,
                    'note' => $requestItem->requested_note,
                ]);

                $requestItem->update([
                    'status' => '承認済み',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('attendance_requests.index')->with('success', '選択された申請を一括承認しました。');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', '一括承認に失敗しました。');
        }
    }
}
