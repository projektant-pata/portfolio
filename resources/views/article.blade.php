@php
    use App\Support\ArticleDate;
    $title = $article->getTranslation('header', $locale);
    $lede = $article->getTranslation('description', $locale);
@endphp

<x-portfolio-layout :title="$title.' — Blog'" :description="$lede" :styles="['resources/css/pages/blog.css']">

    {{--
        No dock hero here on purpose: the ghost wordmark, rotating roles and
        photo all compete with the one thing this page exists for. Continuity
        comes from the shared grammar — one bordered container, hairline
        edges, the ghost stroke demoted to a bookmark in the back rail.
    --}}
    <article class="blog-article portfolio-section portfolio-section--no-reveal">
        <div class="art-rail">
            <a class="art-back" href="{{ route('blog') }}">
                <span aria-hidden="true">←</span>{{ __('pages/blog.back') }}
            </a>
            <span class="art-rail-ghost" aria-hidden="true">{{ __('pages/blog.hero_wordmark') }}</span>
        </div>

        <header class="art-head">
            <p class="art-meta">
                <time datetime="{{ ArticleDate::iso($article->date) }}">{{ ArticleDate::header($article->date, $locale) }}</time>
                <s aria-hidden="true">·</s>
                <span>{{ __('pages/blog.reading_time', ['minutes' => $article->readingTime($locale)]) }}</span>
            </p>

            <h1 class="art-title">{{ $title }}</h1>

            @if ($lede)
                <p class="art-lede">{{ $lede }}</p>
            @endif

            @if ($article->badges->isNotEmpty())
                <div class="art-badges">
                    @foreach ($article->badges as $badge)
                        <x-portfolio.blog-badge :badge="$badge" :locale="$locale" />
                    @endforeach
                </div>
            @endif
        </header>

        @if ($article->thumbnail_url)
            <figure class="art-cover">
                <img src="{{ $article->thumbnail_url }}" alt="">
            </figure>
        @endif

        <div class="blog-prose">
            {!! $body !!}
        </div>

        <footer class="art-foot">
            <span>{{ __('pages/blog.foot_written') }}</span>
            <span>{!! __('pages/blog.foot_mistake', ['url' => config('portfolio.social.email')]) !!}</span>
        </footer>
    </article>

    @if ($readNext->isNotEmpty())
        <section class="portfolio-section portfolio-section--no-reveal">
            <x-portfolio.section-head
                :eyebrow="__('pages/blog.read_next_eyebrow')"
                :title="__('pages/blog.read_next')"
            />

            <div class="blog-list">
                @foreach ($readNext as $next)
                    <x-portfolio.blog-row
                        :article="$next"
                        :locale="$locale"
                        :archive-index="$archiveIndexes[$next->id]"
                    />
                @endforeach
            </div>
        </section>
    @endif

</x-portfolio-layout>
