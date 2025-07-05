<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="logo">

            {{-- ログイン管理者向けナビゲーション --}}
            @if (Auth::check() && Auth::user()->is_admin)
            <nav class="nav">
                <a href="/admin/attendance/list">勤怠一覧</a>
                <a href="/admin/staff/list">スタッフ一覧</a>
                <a href="{{ route('admin.stamp_correction_request.index') }}">申請一覧</a>
                <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="logout-btn">ログアウト</button>
                </form>
            </nav>
            @endif
        </div>
    </header>

    <main class="container">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
