<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    use HasFactory, HasUuids;

    /** Birthday used to auto-compute the "years old" stat. */
    private const BIRTH_DATE = '2006-10-05';

    /** Year the developer journey started, for the "years of experience" stat. */
    private const CAREER_START_YEAR = 2022;

    protected $fillable = [
        'value',
        'text',
        'value_id',
        'source',
        'sort_order',
    ];

    protected $casts = [
        'value' => 'array',
        'text' => 'array',
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
     * The value to render. Cards with a `source` are computed server-side so
     * they stay current without editing; everything else uses the stored text.
     */
    public function displayValue(string $locale): string
    {
        return match ($this->source) {
            'age' => (string) (int) CarbonImmutable::parse(self::BIRTH_DATE)->diffInYears(now()),
            'years_experience' => (now()->year - self::CAREER_START_YEAR).'+',
            default => $this->getTranslation('value', $locale),
        };
    }
}
