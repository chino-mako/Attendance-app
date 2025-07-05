@extends('layouts.app')

@section('title', '申請一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/attendance_request/index.css') }}">
@endpush

@section('content')
<h1 class="title">申請一覧</h1>

<div class="tabs">
    <button class="tab-button active" onclick="switchTab('pending')">承認待ち</button>
    <button class="tab-button" onclick="switchTab('approved')">承認済み</button>
</div>

<div id="pending" class="tab-content active">
    <table class="request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pendingRequests as $request)
            <tr>
                <td>{{ $request->status }}</td>
                <td>{{ $request->attendance->work_date }}</td>
                <td>{{ $request->note }}</td>
                <td>{{ $request->created_at->format('Y/m/d') }}</td>
                <td>
                    <a href="{{ route('attendance.detail', ['id' => $request->attendance_id]) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div id="approved" class="tab-content">
    <table class="request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($approvedRequests as $request)
            <tr>
                <td>{{ $request->status }}</td>
                <td>{{ $request->attendance->work_date }}</td>
                <td>{{ $request->note }}</td>
                <td>{{ $request->created_at->format('Y/m/d') }}</td>
                <td>
                    <a href="{{ route('attendance.detail', ['id' => $request->attendance_id]) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(div => div.classList.remove('active'));
    document.querySelector(`#${tab}`).classList.add('active');
    event.target.classList.add('active');
}
</script>
@endpush
