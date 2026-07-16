@props(['title', 'subtitle' => null])

<div class="flex items-center justify-between">
    <div>
        <h1 class="manage-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="manage-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    {{ $slot }}
</div>
