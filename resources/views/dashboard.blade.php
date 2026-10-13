@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
    <div class="page-header">
        <div>
            <h1>{{ __('Hi :name', ['name' => auth()->user()->name]) }} 👋</h1>
            <p class="muted">{{ __('Here is how your internship search is going.') }}</p>
        </div>
        <a href="{{ route('applications.create') }}" class="btn btn--primary">+ {{ __('New application') }}</a>
    </div>

    <section class="stats">
        <div class="card stat">
            <span class="stat__label">{{ __('Total') }}</span>
            <span class="stat__value" data-count="{{ $total }}">{{ $total }}</span>
        </div>
        <div class="card stat">
            <span class="stat__label">{{ __('Interviews') }}</span>
            <span class="stat__value" data-count="{{ $counts['interview'] }}">{{ $counts['interview'] }}</span>
        </div>
        <div class="card stat">
            <span class="stat__label">{{ __('Offers') }}</span>
            <span class="stat__value" data-count="{{ $counts['offer'] }}">{{ $counts['offer'] }}</span>
        </div>
        <div class="card stat">
            <span class="stat__label">{{ __('Response rate') }}</span>
            <span class="stat__value"><span data-count="{{ $responseRate }}">{{ $responseRate }}</span>%</span>
        </div>
    </section>

    <section class="card">
        <h2 class="card__title">{{ __('Pipeline') }}</h2>
        @if ($total === 0)
            <p class="muted">{{ __('No applications yet. Add your first one to see your pipeline.') }}</p>
        @else
            <div class="pipeline" role="img" aria-label="{{ __('Pipeline') }}">
                @foreach (\App\Enums\ApplicationStatus::cases() as $status)
                    @if ($counts[$status->value] > 0)
                        <span class="pipeline__segment pipeline__segment--{{ $status->value }}" style="--share: {{ $counts[$status->value] / $total }}"></span>
                    @endif
                @endforeach
            </div>
            <ul class="legend">
                @foreach (\App\Enums\ApplicationStatus::cases() as $status)
                    <li>
                        <a href="{{ route('applications.index', ['status' => $status->value]) }}">
                            <span class="dot dot--{{ $status->value }}"></span>{{ $status->label() }} <strong>{{ $counts[$status->value] }}</strong>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <div class="grid-2">
        <section class="card">
            <h2 class="card__title">{{ __('Applications per week') }}</h2>
            @php($max = max(1, max(array_column($weekly, 'count'))))
            <div class="bars">
                @foreach ($weekly as $week)
                    <div class="bars__col" title="{{ $week['count'] }}">
                        <span class="bars__value">{{ $week['count'] }}</span>
                        <span class="bars__bar" style="--height: {{ $week['count'] / $max }}"></span>
                        <span class="bars__label">{{ $week['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card">
            <h2 class="card__title">{{ __('Upcoming interviews') }}</h2>
            @forelse ($upcoming as $application)
                <a href="{{ route('applications.show', $application) }}" class="list-row">
                    <span>
                        <strong>{{ $application->company }}</strong>
                        <span class="muted">{{ $application->position }}</span>
                    </span>
                    <time datetime="{{ $application->interview_at->toIso8601String() }}">{{ $application->interview_at->translatedFormat('D j M, H:i') }}</time>
                </a>
            @empty
                <p class="muted">{{ __('No interviews planned.') }}</p>
            @endforelse
        </section>
    </div>

    <section class="card">
        <h2 class="card__title">{{ __('Recent activity') }}</h2>
        @forelse ($activity as $change)
            <div class="list-row">
                <span>
                    <strong>{{ $change->jobApplication->company }}</strong>
                    @if ($change->from_status)
                        <span class="muted">{{ __('moved from :from to :to', ['from' => $change->from_status->label(), 'to' => $change->to_status->label()]) }}</span>
                    @else
                        <span class="muted">{{ __('added as :status', ['status' => $change->to_status->label()]) }}</span>
                    @endif
                </span>
                <time datetime="{{ $change->created_at->toIso8601String() }}" class="muted">{{ $change->created_at->diffForHumans() }}</time>
            </div>
        @empty
            <p class="muted">{{ __('Nothing here yet.') }}</p>
        @endforelse
    </section>
@endsection
