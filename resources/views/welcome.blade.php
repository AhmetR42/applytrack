@extends('layouts.app')

@section('title', __('Track your internship applications'))

@section('content')
    <section class="hero">
        <div>
            <p class="eyebrow">{{ __('For students looking for an internship') }}</p>
            <h1 class="hero__title">{{ __('Every application,') }} <span class="accent">{{ __('one clear overview.') }}</span></h1>
            <p class="hero__lead">{{ __('ApplyTrack keeps all your internship applications in one place. See where you applied, when your interviews are, and how often companies respond.') }}</p>
            <div class="hero__actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn--primary">{{ __('Go to dashboard') }} →</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn--primary">{{ __('Create a free account') }} →</a>
                    <a href="{{ route('login') }}" class="btn btn--ghost">{{ __('Log in') }}</a>
                @endauth
            </div>
        </div>

        <div class="stack" aria-hidden="true">
            <div class="stack__tile">
                <strong>Tulip Labs</strong>
                <small>Backend Developer Intern</small><br>
                <x-status-badge :status="\App\Enums\ApplicationStatus::Applied" />
            </div>
            <div class="stack__tile">
                <strong>Harbor Systems</strong>
                <small>Laravel Developer Intern</small><br>
                <x-status-badge :status="\App\Enums\ApplicationStatus::Interview" />
            </div>
            <div class="stack__tile">
                <strong>Windmill Software</strong>
                <small>Software Developer Intern</small><br>
                <x-status-badge :status="\App\Enums\ApplicationStatus::Offer" />
            </div>
            <span class="stack__square"></span>
        </div>
    </section>

    <section class="features">
        <article class="feature">
            <span class="feature__num">01</span>
            <h2>{{ __('Status pipeline') }}</h2>
            <p>{{ __('Move each application from wishlist to applied, interview and offer. Every change is saved in a timeline.') }}</p>
        </article>
        <article class="feature">
            <span class="feature__num">02</span>
            <h2>{{ __('Insights') }}</h2>
            <p>{{ __('See your response rate, applications per week and upcoming interviews at a glance.') }}</p>
        </article>
        <article class="feature">
            <span class="feature__num">03</span>
            <h2>{{ __('English and Dutch') }}</h2>
            <p>{{ __('Switch the whole interface between English and Dutch with one click.') }}</p>
        </article>
    </section>
@endsection
