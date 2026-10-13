@csrf

<div class="form-grid">
    <label class="field">
        <span>{{ __('Company') }} *</span>
        <input type="text" name="company" value="{{ old('company', $application->company) }}" required maxlength="120">
        @error('company') <small class="field__error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>{{ __('Position') }} *</span>
        <input type="text" name="position" value="{{ old('position', $application->position) }}" required maxlength="120">
        @error('position') <small class="field__error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>{{ __('Location') }}</span>
        <input type="text" name="location" value="{{ old('location', $application->location) }}" maxlength="120">
        @error('location') <small class="field__error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>{{ __('Status') }} *</span>
        <select name="status" required>
            @foreach (\App\Enums\ApplicationStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $application->status?->value) === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status') <small class="field__error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>{{ __('Applied on') }}</span>
        <input type="date" name="applied_on" value="{{ old('applied_on', $application->applied_on?->toDateString()) }}" max="{{ today()->toDateString() }}">
        @error('applied_on') <small class="field__error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>{{ __('Interview at') }}</span>
        <input type="datetime-local" name="interview_at" value="{{ old('interview_at', $application->interview_at?->format('Y-m-d\TH:i')) }}">
        @error('interview_at') <small class="field__error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>{{ __('Vacancy link') }}</span>
        <input type="url" name="url" value="{{ old('url', $application->url) }}" placeholder="https://">
        @error('url') <small class="field__error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
        <span>{{ __('Contact email') }}</span>
        <input type="email" name="contact_email" value="{{ old('contact_email', $application->contact_email) }}">
        @error('contact_email') <small class="field__error">{{ $message }}</small> @enderror
    </label>

    <label class="field field--wide">
        <span>{{ __('Notes') }}</span>
        <textarea name="notes" rows="5" maxlength="5000">{{ old('notes', $application->notes) }}</textarea>
        @error('notes') <small class="field__error">{{ $message }}</small> @enderror
    </label>
</div>
