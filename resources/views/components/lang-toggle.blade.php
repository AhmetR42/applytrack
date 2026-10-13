@php($current = app()->getLocale())

<form method="POST" action="{{ route('locale') }}" class="lang-toggle" data-locale="{{ $current }}" data-lang-toggle>
    @csrf
    <span class="lang-toggle__thumb" aria-hidden="true"></span>
    <button type="submit" name="locale" value="en" class="lang-toggle__option" aria-pressed="{{ $current === 'en' ? 'true' : 'false' }}" aria-label="English">EN</button>
    <button type="submit" name="locale" value="nl" class="lang-toggle__option" aria-pressed="{{ $current === 'nl' ? 'true' : 'false' }}" aria-label="Nederlands">NL</button>
</form>
