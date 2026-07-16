<?php

use App\Models\Badge;
use App\Models\Experience;
use Illuminate\Support\Facades\Storage;
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
            ->when($this->search, fn ($q) => $q->whereRaw("lower(title->>'en') LIKE lower(?)", ['%'.addcslashes($this->search, '%_\\').'%']))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->orderBy('sort_order')
            ->orderByRaw("title->>'en'")
            ->get();
    }

    public function reorder(int $id, int $position): void
    {
        // Reordering renumbers the whole table; a filtered subset would corrupt
        // positions of hidden rows, so only allow it when no filter is active.
        if (filled($this->search) || filled($this->typeFilter)) {
            return;
        }

        $experiences = Experience::query()
            ->orderBy('sort_order')
            ->orderByRaw("title->>'en'")
            ->get();

        $item = $experiences->firstWhere('id', $id);

        if (! $item) {
            return;
        }

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

        $experience = $this->editingId ? Experience::findOrFail($this->editingId) : null;

        if ($this->imageFile) {
            $path = $this->imageFile->store('experiences', 'public');

            if ($experience?->image_path && str_starts_with($experience->image_path, 'storage/')) {
                Storage::disk('public')->delete(substr($experience->image_path, strlen('storage/')));
            }

            $this->image_path = 'storage/' . $path;
        } else {
            // image_path is never trusted from the client; derive it from the stored model.
            $this->image_path = $experience?->image_path ?? '';
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

        if ($experience) {
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

<div class="manage-page p-6 space-y-6">

    <x-manage.page-header title="Experiences" subtitle="Work and life entries">
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">Add experience</flux:button>
    </x-manage.page-header>

    {{-- Filters --}}
    <div class="flex gap-3 flex-wrap">
        <x-manage.search-input placeholder="Search by title…" />

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
                    <flux:table.cell wire:sort:handle class="manage-drag-handle">
                        <x-manage.drag-handle />
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
                            <flux:button size="sm" variant="subtle" icon="pencil" aria-label="Edit experience" wire:click="openEdit({{ $experience->id }})" />
                            <flux:button size="sm" variant="subtle" icon="trash" aria-label="Delete experience" wire:click="confirmDelete({{ $experience->id }})" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <x-manage.empty-row colspan="8" message="No experiences found." />
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
            <x-manage.locale-tabs>
                <x-slot:en>
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
                </x-slot:en>
                <x-slot:cs>
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
                </x-slot:cs>
            </x-manage.locale-tabs>

            {{-- Non-translatable fields --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        <p class="manage-note text-xs mt-1">Current: {{ $image_path }}</p>
                    @endif
                    @if ($imageFile)
                        <img src="{{ $imageFile->temporaryUrl() }}" class="mt-2 h-16 rounded object-cover" />
                    @endif
                </flux:field>

                <x-manage.link-repeater :links="$links" />

                <x-manage.badge-picker :selected="$selectedBadgeIds" :badges="$this->allBadges" />
            </div>

            <x-manage.modal-footer :editing="(bool) $editingId" />
        </form>
    </flux:modal>

    <x-manage.delete-modal entity="experience" />

</div>
