@extends('layouts.app')

@section('title', 'ログイン')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
    <h1 class="title">ログイン</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" autofocus>
            @error('email')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">パスワード</label>
            <input type="password" name="password" id="password">
            @error('password')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="register-btn">ログインする</button>

        <div class="login-link">
            <a href="{{ route('register') }}">会員登録はこちら</a>
        </div>
    </form>
@endsection
