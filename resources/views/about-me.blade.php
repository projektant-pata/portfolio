<x-portfolio-layout :title="__('layout/header.about_title')" :description="__('layout/header.about_desc')" :styles="['resources/css/pages/about-me.css']">

    @php $locale = app()->getLocale(); @endphp

    {{-- About Me --}}
    <section id="about-me" class="portfolio-section" style="padding-top: var(--sp-section)">
        <h2>{!! \App\Models\Setting::text('about_title', $locale) !!}</h2>
        <div class="about-me-content">
            @foreach ($aboutCards as $card)
                <div class="about-me-card">
                    <h3>{!! $card->getTranslation('title', $locale) !!}</h3>
                    <p>{!! $card->getTranslation('text', $locale) !!}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Stats --}}
    <section id="about-me-stats" class="portfolio-section">
        <h2>{{ \App\Models\Setting::text('stats_title', $locale) }}</h2>
        <article class="about-me-stats-cards">
            @foreach ($stats as $stat)
                <x-portfolio.stats-card :value="$stat->displayValue($locale)" :text="$stat->getTranslation('text', $locale)" :value-id="$stat->value_id" />
            @endforeach
        </article>
    </section>

</x-portfolio-layout>
