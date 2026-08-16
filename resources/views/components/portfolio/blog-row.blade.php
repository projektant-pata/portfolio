@props([
    'article',
    'locale',
    'lead' => false,
    'archiveIndex' => '',
])

@php
    use App\Support\ArticleDate;
@endphp

{{--
    One ledger row: date rail, body, thumbnail. The whole row is the link.
    Used by the listing and by the article page's "Read next" block — there
    is deliberately no second card component.
--}}
<a
    href="{{ route('blog.show', $article->slug) }}"
    @class([
        'blog-row',
        'blog-row--lead' => $lead,
        'blog-row--noimg' => ! $article->thumbnail_url,
    ])
>
    <div class="blog-row-date" @if ($lead) data-flag="{{ __('pages/blog.newest') }}" @endif>
        <time datetime="{{ ArticleDate::iso($article->date) }}" class="blog-row-day">{{ ArticleDate::railDay($article->date, $locale) }}</time>
        <span class="blog-row-my">{{ ArticleDate::railMonth($article->date, $locale) }}</span>
    </div>

    <div class="blog-row-body">
        <h3 class="blog-row-title">{{ $article->getTranslation('header', $locale) }}</h3>

        @if ($article->getTranslation('description', $locale))
            <p class="blog-row-desc">{{ $article->getTranslation('description', $locale) }}</p>
        @endif

        @if ($article->badges->isNotEmpty())
            <div class="blog-row-badges">
                @foreach ($article->badges as $badge)
                    <span class="blog-badge" style="--badge-color: {{ $badge->color }}">{{ $badge->getTranslation('name', $locale) }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <div class="blog-row-thumb">
        @if ($article->thumbnail_url)
            <img src="{{ $article->thumbnail_url }}" alt="" loading="lazy">
        @else
            <span class="blog-row-ghost" aria-hidden="true">{{ $archiveIndex }}</span>
        @endif
    </div>
</a>
