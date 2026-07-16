<x-portfolio-layout :title="__('layout/header.about_title')" :description="__('layout/header.about_desc')" :styles="['resources/css/pages/about-me.css']">

    {{-- About Me --}}
    <section id="about-me" class="portfolio-section" style="padding-top: var(--sp-section)">
        <h2>{!! __('home/about-me.title') !!}</h2>
        <div class="about-me-content">
            <div class="about-me-card">
                <h3>{!! __('home/about-me.card1_title') !!}</h3>
                <p>{!! __('home/about-me.card1_text') !!}</p>
            </div>
            <div class="about-me-card">
                <h3>{!! __('home/about-me.card2_title') !!}</h3>
                <p>{!! __('home/about-me.card2_text') !!}</p>
            </div>
            <div class="about-me-card">
                <h3>{!! __('home/about-me.card3_title') !!}</h3>
                <p>{!! __('home/about-me.card3_text') !!}</p>
            </div>
            <div class="about-me-card">
                <h3>{!! __('home/about-me.card4_title') !!}</h3>
                <p>{!! __('home/about-me.card4_text') !!}</p>
            </div>
            <div class="about-me-card">
                <h3>{!! __('home/about-me.card5_title') !!}</h3>
                <p>{!! __('home/about-me.card5_text') !!}</p>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <section id="about-me-stats" class="portfolio-section">
        <h2>{{ __('home/stats.title') }}</h2>
        <article class="about-me-stats-cards">
            <x-portfolio.stats-card :value="__('home/stats.card1_title')" :text="__('home/stats.card1_text')" />
            <x-portfolio.stats-card :value="__('home/stats.card2_title')" :text="__('home/stats.card2_text')" />
            <x-portfolio.stats-card :value="(now()->year - 2022).'+'" :text="__('home/stats.card3_text')" />
            <x-portfolio.stats-card :value="__('home/stats.card4_title')" :text="__('home/stats.card4_text')" />
            <x-portfolio.stats-card :value="(int) \Carbon\Carbon::parse('2006-10-05')->diffInYears(now())" :text="__('home/stats.card5_text')" />
            <x-portfolio.stats-card :value="__('home/stats.card6_title')" :text="__('home/stats.card6_text')" value-id="elo" />
            <x-portfolio.stats-card :value="__('home/stats.card7_title')" :text="__('home/stats.card7_text')" />
            <x-portfolio.stats-card :value="__('home/stats.card8_title')" :text="__('home/stats.card8_text')" />
            <x-portfolio.stats-card :value="__('home/stats.card9_title')" :text="__('home/stats.card9_text')" />
            <x-portfolio.stats-card :value="__('home/stats.card10_title')" :text="__('home/stats.card10_text')" value-id="github-repos" />
            <x-portfolio.stats-card :value="__('home/stats.card11_title')" :text="__('home/stats.card11_text')" />
        </article>
    </section>

</x-portfolio-layout>
