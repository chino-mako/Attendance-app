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

            {{-- ログインユーザー向けナビゲーション --}}
                @auth
                    @php
                        $today = \Carbon\Carbon::today()->toDateString();
                        $attendance = \App\Models\Attendance::where('user_id', Auth::id())->where('work_date', $today)->first();
                        $status = optional($attendance)->status ?? '勤務外';
                    @endphp

                    <nav class="nav">
                        @if($status !== '退勤済')
                            <a href="/attendance">勤怠</a>
                            <a href="{{ route('attendance.index') }}">勤怠一覧</a>
                            <a href="/stamp_correction_request/list">申請</a>
                        @else
                            <a href="{{ route('attendance.index') }}">今月の出勤一覧</a>
                            <a href="/stamp_correction_request/list">申請一覧</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="logout-btn">ログアウト</button>
                        </form>
                    </nav>
                @endauth

        </div>
    </header>

    <main class="container">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>