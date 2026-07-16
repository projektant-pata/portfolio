@props(['selected' => [], 'badges'])

<div class="col-span-2 space-y-3">
    <div class="flex items-center justify-between">
        <span class="manage-section-label text-sm font-medium">Badges</span>
        <flux:button size="sm" icon="plus" wire:click.prevent="addBadge" class="btn-gold-subtle">Add badge</flux:button>
    </div>
    @foreach ($selected as $i => $badgeId)
        <div class="flex gap-2 items-center" wire:key="badge-{{ $i }}">
            <flux:select wire:model="selectedBadgeIds.{{ $i }}" class="flex-1">
                <flux:select.option value="">— select badge —</flux:select.option>
                @foreach ($badges as $badge)
                    <flux:select.option value="{{ $badge->id }}">{{ $badge->name['en'] ?? $badge->slug }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:button size="sm" variant="subtle" icon="x-mark" wire:click.prevent="removeBadge({{ $i }})" class="btn-muted-icon" />
        </div>
    @endforeach
</div>
