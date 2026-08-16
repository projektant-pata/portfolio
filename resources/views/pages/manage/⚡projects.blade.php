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
    public string $kind = 'personal';
    public string $client = '';
    public string $status = '';
    public array $role = ['en' => '', 'cs' => ''];
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

    public function mount(): void
    {
        if (request()->boolean('create')) {
            $this->openCreate();
        }
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
        $this->kind = $project->kind;
        $this->client = $project->client ?? '';
        $this->status = $project->status ?? '';
        $this->role = array_merge(['en' => '', 'cs' => ''], $project->role ?? []);
        $this->img_url = $project->img_url ?? '';
        $this->imageFile = null;
        $this->links = $project->links->map(fn ($l) => [
            'url' => $l->url,
            'kind' => $l->kind,
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
        $this->links[] = ['url' => '', 'kind' => 'live', 'alt' => ['en' => '', 'cs' => ''], 'img_url' => ''];
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
            'kind' => ['required', 'string', \Illuminate\Validation\Rule::in(Project::KINDS)],
            'client' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', \Illuminate\Validation\Rule::in(Project::STATUSES)],
            'role' => ['nullable', 'array'],
            'role.en' => ['nullable', 'string', 'max:255'],
            'role.cs' => ['nullable', 'string', 'max:255'],
            'imageFile' => ['nullable', 'image', 'max:4096'],
            'links' => ['nullable', 'array'],
            'links.*.url' => ['nullable', 'url', 'max:500'],
            'links.*.kind' => ['nullable', 'string', \Illuminate\Validation\Rule::in(Link::KINDS)],
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
            'role' => array_filter($validated['role'] ?? [], fn ($v) => filled($v)) ?: null,
            'slug' => $validated['slug'],
            'year' => (int) $validated['year'],
            'kind' => $validated['kind'],
            'client' => $validated['kind'] === 'client' ? ($validated['client'] ?: null) : null,
            'status' => $validated['status'] ?: null,
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
                'kind' => $linkData['kind'] ?? 'live',
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
        $this->kind = 'personal';
        $this->client = '';
        $this->status = '';
        $this->role = ['en' => '', 'cs' => ''];
        $this->img_url = '';
        $this->imageFile = null;
        $this->links = [];
        $this->selectedBadgeIds = [];
        $this->resetValidation();
    }
}; ?>

<div class="manage-page p-6 space-y-6">

    <x-manage.page-header title="Projects" subtitle="Portfolio projects">
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">Add project</flux:button>
    </x-manage.page-header>

    <x-manage.search-input placeholder="Search by title…" />

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
                    <flux:table.cell wire:sort:handle class="manage-drag-handle">
                        <x-manage.drag-handle />
                    </flux:table.cell>
                    <flux:table.cell variant="strong">{{ $project->header['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $project->year }}</flux:table.cell>
                    <flux:table.cell>{{ $project->slug }}</flux:table.cell>
                    <flux:table.cell>{{ $project->updated_at->format('d.m.Y') }}</flux:table.cell>
                    <flux:table.cell wire:sort:ignore>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" aria-label="Edit project" wire:click="openEdit('{{ $project->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" aria-label="Delete project" wire:click="confirmDelete('{{ $project->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <x-manage.empty-row colspan="6" message="No projects found." />
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[700px]">
        <flux:heading>{{ $editingId ? 'Edit project' : 'New project' }}</flux:heading>
        <flux:text class="mt-1 mb-5">Fill in the details below.</flux:text>

        <form wire:submit="save" class="space-y-4">
            {{-- Language tabs --}}
            <x-manage.locale-tabs>
                <x-slot:en>
                    <flux:field>
                        <flux:label>Header <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:input wire:model.live.debounce="header.en" placeholder="e.g. Portfolio Website" />
                        <flux:error name="header.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description.en" placeholder="Short project description…" rows="4" />
                        <flux:error name="description.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Role</flux:label>
                        <flux:input wire:model="role.en" placeholder="e.g. Front-end and back-end" />
                        <flux:error name="role.en" />
                    </flux:field>
                </x-slot:en>
                <x-slot:cs>
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
                    <flux:field>
                        <flux:label>Role</flux:label>
                        <flux:input wire:model="role.cs" placeholder="např. Front-end a back-end" />
                        <flux:error name="role.cs" />
                    </flux:field>
                </x-slot:cs>
            </x-manage.locale-tabs>

            {{-- Non-translatable fields --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                <flux:field>
                    <flux:label>Kind <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                    <flux:select wire:model.live="kind">
                        <flux:select.option value="personal">Personal</flux:select.option>
                        <flux:select.option value="client">Client</flux:select.option>
                        <flux:select.option value="school">School</flux:select.option>
                    </flux:select>
                    <flux:error name="kind" />
                </flux:field>

                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select wire:model="status">
                        <flux:select.option value="">—</flux:select.option>
                        <flux:select.option value="live">Live</flux:select.option>
                        <flux:select.option value="archived">Archived</flux:select.option>
                        <flux:select.option value="wip">In progress</flux:select.option>
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>

                @if ($kind === 'client')
                    <flux:field class="col-span-2">
                        <flux:label>Client name</flux:label>
                        <flux:input wire:model="client" placeholder="e.g. PekneWeby" />
                        <flux:error name="client" />
                    </flux:field>
                @endif

                <flux:field class="col-span-2">
                    <flux:label>Image</flux:label>
                    <flux:input wire:model="imageFile" type="file" accept="image/*" />
                    <flux:error name="imageFile" />
                    @if ($img_url && ! $imageFile)
                        <p class="manage-note text-xs mt-1">Current: {{ $img_url }}</p>
                    @endif
                    @if ($imageFile)
                        <img src="{{ $imageFile->temporaryUrl() }}" class="mt-2 h-16 rounded object-cover" />
                    @endif
                </flux:field>

                <x-manage.link-repeater :links="$links" :translatable-alt="true" :with-kind="true" />

                <x-manage.badge-picker :selected="$selectedBadgeIds" :badges="$this->allBadges" />
            </div>

            <x-manage.modal-footer :editing="(bool) $editingId" />
        </form>
    </flux:modal>

    <x-manage.delete-modal entity="project" />

</div>
