<?php

use App\Models\Setting;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage Site content')] class extends Component {
    /** Plain translatable text settings: key => ['en'=>.., 'cs'=>..]. */
    public array $texts = [];

    /** Rotator role lists: key => ['en'=>.., 'cs'=>..], newline-separated. */
    public array $roleLists = [];

    /** @var array<int, string> Keys edited as simple {en,cs} text fields. */
    public array $textKeys = [
        'hero_suptitle',
        'hero_title',
        'about_hero_suptitle',
        'about_hero_title',
        'experience_hero_suptitle',
        'experience_hero_title',
        'projects_hero_suptitle',
        'projects_hero_title',
        'stats_title',
        'tools_title',
        'reviews_title',
        'about_title',
    ];

    /** @var array<int, string> Keys edited as a newline-separated textarea. */
    public array $roleListKeys = [
        'hero_roles',
        'about_hero_roles',
        'experience_hero_roles',
        'projects_hero_roles',
    ];

    /**
     * Rendering order: heading => field keys, drawn from both lists above.
     * Sixteen ungrouped inputs per locale tab would be unusable.
     *
     * @var array<string, array<int, string>>
     */
    public array $groups = [
        'Home hero' => ['hero_suptitle', 'hero_title', 'hero_roles'],
        'About me hero' => ['about_hero_suptitle', 'about_hero_title', 'about_hero_roles'],
        'Experience hero' => ['experience_hero_suptitle', 'experience_hero_title', 'experience_hero_roles'],
        'Projects hero' => ['projects_hero_suptitle', 'projects_hero_title', 'projects_hero_roles'],
        'Section titles' => ['stats_title', 'tools_title', 'reviews_title', 'about_title'],
    ];

    public function mount(): void
    {
        foreach ($this->textKeys as $key) {
            $this->texts[$key] = [
                'en' => Setting::text($key, 'en'),
                'cs' => Setting::text($key, 'cs'),
            ];
        }

        foreach ($this->roleListKeys as $key) {
            $this->roleLists[$key] = [
                'en' => implode("\n", Setting::list($key, 'en')),
                'cs' => implode("\n", Setting::list($key, 'cs')),
            ];
        }
    }

    public function save(): void
    {
        $rules = [];

        foreach ($this->textKeys as $key) {
            $rules["texts.{$key}.en"] = ['required', 'string', 'max:2000'];
            $rules["texts.{$key}.cs"] = ['nullable', 'string', 'max:2000'];
        }

        foreach ($this->roleListKeys as $key) {
            $rules["roleLists.{$key}.en"] = ['required', 'string'];
            $rules["roleLists.{$key}.cs"] = ['nullable', 'string'];
        }

        $this->validate($rules);

        foreach ($this->textKeys as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => [
                'en' => $this->texts[$key]['en'],
                'cs' => $this->texts[$key]['cs'] ?: $this->texts[$key]['en'],
            ]]);
        }

        foreach ($this->roleListKeys as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => [
                'en' => $this->splitLines($this->roleLists[$key]['en']),
                'cs' => $this->splitLines($this->roleLists[$key]['cs'] ?: $this->roleLists[$key]['en']),
            ]]);
        }

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

    /** True when a key is edited as a newline-separated rotator list. */
    public function isRoleList(string $key): bool
    {
        return in_array($key, $this->roleListKeys, true);
    }

    /** Human label for a key, minus the page prefix the group heading already carries. */
    public function label(string $key): string
    {
        $trimmed = preg_replace('/^(about|experience|projects)_hero_/', '', $key);

        return ucfirst(str_replace('_', ' ', $trimmed));
    }
}; ?>

<div class="manage-page p-6 space-y-6">

    <x-manage.page-header title="Site content" subtitle="Page hero copy, rotating roles and section titles" />

    <form wire:submit="save" class="space-y-6 max-w-3xl">
        <x-manage.locale-tabs>
            <x-slot:en>
                @foreach ($groups as $heading => $keys)
                    <flux:heading size="lg" class="pt-2">{{ $heading }}</flux:heading>
                    @foreach ($keys as $key)
                        <flux:field>
                            <flux:label>{{ $this->label($key) }} <flux:badge size="sm" color="yellow" inset="top bottom">Required</flux:badge></flux:label>
                            @if ($this->isRoleList($key))
                                <flux:textarea wire:model="roleLists.{{ $key }}.en" rows="5" placeholder="One role per line" />
                                <flux:description>One rotating role per line.</flux:description>
                                <flux:error name="roleLists.{{ $key }}.en" />
                            @else
                                <flux:input wire:model="texts.{{ $key }}.en" />
                                <flux:error name="texts.{{ $key }}.en" />
                            @endif
                        </flux:field>
                    @endforeach
                @endforeach
            </x-slot:en>
            <x-slot:cs>
                @foreach ($groups as $heading => $keys)
                    <flux:heading size="lg" class="pt-2">{{ $heading }}</flux:heading>
                    @foreach ($keys as $key)
                        <flux:field>
                            <flux:label>{{ $this->label($key) }}</flux:label>
                            @if ($this->isRoleList($key))
                                <flux:textarea wire:model="roleLists.{{ $key }}.cs" rows="5" placeholder="Jedna role na řádek" />
                                <flux:description>Jedna rotující role na řádek.</flux:description>
                                <flux:error name="roleLists.{{ $key }}.cs" />
                            @else
                                <flux:input wire:model="texts.{{ $key }}.cs" />
                                <flux:error name="texts.{{ $key }}.cs" />
                            @endif
                        </flux:field>
                    @endforeach
                @endforeach
            </x-slot:cs>
        </x-manage.locale-tabs>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary" class="btn-gold">Save</flux:button>
        </div>
    </form>

</div>
