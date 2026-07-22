<?php

use App\Models\Setting;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage Site content')] class extends Component {
    /** Plain translatable text settings: key => ['en'=>.., 'cs'=>..]. */
    public array $texts = [];

    /** Rotating hero roles as newline-separated strings per locale. */
    public array $roles = ['en' => '', 'cs' => ''];

    /** @var array<int, string> Keys edited as simple {en,cs} text fields. */
    public array $textKeys = [
        'hero_suptitle',
        'hero_title',
        'stats_title',
        'tools_title',
        'reviews_title',
        'about_title',
    ];

    public function mount(): void
    {
        foreach ($this->textKeys as $key) {
            $this->texts[$key] = [
                'en' => Setting::text($key, 'en'),
                'cs' => Setting::text($key, 'cs'),
            ];
        }

        $this->roles = [
            'en' => implode("\n", Setting::list('hero_roles', 'en')),
            'cs' => implode("\n", Setting::list('hero_roles', 'cs')),
        ];
    }

    public function save(): void
    {
        $rules = ['roles.en' => ['required', 'string'], 'roles.cs' => ['nullable', 'string']];

        foreach ($this->textKeys as $key) {
            $rules["texts.{$key}.en"] = ['required', 'string', 'max:2000'];
            $rules["texts.{$key}.cs"] = ['nullable', 'string', 'max:2000'];
        }

        $this->validate($rules);

        foreach ($this->textKeys as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => [
                'en' => $this->texts[$key]['en'],
                'cs' => $this->texts[$key]['cs'] ?: $this->texts[$key]['en'],
            ]]);
        }

        Setting::updateOrCreate(['key' => 'hero_roles'], ['value' => [
            'en' => $this->splitLines($this->roles['en']),
            'cs' => $this->splitLines($this->roles['cs'] ?: $this->roles['en']),
        ]]);

        Flux::toast(text: 'Site content saved.', variant: 'success');
    }

    /** @return array<int, string> */
    private function splitLines(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /** Human label for a setting key. */
    public function label(string $key): string
    {
        return ucfirst(str_replace('_', ' ', $key));
    }
}; ?>

<div class="manage-page p-6 space-y-6">

    <x-manage.page-header title="Site content" subtitle="Hero text, rotating roles and section titles" />

    <form wire:submit="save" class="space-y-6 max-w-3xl">
        <x-manage.locale-tabs>
            <x-slot:en>
                @foreach ($textKeys as $key)
                    <flux:field>
                        <flux:label>{{ $this->label($key) }} <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                        <flux:input wire:model="texts.{{ $key }}.en" />
                        <flux:error name="texts.{{ $key }}.en" />
                    </flux:field>
                @endforeach
                <flux:field>
                    <flux:label>Hero roles <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                    <flux:textarea wire:model="roles.en" rows="5" placeholder="One role per line" />
                    <flux:description>One rotating role per line.</flux:description>
                    <flux:error name="roles.en" />
                </flux:field>
            </x-slot:en>
            <x-slot:cs>
                @foreach ($textKeys as $key)
                    <flux:field>
                        <flux:label>{{ $this->label($key) }}</flux:label>
                        <flux:input wire:model="texts.{{ $key }}.cs" />
                        <flux:error name="texts.{{ $key }}.cs" />
                    </flux:field>
                @endforeach
                <flux:field>
                    <flux:label>Hero roles</flux:label>
                    <flux:textarea wire:model="roles.cs" rows="5" placeholder="Jedna role na řádek" />
                    <flux:description>Jedna rotující role na řádek.</flux:description>
                    <flux:error name="roles.cs" />
                </flux:field>
            </x-slot:cs>
        </x-manage.locale-tabs>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary" class="btn-gold">Save</flux:button>
        </div>
    </form>

</div>
