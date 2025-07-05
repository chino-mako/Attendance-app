<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class StaffController extends Controller
{
    //スタッフ一覧ページの表示
    public function index(Request $request)
    {
        // 検索キーワードを取得（null可）
        $search = $request->input('search');

        // 並び順カラム（許可されたもののみ）
        $sortableColumns = ['name', 'email'];
        $sortColumn = in_array($request->input('sort'), $sortableColumns) ? $request->input('sort') : 'name';

        // 並び順方向（asc or desc）
        $sortDirection = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        // クエリビルダ開始
        $query = User::where('is_admin', false);

        // 名前またはメールアドレスの部分一致検索（検索キーワードがある場合）
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 並び順を適用
        $query->orderBy($sortColumn, $sortDirection);

        // スタッフ一覧を取得
        $staff = $query->get();

        // ビューへデータを渡して表示
        return view('admin.staff.index', compact('staff', 'search', 'sortColumn', 'sortDirection'));
    }
}
