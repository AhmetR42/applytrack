@extends('layouts.app')

@section('title', __('Track your internship applications'))

@section('content')
    <section class="hero">
        <p class="eyebrow">{{ __('For students looking for an internship') }}</p>
        <h1 class="hero__title">{{ __('Every application,') }} <span class="accent">{{ __('one clear overview.') }}</span></h1>
        <p class="hero__lead">{{ __('ApplyTrack keeps all your internship applications in one place. See where you applied, when your interviews are, and how often companies respond.') }}</p>
        <div class="hero__actions">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn--primary">{{ __('Go to dashboard') }}</a>
            @else
                <a href="{{ route('register') }}" class="btn btn--primary">{{ __('Create a free account') }}</a>
                <a href="{{ route('login') }}" class="btn btn--ghost">{{ __('Log in') }}</a>
            @endauth
        </div>
    </section>

    <section class="features">
        <article class="card feature">
            <span class="feature__icon" aria-hidden="true">📋</span>
            <h2>{{ __('Status pipeline') }}</h2>
            <p>{{ __('Move each application from wishlist to applied, interview and offer. Every change is saved in a timeline.') }}</p>
        </article>
        <article class="card feature">
            <span class="feature__icon" aria-hidden="true">📈</span>
            <h2>{{ __('Insights') }}</h2>
            <p>{{ __('See your response rate, applications per week and upcoming interviews at a glance.') }}</p>
        </article>
        <article class="card feature">
            <span class="feature__icon" aria-hidden="true">🌍</span>
            <h2>{{ __('English and Dutch') }}</h2>
            <p>{{ __('Switch the whole interface between English and Dutch with one click.') }}</p>
        </article>
    </section>
@endsection
