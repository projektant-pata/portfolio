@props(['links' => [], 'translatableAlt' => false, 'withKind' => false])

<div class="col-span-2 space-y-3">
    <div class="flex items-center justify-between">
        <span class="manage-section-label text-sm font-medium">Links</span>
        <flux:button size="sm" icon="plus" wire:click.prevent="addLink" class="btn-gold-subtle">Add link</flux:button>
    </div>
    @foreach ($links as $i => $link)
        <div class="manage-link-box space-y-3 rounded-lg p-4" wire:key="link-{{ $i }}">
            <div class="flex gap-2 items-center">
                <flux:input wire:model="links.{{ $i }}.url" type="url" placeholder="URL — https://…" class="flex-1" />
                <flux:button size="sm" variant="subtle" icon="x-mark" wire:click.prevent="removeLink({{ $i }})" class="btn-muted-icon" />
            </div>
            <flux:error name="links.{{ $i }}.url" />
            @if ($withKind)
                <flux:field>
                    <flux:label>Kind</flux:label>
                    <flux:select wire:model="links.{{ $i }}.kind">
                        <flux:select.option value="live">Live site</flux:select.option>
                        <flux:select.option value="repo">Source</flux:select.option>
                        <flux:select.option value="article">Write-up</flux:select.option>
                    </flux:select>
                    <flux:error name="links.{{ $i }}.kind" />
                </flux:field>
            @endif
            @if ($translatableAlt)
                <div class="grid grid-cols-2 gap-2">
                    <flux:input wire:model="links.{{ $i }}.alt.en" placeholder="Alt (EN)" />
                    <flux:input wire:model="links.{{ $i }}.alt.cs" placeholder="Alt (CS)" />
                </div>
                <div>
                    <flux:input wire:model="links.{{ $i }}.img_url" placeholder="https://… or images/… (icon)" />
                    <flux:error name="links.{{ $i }}.img_url" />
                </div>
            @else
                <flux:input wire:model="links.{{ $i }}.alt" placeholder="Alt text (optional)" />
                <div>
                    <flux:input wire:model="links.{{ $i }}.img_url" type="url" placeholder="Icon URL (optional)" />
                    <flux:error name="links.{{ $i }}.img_url" />
                </div>
            @endif
        </div>
    @endforeach
</div>
