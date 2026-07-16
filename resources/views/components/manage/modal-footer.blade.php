@props(['editing' => false, 'modal' => 'form'])

<div class="flex justify-end gap-2 pt-2">
    <flux:button x-on:click="$flux.modal('{{ $modal }}').close()">Cancel</flux:button>
    <flux:button type="submit" class="btn-gold">{{ $editing ? 'Save changes' : 'Create' }}</flux:button>
</div>
