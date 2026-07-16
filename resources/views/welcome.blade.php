<x-portfolio-layout :title="__('layout/header.home_title')" :description="__('layout/header.home_desc')" :styles="['resources/css/pages/index.css']">

    {{-- Hero --}}
    <section class="hero-page portfolio-section">
        <article class="hero-page-text">
            <p class="hero-suptitle">{{ __('home/hero.hero_suptitle') }}</p>
            <h1>{!! __('home/hero.hero_title') !!}</h1>
            <h4 class="underh1">{!! __('home/hero.hero_subtitle') !!}</h4>
        </article>
        <article class="hero-page-image">
            <img src="{{ asset('images/id-photo-portrait-businessman-suit-260nw-1505360618 1.png') }}" alt="hero">
        </article>
    </section>

    {{-- Stats --}}
    <section id="stats" class="portfolio-section">
        <h2>{{ __('home/stats.title') }}</h2>
        <article class="stats-cards">
            <x-portfolio.stats-card :value="__('home/stats.card1_title')" :text="__('home/stats.card1_text')" />
            <x-portfolio.stats-card :value="__('home/stats.card2_title')" :text="__('home/stats.card2_text')" />
            <x-portfolio.stats-card :value="(now()->year - 2022).'+'" :text="__('home/stats.card3_text')" />
            <x-portfolio.stats-card :value="__('home/stats.card4_title')" :text="__('home/stats.card4_text')" />
        </article>
    </section>

    @php $locale = app()->getLocale(); @endphp
    {{-- Work & Life --}}
    <section class="work portfolio-section">
        <h2>{{ __('home/experience.title_home') }}</h2>
        <article class="work-top">
            <div id="work-top-btn-work" class="work-top-btn">
                <h4>{{ __('home/experience.title_work') }}</h4>
            </div>
            <div id="work-top-btn-life" class="work-top-btn active">
                <h4>{{ __('home/experience.title_life') }}</h4>
            </div>
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
        <article class="projects-row">
            <img src="{{ asset(__('home/projects.spsehub_img')) }}" alt="spse hub">
            <div class="projects-row-space"></div>
            <div class="projects-row-text">
                <h3>{{ __('home/projects.spsehub_title') }}</h3>
                <p>{{ __('home/projects.spsehub_text') }}</p>
                <div class="projects-row-text-links">
                    <a target="_blank" rel="noopener noreferrer" href="{{ __('home/projects.spsehub_web') }}">
                        <img src="{{ asset('images/projects/icons/web.webp') }}" alt="web">
                    </a>
                    <a target="_blank" rel="noopener noreferrer" href="{{ __('home/projects.spsehub_github') }}">
                        <img src="{{ asset('images/mobile/icons/github.webp') }}" alt="github">
                    </a>
                </div>
            </div>
        </article>
        <article class="projects-row">
            <div class="projects-row-text">
                <h3>{{ __('home/projects.usladovny_title') }}</h3>
                <p>{{ __('home/projects.usladovny_text') }}</p>
                <div class="projects-row-text-links">
                    <a target="_blank" rel="noopener noreferrer" href="{{ __('home/projects.usladovny_web') }}">
                        <img src="{{ asset('images/projects/icons/web.webp') }}" alt="web">
                    </a>
                </div>
            </div>
            <div class="projects-row-space"></div>
            <img src="{{ asset(__('home/projects.usladovny_img')) }}" alt="usladovny">
        </article>
    </section>

    {{-- Tools --}}
    <section id="tools" class="portfolio-section">
        <h2>{{ __('home/tools.title') }}</h2>
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
        <h2>{{ __('home/reviews.title') }}</h2>
        <article class="reviews-row">
            <div class="reviews-row-card">
                <p>{{ __('home/reviews.card1_text') }}</p>
                <div class="reviews-row-card-text">
                    <span><p>{{ __('home/reviews.card1_name') }}</p></span>
                    <p class="mini"> - {{ __('home/reviews.card1_position') }}</p>
                </div>
            </div>
            <div class="reviews-row-card">
                <p>{{ __('home/reviews.card2_text') }}</p>
                <div class="reviews-row-card-text">
                    <span><p>{{ __('home/reviews.card2_name') }}</p></span>
                    <p class="mini"> - {{ __('home/reviews.card2_position') }}</p>
                </div>
            </div>
            <div class="reviews-row-card">
                <p>{{ __('home/reviews.card3_text') }}</p>
                <div class="reviews-row-card-text">
                    <span><p>{{ __('home/reviews.card3_name') }}</p></span>
                    <p class="mini"> - {{ __('home/reviews.card3_position') }}</p>
                </div>
            </div>
        </article>
    </section>

</x-portfolio-layout>
