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
     * Where a row shows up: `grid` is a full entry (dot, name, frequency) in
     * the about-me columns and — when `show_on_home` — in the home cells;
     * `rotation` and `learning` are chip-only rows in the two footers.
     */
    public const PLACEMENTS = ['grid', 'rotation', 'learning'];

    /** Rendered as the monospace right-hand label; translated in the pages/toolbox lang files. */
    public const FREQUENCIES = ['daily', 'often', 'sometimes', 'rarely'];

    protected $fillable = [
        'name',
        'color',
        'frequency',
        'group',
        'placement',
        'show_on_home',
        'sort_order',
    ];

    protected $casts = [
        'group' => 'array',
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
     *
     * @param  Collection<int, Tool>  $tools
     * @return Collection<string, Collection<int, Tool>>
     */
    public static function groupColumns(Collection $tools): Collection
    {
        return $tools
            ->where('placement', 'grid')
            ->groupBy(fn (Tool $tool) => $tool->getTranslation('group', 'en'))
            ->reject(fn (Collection $column, string $key) => $key === '');
    }
}
