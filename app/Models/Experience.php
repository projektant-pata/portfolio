<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'is_special',
        'title',
        'subtitle',
        'content',
        'year',
        'image_path',
        'links',
        'sort_order',
        'show_on_about',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_special' => 'boolean',
        'links' => 'array',
        'title' => 'array',
        'subtitle' => 'array',
        'content' => 'array',
        'year' => 'array',
        'show_on_about' => 'boolean',
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
     * The span a set of entries covers, as `2021—2024` — or `2021—now` when
     * any of them is still open-ended ("2022 - now"). Derived from the year
     * strings themselves, so the home counter never drifts from the data.
     *
     * @param  Collection<int, self>  $entries
     */
    public static function timelineSpan(Collection $entries, string $locale): string
    {
        $years = [];
        $isOpenEnded = false;

        foreach ($entries as $entry) {
            $raw = $entry->getTranslation('year', $locale);

            preg_match_all('/\d{4}/', $raw, $matches);

            foreach ($matches[0] as $year) {
                $years[] = (int) $year;
            }

            /* "2022 - now" / "2021 — teď": a dash followed by anything that is
               not another year means the entry has no end yet. */
            if (preg_match('/\d{4}\s*[-–—]\s*(?!\d{4})\S/u', $raw)) {
                $isOpenEnded = true;
            }
        }

        if ($years === []) {
            return '';
        }

        $first = (string) min($years);
        $last = $isOpenEnded ? __('home/experience.wl_span_now') : (string) max($years);

        return $first === $last ? $first : $first.'—'.$last;
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'experience_badge');
    }
}
