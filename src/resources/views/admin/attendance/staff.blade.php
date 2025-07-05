@extends('layouts.admin')

@section('title', 'スタッフ別勤怠一覧')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/staff.css') }}">
@endpush

@section('content')
    <h2 class="title">{{ $user->name }}さんの勤怠一覧</h2>

    <div class="controls">
        <form method="GET" action="{{ route('admin.attendance.staff', ['id' => $user->id]) }}">
            @php
                $currentMonth = \Carbon\Carbon::parse($yearMonth);
                $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
                $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
            @endphp

            <input type="hidden" name="month" value="{{ $prevMonth }}">
            <button type="submit" class="btn">前月</button>
        </form>

        <span class="current-month">{{ $currentMonth->format('Y年n月') }}</span>

        <form method="GET" action="{{ route('admin.attendance.staff', ['id' => $user->id]) }}">
            <input type="hidden" name="month" value="{{ $nextMonth }}">
            <button type="submit" class="btn">翌月</button>
        </form>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩時間</th>
                <th>労働時間</th>
                <th>備考</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                @php
                    $clockIn = $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '';
                    $clockOut = $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '';
                    $breakMinutes = $attendance->breakTimes->reduce(function ($carry, $break) {
                        return $carry + \Carbon\Carbon::parse($break->break_start)->diffInMinutes(\Carbon\Carbon::parse($break->break_end));
                    }, 0);
                    $workMinutes = ($attendance->clock_in && $attendance->clock_out)
                        ? max(0, \Carbon\Carbon::parse($attendance->clock_in)->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out)) - $breakMinutes)
                        : '';
                @endphp
                <tr>
                    <td>{{ $attendance->work_date }}</td>
                    <td>{{ $clockIn }}</td>
                    <td>{{ $clockOut }}</td>
                    <td>{{ $breakMinutes }}分</td>
                    <td>{{ $workMinutes }}分</td>
                    <td>{{ $attendance->note ?? '' }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.show', $attendance->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">勤怠情報がありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <form method="GET" action="{{ route('admin.attendance.staff.export', ['id' => $user->id]) }}">
        <input type="hidden" name="month" value="{{ $yearMonth }}">
        <button type="submit" class="btn csv">CSV出力</button>
    </form>
@endsection
