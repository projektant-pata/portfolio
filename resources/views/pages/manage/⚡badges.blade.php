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
            ->when($this->search, fn ($q) => $q->whereRaw("lower(name->>'en') LIKE lower(?)", ['%'.addcslashes($this->search, '%_\\').'%']))
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
        $badge = $this->editingId ? Badge::findOrFail($this->editingId) : null;

        $validated = $this->validate([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:100'],
            'name.cs' => ['nullable', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', \Illuminate\Validation\Rule::unique('badges', 'slug')->ignore($badge)],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $data = [
            'name' => array_filter($validated['name'], fn ($v) => filled($v)),
            'slug' => $validated['slug'],
            'color' => $validated['color'] ?: null,
        ];

        if ($badge) {
            $badge->update($data);
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

<div class="manage-page p-6 space-y-6">

    <x-manage.page-header title="Badges" subtitle="Skill and technology tags">
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">Add badge</flux:button>
    </x-manage.page-header>

    <x-manage.search-input placeholder="Search by name…" />

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
                            <span class="inline-flex items-center gap-2">
                                <span class="inline-block w-3 h-3 rounded-full" style="background: {{ $badge->color }}"></span>
                                <span class="font-mono text-xs">{{ $badge->color }}</span>
                            </span>
                        @else
                            —
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $badge->updated_at->format('d.m.Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" aria-label="Edit badge" wire:click="openEdit('{{ $badge->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" aria-label="Delete badge" wire:click="confirmDelete('{{ $badge->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <x-manage.empty-row colspan="6" message="No badges found." />
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[560px]">
        <flux:heading>{{ $editingId ? 'Edit badge' : 'New badge' }}</flux:heading>
        <flux:text class="mt-1 mb-5">Fill in the details below.</flux:text>

        <form wire:submit="save" class="space-y-4">
            {{-- Language tabs --}}
            <x-manage.locale-tabs>
                <x-slot:en>
                    <flux:field>
                        <flux:label>Name <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:input wire:model.live.debounce="name.en" placeholder="e.g. Laravel" />
                        <flux:error name="name.en" />
                    </flux:field>
                </x-slot:en>
                <x-slot:cs>
                    <flux:field>
                        <flux:label>Name</flux:label>
                        <flux:input wire:model="name.cs" placeholder="např. Laravel" />
                        <flux:error name="name.cs" />
                    </flux:field>
                </x-slot:cs>
            </x-manage.locale-tabs>

            {{-- Non-translatable fields --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Slug <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                    <flux:input wire:model="slug" placeholder="e.g. laravel" />
                    <flux:error name="slug" />
                </flux:field>

                <flux:field>
                    <flux:label>Color</flux:label>
                    <flux:select wire:model="color">
                        <flux:select.option value="">— none —</flux:select.option>
                        <flux:select.option value="#EAB308">Gold</flux:select.option>
                        <flux:select.option value="#F59E0B">Amber</flux:select.option>
                        <flux:select.option value="#F97316">Orange</flux:select.option>
                        <flux:select.option value="#34D399">Emerald</flux:select.option>
                        <flux:select.option value="#2DD4BF">Teal</flux:select.option>
                        <flux:select.option value="#38BDF8">Sky</flux:select.option>
                        <flux:select.option value="#60A5FA">Blue</flux:select.option>
                        <flux:select.option value="#818CF8">Indigo</flux:select.option>
                        <flux:select.option value="#A78BFA">Violet</flux:select.option>
                    </flux:select>
                    <flux:error name="color" />
                </flux:field>
            </div>

            <x-manage.modal-footer :editing="(bool) $editingId" />
        </form>
    </flux:modal>

    <x-manage.delete-modal entity="badge" />

</div>
