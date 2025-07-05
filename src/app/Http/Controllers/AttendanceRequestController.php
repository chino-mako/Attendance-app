<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceRequest;

class AttendanceRequestController extends Controller
{
    //申請一覧表示
    public function index(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        // 並び順指定（デフォルトは申請日降順）
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        // 承認待ち申請
        $pendingRequests = AttendanceRequest::with(['attendance'])
            ->where('user_id', $userId)
            ->where('status', '承認待ち')
            ->orderBy($sort, $direction)
            ->get();

        // 承認済み申請
        $approvedRequests = AttendanceRequest::with(['attendance'])
            ->where('user_id', $userId)
            ->where('status', '承認済み')
            ->orderBy($sort, $direction)
            ->get();

        return view('attendance_request.index', [
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }
}
