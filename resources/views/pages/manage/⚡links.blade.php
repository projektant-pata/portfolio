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
            ->when($this->search, fn ($q) => $q->whereRaw('lower(url) LIKE lower(?)', ['%'.addcslashes($this->search, '%_\\').'%']))
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

<div class="manage-page p-6 space-y-6">

    <x-manage.page-header title="Links" subtitle="Project external links">
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">Add link</flux:button>
    </x-manage.page-header>

    <x-manage.search-input placeholder="Search by URL…" />

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
                            <flux:button size="sm" variant="subtle" icon="pencil" aria-label="Edit link" wire:click="openEdit('{{ $link->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" aria-label="Delete link" wire:click="confirmDelete('{{ $link->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <x-manage.empty-row colspan="6" message="No links found." />
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
            <x-manage.locale-tabs>
                <x-slot:en>
                    <flux:field>
                        <flux:label>Alt text</flux:label>
                        <flux:input wire:model="alt.en" placeholder="e.g. Visit on GitHub" />
                        <flux:error name="alt.en" />
                    </flux:field>
                </x-slot:en>
                <x-slot:cs>
                    <flux:field>
                        <flux:label>Alt text</flux:label>
                        <flux:input wire:model="alt.cs" placeholder="např. Zobrazit na GitHubu" />
                        <flux:error name="alt.cs" />
                    </flux:field>
                </x-slot:cs>
            </x-manage.locale-tabs>

            <flux:field>
                <flux:label>Icon URL</flux:label>
                <flux:input wire:model="img_url" placeholder="https://… or images/… (icon image)" />
                <flux:error name="img_url" />
            </flux:field>

            <x-manage.modal-footer :editing="(bool) $editingId" />
        </form>
    </flux:modal>

    <x-manage.delete-modal entity="link" />

</div>
