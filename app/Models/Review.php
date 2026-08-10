<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'position',
        'text',
        'highlight',
        'source',
        'source_color',
        'sort_order',
    ];

    protected $casts = [
        'position' => 'array',
        'text' => 'array',
        'highlight' => 'array',
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
     * Initials for the avatar badge, e.g. "Jana Dvořáková" -> "JD".
     */
    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name)) ?: [];

        return mb_strtoupper(implode('', array_map(
            fn (string $word) => mb_substr($word, 0, 1),
            array_slice($words, 0, 2)
        )));
    }

    /**
     * HTML-safe quote text with the `highlight` phrase (if present and found
     * verbatim in `text`) wrapped in a <span> for the gold accent. Escaping
     * happens before the substring match, so the highlight can never break
     * out of the surrounding markup.
     */
    public function highlightedText(string $locale): string
    {
        $text = e($this->getTranslation('text', $locale));
        $highlight = $this->getTranslation('highlight', $locale);

        if ($highlight === '') {
            return $text;
        }

        $escapedHighlight = e($highlight);

        if (! str_contains($text, $escapedHighlight)) {
            return $text;
        }

        return str_replace($escapedHighlight, '<span>'.$escapedHighlight.'</span>', $text);
    }
}
