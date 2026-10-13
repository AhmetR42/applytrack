@extends('layouts.app')

@section('title', __('New application'))

@section('content')
    <div class="page-header">
        <h1>{{ __('New application') }}</h1>
    </div>

    <form method="POST" action="{{ route('applications.store') }}" class="card form">
        @include('applications._form')

        <div class="form__actions">
            <a href="{{ route('applications.index') }}" class="btn btn--ghost">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn--primary">{{ __('Save application') }}</button>
        </div>
    </form>
@endsection
