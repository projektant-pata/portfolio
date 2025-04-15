@extends('base')

@section('title', __('header.home_title'))
@section('description', __('header.home_desc'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endsection

@section('scripts')
    <script src="{{asset('js/work.js')}}" defer></script>
@endsection

@section('content')
    <section id="hero-page">
        <article id="hero-page-text">
            <h3>{{ __('hero.hero_suptitle') }}</h3>
            <h1>{!! __('hero.hero_title') !!}</h1>
            <p id="underh1">{!! __('hero.hero_subtitle') !!}</p>
        </article>
        <article id="hero-page-image">
            <img src="{{ asset('images/id-photo-portrait-businessman-suit-260nw-1505360618 1.png') }}" alt="hero">
        </article>
    </section>
    <section id="stats">
        <h2>{{ __('stats.title') }}</h2>
        <article id="stats-cards">
            <div class="stats-cards-card">
                <h3><span>{{__('stats.card1_title')}}</span></h3>
                <p>{{__('stats.card1_text')}}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{__('stats.card2_title')}}</span></h3>
                <p>{{__('stats.card2_text')}}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{__('stats.card3_title')}}</span></h3>
                <p>{{__('stats.card3_text')}}</p>
            </div>
            <div class="stats-cards-card">
                <h3><span>{{__('stats.card4_title')}}</span></h3>
                <p>{{__('stats.card4_text')}}</p>
            </div>
        </article>
    </section>
    <section id="work">
        <h2>{{ __('experience.title_home') }}</h2>
        <article id="work-top">
            <div id="work-top-btn-work" class="work-top-btn">
                <h4>{{ __('experience.title_work') }}</h4>
            </div>
            <div id="work-top-btn-edu" class="work-top-btn active">
                <h4>{{ __('experience.title_edu') }}</h4>
            </div>
        </article>
        <article id="work-bot">
            <div id="work-bot-line"></div>
            <div id="work-bot-content-edu" class="work-bot-content">
                <a href="https://astro-pi.org/" target="_blank" class="work-bot-content-row">
                    <img src="{{asset("images/work/astropi.png")}}" alt="it-slot">
                    <div class="work-bot-content-row-text">
                        <p class="mini">{{ __('experience.card7_year') }}</p>
                        <h4>{{ __('experience.card7_title') }}</h4>
                        <p>{{ __('experience.card7_subtitle') }}</p>
                    </div>
                </a>
                <a href="https://www.spse.cz/clanek.php?i=1081" target="_blank" class="work-bot-content-row">
                    <img src="{{asset("images/work/erasmus.png")}}" alt="it-slot">
                    <div class="work-bot-content-row-text">
                        <p class="mini">{{ __('experience.card8_year') }}</p>
                        <h4>{{ __('experience.card8_title') }}</h4>
                        <p>{{ __('experience.card8_subtitle') }}</p>
                    </div>
                </a>
                <a href="https://www.spse.cz/" target="_blank" class="work-bot-content-row">
                    <img src="{{asset("images/work/cisco.jpg")}}" alt="it-slot">
                    <div class="work-bot-content-row-text">
                        <p class="mini">{{ __('experience.card4_year') }}</p>
                        <h4>{{ __('experience.card4_title') }}</h4>
                        <p>{{ __('experience.card4_subtitle') }}</p>
                    </div>
                </a>
                <a href="https://www.ecdl.cz/" target="_blank" class="work-bot-content-row">
                    <img src="{{asset("images/work/spse.png")}}" alt="it-slot">
                    <div class="work-bot-content-row-text">
                        <p class="mini">{{ __('experience.card3_year') }}</p>
                        <h4>{{ __('experience.card3_title') }}</h4>
                        <p>{{ __('experience.card3_subtitle') }}</p>
                    </div>
                </a>

                <a href="https://www.it-slot.cz/" target="_blank" class="work-bot-content-row">
                    <img src="{{asset("images/work/it-slot.png")}}" alt="it-slot">
                    <div class="work-bot-content-row-text">
                        <p class="mini">{{ __('experience.card2_year') }}</p>
                        <h4>{{ __('experience.card2_title') }}</h4>
                        <p>{{ __('experience.card2_subtitle') }}</p>
                    </div>
                </a>
                <a href="https://www.it-slot.cz/" target="_blank" class="work-bot-content-row">
                    <img src="{{asset("images/work/ecdl.png")}}" alt="it-slot">
                    <div class="work-bot-content-row-text">
                        <p class="mini">{{ __('experience.card1_year') }}</p>
                        <h4>{{ __('experience.card1_title') }}</h4>
                        <p>{{ __('experience.card1_subtitle') }}</p>
                    </div>
                </a>
            </div>
            <div id="work-bot-content-work" class="work-bot-content" style="display: none">
                <a href="https://byevolution.com/" target="_blank" class="work-bot-content-row">
                    <img src="{{asset("images/work/byevolution.jpg")}}" alt="it-slot">
                    <div class="work-bot-content-row-text">
                        <p class="mini">{{ __('experience.card6_year') }}</p>
                        <h4>{{ __('experience.card6_title') }}</h4>
                        <p>{{ __('experience.card6_subtitle') }}</p>
                    </div>
                </a>
                <a href="https://www.pekneweby.cz/" target="_blank" class="work-bot-content-row">
                    <img src="{{asset("images/work/pekneweby.jpg")}}" alt="it-slot">
                    <div class="work-bot-content-row-text">
                        <p class="mini">{{ __('experience.card5_year') }}</p>
                        <h4>{{ __('experience.card5_title') }}</h4>
                        <p>{{ __('experience.card5_subtitle') }}</p>
                    </div>
                </a>
            </div>
        </article>
    </section>
    <section id="projects">
        <h2>{{ __('projects.title') }}</h2>
        <article class="projects-row">
            <img src="{{asset(__('projects.spsehub_img'))}}" alt="spse_wp">
            <div class="projects-row-space"></div>
            <div class="projects-row-text">
                <h3>{{ __('projects.spsehub_title') }}</h3>
                <p>{{ __('projects.spsehub_text') }}</p>
                <div class="projects-row-text-links">
                    <a target="_blank" href="{{ __('projects.spsehub_web') }}">
                        <img src="{{asset("images/projects/icons/web.webp")}}" alt="">
                    </a>
                    <a target="_blank" href="{{ __('projects.spsehub_github') }}">
                        <img src="{{asset("images/projects/icons/github.webp")}}" alt="">
                    </a>
                </div>
            </div>
        </article>
        <article class="projects-row">
            <div class="projects-row-text">
                <h3>{{ __('projects.usladovny_title') }}</h3>
                <p>{{ __('projects.usladovny_text') }}</p>
                <div class="projects-row-text-links">
                    <a target="_blank" href="{{ __('projects.usladovny_web')}}">
                        <img src="{{asset("images/projects/icons/web.webp")}}" alt="">
                    </a>
                </div>
            </div>
            <div class="projects-row-space"></div>
            <img src="{{asset(__('projects.usladovny_img'))}}" alt="spse_wp">

        </article>
    </section>
    <section id="tools">
        <h2>{{__('tools.title')}}</h2>
        <article class="tools-row">
            <div class="tools-row-card">
                <img src="{{asset("images/tools/html.png")}}" alt="">
                <h4>HTML</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{asset("images/tools/css.png")}}" alt="">
                <h4>CSS</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{asset("images/tools/js.png")}}" alt="">
                <h4>JS</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{asset("images/tools/figma.png")}}" alt="">
                <h4>Figma</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{asset("images/tools/fedora.png")}}" alt="">
                <h4>Fedora</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{asset("images/tools/php.png")}}" alt="">
                <h4>PHP</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{asset("images/tools/laravel.png")}}" alt="">
                <h4>Laravel</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{asset("images/tools/symfony.png")}}" alt="">
                <h4>Symfony</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{asset("images/tools/java.png")}}" alt="">
                <h4>Java</h4>
            </div>
            <div class="tools-row-card">
                <img src="{{asset("images/tools/mysql.png")}}" alt="">
                <h4>MySQL</h4>
            </div>
        </article>
    </section>
    <section id="reviews">
        <h2>{{ __('reviews.title') }}</h2>
        <article class="reviews-row">
            <div class="reviews-row-card">
                <p>{{ __('reviews.card1_text') }}</p>
                <div class="reviews-row-card-text">
                    <span><p>{{ __('reviews.card1_name') }}</p></span>
                    <p class="mini"> - {{ __('reviews.card1_position') }}</p>
                </div>
            </div>
            <div class="reviews-row-card">
                <p>{{ __('reviews.card2_text') }}</p>
                <div class="reviews-row-card-text">
                    <span><p>{{ __('reviews.card2_name') }}</p></span>
                    <p class="mini"> - {{ __('reviews.card2_position') }}</p>
                </div>
            </div>
            <div class="reviews-row-card">
                <p>{{ __('reviews.card3_text') }}</p>
                <div class="reviews-row-card-text">
                    <span><p>{{ __('reviews.card3_name') }}</p></span>
                    <p class="mini"> - {{ __('reviews.card3_position') }}</p>
                </div>
            </div>
        </article>

    </section>

@endsection
