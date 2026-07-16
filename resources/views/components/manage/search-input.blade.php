@props(['placeholder' => 'Search…', 'model' => 'search'])

<flux:input wire:model.live.debounce="{{ $model }}" :placeholder="$placeholder" icon="magnifying-glass" class="max-w-xs" />
