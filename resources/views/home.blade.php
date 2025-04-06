@extends('base')

@section('title', 'projektant-pata')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">

    @section('content')
        <section id="hero-page">
            <article id="hero-page-text">
                <h3>👋 Hello world!</h3>
                <h1>I’m <span>projektant-pata</span>,</h1>
                <p id="underh1"><span>Full-stack</span> developer</p>
            </article>
            <article id="hero-page-image">
                <img src="{{ asset('images/id-photo-portrait-businessman-suit-260nw-1505360618 1.png') }}" alt="hero">
            </article>
        </section>
        <section id="stats">
            <h2>My Stats</h2>
            <article id="stats-cards">
                <div class="stats-cards-card">
                    <h3><span>Junior</span></h3>
                    <p>Professional Level</p>
                </div>
                <div class="stats-cards-card">
                    <h3><span>5+</span></h3>
                    <p>Projects Completed</p>
                </div>
                <div class="stats-cards-card">
                    <h3><span>2</span></h3>
                    <p>Happy Clients</p>
                </div>
                <div class="stats-cards-card">
                    <h3><span>2</span></h3>
                    <p>Countries Reached</p>
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
            <h2>Projects</h2>
            <article class="projects-row">
                <img src="{{asset("images/projects/spse_wp.png")}}" alt="spse_wp">
                <div class="projects-row-space"></div>
                <div class="projects-row-text">
                    <h3>SPŠE Hub</h3>
                    <p>The SPSE hub is a project devised by Mr Nitrogen designed to learn how to make a website. It's my
                        beginnings and it has a nostalgic effect on me, so I'm putting it up. Made in HTML5, CSS3 and
                        JavaScript</p>
                    <div class="projects-row-text-links">
                        <img src="{{asset("images/mobile/icons/github.png")}}" alt="">
                    </div>
                </div>
            </article>
            <article class="projects-row">
                <div class="projects-row-text">
                    <h3>SPŠE Hub</h3>
                    <p>The SPSE hub is a project devised by Mr Nitrogen designed to learn how to make a website. It's my
                        beginnings and it has a nostalgic effect on me, so I'm putting it up. Made in HTML5, CSS3 and
                        JavaScript</p>
                    <div class="projects-row-text-links">
                        <img src="{{asset("images/mobile/icons/github.png")}}" alt="">
                    </div>
                </div>
                <div class="projects-row-space"></div>
                <img src="{{asset("images/projects/spse_wp.png")}}" alt="spse_wp">

            </article>
        </section>
        <section id="tools">
            <h2>Tools</h2>
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
                    <img src="{{asset("images/tools/php.png")}}" alt="">
                    <h4>PHP</h4>
                </div>
            </article>
            <article class="tools-row">
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
                    <img src="{{asset("images/tools/php.png")}}" alt="">
                    <h4>PHP</h4>
                </div>
            </article>
        </section>
        <section id="reviews">
            <h2>Reviews</h2>
            <article class="reviews-row">
                <div class="reviews-row-card">
                    <p>"Richard always delivers clean, efficient code and has a great sense for user-friendly design. A
                        reliable and talented team player!"</p>
                    <div class="reviews-row-card-text">
                        <span><p>Petr Machovec</p></span>
                        <p class="mini"> - Co-founder of Prezz</p>
                    </div>
                </div>
                <div class="reviews-row-card">
                    <p>"Richard always delivers clean, efficient code and has a great sense for user-friendly design. A
                        reliable and talented team player!"</p>
                    <div class="reviews-row-card-text">
                        <span><p>Petr Machovec</p></span>
                        <p class="mini"> - Co-founder of Prezz</p>
                    </div>
                </div>
                <div class="reviews-row-card">
                    <p>"Richard always delivers clean, efficient code and has a great sense for user-friendly design. A
                        reliable and talented team player!"</p>
                    <div class="reviews-row-card-text">
                        <span><p>Petr Machovec</p></span>
                        <p class="mini"> - Co-founder of Prezz</p>
                    </div>
                </div>
            </article>

        </section>

    @endsection
