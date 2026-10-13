@extends('layouts.app')

@section('title', __('Sign up'))

@section('content')
    <div class="auth card">
        <h1>{{ __('Create your account') }}</h1>
        <p class="muted">{{ __('Start tracking your applications in under a minute.') }}</p>

        <form method="POST" action="{{ route('register') }}" class="form">
            @csrf
            <label class="field">
                <span>{{ __('Name') }}</span>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
                @error('name') <small class="field__error">{{ $message }}</small> @enderror
            </label>
            <label class="field">
                <span>{{ __('Email') }}</span>
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                @error('email') <small class="field__error">{{ $message }}</small> @enderror
            </label>
            <label class="field">
                <span>{{ __('Password') }}</span>
                <input type="password" name="password" required autocomplete="new-password">
                @error('password') <small class="field__error">{{ $message }}</small> @enderror
            </label>
            <label class="field">
                <span>{{ __('Confirm password') }}</span>
                <input type="password" name="password_confirmation" required autocomplete="new-password">
            </label>
            <button type="submit" class="btn btn--primary btn--block">{{ __('Sign up') }}</button>
        </form>

        <p class="muted auth__switch">{{ __('Already have an account?') }} <a href="{{ route('login') }}">{{ __('Log in') }}</a></p>
    </div>
@endsection
