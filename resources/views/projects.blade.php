@extends('base')

@section('title', __('header.projects_title'))
@section('description', __('header.projects_desc'))

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
                <img src="{{ asset(__('projects.portfolio_img')) }}" alt="spse_wp">
                <div class="projects-row-space"></div>
                <div class="projects-row-text">
                    <h3>{{ __('projects.portfolio_title') }}</h3>
                    <p>{{ __('projects.portfolio_text') }}</p>
                    <div class="projects-row-text-links">
                        <a target="_blank" href="{{ __('projects.portfolio_web') }}">
                            <img src="{{asset("images/projects/icons/web.webp")}}" alt="">
                        </a>
                        <a target="_blank" href="{{ __('projects.portfolio_github') }}">
                            <img src="{{asset("images/projects/icons/github.webp")}}" alt="">
                        </a>
                    </div>
                </div>
            </div>
        </article>
        <article id="projects-article">
            <h2>2024</h2>
            <div class="projects-article-row">
                <img src="{{asset(__('projects.usladovny_img'))}}" alt="spse_wp">
                <div class="projects-row-space"></div>
                <div class="projects-row-text">
                    <h3>{{__('projects.usladovny_title')}}</h3>
                    <p>{{__('projects.usladovny_text')}}</p>
                    <div class="projects-row-text-links">
                        <a target="_blank" href="{{ __('projects.usladovny_web') }}">
                            <img src="{{asset("images/projects/icons/github.webp")}}" alt="">
                        </a>
                    </div>
                </div>
            </div>
        </article>
        <article id="projects-article">
            <h2>2022</h2>
            <div class="projects-article-row">
                <img src="{{ asset(__('projects.spsehub_img')) }}" alt="spse_wp">
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
            </div>
        </article>
    </section>

@endsection
