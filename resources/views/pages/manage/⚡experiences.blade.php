<?php

use App\Models\Badge;
use App\Models\Experience;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Manage Experiences')] class extends Component {
    use WithFileUploads;

    public string $search = '';
    public string $typeFilter = '';

    public ?int $editingId = null;
    public string $type = 'work';
    public array $title = ['en' => '', 'cs' => ''];
    public array $subtitle = ['en' => '', 'cs' => ''];
    public array $content = ['en' => '', 'cs' => ''];
    public array $year = ['en' => '', 'cs' => ''];
    public $imageFile = null;
    public string $image_path = '';
    public bool $is_special = false;
    public int $sort_order = 0;
    public array $links = [];
    public array $selectedBadgeIds = [];

    public ?int $deletingId = null;

    #[Computed]
    public function experiences(): \Illuminate\Support\Collection
    {
        return Experience::query()
            ->when($this->search, fn ($q) => $q->whereRaw("title->>'en' ILIKE ?", ["%{$this->search}%"]))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->orderBy('sort_order')
            ->orderByRaw("title->>'en'")
            ->get();
    }

    public function reorder(int $id, int $position): void
    {
        $experiences = Experience::query()
            ->when($this->search, fn ($q) => $q->whereRaw("title->>'en' ILIKE ?", ["%{$this->search}%"]))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->orderBy('sort_order')
            ->orderByRaw("title->>'en'")
            ->get();

        $item = $experiences->firstWhere('id', $id);
        $experiences = $experiences->reject(fn ($e) => $e->id === $id)->values();
        $experiences->splice($position, 0, [$item]);

        $experiences->each(fn ($e, $i) => Experience::where('id', $e->id)->update(['sort_order' => $i]));

        unset($this->experiences);
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

    public function openEdit(int $id): void
    {
        $experience = Experience::with('badges')->findOrFail($id);
        $this->editingId = $id;
        $this->type = $experience->type;
        $this->title = array_merge(['en' => '', 'cs' => ''], $experience->title ?? []);
        $this->subtitle = array_merge(['en' => '', 'cs' => ''], $experience->subtitle ?? []);
        $this->content = array_merge(['en' => '', 'cs' => ''], $experience->content ?? []);
        $this->year = array_merge(['en' => '', 'cs' => ''], $experience->year ?? []);
        $this->is_special = (bool) $experience->is_special;
        $this->sort_order = $experience->sort_order ?? 0;
        $this->image_path = $experience->image_path ?? '';
        $this->links = $experience->links ?? [];
        $this->selectedBadgeIds = $experience->badges->pluck('id')->toArray();
        $this->modal('form')->show();
    }

    public function addLink(): void
    {
        $this->links[] = ['url' => '', 'alt' => '', 'img_url' => ''];
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
        $validated = $this->validate([
            'type' => ['required', 'in:work,life'],
            'is_special' => ['boolean'],
            'title' => ['required', 'array'],
            'title.en' => ['required', 'string', 'max:255'],
            'title.cs' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'array'],
            'subtitle.en' => ['nullable', 'string', 'max:255'],
            'subtitle.cs' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'array'],
            'content.en' => ['nullable', 'string'],
            'content.cs' => ['nullable', 'string'],
            'year' => ['nullable', 'array'],
            'year.en' => ['nullable', 'string', 'max:50'],
            'year.cs' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'imageFile' => ['nullable', 'image', 'max:2048'],
            'links' => ['nullable', 'array'],
            'links.*.url' => ['nullable', 'url', 'max:500'],
            'links.*.alt' => ['nullable', 'string', 'max:100'],
            'links.*.img_url' => ['nullable', 'url', 'max:500'],
            'selectedBadgeIds' => ['nullable', 'array'],
            'selectedBadgeIds.*' => ['nullable', 'uuid', 'exists:badges,id'],
        ]);

        if ($this->imageFile) {
            $path = $this->imageFile->store('experiences', 'public');
            $this->image_path = 'storage/' . $path;
        }

        $links = collect($this->links)
            ->filter(fn ($l) => filled($l['url'] ?? ''))
            ->values()
            ->toArray();

        $badgeIds = collect($this->selectedBadgeIds)
            ->filter(fn ($id) => filled($id))
            ->unique()
            ->values()
            ->toArray();

        $data = [
            'type' => $validated['type'],
            'is_special' => $this->is_special,
            'title' => array_filter($validated['title'], fn ($v) => filled($v)),
            'subtitle' => array_filter($validated['subtitle'] ?? [], fn ($v) => filled($v)) ?: null,
            'content' => array_filter($validated['content'] ?? [], fn ($v) => filled($v)) ?: null,
            'year' => array_filter($validated['year'] ?? [], fn ($v) => filled($v)) ?: null,
            'image_path' => $this->image_path ?: null,
            'links' => $links ?: null,
            'sort_order' => $this->sort_order,
        ];

        if ($this->editingId) {
            $experience = Experience::findOrFail($this->editingId);
            $experience->update($data);
            $experience->badges()->sync($badgeIds);
        } else {
            $experience = Experience::create($data);
            $experience->badges()->sync($badgeIds);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->experiences);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Experience::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->experiences);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->type = 'work';
        $this->title = ['en' => '', 'cs' => ''];
        $this->subtitle = ['en' => '', 'cs' => ''];
        $this->content = ['en' => '', 'cs' => ''];
        $this->year = ['en' => '', 'cs' => ''];
        $this->is_special = false;
        $this->sort_order = 0;
        $this->imageFile = null;
        $this->image_path = '';
        $this->links = [];
        $this->selectedBadgeIds = [];
        $this->resetValidation();
    }
}; ?>

<div style="font-family: var(--font-body); color: var(--c-fg);" class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 style="font-size: 2rem; font-weight: 600; color: var(--c-fg);">Experiences</h1>
            <p style="color: var(--c-muted); font-size: 0.875rem; margin-top: 0.2rem;">Work and life entries</p>
        </div>
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">
            Add experience
        </flux:button>
    </div>

    {{-- Filters --}}
    <div class="flex gap-3 flex-wrap">
        <flux:input wire:model.live.debounce="search" placeholder="Search by title…" icon="magnifying-glass" class="max-w-xs" />

        <flux:select wire:model.live="typeFilter" class="max-w-[160px]">
            <flux:select.option value="">All types</flux:select.option>
            <flux:select.option value="work">Work</flux:select.option>
            <flux:select.option value="life">Life</flux:select.option>
        </flux:select>
    </div>

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column></flux:table.column>
            <flux:table.column>Order</flux:table.column>
            <flux:table.column>Title</flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column>Year</flux:table.column>
            <flux:table.column>Subtitle</flux:table.column>
            <flux:table.column>Updated</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows wire:sort="reorder">
            @forelse ($this->experiences as $experience)
                <flux:table.row wire:key="{{ $experience->id }}" wire:sort:item="{{ $experience->id }}">
                    <flux:table.cell wire:sort:handle style="cursor: grab; color: var(--c-muted); width: 1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </flux:table.cell>
                    <flux:table.cell>{{ $experience->sort_order }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ $experience->title['en'] ?? '—' }}</flux:table.cell>

                    <flux:table.cell>
                        <flux:badge
                            size="sm"
                            color="{{ $experience->type === 'work' ? 'blue' : 'green' }}"
                            inset="top bottom"
                        >
                            {{ $experience->type === 'work' ? 'Work' : 'Life' }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell>{{ $experience->year['en'] ?? '—' }}</flux:table.cell>

                    <flux:table.cell>{{ $experience->subtitle['en'] ?? '—' }}</flux:table.cell>

                    <flux:table.cell>{{ $experience->updated_at->format('d.m.Y') }}</flux:table.cell>

                    <flux:table.cell wire:sort:ignore>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" wire:click="openEdit({{ $experience->id }})" />
                            <flux:button size="sm" variant="subtle" icon="trash" wire:click="confirmDelete({{ $experience->id }})" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8">
                        <p style="color: var(--c-muted); text-align: center; padding: 2rem 0;">No experiences found.</p>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[700px]">
        <flux:heading>
            {{ $editingId ? 'Edit experience' : 'New experience' }}
        </flux:heading>
        <flux:text class="mt-1 mb-5">Fill in the details below.</flux:text>

        <form wire:submit="save" class="space-y-4">
            {{-- Language tabs --}}
            <div x-data="{ locale: 'en' }">
                <div class="flex gap-1 mb-4 p-1 rounded-lg" style="background: var(--c-surface-raised, rgba(0,0,0,0.08));">
                    <button
                        type="button"
                        x-on:click="locale = 'en'"
                        :class="locale === 'en' ? 'shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
                        class="flex-1 px-3 py-1.5 rounded-md text-sm transition-all"
                        style="background: transparent;"
                        :style="locale === 'en' ? 'background: var(--c-surface)' : ''"
                    >🇬🇧 English</button>
                    <button
                        type="button"
                        x-on:click="locale = 'cs'"
                        :class="locale === 'cs' ? 'shadow-sm font-semibold' : 'opacity-60 hover:opacity-80'"
                        class="flex-1 px-3 py-1.5 rounded-md text-sm transition-all"
                        style="background: transparent;"
                        :style="locale === 'cs' ? 'background: var(--c-surface)' : ''"
                    >🇨🇿 Czech</button>
                </div>

                {{-- English fields --}}
                <div x-show="locale === 'en'" class="space-y-4">
                    <flux:field>
                        <flux:label>Title <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:input wire:model="title.en" placeholder="e.g. Software Developer" />
                        <flux:error name="title.en" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Subtitle</flux:label>
                        <flux:input wire:model="subtitle.en" placeholder="e.g. Company name or school" />
                        <flux:error name="subtitle.en" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Content</flux:label>
                        <flux:textarea wire:model="content.en" placeholder="Markdown supported…" rows="6" />
                        <flux:error name="content.en" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Year</flux:label>
                        <flux:input wire:model="year.en" placeholder="e.g. 2022 – present" />
                        <flux:error name="year.en" />
                    </flux:field>
                </div>

                {{-- Czech fields --}}
                <div x-show="locale === 'cs'" class="space-y-4">
                    <flux:field>
                        <flux:label>Title</flux:label>
                        <flux:input wire:model="title.cs" placeholder="např. Softwarový vývojář" />
                        <flux:error name="title.cs" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Subtitle</flux:label>
                        <flux:input wire:model="subtitle.cs" placeholder="např. název firmy nebo školy" />
                        <flux:error name="subtitle.cs" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Content</flux:label>
                        <flux:textarea wire:model="content.cs" placeholder="Podporuje Markdown…" rows="6" />
                        <flux:error name="content.cs" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Rok</flux:label>
                        <flux:input wire:model="year.cs" placeholder="např. 2022 – nyní" />
                        <flux:error name="year.cs" />
                    </flux:field>
                </div>
            </div>

            {{-- Non-translatable fields --}}
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Type</flux:label>
                    <flux:select wire:model="type">
                        <flux:select.option value="work">Work</flux:select.option>
                        <flux:select.option value="life">Life</flux:select.option>
                    </flux:select>
                    <flux:error name="type" />
                </flux:field>

                <flux:field>
                    <flux:label>Sort order</flux:label>
                    <flux:input wire:model="sort_order" type="number" min="0" placeholder="0" />
                    <flux:error name="sort_order" />
                </flux:field>

                <flux:field>
                    <flux:label>Special</flux:label>
                    <flux:checkbox wire:model="is_special" label="Mark as special" />
                    <flux:error name="is_special" />
                </flux:field>

                {{-- Image upload --}}
                <flux:field class="col-span-2">
                    <flux:label>Image</flux:label>
                    <flux:input wire:model="imageFile" type="file" accept="image/*" />
                    <flux:error name="imageFile" />
                    @if ($image_path && ! $imageFile)
                        <p class="text-xs mt-1" style="color: var(--c-muted);">Current: {{ $image_path }}</p>
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
                            <flux:input wire:model="links.{{ $i }}.alt" placeholder="Alt text (optional)" />
                            <div>
                                <flux:input wire:model="links.{{ $i }}.img_url" type="url" placeholder="Icon URL (optional)" />
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
                <flux:button type="submit" class="btn-gold">
                    {{ $editingId ? 'Save changes' : 'Create' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete confirmation modal --}}
    <flux:modal name="delete" class="md:w-[400px]">
        <flux:heading>Delete experience?</flux:heading>
        <flux:text class="mt-2 mb-6">This action cannot be undone.</flux:text>
        <div class="flex justify-end gap-2">
            <flux:button x-on:click="$flux.modal('delete').close()">Cancel</flux:button>
            <flux:button wire:click="delete" variant="danger">Delete</flux:button>
        </div>
    </flux:modal>

</div>
