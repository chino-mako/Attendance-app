@extends('layouts.admin')

@section('title', 'スタッフ一覧')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/staff/index.css') }}">
@endpush

@section('content')
    <h1 class="page-title">スタッフ一覧</h1>

    <table class="staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($staff as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><a href="{{ route('admin.attendance.staff', ['id' => $user->id]) }}" class="detail-link">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
