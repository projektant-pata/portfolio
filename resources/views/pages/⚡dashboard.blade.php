<?php

use App\Models\AboutCard;
use App\Models\Article;
use App\Models\Badge;
use App\Models\Experience;
use App\Models\Link;
use App\Models\Project;
use App\Models\Review;
use App\Models\Stat;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component {
    /**
     * Entity overview tiles — count + entry point into each manage page.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function tiles(): array
    {
        return [
            ['label' => __('Projects'), 'icon' => 'layout-grid', 'count' => Project::count(), 'route' => 'manage.projects'],
            ['label' => __('Experiences'), 'icon' => 'briefcase', 'count' => Experience::count(), 'route' => 'manage.experiences'],
            ['label' => __('Badges'), 'icon' => 'tag', 'count' => Badge::count(), 'route' => 'manage.badges'],
            ['label' => __('Articles'), 'icon' => 'newspaper', 'count' => Article::count(), 'route' => 'manage.articles'],
            ['label' => __('Links'), 'icon' => 'link', 'count' => Link::count(), 'route' => 'manage.links'],
            ['label' => __('Stats'), 'icon' => 'chart-bar', 'count' => Stat::count(), 'route' => 'manage.stats'],
            ['label' => __('Reviews'), 'icon' => 'chat-bubble-left-right', 'count' => Review::count(), 'route' => 'manage.reviews'],
            ['label' => __('About cards'), 'icon' => 'identification', 'count' => AboutCard::count(), 'route' => 'manage.about-cards'],
        ];
    }

    /**
     * Quick-create shortcuts — deep-link into each manage page's create modal.
     *
     * @return array<int, array<string, string>>
     */
    #[Computed]
    public function quickActions(): array
    {
        return [
            ['label' => __('Add project'), 'icon' => 'layout-grid', 'route' => 'manage.projects'],
            ['label' => __('Add experience'), 'icon' => 'briefcase', 'route' => 'manage.experiences'],
            ['label' => __('Add badge'), 'icon' => 'tag', 'route' => 'manage.badges'],
            ['label' => __('Add article'), 'icon' => 'newspaper', 'route' => 'manage.articles'],
            ['label' => __('Add link'), 'icon' => 'link', 'route' => 'manage.links'],
            ['label' => __('Add stat'), 'icon' => 'chart-bar', 'route' => 'manage.stats'],
            ['label' => __('Add review'), 'icon' => 'chat-bubble-left-right', 'route' => 'manage.reviews'],
            ['label' => __('Add about card'), 'icon' => 'identification', 'route' => 'manage.about-cards'],
        ];
    }

    /**
     * Most recently updated content across projects, experiences and articles.
     */
    #[Computed]
    public function recent(): Collection
    {
        $locale = app()->getLocale();

        $projects = Project::latest('updated_at')->take(6)->get()->map(fn (Project $p) => [
            'type' => __('Project'),
            'icon' => 'layout-grid',
            'title' => $p->getTranslation('header', $locale) ?: '—',
            'updated' => $p->updated_at,
            'route' => route('manage.projects'),
        ]);

        $experiences = Experience::latest('updated_at')->take(6)->get()->map(fn (Experience $e) => [
            'type' => __('Experience'),
            'icon' => 'briefcase',
            'title' => $e->getTranslation('title', $locale) ?: '—',
            'updated' => $e->updated_at,
            'route' => route('manage.experiences'),
        ]);

        $articles = Article::latest('updated_at')->take(6)->get()->map(fn (Article $a) => [
            'type' => __('Article'),
            'icon' => 'newspaper',
            'title' => $a->getTranslation('header', $locale) ?: '—',
            'updated' => $a->updated_at,
            'route' => route('manage.articles'),
        ]);

        return $projects
            ->concat($experiences)
            ->concat($articles)
            ->sortByDesc('updated')
            ->take(6)
            ->values();
    }
}; ?>

<div class="manage-page p-6 space-y-8">

    <x-manage.page-header
        title="{{ __('Dashboard') }}"
        subtitle="{{ __('Welcome back, :name.', ['name' => auth()->user()->name]) }}"
    />

    {{-- Entity counts --}}
    <div class="dash-grid">
        @foreach ($this->tiles as $tile)
            <a href="{{ route($tile['route']) }}" wire:navigate class="dash-tile">
                <div class="dash-tile__top">
                    <flux:icon :icon="$tile['icon']" variant="mini" />
                </div>
                <span class="dash-tile__count">{{ $tile['count'] }}</span>
                <span class="dash-tile__label">{{ $tile['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Quick actions --}}
    <section class="space-y-3">
        <h2 class="manage-section-label text-sm font-semibold">{{ __('Quick actions') }}</h2>
        <div class="flex flex-wrap gap-3">
            @foreach ($this->quickActions as $action)
                <flux:button
                    :href="route($action['route'], ['create' => 1])"
                    wire:navigate
                    :icon="$action['icon']"
                    class="btn-gold-subtle"
                >
                    {{ $action['label'] }}
                </flux:button>
            @endforeach
        </div>
    </section>

    {{-- Recently updated --}}
    <section class="dash-card">
        <h2 class="dash-card__title">{{ __('Recently updated') }}</h2>

        @if ($this->recent->isEmpty())
            <p class="manage-empty">{{ __('Nothing here yet.') }}</p>
        @else
            <div class="dash-recent">
                @foreach ($this->recent as $item)
                    <a href="{{ $item['route'] }}" wire:navigate class="dash-recent__row">
                        <flux:icon :icon="$item['icon']" variant="mini" class="dash-recent__icon" />
                        <span class="dash-recent__title">{{ $item['title'] }}</span>
                        <span class="dash-recent__meta">{{ $item['type'] }} · {{ $item['updated']->format('d.m.Y') }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

</div>
