@extends('layouts.app')

@section('title', __('Applications'))

@php
    $sortLink = function (string $column) use ($sort, $direction) {
        $next = $sort === $column && $direction === 'asc' ? 'desc' : 'asc';

        return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $next, 'page' => null]);
    };
    $arrow = fn (string $column) => $sort === $column ? ($direction === 'asc' ? '↑' : '↓') : '';
@endphp

@section('content')
    <div class="page-header">
        <div>
            <h1>{{ __('Applications') }}</h1>
            <p class="muted">{{ trans_choice(':count application|:count applications', $applications->total()) }}</p>
        </div>
        <div class="page-header__actions">
            <a href="{{ route('applications.export') }}" class="btn btn--ghost">{{ __('Export CSV') }}</a>
            <a href="{{ route('applications.create') }}" class="btn btn--primary">+ {{ __('New application') }}</a>
        </div>
    </div>

    <form method="GET" class="filters card">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search company, position or city') }}" aria-label="{{ __('Search') }}">
        <select name="status" aria-label="{{ __('Status') }}" data-autosubmit>
            <option value="">{{ __('All statuses') }}</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn--ghost">{{ __('Filter') }}</button>
    </form>

    @if ($applications->isEmpty())
        <div class="card empty">
            <p>{{ __('No applications found.') }}</p>
            <a href="{{ route('applications.create') }}" class="btn btn--primary">{{ __('Add your first application') }}</a>
        </div>
    @else
        <div class="card table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th><a href="{{ $sortLink('company') }}">{{ __('Company') }} {{ $arrow('company') }}</a></th>
                        <th class="hide-xs"><a href="{{ $sortLink('position') }}">{{ __('Position') }} {{ $arrow('position') }}</a></th>
                        <th class="hide-sm">{{ __('Location') }}</th>
                        <th class="hide-xs"><a href="{{ $sortLink('applied_on') }}">{{ __('Applied on') }} {{ $arrow('applied_on') }}</a></th>
                        <th><a href="{{ $sortLink('status') }}">{{ __('Status') }} {{ $arrow('status') }}</a></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($applications as $application)
                        <tr>
                            <td>
                                <a href="{{ route('applications.show', $application) }}" class="strong">{{ $application->company }}</a>
                                <span class="show-xs muted">{{ $application->position }}</span>
                            </td>
                            <td class="hide-xs">{{ $application->position }}</td>
                            <td class="hide-sm muted">{{ $application->location ?? '—' }}</td>
                            <td class="hide-xs muted">{{ $application->applied_on?->translatedFormat('j M Y') ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('applications.status', $application) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="status-select status-select--{{ $application->status->value }}" aria-label="{{ __('Status') }}" data-autosubmit>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->value }}" @selected($application->status === $status)>{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                    <noscript><button type="submit" class="btn btn--ghost btn--sm">{{ __('Save') }}</button></noscript>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $applications->links('pagination') }}
    @endif
@endsection
