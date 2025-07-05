@extends('layouts.app')

@section('title', '勤怠詳細')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endpush

@section('content')
<h1 class="title">勤怠詳細</h1>

<form action="{{ url('/attendance/' . $attendance->id) }}" method="POST">
    @csrf
    @method('PUT')

    <table class="detail-table">
        <tr>
            <th>名前</th>
            <td>{{ Auth::user()->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日') }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                <input type="time" name="clock_in"
                    value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}"> ～
                <input type="time" name="clock_out"
                    value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">
                @error('clock_in')
                    <p class="error">{{ $message }}</p>
                @enderror
                @error('clock_out')
                    <p class="error">{{ $message }}</p>
                @enderror
            </td>
        </tr>

        @foreach ($attendance->breakTimes as $i => $break)
        <tr>
            <th>休憩{{ $i + 1 }}</th>
            <td>
                <input type="time" name="breaks[{{ $i }}][start]"
                    value="{{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}"> ～
                <input type="time" name="breaks[{{ $i }}][end]"
                    value="{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}">
                @error("breaks.$i.start")
                    <p class="error">{{ $message }}</p>
                @enderror
                @error("breaks.$i.end")
                    <p class="error">{{ $message }}</p>
                @enderror
            </td>
        </tr>
        @endforeach

        <tr>
            <th>休憩{{ count($attendance->breakTimes) + 1 }}</th>
            <td>
                <input type="time" name="breaks[{{ count($attendance->breakTimes) }}][start]"> ～
                <input type="time" name="breaks[{{ count($attendance->breakTimes) }}][end]">
                @error('breaks.' . count($attendance->breakTimes) . '.start')
                    <p class="error">{{ $message }}</p>
                @enderror
                @error('breaks.' . count($attendance->breakTimes) . '.end')
                    <p class="error">{{ $message }}</p>
                @enderror
            </td>
        </tr>

        <tr>
            <th>備考</th>
            <td>
                <textarea name="note" class="note-textarea">{{ old('note', $attendance->attendanceRequests->first()->note ?? '') }}</textarea>
                @error('note')
                    <p class="error">{{ $message }}</p>
                @enderror
            </td>
        </tr>
    </table>

    @if ($hasPendingRequest)
        <p class="warning">承認待ちのため修正はできません。</p>
    @endif

    <button type="submit" class="submit-btn" {{ $hasPendingRequest ? 'disabled' : '' }}>修正</button>
</form>
@endsection
