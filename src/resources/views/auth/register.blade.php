@extends('layouts.app')

@section('title', '会員登録')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
        <h1 class="title">会員登録</h1>

        <form method="POST" action="{{ url('/register') }}">
            @csrf

            <div class="form-group">
                <label for="name">名前</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}">
                @error('name')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}">
                @error('email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input id="password" type="password" name="password">
                @error('password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">パスワード確認</label>
                <input id="password_confirmation" type="password" name="password_confirmation">
            </div>

            <button type="submit" class="register-btn">登録する</button>
        </form>

        <div class="login-link">
            <a href="{{ url('/login') }}">ログインはこちら</a>
        </div>
@endsection



