<?php

use App\Models\Article;
use App\Models\Badge;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage Articles')] class extends Component {
    public string $search = '';

    public ?string $editingId = null;
    public array $header = ['en' => '', 'cs' => ''];
    public array $description = ['en' => '', 'cs' => ''];
    public array $content = ['en' => '', 'cs' => ''];
    public string $slug = '';
    public string $date = '';
    public string $thumbnail_url = '';
    public array $selectedBadgeIds = [];

    public ?string $deletingId = null;

    #[Computed]
    public function articles(): \Illuminate\Support\Collection
    {
        return Article::query()
            ->when($this->search, fn ($q) => $q->whereRaw("lower(header->>'en') LIKE lower(?)", ['%'.addcslashes($this->search, '%_\\').'%']))
            ->orderBy('sort_order')
            ->orderBy('date', 'desc')
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

        $articles = Article::query()
            ->orderBy('sort_order')
            ->orderBy('date', 'desc')
            ->orderByRaw("header->>'en'")
            ->get();

        $item = $articles->firstWhere('id', $id);

        if (! $item) {
            return;
        }

        $articles = $articles->reject(fn ($a) => $a->id === $id)->values();
        $articles->splice($position, 0, [$item]);

        $articles->each(fn ($a, $i) => Article::where('id', $a->id)->update(['sort_order' => $i]));

        unset($this->articles);
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
        $article = Article::with('badges')->findOrFail($id);
        $this->editingId = $id;
        $this->header = array_merge(['en' => '', 'cs' => ''], $article->header ?? []);
        $this->description = array_merge(['en' => '', 'cs' => ''], $article->description ?? []);
        $this->content = array_merge(['en' => '', 'cs' => ''], $article->content ?? []);
        $this->slug = $article->slug;
        $this->date = $article->date?->format('Y-m-d') ?? '';
        $this->thumbnail_url = $article->thumbnail_url ?? '';
        $this->selectedBadgeIds = $article->badges->pluck('id')->toArray();
        $this->modal('form')->show();
    }

    public function updatedHeaderEn(string $value): void
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($value);
        }
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
        $article = $this->editingId ? Article::findOrFail($this->editingId) : null;

        $validated = $this->validate([
            'header' => ['required', 'array'],
            'header.en' => ['required', 'string', 'max:255'],
            'header.cs' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string', 'max:500'],
            'description.cs' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'array'],
            'content.en' => ['nullable', 'string'],
            'content.cs' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('articles', 'slug')->ignore($article)],
            'date' => ['required', 'date'],
            'thumbnail_url' => ['nullable', 'url', 'max:500'],
            'selectedBadgeIds' => ['nullable', 'array'],
            'selectedBadgeIds.*' => ['nullable', 'uuid', 'exists:badges,id'],
        ]);

        $badgeIds = collect($this->selectedBadgeIds)
            ->filter(fn ($id) => filled($id))
            ->unique()
            ->values()
            ->toArray();

        $data = [
            'header' => array_filter($validated['header'], fn ($v) => filled($v)),
            'description' => array_filter($validated['description'] ?? [], fn ($v) => filled($v)) ?: null,
            'content' => array_filter($validated['content'] ?? [], fn ($v) => filled($v)) ?: null,
            'slug' => $validated['slug'],
            'date' => $validated['date'],
            'thumbnail_url' => $validated['thumbnail_url'] ?: null,
            'user_id' => auth()->id(),
        ];

        if ($article) {
            $article->update($data);
            $article->badges()->sync($badgeIds);
        } else {
            $article = Article::create($data);
            $article->badges()->sync($badgeIds);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->articles);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Article::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->articles);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->header = ['en' => '', 'cs' => ''];
        $this->description = ['en' => '', 'cs' => ''];
        $this->content = ['en' => '', 'cs' => ''];
        $this->slug = '';
        $this->date = '';
        $this->thumbnail_url = '';
        $this->selectedBadgeIds = [];
        $this->resetValidation();
    }
}; ?>

<div style="font-family: var(--font-sans); color: var(--c-fg);" class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 style="font-size: 2rem; font-weight: 600; color: var(--c-fg);">Articles</h1>
            <p style="color: var(--c-muted); font-size: 0.875rem; margin-top: 0.2rem;">Blog posts and write-ups</p>
        </div>
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">
            Add article
        </flux:button>
    </div>

    {{-- Search --}}
    <flux:input wire:model.live.debounce="search" placeholder="Search by title…" icon="magnifying-glass" class="max-w-xs" />

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column></flux:table.column>
            <flux:table.column>Header</flux:table.column>
            <flux:table.column>Slug</flux:table.column>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column>Updated</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows wire:sort="reorder">
            @forelse ($this->articles as $article)
                <flux:table.row wire:key="{{ $article->id }}" wire:sort:item="{{ $article->id }}">
                    <flux:table.cell wire:sort:handle style="cursor: grab; color: var(--c-muted); width: 1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                    </flux:table.cell>
                    <flux:table.cell variant="strong">{{ $article->header['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $article->slug }}</flux:table.cell>
                    <flux:table.cell>{{ $article->date?->format('d.m.Y') ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $article->updated_at->format('d.m.Y') }}</flux:table.cell>
                    <flux:table.cell wire:sort:ignore>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" wire:click="openEdit('{{ $article->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" wire:click="confirmDelete('{{ $article->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <p style="color: var(--c-muted); text-align: center; padding: 2rem 0;">No articles found.</p>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[700px]">
        <flux:heading>{{ $editingId ? 'Edit article' : 'New article' }}</flux:heading>
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
                        <flux:input wire:model.live.debounce="header.en" placeholder="e.g. How I Built This Portfolio" />
                        <flux:error name="header.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:input wire:model="description.en" placeholder="Short summary shown in listings" />
                        <flux:error name="description.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Content</flux:label>
                        <flux:textarea wire:model="content.en" placeholder="Markdown supported…" rows="8" />
                        <flux:error name="content.en" />
                    </flux:field>
                </div>

                <div x-show="locale === 'cs'" class="space-y-4">
                    <flux:field>
                        <flux:label>Header</flux:label>
                        <flux:input wire:model="header.cs" placeholder="např. Jak jsem postavil toto portfolio" />
                        <flux:error name="header.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:input wire:model="description.cs" placeholder="Krátký popis zobrazovaný v přehledu" />
                        <flux:error name="description.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Content</flux:label>
                        <flux:textarea wire:model="content.cs" placeholder="Podporuje Markdown…" rows="8" />
                        <flux:error name="content.cs" />
                    </flux:field>
                </div>
            </div>

            {{-- Non-translatable fields --}}
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Slug <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                    <flux:input wire:model="slug" placeholder="e.g. how-i-built-this-portfolio" />
                    <flux:error name="slug" />
                </flux:field>

                <flux:field>
                    <flux:label>Date <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                    <flux:input wire:model="date" type="date" />
                    <flux:error name="date" />
                </flux:field>

                <flux:field class="col-span-2">
                    <flux:label>Thumbnail URL</flux:label>
                    <flux:input wire:model="thumbnail_url" type="url" placeholder="https://…" />
                    <flux:error name="thumbnail_url" />
                </flux:field>

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
        <flux:heading>Delete article?</flux:heading>
        <flux:text class="mt-2 mb-6">This action cannot be undone.</flux:text>
        <div class="flex justify-end gap-2">
            <flux:button x-on:click="$flux.modal('delete').close()">Cancel</flux:button>
            <flux:button wire:click="delete" variant="danger">Delete</flux:button>
        </div>
    </flux:modal>

</div>
