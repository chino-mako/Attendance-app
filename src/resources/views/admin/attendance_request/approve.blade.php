@extends('layouts.admin')

@section('title', '勤怠詳細')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_request/approve.css') }}">
@endpush

@section('content')
    <h1 class="title">勤怠詳細</h1>

    <div class="detail-card">
        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td colspan="2">{{ $request->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td colspan="2">{{ \Carbon\Carbon::parse($request->request_date)->format('Y年n月j日') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>{{ \Carbon\Carbon::parse($request->clock_in)->format('H:i') }}</td>
                <td>〜 {{ \Carbon\Carbon::parse($request->clock_out)->format('H:i') }}</td>
            </tr>
            @foreach ($request->requestBreakTimes as $index => $break)
                <tr>
                    <th>休憩{{ $index + 1 }}</th>
                    <td>{{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}</td>
                    <td>〜 {{ \Carbon\Carbon::parse($break->break_end)->format('H:i') }}</td>
                </tr>
            @endforeach
            <tr>
                <th>備考</th>
                <td colspan="2">{{ $request->note }}</td>
            </tr>
        </table>
    </div>

    <form method="POST" action="{{ route('admin.stamp_correction_request.approve', $request->id) }}">
        @csrf
        <button type="submit" class="approve-btn">承認</button>
    </form>
@endsection
