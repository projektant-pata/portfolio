<x-portfolio-layout :title="__('layout/header.about_title')" :description="__('layout/header.about_desc')">

    {{-- About Me --}}
    <section id="about-me" class="portfolio-section" style="padding-top: var(--sp-section)">
        <h2>{!! __('home/about-me.title') !!}</h2>
        <div id="about-me-content">
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
        <article id="about-me-stats-cards">
            <div class="stats-cards-card">
                <h3><span>{{ __('home/stats.card1_title') }}</span></h3>
                <p>{{ __('home/stats.card1_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ __('home/stats.card2_title') }}</span></h3>
                <p>{{ __('home/stats.card2_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ now()->year - 2022 }}+</span></h3>
                <p>{{ __('home/stats.card3_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ __('home/stats.card4_title') }}</span></h3>
                <p>{{ __('home/stats.card4_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ (int) \Carbon\Carbon::parse('2006-10-05')->diffInYears(now()) }}</span></h3>
                <p>{{ __('home/stats.card5_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span id="elo">{{ __('home/stats.card6_title') }}</span></h3>
                <p>{{ __('home/stats.card6_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ __('home/stats.card7_title') }}</span></h3>
                <p>{{ __('home/stats.card7_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ __('home/stats.card8_title') }}</span></h3>
                <p>{{ __('home/stats.card8_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ __('home/stats.card9_title') }}</span></h3>
                <p>{{ __('home/stats.card9_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span id="github-repos">{{ __('home/stats.card10_title') }}</span></h3>
                <p>{{ __('home/stats.card10_text') }}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{ __('home/stats.card11_title') }}</span></h3>
                <p>{{ __('home/stats.card11_text') }}</p>
            </div>
        </article>
    </section>

</x-portfolio-layout>
