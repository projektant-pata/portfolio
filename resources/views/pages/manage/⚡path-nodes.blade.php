<?php

use App\Models\PathNode;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage path nodes')] class extends Component {
    public string $search = '';

    public ?string $editingId = null;
    public array $year = ['en' => '', 'cs' => ''];
    public array $nodeTitle = ['en' => '', 'cs' => ''];
    public array $body = ['en' => '', 'cs' => ''];
    public bool $isNow = false;

    public ?string $deletingId = null;

    #[Computed]
    public function nodes(): \Illuminate\Support\Collection
    {
        return PathNode::query()
            ->when($this->search, fn ($q) => $q->whereRaw("lower(title->>'en') LIKE lower(?)", ['%'.addcslashes($this->search, '%_\\').'%']))
            ->orderBy('sort_order')
            ->get();
    }

    public function mount(): void
    {
        if (request()->boolean('create')) {
            $this->openCreate();
        }
    }

    public function reorder(string $id, int $position): void
    {
        if (filled($this->search)) {
            return;
        }

        $nodes = PathNode::orderBy('sort_order')->get();
        $item = $nodes->firstWhere('id', $id);

        if (! $item) {
            return;
        }

        $nodes = $nodes->reject(fn ($n) => $n->id === $id)->values();
        $nodes->splice($position, 0, [$item]);
        $nodes->each(fn ($n, $i) => PathNode::where('id', $n->id)->update(['sort_order' => $i]));

        unset($this->nodes);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modal('form')->show();
    }

    public function openEdit(string $id): void
    {
        $node = PathNode::findOrFail($id);
        $this->editingId = $id;
        $this->year = array_merge(['en' => '', 'cs' => ''], $node->year ?? []);
        $this->nodeTitle = array_merge(['en' => '', 'cs' => ''], $node->title ?? []);
        $this->body = array_merge(['en' => '', 'cs' => ''], $node->body ?? []);
        $this->isNow = $node->is_now;
        $this->modal('form')->show();
    }

    public function save(): void
    {
        $node = $this->editingId ? PathNode::findOrFail($this->editingId) : null;

        $validated = $this->validate([
            'year' => ['required', 'array'],
            'year.en' => ['required', 'string', 'max:30'],
            'year.cs' => ['nullable', 'string', 'max:30'],
            'nodeTitle' => ['required', 'array'],
            'nodeTitle.en' => ['required', 'string', 'max:150'],
            'nodeTitle.cs' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'array'],
            'body.en' => ['required', 'string', 'max:2000'],
            'body.cs' => ['nullable', 'string', 'max:2000'],
            'isNow' => ['boolean'],
        ]);

        $data = [
            'year' => array_filter($validated['year'], fn ($v) => filled($v)),
            'title' => array_filter($validated['nodeTitle'], fn ($v) => filled($v)),
            'body' => array_filter($validated['body'], fn ($v) => filled($v)),
            'is_now' => $this->isNow,
        ];

        if ($node) {
            $node->update($data);
        } else {
            $data['sort_order'] = (int) PathNode::max('sort_order') + 1;
            PathNode::create($data);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->nodes);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            PathNode::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->nodes);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->year = ['en' => '', 'cs' => ''];
        $this->nodeTitle = ['en' => '', 'cs' => ''];
        $this->body = ['en' => '', 'cs' => ''];
        $this->isNow = false;
        $this->resetValidation();
    }
}; ?>

<div class="manage-page p-6 space-y-6">

    <x-manage.page-header title="Path nodes" subtitle="The timeline on the about-me page">
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">Add node</flux:button>
    </x-manage.page-header>

    <x-manage.search-input placeholder="Search by title…" />

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column></flux:table.column>
            <flux:table.column>Year (EN)</flux:table.column>
            <flux:table.column>Title (EN)</flux:table.column>
            <flux:table.column>Now</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows wire:sort="reorder">
            @forelse ($this->nodes as $node)
                <flux:table.row wire:key="{{ $node->id }}" wire:sort:item="{{ $node->id }}">
                    <flux:table.cell wire:sort:handle class="manage-drag-handle">
                        <x-manage.drag-handle />
                    </flux:table.cell>
                    <flux:table.cell>{{ $node->year['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell variant="strong">{{ $node->title['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $node->is_now ? '●' : '—' }}</flux:table.cell>
                    <flux:table.cell wire:sort:ignore>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" aria-label="Edit node" wire:click="openEdit('{{ $node->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" aria-label="Delete node" wire:click="confirmDelete('{{ $node->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <x-manage.empty-row colspan="5" message="No path nodes found." />
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[640px]">
        <flux:heading>{{ $editingId ? 'Edit node' : 'New node' }}</flux:heading>

        <form wire:submit="save" class="space-y-4">
            {{-- Language tabs --}}
            <x-manage.locale-tabs>
                <x-slot:en>
                    <flux:field>
                        <flux:label>Year <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:input wire:model="year.en" placeholder="e.g. 2019" />
                        <flux:error name="year.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Title <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:input wire:model="nodeTitle.en" placeholder="e.g. Back to school" />
                        <flux:error name="nodeTitle.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Body <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:textarea wire:model="body.en" placeholder="Health issues." rows="5" />
                        <flux:error name="body.en" />
                    </flux:field>
                </x-slot:en>
                <x-slot:cs>
                    <flux:field>
                        <flux:label>Year</flux:label>
                        <flux:input wire:model="year.cs" placeholder="např. 2019" />
                        <flux:error name="year.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Title</flux:label>
                        <flux:input wire:model="nodeTitle.cs" placeholder="např. Zpátky do školy" />
                        <flux:error name="nodeTitle.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Body</flux:label>
                        <flux:textarea wire:model="body.cs" placeholder="Zdravotní problémy." rows="5" />
                        <flux:error name="body.cs" />
                    </flux:field>
                </x-slot:cs>
            </x-manage.locale-tabs>

            <flux:field variant="inline">
                <flux:checkbox wire:model="isNow" label="Mark as the current node" />
                <flux:error name="isNow" />
            </flux:field>

            <x-manage.modal-footer :editing="(bool) $editingId" />
        </form>
    </flux:modal>

    <x-manage.delete-modal entity="node" />

</div>
