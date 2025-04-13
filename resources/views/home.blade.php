@extends('base')

@section('title', 'projektant-pata')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
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
        <h2>Work & Edu</h2>
        <article id="work-top">
            <div class="work-top-btn">
                <h4>Work</h4>
            </div>
            <div class="work-top-btn">
                <h4 class="work-top-btn-active">Education</h4>
            </div>
        </article>
        <article id="work-bot">
            <div id="work-bot-line"></div>
            {{--                <svg height="400px" width="400px" version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-51.2 -51.2 614.40 614.40" xml:space="preserve" fill="var(--primary-color-light-fade)"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <style type="text/css"> .st0{fill:var(--primary-color-light-fade);} </style> <g> <path class="st0" d="M505.837,180.418L279.265,76.124c-7.349-3.385-15.177-5.093-23.265-5.093c-8.088,0-15.914,1.708-23.265,5.093 L6.163,180.418C2.418,182.149,0,185.922,0,190.045s2.418,7.896,6.163,9.627l226.572,104.294c7.349,3.385,15.177,5.101,23.265,5.101 c8.088,0,15.916-1.716,23.267-5.101l178.812-82.306v82.881c-7.096,0.8-12.63,6.84-12.63,14.138c0,6.359,4.208,11.864,10.206,13.618 l-12.092,79.791h55.676l-12.09-79.791c5.996-1.754,10.204-7.259,10.204-13.618c0-7.298-5.534-13.338-12.63-14.138v-95.148 l21.116-9.721c3.744-1.731,6.163-5.504,6.163-9.627S509.582,182.149,505.837,180.418z"></path> <path class="st0" d="M256,346.831c-11.246,0-22.143-2.391-32.386-7.104L112.793,288.71v101.638 c0,22.314,67.426,50.621,143.207,50.621c75.782,0,143.209-28.308,143.209-50.621V288.71l-110.827,51.017 C278.145,344.44,267.25,346.831,256,346.831z"></path> </g> </g></svg>--}}
            <div id="work-bot-content">
                <a href="https://astro-pi.org/" target="_blank" class="work-bot-content-row">
                    <img src="{{asset("images/work/astropi.png")}}" alt="it-slot">
                    <div class="work-bot-content-row-text">
                        <p class="mini">2024</p>
                        <h4>Hackathon Astro Pi</h4>
                        <p>Won spaced themed hackathon in a team of 3 students </p>
                    </div>
                </a>
                <a href="https://www.spse.cz/clanek.php?i=1081" target="_blank" class="work-bot-content-row">
                    <img src="{{asset("images/work/erasmus.png")}}" alt="it-slot">
                    <div class="work-bot-content-row-text">
                        <p class="mini">2023</p>
                        <h4>Erasmus</h4>
                        <p>Work trip to Spain made possible by European Union</p>
                    </div>
                </a>
                <a href="https://www.spse.cz/" target="_blank" class="work-bot-content-row">
                    <img src="{{asset("images/work/spse.png")}}" alt="it-slot">
                    <div class="work-bot-content-row-text">
                        <p class="mini">2022 - now</p>
                        <h4>SPŠE a VOŠ Pardubice</h4>
                        <p>Student at renowned secondary school in IT branch</p>
                    </div>
                </a>
                <a href="https://www.ecdl.cz/" target="_blank" class="work-bot-content-row">
                    <img src="{{asset("images/work/ecdl.png")}}" alt="it-slot">
                    <div class="work-bot-content-row-text">
                        <p class="mini">2022 - now</p>
                        <h4>ECDL Certificate</h4>
                        <p>international certification of IT skills, demonstrating proficiency in using computers
                            and common software applications effectively</p>
                    </div>
                </a>

                <a href="https://www.it-slot.cz/" target="_blank" class="work-bot-content-row">
                    <img src="{{asset("images/work/it-slot.png")}}" alt="it-slot">
                    <div class="work-bot-content-row-text">
                        <p class="mini">2022</p>
                        <h4>IT Slot</h4>
                        <p>11th place out of 4963 contestants</p>
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
                        <img src="{{asset("images/mobile/icons/github.webp")}}" alt="">
                    </a>
                    <a target="_blank" href="{{ __('projects.spsehub_github') }}">
                        <img src="{{asset("images/mobile/icons/github.webp")}}" alt="">
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
                        <img src="{{asset("images/mobile/icons/github.webp")}}" alt="">
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
