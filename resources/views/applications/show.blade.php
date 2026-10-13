@extends('layouts.app')

@section('title', $application->company)

@section('content')
    <a href="{{ route('applications.index') }}" class="back-link">← {{ __('All applications') }}</a>

    <div class="page-header">
        <div>
            <h1>{{ $application->company }}</h1>
            <p class="muted">{{ $application->position }}@if ($application->location) · {{ $application->location }}@endif</p>
        </div>
        <div class="page-header__actions">
            <a href="{{ route('applications.edit', $application) }}" class="btn btn--ghost">{{ __('Edit') }}</a>
            <form method="POST" action="{{ route('applications.destroy', $application) }}" data-confirm="{{ __('Delete this application?') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn--danger">{{ __('Delete') }}</button>
            </form>
        </div>
    </div>

    <div class="grid-2">
        <section class="card">
            <h2 class="card__title">{{ __('Details') }}</h2>
            <dl class="details">
                <dt>{{ __('Status') }}</dt>
                <dd><x-status-badge :status="$application->status" /></dd>

                <dt>{{ __('Applied on') }}</dt>
                <dd>{{ $application->applied_on?->translatedFormat('j F Y') ?? '—' }}</dd>

                <dt>{{ __('Interview at') }}</dt>
                <dd>{{ $application->interview_at?->translatedFormat('l j F Y, H:i') ?? '—' }}</dd>

                <dt>{{ __('Vacancy link') }}</dt>
                <dd>
                    @if ($application->url)
                        <a href="{{ $application->url }}" target="_blank" rel="noopener noreferrer">{{ parse_url($application->url, PHP_URL_HOST) ?? $application->url }} ↗</a>
                    @else
                        —
                    @endif
                </dd>

                <dt>{{ __('Contact email') }}</dt>
                <dd>
                    @if ($application->contact_email)
                        <a href="mailto:{{ $application->contact_email }}">{{ $application->contact_email }}</a>
                    @else
                        —
                    @endif
                </dd>
            </dl>

            @if ($application->notes)
                <h3 class="card__subtitle">{{ __('Notes') }}</h3>
                <p class="notes">{{ $application->notes }}</p>
            @endif
        </section>

        <section class="card">
            <h2 class="card__title">{{ __('Change status') }}</h2>
            <form method="POST" action="{{ route('applications.status', $application) }}" class="status-picker">
                @csrf
                @method('PATCH')
                @foreach (\App\Enums\ApplicationStatus::cases() as $status)
                    <button type="submit" name="status" value="{{ $status->value }}" @class(['chip', 'chip--'.$status->value, 'is-active' => $application->status === $status])>
                        {{ $status->label() }}
                    </button>
                @endforeach
            </form>

            <h2 class="card__title">{{ __('Timeline') }}</h2>
            <ol class="timeline">
                @foreach ($application->statusChanges as $change)
                    <li class="timeline__item">
                        <span class="dot dot--{{ $change->to_status->value }}"></span>
                        <div>
                            <strong>{{ $change->to_status->label() }}</strong>
                            <time class="muted" datetime="{{ $change->created_at->toIso8601String() }}">{{ $change->created_at->translatedFormat('j M Y, H:i') }}</time>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
@endsection
