@extends('layouts.app')

@section('title', __('Edit application'))

@section('content')
    <div class="page-header">
        <h1>{{ __('Edit application') }}</h1>
    </div>

    <form method="POST" action="{{ route('applications.update', $application) }}" class="card form">
        @method('PUT')
        @include('applications._form')

        <div class="form__actions">
            <a href="{{ route('applications.show', $application) }}" class="btn btn--ghost">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn--primary">{{ __('Save changes') }}</button>
        </div>
    </form>
@endsection
