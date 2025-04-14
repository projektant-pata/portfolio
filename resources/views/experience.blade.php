@extends('base')

@section('title', 'Experience')
@section('description', "Discover Richard Hývl’s development experience – from real-world internships to personal and school projects.")
@section('styles')
    <link rel="stylesheet" href="{{ asset('css/experience.css') }}">
@endsection
@section('scripts')
    <script src="{{asset('js/experience.js')}}" defer></script>
@endsection

@section('content')
    <section id="experience">
        <h2>{{ __('experience.title') }}</h2>
        <div id="experience-timeline">        <div id="experience-timelineender"></div>
        </div>
        <div id="experience-triangle"></div>
        <div id="experience-content">
            <article class="experience-content-row">
                <div class="experience-content-row-circle"></div>
                <div class="experience-content-row-line"></div>
                <div class="experience-content-row-card">
                    <h4>{!! __('experience.card7_year') !!}</h4>
                    <h3><span>{!! __('experience.card7_title') !!}</span></h3>
                    <p class="mini">{!! __('experience.card7_subtitle') !!}</p>
                    <p>{!! __('experience.card7_text') !!}
                    </p>
                    <div class="experience-content-row-card-buttons">
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card7_but1') !!}
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card7_but2') !!}
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card7_but3') !!}
                        </div>
                    </div>

                </div>
            </article>
            <article class="experience-content-row">
                <div class="experience-content-row-circle"></div>
                <div class="experience-content-row-line"></div>
                <div class="experience-content-row-card">
                    <h4>{!! __('experience.card6_year') !!}</h4>
                    <h3><span>{!! __('experience.card6_title') !!}</span></h3>
                    <p class="mini">{!! __('experience.card6_subtitle') !!}</p>
                    <p>{!! __('experience.card6_text') !!}</p>
                    <div class="experience-content-row-card-buttons">
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card6_but1') !!}
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card6_but2') !!}
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card6_but3') !!}
                        </div>
                    </div>

                </div>
            </article>
            <article class="experience-content-row">
                <div class="experience-content-row-circle"></div>
                <div class="experience-content-row-line"></div>
                <div class="experience-content-row-card">
                    <h4>{!! __('experience.card8_year') !!}</h4>
                    <h3><span>{!! __('experience.card8_title') !!}</span></h3>
                    <p class="mini">{!! __('experience.card8_subtitle') !!}</p>
                    <p>{!! __('experience.card8_text') !!}</p>
                    <div class="experience-content-row-card-buttons">
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card8_but1') !!}
                        </div>

                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card8_but3') !!}
                        </div>
                    </div>

                </div>
            </article>
            <article class="experience-content-row">
                <div class="experience-content-row-circle"></div>
                <div class="experience-content-row-line"></div>
                <div class="experience-content-row-card experience-content-row-specialcard">
                    <h4>{!! __('experience.card5_year') !!}</h4>
                    <h3><span>{!! __('experience.card5_title') !!}</span></h3>
                    <p class="mini">{!! __('experience.card5_subtitle') !!}</p>
                    <p>{!! __('experience.card5_text') !!}</p>
                    <div class="experience-content-row-card-buttons">
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card5_but1') !!}
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card5_but2') !!}
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card5_but3') !!}
                        </div>
                    </div>

                </div>
            </article>
            <article class="experience-content-row">
                <div class="experience-content-row-circle"></div>
                <div class="experience-content-row-line"></div>
                <div class="experience-content-row-card">
                    <h4>{!! __('experience.card4_year') !!}</h4>
                    <h3><span>{!! __('experience.card4_title') !!}</span></h3>
                    <p class="mini">{!! __('experience.card4_subtitle') !!}</p>
                    <div class="experience-content-row-card-buttons">
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card4_but1') !!}
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card4_but2') !!}
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card4_but3') !!}
                        </div>
                    </div>

                </div>
            </article>
            <article class="experience-content-row">
                <div class="experience-content-row-circle"></div>
                <div class="experience-content-row-line"></div>
                <div class="experience-content-row-card">
                    <h4>{!! __('experience.card3_year') !!}</h4>
                    <h3><span>{!! __('experience.card3_title') !!}</span></h3>
                    <p class="mini">{!! __('experience.card3_subtitle') !!}</p>
                    <p>{!! __('experience.card3_text') !!}</p>
                    <div class="experience-content-row-card-buttons">
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card3_but1') !!}
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card3_but2') !!}
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card3_but3') !!}
                        </div>
                    </div>

                </div>
            </article>
            <article class="experience-content-row">
                <div class="experience-content-row-circle"></div>
                <div class="experience-content-row-line"></div>
                <div class="experience-content-row-card experience-content-row-specialcard">
                    <h4>{!! __('experience.card2_year') !!}</h4>
                    <h3><span>{!! __('experience.card2_title') !!}</span></h3>
                    <p class="mini">{!! __('experience.card2_subtitle') !!}</p>
                    <div class="experience-content-row-card-buttons">
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card2_but1') !!}
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card2_but2') !!}
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card2_but3') !!}
                        </div>
                    </div>

                </div>
            </article>
            <article class="experience-content-row">
                <div class="experience-content-row-circle"></div>
                <div class="experience-content-row-line"></div>
                <div class="experience-content-row-card">
                    <h4>{!! __('experience.card1_year') !!}</h4>
                    <h3><span>{!! __('experience.card1_title') !!}</span></h3>
                    <p class="mini">{!! __('experience.card1_subtitle') !!}</p>
                    <div class="experience-content-row-card-buttons">
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card1_but1') !!}
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card1_but2') !!}
                        </div>
                        <div class="experience-content-row-card-buttons-button">
                            {!! __('experience.card1_but3') !!}
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>
@endsection
