@props([
    'tools',
    'variant' => 'cells',
    'chips' => null,
    'chipsLabel' => '',
    'link' => '',
    'linkLabel' => '',
])

@php
    $locale = app()->getLocale();
    $chips = $chips ?? collect();
@endphp

{{-- An empty toolbox draws no frame: the home section keeps its head, like
     every other home section on an empty database. --}}
@if ($tools->isNotEmpty() || $chips->isNotEmpty())

{{--
    The toolbox both pages share. `cells` is the home silhouette — eight
    weekly drivers in hairline-divided cells; `cols` is the about-me one — the
    same atom grouped into four labelled columns. The footer carries the
    chip-only rows (also in rotation / learning now) and an optional link.

    No brand logos on purpose: ten third-party marks force a second visual
    language onto the page, the 6px dot carries the same colour information.
--}}
<div class="tk">
    @if ($variant === 'cols')
        <div class="tk-cols">
            @foreach (\App\Models\Tool::groupColumns($tools) as $column)
                <div class="tk-col">
                    <h3>{{ $column->first()->getTranslation('group', $locale) }}</h3>
                    <div class="tk-list">
                        @foreach ($column as $tool)
                            <x-portfolio.tool-item :tool="$tool" />
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="tk-cells">
            @foreach ($tools as $tool)
                <div class="tk-cell">
                    <x-portfolio.tool-item :tool="$tool" />
                </div>
            @endforeach
        </div>
    @endif

    @if ($chips->isNotEmpty() || $link !== '')
        <div class="tk-foot">
            @if ($chips->isNotEmpty())
                <b>{{ $chipsLabel }}</b>
                <div class="tk-chips">
                    @foreach ($chips as $chip)
                        <span class="tk-chip">{{ $chip->name }}</span>
                    @endforeach
                </div>
            @endif

            @if ($link !== '')
                <a class="tk-link" href="{{ $link }}">{{ $linkLabel }}</a>
            @endif
        </div>
    @endif
</div>
@endif
