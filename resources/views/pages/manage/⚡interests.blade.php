<?php

use App\Models\Interest;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage interests')] class extends Component {
    public string $search = '';

    public ?string $editingId = null;
    public array $interestTitle = ['en' => '', 'cs' => ''];
    public array $highlight = ['en' => '', 'cs' => ''];
    public array $body = ['en' => '', 'cs' => ''];
    public array $meta = ['en' => '', 'cs' => ''];
    public array $label = ['en' => '', 'cs' => ''];
    public array $pull = ['en' => '', 'cs' => ''];

    /** @var array<int, array{en_dt: string, en_dd: string, cs_dt: string, cs_dd: string}> */
    public array $facts = [];

    public string $imagePath = '';

    public ?string $deletingId = null;

    #[Computed]
    public function interests(): \Illuminate\Support\Collection
    {
        return Interest::query()
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

        $interests = Interest::orderBy('sort_order')->get();
        $item = $interests->firstWhere('id', $id);

        if (! $item) {
            return;
        }

        $interests = $interests->reject(fn ($i) => $i->id === $id)->values();
        $interests->splice($position, 0, [$item]);
        $interests->each(fn ($i, $idx) => Interest::where('id', $i->id)->update(['sort_order' => $idx]));

        unset($this->interests);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modal('form')->show();
    }

    public function openEdit(string $id): void
    {
        $interest = Interest::findOrFail($id);
        $this->editingId = $id;
        $this->interestTitle = array_merge(['en' => '', 'cs' => ''], $interest->title ?? []);
        $this->highlight = array_merge(['en' => '', 'cs' => ''], $interest->highlight ?? []);
        $this->body = array_merge(['en' => '', 'cs' => ''], $interest->body ?? []);
        $this->meta = array_merge(['en' => '', 'cs' => ''], $interest->meta ?? []);
        $this->label = array_merge(['en' => '', 'cs' => ''], $interest->label ?? []);
        $this->pull = array_merge(['en' => '', 'cs' => ''], $interest->pull ?? []);
        $this->facts = $this->factsToRows($interest->facts ?? []);
        $this->imagePath = $interest->image_path ?? '';
        $this->modal('form')->show();
    }

    public function addFactRow(): void
    {
        $this->facts[] = ['en_dt' => '', 'en_dd' => '', 'cs_dt' => '', 'cs_dd' => ''];
    }

    public function removeFactRow(int $index): void
    {
        unset($this->facts[$index]);
        $this->facts = array_values($this->facts);
    }

    public function save(): void
    {
        $interest = $this->editingId ? Interest::findOrFail($this->editingId) : null;

        $validated = $this->validate([
            'interestTitle' => ['required', 'array'],
            'interestTitle.en' => ['required', 'string', 'max:150'],
            'interestTitle.cs' => ['nullable', 'string', 'max:150'],
            'highlight' => ['nullable', 'array'],
            'highlight.en' => ['nullable', 'string', 'max:150'],
            'highlight.cs' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'array'],
            'body.en' => ['required', 'string', 'max:2000'],
            'body.cs' => ['nullable', 'string', 'max:2000'],
            'meta' => ['nullable', 'array'],
            'meta.en' => ['nullable', 'string', 'max:120'],
            'meta.cs' => ['nullable', 'string', 'max:120'],
            'label' => ['nullable', 'array'],
            'label.en' => ['nullable', 'string', 'max:60'],
            'label.cs' => ['nullable', 'string', 'max:60'],
            'pull' => ['nullable', 'array'],
            'pull.en' => ['nullable', 'string', 'max:300'],
            'pull.cs' => ['nullable', 'string', 'max:300'],
            'facts' => ['array'],
            'facts.*.en_dt' => ['nullable', 'string', 'max:60'],
            'facts.*.en_dd' => ['nullable', 'string', 'max:120'],
            'facts.*.cs_dt' => ['nullable', 'string', 'max:60'],
            'facts.*.cs_dd' => ['nullable', 'string', 'max:120'],
            'imagePath' => ['nullable', 'string', 'max:255'],
        ]);

        $data = [
            'title' => array_filter($validated['interestTitle'], fn ($v) => filled($v)),
            'highlight' => array_filter($validated['highlight'] ?? [], fn ($v) => filled($v)),
            'body' => array_filter($validated['body'], fn ($v) => filled($v)),
            'meta' => array_filter($validated['meta'] ?? [], fn ($v) => filled($v)),
            'label' => array_filter($validated['label'] ?? [], fn ($v) => filled($v)),
            'pull' => array_filter($validated['pull'] ?? [], fn ($v) => filled($v)),
            'facts' => $this->rowsToFacts($validated['facts'] ?? []),
            'image_path' => $validated['imagePath'] ?: null,
        ];

        if ($interest) {
            $interest->update($data);
        } else {
            $data['sort_order'] = (int) Interest::max('sort_order') + 1;
            Interest::create($data);
        }

        $this->modal('form')->close();
        $this->resetForm();
        unset($this->interests);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->modal('delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Interest::findOrFail($this->deletingId)->delete();
            $this->deletingId = null;
            $this->modal('delete')->close();
            unset($this->interests);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->interestTitle = ['en' => '', 'cs' => ''];
        $this->highlight = ['en' => '', 'cs' => ''];
        $this->body = ['en' => '', 'cs' => ''];
        $this->meta = ['en' => '', 'cs' => ''];
        $this->label = ['en' => '', 'cs' => ''];
        $this->pull = ['en' => '', 'cs' => ''];
        $this->facts = [];
        $this->imagePath = '';
        $this->resetValidation();
    }

    /**
     * `facts` model shape ({en: [{dt,dd}], cs: [{dt,dd}]}) to aligned form
     * rows ({en_dt, en_dd, cs_dt, cs_dd}), matching English and Czech facts
     * by position.
     *
     * @param  array<string, mixed>  $facts
     * @return array<int, array{en_dt: string, en_dd: string, cs_dt: string, cs_dd: string}>
     */
    private function factsToRows(array $facts): array
    {
        $en = is_array($facts['en'] ?? null) ? $facts['en'] : [];
        $cs = is_array($facts['cs'] ?? null) ? $facts['cs'] : [];
        $count = max(count($en), count($cs));

        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'en_dt' => $en[$i]['dt'] ?? '',
                'en_dd' => $en[$i]['dd'] ?? '',
                'cs_dt' => $cs[$i]['dt'] ?? '',
                'cs_dd' => $cs[$i]['dd'] ?? '',
            ];
        }

        return $rows;
    }

    /**
     * Form rows back to the `facts` model shape, dropping rows that are
     * blank in a given locale.
     *
     * @param  array<int, array{en_dt: string, en_dd: string, cs_dt: string, cs_dd: string}>  $rows
     * @return array{en: array<int, array{dt: string, dd: string}>, cs: array<int, array{dt: string, dd: string}>}
     */
    private function rowsToFacts(array $rows): array
    {
        $en = collect($rows)
            ->filter(fn (array $row) => filled($row['en_dt'] ?? null) && filled($row['en_dd'] ?? null))
            ->map(fn (array $row) => ['dt' => $row['en_dt'], 'dd' => $row['en_dd']])
            ->values()
            ->all();

        $cs = collect($rows)
            ->filter(fn (array $row) => filled($row['cs_dt'] ?? null) && filled($row['cs_dd'] ?? null))
            ->map(fn (array $row) => ['dt' => $row['cs_dt'], 'dd' => $row['cs_dd']])
            ->values()
            ->all();

        return ['en' => $en, 'cs' => $cs];
    }
}; ?>

<div class="manage-page p-6 space-y-6">

    <x-manage.page-header title="Interests" subtitle="The interest cards on the about-me page">
        <flux:button wire:click="openCreate" icon="plus" class="btn-gold">Add interest</flux:button>
    </x-manage.page-header>

    <x-manage.search-input placeholder="Search by title…" />

    {{-- Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column></flux:table.column>
            <flux:table.column>Title (EN)</flux:table.column>
            <flux:table.column>Title (CS)</flux:table.column>
            <flux:table.column>Updated</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows wire:sort="reorder">
            @forelse ($this->interests as $interest)
                <flux:table.row wire:key="{{ $interest->id }}" wire:sort:item="{{ $interest->id }}">
                    <flux:table.cell wire:sort:handle class="manage-drag-handle">
                        <x-manage.drag-handle />
                    </flux:table.cell>
                    <flux:table.cell variant="strong">{{ $interest->title['en'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $interest->title['cs'] ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $interest->updated_at->format('d.m.Y') }}</flux:table.cell>
                    <flux:table.cell wire:sort:ignore>
                        <div class="flex gap-2 justify-end">
                            <flux:button size="sm" variant="subtle" icon="pencil" aria-label="Edit interest" wire:click="openEdit('{{ $interest->id }}')" />
                            <flux:button size="sm" variant="subtle" icon="trash" aria-label="Delete interest" wire:click="confirmDelete('{{ $interest->id }}')" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <x-manage.empty-row colspan="5" message="No interests found." />
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit modal --}}
    <flux:modal name="form" class="w-full md:w-[640px]">
        <flux:heading>{{ $editingId ? 'Edit interest' : 'New interest' }}</flux:heading>
        <flux:text class="mt-1 mb-5">The highlight is rendered gold inside the title — it must appear in the title verbatim.</flux:text>

        <form wire:submit="save" class="space-y-4">
            {{-- Language tabs --}}
            <x-manage.locale-tabs>
                <x-slot:en>
                    <flux:field>
                        <flux:label>Label</flux:label>
                        <flux:input wire:model="label.en" placeholder="e.g. Chess" />
                        <flux:description>Short chapter label shown above the title.</flux:description>
                        <flux:error name="label.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Title <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:input wire:model="interestTitle.en" placeholder="e.g. Chess since the second grade" />
                        <flux:error name="interestTitle.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Highlight</flux:label>
                        <flux:input wire:model="highlight.en" placeholder="e.g. second grade" />
                        <flux:error name="highlight.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Body <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:textarea wire:model="body.en" placeholder="First paragraph.&#10;&#10;Second paragraph." rows="7" />
                        <flux:description>Two paragraphs, separated by a blank line.</flux:description>
                        <flux:error name="body.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Pull line</flux:label>
                        <flux:input wire:model="pull.en" placeholder="Optional standout line" />
                        <flux:error name="pull.en" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Meta</flux:label>
                        <flux:input wire:model="meta.en" placeholder="e.g. 1684 elo" />
                        <flux:error name="meta.en" />
                    </flux:field>
                </x-slot:en>
                <x-slot:cs>
                    <flux:field>
                        <flux:label>Label</flux:label>
                        <flux:input wire:model="label.cs" placeholder="např. Šachy" />
                        <flux:description>Krátký popisek kapitoly nad nadpisem.</flux:description>
                        <flux:error name="label.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Title</flux:label>
                        <flux:input wire:model="interestTitle.cs" placeholder="např. Šachy od druhé třídy" />
                        <flux:error name="interestTitle.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Highlight</flux:label>
                        <flux:input wire:model="highlight.cs" placeholder="např. druhé třídy" />
                        <flux:error name="highlight.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Body</flux:label>
                        <flux:textarea wire:model="body.cs" placeholder="První odstavec.&#10;&#10;Druhý odstavec." rows="7" />
                        <flux:description>Dva odstavce oddělené prázdným řádkem.</flux:description>
                        <flux:error name="body.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Pull line</flux:label>
                        <flux:input wire:model="pull.cs" placeholder="Volitelná zvýrazněná věta" />
                        <flux:error name="pull.cs" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Meta</flux:label>
                        <flux:input wire:model="meta.cs" placeholder="např. 1684 elo" />
                        <flux:error name="meta.cs" />
                    </flux:field>
                </x-slot:cs>
            </x-manage.locale-tabs>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <flux:label>Facts</flux:label>
                    <flux:button size="sm" variant="subtle" icon="plus" wire:click="addFactRow">Add fact</flux:button>
                </div>

                @foreach ($facts as $index => $row)
                    <div wire:key="fact-{{ $index }}" class="grid grid-cols-1 md:grid-cols-[1fr_1fr_auto] gap-2 items-start rounded-lg border border-zinc-200 dark:border-zinc-700 p-3">
                        <div class="grid grid-cols-2 gap-2">
                            <flux:input wire:model="facts.{{ $index }}.en_dt" placeholder="Label (EN)" />
                            <flux:input wire:model="facts.{{ $index }}.en_dd" placeholder="Value (EN)" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <flux:input wire:model="facts.{{ $index }}.cs_dt" placeholder="Popisek (CS)" />
                            <flux:input wire:model="facts.{{ $index }}.cs_dd" placeholder="Hodnota (CS)" />
                        </div>
                        <flux:button size="sm" variant="subtle" icon="trash" aria-label="Remove fact" wire:click="removeFactRow({{ $index }})" />
                    </div>
                @endforeach
            </div>

            <flux:field>
                <flux:label>Image path</flux:label>
                <flux:input wire:model="imagePath" placeholder="images/about/chess.webp" />
                <flux:description>Relative to public/. Empty renders the striped placeholder plate.</flux:description>
                <flux:error name="imagePath" />
            </flux:field>

            <x-manage.modal-footer :editing="(bool) $editingId" />
        </form>
    </flux:modal>

    <x-manage.delete-modal entity="interest" />

</div>
