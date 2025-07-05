@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance/create.css') }}">
@endpush

@section('title', '勤怠打刻')

@section('content')
    <div class="status-label">
        {{ $attendance->status ?? '勤務外' }}
    </div>

    <div class="date">{{ now()->format('Y年n月j日(D)') }}</div>
    <div class="time">{{ now()->format('H:i') }}</div>

    @if (session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ url('/attendance') }}">
        @csrf
        @php
            $status = $attendance->status ?? '勤務外';
        @endphp

        @if ($status === '勤務外')
            <button type="submit" name="action" value="clock_in" class="action-btn">出勤</button>
        @elseif ($status === '出勤中')
            <button type="submit" name="action" value="break_start" class="action-btn">休憩</button>
            <button type="submit" name="action" value="clock_out" class="action-btn">退勤</button>
        @elseif ($status === '休憩中')
            <button type="submit" name="action" value="break_end" class="action-btn">休憩戻</button>
        @elseif ($status === '退勤済')
            <p class="completed">お疲れ様でした。</p>
        @endif
    </form>
@endsection
