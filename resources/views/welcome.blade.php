<x-portfolio-layout :title="__('layout/header.home_title')" :description="__('layout/header.home_desc')" :styles="['resources/css/pages/index.css']">

    @php $locale = app()->getLocale(); @endphp
    @php $heroRoles = \App\Models\Setting::list('hero_roles', $locale); @endphp

    {{-- Hero --}}
    <section class="hero-page portfolio-section">
        <article class="hero-page-text">
            <p class="hero-suptitle">{{ \App\Models\Setting::text('hero_suptitle', $locale) }}</p>
            <h1>{!! \App\Models\Setting::text('hero_title', $locale) !!}</h1>
            <h4 class="underh1">
                <span id="hero-rotator" data-roles='@json($heroRoles)' aria-live="polite">{!! $heroRoles[0] ?? '' !!}</span><span class="hero-caret" aria-hidden="true"></span>
            </h4>
        </article>
        <article class="hero-page-image">
            <img src="{{ asset('images/id-photo-portrait-businessman-suit-260nw-1505360618 1.png') }}" alt="hero">
        </article>
    </section>

    {{-- Stats --}}
    <section id="stats" class="portfolio-section">
        <h2>{{ \App\Models\Setting::text('stats_title', $locale) }}</h2>
        <article class="stats-cards">
            @foreach ($stats as $stat)
                <x-portfolio.stats-card :value="$stat->displayValue($locale)" :text="$stat->getTranslation('text', $locale)" :value-id="$stat->value_id" />
            @endforeach
        </article>
    </section>

    {{-- Work & Life --}}
    <section class="work portfolio-section">
        <h2>{{ __('home/experience.title_home') }}</h2>
        <article class="work-top">
            <button type="button" id="work-top-btn-work" class="work-top-btn" aria-pressed="false">
                <h4>{{ __('home/experience.title_work') }}</h4>
            </button>
            <button type="button" id="work-top-btn-life" class="work-top-btn active" aria-pressed="true">
                <h4>{{ __('home/experience.title_life') }}</h4>
            </button>
        </article>
        <article class="work-bot">
            <div class="work-bot-line"></div>

            <div id="work-bot-content-life" class="work-bot-content">
                @foreach ($lifeExperiences as $experience)
                    <x-portfolio.experience-row :experience="$experience" :locale="$locale" />
                @endforeach
            </div>

            <div id="work-bot-content-work" class="work-bot-content" style="display: none">
                @foreach ($workExperiences as $experience)
                    <x-portfolio.experience-row :experience="$experience" :locale="$locale" />
                @endforeach
            </div>
        </article>
    </section>

    {{-- Projects --}}
    <section id="projects" class="portfolio-section">
        <h2>{{ __('home/projects.title') }}</h2>
        @foreach ($featuredProjects as $index => $project)
            <x-portfolio.project-row :project="$project" :locale="$locale" :reverse="$index % 2 !== 0" />
        @endforeach
    </section>

    {{-- Tools --}}
    <section id="tools" class="portfolio-section">
        <h2>{{ \App\Models\Setting::text('tools_title', $locale) }}</h2>
        <article class="tools-row">
            <div class="tools-row-card">
                <img src="{{ asset('images/tools/laravel.png') }}" alt="Laravel">
                <h4>Laravel</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{ asset('images/tools/springboot.png') }}" alt="Spring Boot">
                <h4>Spring Boot</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{ asset('images/tools/typescript.png') }}" alt="TypeScript">
                <h4>TypeScript</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{ asset('images/tools/docker.png') }}" alt="Docker">
                <h4>Docker</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{ asset('images/tools/postgresql.png') }}" alt="PostgreSQL">
                <h4>PostgreSQL</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{ asset('images/tools/githubw.png') }}" alt="GitHub" class="tool-github-dark">
                <img src="{{ asset('images/tools/githubb.png') }}" alt="GitHub" class="tool-github-light">
                <h4>GitHub</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{ asset('images/tools/figma.png') }}" alt="Figma">
                <h4>Figma</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{ asset('images/tools/vscode.png') }}" alt="VS Code">
                <h4>VS Code</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{ asset('images/tools/arch.png') }}" alt="Arch Linux">
                <h4>Arch Linux</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{ asset('images/tools/claudecode.png') }}" alt="Claude Code">
                <h4>Claude Code</h4>
            </div>
        </article>
    </section>

    {{-- Reviews --}}
    <section id="reviews" class="portfolio-section">
        <h2>{{ \App\Models\Setting::text('reviews_title', $locale) }}</h2>
        <article class="reviews-row">
            @foreach ($reviews as $review)
                <div class="reviews-row-card">
                    <p>{{ $review->getTranslation('text', $locale) }}</p>
                    <div class="reviews-row-card-text">
                        <span><p>{{ $review->name }}</p></span>
                        <p class="mini"> - {{ $review->getTranslation('position', $locale) }}</p>
                    </div>
                </div>
            @endforeach
        </article>
    </section>

</x-portfolio-layout>
