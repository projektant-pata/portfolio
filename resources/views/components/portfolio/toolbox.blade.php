@props([
    'tools',
    'variant' => 'cells',
    'chips' => null,
    'chipsLabel' => '',
    'chipsNote' => '',
    'link' => '',
    'linkLabel' => '',
])

@php
    $locale = app()->getLocale();
    $chips = $chips ?? collect();
    $groups = $variant === 'rows' ? \App\Models\Tool::groupByArea($tools) : collect();
@endphp

{{-- An empty toolbox draws nothing: the home section keeps its head, like
     every other home section on an empty database. --}}
@if ($tools->isNotEmpty() || $chips->isNotEmpty())

{{--
    The toolbox both pages share. `list` is the home silhouette — the weekly
    drivers as one flat, ungrouped run of rows; `rows` is the about-me
    one, a borderless grouped list built on the same listing rhythm as /blog
    and /projects so the three pages read as one family. Each group opens
    with the outlined heading, gradient hairline and monospace note the year
    heads on /projects use, then splits into two columns of rows.

    The rows are a static list, not links: no hover, no focus, no cursor.

    No brand logos on purpose: seventeen third-party marks force a second
    visual language onto the page, the dot carries the same information.
--}}
<div class="tk tk--{{ $variant }}">
    @if ($variant === 'rows')
        <div class="tk-groups">
            @foreach ($groups as $group)
                @php $note = \App\Models\Tool::noteFor($group, $locale); @endphp
                <div class="tk-group">
                    <div class="tk-ghead">
                        <h3>{{ $group->first()->getTranslation('group', $locale) }}</h3>
                        <span class="tk-gline"></span>
                        @if ($note !== '')
                            <span class="tk-gnote">{{ $note }}</span>
                        @endif
                    </div>
                    <div class="tk-rows">
                        @foreach ($group as $tool)
                            <div class="tk-row">
                                <x-portfolio.tool-item :tool="$tool" />
                                @if ($tool->state)
                                    <span class="tk-state" data-s="{{ $tool->state }}">{{ __('pages/toolbox.state.'.$tool->state) }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @elseif ($variant === 'list')
        {{-- Home: the same rows, minus the group heads and their rules — the
             section head already says what this is. --}}
        <div class="tk-rows">
            @foreach ($tools as $tool)
                <div class="tk-row">
                    <x-portfolio.tool-item :tool="$tool" />
                    @if ($tool->state)
                        <span class="tk-state" data-s="{{ $tool->state }}">{{ __('pages/toolbox.state.'.$tool->state) }}</span>
                    @endif
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
                <div class="tk-foot-chips">
                    <b>{{ $chipsLabel }}</b>
                    <div class="tk-chips">
                        @foreach ($chips as $chip)
                            <span class="tk-chip">{{ $chip->name }}</span>
                        @endforeach
                    </div>
                    @if ($chipsNote !== '')
                        <span class="tk-chips-note">{{ $chipsNote }}</span>
                    @endif
                </div>
            @endif

            @if ($link !== '')
                <div class="tk-foot-side">
                    <a class="tk-link" href="{{ $link }}">{{ $linkLabel }}</a>
                </div>
            @endif
        </div>
    @endif
</div>
@endif
