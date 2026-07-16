@props(['value', 'text', 'valueId' => null])

<div class="stats-cards-card">
    <h3><span @if ($valueId) id="{{ $valueId }}" @endif>{{ $value }}</span></h3>
    <p>{{ $text }}</p>
</div>
