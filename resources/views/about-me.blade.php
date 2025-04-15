@extends('base')

@section('title', __('header.about_title'))
@section('description', __('header.about_desc'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/about-me.css') }}">
@endsection
@section('scripts')
    <script defer src="{{ asset('js/about-me.js') }}"></script>
    <script defer src="{{ asset('js/about-me-api.js') }}"></script>
@endsection
@section('content')
    <section id="about-me">
        <h2>{!! __('about-me.title') !!}</h2>
        <div id="about-me-content">
            <div class="about-me-row-card">
                <h3>{!! __('about-me.card1_title') !!}</h3>
                <p>{!! __('about-me.card1_text') !!}</p>
            </div>
            <div class="about-me-row-card">
                <h3>{!! __('about-me.card2_title') !!}</h3>
                <p>{!! __('about-me.card2_text') !!}</p>
            </div>
            <div class="about-me-row-card">
                <h3>{!! __('about-me.card3_title') !!}</h3>
                <p>{!! __('about-me.card3_text') !!}</p>
            </div>
            <div class="about-me-row-card">
                <h3>{!! __('about-me.card4_title') !!}</h3>
                <p>{!! __('about-me.card4_text') !!}</p>
            </div>
            <div class="about-me-row-card">
                <h3>{!! __('about-me.card5_title') !!}</h3>
                <p>{!! __('about-me.card5_text') !!}</p>
            </div>
        </div>
    </section>
    <section id="stats">
        <h2>{{ __('stats.title') }}</h2>
        <article id="stats-content">
            <div class="stats-content-row-card">
                <h3><span>{{ __('stats.card1_title') }}</span></h3>
                <p>{{ __('stats.card1_text') }}</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span>{{ __('stats.card2_title') }}</span></h3>
                <p>{{ __('stats.card2_text') }}</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span>{{ __('stats.card3_title') }}</span></h3>
                <p>{{ __('stats.card3_text') }}</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span>{{ __('stats.card4_title') }}</span></h3>
                <p>{{ __('stats.card4_text') }}</p>
            </div>

            <div class="stats-content-row-card">
                <h3><span>{{ __('stats.card5_title')}}</span></h3>
                <p>{{ __('stats.card5_text')}}</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span id="elo">{{ __('stats.card6_title')}}</span></h3>
                <p>{{ __('stats.card6_text')}}</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span>{{ __('stats.card7_title')}}</span></h3>
                <p>{{ __('stats.card7_text')}}</p>
            </div>

            <div class="stats-content-row-card">
                <h3><span>{{ __('stats.card8_title')}}</span></h3>
                <p>{{ __('stats.card8_text')}}</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span>{{ __('stats.card9_title')}}</span></h3>
                <p>{{ __('stats.card9_text')}}</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span id="github-repos">{{ __('stats.card10_title')}}</span></h3>
                <p>{{ __('stats.card10_text')}}</p>
            </div>
            <div class="stats-content-row-card">
                <h3><span>{{ __('stats.card11_title')}}</span></h3>
                <p>{{ __('stats.card11_text')}}</p>
            </div>
        </article>
    </section>

@endsection
