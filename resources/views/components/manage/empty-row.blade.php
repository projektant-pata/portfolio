@props(['colspan', 'message'])

<flux:table.row>
    <flux:table.cell colspan="{{ $colspan }}">
        <p class="manage-empty">{{ $message }}</p>
    </flux:table.cell>
</flux:table.row>
