@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
<div class="container">
    <p class="message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>
    @php
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => auth()->user()->id, 'hash' => sha1(auth()->user()->email)]
    );
    @endphp

    <a href="{{ $verificationUrl }}" class="verify-button">認証はこちらから</a>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="submit">認証メールを再送する</button>
    </form>

    @if (session('resent'))
        <div class="alert alert-success" role="alert">
            認証メールを再送信しました。
        </div>
    @endif

    <div class="mt-3">
        <a href="http://127.0.0.1:8025" target="_blank" rel="noopener noreferrer">MailHogでメールを確認する</a>
    </div>
</div>
@endsection
