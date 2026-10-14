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
        'label',
        'pull',
        'facts',
        'image_path',
        'sort_order',
    ];

    protected $casts = [
        'title' => 'array',
        'highlight' => 'array',
        'body' => 'array',
        'meta' => 'array',
        'label' => 'array',
        'pull' => 'array',
        'facts' => 'array',
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

    /**
     * `body` split into paragraphs on a blank line, trimmed, empty ones dropped.
     * A single-paragraph body still comes back as a one-element array.
     *
     * @return array<int, string>
     */
    public function paragraphs(string $locale): array
    {
        $body = $this->getTranslation('body', $locale);

        if ($body === '') {
            return [];
        }

        return collect(preg_split('/\R\s*\R/', $body))
            ->map(fn (string $paragraph) => trim($paragraph))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * The chapter's fact rows for the given locale, falling back to English.
     * Named `factRows` rather than `facts` so it does not collide with the
     * `facts` attribute/cast.
     *
     * @return array<int, array{dt: string, dd: string}>
     */
    public function factRows(string $locale): array
    {
        $facts = $this->facts[$locale] ?? $this->facts['en'] ?? [];

        if (! is_array($facts)) {
            return [];
        }

        return $facts;
    }
}
