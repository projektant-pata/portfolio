<x-portfolio-layout :title="__('layout/header.projects_title')" :description="__('layout/header.projects_desc')" :styles="['resources/css/pages/projects.css']">

    @php $locale = app()->getLocale(); @endphp
    <section id="projects" class="portfolio-section" style="padding-top: var(--sp-section)">
        @forelse ($projects as $year => $yearProjects)
            <article class="projects-year-group">
                <h2 class="projects-year-label">{{ $year }}</h2>

                @foreach ($yearProjects as $index => $project)
                    <x-portfolio.project-row :project="$project" :locale="$locale" :reverse="$index % 2 !== 0" />
                @endforeach
            </article>
        @empty
            <p style="color: var(--c-muted); text-align: center; padding: 4rem 0;">
                {{ app()->getLocale() === 'cs' ? 'Žádné projekty zatím nejsou.' : 'No projects yet.' }}
            </p>
        @endforelse
    </section>

</x-portfolio-layout>
