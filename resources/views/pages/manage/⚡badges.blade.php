<?php

use App\Models\Badge;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage Badges')] class extends Component {
    public string $search = '';

    public ?string $editingId = null;
    public array $name = ['en' => '', 'cs' => ''];
    public string $slug = '';
    public string $color = '';

    public ?string $deletingId = null;

    #[Computed]
    public function badges(): \Illuminate\Support\Collection
    {
        return Badge::query()
            ->when($this->search, fn ($q) => $q->whereRaw("name->>'en' ILIKE ?", ["%{$this->search}%"]))
            ->orderByRaw("name->>'en'")
            ->get();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modal('form')->show();
    }

    public function openEdit(string $id): void
    {
        $badge = Badge::findOrFail($id);
        $this->editingId = $id;
        $this->name = array_merge(['en' => '', 'cs' => ''], $badge->name ?? []);
        $this->slug = $badge->slug;
        $this->color = $badge->color ?? '';
        $this->modal('form')->show();
    }

    public function updatedNameEn(string $value): void
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($value);
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:100'],
            'name.cs' => ['nullable', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', $this->editingId
                ? \Illuminate\Validation\Rule::unique('badges', 'slug')->ignore($this->editingId)
                : 'unique:badges,slug'],
            'color' => ['nullable', 'string', 'max:50'],
        ]);

        $data = [
            'name' => array_filter($validated['name'], fn ($v) => filled($v)),
            'slug' => $validated['slug'],
            'color' => $validated['color'] ?: null,
        ];

        if ($this->editingId) {
            Badge::findOrFail($this->editingId)->update($data);
        } else {
            Badge::create($data);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->badges);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Badge::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->badges);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = ['en' => '', 'cs' => ''];
        $this->slug = '';
        $this->color = '';
        $this->resetValidation();
    }
}; ?>

<div style="font-family: var(--font-body); color: var(--c-fg);" class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 style="font-size: 2rem; font-weight: 600; color: var(--c-fg);">Badges</h1>
            <p style="color: var(--c-muted); font-size: 0.875rem; margin-top: 0.2rem;">Skill and technology tags</p>
        </div>
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">
            Add badge
        </flux:button>
    </div>

    {{-- Search --}}
    <flux:input wire:model.live.debounce="search" placeholder="Search by name…" icon="magnifying-glass" class="max-w-xs" />

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Name (EN)</flux:table.column>
            <flux:table.column>Name (CS)</flux:table.column>
            <flux:table.column>Slug</flux:table.column>
            <flux:table.column>Color</flux:table.column>
            <flux:table.column>Updated</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->badges as $badge)
                <flux:table.row wire:key="{{ $badge->id }}">
                    <flux:table.cell variant="strong">{{ $badge->name['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $badge->name['cs'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $badge->slug }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($badge->color)
                            <flux:badge size="sm" color="{{ $badge->color }}" inset="top bottom">{{ $badge->color }}</flux:badge>
                        @else
                            —
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $badge->updated_at->format('d.m.Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" wire:click="openEdit('{{ $badge->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" wire:click="confirmDelete('{{ $badge->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <p style="color: var(--c-muted); text-align: center; padding: 2rem 0;">No badges found.</p>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[560px]">
        <flux:heading>{{ $editingId ? 'Edit badge' : 'New badge' }}</flux:heading>
        <flux:text class="mt-1 mb-5">Fill in the details below.</flux:text>

        <form wire:submit="save" class="space-y-4">
            {{-- Language tabs --}}
            <div x-data="{ locale: 'en' }">
                <div class="flex gap-1 mb-4 p-1 rounded-lg" style="background: var(--c-surface-raised, rgba(0,0,0,0.08));">
                    <button type="button" x-on:click="locale = 'en'"
                        :class="locale === 'en' ? 'shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
                        class="flex-1 px-3 py-1.5 rounded-md text-sm transition-all"
                        style="background: transparent;" :style="locale === 'en' ? 'background: var(--c-surface)' : ''">
                        🇬🇧 English
                    </button>
                    <button type="button" x-on:click="locale = 'cs'"
                        :class="locale === 'cs' ? 'shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
                        class="flex-1 px-3 py-1.5 rounded-md text-sm transition-all"
                        style="background: transparent;" :style="locale === 'cs' ? 'background: var(--c-surface)' : ''">
                        🇨🇿 Czech
                    </button>
                </div>

                <div x-show="locale === 'en'" class="space-y-4">
                    <flux:field>
                        <flux:label>Name <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:input wire:model="name.en" wire:model.live.debounce="name.en" placeholder="e.g. Laravel" />
                        <flux:error name="name.en" />
                    </flux:field>
                </div>

                <div x-show="locale === 'cs'" class="space-y-4">
                    <flux:field>
                        <flux:label>Name</flux:label>
                        <flux:input wire:model="name.cs" placeholder="např. Laravel" />
                        <flux:error name="name.cs" />
                    </flux:field>
                </div>
            </div>

            {{-- Non-translatable fields --}}
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Slug <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                    <flux:input wire:model="slug" placeholder="e.g. laravel" />
                    <flux:error name="slug" />
                </flux:field>

                <flux:field>
                    <flux:label>Color</flux:label>
                    <flux:select wire:model="color">
                        <flux:select.option value="">— none —</flux:select.option>
                        <flux:select.option value="red">Red</flux:select.option>
                        <flux:select.option value="orange">Orange</flux:select.option>
                        <flux:select.option value="yellow">Yellow</flux:select.option>
                        <flux:select.option value="green">Green</flux:select.option>
                        <flux:select.option value="blue">Blue</flux:select.option>
                        <flux:select.option value="purple">Purple</flux:select.option>
                        <flux:select.option value="zinc">Zinc</flux:select.option>
                    </flux:select>
                    <flux:error name="color" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button x-on:click="$flux.modal('form').close()">Cancel</flux:button>
                <flux:button type="submit" class="btn-gold">{{ $editingId ? 'Save changes' : 'Create' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete modal --}}
    <flux:modal name="delete" class="md:w-[400px]">
        <flux:heading>Delete badge?</flux:heading>
        <flux:text class="mt-2 mb-6">This action cannot be undone.</flux:text>
        <div class="flex justify-end gap-2">
            <flux:button x-on:click="$flux.modal('delete').close()">Cancel</flux:button>
            <flux:button wire:click="delete" variant="danger">Delete</flux:button>
        </div>
    </flux:modal>

</div>
