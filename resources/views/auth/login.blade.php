@extends('layouts.app')

@section('title', __('Log in'))

@section('content')
    <div class="auth card">
        <h1>{{ __('Welcome back') }}</h1>
        <p class="muted">{{ __('Log in to see your applications.') }}</p>

        <form method="POST" action="{{ route('login') }}" class="form">
            @csrf
            <label class="field">
                <span>{{ __('Email') }}</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                @error('email') <small class="field__error">{{ $message }}</small> @enderror
            </label>
            <label class="field">
                <span>{{ __('Password') }}</span>
                <input type="password" name="password" required autocomplete="current-password">
                @error('password') <small class="field__error">{{ $message }}</small> @enderror
            </label>
            <label class="checkbox">
                <input type="checkbox" name="remember" value="1">
                <span>{{ __('Remember me') }}</span>
            </label>
            <button type="submit" class="btn btn--primary btn--block">{{ __('Log in') }}</button>
        </form>

        <p class="muted auth__switch">{{ __('No account yet?') }} <a href="{{ route('register') }}">{{ __('Sign up') }}</a></p>
    </div>
@endsection
