@props(['year', 'count'])

@php
    $key = $count === 1 ? 'one' : ($count >= 2 && $count <= 4 ? 'few' : 'many');
@endphp

<div class="proj-yhead">
    <h2>{{ $year }}</h2>
    <span class="proj-yline" aria-hidden="true"></span>
    <span
        class="proj-ycount"
        data-one="{{ __('pages/projects.year_count_one') }}"
        data-few="{{ __('pages/projects.year_count_few') }}"
        data-many="{{ __('pages/projects.year_count_many') }}"
    >{{ __('pages/projects.year_count_'.$key, ['count' => $count]) }}</span>
</div>
