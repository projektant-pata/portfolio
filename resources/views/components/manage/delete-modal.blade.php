@props(['entity'])

<flux:modal name="delete" class="md:w-[400px]">
    <flux:heading>Delete {{ $entity }}?</flux:heading>
    <flux:text class="mt-2 mb-6">This action cannot be undone.</flux:text>
    <div class="flex justify-end gap-2">
        <flux:button x-on:click="$flux.modal('delete').close()">Cancel</flux:button>
        <flux:button wire:click="delete" variant="danger">Delete</flux:button>
    </div>
</flux:modal>
