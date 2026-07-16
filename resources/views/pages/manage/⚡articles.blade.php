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

<div class="manage-page p-6 space-y-6">

    <x-manage.page-header title="Articles" subtitle="Blog posts and write-ups">
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">Add article</flux:button>
    </x-manage.page-header>

    <x-manage.search-input placeholder="Search by title…" />

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
                    <flux:table.cell wire:sort:handle class="manage-drag-handle">
                        <x-manage.drag-handle />
                    </flux:table.cell>
                    <flux:table.cell variant="strong">{{ $article->header['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $article->slug }}</flux:table.cell>
                    <flux:table.cell>{{ $article->date?->format('d.m.Y') ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $article->updated_at->format('d.m.Y') }}</flux:table.cell>
                    <flux:table.cell wire:sort:ignore>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" aria-label="Edit article" wire:click="openEdit('{{ $article->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" aria-label="Delete article" wire:click="confirmDelete('{{ $article->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <x-manage.empty-row colspan="6" message="No articles found." />
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[700px]">
        <flux:heading>{{ $editingId ? 'Edit article' : 'New article' }}</flux:heading>
        <flux:text class="mt-1 mb-5">Fill in the details below.</flux:text>

        <form wire:submit="save" class="space-y-4">
            {{-- Language tabs --}}
            <x-manage.locale-tabs>
                <x-slot:en>
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
                </x-slot:en>
                <x-slot:cs>
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
                </x-slot:cs>
            </x-manage.locale-tabs>

            {{-- Non-translatable fields --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                <x-manage.badge-picker :selected="$selectedBadgeIds" :badges="$this->allBadges" />
            </div>

            <x-manage.modal-footer :editing="(bool) $editingId" />
        </form>
    </flux:modal>

    <x-manage.delete-modal entity="article" />

</div>
