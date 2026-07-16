<?php

use App\Models\Badge;
use App\Models\Link;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Manage Projects')] class extends Component {
    use WithFileUploads;

    public string $search = '';

    public ?string $editingId = null;
    public array $header = ['en' => '', 'cs' => ''];
    public array $description = ['en' => '', 'cs' => ''];
    public string $slug = '';
    public string $year = '';
    public string $img_url = '';
    public $imageFile = null;
    public array $links = [];
    public array $selectedBadgeIds = [];

    public ?string $deletingId = null;

    #[Computed]
    public function projects(): \Illuminate\Support\Collection
    {
        return Project::query()
            ->when($this->search, fn ($q) => $q->whereRaw("lower(header->>'en') LIKE lower(?)", ['%'.addcslashes($this->search, '%_\\').'%']))
            ->orderBy('sort_order')
            ->orderBy('year', 'desc')
            ->orderByRaw("header->>'en'")
            ->get();
    }

    public function reorder(string $id, int $position): void
    {
        // Reordering renumbers the whole table; a filtered subset would corrupt
        // positions of hidden rows, so only allow it when no filter is active.
        if (filled($this->search)) {
            return;
        }

        $projects = Project::query()
            ->orderBy('sort_order')
            ->orderBy('year', 'desc')
            ->orderByRaw("header->>'en'")
            ->get();

        $item = $projects->firstWhere('id', $id);

        if (! $item) {
            return;
        }

        $projects = $projects->reject(fn ($p) => $p->id === $id)->values();
        $projects->splice($position, 0, [$item]);

        $projects->each(fn ($p, $i) => Project::where('id', $p->id)->update(['sort_order' => $i]));

        unset($this->projects);
    }

    #[Computed]
    public function allBadges(): \Illuminate\Support\Collection
    {
        return Badge::orderByRaw("name->>'en'")->get();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modal('form')->show();
    }

    public function openEdit(string $id): void
    {
        $project = Project::with(['badges', 'links'])->findOrFail($id);
        $this->editingId = $id;
        $this->header = array_merge(['en' => '', 'cs' => ''], $project->header ?? []);
        $this->description = array_merge(['en' => '', 'cs' => ''], $project->description ?? []);
        $this->slug = $project->slug;
        $this->year = (string) $project->year;
        $this->img_url = $project->img_url ?? '';
        $this->imageFile = null;
        $this->links = $project->links->map(fn ($l) => [
            'url' => $l->url,
            'alt' => array_merge(['en' => '', 'cs' => ''], $l->alt ?? []),
            'img_url' => $l->img_url ?? '',
        ])->toArray();
        $this->selectedBadgeIds = $project->badges->pluck('id')->toArray();
        $this->modal('form')->show();
    }

    public function updatedHeaderEn(string $value): void
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($value);
        }
    }

    public function addLink(): void
    {
        $this->links[] = ['url' => '', 'alt' => ['en' => '', 'cs' => ''], 'img_url' => ''];
    }

    public function removeLink(int $index): void
    {
        array_splice($this->links, $index, 1);
        $this->links = array_values($this->links);
    }

    public function addBadge(): void
    {
        $this->selectedBadgeIds[] = '';
    }

    public function removeBadge(int $index): void
    {
        array_splice($this->selectedBadgeIds, $index, 1);
        $this->selectedBadgeIds = array_values($this->selectedBadgeIds);
    }

    public function save(): void
    {
        $project = $this->editingId ? Project::findOrFail($this->editingId) : null;

        $validated = $this->validate([
            'header' => ['required', 'array'],
            'header.en' => ['required', 'string', 'max:255'],
            'header.cs' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string', 'max:1000'],
            'description.cs' => ['nullable', 'string', 'max:1000'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('projects', 'slug')->ignore($project)],
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'imageFile' => ['nullable', 'image', 'max:4096'],
            'links' => ['nullable', 'array'],
            'links.*.url' => ['nullable', 'url', 'max:500'],
            'links.*.alt' => ['nullable', 'array'],
            'links.*.alt.en' => ['nullable', 'string', 'max:100'],
            'links.*.alt.cs' => ['nullable', 'string', 'max:100'],
            'links.*.img_url' => ['nullable', 'string', 'max:500'],
            'selectedBadgeIds' => ['nullable', 'array'],
            'selectedBadgeIds.*' => ['nullable', 'uuid', 'exists:badges,id'],
        ]);

        $badgeIds = collect($this->selectedBadgeIds)
            ->filter(fn ($id) => filled($id))
            ->unique()
            ->values()
            ->toArray();

        $filteredLinks = collect($this->links)
            ->filter(fn ($l) => filled($l['url'] ?? ''))
            ->values()
            ->toArray();

        if ($this->imageFile) {
            $path = $this->imageFile->store('projects', 'public');

            if ($project?->img_url && str_starts_with($project->img_url, 'storage/')) {
                Storage::disk('public')->delete(substr($project->img_url, strlen('storage/')));
            }

            $this->img_url = 'storage/' . $path;
        } else {
            // img_url is never trusted from the client; derive it from the stored model.
            $this->img_url = $project?->img_url ?? '';
        }

        $data = [
            'header' => array_filter($validated['header'], fn ($v) => filled($v)),
            'description' => array_filter($validated['description'] ?? [], fn ($v) => filled($v)) ?: null,
            'slug' => $validated['slug'],
            'year' => (int) $validated['year'],
            'img_url' => $this->img_url ?: null,
        ];

        if ($project) {
            $project->update($data);
            $project->badges()->sync($badgeIds);
            $project->links()->delete();
        } else {
            $project = Project::create($data);
            $project->badges()->sync($badgeIds);
        }

        foreach ($filteredLinks as $linkData) {
            $project->links()->create([
                'url' => $linkData['url'],
                'alt' => array_filter($linkData['alt'] ?? [], fn ($v) => filled($v)) ?: null,
                'img_url' => $linkData['img_url'] ?: null,
            ]);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->projects);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Project::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->projects);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->header = ['en' => '', 'cs' => ''];
        $this->description = ['en' => '', 'cs' => ''];
        $this->slug = '';
        $this->year = '';
        $this->img_url = '';
        $this->imageFile = null;
        $this->links = [];
        $this->selectedBadgeIds = [];
        $this->resetValidation();
    }
}; ?>

<div style="font-family: var(--font-sans); color: var(--c-fg);" class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 style="font-size: 2rem; font-weight: 600; color: var(--c-fg);">Projects</h1>
            <p style="color: var(--c-muted); font-size: 0.875rem; margin-top: 0.2rem;">Portfolio projects</p>
        </div>
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">
            Add project
        </flux:button>
    </div>

    {{-- Search --}}
    <flux:input wire:model.live.debounce="search" placeholder="Search by title…" icon="magnifying-glass" class="max-w-xs" />

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column></flux:table.column>
            <flux:table.column>Header</flux:table.column>
            <flux:table.column>Year</flux:table.column>
            <flux:table.column>Slug</flux:table.column>
            <flux:table.column>Updated</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows wire:sort="reorder">
            @forelse ($this->projects as $project)
                <flux:table.row wire:key="{{ $project->id }}" wire:sort:item="{{ $project->id }}">
                    <flux:table.cell wire:sort:handle style="cursor: grab; color: var(--c-muted); width: 1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </flux:table.cell>
                    <flux:table.cell variant="strong">{{ $project->header['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $project->year }}</flux:table.cell>
                    <flux:table.cell>{{ $project->slug }}</flux:table.cell>
                    <flux:table.cell>{{ $project->updated_at->format('d.m.Y') }}</flux:table.cell>
                    <flux:table.cell wire:sort:ignore>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" wire:click="openEdit('{{ $project->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" wire:click="confirmDelete('{{ $project->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <p style="color: var(--c-muted); text-align: center; padding: 2rem 0;">No projects found.</p>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[700px]">
        <flux:heading>{{ $editingId ? 'Edit project' : 'New project' }}</flux:heading>
        <flux:text class="mt-1 mb-5">Fill in the details below.</flux:text>

        <form wire:submit="save" class="space-y-4">
            {{-- Language tabs --}}
            <div x-data="{ locale: 'en' }">
                <div class="flex gap-1 mb-4 p-1 rounded-lg" style="background: var(--c-surface-sunken);">
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
                        <flux:label>Header <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:input wire:model="header.en" wire:model.live.debounce="header.en" placeholder="e.g. Portfolio Website" />
                        <flux:error name="header.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description.en" placeholder="Short project description…" rows="4" />
                        <flux:error name="description.en" />
                    </flux:field>
                </div>

                <div x-show="locale === 'cs'" class="space-y-4">
                    <flux:field>
                        <flux:label>Header</flux:label>
                        <flux:input wire:model="header.cs" placeholder="např. Portfoliový web" />
                        <flux:error name="header.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description.cs" placeholder="Krátký popis projektu…" rows="4" />
                        <flux:error name="description.cs" />
                    </flux:field>
                </div>
            </div>

            {{-- Non-translatable fields --}}
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Slug <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                    <flux:input wire:model="slug" placeholder="e.g. portfolio-website" />
                    <flux:error name="slug" />
                </flux:field>

                <flux:field>
                    <flux:label>Year <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                    <flux:input wire:model="year" type="number" placeholder="e.g. 2026" min="1900" max="2100" />
                    <flux:error name="year" />
                </flux:field>

                <flux:field class="col-span-2">
                    <flux:label>Image</flux:label>
                    <flux:input wire:model="imageFile" type="file" accept="image/*" />
                    <flux:error name="imageFile" />
                    @if ($img_url && ! $imageFile)
                        <p class="text-xs mt-1" style="color: var(--c-muted);">Current: {{ $img_url }}</p>
                    @endif
                    @if ($imageFile)
                        <img src="{{ $imageFile->temporaryUrl() }}" class="mt-2 h-16 rounded object-cover" />
                    @endif
                </flux:field>

                {{-- Links --}}
                <div class="col-span-2 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium" style="color: var(--c-fg);">Links</span>
                        <flux:button size="sm" icon="plus" wire:click.prevent="addLink" class="btn-gold-subtle">Add link</flux:button>
                    </div>
                    @foreach ($links as $i => $link)
                        <div class="space-y-3 rounded-lg p-4" style="border: 1px solid var(--c-primary-fade); background-color: rgba(96,84,67,0.15);" wire:key="link-{{ $i }}">
                            <div class="flex gap-2 items-center">
                                <flux:input wire:model="links.{{ $i }}.url" type="url" placeholder="URL — https://…" class="flex-1" />
                                <flux:button size="sm" variant="subtle" icon="x-mark" wire:click.prevent="removeLink({{ $i }})" class="btn-muted-icon" />
                            </div>
                            <flux:error name="links.{{ $i }}.url" />
                            <div class="grid grid-cols-2 gap-2">
                                <flux:input wire:model="links.{{ $i }}.alt.en" placeholder="Alt (EN)" />
                                <flux:input wire:model="links.{{ $i }}.alt.cs" placeholder="Alt (CS)" />
                            </div>
                            <div>
                                <flux:input wire:model="links.{{ $i }}.img_url" placeholder="https://… or images/… (icon)" />
                                <flux:error name="links.{{ $i }}.img_url" />
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Badges --}}
                <div class="col-span-2 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium" style="color: var(--c-fg);">Badges</span>
                        <flux:button size="sm" icon="plus" wire:click.prevent="addBadge" class="btn-gold-subtle">Add badge</flux:button>
                    </div>
                    @foreach ($selectedBadgeIds as $i => $badgeId)
                        <div class="flex gap-2 items-center" wire:key="badge-{{ $i }}">
                            <flux:select wire:model="selectedBadgeIds.{{ $i }}" class="flex-1">
                                <flux:select.option value="">— select badge —</flux:select.option>
                                @foreach ($this->allBadges as $badge)
                                    <flux:select.option value="{{ $badge->id }}">{{ $badge->name['en'] ?? $badge->slug }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:button size="sm" variant="subtle" icon="x-mark" wire:click.prevent="removeBadge({{ $i }})" class="btn-muted-icon" />
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:button x-on:click="$flux.modal('form').close()">Cancel</flux:button>
                <flux:button type="submit" class="btn-gold">{{ $editingId ? 'Save changes' : 'Create' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete modal --}}
    <flux:modal name="delete" class="md:w-[400px]">
        <flux:heading>Delete project?</flux:heading>
        <flux:text class="mt-2 mb-6">This action cannot be undone.</flux:text>
        <div class="flex justify-end gap-2">
            <flux:button x-on:click="$flux.modal('delete').close()">Cancel</flux:button>
            <flux:button wire:click="delete" variant="danger">Delete</flux:button>
        </div>
    </flux:modal>

</div>
