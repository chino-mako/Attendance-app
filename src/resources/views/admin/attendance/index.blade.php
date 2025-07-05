@extends('layouts.admin')

@section('title', '勤怠一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/index.css') }}">
@endpush

@section('content')
<h1 class="page-title">{{ \Carbon\Carbon::parse(request('date', now()->toDateString()))->format('Y年n月j日') }}の勤怠</h1>

<div class="date-navigation">
    <a href="{{ route('admin.attendance.index', ['date' => \Carbon\Carbon::parse(request('date', now()->toDateString()))->subDay()->toDateString()]) }}" class="nav-btn">← 前日</a>
    <span class="current-date"><i class="fas fa-calendar-alt"></i> {{ request('date', now()->format('Y-m-d')) }}</span>
    <a href="{{ route('admin.attendance.index', ['date' => \Carbon\Carbon::parse(request('date', now()->toDateString()))->addDay()->toDateString()]) }}" class="nav-btn">翌日 →</a>
</div>

<table class="attendance-table">
    <thead>
        <tr>
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($attendances as $attendance)
            <tr>
                <td>{{ $attendance->user->name }}</td>
                <td>{{ optional($attendance->clock_in)->format('H:i') ?? '' }}</td>
                <td>{{ optional($attendance->clock_out)->format('H:i') ?? '' }}</td>
                <td>
                    @php
                        $breakMinutes = $attendance->breakTimes->sum(function ($bt) {
                            return \Carbon\Carbon::parse($bt->break_start)->diffInMinutes($bt->break_end ?? now());
                        });
                    @endphp
                    {{ floor($breakMinutes / 60) }}:{{ str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) }}
                </td>
                <td>
                    @if ($attendance->clock_in && $attendance->clock_out)
                        @php
                            $totalMinutes = \Carbon\Carbon::parse($attendance->clock_in)->diffInMinutes($attendance->bclock_out) - $breakMinutes;
                        @endphp
                        {{ floor($totalMinutes / 60) }}:{{ str_pad($totalMinutes % 60, 2, '0', STR_PAD_LEFT) }}
                    @endif
                </td>
                <td><a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}">詳細</a></td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
