@php
    $assistantName = (string) config('assistant.branding.name');
    $assistantTagline = (string) config('assistant.branding.tagline');
@endphp

<div class="assistant-widget" data-assistant data-endpoint="{{ route('assistant.chat') }}">
    <section class="assistant-panel" id="assistant-panel" data-assistant-panel hidden aria-label="{{ $assistantName }}">
        <header class="assistant-panel-header">
            <div class="assistant-identity">
                <span class="assistant-avatar" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><rect x="4" y="7" width="16" height="12" rx="3"></rect><path d="M12 3v4M9 13h.01M15 13h.01"></path></svg>
                </span>
                <span class="min-w-0">
                    <strong class="block truncate text-sm text-white">{{ $assistantName }}</strong>
                    <small class="block truncate text-xs text-brand-100">{{ $assistantTagline }}</small>
                </span>
            </div>
            <button type="button" class="assistant-panel-close" data-assistant-toggle aria-label="{{ __('Close assistant') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"></path></svg>
            </button>
        </header>

        <div class="assistant-thread" data-assistant-thread role="log" aria-live="polite">
            <div class="assistant-message assistant-message-bot">
                <p>{{ __('Hello. I can explain registration, e-mail and phone verification, the business profile, office hours, and how approved opportunities open after a profile is saved.') }}</p>
            </div>
        </div>

        <div class="assistant-suggestions">
            <button type="button" data-assistant-suggestion>{{ __('How do I register as an investor?') }}</button>
            <button type="button" data-assistant-suggestion>{{ __('Is the office open now?') }}</button>
            <button type="button" data-assistant-suggestion>{{ __('Are opportunity lists public?') }}</button>
            <button type="button" data-assistant-suggestion>{{ __('What sectors can I invest in?') }}</button>
        </div>

        <form class="assistant-form" data-assistant-form>
            <label class="min-w-0 flex-1">
                <span class="sr-only">{{ __('Ask the assistant') }}</span>
                <input type="text" name="message" data-assistant-input autocomplete="off" maxlength="{{ (int) config('assistant.guardrails.max_question_length', 1000) }}" required placeholder="{{ __('Ask me for assistance') }}">
            </label>
            <button type="submit" class="assistant-send" data-assistant-send aria-label="{{ __('Send') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4z"></path></svg>
            </button>
        </form>
        <p class="assistant-disclaimer">{{ __('Answers use verified platform information. This assistant does not give personalised investment advice.') }}</p>
    </section>

    <div class="assistant-dock">
        <p class="assistant-peek">{{ __('Ask GIPA') }}</p>
        <button type="button" class="assistant-launcher" data-assistant-toggle aria-expanded="false" aria-controls="assistant-panel" aria-label="{{ __('Open the :name', ['name' => $assistantName]) }}">
            <svg class="assistant-launcher-open" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H8l-5 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            <svg class="assistant-launcher-close" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"></path></svg>
        </button>
    </div>
</div>
