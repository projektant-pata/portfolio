<?php

use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * The contact page's form. Five fields, one consent box, and three outcomes:
 * the form itself, a success panel in its place, or a failure panel that
 * keeps every character the visitor typed.
 *
 * Nothing is persisted — the message is mailed to the owner's inbox and then
 * exists only there, which is what the page promises the reader.
 */
new class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $topic = 'freelance';

    public string $timeline = '';

    public string $message = '';

    public bool $consent = false;

    /** Honeypot. A real visitor never sees this field, so anything in it is a bot. */
    public string $website = '';

    /** Unix seconds of the first render, used as the "filled in too fast" gate. */
    public int $startedAt = 0;

    /** Success and failure are mutually exclusive; both replace the form. */
    public bool $sent = false;

    public bool $failed = false;

    public function mount(): void
    {
        $this->startedAt = time();
    }

    /** @return array<string, array<int, string>> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'topic' => ['required', 'string', 'in:'.implode(',', array_keys($this->topics()))],
            'timeline' => ['nullable', 'string', 'max:190'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
            'consent' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'name.required' => __('pages/contact.err_name'),
            'email.required' => __('pages/contact.err_email'),
            'email.email' => __('pages/contact.err_email'),
            'message.required' => __('pages/contact.err_message'),
            'message.min' => __('pages/contact.err_message'),
            'consent.accepted' => __('pages/contact.err_consent'),
        ];
    }

    /**
     * Topic keys are stable across locales so the inbox subject always reads
     * in English, whichever language the visitor wrote in.
     *
     * @return array<string, string>
     */
    public function topics(): array
    {
        return [
            'freelance' => __('pages/contact.topic_freelance'),
            'job' => __('pages/contact.topic_job'),
            'hackathon' => __('pages/contact.topic_hackathon'),
            'question' => __('pages/contact.topic_question'),
            'other' => __('pages/contact.topic_other'),
        ];
    }

    public function submit(): void
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            /* The browser owns focus management; the server only says when
               there is somewhere to jump to. */
            $this->dispatch('ct-invalid');

            throw $e;
        }

        /* Two silent traps, both answered with the success panel: telling a
           bot why it was rejected is how it learns to get through. */
        if ($this->website !== '' || time() - $this->startedAt < 3) {
            $this->sent = true;

            return;
        }

        $key = 'contact-form:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->failed = true;

            return;
        }

        RateLimiter::hit($key, 3600);

        try {
            Mail::to(config('portfolio.contact.inbox'))->send(new ContactMessage([
                'name' => $this->name,
                'email' => $this->email,
                'topic' => __('pages/contact.topic_'.$this->topic, [], 'en'),
                'timeline' => $this->timeline,
                'message' => $this->message,
                'locale' => app()->getLocale(),
            ]));

            $this->sent = true;
        } catch (\Throwable $e) {
            report($e);
            $this->failed = true;
        }
    }

    /** Failure keeps the typed text, so the retry is one button, not a re-type. */
    public function retry(): void
    {
        $this->failed = false;
    }
};
?>

@php
    $inbox = config('portfolio.contact.email');
@endphp

<div class="ct-formstate">
    @if ($sent)
        <div class="ct-state ct-state--ok" role="status">
            <p class="ct-state-k">{{ __('pages/contact.form_head_eyebrow') }}</p>
            <h4>{{ __('pages/contact.success_title') }}</h4>
            <p>{!! __('pages/contact.success_body', ['email' => '<a href="mailto:'.$inbox.'">'.$inbox.'</a>']) !!}</p>
        </div>
    @elseif ($failed)
        <div class="ct-state ct-state--err" role="alert">
            <p class="ct-state-k">{{ __('pages/contact.form_head_eyebrow') }}</p>
            <h4>{{ __('pages/contact.failure_title') }}</h4>
            <p>{!! __('pages/contact.failure_body', ['email' => '<a href="mailto:'.$inbox.'">'.$inbox.'</a>']) !!}</p>
            <div class="ct-state-acts">
                <x-portfolio.copy-button :value="$message" :label="__('pages/contact.copy_message')" />
                <x-portfolio.pill-button variant="gold" size="sm" wire:click="retry">{{ __('pages/contact.send') }}</x-portfolio.pill-button>
            </div>
        </div>
    @else
        <form
            class="ct-form"
            wire:submit="submit"
            novalidate
            x-data="{ count: 0 }"
            x-init="count = $refs.message.value.length"
            x-on:ct-invalid.window="$nextTick(() => $el.querySelector('[aria-invalid=&quot;true&quot;]')?.focus())"
        >
            {{-- Bot bait: off-screen rather than display:none, which some
                 form fillers skip, and never announced or tab-reachable. --}}
            <div aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden">
                <label for="ct-website">Website</label>
                <input id="ct-website" type="text" tabindex="-1" autocomplete="off" wire:model="website">
            </div>

            <div class="ct-row">
                <div @class(['ct-field', 'ct-field--err' => $errors->has('name')])>
                    <label class="ct-label" for="ct-name">
                        {{ __('pages/contact.name_label') }} <span>{{ __('pages/contact.field_required') }}</span>
                    </label>
                    <input
                        class="ct-input"
                        id="ct-name"
                        type="text"
                        autocomplete="name"
                        placeholder="{{ __('pages/contact.name_placeholder') }}"
                        wire:model="name"
                        @if ($errors->has('name')) aria-invalid="true" aria-describedby="ct-name-err" @endif
                    >
                    @error('name')
                        <p class="ct-err" id="ct-name-err">{{ $message }}</p>
                    @enderror
                </div>

                <div @class(['ct-field', 'ct-field--err' => $errors->has('email')])>
                    <label class="ct-label" for="ct-email">
                        {{ __('pages/contact.email_label') }} <span>{{ __('pages/contact.field_required') }}</span>
                    </label>
                    <input
                        class="ct-input"
                        id="ct-email"
                        type="email"
                        autocomplete="email"
                        placeholder="{{ __('pages/contact.email_placeholder') }}"
                        wire:model="email"
                        @if ($errors->has('email')) aria-invalid="true" aria-describedby="ct-email-err" @endif
                    >
                    @error('email')
                        <p class="ct-err" id="ct-email-err">{{ $message }}</p>
                    @else
                        <p class="ct-help">{{ __('pages/contact.email_help') }}</p>
                    @enderror
                </div>
            </div>

            <div class="ct-row">
                <div class="ct-field">
                    <label class="ct-label" for="ct-topic">{{ __('pages/contact.topic_label') }}</label>
                    <select class="ct-select" id="ct-topic" wire:model="topic">
                        @foreach ($this->topics() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ct-field">
                    <label class="ct-label" for="ct-timeline">
                        {{ __('pages/contact.timeline_label') }} <span>{{ __('pages/contact.field_optional') }}</span>
                    </label>
                    <input
                        class="ct-input"
                        id="ct-timeline"
                        type="text"
                        placeholder="{{ __('pages/contact.timeline_placeholder') }}"
                        wire:model="timeline"
                    >
                    <p class="ct-help">{{ __('pages/contact.timeline_help') }}</p>
                </div>
            </div>

            <div @class(['ct-field', 'ct-field--err' => $errors->has('message')])>
                <label class="ct-label" for="ct-message">
                    {{ __('pages/contact.message_label') }} <span>{{ __('pages/contact.field_required') }}</span>
                </label>
                <textarea
                    class="ct-textarea"
                    id="ct-message"
                    x-ref="message"
                    maxlength="2000"
                    placeholder="{{ __('pages/contact.message_placeholder') }}"
                    wire:model="message"
                    x-on:input="count = $el.value.length"
                    @if ($errors->has('message')) aria-invalid="true" aria-describedby="ct-message-err" @endif
                ></textarea>
                <div class="ct-count">
                    @error('message')
                        <p class="ct-err" id="ct-message-err">{{ $message }}</p>
                    @else
                        <p class="ct-help">{{ __('pages/contact.message_help') }}</p>
                    @enderror
                    <p class="ct-help" aria-hidden="true"><span x-text="count">0</span> / 2000</p>
                </div>
            </div>

            <div @class(['ct-field', 'ct-field--err' => $errors->has('consent')])>
                <label class="ct-check" for="ct-consent">
                    <input
                        type="checkbox"
                        id="ct-consent"
                        wire:model="consent"
                        @if ($errors->has('consent')) aria-invalid="true" aria-describedby="ct-consent-err" @endif
                    >
                    <span>{!! __('pages/contact.consent', [
                        'link' => '<button type="button" onclick="document.getElementById(\'ct-privacy\').showModal()">'.__('pages/contact.consent_link').'</button>',
                    ]) !!}</span>
                </label>
                @error('consent')
                    <p class="ct-err" id="ct-consent-err">{{ $message }}</p>
                @enderror
            </div>

            <div class="ct-form-foot">
                <p class="ct-form-fine">
                    {{ __('pages/contact.form_fine') }} <a href="mailto:{{ $inbox }}">{{ $inbox }}</a>
                </p>

                {{-- Active with empty fields on purpose: the form validates on
                     submit, it does not hold the button hostage. --}}
                <x-portfolio.pill-button
                    variant="gold"
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                >
                    <span wire:loading.remove wire:target="submit">{{ __('pages/contact.send') }}</span>
                    <span wire:loading wire:target="submit">{{ __('pages/contact.sending') }}</span>
                </x-portfolio.pill-button>
            </div>
        </form>
    @endif
</div>
