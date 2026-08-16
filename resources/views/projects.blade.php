<x-portfolio-layout :title="__('layout/header.projects_title')" :description="__('layout/header.projects_desc')" :styles="['resources/css/pages/projects.css']">

    @php $locale = app()->getLocale(); @endphp
    {{-- Hero --}}
    <x-portfolio.dock-hero
        :eyebrow="\App\Models\Setting::text('projects_hero_suptitle', $locale)"
        :title="\App\Models\Setting::text('projects_hero_title', $locale)"
        :roles="\App\Models\Setting::list('projects_hero_roles', $locale)"
        :tags="__('pages/projects.hero_tags')"
        :wordmark="__('pages/projects.hero_wordmark')"
        :photo="config('portfolio.hero_images.projects')"
        photo-alt=""
        photo-position="50% 30%"
    />

    {{-- No fade-up: the hero leaves this section's top edge inside the first
         viewport, so it must already be there when the page paints. --}}
    <section id="projects" class="portfolio-section portfolio-section--no-reveal">
        <div class="proj-list">
            @forelse ($projects as $year => $yearProjects)
                <section class="proj-group">
                    <x-portfolio.project-year-head :year="$year" :count="$yearProjects->count()" />

                    @foreach ($yearProjects as $project)
                        <x-portfolio.project-row :project="$project" :locale="$locale" />
                    @endforeach
                </section>
            @empty
                <p class="proj-none">{{ __('pages/projects.empty_list') }}</p>
            @endforelse
        </div>
    </section>

</x-portfolio-layout>
