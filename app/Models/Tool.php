<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Tool extends Model
{
    use HasFactory, HasUuids;

    /**
     * Where a row shows up: `grid` is a full entry (dot, name, state) in the
     * about-me groups and — when `show_on_home` — in the home cells;
     * `rotation` and `learning` are chip-only rows in the two footers.
     */
    public const PLACEMENTS = ['grid', 'rotation', 'learning'];

    /**
     * Two states, never a percentage and never a skill bar: `using` is what
     * gets built with right now, `experience` is what has shipped before and
     * could be picked back up. Translated in the pages/toolbox lang files.
     */
    public const STATES = ['using', 'experience'];

    protected $fillable = [
        'name',
        'color',
        'state',
        'group',
        'group_note',
        'placement',
        'show_on_home',
        'sort_order',
    ];

    protected $casts = [
        'group' => 'array',
        'group_note' => 'array',
        'show_on_home' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getTranslation(string $field, string $locale, string $fallback = 'en'): string
    {
        $value = $this->{$field};

        if (! is_array($value)) {
            return '';
        }

        return $value[$locale] ?? $value[$fallback] ?? '';
    }

    /**
     * Grid tools bucketed by their English group name, which is the stable key
     * across locales — the heading itself is read back in the visitor's locale.
     * Each group is sorted current-first so `using` rows lead and `experience`
     * rows trail, instead of the two states interleaving down the column.
     *
     * @param  Collection<int, Tool>  $tools
     * @return Collection<string, Collection<int, Tool>>
     */
    public static function groupByArea(Collection $tools): Collection
    {
        return $tools
            ->where('placement', 'grid')
            ->groupBy(fn (Tool $tool) => $tool->getTranslation('group', 'en'))
            ->reject(fn (Collection $group, string $key) => $key === '')
            ->map(fn (Collection $group) => $group->sortBy(fn (Tool $tool) => array_search($tool->state, self::STATES, true) === false
                    ? count(self::STATES)
                    : array_search($tool->state, self::STATES, true))->values());
    }

    /**
     * The human note beside a group heading. It rides on the tool rows rather
     * than on a group table, so the first row that carries one speaks for the
     * whole group and the rest may leave it empty.
     *
     * @param  Collection<int, Tool>  $group
     */
    public static function noteFor(Collection $group, string $locale): string
    {
        return $group
            ->map(fn (Tool $tool) => $tool->getTranslation('group_note', $locale))
            ->first(fn (string $note) => $note !== '', '');
    }
}
