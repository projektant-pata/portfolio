<x-portfolio-layout :title="__('layout/header.blog_title')" :description="__('layout/header.blog_desc')" :styles="['resources/css/pages/blog.css']">

    @php $locale = app()->getLocale(); @endphp

    <x-portfolio.dock-hero
        :eyebrow="\App\Models\Setting::text('blog_hero_suptitle', $locale)"
        :title="\App\Models\Setting::text('blog_hero_title', $locale)"
        :roles="\App\Models\Setting::list('blog_hero_roles', $locale)"
        :tags="__('pages/blog.hero_tags')"
        :wordmark="__('pages/blog.hero_wordmark')"
        :photo="config('portfolio.hero_images.blog')"
        photo-alt=""
        photo-position="50% 38%"
    />

    {{-- No fade-up: the hero sits above this section's top edge, so it must
         already be painted when the page loads. --}}
    <section id="blog" class="portfolio-section portfolio-section--no-reveal">

        {{-- Czech needs three plural forms and picks them off the archive
             total, not off the filtered count: "1 z 1 článku" but
             "1 ze 2 článků". Same shape as home/experience.count_*. --}}
        @php
            $countKey = $total === 1 ? 'count_one' : ($total <= 4 ? 'count_few' : 'count_many');
        @endphp

        <div class="blog-head">
            <h2>{{ __('pages/blog.list_title') }}</h2>
            <span class="blog-head-count">{!! str_replace(
                [':count', ':total'],
                ['<b>'.$articles->count().'</b>', $total],
                __('pages/blog.'.$countKey)
            ) !!}</span>
        </div>

        @if ($activeSlug !== '')
            <p class="blog-filter">
                <span>{{ __('pages/blog.filtered_by') }}</span>
                @if ($activeBadge)
                    <span class="blog-badge" style="--badge-color: {{ $activeBadge->color }}">{{ $activeBadge->getTranslation('name', $locale) }}</span>
                @else
                    <span class="blog-badge">{{ $activeSlug }}</span>
                @endif
                <a href="{{ route('blog') }}" class="blog-reset">{{ __('pages/blog.show_all') }}</a>
            </p>
        @endif

        <div class="blog-list">
            @forelse ($articles as $index => $article)
                <x-portfolio.blog-row
                    :article="$article"
                    :locale="$locale"
                    :lead="$index === 0"
                    :archive-index="$archiveIndexes[$article->id]"
                />
            @empty
                <p class="blog-empty">{{ $activeSlug !== '' ? __('pages/blog.empty_filtered') : __('pages/blog.empty') }}</p>
            @endforelse
        </div>

        @if ($articles->isNotEmpty())
            <p class="blog-end">{{ __('pages/blog.end', [
                'total' => $total,
                'since' => \App\Support\ArticleDate::monthYear($articles->last()->date, $locale),
            ]) }}</p>
        @endif
    </section>

</x-portfolio-layout>
