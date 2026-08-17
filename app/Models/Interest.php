<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'highlight',
        'body',
        'meta',
        'image_path',
        'sort_order',
    ];

    protected $casts = [
        'title' => 'array',
        'highlight' => 'array',
        'body' => 'array',
        'meta' => 'array',
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
     * HTML-safe title with the `highlight` phrase (if present and found
     * verbatim) wrapped in a <span> for the gold accent. Escaping happens
     * before the substring match, so the highlight can never break out of
     * the surrounding markup.
     */
    public function highlightedTitle(string $locale): string
    {
        $title = e($this->getTranslation('title', $locale));
        $highlight = $this->getTranslation('highlight', $locale);

        if ($highlight === '') {
            return $title;
        }

        $escapedHighlight = e($highlight);

        if (! str_contains($title, $escapedHighlight)) {
            return $title;
        }

        return str_replace($escapedHighlight, '<span>'.$escapedHighlight.'</span>', $title);
    }
}
