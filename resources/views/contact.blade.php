<x-portfolio-layout :title="__('layout/header.contact_title')" :description="__('layout/header.contact_desc')" :styles="['resources/css/pages/contact.css']">

    @php
        $locale = app()->getLocale();

        $email = config('portfolio.contact.email');
        $phone = config('portfolio.contact.phone');

        /* Text glyphs in rings, same family as the footer's social marks, so
           four channel rows do not drag in an icon set. A blank value drops
           the row rather than shipping a placeholder. */
        $channels = collect([
            [
                'key' => 'email',
                'color' => '#FACC15',
                'glyph' => '✉',
                'value' => $email,
                'href' => 'mailto:'.$email,
                'cta' => __('pages/contact.write_to_me'),
                'copy' => $email,
            ],
            [
                'key' => 'linkedin',
                'color' => '#60A5FA',
                'glyph' => 'in',
                'value' => config('portfolio.contact.linkedin_handle'),
                'href' => config('portfolio.social.linkedin'),
                'cta' => __('pages/contact.open_profile'),
                'copy' => null,
            ],
            [
                'key' => 'github',
                'color' => '#818CF8',
                'glyph' => '☍',
                'value' => config('portfolio.contact.github_handle'),
                'href' => config('portfolio.social.github'),
                'cta' => __('pages/contact.open_profile'),
                'copy' => null,
            ],
            [
                'key' => 'phone',
                'color' => '#34D399',
                'glyph' => '☎',
                'value' => $phone,
                'href' => null,
                'cta' => null,
                'copy' => $phone,
            ],
        ])->filter(fn (array $channel) => $channel['value'] !== '');
    @endphp

    {{-- Hero --}}
    <x-portfolio.dock-hero
        :eyebrow="\App\Models\Setting::text('contact_hero_suptitle', $locale)"
        :title="\App\Models\Setting::text('contact_hero_title', $locale)"
        :roles="\App\Models\Setting::list('contact_hero_roles', $locale)"
        :tags="__('pages/contact.hero_tags')"
        :wordmark="__('pages/contact.hero_wordmark')"
        :photo="config('portfolio.hero_images.contact')"
        photo-alt=""
        photo-position="50% 25%"
    />

    {{-- 1 · Direct channels — the borderless listing pattern, same as
         projects and the toolbox. No fade-up: the hero leaves this
         section's top edge inside the first viewport. --}}
    <section id="contact-channels" class="portfolio-section portfolio-section--no-reveal">
        <x-portfolio.section-head
            :ghost="__('pages/contact.ways_head_ghost')"
            :eyebrow="__('pages/contact.ways_head_eyebrow')"
            :title="__('pages/contact.ways_head_title')"
            :note="__('pages/contact.ways_head_note')"
        />
        <div class="ct-ways">
            @foreach ($channels as $channel)
                <div class="ct-way" style="--bc: {{ $channel['color'] }}">
                    <p class="ct-way-k">
                        <span class="ct-way-ico" aria-hidden="true">{{ $channel['glyph'] }}</span>
                        {{ __('pages/contact.way_'.$channel['key'].'_label') }}
                    </p>
                    <div class="ct-way-main">
                        <p class="ct-way-v">{{ $channel['value'] }}</p>
                        <p class="ct-way-n">{{ __('pages/contact.way_'.$channel['key'].'_note') }}</p>
                    </div>
                    <div class="ct-way-acts">
                        @if ($channel['copy'])
                            <x-portfolio.copy-button :value="$channel['copy']" />
                        @endif
                        @if ($channel['href'] && $channel['cta'])
                            @if ($channel['key'] === 'email')
                                <x-portfolio.pill-button :href="$channel['href']" variant="gold" size="sm">{{ $channel['cta'] }}</x-portfolio.pill-button>
                            @else
                                <x-portfolio.pill-button :href="$channel['href']" variant="ghost" size="sm" target="_blank" rel="noopener noreferrer">{{ $channel['cta'] }}</x-portfolio.pill-button>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 2 · Form --}}
    <section id="contact-form" class="portfolio-section">
        <x-portfolio.section-head
            :ghost="__('pages/contact.form_head_ghost')"
            :eyebrow="__('pages/contact.form_head_eyebrow')"
            :title="__('pages/contact.form_head_title')"
            :note="__('pages/contact.form_head_note')"
        />
        <div class="ct-formwrap">
            <livewire:contact-form />

            <aside class="ct-rail">
                <h3>{{ __('pages/contact.rail_title') }}</h3>
                <div class="ct-rail-list">
                    @foreach (__('pages/contact.rail_items') as $item)
                        <div class="ct-rail-item">
                            <span class="ct-rail-n" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <p>{!! $item !!}</p>
                        </div>
                    @endforeach
                </div>
                <p class="ct-rail-note">{{ __('pages/contact.rail_note') }}</p>
            </aside>
        </div>
    </section>

    {{-- 3 · What happens next --}}
    <section id="contact-steps" class="portfolio-section">
        <x-portfolio.section-head
            :ghost="__('pages/contact.steps_head_ghost')"
            :eyebrow="__('pages/contact.steps_head_eyebrow')"
            :title="__('pages/contact.steps_head_title')"
            :note="__('pages/contact.steps_head_note')"
        />
        <div class="ct-steps">
            @foreach (__('pages/contact.steps') as $step)
                <div class="ct-step">
                    <div class="ct-step-top">
                        <span class="ct-step-n">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="ct-step-t">{{ $step['when'] }}</span>
                    </div>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['body'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 4 · FAQ --}}
    <section id="contact-faq" class="portfolio-section">
        <x-portfolio.section-head
            :ghost="__('pages/contact.faq_head_ghost')"
            :eyebrow="__('pages/contact.faq_head_eyebrow')"
            :title="__('pages/contact.faq_head_title')"
            :note="__('pages/contact.faq_head_note')"
        />
        <div class="ct-faq">
            @foreach (__('pages/contact.faq') as $qa)
                <div class="ct-qa">
                    <h3>{{ $qa['q'] }}</h3>
                    <p>{!! $qa['a'] !!}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- The consent line's only escape hatch. Three paragraphs do not earn a
         route, and the reader is mid-form. --}}
    <dialog class="ct-privacy" id="ct-privacy">
        <h3>{{ __('pages/contact.privacy_title') }}</h3>
        @foreach (__('pages/contact.privacy_body') as $paragraph)
            <p>{{ $paragraph }}</p>
        @endforeach
        <div class="ct-privacy-foot">
            <x-portfolio.pill-button variant="ghost" size="sm" onclick="this.closest('dialog').close()">{{ __('pages/contact.privacy_close') }}</x-portfolio.pill-button>
        </div>
    </dialog>

</x-portfolio-layout>
