<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ApplyTrack') · ApplyTrack</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;500;600;700;800&family=Lexend:wght@500;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
    <header class="topbar">
        <div class="container topbar__inner">
            <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="brand">
                <x-logo />
                <span><b>APPLY</b><span>TRACK</span></span>
            </a>

            @auth
                <nav class="nav" aria-label="{{ __('Main') }}">
                    <a href="{{ route('dashboard') }}" @class(['nav__link', 'is-active' => request()->routeIs('dashboard')])>{{ __('Dashboard') }}</a>
                    <a href="{{ route('applications.index') }}" @class(['nav__link', 'is-active' => request()->routeIs('applications.*')])>{{ __('Applications') }}</a>
                </nav>
            @endauth

            <div class="topbar__actions">
                <x-lang-toggle />

                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn--ghost btn--sm">{{ __('Log out') }}</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn--ghost btn--sm">{{ __('Log in') }}</a>
                    <a href="{{ route('register') }}" class="btn btn--primary btn--sm">{{ __('Sign up') }}</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="page" data-page>
        <div class="container">
            @if (session('status'))
                <div class="flash" role="status" data-flash>{{ session('status') }}</div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            {{ __('Built with Laravel by') }} <a href="https://github.com/AhmetR42">Ahmet</a> · {{ now()->year }}
        </div>
    </footer>
</body>
</html>
