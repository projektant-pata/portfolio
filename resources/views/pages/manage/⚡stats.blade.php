<?php

use App\Models\Stat;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage Stats')] class extends Component {
    public string $search = '';

    public ?string $editingId = null;
    public array $value = ['en' => '', 'cs' => ''];
    public array $text = ['en' => '', 'cs' => ''];
    public string $valueId = '';
    public string $source = '';

    public ?string $deletingId = null;

    #[Computed]
    public function stats(): \Illuminate\Support\Collection
    {
        return Stat::query()
            ->when($this->search, fn ($q) => $q->whereRaw("lower(text->>'en') LIKE lower(?)", ['%'.addcslashes($this->search, '%_\\').'%']))
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

        $stats = Stat::orderBy('sort_order')->get();
        $item = $stats->firstWhere('id', $id);

        if (! $item) {
            return;
        }

        $stats = $stats->reject(fn ($s) => $s->id === $id)->values();
        $stats->splice($position, 0, [$item]);
        $stats->each(fn ($s, $i) => Stat::where('id', $s->id)->update(['sort_order' => $i]));

        unset($this->stats);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modal('form')->show();
    }

    public function openEdit(string $id): void
    {
        $stat = Stat::findOrFail($id);
        $this->editingId = $id;
        $this->value = array_merge(['en' => '', 'cs' => ''], $stat->value ?? []);
        $this->text = array_merge(['en' => '', 'cs' => ''], $stat->text ?? []);
        $this->valueId = $stat->value_id ?? '';
        $this->source = $stat->source ?? '';
        $this->modal('form')->show();
    }

    public function save(): void
    {
        $stat = $this->editingId ? Stat::findOrFail($this->editingId) : null;

        $validated = $this->validate([
            'value' => ['nullable', 'array'],
            'value.en' => ['nullable', 'string', 'max:50'],
            'value.cs' => ['nullable', 'string', 'max:50'],
            'text' => ['required', 'array'],
            'text.en' => ['required', 'string', 'max:100'],
            'text.cs' => ['nullable', 'string', 'max:100'],
            'valueId' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'in:age,years_experience'],
        ]);

        $data = [
            'value' => array_filter($validated['value'] ?? [], fn ($v) => filled($v)) ?: null,
            'text' => array_filter($validated['text'], fn ($v) => filled($v)),
            'value_id' => $validated['valueId'] ?: null,
            'source' => $validated['source'] ?: null,
        ];

        if ($stat) {
            $stat->update($data);
        } else {
            $data['sort_order'] = (int) Stat::max('sort_order') + 1;
            Stat::create($data);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->stats);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Stat::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->stats);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->value = ['en' => '', 'cs' => ''];
        $this->text = ['en' => '', 'cs' => ''];
        $this->valueId = '';
        $this->source = '';
        $this->resetValidation();
    }
}; ?>

<div class="manage-page p-6 space-y-6">

    <x-manage.page-header title="Stats" subtitle="Homepage & about-me stat cards (first four show on the homepage)">
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">Add stat</flux:button>
    </x-manage.page-header>

    <x-manage.search-input placeholder="Search by caption…" />

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column></flux:table.column>
            <flux:table.column>Value</flux:table.column>
            <flux:table.column>Caption (EN)</flux:table.column>
            <flux:table.column>Source</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows wire:sort="reorder">
            @forelse ($this->stats as $stat)
                <flux:table.row wire:key="{{ $stat->id }}" wire:sort:item="{{ $stat->id }}">
                    <flux:table.cell wire:sort:handle class="manage-drag-handle">
                        <x-manage.drag-handle />
                    </flux:table.cell>
                    <flux:table.cell variant="strong">
                        @if ($stat->source)
                            <span class="font-mono text-xs manage-note">auto: {{ $stat->source }}</span>
                        @else
                            {{ $stat->value['en'] ?? '—' }}
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $stat->text['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($stat->value_id)
                            <span class="font-mono text-xs">#{{ $stat->value_id }}</span>
                        @else
                            —
                        @endif
                    </flux:table.cell>
                    <flux:table.cell wire:sort:ignore>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" aria-label="Edit stat" wire:click="openEdit('{{ $stat->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" aria-label="Delete stat" wire:click="confirmDelete('{{ $stat->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <x-manage.empty-row colspan="5" message="No stats found." />
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[560px]">
        <flux:heading>{{ $editingId ? 'Edit stat' : 'New stat' }}</flux:heading>
        <flux:text class="mt-1 mb-5">The caption is required. Leave the value blank when using an automatic source.</flux:text>

        <form wire:submit="save" class="space-y-4">
            {{-- Language tabs --}}
            <x-manage.locale-tabs>
                <x-slot:en>
                    <flux:field>
                        <flux:label>Value</flux:label>
                        <flux:input wire:model="value.en" placeholder="e.g. 5+" />
                        <flux:error name="value.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Caption <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:input wire:model="text.en" placeholder="e.g. Projects Completed" />
                        <flux:error name="text.en" />
                    </flux:field>
                </x-slot:en>
                <x-slot:cs>
                    <flux:field>
                        <flux:label>Value</flux:label>
                        <flux:input wire:model="value.cs" placeholder="např. 5+" />
                        <flux:error name="value.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Caption</flux:label>
                        <flux:input wire:model="text.cs" placeholder="např. Projektů dokončeno" />
                        <flux:error name="text.cs" />
                    </flux:field>
                </x-slot:cs>
            </x-manage.locale-tabs>

            {{-- Non-translatable fields --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Automatic source</flux:label>
                    <flux:select wire:model="source">
                        <flux:select.option value="">— static value —</flux:select.option>
                        <flux:select.option value="age">Age (auto)</flux:select.option>
                        <flux:select.option value="years_experience">Years of experience (auto)</flux:select.option>
                    </flux:select>
                    <flux:error name="source" />
                </flux:field>

                <flux:field>
                    <flux:label>Live value ID</flux:label>
                    <flux:input wire:model="valueId" placeholder="e.g. elo, github-repos" />
                    <flux:description>Filled client-side (chess elo, GitHub repos).</flux:description>
                    <flux:error name="valueId" />
                </flux:field>
            </div>

            <x-manage.modal-footer :editing="(bool) $editingId" />
        </form>
    </flux:modal>

    <x-manage.delete-modal entity="stat" />

</div>
