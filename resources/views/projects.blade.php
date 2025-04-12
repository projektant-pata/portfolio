@extends('base')

@section('title', 'Projects')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/projects.css') }}">
@endsection
@section('scripts')
    {{--    <script src="{{asset('js/projects.js')}}" defer></script>--}}
@endsection

@section('content')
    <section id="projects">
        <article id="projects-article">
            <h2>2025</h2>
            <div class="projects-article-row">
                <img src="{{asset("images/projects/portfolio.png")}}" alt="spse_wp">
                <div class="projects-row-space"></div>
                <div class="projects-row-text">
                    <h3>Portfolio</h3>
                    <p>I think everybody making something artistic should have portfolio in any form that displays their minds, creativity and most importantly personality - and I believe that web developers are artists too.</p>
                    <div class="projects-row-text-links">
                        <a target="_blank" href="https://github.com/projektant-pata/SPSE-WP">
                            <img src="{{asset("images/mobile/icons/github.png")}}" alt="">
                        </a>
                    </div>
                </div>
            </div>
        </article>
        <article id="projects-article">
            <h2>2024</h2>
            <div class="projects-article-row">
                <img src="{{asset("images/projects/usladovny.png")}}" alt="spse_wp">
                <div class="projects-row-space"></div>
                <div class="projects-row-text">
                    <h3>U Sladovny</h3>
                    <p>The project I was part of during my part-time work at PekneWeby. The plan was simple - rebrand and reconstruct the restaurant. My take was in front-end and back-end. And the result? I think PekneWeby did fabulous job. </p>
                    <div class="projects-row-text-links">
                        <a target="_blank" href="https://github.com/projektant-pata/SPSE-WP">
                            <img src="{{asset("images/mobile/icons/github.png")}}" alt="">
                        </a>
                    </div>
                </div>
            </div>
        </article>
        <article id="projects-article">
            <h2>2022</h2>
            <div class="projects-article-row">
                <img src="{{asset("images/projects/spse_wp.png")}}" alt="spse_wp">
                <div class="projects-row-space"></div>
                <div class="projects-row-text">
                    <h3>SPŠE Hub</h3>
                    <p>The SPSE hub is a project devised by Mr Nitrogen designed to learn how to make a website. It's my
                        beginnings and it has a nostalgic effect on me, so I'm putting it up. Made in HTML5, CSS3 and
                        JavaScript.</p>
                    <div class="projects-row-text-links">
                        <a target="_blank" href="https://github.com/projektant-pata/SPSE-WP">
                            <img src="{{asset("images/mobile/icons/github.png")}}" alt="">
                        </a>
                    </div>
                </div>
            </div>
        </article>
    </section>

@endsection
