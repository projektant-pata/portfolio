@props(['en', 'cs'])

<div x-data="{ locale: 'en' }">
    <div class="locale-tabs flex gap-1 mb-4 p-1 rounded-lg">
        <button type="button" x-on:click="locale = 'en'"
            :class="locale === 'en' ? 'locale-tab--active shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
            class="locale-tab flex-1 px-3 py-1.5 rounded-md text-sm transition-all">
            🇬🇧 English
        </button>
        <button type="button" x-on:click="locale = 'cs'"
            :class="locale === 'cs' ? 'locale-tab--active shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
            class="locale-tab flex-1 px-3 py-1.5 rounded-md text-sm transition-all">
            🇨🇿 Czech
        </button>
    </div>

    <div x-show="locale === 'en'" class="space-y-4">{{ $en }}</div>
    <div x-show="locale === 'cs'" class="space-y-4">{{ $cs }}</div>
</div>
