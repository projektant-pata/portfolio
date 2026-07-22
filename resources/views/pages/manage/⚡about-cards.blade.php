<?php

use App\Models\AboutCard;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage About cards')] class extends Component {
    public string $search = '';

    public ?string $editingId = null;
    public array $cardTitle = ['en' => '', 'cs' => ''];
    public array $text = ['en' => '', 'cs' => ''];

    public ?string $deletingId = null;

    #[Computed]
    public function cards(): \Illuminate\Support\Collection
    {
        return AboutCard::query()
            ->when($this->search, fn ($q) => $q->whereRaw("lower(title->>'en') LIKE lower(?)", ['%'.addcslashes($this->search, '%_\\').'%']))
            ->orderBy('sort_order')
            ->get();
    }

    public function mount(): void
    {
        if (request()->boolean('create')) {
            $this->openCreate();
        }
    }

    public function reorder(string $id, int $position): void
    {
        if (filled($this->search)) {
            return;
        }

        $cards = AboutCard::orderBy('sort_order')->get();
        $item = $cards->firstWhere('id', $id);

        if (! $item) {
            return;
        }

        $cards = $cards->reject(fn ($c) => $c->id === $id)->values();
        $cards->splice($position, 0, [$item]);
        $cards->each(fn ($c, $i) => AboutCard::where('id', $c->id)->update(['sort_order' => $i]));

        unset($this->cards);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modal('form')->show();
    }

    public function openEdit(string $id): void
    {
        $card = AboutCard::findOrFail($id);
        $this->editingId = $id;
        $this->cardTitle = array_merge(['en' => '', 'cs' => ''], $card->title ?? []);
        $this->text = array_merge(['en' => '', 'cs' => ''], $card->text ?? []);
        $this->modal('form')->show();
    }

    public function save(): void
    {
        $card = $this->editingId ? AboutCard::findOrFail($this->editingId) : null;

        $validated = $this->validate([
            'cardTitle' => ['required', 'array'],
            'cardTitle.en' => ['required', 'string', 'max:150'],
            'cardTitle.cs' => ['nullable', 'string', 'max:150'],
            'text' => ['required', 'array'],
            'text.en' => ['required', 'string', 'max:5000'],
            'text.cs' => ['nullable', 'string', 'max:5000'],
        ]);

        $data = [
            'title' => array_filter($validated['cardTitle'], fn ($v) => filled($v)),
            'text' => array_filter($validated['text'], fn ($v) => filled($v)),
        ];

        if ($card) {
            $card->update($data);
        } else {
            $data['sort_order'] = (int) AboutCard::max('sort_order') + 1;
            AboutCard::create($data);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->cards);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            AboutCard::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->cards);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->cardTitle = ['en' => '', 'cs' => ''];
        $this->text = ['en' => '', 'cs' => ''];
        $this->resetValidation();
    }
}; ?>

<div class="manage-page p-6 space-y-6">

    <x-manage.page-header title="About cards" subtitle="The story cards on the about-me page">
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">Add card</flux:button>
    </x-manage.page-header>

    <x-manage.search-input placeholder="Search by title…" />

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column></flux:table.column>
            <flux:table.column>Title (EN)</flux:table.column>
            <flux:table.column>Title (CS)</flux:table.column>
            <flux:table.column>Updated</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows wire:sort="reorder">
            @forelse ($this->cards as $card)
                <flux:table.row wire:key="{{ $card->id }}" wire:sort:item="{{ $card->id }}">
                    <flux:table.cell wire:sort:handle class="manage-drag-handle">
                        <x-manage.drag-handle />
                    </flux:table.cell>
                    <flux:table.cell variant="strong">{{ $card->title['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $card->title['cs'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $card->updated_at->format('d.m.Y') }}</flux:table.cell>
                    <flux:table.cell wire:sort:ignore>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" aria-label="Edit card" wire:click="openEdit('{{ $card->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" aria-label="Delete card" wire:click="confirmDelete('{{ $card->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <x-manage.empty-row colspan="5" message="No about cards found." />
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[640px]">
        <flux:heading>{{ $editingId ? 'Edit card' : 'New card' }}</flux:heading>
        <flux:text class="mt-1 mb-5">Text supports basic HTML (&lt;span&gt;, &lt;br&gt;, &lt;ul&gt;).</flux:text>

        <form wire:submit="save" class="space-y-4">
            {{-- Language tabs --}}
            <x-manage.locale-tabs>
                <x-slot:en>
                    <flux:field>
                        <flux:label>Title <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:input wire:model="cardTitle.en" placeholder="e.g. About me" />
                        <flux:error name="cardTitle.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Text <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:textarea wire:model="text.en" placeholder="Hi there! I'm…" rows="7" />
                        <flux:error name="text.en" />
                    </flux:field>
                </x-slot:en>
                <x-slot:cs>
                    <flux:field>
                        <flux:label>Title</flux:label>
                        <flux:input wire:model="cardTitle.cs" placeholder="např. O mně" />
                        <flux:error name="cardTitle.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Text</flux:label>
                        <flux:textarea wire:model="text.cs" placeholder="Ahoj! Jsem…" rows="7" />
                        <flux:error name="text.cs" />
                    </flux:field>
                </x-slot:cs>
            </x-manage.locale-tabs>

            <x-manage.modal-footer :editing="(bool) $editingId" />
        </form>
    </flux:modal>

    <x-manage.delete-modal entity="card" />

</div>
