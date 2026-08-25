<?php

use App\Models\Tool;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage tools')] class extends Component {
    public string $search = '';

    public ?string $editingId = null;
    public string $name = '';
    public string $color = '';
    public string $frequency = 'daily';
    public array $group = ['en' => '', 'cs' => ''];
    public string $placement = 'grid';
    public bool $showOnHome = false;

    public ?string $deletingId = null;

    #[Computed]
    public function tools(): \Illuminate\Support\Collection
    {
        return Tool::query()
            ->when($this->search, fn ($q) => $q->where('name', 'ILIKE', '%'.addcslashes($this->search, '%_\\').'%'))
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

        $tools = Tool::orderBy('sort_order')->get();
        $item = $tools->firstWhere('id', $id);

        if (! $item) {
            return;
        }

        $tools = $tools->reject(fn ($t) => $t->id === $id)->values();
        $tools->splice($position, 0, [$item]);
        $tools->each(fn ($t, $idx) => Tool::where('id', $t->id)->update(['sort_order' => $idx]));

        unset($this->tools);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modal('form')->show();
    }

    public function openEdit(string $id): void
    {
        $tool = Tool::findOrFail($id);
        $this->editingId = $id;
        $this->name = $tool->name;
        $this->color = $tool->color ?? '';
        $this->frequency = $tool->frequency ?? '';
        $this->group = array_merge(['en' => '', 'cs' => ''], $tool->group ?? []);
        $this->placement = $tool->placement;
        $this->showOnHome = $tool->show_on_home;
        $this->modal('form')->show();
    }

    public function save(): void
    {
        $tool = $this->editingId ? Tool::findOrFail($this->editingId) : null;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:80'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'frequency' => ['nullable', 'string', 'in:'.implode(',', Tool::FREQUENCIES)],
            'group' => ['nullable', 'array'],
            'group.en' => ['nullable', 'string', 'max:60'],
            'group.cs' => ['nullable', 'string', 'max:60'],
            'placement' => ['required', 'string', 'in:'.implode(',', Tool::PLACEMENTS)],
            'showOnHome' => ['boolean'],
        ]);

        /* Chips carry a name and nothing else: a group would invent a fifth
           column and a frequency would render a label the chip cannot show. */
        $isGrid = $validated['placement'] === 'grid';

        $data = [
            'name' => $validated['name'],
            'color' => $isGrid ? ($validated['color'] ?: null) : null,
            'frequency' => $isGrid ? ($validated['frequency'] ?: null) : null,
            'group' => $isGrid ? array_filter($validated['group'] ?? [], fn ($v) => filled($v)) : null,
            'placement' => $validated['placement'],
            'show_on_home' => $isGrid && $validated['showOnHome'],
        ];

        if ($tool) {
            $tool->update($data);
        } else {
            $data['sort_order'] = (int) Tool::max('sort_order') + 1;
            Tool::create($data);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->tools);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Tool::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->tools);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->color = '';
        $this->frequency = 'daily';
        $this->group = ['en' => '', 'cs' => ''];
        $this->placement = 'grid';
        $this->showOnHome = false;
        $this->resetValidation();
    }
}; ?>

<div class="manage-page p-6 space-y-6">

    <x-manage.page-header title="Tools" subtitle="The toolbox on the home and about-me pages">
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">Add tool</flux:button>
    </x-manage.page-header>

    <x-manage.search-input placeholder="Search by name…" />

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column></flux:table.column>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Group (EN)</flux:table.column>
            <flux:table.column>Frequency</flux:table.column>
            <flux:table.column>Placement</flux:table.column>
            <flux:table.column>Home</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows wire:sort="reorder">
            @forelse ($this->tools as $tool)
                <flux:table.row wire:key="{{ $tool->id }}" wire:sort:item="{{ $tool->id }}">
                    <flux:table.cell wire:sort:handle class="manage-drag-handle">
                        <x-manage.drag-handle />
                    </flux:table.cell>
                    <flux:table.cell variant="strong">{{ $tool->name }}</flux:table.cell>
                    <flux:table.cell>{{ $tool->group['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $tool->frequency ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $tool->placement }}</flux:table.cell>
                    <flux:table.cell>{{ $tool->show_on_home ? 'Yes' : '—' }}</flux:table.cell>
                    <flux:table.cell wire:sort:ignore>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" aria-label="Edit tool" wire:click="openEdit('{{ $tool->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" aria-label="Delete tool" wire:click="confirmDelete('{{ $tool->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <x-manage.empty-row colspan="7" message="No tools found." />
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[640px]">
        <flux:heading>{{ $editingId ? 'Edit tool' : 'New tool' }}</flux:heading>
        <flux:text class="mt-1 mb-5">Grid tools fill the four about-me columns; rotation and learning rows are chips in the two footers.</flux:text>

        <form wire:submit="save" class="space-y-4">
            <flux:field>
                <flux:label>Name <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                <flux:input wire:model="name" placeholder="e.g. Spring Boot" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Placement</flux:label>
                <flux:select wire:model.live="placement">
                    <flux:select.option value="grid">Grid — a full row in the toolbox</flux:select.option>
                    <flux:select.option value="rotation">Rotation — chip in the home footer</flux:select.option>
                    <flux:select.option value="learning">Learning — chip in the about-me footer</flux:select.option>
                </flux:select>
                <flux:error name="placement" />
            </flux:field>

            @if ($placement === 'grid')
                <x-manage.locale-tabs>
                    <x-slot:en>
                        <flux:field>
                            <flux:label>Group</flux:label>
                            <flux:input wire:model="group.en" placeholder="e.g. Backend" />
                            <flux:description>The column heading. Tools sharing an English group land in one column.</flux:description>
                            <flux:error name="group.en" />
                        </flux:field>
                    </x-slot:en>
                    <x-slot:cs>
                        <flux:field>
                            <flux:label>Group</flux:label>
                            <flux:input wire:model="group.cs" placeholder="např. Backend" />
                            <flux:error name="group.cs" />
                        </flux:field>
                    </x-slot:cs>
                </x-manage.locale-tabs>

                <flux:field>
                    <flux:label>Frequency</flux:label>
                    <flux:select wire:model="frequency">
                        @foreach (\App\Models\Tool::FREQUENCIES as $option)
                            <flux:select.option value="{{ $option }}">{{ $option }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="frequency" />
                </flux:field>

                <flux:field>
                    <flux:label>Colour</flux:label>
                    <flux:input wire:model="color" placeholder="#6DB33F" />
                    <flux:description>Brand hex for the 6px dot. Empty falls back to gold.</flux:description>
                    <flux:error name="color" />
                </flux:field>

                <flux:checkbox wire:model="showOnHome" label="Show on the home page" description="The home grid is the weekly drivers only — eight tools read best." />
            @endif

            <x-manage.modal-footer :editing="(bool) $editingId" />
        </form>
    </flux:modal>

    <x-manage.delete-modal entity="tool" />

</div>
