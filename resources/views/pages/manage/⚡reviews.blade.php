<?php

use App\Models\Review;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage Reviews')] class extends Component {
    public string $search = '';

    public ?string $editingId = null;
    public string $name = '';
    public array $position = ['en' => '', 'cs' => ''];
    public array $text = ['en' => '', 'cs' => ''];

    public ?string $deletingId = null;

    #[Computed]
    public function reviews(): \Illuminate\Support\Collection
    {
        return Review::query()
            ->when($this->search, fn ($q) => $q->whereRaw('lower(name) LIKE lower(?)', ['%'.addcslashes($this->search, '%_\\').'%']))
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

        $reviews = Review::orderBy('sort_order')->get();
        $item = $reviews->firstWhere('id', $id);

        if (! $item) {
            return;
        }

        $reviews = $reviews->reject(fn ($r) => $r->id === $id)->values();
        $reviews->splice($position, 0, [$item]);
        $reviews->each(fn ($r, $i) => Review::where('id', $r->id)->update(['sort_order' => $i]));

        unset($this->reviews);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modal('form')->show();
    }

    public function openEdit(string $id): void
    {
        $review = Review::findOrFail($id);
        $this->editingId = $id;
        $this->name = $review->name;
        $this->position = array_merge(['en' => '', 'cs' => ''], $review->position ?? []);
        $this->text = array_merge(['en' => '', 'cs' => ''], $review->text ?? []);
        $this->modal('form')->show();
    }

    public function save(): void
    {
        $review = $this->editingId ? Review::findOrFail($this->editingId) : null;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'position' => ['nullable', 'array'],
            'position.en' => ['nullable', 'string', 'max:150'],
            'position.cs' => ['nullable', 'string', 'max:150'],
            'text' => ['required', 'array'],
            'text.en' => ['required', 'string', 'max:1000'],
            'text.cs' => ['nullable', 'string', 'max:1000'],
        ]);

        $data = [
            'name' => $validated['name'],
            'position' => array_filter($validated['position'] ?? [], fn ($v) => filled($v)) ?: null,
            'text' => array_filter($validated['text'], fn ($v) => filled($v)),
        ];

        if ($review) {
            $review->update($data);
        } else {
            $data['sort_order'] = (int) Review::max('sort_order') + 1;
            Review::create($data);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->reviews);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Review::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->reviews);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->position = ['en' => '', 'cs' => ''];
        $this->text = ['en' => '', 'cs' => ''];
        $this->resetValidation();
    }
}; ?>

<div class="manage-page p-6 space-y-6">

    <x-manage.page-header title="Reviews" subtitle="Testimonials shown on the homepage">
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">Add review</flux:button>
    </x-manage.page-header>

    <x-manage.search-input placeholder="Search by name…" />

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column></flux:table.column>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Position (EN)</flux:table.column>
            <flux:table.column>Updated</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows wire:sort="reorder">
            @forelse ($this->reviews as $review)
                <flux:table.row wire:key="{{ $review->id }}" wire:sort:item="{{ $review->id }}">
                    <flux:table.cell wire:sort:handle class="manage-drag-handle">
                        <x-manage.drag-handle />
                    </flux:table.cell>
                    <flux:table.cell variant="strong">{{ $review->name }}</flux:table.cell>
                    <flux:table.cell>{{ $review->position['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $review->updated_at->format('d.m.Y') }}</flux:table.cell>
                    <flux:table.cell wire:sort:ignore>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" aria-label="Edit review" wire:click="openEdit('{{ $review->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" aria-label="Delete review" wire:click="confirmDelete('{{ $review->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <x-manage.empty-row colspan="5" message="No reviews found." />
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[600px]">
        <flux:heading>{{ $editingId ? 'Edit review' : 'New review' }}</flux:heading>
        <flux:text class="mt-1 mb-5">Fill in the details below.</flux:text>

        <form wire:submit="save" class="space-y-4">
            <flux:field>
                <flux:label>Name <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                <flux:input wire:model="name" placeholder="e.g. Petr Machovec" />
                <flux:error name="name" />
            </flux:field>

            {{-- Language tabs --}}
            <x-manage.locale-tabs>
                <x-slot:en>
                    <flux:field>
                        <flux:label>Position</flux:label>
                        <flux:input wire:model="position.en" placeholder="e.g. Co-founder of Prezz" />
                        <flux:error name="position.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Quote <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:textarea wire:model="text.en" placeholder="“Richard always delivers…”" rows="4" />
                        <flux:error name="text.en" />
                    </flux:field>
                </x-slot:en>
                <x-slot:cs>
                    <flux:field>
                        <flux:label>Position</flux:label>
                        <flux:input wire:model="position.cs" placeholder="např. Spoluzakladatel Prezz" />
                        <flux:error name="position.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Quote</flux:label>
                        <flux:textarea wire:model="text.cs" placeholder="„Richard vždy dodává…“" rows="4" />
                        <flux:error name="text.cs" />
                    </flux:field>
                </x-slot:cs>
            </x-manage.locale-tabs>

            <x-manage.modal-footer :editing="(bool) $editingId" />
        </form>
    </flux:modal>

    <x-manage.delete-modal entity="review" />

</div>
