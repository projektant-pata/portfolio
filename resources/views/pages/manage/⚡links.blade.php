<?php

use App\Models\Link;
use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage Links')] class extends Component {
    public string $search = '';

    public ?string $editingId = null;
    public string $project_id = '';
    public string $url = '';
    public array $alt = ['en' => '', 'cs' => ''];
    public string $img_url = '';

    public ?string $deletingId = null;

    #[Computed]
    public function links(): \Illuminate\Support\Collection
    {
        return Link::with('project')
            ->when($this->search, fn ($q) => $q->where('url', 'ILIKE', "%{$this->search}%"))
            ->orderByRaw("alt->>'en'")
            ->get();
    }

    #[Computed(persist: true)]
    public function allProjects(): \Illuminate\Support\Collection
    {
        return Project::orderByRaw("header->>'en'")->get();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modal('form')->show();
    }

    public function openEdit(string $id): void
    {
        $link = Link::findOrFail($id);
        $this->editingId = $id;
        $this->project_id = $link->project_id;
        $this->url = $link->url;
        $this->alt = array_merge(['en' => '', 'cs' => ''], $link->alt ?? []);
        $this->img_url = $link->img_url ?? '';
        $this->modal('form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'project_id' => ['required', 'uuid', 'exists:projects,id'],
            'url' => ['required', 'url', 'max:500'],
            'alt' => ['nullable', 'array'],
            'alt.en' => ['nullable', 'string', 'max:100'],
            'alt.cs' => ['nullable', 'string', 'max:100'],
            'img_url' => ['nullable', 'string', 'max:500'],
        ]);

        $data = [
            'project_id' => $validated['project_id'],
            'url' => $validated['url'],
            'alt' => array_filter($validated['alt'] ?? [], fn ($v) => filled($v)) ?: null,
            'img_url' => $validated['img_url'] ?: null,
        ];

        if ($this->editingId) {
            Link::findOrFail($this->editingId)->update($data);
        } else {
            Link::create($data);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->links);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Link::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->links);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->project_id = '';
        $this->url = '';
        $this->alt = ['en' => '', 'cs' => ''];
        $this->img_url = '';
        $this->resetValidation();
    }
}; ?>

<div style="font-family: var(--font-body); color: var(--c-fg);" class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 style="font-size: 2rem; font-weight: 600; color: var(--c-fg);">Links</h1>
            <p style="color: var(--c-muted); font-size: 0.875rem; margin-top: 0.2rem;">Project external links</p>
        </div>
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">
            Add link
        </flux:button>
    </div>

    {{-- Search --}}
    <flux:input wire:model.live.debounce="search" placeholder="Search by URL…" icon="magnifying-glass" class="max-w-xs" />

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>URL</flux:table.column>
            <flux:table.column>Alt (EN)</flux:table.column>
            <flux:table.column>Alt (CS)</flux:table.column>
            <flux:table.column>Project</flux:table.column>
            <flux:table.column>Updated</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->links as $link)
                <flux:table.row wire:key="{{ $link->id }}">
                    <flux:table.cell variant="strong" class="max-w-[200px] truncate">{{ $link->url }}</flux:table.cell>
                    <flux:table.cell>{{ $link->alt['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $link->alt['cs'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $link->project?->header['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $link->updated_at->format('d.m.Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" wire:click="openEdit('{{ $link->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" wire:click="confirmDelete('{{ $link->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <p style="color: var(--c-muted); text-align: center; padding: 2rem 0;">No links found.</p>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[560px]">
        <flux:heading>{{ $editingId ? 'Edit link' : 'New link' }}</flux:heading>
        <flux:text class="mt-1 mb-5">Fill in the details below.</flux:text>

        <form wire:submit="save" class="space-y-4">
            <flux:field>
                <flux:label>Project <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                <flux:select wire:model="project_id">
                    <flux:select.option value="">— select project —</flux:select.option>
                    @foreach ($this->allProjects as $project)
                        <flux:select.option value="{{ $project->id }}">{{ $project->header['en'] ?? $project->slug }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="project_id" />
            </flux:field>

            <flux:field>
                <flux:label>URL <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                <flux:input wire:model="url" type="url" placeholder="https://…" />
                <flux:error name="url" />
            </flux:field>

            {{-- Language tabs for alt --}}
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
                        <flux:label>Alt text</flux:label>
                        <flux:input wire:model="alt.en" placeholder="e.g. Visit on GitHub" />
                        <flux:error name="alt.en" />
                    </flux:field>
                </div>

                <div x-show="locale === 'cs'" class="space-y-4">
                    <flux:field>
                        <flux:label>Alt text</flux:label>
                        <flux:input wire:model="alt.cs" placeholder="např. Zobrazit na GitHubu" />
                        <flux:error name="alt.cs" />
                    </flux:field>
                </div>
            </div>

            <flux:field>
                <flux:label>Icon URL</flux:label>
                <flux:input wire:model="img_url" placeholder="https://… or images/… (icon image)" />
                <flux:error name="img_url" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button x-on:click="$flux.modal('form').close()">Cancel</flux:button>
                <flux:button type="submit" class="btn-gold">{{ $editingId ? 'Save changes' : 'Create' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete modal --}}
    <flux:modal name="delete" class="md:w-[400px]">
        <flux:heading>Delete link?</flux:heading>
        <flux:text class="mt-2 mb-6">This action cannot be undone.</flux:text>
        <div class="flex justify-end gap-2">
            <flux:button x-on:click="$flux.modal('delete').close()">Cancel</flux:button>
            <flux:button wire:click="delete" variant="danger">Delete</flux:button>
        </div>
    </flux:modal>

</div>
