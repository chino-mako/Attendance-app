@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endpush

@section('title', '勤怠一覧')

@section('content')
    <div class="attendance-header">
        <h1 class="title">勤怠一覧</h1>

        <div class="month-navigation">
            <a href="{{ route('attendance.index', ['month' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}" class="nav-btn">← 前月</a>
            <span class="current-month">
                <i class="fa fa-calendar"></i> {{ \Carbon\Carbon::parse($month)->format('Y年m月') }}
            </span>
            <a href="{{ route('attendance.index', ['month' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m')]) }}" class="nav-btn">翌月 →</a>
        </div>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @php
                $daysInMonth = \Carbon\Carbon::parse($month)->daysInMonth;
                $startDate = \Carbon\Carbon::parse($month)->startOfMonth();
            @endphp

            @for ($i = 0; $i < $daysInMonth; $i++)
                @php
                    $date = $startDate->copy()->addDays($i);
                    $attendance = $attendances->firstWhere('work_date', $date->format('Y-m-d'));
                    $totalBreak = ($attendance->breakTimes ?? collect())->sum(function ($break) {
                        return \Carbon\Carbon::parse($break->break_start)->diffInMinutes($break->break_end);
                    });
                    $breakFormatted = $totalBreak ? floor($totalBreak / 60) . ':' . str_pad($totalBreak % 60, 2, '0', STR_PAD_LEFT) : '';
                    $totalHours = '';
                    if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                        $worked = \Carbon\Carbon::parse($attendance->clock_in)->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out)) - $totalBreak;
                        $totalHours = floor($worked / 60) . ':' . str_pad($worked % 60, 2, '0', STR_PAD_LEFT);
                    }
                @endphp
                <tr>
                    <td>{{ $date->format('m/d(D)') }}</td>
                    <td>{{ optional($attendance)->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                    <td>{{ optional($attendance)->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                    <td>{{ $breakFormatted }}</td>
                    <td>{{ $totalHours }}</td>
                    <td>
                        @if($attendance)
                            <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                        @endif
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>
@endsection
